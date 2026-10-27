# Webkul SaaS — multi-tenant layer for Bagisto

This package turns a single Bagisto 2.5.x installation into a multi-tenant commerce platform. It uses a shared database with an explicit `tenant_id` ownership column, host-based storefront resolution, a separate Super Admin panel, plan-based entitlements and a provider-independent billing layer.

The package lives at `packages/Webkul/Saas` and is already wired into the application: `SaasServiceProvider` is registered in `bootstrap/providers.php`, `ModuleServiceProvider` is registered in `config/concord.php`, the PSR-4 namespace is in `composer.json`, and the Pest suites are declared in `phpunit.xml`.

## Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Architecture](#architecture)
- [Tenant resolution and domains](#tenant-resolution-and-domains)
- [Registration and activation](#registration-and-activation)
- [Provisioning](#provisioning)
- [Tenant lifecycle](#tenant-lifecycle)
- [Plans, entitlements and usage](#plans-entitlements-and-usage)
- [Subscriptions and billing](#subscriptions-and-billing)
- [Super Admin panel](#super-admin-panel)
- [Tenant admin panel](#tenant-admin-panel)
- [Storefront and commerce](#storefront-and-commerce)
- [Versioned API](#versioned-api)
- [Security model](#security-model)
- [Commands and schedule](#commands-and-schedule)
- [Notifications](#notifications)
- [Legacy data and migrations](#legacy-data-and-migrations)
- [Testing](#testing)
- [Known limitations](#known-limitations)

## Requirements

- Bagisto 2.5.x, Laravel 13, PHP 8.4.
- MySQL 8.0, MariaDB 10.11 or PostgreSQL 16; every migration and query in the package targets all three.
- A queue worker and the Laravel scheduler for provisioning, exports, usage collection, domain checks, trial reminders and subscription lifecycle work.
- DNS control for the platform domain when custom domains are enabled.

## Installation

Run a normal Bagisto installation first, then the SaaS bootstrap commands.

```bash
php artisan migrate
php artisan saas:install
php artisan saas:create-super-admin
```

`saas:install` seeds the Free, Starter and Growth plans and the default platform settings. It is idempotent: existing plans and settings are never overwritten. `saas:create-super-admin` prompts for the name, email, password and password confirmation; it never uses or prints a default password. Pass `--name=`, `--email=` and `--role=owner|admin|support` to skip the corresponding prompts. The password is always read through a hidden prompt.

After installation:

1. Start a queue worker with `php artisan queue:work`.
2. Make sure the scheduler runs `php artisan schedule:run` every minute.
3. Point DNS at the platform host and, when custom domains are used, make sure `SAAS_PLATFORM_DOMAIN` matches the host that serves `/super-admin` and `/saas/register`. If you reach the app through an address that differs from `APP_URL` (for example `127.0.0.1` while `APP_URL` uses `localhost`), add that address to `SAAS_PLATFORM_HOSTS`; otherwise the platform-host middleware returns 404.
4. For platform subdomains, configure a wildcard DNS record for the configured suffix (for example `*.saas.example.com`) at the edge. The application resolves those hostnames, but it does not create DNS records.

The Super Admin panel is available at `/super-admin`, public registration at `/saas/register`, and the tenant admin area at `/admin/saas` (with the configured `APP_ADMIN_URL`).

## Configuration

The package merges `packages/Webkul/Saas/src/Config/saas.php` into the `saas` config key. The following environment variables are read:

| Variable | Default | Purpose |
|---|---|---|
| `SAAS_ENABLED` | `true` | Master switch. When false the platform behaves like a normal Bagisto installation; host resolution, tenant scoping and the SaaS panels are disabled. |
| `SAAS_PLATFORM_NAME` | `Bagisto SaaS` | Display name used by the public pages and mail. |
| `SAAS_PLATFORM_DOMAIN` | host of `APP_URL` | The host that serves the platform panel, registration and plan pages. It is never resolved as a tenant storefront. |
| `SAAS_PLATFORM_HOSTS` | empty | Comma-separated additional hosts treated as platform hosts. |
| `SAAS_SUPER_ADMIN_PREFIX` | `super-admin` | URL prefix of the platform panel. |
| `SAAS_DOMAIN_SUFFIX` | value of `SAAS_PLATFORM_DOMAIN` | Suffix used for tenant platform subdomains (`{slug}.{suffix}`). |
| `SAAS_ALLOW_CUSTOM_DOMAINS` | `true` | Whether tenants may attach customer-owned hostnames. |
| `SAAS_CACHE_STORE` | default cache store | Store used for host resolution caching. |
| `SAAS_CACHE_TTL` | `300` | Lifetime of a successful host lookup, in seconds. Negative lookups use the domain verification cache TTL (60 seconds). |
| `SAAS_REGISTRATION_ENABLED` | `true` | Hard switch for public registration combined with the stored platform setting. |
| `SAAS_REQUIRE_EMAIL_VERIFICATION` | `true` | Default for the stored setting that gates activation on owner email verification. |
| `SAAS_REQUIRE_APPROVAL` | `true` | Default for the stored setting that gates activation on Super Admin approval. |
| `SAAS_DEFAULT_PLAN` | `free` | Plan slug used when a registration does not choose a plan. |
| `SAAS_TRIAL_DAYS` | `14` | Default trial length for paid plans. |
| `SAAS_DEFAULT_TIMEZONE` | `UTC` | Tenant timezone default. |
| `SAAS_DEFAULT_LOCALE` | `en` | Tenant locale default. |
| `SAAS_DEFAULT_CURRENCY` | `USD` | Tenant currency default. |
| `SAAS_EXPORT_DISK` | `local` | Private disk that receives tenant exports. |
| `SAAS_TENANT_RETENTION_DAYS` | `30` | Documented soft-delete retention policy; see limitations. |
| `SAAS_BILLING_PROVIDER` | `manual` | Billing adapter. `manual` records offline payments. Any other value resolves to the fail-closed unconfigured adapter. |
| `SAAS_BILLING_CURRENCY` | `USD` | Fallback invoice currency. |
| `SAAS_BILLING_GRACE_DAYS` | `3` | Grace period after an invoice becomes past due. |
| `SAAS_INVOICE_DUE_DAYS` | `7` | Days an invoice stays open before it is marked past due. |
| `SAAS_QUOTA_GRACE_PERCENT` | `0` | Optional overage tolerance applied to numeric quotas. |
| `SAAS_API_RATE_LIMIT` | `60` | Requests per minute for the versioned SaaS API, keyed per tenant. |
| `SAAS_REGISTRATION_RATE_LIMIT` | `5` | Registrations per hour per IP. |
| `SAAS_MAIL_FROM` | `MAIL_FROM_ADDRESS` | Sender for SaaS notifications. |
| `SAAS_NOTIFICATION_QUEUE` | `default` | Queue name for SaaS notifications. |

Platform settings stored in `saas_platform_settings` take precedence over the config defaults. They are managed at `/super-admin/settings`. The one exception is registration: `SAAS_REGISTRATION_ENABLED=false` disables registration regardless of the stored setting, and the stored setting can also close registration while the environment flag is true.

Do not commit real credentials. Payment provider credentials belong in the environment or an encrypted settings entry, never in source control; the audit logger strips credential-shaped keys before writing metadata.

## Architecture

### Shared database

Every tenant-owned row carries a nullable `tenant_id`. The migration `2026_10_27_101300_add_tenant_id_to_commerce_tables.php` adds the column to `admins`, `products`, `categories`, `customers`, `cart`, `orders`, `invoices`, `shipments`, `refunds`, `order_transactions`, `inventory_sources`, `cms_pages`, `cart_rules`, `catalog_rules`, `wishlist_items` and `product_reviews`. It also replaces the global unique indexes on `products.sku` and `inventory_sources.code` with `(tenant_id, sku)` and `(tenant_id, code)`.

The explicit list of tenant-owned models is `config('saas.tenant_owned_models')`. Platform-owned data stays outside that list: plans, subscriptions, billing records, payment transactions, provisioning operations, usage metrics, audit logs, platform settings, tenant records, tenant domains, domain challenges, invitations and Super Admin accounts.

### Tenant context

`Webkul\Saas\Services\TenantContext` is a request/job-scoped singleton holding the active tenant and the source that resolved it. It is activated in four places:

- `ResolveTenantFromHost`, prepended to the `shop` middleware group, resolves the storefront host.
- `SetTenantContextForAdmin`, appended to the global `web` group, activates the tenant of the signed-in admin after route binding.
- `ResolveTenantForApi` activates the tenant attached to the Sanctum token.
- `TenantContext::run()` wraps provisioning and usage collection so background work has a scoped context and the previous context is restored afterwards.

`TenantContext::requireTenant()` is the deny-by-default accessor. Code that needs a tenant must use it rather than reading a request parameter.

### Isolation layers

Tenant isolation is deliberately implemented in more than one layer:

1. `TenantScope` adds `where tenant_id = <active tenant>` to every query on a tenant-owned model while a context is active. It does nothing when no context is active, so platform commands and migrations still see all rows.
2. Model event hooks on the same models assign the active tenant to new rows and reject a forged `tenant_id` with `CrossTenantAccessException`. They also verify ownership on update and delete.
3. Services that mutate tenant data validate ownership themselves: domain verification and primary changes, billing records, subscription changes, team membership changes and exports. Requests never supply the tenant used for authorization.
4. `TenantPolicy` and the tenant panel controllers check membership, role and status server-side. The ACL map in `src/Config/acl.php` means the Bagisto admin panel refuses any SaaS route that is not explicitly declared.

Global scopes alone are not treated as authorization. Every cross-tenant identifier in a route is re-checked against the active tenant before the record is used.

### Package conventions

The package follows the Bagisto conventions: a Contract, Model, Proxy and Repository for every entity, repositories extend `Webkul\Core\Eloquent\Repository`, migrations live in `src/Database/Migrations`, providers and route groups are registered by `SaasServiceProvider`, and views are namespaced `saas::`.

## Tenant resolution and domains

`TenantResolver` normalises the request host before it becomes a query key: it strips schemes, paths and ports, lowercases the value, validates hostname or IP syntax, and converts IDNs to ASCII when `intl` is available.

Resolution order for a storefront request:

1. If `SAAS_ENABLED=false`, no tenant is resolved.
2. If the host matches `SAAS_PLATFORM_DOMAIN`, `www.` plus that domain, or any `SAAS_PLATFORM_HOSTS` entry, the request is a platform request and is never served as a storefront.
3. The host is looked up in `saas_tenant_domains`. A successful or negative result is cached for `SAAS_CACHE_TTL` seconds (successful) or 60 seconds (negative).
4. The domain must be `verified`. Its tenant must be `active`. The current subscription, when present, must be trialing, active or past due.
5. The tenant is activated and its channel becomes the current Bagisto channel.

Suspended, cancelled, unverified, unknown and platform hosts all return a non-disclosing error. The cache is invalidated by `TenantResolver::forgetHost()`, `forgetForTenant()` and the domain service whenever a domain or tenant changes.

### Platform subdomain

Provisioning registers `{slug}.{SAAS_DOMAIN_SUFFIX}` as a `platform_subdomain`. Platform subdomains are auto-verified when `saas.domains.auto_verify_platform_subdomains` is true (the default); no DNS challenge is issued for them. Reserved names such as `admin`, `api`, `billing`, `mail`, `www`, `super-admin` and `test` are rejected by slug validation.

### Custom domain

A tenant administrator can add a customer-owned hostname when the plan includes `custom_domain`, the feature flag `SAAS_ALLOW_CUSTOM_DOMAINS` is true, the hostname is valid, not an IP address, not a platform host, not a platform subdomain, and not already used by another tenant.

Adding a domain creates a pending record and a DNS TXT challenge. The expected record is:

```text
<hostname>  TXT  "saas-verification=<token>"
```

The challenge has a 24-hour TTL by default. `DomainService::verify()` reads the challenge through the `DomainOwnershipVerifier` contract, records the attempt and timestamp, and marks the domain verified only when the DNS value matches. Challenges are limited to 20 attempts; an expired challenge can be reissued. The scheduled command `saas:verify-domains` checks every pending custom domain, and the Super Admin and tenant panels can trigger a single check.

A verified custom domain can become the tenant's primary hostname. Removing the last verified domain is refused; removing the primary domain promotes another verified domain when one exists and updates the tenant channel hostname.

### SSL

Domain verification and SSL issuance are separate states. `saas_tenant_domains.ssl_status` starts at `pending` and is only set to `issued` by `DomainService::markSslIssued()`, which requires the domain to be verified. This package does not call an ACME client and does not mark SSL as issued merely because DNS verification succeeded. Terminating TLS for custom domains is an edge/proxy responsibility until an ACME integration is added.

## Registration and activation

Public registration is served only on the platform host at `/saas/register`. The form is available when registration is enabled. The write route is protected by the `saas-registration` limiter: five attempts per hour per IP by default.

Validation requires a store name, a slug matching `saas.tenants.slug_regex`, owner name and email, a password of at least 12 characters with confirmation, a plan, and accepted terms. Slugs must be unique and cannot be reserved. Duplicate owner emails are rejected against both Super Admin and tenant admin accounts. Passwords are hashed; the plaintext value never reaches the audit log.

Registration creates, in one transaction:

- a `pending` tenant with the selected plan, timezone, locale, currency and initial settings;
- a restricted tenant role;
- the owner `Admin` account with `tenant_id` set;
- the owner `TenantUser` membership with role `owner` and status `active`.

The session records the pending tenant UUID. Depending on the settings:

- When email verification is required, a signed verification URL valid for 48 hours is mailed to the owner account. The verification endpoint is signed and marks the tenant's `email_verified_at` and the owner membership's `email_verified_at`. Resending is enumeration-safe: the response is the same whether or not the address exists, and the route is rate limited.
- When approval is required, the tenant stays pending until a Super Admin approves it.
- When both requirements are satisfied, provisioning is dispatched immediately.

The activation policy is explicit: a tenant is only set to `active` by the final provisioning step after every provisioning requirement has succeeded, and only if the configured verification and approval requirements are met. A tenant that is unverified or unapproved stays `pending` with the provisioning operation step `awaiting_requirements`; it is never silently marked active. If an operator turns off both requirements, a tenant can activate without either.

## Provisioning

`ProvisioningService::provision()` runs under `TenantContext::run()` and a single database transaction. It records a `saas_provisioning_operations` row for every attempt and is idempotent: repeating a completed provisioning for an active tenant returns the latest completed operation without creating duplicates.

The steps, in order, are:

| Step | What it ensures |
|---|---|
| `plan` | The tenant has a plan; falls back to the configured default plan. |
| `role` | The restricted tenant role exists with the ACL keys in `saas.tenant_role_permissions`. |
| `owner` | The owner admin exists, has the tenant role, and has `tenant_id` set. |
| `membership` | The owner membership exists and is active. |
| `inventory` | A tenant inventory source exists with a tenant-specific code. |
| `root_category` | A root category exists for the tenant. |
| `domain` | The `{slug}.{suffix}` platform subdomain is registered and verified. |
| `channel` | A Bagisto channel exists, linked to the tenant's category, inventory source and hostname. |
| `finalize` | The subscription is started and the tenant becomes active when requirements are met. |

The tenant record is updated with `channel_id`, `owner_admin_id`, `plan_id` and default settings. Activation sends `TenantApprovedMail`.

Failures roll back the transaction, mark the operation `failed`, persist the failing step and error, and clear the tenant context. The `ProvisionTenant` job retries three times with 60, 300 and 900 second backoff. A Super Admin can retry from `/super-admin/provisioning` or run `saas:provision-tenant`, and the failure is captured in the audit log. A partially provisioned tenant is never marked active.

## Tenant lifecycle

`TenantStatus` has `pending`, `provisioning`, `active`, `suspended` and `cancelled`.

- Pending → provisioning or cancelled.
- Provisioning → active, pending (requirements not met) or cancelled.
- Active → suspended or cancelled.
- Suspended → active or cancelled.
- Cancelled is terminal.

Suspending a tenant sets the suspension reason and timestamp, suspends the current subscription when it still grants access, invalidates the resolution cache, and logs the action. The tenant admin middleware logs out an admin whose tenant is suspended or cancelled on the next request. Reactivating restores `active` and resumes a suspended subscription. Cancelling a tenant cancels its subscription and disables every domain; the storefront stops resolving. Deleting requires typing the tenant slug, cancels the tenant if needed, disables domains and soft-deletes the tenant, preserving billing history.

## Plans, entitlements and usage

Plans store a name, slug, description, price, currency, billing interval (`monthly`, `quarterly`, `yearly`), trial days, numeric limits, boolean features, visibility, active flag and sort order. The default plans are Free, Starter and Growth. A plan that has subscribers cannot be deleted; the Super Admin panel refuses the delete and explains why. Plans can be previewed before publishing.

Feature keys are:

| Key | Type | Enforced where |
|---|---|---|
| `max_products` | quota | Product model creation event and API/service assertions. |
| `max_staff` | quota | Membership creation event and invitation acceptance. |
| `monthly_orders` | quota | Order model creation event. |
| `storage_mb` | quota | Measured and reported; see limitations. |
| `custom_domain` | boolean | Custom domain creation. |
| `advanced_analytics` | boolean | Entitlement summary; no separate analytics product is implemented. |
| `api_access` | boolean | `saas.feature:api_access` middleware on API product and order routes. |
| `premium_support` | boolean | Entitlement summary and plan display. |

A stored `saas_tenant_entitlements` override takes precedence over the plan for a specific tenant, which is what a contract or support exception uses. A limit of `null` means unlimited and `0` means unavailable. `SAAS_QUOTA_GRACE_PERCENT` can allow a small numeric overage; the default is zero.

Usage is measured live where possible: products, customers and current-month orders are counted through tenant-scoped repositories; staff is counted from active memberships; storage is estimated from product image paths (up to 5,000 paths per collection). `saas:collect-usage` persists a snapshot in `saas_usage_metrics`. Downgrading a plan never deletes data: an over-quota tenant keeps its existing rows and new creations are refused until usage falls under the new limit.

## Subscriptions and billing

A subscription belongs to one tenant and one plan and keeps its full history. Statuses are `trialing`, `active`, `past_due`, `suspended`, `cancelled` and `expired`.

Starting a subscription:

- A free plan starts `active`.
- A paid plan starts `trialing` when it has trial days, otherwise `active`.
- Trial and period dates are recorded; `provider` is set only for paid subscriptions.
- Changing a plan preserves the subscription and appends `from`, `to` and `changed_at` to `metadata.plan_history`, then updates the tenant's plan.
- Extending a trial requires a reason and is recorded in the audit log.

`saas:sync-subscriptions` runs hourly and performs three operations:

1. `processLifecycle()` moves an expired trial to `active` for a free plan or `past_due` for a paid plan; moves an active subscription whose paid period ended to `past_due` (or cancels it when `cancel_at_period_end` is set); and suspends a `past_due` subscription when its grace period ends.
2. `invoiceDuePeriods()` creates an invoice for every elapsed period of an active or past-due paid subscription, advances the period and counts only newly created invoices. It is safe to run repeatedly; an existing invoice for the same subscription and period is reused.
3. `markOverdueInvoices()` flags open invoices past `due_at` as `past_due` and moves their subscriptions to `past_due`.

Recording a payment is deliberately manual in this release. The `manual` provider accepts a payment only when the operator confirms it and supplies a non-empty reference (at most 191 characters). The service checks the currency, requires the amount to be positive and no greater than the invoice balance, rejects invoices that are already paid, void or refunded, and de-duplicates by provider reference. A failed provider result is recorded as a failed transaction and surfaced as a validation error. A successful payment updates `amount_paid`, marks the invoice paid in full when appropriate, and resumes a past-due or suspended subscription. The payment receipt mail is sent after the transaction is committed.

The invoice amount is the plan price for the period. There is no proration, tax engine, credit note, refund or gateway webhook handling in this release. `mrr_is_estimate` is true: the dashboard labels the figure as an estimate derived from active subscription plan prices, not as recognised revenue. Collected revenue is calculated from succeeded payment transactions.

### Integrating a real provider

`Webkul\Saas\Contracts\BillingProvider` is the integration point. Implement `name()`, `isConfigured()`, `createInvoice()`, `charge()` and `refund()`, bind the implementation in `SaasServiceProvider`, and set `SAAS_BILLING_PROVIDER` to its identifier. Until an adapter is registered, any identifier other than `manual` resolves to `UnconfiguredBillingProvider`, which fails every operation explicitly instead of pretending a charge happened. A real adapter must verify provider webhook signatures, protect against replay, verify amounts and currencies against the stored invoice, and be idempotent on provider transaction identifiers.

## Super Admin panel

The panel is isolated from the tenant admin guard:

- URL prefix: `/super-admin` by default.
- Guard: `super_admin`, backed by the `saas_super_admins` table and its own password reset table.
- Middleware: `saas.platform` (platform host only) and `saas.super-admin` (guard), plus an ability check per route.
- Roles: `owner` and `admin` hold every platform ability; `support` is read-only for tenants, audit and dashboard. The console command never sets a default password.

Pages:

| Page | Capability |
|---|---|
| Dashboard | Tenant status counts, active, trialing and past-due subscriptions, estimated MRR, collected revenue this month, registrations this month, plan distribution, failed provisioning operations and recent audit entries. |
| Tenants | List, create, inspect, update, approve, activate, suspend, reactivate, cancel, soft delete, export and retry provisioning. |
| Plans | List, create, edit, preview and delete plans that have no subscribers. |
| Subscriptions | List and inspect subscriptions, extend trials, change plans, cancel and reactivate, all with audit records. |
| Billing | List and inspect invoices, record an offline payment and process overdue invoices. |
| Domains | Inspect every tenant domain, verify a custom domain and promote it to primary. |
| Provisioning | Inspect operations, including failed steps and attempts, and retry. |
| Settings | Manage platform name, support email, registration, approval and verification switches, trial days, default plan, maintenance message, data retention days and default product and staff limits. |
| Audit | Inspect the platform and tenant audit trail with filters. |

The dashboard is built from live database queries, not static data. Every mutation writes an audit entry through `AuditLogger`.

## Tenant admin panel

Tenant administrators use the normal Bagisto admin guard. The SaaS routes live under `/{APP_ADMIN_URL}/saas` and require `web`, `admin`, `NoCacheMiddleware` and `saas.tenant.admin`. `SetTenantContextForAdmin` resolves the tenant from the signed-in admin, validates the tenant status and the active membership, logs out suspended or cancelled tenants, and shares the tenant with the views.

Pages: dashboard with onboarding checklist and usage, team management, domains, billing, usage and tenant settings. The onboarding checklist derives domain and product completion automatically and stores operator-confirmed profile, branding, payment, shipping and publish flags in the tenant's `settings.onboarding`.

Team management invites admins by email. Invitations are stored with a SHA-256 hash of the token, expire, are single-use and are accepted at `/saas/invitation/{token}` with a password of at least 12 characters. Acceptance checks the staff quota and the tenant's active status, creates the admin account with `tenant_id`, creates the membership and records the join in the audit log. Update and delete operations check that the membership belongs to the active tenant; the owner cannot be removed through the generic membership path.

The tenant role only receives the ACL keys listed in `saas.tenant_role_permissions` plus the SaaS pages. Platform-wide settings, channels, inventory sources and the platform user and role screens are deliberately excluded.

## Storefront and commerce

`ResolveTenantFromHost` is prepended to the `shop` middleware group, so the tenant is known before Bagisto's theme, locale and currency middleware run. Activation sets the tenant channel as the current channel. Products, categories, customers, carts, orders, invoices, shipments, refunds, transactions, inventory sources, pages, cart rules, catalog rules, wishlists and reviews are scoped to the tenant for the rest of the request.

Storefront URLs use the primary verified domain, or the first verified domain when no primary is set. A storefront request for a suspended, cancelled, pending or unverified tenant is rejected with a generic unavailable/not-found message.

## Versioned API

The API is mounted at `/api/v1/saas` with `api`, `auth:sanctum`, `saas.api.tenant` and the `saas-api` limiter (60 requests per minute, keyed per tenant).

`ResolveTenantForApi` never reads a tenant from the payload. It takes the authenticated Sanctum admin, requires `tenant_id`, requires an active tenant and an active membership, activates the tenant and clears the context in a `finally` block.

| Method | Path | Description |
|---|---|---|
| GET | `/tenant` | UUID, name, slug, status, locale, currency, timezone, plan, current subscription state, verified domains and storefront URL. |
| GET | `/tenant/usage` | Persisted usage snapshot and entitlement summary. |
| GET | `/products` | Tenant products, paginated. `per_page` is clamped to 1–100. Requires the `api_access` entitlement. |
| GET | `/orders` | Tenant orders, paginated. `per_page` is clamped to 1–100. Requires the `api_access` entitlement. |

A token that is not attached to a tenant receives `403`; an unauthenticated request receives `401`. Other tenants' records are unreachable because the repositories are tenant-scoped and the active tenant comes from the token.

## Security model

- **Authorization is server-side.** Middleware, policies and services enforce tenant, membership, role, plan and super-admin abilities. Routes are deny-by-default in the admin ACL map.
- **The request never chooses the tenant.** Hosts, admin accounts and Sanctum tokens are the only sources of tenant identity. A submitted `tenant_id` cannot override the active context; attempts to create or mutate another tenant's row raise `CrossTenantAccessException`.
- **Cross-tenant identifiers return 404.** Domain, membership, invitation and billing lookups check ownership before use.
- **No information leakage.** Unknown, unverified and suspended hosts share non-disclosing messages.
- **CSRF and signed URLs.** Normal POST forms use Laravel's CSRF protection. Email verification uses temporary signed routes. Invitation tokens are random, stored hashed and rate limited on acceptance.
- **Rate limits.** Registration, resend, team invitations, domain verification, invitation acceptance, Super Admin login and the API are rate limited.
- **Audit redaction.** `AuditLogger` drops passwords, secrets, API keys, tokens, card data and similar keys from metadata recursively. It records the acting super admin, tenant admin or system actor resolved from the guard, never from the request body.
- **Secrets stay out of the database and source control.** Non-secret settings are stored in `saas_platform_settings`; the settings service supports encrypted values for future secrets. No default Super Admin password exists.
- **Exports are private.** Tenant exports are written to the private disk configured by `SAAS_EXPORT_DISK`, under a tenant UUID and timestamp directory, with a manifest containing per-file SHA-256 hashes. Downloads are not exposed as public URLs.
- **Tenant context is cleared.** Middleware and jobs clear the context in `finally` blocks so a worker cannot leak one tenant's context into another job.

## Commands and schedule

| Command | Purpose |
|---|---|
| `php artisan saas:install` | Seed the default plans and platform settings without overwriting existing records. |
| `php artisan saas:create-super-admin` | Create a Super Admin through hidden prompts; supports `--name`, `--email`, `--role`. |
| `php artisan saas:provision-tenant {tenant?} [--all] [--sync]` | Queue or synchronously provision a tenant by ID, UUID or slug, or every eligible tenant. |
| `php artisan saas:verify-domains [--domain=]` | Check pending custom-domain DNS challenges. It never marks SSL as issued. |
| `php artisan saas:sync-subscriptions` | Advance trial and period lifecycles, create invoices for elapsed periods, flag overdue invoices. |
| `php artisan saas:collect-usage [--tenant=]` | Recalculate and persist usage metrics for one tenant or all active tenants. |
| `php artisan saas:send-trial-warnings [--dry-run]` | Queue one reminder per configured trial threshold (7, 3 and 1 days by default). |
| `php artisan saas:bootstrap-tenant {tenant} [--channel=] [--force]` | Assign legacy rows with `tenant_id IS NULL` to a tenant; rows owned by another tenant are never touched. |

The service provider schedules:

- `saas:sync-subscriptions` hourly.
- `saas:collect-usage` daily.
- `saas:verify-domains` every six hours.
- `saas:send-trial-warnings` daily.

## Notifications

Mail classes cover owner verification, approval/activation, domain verification, invitations, recorded payments and trial-ending reminders. All of them use `SAAS_MAIL_FROM` as the envelope sender. The trial reminder is queued on `SAAS_NOTIFICATION_QUEUE`; the other notifications are sent inline because they confirm an action the operator has just completed. Notification failures are logged and do not fail the request or provisioning; critical state is persisted first. Configure a real mailer and queue worker in production.

## Legacy data and migrations

The `tenant_id` columns are nullable so an existing Bagisto database can be migrated without assigning rows immediately. `saas:bootstrap-tenant` reports the unassigned row counts, asks for confirmation (unless `--force`), and updates only rows where `tenant_id IS NULL` inside a transaction. It can also attach an existing channel to the tenant with `--channel=`.

Bootstrap is intentionally conservative: it does not guess ownership, it does not rewrite rows that already belong to a tenant, and it records the number of rows and models in the audit log. Review the counts before confirming. If an existing installation had customer, product or order data spanning what will become multiple tenants, split that data explicitly before running the command.

The migration also changes the unique indexes on `products.sku` and `inventory_sources.code` to be per tenant. Review any other global unique constraints in a customised installation (for example `admins.email`) before onboarding tenants that need to reuse an address.

## Testing

The package has Pest suites for feature and unit tests:

```bash
vendor/bin/pest --testsuite="Saas Feature Test"
vendor/bin/pest --testsuite="Saas Unit Test"
vendor/bin/pest packages/Webkul/Saas/tests/Feature
vendor/bin/pint --dirty
```

Feature tests cover registration and verification, provisioning success, idempotency and rollback, tenant isolation, cross-tenant domain rejection, super-admin versus tenant-panel separation, suspended tenants, API tenant resolution, subscription lifecycle and billing, and the console commands. `SaasTestCase` extends the Bagisto admin test bench and clears the tenant context around every test.

When a seeder changes, rebuild the parallel test databases with `php artisan test --parallel --recreate-databases`; otherwise workers keep the old baseline.

## Known limitations

- **Manual billing only.** No payment gateway, recurring charge, webhook verification, proration, tax, credit note or refund is implemented. `saas:sync-subscriptions` creates invoices but does not charge them. The manual adapter records payments an operator confirms, with a reference; it never fabricates a success.
- **MRR is an estimate.** It is derived from active subscription plan prices and labelled as an estimate in the panel. It is not recognised revenue.
- **SSL is not automated.** Verification proves DNS ownership only. `ssl_status` stays `pending` until `DomainService::markSslIssued()` is called, and no ACME client is bundled. Terminate TLS at the edge or add an ACME integration.
- **Domain verification is DNS TXT only.** The HTTP verifier is represented by the `DomainOwnershipVerifier` contract but only `DnsOwnershipVerifier` is bound. Verification depends on public DNS propagation and a resolvable TXT record.
- **Storage quota is measured, not enforced on upload.** Uploads are not blocked when `storage_mb` is exceeded; the quota appears in usage and entitlements. Add a server-side upload guard if hard enforcement is required.
- **Advanced analytics and premium support are entitlement flags**, not implemented products or support workflows.
- **Retention settings are documented policy values.** `data_retention_days` and `soft_delete_retention_days` are stored and displayed, but no scheduled purge deletes data. Soft-deleted tenants and their billing history remain until an operator removes them.
- **Core Bagisto queued jobs are not retrofitted.** This package clears and scopes its own jobs, but third-party or core jobs that assume a tenant must carry `tenant_id` or run inside `TenantContext::run()`. Audit any asynchronous integration added to a tenant.
- **Admin email addresses are globally unique** because `admins.email` remains a global unique index in Bagisto. A person cannot be an admin of two tenants with the same address; use a distinct address per tenant.
- **Customer identity is scoped by tenant and channel.** Bagisto's customer unique constraint is `(email, channel_id)`. Each tenant has its own channel, so the same customer email can exist in different tenants; a session started on one tenant cannot read another tenant's rows because the customer model is tenant-scoped.
- **Non-English locale files currently mirror English.** All 23 locale files exist and pass key-parity checks, but the translations are English placeholders until translated.
- **`bagisto:translations:check` is broken on Windows** because its path stripping assumes `/` separators. This is a pre-existing Core command bug, not a SaaS issue; verify SaaS keys directly or run the checker on a POSIX system.
- **API surface is read-only and limited** to the tenant resource, usage, products and orders. There is no write API, no webhook API and no GraphQL schema in this release.
- **No automated customer-facing billing portal.** Tenants can see invoices and usage in the tenant panel, but plan changes, trial extensions and payments are operator actions in the Super Admin panel.
