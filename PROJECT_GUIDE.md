# Common Ground Marketplace: Setup and Project Roadmap

This guide explains how to run the marketplace locally, where its main features live, and a practical order for learning the codebase.

## What this project does

Common Ground is a neighborhood marketplace. Visitors can browse and search listings. Registered members can create and manage their own listings. Administrators can view the admin dashboard, member accounts, and activity records.

The application uses Laravel for the API and server-rendered SPA shell, Vue 3 for the interface, Vue Router for page navigation, Pinia for client state, and Tailwind CSS 4 for styling.

## Requirements

- PHP 8.3 or later, with the PHP extensions required by Laravel and Composer.
- Composer.
- Node.js and npm.
- SQLite for the default local database, or another database configured in `.env`.

## Local setup

From the repository root, install dependencies and configure the environment:

```sh
composer install
copy .env.example .env
php artisan key:generate
npm install
```

On macOS or Linux, use `cp .env.example .env` instead of `copy`.

The example environment uses SQLite. Make sure `database/database.sqlite` exists, then run the migrations and seed the built-in marketplace categories:

```sh
php artisan migrate --seed
php artisan storage:link
```

Build the frontend assets and start the Laravel server and Vite development server in separate terminals:

```sh
npm run build
php artisan serve
npm run dev
```

Open the local URL printed by `php artisan serve` (normally `http://127.0.0.1:8000`). During active frontend development, leave `npm run dev` running for hot reload. For a production-like local view, stop Vite and use the built assets from `npm run build`.

## Environment configuration

The application reads local settings from `.env`; do not commit real credentials.

| Variable | Purpose |
| --- | --- |
| `APP_URL` | Public base URL used by Laravel. |
| `DB_CONNECTION` and database variables | Database connection. SQLite is the example default. |
| `VITE_TURNSTILE_SITE_KEY` | Public Cloudflare Turnstile site key embedded in the login and registration pages. |
| `TURNSTILE_SECRET_KEY` | Private server-side Turnstile Siteverify key. Never expose it through a `VITE_` variable. |
| `TURNSTILE_CA_BUNDLE` | Optional CA bundle path when the local PHP/cURL environment needs a custom certificate bundle. Leave empty when the system CA store works. |

Listing photos are stored on Laravel's `public` disk under `storage/app/public/listings`. The `public/storage` link created by `php artisan storage:link` makes their URLs available to the browser. Each listing accepts up to three JPEG, PNG, or WebP images, at 5 MB each; new uploads are resized and compressed in the browser. Existing category artwork remains visible whenever a listing has no photos.

For Turnstile, add the local hostname you use (for example `localhost` or `127.0.0.1`) to the widget's allowed hostnames in Cloudflare. The site key and secret must belong to the same widget. After changing Vite-prefixed variables, restart `npm run dev` or rebuild the assets.

## How the code is organized

### Laravel backend

- `routes/api.php` — API endpoints, login/registration rate limits, authentication, listing routes, and admin access.
- `routes/web.php` — serves the Vue application shell for browser routes.
- `app/Http/Controllers/Api/` — API actions for authentication, categories, listings, and the admin dashboard.
- `app/Http/Requests/` — server-side validation rules for authentication and listing input.
- `app/Http/Resources/` — shapes listing and category JSON responses.
- `app/Models/` — users, listings, categories, subcategories, and activity records.
- `app/Policies/ListingPolicy.php` — authorization for listing ownership and management.
- `app/Services/` — Turnstile verification and activity logging.
- `app/Http/Middleware/ApiResponseEnvelope.php` — consistent API response envelopes.
- `database/migrations/` — schema history; `database/seeders/DatabaseSeeder.php` creates the starter categories.
- `app/Console/Commands/MakeUserAdmin.php` — promotes an existing account to administrator.

### Vue frontend

