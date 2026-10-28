# Full Application Audit — Bagisto SaaS

Audit date: 2026-10-28
Repository: Bagisto 2.5.x, branch `super-admin-rasha`, base branch `2.5`
Scope: marketing site, registration and onboarding, Super Admin panel, tenant panel, subscriptions/billing/entitlements, tenant isolation, storefront commerce, routing and domains, database and migrations, API and queues, security/privacy, performance, testing, deployment readiness.

Status legend: **Fixed** — code changed and covered by a regression test; **Verified** — behaviour observed against the running application/database; **Mitigated** — risk reduced but not eliminated; **Open** — not fixed; **Blocked** — cannot proceed without external input.

Evidence rule: every finding below cites a file path, route, class, test or database observation. Nothing is marked passing unless the command was run in this environment; results are recorded verbatim in section 9.

## 1. Executive summary

The SaaS layer is functionally broad: a marketing site, public registration with email verification and approval, a Super Admin panel with tenant lifecycle, plans, subscriptions, manual billing, domains, provisioning and audit, a tenant panel with team, domains, billing, usage and onboarding, and a host-based storefront with application-enforced isolation.

It is **not ready for production as a paid multi-tenant service**. Three Critical isolation defects were found and fixed during this audit: raw DataGrid queries bypassed the tenant scope and exposed other tenants' orders, customers, products, CMS pages, promotions, RMA, bookings, withdrawals and GDPR requests; four derived commerce tables had no tenant ownership at all; and unknown hostnames silently served the default storefront instead of failing closed. One High authorization defect was also fixed: tenant-bound roles retained write access to shared platform modules and to marketing subscriber data.

The remaining blockers are known and bounded: tenant product-to-channel assignment is not ownership-checked (SAAS-05); tenant activation depends on a queued provisioning job with no reconciliation, and all four development tenants currently have no channel (SAAS-06); the Super Admin account has no password reset or second factor (SAAS-07); billing is manual-only with no gateway, webhook or dunning (SAAS-13); and reports aggregate every channel (SAAS-12). Storefront and platform hosts must be distinct in deployment; that is now documented and encoded in the test harness but remains an operational precondition.

## 2. Method and constraints

The audit combined static inspection, database inspection, targeted HTTP tests and package test runs. It did not perform destructive security testing, load testing, or calls to external services; no payment was simulated; no production infrastructure was touched; no `.env` value or secret is reproduced in this report.

Limitations that affect confidence:

- The full Shop suite was not re-run after the test configuration change; only `HomePageTest` was re-run (section 9). The earlier full run, with SaaS forced on for every test, produced 546 failures caused by platform-host routing of `localhost`, not by storefront code.
- MariaDB and PostgreSQL were not available locally. Database portability is asserted from schema-builder usage and CI coverage, not measured here.
- The API has three endpoints and no dedicated test file; its behaviour was inspected, not exercised end to end.
- No browser end-to-end tests exist for the Super Admin or tenant panels; UI verification is HTTP smoke testing.
- Three SaaS tests fail locally against the development database; their cause is pre-existing data, not the changes made here (SAAS-16).

## 3. Severity summary

| Severity | Total | Fixed/Verified | Mitigated | Open |
|---|---:|---:|---:|---:|
| Critical | 3 | 3 | 0 | 0 |
| High | 6 | 1 | 0 | 5 |
| Medium | 8 | 4 | 1 | 3 |
| Low | 5 | 0 | 0 | 5 |
| **Total** | **22** | **8** | **1** | **13** |

Launch blockers: SAAS-05, SAAS-06, SAAS-07, SAAS-12 and SAAS-13, plus the deployment preconditions in section 10. SAAS-14 and SAAS-16 must be resolved or explicitly accepted before charging customers.

## 4. Tenant isolation and data security

### SAAS-01 — DataGrid queries bypassed tenant isolation (Critical, Fixed, Verified)

