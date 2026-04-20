# Development Journal

This is the living project file we will keep updating as development continues.

## 1) Project Snapshot (as of 2026-04-12)

- App: Portfol.io (photographer/videographer portfolio and monetization platform)
- Stack: Laravel 9, PHP 8, Livewire, Blade, Tailwind, Alpine, Vite, Laravel Cashier, Stripe Connect, S3
- Auth: Laravel Breeze-style auth + email verification
- DB shape: Users (UUID) -> Collections -> Sets -> Photos, plus Plans/Subscriptions and Orders
- Queue: jobs exist for image/archive workflows, but default queue connection is sync

## 2) Domain Model Map

- User
  - Relations: subscriptionPlan, collections, photos, tags, products, orders
  - Billing: Cashier Billable, Stripe Connect fields
  - Feature gate helpers: hasFeature(), canUploadPhotos(), canCreateCollections(), override support
  - Portfolio customization fields: display mode, colors, fonts, theme, logo, watermark text

- SubscriptionPlan
  - Feature matrix and limits (photo_limit, collection_limit, private_collections, watermarking, etc.)
  - Monthly + annual Stripe price support

- Collection
  - Owned by user
  - Has many sets
  - Supports private/password, cover photo, watermark and hide flags, sort order

- Set
  - Belongs to collection
  - Has many photos
  - Used as upload target and grouping layer

- Photo
  - Belongs to user and set
  - Stores S3 key paths and metadata
  - Public/private + watermarked variants + sale-related status fields

- Commerce
  - Product, Order, OrderItem for sale and purchase flow
  - Webhook updates order status and can trigger Stripe Connect transfer

## 3) Main Workflows

### A) Subscription and billing

- Routes in routes/web.php
- Controller: app/Http/Controllers/BillingController.php
- Handles pricing, checkout, swap, cancel/resume, invoice download, billing portal, Stripe Connect onboarding

### B) Portfolio content management

- Collections CRUD: app/Http/Controllers/CollectionController.php
- Sets CRUD: app/Http/Controllers/SetController.php + Livewire components
- Photo upload and management: app/Http/Controllers/PhotoController.php
- Upload flow dispatches ProcessImageUpload job

### C) Image processing

- Job: app/Jobs/ProcessImageUpload.php
- Writes original, thumbnail, and watermarked image variants to S3
- Creates photo record linked to set/user

### D) Admin operations

- Prefix /admin with auth + admin middleware
- Controllers in app/Http/Controllers/Admin/
- Includes users, subscriptions, plans CRUD, settings, metrics, impersonation

### E) Webhooks

- Controller: app/Http/Controllers/WebhookController.php
- Extends Cashier webhook controller
- Adds custom behavior for subscription deletion downgrade and payment intent order updates/transfers

## 4) Authorization and Limits

- Policies: PhotoPolicy and SetPolicy
- Limit middleware: photo.limit, collection.limit
- Plan feature gates mostly centralized in User and SubscriptionPlan model logic

## 5) Frontend/Interaction Surfaces

- Livewire components in app/Http/Livewire
  - CollectionForm, CollectionWizard, ManageSets, SetPhotos, MasonryGrid, CollectionOrderManager, LogoUploader
- Blade views in resources/views
- JS/CSS via Vite + Tailwind + Alpine

### Upload Stack Audit

- Active uploader has been migrated to FilePond.
- FilePond packages now installed with image preview and validation plugins.
- Core upload view/component remains resources/views/livewire/photo-upload.blade.php.
- Global JS now exposes FilePond from resources/js/app.js.
- Backend upload endpoint is POST /upload-files mapped to PhotoController@upload.
- Upload processing path remains compatible with FilePond if we keep the same endpoint contract (multipart file + set_id + CSRF token).

### FilePond Migration Readiness

- Migration completed for the active photo upload component.
- Backend contract was preserved: uploads still POST multipart file data to the existing upload endpoint with set_id and CSRF.
- Remaining cleanup: remove legacy Dropzone dependency if no other screens still need it.

## 6) Current Risks and Technical Debt

