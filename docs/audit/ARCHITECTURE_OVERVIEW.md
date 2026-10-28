# Bagisto SaaS — Architecture Overview

Audit date: 2026-10-28. Branch: `super-admin-rasha` (based on `2.5`). This document describes the system as verified in the repository; it is not a design proposal. Where a component is only partially implemented, the gap is stated here and tracked in [FULL_APPLICATION_AUDIT.md](FULL_APPLICATION_AUDIT.md).

## 1. System at a glance

The application is a Bagisto 2.5 storefront and admin panel extended with a first-party multi-tenant layer in `packages/Webkul/Saas`. One deployment serves three surfaces:

| Surface | Host | URL prefix | Guard | Database model |
|---|---|---|---|---|
| Platform marketing site | `SAAS_PLATFORM_DOMAIN` and `SAAS_PLATFORM_HOSTS` | `/` (marketing routes), `/saas/register`, `/contact` | none | Reads plans and contact inquiries |
| Super Admin panel | platform host only | `/super-admin` | `super_admin` | Cross-tenant, unscoped |
| Tenant storefront | Each verified tenant hostname | `/` | `customer` | Scoped by `tenant_id` |
| Tenant admin panel | Tenant hostnames and platform host | `/{APP_ADMIN_URL}/saas` | `admin` | Scoped by `tenant_id` |
| Tenant invitation acceptance | Any host | `/saas/invitation` | none / `admin` | Scoped to the invitation |
| Tenant API | Tenant hostnames | `/api/v1/saas` | `sanctum` | Scoped by `tenant_id` |

All tenants share one database and one schema. Isolation is enforced in application code — a global Eloquent scope, model event guards, middleware, ACL, and (since this audit) a DataGrid query listener. It is not database-level row security, so any query path that bypasses Eloquent, repositories, and DataGrid hooks is a potential isolation hole; see finding SAAS-01.

## 2. Runtime and dependencies (verified)

- PHP 8.4.27, Laravel 13.36.0, Bagisto 2.5.x.
- MySQL is the development database (`bagisto_db`). The codebase contract still requires every migration and raw fragment to run on MySQL 8.0, MariaDB 10.11 and PostgreSQL 16; CI covers all three.
- Pest 5 / PHPUnit 13, Pint (Laravel preset), Vite 6, Tailwind CSS 4, Vue 3, Node 22.15.0.
- Cache, queue and session drivers are environment-controlled. Tenant host resolution is cached through `config('saas.cache.store')` with an array fallback when the configured store is unavailable (`TenantResolver::cache()`).

## 3. Package layout

```
packages/Webkul/Saas/
├── src/
│   ├── Config/           saas.php, marketing.php, acl.php, menu.php
│   ├── Console/          InstallCommand, ProvisionTenantCommand, BootstrapTenantCommand,
│   │                     SyncSubscriptionsCommand, CollectUsageCommand, VerifyDomainsCommand,
│   │                     SendTrialWarningsCommand, CreateSuperAdminCommand
│   ├── Contracts/        Plan, Tenant, TenantDomain, TenantUser, Subscription, Invoice, …
│   ├── Database/         18 migrations, factories
│   ├── Enums/            TenantStatus, SubscriptionStatus, MembershipRole, FeatureKey, …
│   ├── Exceptions/       CrossTenantAccessException, TenantResolutionException, …
│   ├── Http/
│   │   ├── Controllers/  Marketing, Platform, SuperAdmin, Tenant, Api, Invitation
│   │   ├── Middleware/   ResolveTenantFromHost, SetTenantContextForAdmin, ResolveTenantForApi,
│   │   │                 AuthenticateSuperAdmin, EnsureSuperAdminAbility, EnsureTenantAdminContext,
│   │   │                 EnsurePlatformHost, EnsureFeature, SetMarketingLocale
│   │   ├── Requests/     Form requests for registration, plans, tenants, team, domains
│   │   └── Resources/    API resources
│   ├── Jobs/             Provisioning and notification jobs
│   ├── Listeners/        ScopeDataGridToTenant (added during this audit)
│   ├── Models/           Eloquent models; Proxies live beside Contracts
│   ├── Policies/         TenantPolicy
│   ├── Repositories/     Tenant, Plan, Subscription, Domain, Membership, Billing, …
│   ├── Resources/views/  marketing, super-admin, tenant, invitation, emails, layouts
│   ├── Routes/           marketing.php, platform.php, super-admin.php, tenant.php,
│   │                     invitation.php, api.php
│   ├── Scopes/           TenantScope
│   └── Services/         TenantResolver, TenantContext, TenantManager, ProvisioningService,
│                         DomainService, SubscriptionService, BillingService, EntitlementService,
│                         TenantRoleService, UsageService, AuditLogger, Billing/*, …
└── tests/                12 test files (Feature + Unit), SaasTestCase
```