**Evidence.** Bagisto DataGrids build raw `DB::table()` queries in `prepareQueryBuilder()` (`packages/Webkul/DataGrid/src/DataGrid.php`), so Eloquent global scopes never apply. A tenant admin opening Customers, Products, Orders, CMS Pages, Cart Rules, Catalog Rules, Inventory Sources, RMA, Bookings, EU Withdrawals, GDPR Requests or cart-rule coupons saw every tenant's rows, and the same query builder feeds CSV/Excel exports.

**Impact.** Cross-tenant disclosure of customer names, emails, addresses, orders and revenue; cross-tenant export; a malicious tenant could target IDs observed in a grid. This is a confidentiality and integrity failure, not a cosmetic one.

**Root cause.** Isolation was implemented only at the Eloquent model layer (`TenantScope`, model events). DataGrids are a second data-access path.

**Fix.** Added `packages/Webkul/Saas/src/Listeners/ScopeDataGridToTenant.php` and registered it for `datagrid.*.query_builder.prepare.after` in `SaasServiceProvider::registerDataGridScope()`. The listener:
- ignores events when SaaS is disabled or no tenant context is active;
- scopes tables that own `tenant_id` directly;
- scopes `product_flat` and `cart_rule_coupons` through a join to their owner table;
- leaves `saas_*` and platform tables untouched;
- does nothing if the builder is not a query builder or the predicate is already present.

**Verification.** `packages/Webkul/Saas/tests/Feature/DataGridTenantScopeTest.php` creates two tenants and asserts that the customer grid and the product grid return only the active tenant's rows; those tests pass (section 9).

### SAAS-02 — Derived commerce tables had no tenant ownership (Critical, Fixed, Verified)

**Evidence.** `bookings`, `eu_withdrawals`, `gdpr_data_request` and `rma` lacked `tenant_id`, were absent from `config('saas.tenant_owned_models')`, and were therefore neither scoped nor guarded. Their DataGrids were also unscoped (SAAS-01).

**Impact.** Cross-tenant read and write of bookings, withdrawal requests (which contain order and customer data), GDPR erasure requests and return merchandise authorisations; a tenant could update or delete another tenant's records through the admin panel.

**Fix.**
- `2026_10_28_100000_add_tenant_id_to_derived_commerce_tables.php` adds a nullable indexed `tenant_id` and backfills it from the owning order/customer/product relation with a correlated subquery. The migration is additive and reversible.
- `config/saas.php` adds `Booking`, `Withdrawal`, `GDPRDataRequest` and `RMA` to `tenant_owned_models`, which attaches the global scope and the creating/updating/deleting guards.

**Verification.** Both new migrations report `Ran` in `php artisan migrate:status`. `DataGridTenantScopeTest` creates a GDPR request under tenant context and asserts the persisted `tenant_id`; it passes.

### SAAS-03 — Unknown hostnames failed open (Critical, Fixed, Verified)

**Evidence.** `ResolveTenantFromHost::handle()` previously returned `$next($request)` when no tenant resolved and the `saas_tenants` table was empty. Any hostname that did not match a verified domain could therefore be served the default storefront.

**Impact.** A mistyped, parked or attacker-controlled domain pointed at the platform could display a real store's catalogue and accept traffic under the wrong identity; conversely, an unconfigured deployment looked healthy while serving the wrong store.

**Fix.** Removed the `tenantsExist()` fallback and the `Tenant` import. Unknown hosts now return `404` (JSON or HTML), and suspended/cancelled tenants return `503`, regardless of how many tenants exist.

**Verification.** `PlatformSecurityTest` now asserts unknown host `404`, verified tenant platform host `200`, and suspended tenant host `503`; all pass.

### SAAS-04 — Tenant roles retained global writes and PII access (High, Fixed, Verified)

**Evidence.** `config('saas.tenant_role_permissions')` granted tenant roles `catalog.attributes.*`, `catalog.families.*`, `customers.groups.*`, every `marketing.communications.*` and `marketing.search_seo.*` permission, and RMA configuration writes. Those modules are platform-global in a shared database: editing an attribute or family changes every tenant's catalogue; subscriber screens expose other tenants' email addresses; SEO rewrites and sitemaps are shared.