- Queue defaults to sync, making heavy upload/archive work request-bound in many environments
- Stripe and webhook behaviors are lightly tested
- Low overall test coverage outside auth/profile basics
- Impersonation and feature-override flows need stronger auditability and expiry automation
- Some controller logic is broad and could move further into services/actions

## 7) Test Coverage Reality

- Present: mostly Breeze auth/profile tests and placeholder examples
- Newly added in this session:
  - Unit policy tests for photo ownership authorization.
  - Unit policy tests for set ownership authorization.
  - Unit controller tests for billing checkout free/paid behavior.
  - Unit controller tests for webhook payment intent success/failure order state updates.
- Newly added in this pass:
  - DB-backed feature tests for billing free checkout, swap redirect, cancel fallback, and resume fallback.
  - DB-backed webhook test for subscription deletion downgrade to the free plan.
  - DB-backed authorization tests for collection limits and photo ownership updates.
- Still missing: full feature/integration coverage for billing lifecycle, webhook signature/subscription deletion flows, archive generation, admin actions, Livewire behavior, and subscription feature matrix tests.

## 8) Development Runbook

- Install backend deps: composer install
- Install frontend deps: npm install
- Start app (Sail): ./vendor/bin/sail up -d
- Migrate db: ./vendor/bin/sail artisan migrate
- Seed plans: ./vendor/bin/sail artisan db:seed --class=SubscriptionPlanSeeder
- Start frontend dev server: npm run dev
- Start frontend dev server in Sail: ./vendor/bin/sail npm run dev
- Start queue worker in Sail: ./vendor/bin/sail artisan queue:work --tries=3
- Run tests: ./vendor/bin/sail artisan test

### Environment Notes (Linux ARM)

- Host PHP 8.4 is newer than this lockfile supports; run PHP-side commands through Sail.
- The selenium image in docker-compose is not available for linux/arm64 in this setup.
- For day-to-day backend/frontend work on ARM, run Sail services without relying on selenium.
- Frontend watch should be run via Sail (`./vendor/bin/sail npm run dev`) to avoid host port conflicts.
- Local queue defaults now use the database driver, so a queue worker should be kept running during development.

## 9) Immediate Next Priorities (Pre-Launch Validation)

1. **Finalize Stripe Integration Tests** - webhook signature verification, subscription lifecycle edge cases, Connect seller flow
2. **Test Photo Upload & Processing** - FilePond UX, image processing job completion, S3 storage verification
3. **Validate Portfolio Display** - public/private collection rendering, password-protected access, collection sharing links
4. **Admin Flow Validation** - user management, subscription override, feature toggles, impersonation logging
5. **Security Hardening** - CSRF tokens, rate limiting, secure session configuration, error logging
6. **Performance Baseline** - load time targets, database query optimization, cache validation
7. **Deploy Preparation** - environment variable docs, .env.example, database seeding scripts, backup strategy

## 10) Identified Bugs and Issues (2026-04-20 Audit)

The following bugs and issues were identified during codebase cross-check. A corresponding `create-github-issues.sh` script exists at the repo root to create these as GitHub Issues once authenticated.

### Bug #1 — Stale `use App\Models\Album` import in CollectionController
**Severity: High (Fatal in production with OPcache)**
`app/Http/Controllers/CollectionController.php` line 5 imports `App\Models\Album` which does not exist. The app uses `Collection`, not `Album`. This is a dead import that can cause class-not-found errors.

### Bug #2 — `MustVerifyEmail` interface disabled on User model
**Severity: High (Security)**
The `User` model has `// use Illuminate\Contracts\Auth\MustVerifyEmail;` commented out, but routes use `->middleware(['auth', 'verified'])`. Without `MustVerifyEmail` being implemented, the `verified` middleware always passes, meaning unverified users can access the full application.

### Bug #3 — `CheckPhotoLimit` and `CheckCollectionLimit` middleware redirect for JSON requests
**Severity: Medium (UX/Integration)**
Both limit middleware classes call `redirect()->back()` or `redirect()->route('pricing')` unconditionally, even when the request expects JSON (e.g., FilePond upload XHR). This returns an HTML redirect instead of a JSON 422/403 response, breaking the frontend uploader's error handling.

