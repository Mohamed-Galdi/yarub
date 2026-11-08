# Authentication redesign and Google sign-in

Implemented 2026-11-06. No production deployment, production migration, live mail, or live Google OAuth was performed.

## Audit and compatibility

YARUB uses Laravel 10, Blade, session authentication on the `web` guard, Breeze controllers/FormRequests, Eloquent users and string roles (`student`, `admin`). It does not use Inertia or Vue. `routes/auth.php` owns authentication. `routes/student.php` uses `auth`, `verified`, `student`, and visit logging; `routes/admin.php` uses the existing administrator gate. `RedirectIfAuthenticated` sends signed-in users to their role's dashboard. `User` uses soft deletion; the Eloquent authentication provider excludes deleted/blocked accounts.

Existing schema: unique email, required hashed password, role, optional phone/avatar/verification timestamp, remember token and soft deletion. There is no additional mandatory student onboarding field: registration requires a name and email. Google supplies these; students can edit their name and profile in the existing account page.

Audit findings and outcomes:

| Finding | Implementation |
| --- | --- |
| Recovery/confirmation/verification views had mismatched Breeze tags and English content | Replaced with shared Arabic authentication layout and accessible components |
| Login omitted remember-me and recovery navigation | Added both; existing Laravel authentication and password broker retained |
| Registration did not regenerate the session | Added regeneration, retaining session/cart data |
| Student intended redirects only restored a small list of pages | Shared destination helper permits same-origin student-dashboard pages and the explicit Google account page; rejects external destinations and retains the existing video query sanitizer |
| Verification and confirmation pointed to nonexistent `/dashboard` | Use existing role dashboard routes |
| Registration and password recovery/reset lacked endpoint throttles | Added route throttles; existing five-attempt credentials limiter retained, with Arabic retry time |
| Expired verification links returned a generic error | Arabic recovery screen with resend action; signature validation remains enforced |
| Password broker disclosed unknown email in its public response | Same success message for missing and existing accounts |
| Student profile password endpoint lacked new-password validation and session invalidation | Added Laravel password rules, remember-token rotation, session/CSRF invalidation; existing JSON success/error contracts retained |

**Email verification is not mandatory in the existing application.** `User` does not implement `MustVerifyEmail`; therefore the existing `verified` middleware and Registered listener do not enforce it. This change intentionally retains that policy so existing accounts keep access without owner action. Manual verification/resend routes work, signed/expired links are checked, and traditional accounts are not silently marked verified. Enabling compulsory verification requires a separate policy and compatibility decision. Verified Google email marks only a newly created Google student verified; linking does not overwrite an existing account's verification state.

Administrator login retains its controller method, guard, credential checks, role gate, URL `/yarub-moshref-center`, route names and redirects. Its view alone is redesigned. No administrator registration or Google sign-in was added. Existing passwords are not rehashed or migrated by this release.

## Pages and interaction

Redesigned login, registration, administrator login, forgot password, reset password, password confirmation, email verification. Added expired-verification recovery and Google connection/password-setup page. Public pages and dashboards are unchanged.

The shared `layouts.auth`, `x-auth.field`, `x-auth.google`, and `x-auth.submit` use the existing indigo, Hacen/Judur fonts and logo. Labels persist; errors are associated with fields and summarized in a focused alert. Password reveal is keyboard-operable. Submit buttons show busy states and reject duplicate submission; browser history restoration resets controls. Server-side validation/throttling still works without JavaScript. At mobile sizes the brand panel becomes a compact header. No payment scripts, remote fonts, or OAuth secret is sent to these pages.

Google buttons are always visible on student login/registration, with the multicolor Google logo. An unconfigured Google request returns an Arabic error; live sign-in still requires all three Google configuration values.

## Google architecture and account linking

Uses official `laravel/socialite`, locked to **5.31.0**. Six new locked packages: Socialite, firebase/php-jwt 7.2.1, league/oauth1-client 1.12.0, phpseclib 4.0.3, paragonie/constant_time_encoding 3.1.3, symfony/polyfill-php82 1.43.0. Existing locked package versions remain unchanged. All new packages support the application's PHP ^8.1 baseline.