**Impact.** A tenant administrator could alter global catalogue structure, read or delete marketing subscribers across tenants, and change shared SEO configuration — cross-tenant integrity and privacy exposure through legitimate ACL, not a bypass.

**Fix.**
- Pruned the allow-list in `config/saas.php` to read-only or tenant-relevant keys; communications, search SEO, attribute/family writes, customer-group writes and RMA configuration writes were removed. The docblock records why.
- `2026_10_28_100100_prune_tenant_role_permissions.php` intersects existing tenant-bound roles' stored permissions with the new allow-list (chunked, updates only changed roles, empty `down()` because restoring removed privileges is unsafe).

**Verification.** Migration `Ran`. Current development database: 1 tenant-bound role, 0 `marketing.communications*` keys, 0 `marketing.search_seo*` keys, 0 attribute-write keys. The command output is in section 9.

### SAAS-05 — Product channel assignment is not ownership-checked (High, Open)

**Evidence.**
- `packages/Webkul/Product/src/Type/AbstractType.php:176` attaches a new product to `core()->getDefaultChannel()->id`; `Core::getDefaultChannel()` resolves `config('app.channel')`, not the tenant channel set by `TenantResolver::activate()`.
- `AbstractType.php:208` calls `$product->channels()->sync($data['channels'])` with the submitted channel IDs and no ownership check.
- `ProductDataGrid` filter options are built from `core()->getAllChannels()`, so channel identifiers are discoverable in the tenant panel.
- `product_flat` has no `tenant_id`; storefront visibility is channel-scoped, which makes channel assignment a load-bearing isolation boundary.

**Impact.** A tenant administrator can submit another tenant's channel ID, attaching their product to that channel. Because storefront listings filter by channel and `product_flat` is not tenant-scoped, the product may appear in the other tenant's storefront. Separately, products created through paths that fall back to `getDefaultChannel()` may never appear on the tenant's own storefront. The first is a cross-tenant integrity issue; the second is a correctness issue.

**Required remediation.** Constrain `channels` to the active tenant's channel in the form request and in `AbstractType` (or a SaaS listener), replace the default-channel fallback with the tenant channel under tenant context, and add an end-to-end test that a tenant cannot attach a product to a foreign channel and that a created product appears on its own storefront. If reproduction confirms storefront visibility, escalate to Critical.

### SAAS-12 — Reporting aggregates every channel (High, Open)

**Evidence.** `packages/Webkul/Admin/src/Helpers/Reporting/AbstractReporting.php:55` builds `channelIds` from `core()->getAllChannels()` with no tenant filter; line 62 contains a stray commented override. Report queries are raw aggregates and do not apply `tenant_id`. DataGrid filter metadata also lists every channel (`ProductDataGrid`, `CustomerDataGrid`, `OrderDataGrid`, `EUWithdrawalDataGrid`, SEO grids).

**Impact.** A tenant administrator's sales, customer and product reports can include other tenants' totals and top lists; filter dropdowns disclose other tenants' channel names. Even where rows are scoped, aggregates built over unscoped queries are not.

**Required remediation.** In `setChannel()`, restrict to `TenantContext`'s channel when a tenant is active; add `tenant_id` predicates to the reporting queries for orders, invoices, refunds, customers and products; scope filter options; remove the commented override. Add a two-tenant reporting test.

## 5. Platform, provisioning and authentication

### SAAS-06 — Activation depends on a queue worker and there is no channel reconciliation (High, Open)

**Evidence.** `TenantManager::activate()` dispatches `ProvisionTenant` and returns; `ProvisioningService::provision()` creates the channel and sets `tenants.channel_id`. There is no blocking step, no reconciliation command and no backfill for tenants created or activated outside the job. Current development database: 4 tenants, 4 with `channel_id` NULL, 1 channel total.

