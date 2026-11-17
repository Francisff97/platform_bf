<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;   // ✅ facade corretta
use Illuminate\Support\Facades\View;    // ✅ per i composer
use App\Support\Cart;                   // se usi Cart::count() nel composer
use App\Models\SiteSetting;       
use App\Services\FlagsClient;      // per leggere la currency
use App\Models\PrivacySetting;
use Illuminate\Support\Facades\URL;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
          $helpers = app_path('helpers.php');
        if (is_file($helpers)) {
            require_once $helpers;
        }

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        
        View::composer('*', function ($view) {
        static $privacy = null;
        if ($privacy === null) $privacy = PrivacySetting::first();
        $view->with('privacySettings', $privacy);
    });
        // Condivido cartCount con tutte le view (se ti serve)
        View::composer('*', function ($view) {
            try {
                $view->with('cartCount', Cart::count());
            } catch (\Throwable $e) {
                // in fase di install/migrazioni può non esistere la tabella; evita di rompere la render
                $view->with('cartCount', 0);
            }
        });
        view()->composer('*', function ($view) {
    $site = \App\Models\SiteSetting::first();
    $view->with('siteDiscord', $site?->discord_link ?? null);
});

        // Direttiva @money($cents) o @money($cents, 'EUR')
        Blade::directive('money', function ($expression) {
            // Semplice: passa gli argomenti così come scritti alla nostra utility
            return "<?php echo \\App\\Support\\Money::formatCents($expression); ?>";
        });

        // Variabile globale con la valuta del sito
        View::composer('*', function ($view) {
            try {
                $site = SiteSetting::first();
                $view->with('siteCurrency', strtoupper($site->currency ?? 'EUR'));
            } catch (\Throwable $e) {
                $view->with('siteCurrency', 'EUR');
            }
        });

        // Direttiva condizionale @feature('chiave')
        // Blade::if('feature', function ($key) {
        //     try {
        //         return \App\Support\FeatureFlags::enabled($key);
        //     } catch (\Throwable $e) {
        //         return false;
        //     }
        // });
        try {
        $fc = app(FlagsClient::class);
        $data = $fc->get(); // { features: {...} }
        View::share('features', $data['features'] ?? config('features'));
    } catch (\Throwable $e) {
        View::share('features', config('features'));
        // opzionale: \Log::warning('Flags load failed', ['e'=>$e->getMessage()]);
    }
}
}
