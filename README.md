# गैह्रे वंशावली · Gaihre Bansawali

A Laravel 13 / PHP 8.4+ / MySQL genealogy archive with a Blade, Tailwind, and Vite frontend. Only administrators have accounts. There is no public registration or collection of private genealogy member data.

## Run locally

```sh
composer install
npm install
# Copy .env.example to .env only for a new installation; preserve an existing .env.
php artisan key:generate # New installations only.
php artisan migrate --seed
php artisan bansawali:admin
php artisan storage:link
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

Configure MySQL and a unique `ADMIN_PASSWORD` of at least 12 characters in `.env` first. The configured default email is `gaihrenirmal2021@gmail.com`. This installation already has its generated admin password in `.env`; the password is not committed or prefilled into the login page.

Website: http://127.0.0.1:8000. Admin: http://127.0.0.1:8000/admin.

The installed XAMPP PHP is 8.2, which cannot run Laravel 13. Use the system PHP 8.4 CLI server above, or configure Apache/PHP-FPM with PHP 8.4+ and the document root `/opt/lampp/htdocs/bamsawali/public`. Do not serve the application root. The root `.htaccess` also blocks direct access to source and environment files under shared Apache htdocs.

To deliberately reset the existing admin's password after changing `ADMIN_PASSWORD`:

```sh
php artisan config:clear
php artisan bansawali:admin --reset-password
```

Normal seeding never overwrites edited content or admin credentials. Use HTTPS and `APP_DEBUG=false` for a deployed installation; set `APP_URL` to its actual URL. No public deployment has been performed.

## Admin workflow

- **Members:** add a root ancestor or choose an existing parent. “+ Child” preselects the parent. Search the parent selector in Nepali or English. Generations update automatically, including descendants when a branch moves.
- **Tree view:** view all members, edit records, add children, zoom, collapse branches, and use fullscreen. Hidden records remain visible to administrators; hiding a parent hides its whole public branch.
- **Deletion:** members with children cannot be deleted. Move or remove their children first. Database foreign keys also restrict deletion.
- **Website content:** edit the homepage introduction, Gaihre introduction, genealogy introduction, history, about, and contact pages. Use the rich-text toolbar for headings, lists, quotes, emphasis, and links.
- **Articles:** manage drafts, immediate or scheduled publishing, featured images, and SEO metadata. Upload JPEG, PNG, or WebP up to 4 MB. Times use Asia/Kathmandu.
- **Settings:** edit website names, both hero headline lines, tagline, footer, and contact text.

No invented genealogy or historical claims are seeded. Initial prose is an editable introduction; add verified genealogy records and your own articles through the admin panel.

## Database schema

| Table | Main columns |
| --- | --- |
| `users` | name, email, hashed password, is_admin |
| `bansawali_members` | name_ne, nullable name_en, nullable parent_id, generation, short_description, display_order, status |
| `blog_posts` | title, unique slug, short_description, content, featured_image, author_id, status, published_at, meta_title, meta_description |
| `content_pages` | unique key, title, content, status |
| `settings` | unique key, value |

Laravel's standard session, cache, and queue tables remain available. Genealogy and author foreign keys restrict destructive parent deletion.

## Architecture and routes

- `app/Models`: database models and article publication filtering.
- `app/Http/Controllers/PublicController.php`: homepage, content, tree, articles, sitemap.
- `app/Http/Controllers/Admin`: dashboard and CRUD screens.
- `app/Http/Requests`: input and upload validation.
- `app/Http/Middleware/EnsureAdmin.php`: explicit administrator authorization.
- `app/Services/FamilyTree.php`: loads members once, assembles visibility by parent, validates cycles, and updates descendant generations within a transaction.
- `app/Services/SafeHtml.php`: allowlist HTML sanitizer; strips scripts, event handlers, unsafe links, and unsupported markup.
- `resources/views/components/tree-node.blade.php`: recursively renders already-loaded groups with CSS connectors, without recursive database queries or fixed generation counts.
- `resources/js/app.js`: tree controls, member detail dialog, search focusing, parent selector filtering, and the simple rich-text editor.
- `database/seeders/DatabaseSeeder.php`: non-destructive starting content and settings.

Public routes: `/`, `/bansawali`, `/gaihre-parichaya`, `/history`, `/blog`, `/blog/{slug}`, `/about`, `/contact`, `/sitemap.xml`.

Admin routes: `/admin/login`, `/admin`, `/admin/members`, `/admin/members/tree`, `/admin/posts`, `/admin/content`, `/admin/settings`. All writes use CSRF protection and authorization. Authentication is session-based with rate limiting. Public HTML escapes user text and sanitizes rich text. Image storage uses Laravel's public disk.

## Verification

This machine has PDO MySQL but no PDO SQLite. `phpunit.xml` targets a separate `bamsawali_test` database. Create it before running tests; never point tests at your live database.

```sh
mysql -u root -e 'CREATE DATABASE IF NOT EXISTS bamsawali_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'
php artisan test --compact
vendor/bin/pint --format agent
npm run build
```

Tests cover authorization, login throttling, CRUD, cycle rejection, safe deletion, automatic generations, hidden branches, search, one-query tree loading, deep trees, image upload/removal, scheduled/draft visibility, sanitization, content/settings updates, and non-destructive seeding.
