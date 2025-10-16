<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request)
{
    $request->authenticate();
    $request->session()->regenerate();

    $user = $request->user();

    // Se la Demo Mode è attiva e l'utente è demo → dashboard admin
    if (config('demo.enabled') && $user && $user->is_demo) {
        return redirect()->route('admin.dashboard');
    }

    // Se è un admin vero → dashboard admin
    if ($user && strtolower((string) $user->role) === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    // Altrimenti → home classica
    return redirect()->route('home');
}

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}