Migration: `database/migrations/2026_11_06_000001_create_social_identities_table.php`.

- Adds `users.has_local_password`, default true for all existing users.
- Creates `social_identities` with foreign key to users and unique constraints on provider+provider ID and user+provider. Provider IDs are identifiers, not credentials; they are hidden from model serialization and protected by the database/application access controls. No access or refresh tokens are persisted.
- New Google students get a random, unknown 64-character password hashed with Laravel, plus `has_local_password=false`; nobody is asked to create or remember that placeholder. This preserves the existing non-null password schema without permitting a blank or shared password. Email recovery can establish an optional local password and sets the flag true.

| Method | Path | Name / access |
| --- | --- | --- |
| GET | `/auth/google` | `google.redirect`, guests, 10/minute |
| GET | `/auth/google/callback` | `google.callback`, 20/minute; Socialite state validation and 10-minute session-bound flow |
| GET | `/auth/google/account` | `google.account`, authenticated students |
| POST | `/auth/google/link` | `google.link`, authenticated students, CSRF, current local password, 5/minute |
| POST | `/auth/password-email` | `student.password.email`, authenticated students, CSRF, 5/minute |

Google authorization requests only `openid email profile`, with account selection. It uses the ordinary server-side authorization-code flow; `stateless()` is never used. Socialite verifies state before token exchange. Missing/expired session flow, invalid state, cancellation, provider errors and incomplete/unverified user information return recovery guidance without exposing provider responses.

Repeat sign-in uses the stable Google subject, not email. Identities attached to administrators or soft-deleted students cannot sign in. A new Google identity whose email already exists (including blocked users, case-insensitively) **never auto-links** or creates a duplicate. The user must sign in with their existing password; a conflicting Google attempt sends their next successful login to the connection screen. The same screen is reachable from the student login's “ربط Google بحسابك الحالي” link.

Linking requires the current local password, the same authenticated student at callback, and a verified matching Google email. It cannot attach an identity owned by another user, replace an existing Google identity, or grant an administrator role. Uniqueness constraints protect concurrent inserts; a conflict rolls back account creation. A Google-only student may request a password setup email while signed in and use the recovery link after signing out. There is no identity-unlink action, avoiding accidental removal of a user's only sign-in method.

## Google Cloud Console setup

The code is ready for configuration; these steps require your Google account. The production domain cannot be established reliably from committed configuration: `config/app.php` reads `APP_URL`, and `.env.example` uses localhost. Do not treat another project's domain or a public link in page content as the deployment origin.

