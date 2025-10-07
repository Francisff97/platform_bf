<?php

// DISCORD PUBLIC //
use App\Http\Controllers\DiscordPublicController;
use App\Http\Middleware\FeatureGate;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Support\FeatureFlags;
// routes/web.php (o routes/admin.php se lo usi)
use App\Http\Controllers\Admin\DiscordAddonsController;

// Partner Controller //
use App\Http\Controllers\Admin\PartnerController;

// BOT
use App\Http\Controllers\DiscordController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Middleware\AdminOnly;
use Illuminate\Http\Request;
use App\Services\FlagsClient;

// Public controllers
use App\Http\Controllers\PageController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PublicPackController;
use App\Http\Controllers\AboutController;

// Admin controllers
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PackController as AdminPackController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\BuilderController as AdminBuilderController;
use App\Http\Controllers\Admin\CoachController as AdminCoachController;
use App\Http\Controllers\Admin\HeroController as AdminHeroController;
use App\Http\Controllers\Admin\SlideController as AdminSlideController;
use App\Http\Controllers\Admin\AppearanceController as AdminAppearanceController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\AboutSectionController as AboutSectionController;

// User / account
use App\Http\Controllers\ProfileController;

// Cart / Checkout
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;

// Models for public listing closures
use App\Models\Builder;
use App\Models\Coach;

/*
|--------------------------------------------------------------------------
| Pagine pubbliche
|--------------------------------------------------------------------------
*/
Route::get('/', [PageController::class, 'home'])->name('home');
Route::view('/about-us','public.about')->name('about');
Route::get('/services-public', [PageController::class, 'services'])->name('services.public');

Route::get('/packs', [PublicPackController::class, 'index'])->name('packs.public');
Route::get('/packs/{slug}', [PageController::class, 'packShow'])->name('packs.show');

Route::get('/contacts', [PageController::class, 'contact'])->name('contacts');
Route::post('/contacts', [ContactController::class, 'submit'])->name('contacts.submit');

/* Builders pubblici */
Route::get('/builders', function () {
    $builders = Builder::latest()->paginate(12);
    return view('public.builders.index', compact('builders'));
})->name('builders.index');

Route::get('/builders/{slug}', function ($slug) {
    $builder = Builder::where('slug', $slug)->firstOrFail();
    $packs   = $builder->packs()->where('status','published')->latest()->paginate(12);
    return view('public.builders.show', compact('builder','packs'));
})->name('builders.show');

/* Coaches pubblici */
Route::get('/coaches', function () {
    $coaches = Coach::latest()->paginate(12);
    return view('public.coaches.index', compact('coaches'));
})->name('coaches.index');

Route::get('/coaches/{slug}', function ($slug) {
    $coach = Coach::where('slug',$slug)->firstOrFail();
    return view('public.coaches.show', compact('coach'));
})->name('coaches.show');

use App\Http\Controllers\DiscordFeedController;
Route::get('/news', [DiscordFeedController::class,'news'])->name('discord.news');
Route::get('/feedback', [DiscordFeedController::class,'feedback'])->name('discord.feedback');

/*
|--------------------------------------------------------------------------
| Auth (Breeze)
|--------------------------------------------------------------------------
*/
if (file_exists(__DIR__.'/auth.php')) {
    require __DIR__.'/auth.php';
}

