<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Dove reindirizzare gli utenti dopo il login/registrazione:
     * - utenti normali -> homepage
     * - admin -> /admin (lo useremo noi nei controller/middleware)
     */
    public const HOME = '/';
    public const ADMIN_HOME = '/admin';
}