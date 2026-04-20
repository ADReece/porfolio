#!/usr/bin/env bash
# =============================================================================
# create-github-issues.sh
# Creates GitHub Issues for all bugs identified in the 2026-04-20 audit.
#
# Usage:
#   1. Generate a PAT at https://github.com/settings/tokens
#      (needs: repo scope)
#   2. Run:  GH_TOKEN=<your-token> bash create-github-issues.sh
# =============================================================================

set -e

REPO="ADReece/portfolio"
GH="${HOME}/bin/gh"

if [[ -z "${GH_TOKEN}" ]]; then
  echo "Error: GH_TOKEN environment variable is not set."
  echo ""
  echo "Usage: GH_TOKEN=<your-token> bash create-github-issues.sh"
  echo ""
  echo "Generate a token at: https://github.com/settings/personal-access-tokens/new"
  echo "Required permission: Issues -> Read and write"
  exit 1
fi

if [[ ! -x "${GH}" ]]; then
  echo "gh CLI not found at ${GH}. Run the following to install:"
  echo "  mkdir -p ~/bin && curl -sL https://github.com/cli/cli/releases/download/v2.68.0/gh_2.68.0_linux_arm64.tar.gz | tar -xz -C /tmp && cp /tmp/gh_2.68.0_linux_arm64/bin/gh ~/bin/gh"
  exit 1
fi

# GH_TOKEN in environment is picked up automatically — no auth login needed.
echo "Creating GitHub issues on ${REPO} ..."

# ── Bug #1 ────────────────────────────────────────────────────────────────────
"${GH}" issue create \
  --repo "${REPO}" \
  --title "bug: stale \`use App\\Models\\Album\` import in CollectionController" \
\
  --body "## Description