Wiring: `SaasServiceProvider` is registered in `bootstrap/providers.php`; its Concord `ModuleServiceProvider` is registered in `config/concord.php`; PSR-4 autoload entries exist in `composer.json`. The package's tests are declared as `Saas Feature Test` and `Saas Unit Test` suites in `phpunit.xml` and bound to `SaasTestCase` in `tests/Pest.php`.

## 4. Tenancy model

**Identity.** Every tenant-owned row carries a nullable, indexed `tenant_id`. The original commerce tables were altered by `2026_10_27_101300_add_tenant_id_to_commerce_tables.php`; the derived tables (bookings, EU withdrawals, GDPR requests, RMA) were added by `2026_10_28_100000_add_tenant_id_to_derived_commerce_tables.php`.

**Context.** `TenantContext` is a container singleton holding the active `Tenant` and the source (`host`, `admin`, `api`, `platform`). It is cleared at the end of each request.

**Resolution.**
- Storefront: `ResolveTenantFromHost` is prepended to the `shop` middleware group. It skips platform-panel paths, rejects platform hosts from storefront routes, resolves the request host through `TenantResolver`, isolates the customer session when the tenant changes, activates the tenant and its channel, and clears the context in a `finally` block.
- Admin: `SetTenantContextForAdmin` is appended to the `web` group. For an authenticated admin with `tenant_id`, it loads the tenant, rejects suspended, cancelled and non-active tenants, verifies an active `saas_tenant_users` membership, activates the tenant and records `last_active_at` at most every five minutes.
- API: `ResolveTenantForApi` derives the tenant from the Sanctum user and rejects requests whose host or token does not match.

**Enforcement.**
- `TenantScope` is attached as a global scope to every class in `config('saas.tenant_owned_models')`.
- `creating` assigns `tenant_id` from the context or refuses a conflicting value; `updating` and `deleting` refuse rows owned by another tenant. Both throw `CrossTenantAccessException`, which renders JSON or HTML `403` rather than a `500` (fixed during this audit).
- DataGrids in Bagisto build raw `DB::table()` queries and therefore bypass global scopes. `ScopeDataGridToTenant` listens to `datagrid.*.query_builder.prepare.after` and adds a `tenant_id` predicate (or a join-based predicate for `product_flat` and `cart_rule_coupons`). This was the most serious isolation gap found and is now covered by regression tests.

**Session isolation.** The storefront cookie is shared across hosts. `ResolveTenantFromHost::isolateSession()` logs out the customer guard and forgets `cart`, `coupon_code`, `locale`, `currency` and `url.intended` when the resolved tenant changes, then stores `saas_tenant_id`. Admin sessions use Bagisto's own guard and tenant context is rebuilt per request from `admins.tenant_id`.

## 5. Request lifecycle

### Storefront

1. Global middleware; `ResolveTenantFromHost` runs first in the `shop` group.
2. Platform-panel paths are passed through untouched.
3. Platform hosts are refused on storefront routes (`404`).
4. Host normalisation rejects malformed values and internationalises IDNs.
5. `TenantResolver` consults the cache, then `saas_tenant_domains.hostname`; the domain must be verified, the tenant active, and the current subscription trialing, active or past due.
6. The tenant and its channel are activated, `saasTenant` is shared with views, and the session is isolated if the tenant changed.
7. Bagisto's theme, locale and currency middleware then run inside the tenant channel.

Unknown hosts now always fail (`404`, or `503` when the tenant is suspended) even when the database contains no tenants. The previous fail-open fallback to the default storefront was removed because it silently served another tenant's catalogue on a mistyped or unconfigured domain.

### Admin

`web` → `SetTenantContextForAdmin` → Bagisto `admin` middleware → `EnsureTenantAdminContext` on `/admin/saas` routes. An admin without a tenant can still use the platform admin panel but cannot reach tenant SaaS screens.

### Super Admin

