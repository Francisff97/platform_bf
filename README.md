# BaseForge — storefront & admin platform for gaming communities

**BaseForge** is a Laravel platform that lets Base Builder communities sell **packs, services and coaching sessions** online. Each community gets its own branded storefront and a full admin panel, while a central server toggles premium add-ons per installation.

> The project is no longer online: this repository is kept as a portfolio piece.

## Features

**Storefront**
- Packs, services, builders and coaches, each with its own page and slug
- Cart with quantities, discount coupons and checkout with **PayPal** (sandbox/live)
- News and feedback pages fed by the community's **Discord** channels
- SEO basics out of the box: sitemap, robots.txt, per-page meta, WebP images
- Installable as a **PWA**

**Admin panel**
- Dashboard with orders and analytics
- CRUD for packs, services, categories, builders, coaches (with price tiers), partners and tutorials
- Appearance editor: brand, currency, hero sections, slides, About sections
- Editable email templates, CSV import, Google Tag Manager setting
- Role-based access with Spatie Permission and a read-only demo mode

**Add-ons & feature flags**
- Premium modules (Discord integration, email templates, tutorials, announcements) switched on per installation
- Flags are fetched from a separate flags server ([flags-server](https://github.com/Francisff97/flags-server)) with HMAC-signed requests and local caching

## Tech stack

Laravel 11 · PHP 8.3 · Blade · Tailwind CSS · Vite · MySQL · Spatie Permission · PayPal · Discord webhooks · Docker

## Getting started

```bash
composer install
npm install && npm run build

cp .env.example .env
php artisan key:generate

# set DB_* in .env, then:
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

`--seed` creates a demo admin from `AdminUserSeeder` (`admin@example.com` / `password`): change it right away, or skip the seeder and create the first administrator from `/create-admin`, which is disabled as soon as an admin exists.

Optional services (PayPal, Discord, flags server, mail) are configured through the variables in `.env.example`.

A `Dockerfile` (PHP 8.3 + Apache) is included for container deployments behind a reverse proxy.

## Project structure

```
app/Http/Controllers/          # storefront: packs, cart, checkout, PayPal, Discord feed
app/Http/Controllers/Admin/    # admin panel modules
app/Http/Controllers/Integrations/  # Discord and flags webhooks
app/Services/                  # FlagsClient, DiscordClient, CsvImporter, email templates
database/migrations/           # schema
resources/views/               # Blade views (public + admin)
routes/web.php                 # routes
```

## Related repositories

- [docs](https://github.com/Francisff97/docs) — user guide (Docusaurus)
- [flags-server](https://github.com/Francisff97/flags-server) — feature-flag API per installation
- [baseforge](https://github.com/Francisff97/baseforge) — platform info and assets

---

Designed and developed by [Francesco Ferri](https://www.francescoferri.me) — Francesco Design.