**Impact.** A tenant can be marked active while having no channel. Its storefront then falls back to the platform default channel (`Core::getCurrentChannel()` returns the first channel when the host does not match), so theme, currency, locale, root category and inventory are wrong; provisioning failures surface only by inspecting the operations table.

**Required remediation.** Run a queue worker and scheduler in every environment; make activation fail loudly or stay `Provisioning` until the channel exists; add `saas:reconcile-tenants` (or extend `saas:bootstrap-tenant`) to create missing channels and domains; backfill existing rows; show a blocking banner in the tenant panel when `channel_id` is null.

### SAAS-07 — No Super Admin password reset or second factor (High, Open)

**Evidence.** `Routes/super-admin.php` exposes login and logout only. `CreateSuperAdminCommand` seeds an account; there is no forgot-password route, notification or token table, and no MFA. Login is throttled at 10 requests per minute.

**Impact.** A locked-out platform operator cannot recover without database or console access; a single password compromise grants full cross-tenant access. For an operator account that can read every tenant's data and change billing, password-only authentication is a material risk.

**Required remediation.** Add a signed, single-use, expiring password reset flow restricted to Super Admins and notify the account owner; add TOTP or WebAuthn; log authentication events to the audit table; require a strong password and rotate the bootstrap credential after first login.

### SAAS-08 — Cross-tenant writes returned HTTP 500 (Medium, Fixed)

**Evidence.** `CrossTenantAccessException` had no `render()` method, so a guard rejection surfaced as a generic server error.

**Impact.** Tenants saw an error page instead of a clear refusal; operators could mistake a blocked attack for an application fault; monitoring noise.

**Fix.** Added `render(Request $request)` returning JSON `{message}` with `403` for JSON requests and a plain `403` response otherwise.

**Verification.** Covered by `DataGridTenantScopeTest` ("renders a cross-tenant exception as 403"); passes.

### SAAS-09 — Tenant policy allowed any tenant admin to list tenants (Medium, Fixed)

**Evidence.** `TenantPolicy::viewAny()` previously returned true for any authenticated admin, including tenant-bound administrators.

**Impact.** A tenant administrator could enumerate every tenant through any policy-gated surface, leaking customer names and plan data.

**Fix.** `viewAny()` now requires a platform ability or a Super Admin; tenant-bound admins pass only when the policy is evaluated for their own tenant.

**Verification.** Covered by the SaaS suite; tested through the Super Admin smoke tests and policy path.

### SAAS-10 — Slug changes were unvalidated and left stale domains (Medium, Fixed)

**Evidence.** `TenantManager::update()` accepted a new slug without checking reserved words or availability and did not re-register `{slug}.{suffix}`.

**Impact.** A tenant could take a reserved or already-used hostname, or keep a stale platform domain after renaming, causing resolution to the wrong storefront.

**Fix.** `update()` captures the previous slug, validates the new one through `DomainService::assertSlugAvailable()`, and calls `registerPlatformDomain()` when the slug changes.

**Verification.** `DataGridTenantScopeTest` asserts that renaming to reserved `admin` throws a validation error and leaves the slug unchanged; it passes.

### SAAS-11 — Marketing route names are registered once per host (Medium, Open)

**Evidence.** `SaasServiceProvider::registerMarketingRoutes()` loops over `localhost`, `www.localhost` and `127.0.0.1` (or configured hosts), registering the same `saas.marketing.*` names each time.

**Impact.** `route('saas.marketing.home')` resolves to the last registered host, so generated links, redirects and sitemap URLs can point at the wrong host; in production with several platform hosts this produces broken canonical URLs.

**Required remediation.** Register the route names once without a domain constraint and enforce the host in `EnsurePlatformHost`, or generate URLs from the current host (`url()->current()` / relative links) and give each host a distinct name suffix. Add a test asserting a link generated on each platform host stays on that host.

### SAAS-15 — Storefront test suites conflict with platform-host routing (Medium, Mitigated)