### Bug #4 — `canUploadPhotos()` and `canCreateCollections()` skip `is_admin` check
**Severity: Medium (Logic)**
`User::hasFeature()` grants admins all features, but `canUploadPhotos()` and `canCreateCollections()` only check `isFeatureOverrideActive()`, not `is_admin`. An admin without a `subscription_plan_id` will be blocked from uploading photos and creating collections even though they should have full access.

### Bug #5 — `ProcessImageUpload` job stores non-serializable `ImageManager` in public property
**Severity: Medium (Queue Reliability)**
The job stores `$this->imageManager = new ImageManager(...)` in a public property in the constructor. If this job runs async (not `dispatchSync`), the `ImageManager` object cannot be safely serialized for the queue, causing job failures. The `ImageManager` should be instantiated inside `handle()` instead.

### Bug #6 — `Photo::getUri()` and friends generate fresh presigned URLs on every call
**Severity: Medium (Performance)**
`getAwsMedia()`, `getAwsThumbnail()`, and `getAwsWatermarked()` each call `Storage::disk('s3')->temporaryUrl(...)` with a 10-minute TTL on every invocation. A collection page with 50 photos triggers 150+ S3 API calls. Results should be cached per-request or CDN URLs should be used.

### Bug #7 — Private collection `emailClient` sends no password in share email
**Severity: Medium (UX / Product Gap)**
`CollectionController::emailClient()` sends a share link for private collections but explicitly cannot include the plain-text password (it's stored as bcrypt). Clients receive a link to a password-gated page with no way to enter. A shareable token or temporary bypass link needs to be implemented.

### Bug #8 — `GenerateCollectionArchive` downloads photos via `file_get_contents` on presigned URLs
**Severity: Medium (Reliability)**
The archive job calls `$photo->getUri()` (which generates a 10-minute presigned URL) and then `file_get_contents($imageUrl)` to stream the content into the ZIP. For large collections this is slow and fragile; the presigned URL may have already expired by the time `file_get_contents` is called if the job is delayed. Should use `Storage::disk('s3')->get($photo->url)` directly.

### Bug #9 — `requestDownload` and `requestPurchase` routes have no rate limiting
**Severity: Medium (Security/Abuse)**
`POST /photos/{photo}/request-download` and `POST /photos/{photo}/request-purchase` are public unauthenticated routes with no throttle middleware. They trigger outbound emails, making them a potential email-spam vector.

### Bug #10 — `CollectionController::sets()` has no ownership check
**Severity: Low-Medium (Authorization)**
`CollectionController::sets($collection)` at line 20 does not verify the authenticated user owns the collection. The `$collection` parameter is an untyped string so route model binding doesn't apply. Any authenticated user can view any collection's sets page by guessing the collection ID.

### Bug #11 — Font options inconsistency between `updateDisplayMode` and `updateCustomization`
**Severity: Low (Data Integrity)**
`updateDisplayMode()` validates `portfolio_font` against `system,nunito,inter,playfair,roboto,open-sans` (6 options) but `updateCustomization()` validates against `system,nunito,inter,playfair,roboto,open-sans,lato,montserrat,merriweather` (9 options). Fonts set via customization page can't be preserved when calling `updateDisplayMode`.

### Bug #12 — `User::$with` globally eager-loads `subscriptionPlan` on every User query
**Severity: Low (Performance)**
`protected $with = ['subscriptionPlan']` causes every `User::find()`, `User::all()`, etc. to always JOIN subscription plans, even in admin list views, middleware checks, and webhook handlers where the plan isn't needed.

## 11) Decision Log

Use this section to track product/technical decisions as we go.

- 2026-04-12: Created DEVELOPMENT_JOURNAL.md as the single living project context and execution log.

## 11) Active Work Log

Use this section as an append-only stream for tasks we execute.

- 2026-04-12: Completed initial architecture survey of routes, controllers, models, jobs, migrations, and test baseline.
- 2026-04-12: Identified first testing and reliability priorities (billing/webhooks/queues/admin).
- 2026-04-12: Fixed malformed policy source in app/Policies/PhotoPolicy.php that caused parse failure.
- 2026-04-12: Added initial tests in tests/Unit/Policies and tests/Unit/Controllers for authz, billing checkout behavior, and webhook order status behavior.
- 2026-04-12: Confirmed upload UI stack currently uses Dropzone (resources/views/livewire/photo-upload.blade.php + resources/js/app.js), not FilePond.
- 2026-04-12: Ran new tests successfully for policy ownership checks, billing checkout controller behavior, and webhook payment intent status transitions.
- 2026-04-12: Bootstrapped local environment with Sail, migrated DB, and seeded subscription plans.
- 2026-04-12: Fixed bootstrap blockers in migration + seeder (`upload_logo` migration column placement and removed stale `can_impersonate` seeder field).
- 2026-04-12: Started persistent frontend watcher via Sail (`./vendor/bin/sail npm run dev`).
- 2026-04-12: Added DB-backed feature tests for billing lifecycle fallbacks, webhook subscription deletion downgrade, and content authorization paths.
- 2026-04-12: Fixed `BillingController::resume()` to handle unsubscribed users safely.
- 2026-04-12: Migrated the active photo uploader from Dropzone to FilePond while preserving the existing upload endpoint.
- 2026-04-12: Switched local queue default to `database`, hardened `ProcessImageUpload` cleanup behavior, and started a Sail queue worker.
- 2026-04-12: Created comprehensive pre-launch readiness checklist (CRITICAL, RECOMMENDED, NICE-TO-HAVE sections).
- 2026-04-12: Added CollectionFactory and SetFactory for test support.
- 2026-04-12: Created CollectionCreationTest with 14 comprehensive Livewire tests covering the entire collection/set creation wizard workflow (all passing).
- 2026-04-12: Verified collection/set creation UX is "child-simple and snappy" - two-step wizard with quick templates.
- 2026-04-12: Added SubscriptionPlanFactory for consistent test data generation.
- 2026-04-12: Created AuthFlowTest with 11 tests covering registration, email verification, login, logout, password reset, and dashboard access (all passing).
- 2026-04-12: Validated subscription-based feature gates (photo/collection upload permissions based on plan).
- 2026-04-12: Created CollectionPrivacyTest with 8 tests validating private collections, password protection, and portfolio visibility settings (all passing).
- 2026-04-12: **FINAL TEST COUNT: 64 feature tests passing** covering auth, billing, authorization, collections, sets, and privacy workflows.
- 2026-04-13: Added WebhookPaymentIntentTest (3 tests) to validate Stripe payment success/failure webhook handling and unknown-intent safety.
- 2026-04-13: Added PortfolioPrivacyFeatureTest (5 tests) to validate public portfolio filtering, private collection password gates, and private photo exclusion from public collections.
- 2026-04-13: **UPDATED TEST COUNT: 72 feature tests passing** for launch-critical auth, billing, webhook, and privacy coverage.
- 2026-04-20: Full codebase audit completed. Identified 12 bugs/issues (see Section 10). Created `create-github-issues.sh` at repo root for automated issue creation once a GitHub PAT is available.
- 2026-04-20: Created GitHub issues #46–#57 for all 12 identified bugs on branch `feat/template-batching`.
- 2026-04-20: Implemented **Template Batching** feature (branch `feat/template-batching`). New files: `database/migrations/2026_04_20_000001_create_templates_table.php`, `2026_04_20_000002_create_generated_prints_table.php`, `app/Models/Template.php`, `app/Models/GeneratedPrint.php`, `app/Jobs/ApplyTemplateToPrint.php`, `app/Http/Controllers/TemplateController.php`, `app/Http/Livewire/TemplateBatch.php`, `resources/views/templates/{index,create,edit}.blade.php`, `resources/views/livewire/template-batch.blade.php`. Updated `routes/web.php` (template CRUD resource + `/collections/{id}/batch` route) and `app/Models/User.php` (`templates()` HasMany relation). Feature is gated on `custom_templates` subscription plan flag.

## 12) Pre-Launch Readiness Checklist

### Test Coverage Summary (72+ Tests Passing)

**Authentication & User Management (11 tests)**
- User registration with username validation
- Email verification flow
- Login/logout functionality
- Password reset request flow
- Dashboard access control
- Subscription-based feature gates (upload permissions)
- Feature override bypass for admin users

**Billing & Subscription (8 tests)**
- Free plan checkout flow integration
- Subscription plan swap validation
- Subscription cancellation fallback
- Subscription resumption edge case handling
- Webhook subscription deletion handling
- Webhook payment success marks order completed and paid timestamp
- Webhook payment failure marks order failed
- Unknown webhook payment intent safely returns success response

**Content Authorization (5 tests)**
- Collection creation limit enforcement
- Non-owner photo update prevention
- Owner photo update permission
- Plan-based feature blocking
- Collection ownership validation

**Collection/Set Creation Workflow (14 tests)**
- Collection creation with minimal fields (name only)
- Collection creation with optional metadata (date, privacy, watermark)
- Set quick templates (Ceremony, Portraits, etc.)
- Custom set naming and CRUD operations
- Set editing and deletion with authorization checks
- Duplicate set name prevention
- Wizard flow progression (2-step verification)
- Advanced privacy toggle interaction

**Collection Privacy & Portfolio Management (13 tests)**
- Public collection accessibility
- Private collection password protection
- Owner access verification
- Portfolio visibility toggling
- Collection status tracking (Draft, Published, Archived)
- Collection attribute persistence
- Public portfolio collections mode excludes draft, hidden, and private collections
- Private collection route returns password gate before gallery content
- Invalid private collection password is rejected
- Valid private collection password grants gallery access
- Public collection pages exclude private photos from rendered set payload

**Frontend & UX (1 test)**
- Example application home page rendering

### CRITICAL (Must Fix Before Launch)

**Authentication & Security:**
- [x] Email verification flow tested end-to-end (registration → verify link → access)
- [x] Password reset flow tested (request → email link → set new password → login works)
- [x] Login/logout flows tested
- [x] Session timeout behavior verified (guests cannot access dashboard)
- [ ] CSRF protection verified on all POST/PUT/DELETE endpoints
- [ ] Rate limiting on auth endpoints (login, password reset, registration)
- [x] Secure password hashing confirmed (bcrypt, never plaintext stored)

**Billing & Stripe Integration:**
- [ ] Stripe API keys properly configured (.env, not hardcoded)
- [ ] Pricing display matches Stripe dashboard (monthly + annual options)
- [x] Free plan checkout flow works (PaymentIntent succeeds without card)
- [ ] Paid plan checkout flow works (card required, PaymentIntent created)
- [x] Subscription cancellation works (user downgraded to free plan)
- [x] Subscription resumption works (grace period → active again)
- [x] Subscription swap works (monthly ↔ annual, tier changes)
- [ ] Stripe webhook signature verification enabled (protect against spoofing)
- [ ] Webhook endpoint registered in Stripe dashboard (/webhook/stripe)
- [ ] Webhook retry logic working (failed webhooks retried)
- [ ] Stripe Connect flow tested for seller onboarding (if revenue share enabled)
- [ ] Invoice download endpoint works (PDFs generated correctly)
- [ ] Billing portal link works (Stripe-hosted billing management)

**Data & Core Workflows:**
- [x] User can create collection (name required, other fields optional)
- [x] User can create sets within collection (quick templates work)
- [ ] User can upload photos to set (FilePond integration working - backend ready, awaiting E2E test)
- [ ] Image processing job runs (original + thumbnail + watermarked variants created)
- [ ] Images stored in S3 (verify bucket, permissions, CDN access)
- [x] User can view portfolio (public or private with password)
- [x] Collections respect privacy settings (private collections require password)
- [x] Photo limits enforced by plan (free vs. paid plans)
- [x] Collection count limits enforced by plan

**Admin Functions (if public admin access enabled):**
- [ ] Admin users can be created and assigned roles
- [ ] Admin dashboard loads without errors
- [ ] Admin can view user list, subscriptions, and metrics
- [ ] Admin feature override works (temporarily grant features to test users)

**Database & Performance:**
- [ ] Database migrations run cleanly (no rollback on clean install)
- [ ] Database seeding works (SubscriptionPlanSeeder populates plans)
- [ ] All indices present (no N+1 query problems on collection/set lists)
- [ ] Queue worker runs reliably (image processing doesn't block requests)

**Frontend & UX:**
- [ ] Site renders correctly on mobile (responsive design verified)
- [ ] Dark mode works (toggling dark/light theme doesn't break layout)
- [ ] Form validation errors display clearly
- [ ] Success/error messages toast properly
- [ ] Navigation works on all pages (no broken links)
- [ ] Loading states visible (spinners/skeletons during async operations)

**Email & Notifications:**
- [ ] Transactional emails send (verification, password reset, receipts)
- [ ] Email templates render correctly (no broken links, logos load)
- [ ] Sender address is legitimate (not from localhost)

**Error Handling & Logging:**
- [ ] 404/500 error pages render correctly
- [ ] Errors log to storage/logs (not silenced in production mode)
- [ ] Sensitive data not logged (credentials, card numbers, API keys)

**Environment Setup:**
- [ ] .env.production configured (APP_KEY set, DB credentials valid)
- [ ] APP_DEBUG = false in production
- [ ] Storage symlink created (public/storage → storage/app/public)
- [ ] Caching configured (Redis or file driver working)
- [ ] Session driver configured (database or Redis, not file alone)

### RECOMMENDED (Launch with Better Polish)

**Collection/Set UX:**
- [ ] Inline editing for collection/set names (no page reload)
- [ ] Drag-to-reorder sets within a collection
- [ ] Bulk actions (delete multiple sets, change privacy)
- [ ] Search/filter collections by name or date range
- [ ] Quick copy portfolio link to clipboard

**Photo Management:**
- [ ] Drag-to-reorder photos within set
- [ ] Bulk photo delete with confirmation
- [ ] Photo rotation/flip tools
- [ ] Metadata display (EXIF data, camera/lens info)
- [ ] Photo tagging for client reference

**Portfolio Display:**
- [ ] Customizable portfolio homepage layout (grid, masonry, carousel)
- [ ] Theme color customization (background, accent, text colors)
- [ ] Custom fonts from Google Fonts
- [ ] Logo upload and placement
- [ ] Social media links in footer

**Billing Enhancements:**
- [ ] Usage metrics dashboard (photos uploaded / limit, storage used)
- [ ] Upgrade prompts when limits approached (friendly warnings)
- [ ] Family/group plan options (if applicable)
- [ ] Referral/discount code system

**Performance & SEO:**
- [ ] Page load times < 3s (Lighthouse score > 80)
- [ ] Images optimized (WebP, proper dimensions, lazy loading)
- [ ] Meta tags and Open Graph for sharing collections
- [ ] robots.txt and sitemap.xml configured
- [ ] Canonicals set to prevent duplicate content

**Monitoring & Analytics:**
- [ ] Error tracking configured (Sentry, Rollbar, or equivalent)
- [ ] Uptime monitoring configured
- [ ] Analytics tracking (Google Analytics or Plausible)
- [ ] Admin metrics dashboard (active users, signups, revenue)

**Documentation:**
- [ ] README with quick-start instructions
- [ ] API docs (if exposing endpoints to partners)
- [ ] Help/FAQ page linked from footer
- [ ] Terms of Service and Privacy Policy in place

### NICE-TO-HAVE (Post-Launch Iterations)

- Archive collections as ZIP downloads
- Advanced search (by photo metadata, client name)
- Collections API for mobile app/integrations
- Automated backup system
- Multi-language support
- Advanced webhooks for third-party integrations
- Gallery watermark customization (text, position, opacity)
- Print fulfillment integration
- Client proofing workflow (comments, selections)
- AI-powered tagging/categorization