`/super-admin` routes are protected by `AuthenticateSuperAdmin` (an isolated `super_admin` guard with its own provider) and per-route `EnsureSuperAdminAbility` (`tenants.view`, `tenants.manage`, `plans.view`, `plans.manage`, `subscriptions.view`, `subscriptions.manage`, `billing.view`, `billing.manage`, `settings.manage`, `audit.view`). Because the guard name differs, Laravel stores its session under a separate `login_super_admin_*` key, so a storefront or tenant-admin login never grants platform access. `config('saas.super_admin_session_key')` is defined but not referenced; the isolation comes from the guard name, not that setting (see SAAS-20).

## 6. Routing surface

- **Marketing** (`Routes/marketing.php`, registered during provider *register* for every platform host, with `web`, `saas.platform`, `saas.marketing.locale`): `/`, `/features`, `/solutions`, `/pricing`, `/resources`, `/about`, `/contact`, `/privacy`, `/terms`, `/refund-policy`, `/merchant-login`, locale switch, sitemap and robots. Registering before the storefront routes is deliberate: the storefront owns a catch-all on `/` and would otherwise swallow the platform homepage.
- **Registration** (`Routes/platform.php`, prefix `/saas`): plan listing, registration form and submission, email verification, status page.
- **Super Admin** (`Routes/super-admin.php`, prefix `/super-admin`): dashboard, tenants (list, create, show, edit, plan assignment, approve, activate, suspend, reactivate, cancel, delete, export, provision), cross-tenant orders/products/customers, plans, subscriptions, billing and manual payments, domains, provisioning operations, settings, audit log, insights, CMS and configure.
- **Tenant panel** (`Routes/tenant.php`, prefix `/{APP_ADMIN_URL}/saas`): dashboard, settings, team and invitations, domains, billing, usage, onboarding.
- **Invitations** (`Routes/invitation.php`): acceptance flow.
- **API** (`Routes/api.php`, prefix `api/v1/saas`): tenant profile, products index, orders index, protected by Sanctum, tenant resolution and `throttle:saas-api`.

Rate limiters: `saas-registration` (5/hour per IP), `saas-api` (60/minute per tenant or IP), `saas-contact` (5/hour per IP), plus route-level throttles on login and invitation resend.

## 7. Domain and provisioning lifecycle

- A tenant always receives a deterministic platform hostname, `{slug}.{saas.domains.suffix}`, registered as a platform domain (`DomainService::registerPlatformDomain`, `platformHostFor`).
- Custom domains require a TXT challenge (`saas_domain_challenges`, `DnsOwnershipVerifier`), are rate-limited, and are verified by `saas:verify-domains` every six hours or on demand.
- Reserved subdomains are rejected (`saas.domains.reserved_subdomains`), and slug changes re-check availability and re-register the platform host (`TenantManager::update`, fixed during this audit).
- `ProvisioningService` creates the tenant channel, admin role, owner membership and initial subscription; operations are recorded in `saas_provisioning_operations` and can be retried from the Super Admin panel.
- Resolver caches are invalidated per hostname and per tenant when a domain or tenant changes.

## 8. Identity, roles and entitlements

- **Customers** are tenant-owned. A customer registered on one storefront cannot authenticate on another because the session is isolated and the customer row is tenant-scoped.
- **Tenant admins** are ordinary Bagisto admins with `tenant_id`, a tenant-bound role and an active membership. `config('saas.tenant_role_permissions')` is the allow-list; it deliberately excludes platform settings, channels, inventory sources, users/roles and other global modules. Tenant roles are pruned on creation and by a data migration for existing roles.
- **Super Admins** live in `saas_super_admins` with an abilities array, independent of `admins`.
- **Entitlements** are resolved from the plan (`limits` and `features`) by `EntitlementService`. Quota guards fire on product, order and staff creation (`MaxProducts`, `MonthlyOrders`, `MaxStaff`). A `null` limit means unlimited; over-quota as a result of a downgrade blocks new creation without deleting data. Feature flags exist for `custom_domain`, `advanced_analytics`, `api_access` and `premium_support`; only the first and `api_access` are enforced in request paths today (see SAAS-14).

## 9. Billing and subscriptions

- `SubscriptionService` starts, changes, cancels, reactivates and expires subscriptions; `SyncSubscriptionsCommand` runs hourly; trial warnings run daily at 7, 3 and 1 days.
- `BillingService` issues invoices, records manual payments, marks invoices overdue and estimates MRR (documented as an estimate, `saas.billing.mrr_is_estimate`).
- `BillingProvider` is bound to `ManualBillingProvider` by default, with `UnconfiguredBillingProvider` as the fallback. There is no online payment gateway, no webhook endpoint, and no automated dunning. This is a deliberate MVP boundary but a production blocker for self-serve paid plans (SAAS-13).