- `resources/js/app.js` — Vue, Pinia, router, and plugin setup.
- `resources/js/App.vue` — shared header, navigation, footer, and page outlet.
- `resources/js/router/index.js` — browser routes and client-side guest/member/admin navigation checks. Backend middleware remains the security boundary.
- `resources/js/mixins/CountryMixin.js` — prioritizes common countries and loads state/city choices on demand for listing forms; custom place names remain supported.
- `resources/js/services/api.js` — shared API client and response/error handling.
- `resources/js/stores/auth.js` — sign-in state and current-user session handling.
- `resources/js/views/` — page-level components such as home, listings, listing detail, login, registration, account listings, and admin dashboard.
- `resources/js/components/` — reusable listing cards and Turnstile widget.
- `resources/css/app.css` — Tailwind setup, Google-blue theme tokens, responsive styles, forms, buttons, and plugin styling.
- `resources/views/welcome.blade.php` — SPA document shell, font loading, and Vite entry points.
- `public/favicon.svg` — site favicon.

## Roles, access, and activity

New accounts receive the `user` role by default. Administrators have the `admin` role. The frontend hides admin navigation from members, while the API applies server-side authorization to protect admin data. To promote an account after it has registered, run:

```sh
php artisan user:make-admin person@example.com
```

Activity records are written by `app/Services/ActivityLogger.php` and stored in the activity-log table. They are intended to help administrators understand important actions and their actors.

Protected API routes use `auth:sanctum`. Sanctum accepts a configured browser session first, then checks a bearer personal-access token. This Vue app uses the bearer-token path: login or registration returns a token, the auth store saves it in local storage, and `resources/js/services/api.js` sends it in the `Authorization` header. Sanctum finds and validates the token record, then makes its user available as `$request->user()`. Missing or invalid credentials are rejected before the controller runs. Signing out deletes the current token. Admin API routes add the server-side `can:admin` role check.

## Common tasks

```sh
# Apply schema changes
php artisan migrate

# Seed the starter categories
php artisan db:seed

# Rebuild optimized frontend assets
npm run build

# Run the automated test suite
php artisan test

# Format changed PHP files
vendor/bin/pint --dirty --format agent
```

## Learning roadmap

1. **Start with the shell:** read `routes/web.php`, `resources/views/welcome.blade.php`, `resources/js/app.js`, and `resources/js/App.vue` to see how Laravel serves the Vue application.
2. **Follow a page:** read `resources/js/router/index.js`, then a view in `resources/js/views/` and any components it imports. Route views load asynchronously so each page can be split into its own frontend chunk.
3. **Trace an API call:** follow `resources/js/services/api.js` into `routes/api.php`, then the matching controller, request validator, resource, model, and migration.
4. **Understand member access:** inspect the auth store, `AuthController`, role middleware/policies, and the user role migration. Remember that Vue route checks improve navigation but do not replace backend authorization.
5. **Understand listings:** start at `Listings.vue` or `CreateListing.vue`, then follow `CountryMixin.js`, `ListingController`, `StoreListingRequest`, `ListingPolicy`, and the listing/category migrations. Country, state, and city selectors use searchable options and also accept custom entries.
6. **Understand administration:** review `AdminDashboard.vue`, `AdminController`, activity-log model/service, and `user:make-admin`.
7. **Change the visual system:** update theme tokens and shared rules in `resources/css/app.css`, then check shared shell and page components at phone, tablet, and desktop widths.
8. **Make a change safely:** add or update a focused feature test, run it with `php artisan test`, run `vendor/bin/pint --dirty --format agent` after PHP changes, and run `npm run build` after frontend changes.

## Route overview

| Route | Access | Purpose |
| --- | --- | --- |
| `GET /api/categories` | Public | Category and subcategory data. |
| `GET /api/listings` | Public | Search and browse listings. |
| `GET /api/listings/{listing}` | Public | Listing details. |
| `POST /api/register` | Public, rate limited, requires valid Turnstile configuration | Create an account. |
| `POST /api/login` | Public, rate limited, requires valid Turnstile configuration | Sign in. |
| `GET /api/profile`, `POST /api/logout` | Signed in | Current account and sign out. |
| `/api/mylistings` and listing create/update/delete routes | Signed in; ownership checked | Manage the member's listings. |
| `GET /api/admin/dashboard` | Administrator | Admin summaries, users, and activity data. |

## Notes for contributors

- Keep validation on the backend even when the Vue form also checks input.
- Protect authorization-sensitive data in Laravel; client-side role checks are only a user-interface convenience.
- Keep private integration credentials in server-only environment variables.
- Use the existing API response format and shared components when extending features.
- The site uses Lato from Google Fonts with local system-font fallbacks. If external fonts are unavailable, the app remains readable with the fallback stack.
