# Laravel Skeleton (Packs, Services, Admin, Public Pages)
:)
Questo zip contiene **solo i file applicativi** (controller, model, migrazioni, viste, rotte, seeder)
da **copiare in un progetto Laravel nuovo** (Laravel 11) con Breeze + Spatie Permission.

## Setup rapido (nuovo progetto)

```bash
# 1) Crea progetto e dipendenze
composer create-project laravel/laravel blueprintlike
cd blueprintlike

composer require laravel/breeze --dev
php artisan breeze:install blade
npm install && npm run build

composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# 2) Copia i file di questo zip nella root del progetto, sovrascrivendo se richiesto
# (Controllers, Models, database/migrations, resources/views, routes/web.php, seeders, ecc.)

# 3) Aggiungi il middleware di Spatie in app/Http/Kernel.php (array $routeMiddleware)
#    'role' => \Spatie\Permission\Middlewares\RoleMiddleware::class,
#    'permission' => \Spatie\Permission\Middlewares\PermissionMiddleware::class,

# 4) Nel modello User aggiungi il trait HasRoles:
#    use Spatie\Permission\Traits\HasRoles;
#    class User extends Authenticatable { use HasRoles; ... }

# 5) Configura il DB in .env (vedi sezione sotto)

# 6) Migrazioni e seed
php artisan migrate
php artisan db:seed --class=AdminUserSeeder
php artisan storage:link

# 7) Avvio
php artisan serve
# Login: admin@example.com / password
```

## Collegare il DB (env)

Apri `.env` e imposta i parametri del database MySQL:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=blueprintlike
DB_USERNAME=tuoutente
DB_PASSWORD=tuapassword
```
Poi esegui le migrazioni come sopra.

## Test funzionale

1. Vai su `http://127.0.0.1:8000/` → home.
2. Vai su `/login` → accedi con l'utente admin creato dal seeder.
3. Vai su `/admin` → dashboard visibile solo con ruolo `admin`.
4. Crea un Pack in `/admin/packs` (status `published`) e verifica che compaia su `/packs`.
5. Crea o modifica Services in `/admin/services` e verifica `/services`.

## Rotte

- Pubblico: `/` (home), `/about-us`, `/services`, `/packs`, `/packs/{slug}`, `/contacts`.
- Admin: `/admin` (dashboard), `/admin/packs`, `/admin/services`.
- Auth di Breeze: `/login`, `/register`, etc.

> Nota: questo skeleton usa **Blade**. Puoi aggiungere React / Inertia in seguito se necessario.
