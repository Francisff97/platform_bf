<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage; // 👈
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information (con upload avatar).
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Valida i campi gestiti da ProfileUpdateRequest (nome, email, ecc.)
        $user->fill($request->validated());

        // Valida e gestisci l'avatar (opzionale)
        if ($request->hasFile('avatar')) {
            $request->validate([
                'avatar' => ['image','max:4096'], // max ~4MB
            ]);

            // Elimina l'eventuale avatar precedente
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            // Salva il nuovo avatar in storage/app/public/avatars
            $user->avatar_path = $request->file('avatar')->store('avatars', 'public');
        }

        // Se l'email cambia, invalida la verifica
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        // (Opzionale) elimina l'avatar dallo storage
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
