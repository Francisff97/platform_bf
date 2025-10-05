<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view (registrazione UTENTE).
     */
    public function create(): View
    {
        return view('auth.register'); // form standard -> ruolo "user"
    }

    /**
     * Handle an incoming registration request (UTENTE).
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'user', // 👈 utente normale
        ]);

        event(new Registered($user));
        Auth::login($user);

        // dopo login: se non admin, verso home; gli admin hanno /create-admin separato
        return redirect()->route('home');
    }

    /**
     * Mostra lo stesso form di register ma in modalità ADMIN.
     */
    public function showAdminForm(): View
    {
        // Passo un flag alla view per cambiare action e testi
        return view('auth.register', ['isAdmin' => true]);
    }

    /**
     * Crea un ADMIN.
     */
    public function storeAdmin(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'admin', // 👈 ADMIN qui
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('admin.dashboard');
    }
}