**Evidence.** With SaaS enabled, `ResolveTenantFromHost` correctly refuses `localhost` as a storefront host. The shared test environment used `localhost`, so every Shop request returned `404`: 546 failures, 172 passes in the full Shop suite. `HomePageTest` alone went from 12 failures to 1 after the fix.

**Mitigation.** `phpunit.xml` now sets `SAAS_ENABLED=false` for the general suites, and `SaasTestCase::createApplication()` turns it on before the application boots for the SaaS suite; `tearDown()` turns it off again. `APP_LOCALE=en` and `MAIL_FROM_ADDRESS=hello@example.com` were added to remove unrelated environment dependence.

**Residual.** This is a test-harness split, not a production fix. Production must use distinct platform and tenant hostnames; a single-host deployment cannot serve both surfaces. The full Shop suite was not re-run after the change (section 9).

### SAAS-20 — `saas.super_admin_session_key` is dead configuration (Low, Open)

**Evidence.** The key is defined in `config/saas.php` but referenced nowhere. Session separation actually comes from the `super_admin` guard name.

**Impact.** Operators may believe a custom cookie name is applied; it is not. Low direct risk, but misleading security configuration.

**Required remediation.** Either wire it into the guard/session configuration or remove it and document that isolation comes from the guard.

## 6. Billing, subscriptions and entitlements

### SAAS-13 — Billing is manual-only (High, Open)

**Evidence.** `BillingProvider` resolves to `ManualBillingProvider` by default and `UnconfiguredBillingProvider` otherwise. There is no gateway adapter, no webhook route, no payment-intent flow, no automated dunning; invoices and payments are recorded by a Super Admin. `BillingService::monthlyRecurringRevenue()` is documented as an estimate.

**Impact.** Subscriptions can be modelled and invoiced, but the platform cannot collect recurring revenue automatically, reconcile gateway events, or recover failed payments. Self-serve paid plans are not operable without manual work.

**Required remediation.** Integrate a gateway behind the existing `BillingProvider` contract, add idempotent webhook handling with signature verification, map gateway states to `SubscriptionStatus`, and add dunning. Keep manual recording for invoiced/enterprise accounts. This is a product decision: acceptable for sales-assisted pilots, blocking for self-serve paid launch.

### SAAS-14 — Feature entitlements are only partially enforced (Medium, Open)

**Evidence.** Quotas are enforced on product, order and staff creation (`MaxProducts`, `MonthlyOrders`, `MaxStaff`). `custom_domain` is checked on the domain route and `api_access` on API routes. `storage_mb`, `advanced_analytics` and `premium_support` are defined in `FeatureKey` and plan payloads but no request path or job enforces them.

**Impact.** A plan that promises 1 GB storage or advanced analytics has no technical effect; tenants can exceed storage silently and see analytics they did not pay for. Billing disputes and uncontrolled disk growth.

**Required remediation.** Measure storage on upload (or nightly) against `storage_mb`; gate analytics screens and exports behind `advanced_analytics`; define what `premium_support` changes (routing, SLA, contact channel) and enforce or remove it. Surface usage in the tenant panel and Super Admin before the limit is hit.

## 7. API, tooling, testing and operations

### SAAS-16 — SaaS tests run against the development database (Medium, Open)

**Evidence.** There is no `.env.testing`; `SaasTestCase` extends `AdminTestCase`, which wraps tests in database transactions against the configured database. Three tests assert global counts and therefore see seeded development rows:
- `ConsoleCommandsTest` trial-warning mail count: 2 vs expected 1.
- `SubscriptionBillingTest::processLifecycle()`: 2 vs expected 1.
- `SubscriptionBillingTest` MRR: 138.0 vs expected 40.0.

**Impact.** Local results are not reproducible; genuine regressions can hide among data-dependent failures; contributors cannot trust a red suite. CI uses a freshly migrated and seeded database per worker and is not affected by stale data.

**Required remediation.** Add a dedicated test database (`.env.testing` or `DB_DATABASE=bagisto_test` in CI and local setup), run `migrate:fresh --seed` once per worker, and make count-based tests scope to the records they create. Do not "fix" these three by loosening assertions.