Route::middleware('auth')->group(function () {
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',[ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',[ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Area ADMIN (auth + admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', AdminOnly::class])
    ->prefix('admin')->name('admin.')
    ->group(function () {
        // Add-on Discord (queste esistevano già: lasciate invariate)
        Route::prefix('addons')->name('addons.')->group(function () {
            Route::get('discord', [\App\Http\Controllers\Admin\DiscordAddonController::class,'index'])
                ->name('discord');
            Route::post('discord', [\App\Http\Controllers\Admin\DiscordAddonController::class,'save'])
                ->name('discord.save');
            Route::get('discord/sync', [\App\Http\Controllers\Admin\DiscordAddonController::class,'sync'])
                ->name('discord.sync');
        });

        // Admin > Analytics (GTM)
        Route::get('analytics', [\App\Http\Controllers\Admin\AnalyticsController::class, 'edit'])
            ->name('analytics.edit');
        Route::post('analytics', [\App\Http\Controllers\Admin\AnalyticsController::class, 'update'])
            ->name('analytics.update');

        Route::resource('about', AboutSectionController::class)
            ->parameters(['about' => 'about'])
            ->names('about');

        Route::get('/', \App\Http\Controllers\Admin\DashboardController::class)->name('dashboard');
        Route::resource('partners', \App\Http\Controllers\Admin\PartnerController::class)->except(['show']);
        Route::resource('packs',    \App\Http\Controllers\Admin\PackController::class)->except(['show']);
        Route::resource('services', \App\Http\Controllers\Admin\ServiceController::class)->except(['show']);
        Route::resource('builders', \App\Http\Controllers\Admin\BuilderController::class)->except(['show']);
        Route::resource('coaches',  \App\Http\Controllers\Admin\CoachController::class)->except(['show']);
        Route::resource('heroes',   \App\Http\Controllers\Admin\HeroController::class)->except(['show']);
        Route::resource('slides',   \App\Http\Controllers\Admin\SlideController::class)->except(['show']);
        Route::get('appearance',  [\App\Http\Controllers\Admin\AppearanceController::class,'edit'])->name('appearance.edit');
        Route::post('appearance', [\App\Http\Controllers\Admin\AppearanceController::class,'update'])->name('appearance.update');

        Route::resource('orders', \App\Http\Controllers\Admin\OrderController::class)
            ->only(['index','show','destroy']);

        Route::post('orders/{order}/mark-paid', [
            \App\Http\Controllers\Admin\OrderController::class,
            'markPaid'
        ])->name('orders.markPaid');

        Route::post('orders/{order}/mark-canceled', [
            \App\Http\Controllers\Admin\OrderController::class,
            'markCanceled'
        ])->name('orders.markCanceled');

        Route::resource('categories', \App\Http\Controllers\CategoryController::class)->except(['show']);

        // Platform info
        Route::get('platform-info', [\App\Http\Controllers\Admin\PlatformInfoController::class, 'index'])
            ->name('platform.info');
        Route::post('platform-info/refresh', [\App\Http\Controllers\Admin\PlatformInfoController::class, 'refresh'])
            ->name('platform.info.refresh');

        // ------------------------------------------------------------------
        // ADD-ONS: **sempre registrati**, accesso protetto da FeatureGate
        // ------------------------------------------------------------------

        // EMAIL TEMPLATES
        Route::prefix('addons')->middleware(FeatureGate::class.':addons,email_templates')->group(function () {
            Route::get('/email-templates', [EmailTemplateController::class, 'index'])
                ->name('addons.email-templates');
            Route::get('/email-templates/create', [EmailTemplateController::class, 'create'])
                ->name('addons.email-templates.create');
            Route::post('/email-templates', [EmailTemplateController::class, 'store'])
                ->name('addons.email-templates.store');
            Route::get('/email-templates/{template}/edit', [EmailTemplateController::class, 'edit'])
                ->name('addons.email-templates.edit');
            Route::put('/email-templates/{template}', [EmailTemplateController::class, 'update'])
                ->name('addons.email-templates.update');
            Route::delete('/email-templates/{template}', [EmailTemplateController::class, 'destroy'])
                ->name('addons.email-templates.destroy');
        });

        // TUTORIALS
        Route::prefix('addons')->middleware(FeatureGate::class.':addons,tutorials')->name('addons.')->group(function () {
            Route::get('tutorials', [\App\Http\Controllers\Admin\TutorialController::class,'index'])
                ->name('tutorials');
            Route::get('tutorials/create', [\App\Http\Controllers\Admin\TutorialController::class,'create'])
                ->name('tutorials.create');
            Route::post('tutorials', [\App\Http\Controllers\Admin\TutorialController::class,'store'])
                ->name('tutorials.store');
            Route::get('tutorials/{tutorial}/edit', [\App\Http\Controllers\Admin\TutorialController::class,'edit'])
                ->name('tutorials.edit');
            Route::put('tutorials/{tutorial}', [\App\Http\Controllers\Admin\TutorialController::class,'update'])
                ->name('tutorials.update');
            Route::delete('tutorials/{tutorial}', [\App\Http\Controllers\Admin\TutorialController::class,'destroy'])
                ->name('tutorials.destroy');
        });

        // NOTA: blocco "discord_integration" condizionale rimosso (duplicava le rotte già definite sopra)
    });

/*
|--------------------------------------------------------------------------
| Carrello & Checkout (PayPal)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/cart',                   [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add-pack/{pack}',  [CartController::class, 'addPack'])->name('cart.add.pack');
    Route::post('/cart/add-coach/{coach}',[CartController::class, 'addCoach'])->name('cart.add.coach');
    Route::post('/cart/remove/{key}',     [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/clear',            [CartController::class, 'clear'])->name('cart.clear');

    Route::get('/checkout',         [CheckoutController::class, 'checkout'])->name('checkout.index');
    Route::post('/checkout/paypal', [CheckoutController::class, 'createOrderFromCart'])->name('checkout.paypal');
    Route::get('/checkout/capture', [CheckoutController::class, 'captureCart'])->name('checkout.capture');
    Route::get('/checkout/cancel',  [CheckoutController::class, 'cancel'])->name('checkout.cancel');
    Route::get('/checkout/success', fn () => view('checkout.success'))->name('checkout.success');
});

// Compat: vecchi riferimenti a route('dashboard')
Route::get('/dashboard', function () {
    if (auth()->check() && auth()->user()->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('home');
})->name('dashboard');

// --- Create Admin (solo guest) ---
Route::middleware('guest')->group(function () {
    Route::get('/create-admin', [RegisteredUserController::class, 'showAdminForm'])
        ->name('register.admin');

    Route::post('/create-admin', [RegisteredUserController::class, 'storeAdmin'])
        ->name('register.admin.store'); // 👈 nome esplicito per la POST
});

Route::get('/_debug/flags', function(App\Services\FlagsClient $fc){
    return response()->json($fc->get());
});

// Admin Addons index & update (restano come li avevi)
Route::middleware(['auth']) // aggiungi tuo middleware admin se ce l’hai
    ->prefix('admin/addons')
    ->group(function () {

        Route::get('/', function (FlagsClient $flags) {
            $data = $flags->get(); // { features: [...] }
            return view('admin.addons.index', [
                'features' => $data['features'] ?? [],
            ]);
        })->name('admin.addons.index');

        Route::post('/', function (Request $r, FlagsClient $flags) {
            $payload = [
                'addons'               => $r->boolean('addons'),
                'email_templates'      => $r->boolean('email_templates'),
                'discord_integration'  => $r->boolean('discord_integration'),
                'tutorials'            => $r->boolean('tutorials'),
                'announcements'        => $r->boolean('announcements'),
            ];

            $ok = $flags->set($payload);

            return back()->with('success', $ok
                ? 'Flags aggiornati sul server.'
                : 'Impossibile aggiornare i flag (server flags non raggiungibile).');
        })->name('admin.addons.update');
    });

// DISCORD PUBLIC ANNOUNCMENTS & FEEDBACK //
Route::middleware(FeatureGate::class.':addons,discord_integration')->group(function () {
    Route::get('/announcements', [DiscordPublicController::class, 'announcements'])
        ->name('announcements');
    Route::get('/feedback', [DiscordPublicController::class, 'feedback'])
        ->name('feedback');
});

// routes/web.php (solo dev)
Route::get('/_dev/force-success/{order}', function (\App\Models\Order $order) {
    $order->update(['status' => 'paid']);
    return redirect()->route('checkout.success', ['order' => $order->id]);
});

// routes/web.php (aiuto temporaneo)
Route::get('/_debug/flags_nocache', function () {
    $base = rtrim(env('FLAGS_BASE_URL',''),'/');
    $slug = env('FLAGS_INSTALLATION_SLUG', env('FLAGS_SLUG','demo'));
    $res = \Illuminate\Support\Facades\Http::acceptJson()->get("$base/api/installations/$slug/flags");
    return $res->json();
});

Route::get('/_debug/flags-final', function () {
    return FeatureFlags::all(); // quello che usa la tua UI
});

Route::get('/_debug/flags-raw', function (FlagsClient $c) {
    return $c->get(); // la fetch cruda (client)
});

Route::post('/api/flags/purge', function (Request $r) {
    $slug = $r->input('slug', env('FLAGS_INSTALLATION_SLUG', env('FLAGS_SLUG','demo')));
    Cache::forget("features.remote.$slug");
    Cache::forget("features.remote.{$slug}");
    Cache::forget("features.$slug");
    return ['ok' => true, 'purged' => $slug];
});

Route::get('/about', [AboutController::class, 'show'])->name('about');
Route::get('/about-us', [AboutController::class, 'show']);

Route::post('/flags/debug', function (\Illuminate\Http\Request $r) {
    $secret = env('FLAGS_SIGNING_SECRET', '');
    return [
        'env_secret' => $secret,
        'body' => $r->getContent(),
        'header_sig' => $r->header('X-Signature'),
        'expected_sig' => hash_hmac('sha256', $r->getContent(), $secret),
        'match' => hash_equals(hash_hmac('sha256', $r->getContent(), $secret), $r->header('X-Signature')),
    ];
});