## 10. Marketing site

The marketing site is server-rendered Blade under `Resources/views/marketing`, with its own layout, design tokens, components, 11 public pages, a locale switcher (23 locales, RTL for Arabic and Hebrew), a contact form persisting to `saas_contact_inquiries`, sitemap and robots endpoints. Pricing reads active public plans. It is host-constrained, so tenant hosts fall through to the storefront and platform hosts never render a storefront.

## 11. Background work

Scheduled in `SaasServiceProvider::registerSchedule()`: `saas:sync-subscriptions` hourly, `saas:collect-usage` daily, `saas:verify-domains` every six hours, `saas:send-trial-warnings` daily. Provisioning, notifications and exports run through queued jobs. The schedule assumes a running queue worker and a single scheduler host; deployment documentation must state both (see REMEDIATION_CHECKLIST).

## 12. Testing

- `phpunit.xml` declares separate suites per package, including `Saas Feature Test` and `Saas Unit Test`.
- SaaS tests extend `SaasTestCase` (which extends `AdminTestCase`) and use database transactions against the configured database. There is no `.env.testing` in the repository, so local runs use the development database. This is the root cause of three local failures caused by pre-existing seeded subscriptions (see audit finding SAAS-16); CI builds a fresh database per worker and is not affected by that data.
- `SaasTestCase::createApplication()` enables SaaS before the application boots, while the shared `phpunit.xml` sets `SAAS_ENABLED=false` so the single-store storefront suites keep their routing. Without this split, every Shop test that requests `localhost` would be treated as a platform host and return `404` (SAAS-15).
- Coverage is HTTP- and service-level. There is no browser end-to-end suite for the Super Admin or tenant panel; Playwright suites exist only for Admin, Shop and Installer.

## 13. Architectural constraints and residual risks

1. **Shared database, application-enforced isolation.** Every new raw query, report, export or console command must apply `tenant_id` explicitly. The DataGrid listener covers DataGrids, not arbitrary SQL.
2. **Global modules.** Attributes, attribute families, customer groups, channels, inventory sources, tax categories, email templates and CMS configuration are shared platform data. Tenant roles must not receive write access to them; the allow-list is the control.
3. **Channel assignment.** Products are attached to channels through `product_channels`. The tenant's channel is created by provisioning, but the assignment path does not verify channel ownership (SAAS-05).
4. **Host separation is a deployment requirement.** Platform and tenant hosts must be distinct; the platform host is refused on storefront routes, and unknown hosts fail closed.
5. **Manual billing.** No gateway, webhooks or automated collection.
6. **Reporting** aggregates across channels (`core()->getAllChannels()`) without tenant filtering (SAAS-12).
7. **Local test parity.** Development-database state can fail SaaS tests; a dedicated test database is recommended.
8. **Translations tooling** (`bagisto:translations:check`) is broken on Windows path handling; a parity script confirms the 23 locales are consistent today (SAAS-17).

## 14. Evidence index

- Provider and routing: `packages/Webkul/Saas/src/Providers/SaasServiceProvider.php`, `src/Routes/*.php`.
- Resolution and context: `src/Services/TenantResolver.php`, `src/Services/TenantContext.php`, `src/Http/Middleware/ResolveTenantFromHost.php`, `SetTenantContextForAdmin.php`, `ResolveTenantForApi.php`.
- Isolation: `src/Scopes/TenantScope.php`, `src/Listeners/ScopeDataGridToTenant.php`, `src/Exceptions/CrossTenantAccessException.php`, `src/Config/saas.php` (`tenant_owned_models`).
- Authorization: `src/Http/Middleware/AuthenticateSuperAdmin.php`, `EnsureSuperAdminAbility.php`, `EnsureTenantAdminContext.php`, `src/Policies/TenantPolicy.php`, `src/Services/TenantRoleService.php`.
- Provisioning and domains: `src/Services/ProvisioningService.php`, `DomainService.php`, `src/Console/Commands/ProvisionTenantCommand.php`, `VerifyDomainsCommand.php`.
- Billing: `src/Services/SubscriptionService.php`, `BillingService.php`, `src/Services/Billing/*`, `src/Console/Commands/SyncSubscriptionsCommand.php`.
- Tests: `packages/Webkul/Saas/tests/`, `phpunit.xml`, `tests/Pest.php`.