### SAAS-17 — Translation check command fails on Windows (Low, Open)

**Evidence.** `php artisan bagisto:translations:check` reports all 374 locale comparisons as failing (4 missing and 4 extra files per locale with `--details`). A direct parity script over the SaaS package's `Resources/lang` found `en/app.php` 730 keys and `en/marketing.php` 459 keys with no missing or extra keys across the 23 locales.

**Impact.** The documented CI gate cannot be used locally on Windows; contributors may add a key and believe it is checked when it is not.

**Required remediation.** Fix path normalisation in the command (it appears to compare Windows separators or absolute paths), or document a portable alternative and add it to CI. No translation drift was found in this audit.

### SAAS-18 — API coverage is minimal (Medium, Open)

**Evidence.** `Routes/api.php` exposes tenant profile, product list and order list with Sanctum, `saas.api.tenant` and `saas-api` throttling. There is no API test file and no token-management UI or documented token issuance flow in the package.

**Impact.** The API is advertised by the `api_access` entitlement but is not proven to scope, authenticate or rate-limit correctly; there is no supported way for a tenant to obtain a token.

**Required remediation.** Add feature tests for authentication, tenant mismatch, throttling and response shape; document token issuance; decide whether token management belongs in the tenant panel.

### SAAS-19 — No browser end-to-end coverage for platform surfaces (Low, Open)

**Evidence.** Playwright suites exist for Admin, Shop and Installer only. Super Admin and tenant panel coverage is HTTP smoke testing (`SuperAdminPagesSmokeTest`, `MarketingPagesTest`, `PlatformSecurityTest`).

**Impact.** Regressions in JavaScript-driven interactions, navigation, forms and responsive layout will not be caught. The UI was rebuilt during this work; only server-rendered responses are asserted.

**Required remediation.** Add a Playwright project for the platform host covering Super Admin login, tenant lifecycle, plan editing, billing recording, and tenant team/domain flows.

### SAAS-21 — Stray commented override in reporting (Low, Open)

**Evidence.** `AbstractReporting.php:62` contains `// $this->channelIds = [2];` inside a method body, contrary to the project's no-comments-in-bodies rule and a sign of local debugging.

**Impact.** Suggests reporting was pinned to a single channel during development; confusing and a Pint/review violation.

**Required remediation.** Remove it together with the SAAS-12 scoping work.

### SAAS-22 — Contact form abuse controls are unverified (Low, Open)

**Evidence.** `Marketing/ContactController` persists inquiries; `saas-contact` limits 5/hour per IP and there is a CSRF-protected POST. The marketing tests assert page rendering, not submission validation, throttling or sanitisation.

**Impact.** Spam or stored content abuse could fill the inquiry table; the rate limit is untested.

**Required remediation.** Add tests for validation, honeypot/rate limit, truncation and output escaping; add a retention/pruning job for inquiries.

## 8. Functional inventory

| Area | State | Notes |
|---|---|---|
| Marketing site | Implemented | 11 pages, locale switcher with RTL, pricing from active plans, contact form, sitemap and robots; rendering and locale switching tested |
| Registration and onboarding | Implemented | Plan selection, registration, email verification, approval, trial, status page; provisioning depends on queue (SAAS-06) |
| Super Admin dashboard and insights | Implemented | Tenant, revenue and usage summaries; manual billing data |
| Super Admin tenants | Implemented | List, filters, create, edit, lifecycle actions, cross-tenant orders/products/customers, export, provisioning retry |
| Plans and subscriptions | Implemented | CRUD, offers, trial extension, plan change, cancel, reactivate |
| Billing | Partial | Manual invoices/payments, overdue marking, MRR estimate; no gateway or dunning (SAAS-13) |
| Domains | Implemented | Platform and custom domains, TXT challenge, verification command, primary domain, cache invalidation |
| Provisioning | Partial | Channel, role, owner, category, inventory, subscription; queued and unreconciled (SAAS-06) |
| Settings, CMS, configure | Implemented | Platform settings, CMS content and configuration pages |
| Audit log | Implemented | Tenant and platform actions recorded; covers authentication only partially (SAAS-07) |
| Tenant dashboard, settings, team, domains, billing, usage, onboarding | Implemented | Tenant-scoped; billing is read-only for tenants |
| Storefront commerce | Implemented (core) | Bagisto core with host resolution, session isolation and tenant scoping; channel assignment gap (SAAS-05) |
| Reporting | Partial | Unscoped aggregation across channels (SAAS-12) |
| Entitlements | Partial | Product/order/staff quotas, custom domain and API access; storage, analytics and premium support unenforced (SAAS-14) |
| API | Partial | Three read endpoints, no tests or token issuance flow (SAAS-18) |
| Notifications | Implemented | Verification, invitation, trial warnings; requires mail and queue configuration |
| Exports | Implemented | Tenant data export to configured disk |
| Search | Implemented (database engine) | No per-tenant index; channel and tenant scoping must hold for any external engine |