1. Open [Google Cloud Console](https://console.cloud.google.com/). Select or create a development project. Use a separate production project and production OAuth client, following [Google's environment separation policy](https://developers.google.com/identity/protocols/oauth2/production-readiness/policy-compliance).
2. Open **Google Auth Platform → Branding** and choose **Get started** if needed. Enter YARUB's app name, monitored support email and developer contact. In production, supply the real homepage, privacy-policy and any terms URLs; use domains you control and complete domain/brand verification when requested. Do not invent policy URLs.
3. In **Audience**, choose **External** for a platform open to students with ordinary Google accounts. Choose Internal only if access should intentionally be limited to your Google Workspace organization. Use Testing while configuring development; add designated test Google accounts under Test users where applicable.
4. In **Data Access**, request only the OpenID Connect identity scopes: `openid`, `https://www.googleapis.com/auth/userinfo.email`, `https://www.googleapis.com/auth/userinfo.profile` (the code uses their equivalent `email` and `profile` aliases). No Gmail, Drive or offline access is needed.
5. In **Clients → Create client**, choose **Web application**. Give it an environment-specific name. This is a confidential server-side client with a secret, not a browser/SPA client.
6. **Authorized JavaScript origins:** leave empty for this implementation. Laravel performs the redirect and code exchange; no Google JavaScript sign-in SDK runs on the page.
7. **Authorized redirect URIs:** for the local configuration below, register exactly `http://localhost:8000/auth/google/callback`. For production, register the exact HTTPS application origin followed by `/auth/google/callback`. Scheme, hostname, port, path, case and trailing slash must match `GOOGLE_REDIRECT_URI`. If you develop on `127.0.0.1` or another port, register that exact URI on the development client too and use it consistently for the whole session. Do not mix localhost and 127.0.0.1.
8. Create the client. Copy the **Client ID** and **Client Secret** into the appropriate server environment securely. Keep downloaded credential files outside the repository; never put secrets in `VITE_*`, Blade, client JavaScript or screenshots.
9. Test the development client in a normal browser. For a public launch, use **Audience → Publish app** for the production project and follow its displayed verification requirements. Basic `openid/email/profile` scopes do not need sensitive/restricted-scope review; brand/name/logo verification may still apply. Google's current audience documentation gives identity-only requests an exception to the usual Testing test-user/7-day authorization restrictions. Adding explicit testers is still useful, and Workspace/Advanced Protection policies can impose their own restrictions. Do not assume the “Testing” label alone prevents all other Google accounts from signing in.

Official references: [consent setup](https://developers.google.com/workspace/guides/configure-oauth-consent), [audience and identity-scope Testing exception](https://support.google.com/cloud/answer/15549945), [web-server OAuth and exact redirect matching](https://developers.google.com/identity/protocols/oauth2/web-server), [OpenID identity claims](https://developers.google.com/identity/openid-connect/openid-connect), [Laravel Socialite](https://laravel.com/docs/10.x/socialite).

## Exact environment variables

Local `.env`, assuming you intentionally run Laravel at `http://localhost:8000` with a disposable local DB and non-live integrations:

```env
APP_URL=http://localhost:8000
GOOGLE_CLIENT_ID=PASTE_DEVELOPMENT_WEB_CLIENT_ID
GOOGLE_CLIENT_SECRET=PASTE_DEVELOPMENT_WEB_CLIENT_SECRET
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

Production `.env` — replace the placeholders; the production origin is deliberately unknown here:

```env
APP_URL=REPLACE_WITH_ACTUAL_HTTPS_PRODUCTION_ORIGIN
GOOGLE_CLIENT_ID=PASTE_PRODUCTION_WEB_CLIENT_ID
GOOGLE_CLIENT_SECRET=PASTE_PRODUCTION_WEB_CLIENT_SECRET
GOOGLE_REDIRECT_URI=REPLACE_WITH_ACTUAL_HTTPS_PRODUCTION_ORIGIN/auth/google/callback
```

The production placeholder is not a runnable value. The client ID, secret, application URL and callback URI differ by environment. Keep existing `APP_KEY`; do not regenerate it. Reliable reset/verification mail requires the environment's existing `MAIL_*` settings and sender configuration. No new mail provider was introduced.

State requires a persistent session across the Google round trip. Retain the established session driver/domain; verify HTTPS proxy handling and secure HTTP-only cookies in production. Laravel's existing SameSite Lax setting supports Google's top-level GET callback. Do not use a stateless/array session driver for real OAuth or disable state to work around cookie errors.

## Release steps

Follow [DEPLOYMENT.md](DEPLOYMENT.md); nothing here authorizes production actions. This release needs dependencies, one reviewed migration, assets and affected caches.

1. Back up the database and review current migration history and deployment maintenance/recovery procedure. Adding a column to users can lock a large table; plan the release in the actual MySQL environment.
2. Provision the environment values securely. Install from `composer.lock` using the deployment's verified Composer executable inside its compatible PHP runtime. The conventional command is `composer install --no-dev --prefer-dist --optimize-autoloader`; confirm that runtime and the existing hooks before executing it. Do not run `composer update` on production. Socialite is explicitly registered in `config/app.php` as well as supporting package discovery.
3. Apply only the reviewed pending migration, after dependencies are installed:

```bash
docker exec --user www-data --workdir /var/www/yarub yarub-php \
  php artisan migrate --path=database/migrations/2026_11_06_000001_create_social_identities_table.php --force
```

4. Build assets on the host following the runbook (`sudo -u www-data npm --cache=/var/cache/yarub-npm run build`). Pulling alone does not publish `public/build`.
5. Clear affected routes and compiled views using the recorded container procedure:

```bash
docker exec --user www-data --workdir /var/www/yarub yarub-php php artisan route:clear
docker exec --user www-data --workdir /var/www/yarub yarub-php php artisan view:clear
```

6. Environment changes must reach Laravel configuration. If this deployment already uses cached config, refresh it under its established policy; `php artisan config:clear` removes stale cached values, but do not introduce `config:cache` routinely (the project has direct integration `env()` reads). Run any required cache command in the same recorded PHP container/user context. Do not clear sessions. Reuse the established OPcache procedure if needed; no new restart procedure is assumed.
7. Complete the live checks below before announcing Google sign-in. Retain Google identities on code rollback; do not casually reverse the migration after Google accounts exist, because that removes their sign-in method.

## Verification and remaining manual checks

Automated authentication suite uses forced `testing`, SQLite `:memory:`, array mail/session/cache and only the user/password-token/social-identity migrations. It does not load a real environment file; the auth test base refuses any other database. Visit logging is disabled in these focused tests. Whole-project migrations include MySQL-specific video schema checks, so the focused suite deliberately excludes them.

```powershell
php vendor/phpunit/phpunit/phpunit tests/Feature/Auth --do-not-cache-result
```

Coverage includes existing credentials and role boundaries, registration validation/role assignment/session rotation, remember/logout/CSRF, local-password change JSON contracts, recovery/reset/token reuse/expiry, verification resend/signed links, protected routes and intended destinations, Google first/repeat login, matching-email conflicts, authenticated linking/password proof, identity conflicts, blocked/admin rejection, incomplete/unverified claims, invalid state, cancelled/expired flows, changed sessions, provider outages and missing configuration. Google tests exercise the real Socialite provider and state checks with mocked token/userinfo HTTP responses; no Google credentials or network calls are used.

Final results: **43 tests, 249 assertions passed**. PHP 8.4 emitted 67 existing vendor deprecations. Vite production asset compilation, PHP syntax checks and `git diff --check` passed. Composer configuration/lock validation passed with the existing Flysystem exact-version warning. The six new authentication packages had no advisories in the final locked audit; the existing repository still has unrelated advisories. The independent visual review's two hierarchy fixes were scored resolved at fix-list scope.

Rendered browser QA uses the real Blade pages exported with disposable test data. Captures at 1440, 390 and 320 px show no horizontal overflow. Password reveal, busy/duplicate-submit and history restoration were exercised. This verifies rendered forms, not a live HTTP authentication session or assistive technology certification.

Live prerequisites and limits:

- Actual Google credentials, consent branding/publishing, domain and session/proxy configuration remain owner setup. Verify first login, repeat login, deliberate cancellation, matching-email conflict and authenticated linking in a normal browser. Confirm an administrator cannot use Google.
- Verify mail delivery, sender reputation, working absolute email URLs and reset/verification links against the intended staging/production host.
- Test the migration in a disposable MySQL environment matching production; automated schema verification here uses SQLite. No production database was touched.
- Local PHP 8.4 reports existing vendor implicit-nullability deprecations; the locked OpenSpout version excludes PHP 8.4. This is not proof of whole-application PHP 8.4 support. The dependency install used only an upper-PHP-version override locally; do not copy that override into production.
- Composer reports pre-existing repository advisories. No existing locked dependency was upgraded as part of this feature; broader remediation remains separate. New authentication packages were checked independently.

The authentication migration, dependencies and environment setup are required before enabling the new code. Do not claim live OAuth or production readiness has been verified from mocked tests alone.
