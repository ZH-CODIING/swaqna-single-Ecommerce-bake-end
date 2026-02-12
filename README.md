
# Single E-commerce

![PHP](https://img.shields.io/badge/PHP-8.2%2B-brightgreen)
![Laravel](https://img.shields.io/badge/Laravel-^12.0-red)
![Tests](https://img.shields.io/badge/tests-passing-brightgreen)

Professional single-vendor e-commerce platform (Laravel)

---

هذا المشروع هو تطبيق متجر إلكتروني كامل مبني على Laravel، مُهيأ ليعمل كمنصة متجر واحدة (single-vendor). يحتوي على إدارة المنتجات، الطلبات، الشحن، كوبونات الخصم، ومزايا أخرى مفيدة للتشغيل التجاري.

This repository contains a single-vendor e-commerce application built with the Laravel framework. It provides product management, orders, shipping, coupon support, user notifications, and integrations commonly needed for a production shop.

## Table of contents

- About / نبذة
- Tech stack / التقنيات
- Requirements / المتطلبات
- Quick setup (Windows - PowerShell) / تثبيت سريع (PowerShell)
- Development / التطوير
- Testing / الاختبار
- Deployment notes / ملاحظات النشر
- Project structure / هيكل المشروع
- Contributing / المساهمة
- License / الترخيص

## About / نبذة

Purpose: A Laravel-based e-commerce skeleton tailored for single-vendor stores with common e-commerce features (orders, shipments, payments, coupons, reviews, pages, notifications).

الهدف: هيكل جاهز لمتجر إلكتروني بواجهة إدارية وميزات أساسية لبيع المنتجات وإدارة الطلبات والشحن والدفع.

## Tech stack / التقنيات

- Backend: PHP ^8.2, Laravel ^12.0
- Frontend build: Vite + Tailwind CSS
- Queue / Cache: Predis (Redis client) available
- Extras: Google API client, Guzzle HTTP client, Laravel Sanctum, Socialite, maatwebsite/excel, simple-qrcode

This information comes from `composer.json` and `package.json` in the project root.

## Requirements / المتطلبات

- PHP 8.2+
- Composer
- Node.js (16+) and npm
- A database (MySQL, MariaDB, or SQLite for quick testing)
- Redis (recommended for queue & cache) or allow Predis fallback
- Git (for version control)

## Quick setup (Windows - PowerShell)

The following commands assume you're on Windows PowerShell (the repository root is the folder containing `artisan`). Adjust for Linux/macOS as needed.

1. Clone & install PHP dependencies

```powershell
git clone <repo-url> your-project
cd your-project
composer install --no-interaction --prefer-dist
```

2. Copy environment file and generate app key

```powershell
copy .env.example .env
php artisan key:generate
```

3. Configure `.env` (DB, MAIL, PAYMENT, API KEYS, etc.)

Edit the `.env` file and set database credentials and other third-party keys. Example for SQLite quick test:

```powershell
# create SQLite file
if (-not (Test-Path database\database.sqlite)) { New-Item database\database.sqlite -ItemType File }
# ensure .env contains DB_CONNECTION=sqlite and DB_DATABASE=database/database.sqlite
```

4. Run migrations and (optional) seeders

```powershell
php artisan migrate --seed
```

5. Install JS dependencies and run Vite (dev)

```powershell
npm install
npm run dev
```

6. Serve the application (local)

```powershell
php artisan serve
```

Notes: The repository includes an npm script and composer `dev` script that runs `concurrently` to start server, queue listener, and vite together. See `composer.json` scripts for details.

## Development / التطوير

- Code style: The project includes `laravel/pint` for formatting/linting. Run `./vendor/bin/pint` (or the equivalent Windows invocation) to check/format code.
- Queues: `php artisan queue:listen` or use `queue:work` with a process manager (Supervisor) in production.
- Background jobs: Jobs are under `app/Jobs` and `app/Console/Commands` for scheduled tasks.

Helpful composer scripts (defined in `composer.json`):

- `composer run dev` — convenience script that starts `php artisan serve`, `php artisan queue:listen`, and `npm run dev` in parallel (via `concurrently`).
- `composer test` — clears config cache and runs `php artisan test`.

## Testing / الاختبار

Run the automated tests with PHPUnit (project ships with PHP Unit config):

```powershell
composer test
# or
php artisan test
```

Add tests under `tests/Feature` and `tests/Unit`.

## Deployment notes / ملاحظات النشر

Production checklist:

1. Use a proper web server (Nginx/Apache) and PHP-FPM.
2. Set `APP_ENV=production` and `APP_DEBUG=false` in `.env`.
3. Use a persistent queue worker (Supervisor) for `queue:work`.
4. Use Redis for cache/queue if possible. Predis is available in composer dependencies.
5. Build assets: `npm ci && npm run build` (Vite build) and ensure `public/build` served.
6. Create `storage` symlink and set correct permissions:

```powershell
php artisan storage:link
# set folder permissions appropriately on the server
```

7. Use an SSL certificate and configure secure cookies and settings.

## Project structure / هيكل المشروع

- `app/` — Application code (Controllers, Models, Jobs, Notifications, Services)
- `routes/` — Routes: `web.php`, `api.php`, and others
- `resources/views/` — Blade templates
- `resources/js` / `resources/css` — Frontend assets (Vite)
- `database/migrations` — Migrations
- `tests/` — Automated tests

Some important app-level items found in the repository:
- `app/Models` — product, order, shipping, coupon models, etc.
- `app/Jobs` — background jobs (email, notifications, shipping tasks)
- `app/Channels` — custom notification channels (WhatsApp integration present)

## Contributing / المساهمة

1. Fork the repository
2. Create a feature branch: `git checkout -b feat/your-feature`
3. Add tests for new behavior
4. Lint/format code with Pint
5. Open a pull request describing the change

If you add any breaking changes, please document migration steps in the PR.

## Contact

If you need help or want to report issues, open an issue in this repository with details (environment, steps to reproduce, logs).

## License / الترخيص

This project is released under the MIT License — see the `LICENSE` file (if present) or `composer.json` license field.

---

Optional next steps I can do for you:

- Add badges (PHP, Laravel, tests) at the top of the README
- Add CI GitHub Actions workflow for tests and lint checks
- Add a short developer quickstart script (PowerShell) in `scripts/`

If you want any of the above, tell me which and I'll add it.
<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