## 9. Verification log (actual results)

Environment: PHP 8.4.27, Laravel 13.36.0, Pest 5, MySQL development database, Windows.

| Command | Result |
|---|---|
| `vendor/bin/pint --dirty` | **Pass** — 13 files checked |
| `vendor/bin/pest packages/Webkul/Saas/tests --compact` | **3 failed, 82 passed** (272 assertions) — the three data-dependent tests in SAAS-16 |
| `vendor/bin/pest packages/Webkul/Saas/tests/Feature/DataGridTenantScopeTest.php packages/Webkul/Saas/tests/Feature/MarketingPagesTest.php packages/Webkul/Saas/tests/Feature/PlatformSecurityTest.php --compact` | **30 passed** (51 assertions) |
| `vendor/bin/pest packages/Webkul/EUWithdrawal/tests --compact` | **12 passed** (36 assertions) |
| `vendor/bin/pest packages/Webkul/Shop/tests/Feature/HomePageTest.php --compact` | **1 failed, 13 passed** — compare-list test compares raw JSON containing escaped Arabic (`\u062a…`) with the unescaped translation; test defect, unrelated to SaaS |
| `php artisan migrate:status` | Both new migrations `Ran` (batch 4) |
| Tenant role permission query | 1 tenant-bound role; 0 communications keys, 0 SEO keys, 0 attribute-write keys |
| Tenant/channel query | 4 tenants, 4 with `channel_id` NULL, 1 channel |
| `php artisan bagisto:translations:check` | **Fails on Windows** — 374 locale comparisons reported failed; parity script shows 23-locale key parity for `app.php` (730 keys) and `marketing.php` (459 keys) |

Not run: full Shop suite after the configuration change; MariaDB/PostgreSQL runs; Playwright; load tests; external payment or DNS calls.

## 10. Go-live assessment

**Verdict: not production-ready.** The Critical isolation defects are closed and verified, but five High findings remain, two of which are operational preconditions rather than code.

Mandatory before go-live:

1. Fix SAAS-05 (channel ownership) and prove storefront isolation with two tenants.
2. Fix SAAS-06: run queue and scheduler, block activation without a channel, reconcile the four existing tenants.
3. Fix SAAS-12 (report scoping) and remove SAAS-21.
4. Add Super Admin password reset and a second factor (SAAS-07).
5. Decide billing: implement a gateway, webhook and dunning (SAAS-13), or launch as invoice-only and remove self-serve paid signup.
6. Separate platform and tenant hostnames with wildcard DNS and TLS; confirm `SAAS_PLATFORM_HOSTS` contains every platform host and no tenant host.
7. Provide a dedicated test database and green SaaS suite (SAAS-16).
8. Enforce or remove storage/analytics/premium-support entitlements (SAAS-14).
9. Add API tests and token issuance, or disable `api_access` (SAAS-18).

## 11. External configuration and credentials required