\`app/Http/Controllers/CollectionController.php\` line 5 imports \`App\\Models\\Album\` which does not exist. The app uses \`Collection\`, not \`Album\`. This is a dead class import that will cause a fatal \`Class not found\` error in environments with OPcache or eager class loading.

## Steps to Reproduce
Load any collection route (\`/collections\`, \`/collections/create\`, etc.)

## Expected Behaviour
Page loads without error.

## Actual Behaviour
PHP fatal: \`Class 'App\\Models\\Album' not found\`

## Fix
Remove line 5 from \`app/Http/Controllers/CollectionController.php\`:
\`\`\`diff
- use App\Models\Album;
\`\`\`
" 2>&1

# ── Bug #2 ────────────────────────────────────────────────────────────────────
"${GH}" issue create \
  --repo "${REPO}" \
  --title "bug: MustVerifyEmail interface disabled — email verification not enforced" \
\
  --body "## Description
\`app/Models/User.php\` has \`// use Illuminate\\Contracts\\Auth\\MustVerifyEmail;\` commented out. The dashboard route uses \`->middleware(['auth', 'verified'])\`, but without the model implementing \`MustVerifyEmail\`, the \`verified\` middleware never rejects requests. Unverified users can access the full authenticated application.

## Fix
Uncomment line 5 in \`app/Models/User.php\`:
\`\`\`diff
- // use Illuminate\Contracts\Auth\MustVerifyEmail;
+ use Illuminate\Contracts\Auth\MustVerifyEmail;
\`\`\`

And update the class declaration:
\`\`\`diff
- class User extends Authenticatable
+ class User extends Authenticatable implements MustVerifyEmail
\`\`\`
" 2>&1

# ── Bug #3 ────────────────────────────────────────────────────────────────────
"${GH}" issue create \
  --repo "${REPO}" \
  --title "bug: photo/collection limit middleware returns HTML redirect for JSON (FilePond) requests" \
\
  --body "## Description
\`CheckPhotoLimit\` and \`CheckCollectionLimit\` middleware call \`redirect()->back()\` and \`redirect()->route('pricing')\` unconditionally. When FilePond POSTs to \`/upload-files\` and the photo limit is reached, the middleware returns an HTTP 302 HTML redirect instead of a JSON error response. FilePond cannot parse this, so the uploader silently fails with no useful error shown to the user.

## Fix
In both middleware \`handle()\` methods, check \`\$request->expectsJson()\` before deciding the response format:
\`\`\`php
if (\$request->expectsJson()) {
    return response()->json(['error' => \"You've reached your photo limit...\"], 403);
}
return redirect()->back()->with('error', '...');
\`\`\`
" 2>&1

# ── Bug #4 ────────────────────────────────────────────────────────────────────
"${GH}" issue create \
  --repo "${REPO}" \
  --title "bug: canUploadPhotos() and canCreateCollections() do not respect is_admin flag" \
\
  --body "## Description
\`User::hasFeature()\` returns \`true\` for admins unconditionally, but \`canUploadPhotos()\` and \`canCreateCollections()\` bypass \`hasFeature()\` and only check \`isFeatureOverrideActive()\`. An admin with no \`subscription_plan_id\` set will fail the \`if (!\$this->subscriptionPlan)\` guard and be blocked from uploading photos or creating collections.

## Steps to Reproduce
1. Create an admin user with \`is_admin = true\` but \`subscription_plan_id = null\`
2. Attempt to upload a photo or create a collection
3. The action is blocked with \"Please select a subscription plan\"

## Fix
Add the \`is_admin\` check at the top of both methods, matching the pattern in \`hasFeature()\`:
\`\`\`php
public function canUploadPhotos(): bool
{
    if (\$this->is_admin) return true;
    // ... existing logic
}
\`\`\`
" 2>&1

# ── Bug #5 ────────────────────────────────────────────────────────────────────
"${GH}" issue create \
  --repo "${REPO}" \
  --title "bug: ProcessImageUpload job stores non-serializable ImageManager in public property" \
\
  --body "## Description
\`app/Jobs/ProcessImageUpload.php\` instantiates \`ImageManager\` in the constructor and assigns it to \`\$this->imageManager\`. When the job is queued (not dispatched via \`dispatchSync\`), Laravel attempts to serialize the job class. \`ImageManager\` contains GD/Imagick resources that cannot be serialized, causing the job to fail when pushed to a real queue driver (database, Redis, etc.).

Currently the code calls \`dispatchSync\` which bypasses this, but the job implements \`ShouldQueue\` suggesting async use is intended.

## Fix
Remove \`\$this->imageManager\` from the constructor and instantiate it inside \`handle()\`:
\`\`\`php
public function handle()
{
    \$imageManager = new ImageManager(['driver' => 'imagick']);
    // use \$imageManager locally...
}
\`\`\`
" 2>&1

# ── Bug #6 ────────────────────────────────────────────────────────────────────
"${GH}" issue create \
  --repo "${REPO}" \
  --title "perf: Photo model generates a new presigned S3 URL on every getUri() call" \
\
  --body "## Description
\`Photo::getAwsMedia()\`, \`getAwsThumbnail()\`, and \`getAwsWatermarked()\` each call \`Storage::disk('s3')->temporaryUrl()\` on every invocation. A collection page with 50 photos makes 150+ S3 API calls just to render photo URLs, adding significant latency.

## Suggested Fix
Options in order of preference:
1. Use a CloudFront distribution with signed cookies/URLs for all media — eliminates per-photo API calls entirely.
2. Cache presigned URLs in the model instance during the request lifecycle using a private property.
3. Generate URLs with a longer TTL (e.g., 24 hours) and cache them in Redis keyed by \`photo_id:variant\`.
" 2>&1

# ── Bug #7 ────────────────────────────────────────────────────────────────────
"${GH}" issue create \
  --repo "${REPO}" \
  --title "bug: emailClient sends share link for private collection but cannot include password" \
\
  --body "## Description
\`CollectionController::emailClient()\` sends a share email for private collections but the password is stored as a bcrypt hash and cannot be recovered. The email directs clients to a password-gated page with no password included. This breaks the private gallery sharing workflow end-to-end.

The existing code has a TODO comment acknowledging this limitation.

## Suggested Fix
Implement a temporary shareable access token for private collections:
1. Generate a signed URL or store a random token in a \`collection_share_tokens\` table with an expiry.
2. Include the signed URL in the share email — clicking it bypasses the password gate for that session.
3. The \`profile.collection\` route can check for a valid token in the query string and grant access.
" 2>&1

# ── Bug #8 ────────────────────────────────────────────────────────────────────
"${GH}" issue create \
  --repo "${REPO}" \
  --title "bug: GenerateCollectionArchive downloads photos via file_get_contents on presigned URLs" \
\
  --body "## Description
\`app/Jobs/GenerateCollectionArchive.php\` calls \`\$photo->getUri()\` to get a 10-minute presigned S3 URL, then passes it to \`file_get_contents()\`. For large collections:
- The presigned URL may expire before \`file_get_contents\` is called
- It adds unnecessary HTTP overhead (presigned URL generation + external HTTP request per photo)
- \`allow_url_fopen\` must be enabled on the server

## Fix
Read directly from S3 using the storage driver:
\`\`\`php
\$imageContent = Storage::disk('s3')->get(\$photo->url);
\`\`\`
This avoids presigned URL generation entirely and is faster and more reliable.
" 2>&1

# ── Bug #9 ────────────────────────────────────────────────────────────────────
"${GH}" issue create \
  --repo "${REPO}" \
  --title "security: requestDownload and requestPurchase routes lack rate limiting" \
\
  --body "## Description
\`POST /photos/{photo}/request-download\` and \`POST /photos/{photo}/request-purchase\` are publicly accessible unauthenticated routes that dispatch outbound emails. There is no throttle middleware on these routes, making them a potential email-spam vector — anyone can trigger mass emails by POSTing in a loop.

## Fix
Add throttle middleware to both routes in \`routes/web.php\`:
\`\`\`php
Route::post('/photos/{photo}/request-download', [PhotoController::class, 'requestDownload'])
    ->name('photos.request-download')
    ->middleware('throttle:5,1'); // 5 per minute per IP

Route::post('/photos/{photo}/request-purchase', [PhotoController::class, 'requestPurchase'])
    ->name('photos.request-purchase')
    ->middleware('throttle:5,1');
\`\`\`
" 2>&1

# ── Bug #10 ───────────────────────────────────────────────────────────────────
"${GH}" issue create \
  --repo "${REPO}" \
  --title "bug: CollectionController::sets() missing ownership authorization check" \
\
  --body "## Description
\`CollectionController::sets(\$collection)\` (mapped to \`GET /collections/{collection}/sets\`) does not verify that the authenticated user owns the collection. The \`\$collection\` parameter is untyped so route model binding is not applied — the raw ID is passed in. Any authenticated user can view another user's sets page by navigating to \`/collections/{other_user_collection_id}/sets\`.

## Fix
Add an ownership check and use proper route model binding:
\`\`\`php
public function sets(\$collectionId): View
{
    \$collection = auth()->user()->collections()->findOrFail(\$collectionId);
    return view('collections.backend.sets', ['collection' => \$collection]);
}
\`\`\`
" 2>&1

# ── Bug #11 ───────────────────────────────────────────────────────────────────
"${GH}" issue create \
  --repo "${REPO}" \
  --title "bug: portfolio_font validation lists are inconsistent between two update endpoints" \
\
  --body "## Description
Two profile update methods validate \`portfolio_font\` against different option lists:

- \`ProfileController::updateDisplayMode()\`: \`system,nunito,inter,playfair,roboto,open-sans\` (6 fonts)
- \`ProfileController::updateCustomization()\`: \`system,nunito,inter,playfair,roboto,open-sans,lato,montserrat,merriweather\` (9 fonts)

A user who sets their font to \`lato\`, \`montserrat\`, or \`merriweather\` via the customization page will have that value silently overwritten with a validation failure if they later call \`updateDisplayMode\`.

## Fix
Consolidate the allowed font list into a constant or config value and reference it from both validation rules.
" 2>&1

# ── Bug #12 ───────────────────────────────────────────────────────────────────
"${GH}" issue create \
  --repo "${REPO}" \
  --title "perf: User model globally eager-loads subscriptionPlan on every query" \
\
  --body "## Description
\`app/Models/User.php\` has \`protected \$with = ['subscriptionPlan'];\` which causes every \`User\` query — including admin list pages, middleware auth checks, and webhook lookups — to always JOIN the \`subscription_plans\` table. This is unnecessary overhead in contexts that only need the user record.

## Fix
Remove \`\$with\` and instead eager-load the relation only where it is needed:
\`\`\`php
// In controllers/middleware that need it:
User::with('subscriptionPlan')->paginate(20);

// Or use loadMissing() where the relation may already be loaded:
\$user->loadMissing('subscriptionPlan');
\`\`\`
" 2>&1

echo ""
echo "Done! All 12 issues created on ${REPO}."
echo "View them at: https://github.com/${REPO}/issues"
