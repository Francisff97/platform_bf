<?php

// app/Http/Controllers/ContactController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactCustomerMail;
use App\Mail\ContactAdminMail;

class ContactController extends Controller
{
    public function submit(Request $request)
    {
        $data = $request->validate([
            'name'    => ['required','string','max:200'],
            'email'   => ['required','email','max:200'],
            'subject' => ['nullable','string','max:200'],
            'message' => ['required','string','max:5000'],
        ]);

        // indirizzo admin: usa quello che preferisci tra env/config
        $adminEmail = config('mail.admin')
            ?? env('MAIL_ADMIN')
            ?? config('mail.from.address');

        // invia al customer (con copia a te stesso come reply-to utile)
        Mail::to($data['email'])->send(new ContactCustomerMail($data));

        // invia all'admin
        if ($adminEmail) {
            Mail::to($adminEmail)->send(new ContactAdminMail($data));
        }

        return back()->with('success', 'Thanks! Your request was sent successfully.');
    }
}