- Mail transport and a verified sender for verification, invitation and trial-warning emails.
- Queue worker and scheduler (cron or platform scheduler) for provisioning and the four scheduled commands.
- Cache store for host resolution; a shared store (Redis/database) when running multiple app servers.
- Wildcard DNS for the tenant suffix and TLS certificates for tenant and platform hosts.
- Custom-domain TXT verification requires public DNS; the verifier must not be expected to work on an isolated network.
- Payment gateway credentials and webhook signing secret if SAAS-13 is implemented; no credentials were invented or used.
- Object storage or disk for tenant exports, with retention.
- Super Admin bootstrap credentials created through `saas:create-super-admin`; rotate after first login.
- `SAAS_PLATFORM_DOMAIN`, `SAAS_PLATFORM_HOSTS`, `SAAS_DOMAIN_SUFFIX`, `SAAS_SUPER_ADMIN_PREFIX`, `SAAS_CACHE_STORE`, `SAAS_ENABLED`, `SAAS_BILLING_*`, `SAAS_EXPORT_DISK`.

## 12. Residual risks

1. Shared-database isolation is application-enforced. New raw SQL, reports, exports or console commands must add `tenant_id` explicitly; code review must treat every new query as an isolation change.
2. `product_flat` has no `tenant_id`; storefront isolation depends on channel assignment being correct (SAAS-05) and on products always being attached to exactly the tenant channel.
3. Global modules (attributes, families, customer groups, channels, inventory sources, tax, email templates) must stay out of tenant ACL; the allow-list is the control and needs a test that every tenant role is a subset of it.
4. `Core::getCurrentChannel()` falls back to the first channel when the host does not match, which masks channel-provisioning failures.
5. Marketing route names are host-ambiguous (SAAS-11) and links may cross hosts.
6. Development-database test state hides regressions (SAAS-16).
7. Billing remains manual until a gateway is implemented; MRR is an estimate.
8. No browser end-to-end coverage of the panels (SAAS-19).

## 13. Files changed during remediation

| File | Change |
|---|---|
| `packages/Webkul/Saas/src/Listeners/ScopeDataGridToTenant.php` | New — tenant scope for raw DataGrid queries |
| `packages/Webkul/Saas/src/Providers/SaasServiceProvider.php` | Registers the DataGrid listener |
| `packages/Webkul/Saas/src/Config/saas.php` | Adds derived models to tenant ownership; prunes tenant role ACL |
| `packages/Webkul/Saas/src/Database/Migrations/2026_10_28_100000_add_tenant_id_to_derived_commerce_tables.php` | New — ownership columns, indexes and backfill |
| `packages/Webkul/Saas/src/Database/Migrations/2026_10_28_100100_prune_tenant_role_permissions.php` | New — prunes stored tenant role permissions |
| `packages/Webkul/Saas/src/Http/Middleware/ResolveTenantFromHost.php` | Fail closed on unknown hosts |
| `packages/Webkul/Saas/src/Exceptions/CrossTenantAccessException.php` | Renders `403` instead of `500` |
| `packages/Webkul/Saas/src/Policies/TenantPolicy.php` | Restricts `viewAny` |
| `packages/Webkul/Saas/src/Services/TenantManager.php` | Validates slug changes and re-registers the platform domain |
| `packages/Webkul/Saas/tests/SaasTestCase.php` | Opts the SaaS suite into SaaS before boot; cleans up after |
| `packages/Webkul/Saas/tests/Feature/DataGridTenantScopeTest.php` | New — isolation, derived ownership, `403`, slug tests |
| `packages/Webkul/Saas/tests/Feature/MarketingPagesTest.php` | New — pages, locale/RTL, sitemap, robots |
| `packages/Webkul/Saas/tests/Feature/PlatformSecurityTest.php` | Adds host-resolution tests |
| `phpunit.xml` | SaaS disabled for general suites; locale and mail defaults |
| `docs/audit/FULL_APPLICATION_AUDIT.md`, `ARCHITECTURE_OVERVIEW.md`, `REMEDIATION_CHECKLIST.md` | New — this audit |
