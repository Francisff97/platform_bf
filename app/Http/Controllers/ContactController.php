<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContactMessage;

class ContactController extends Controller
{
    public function submit(Request $request)
    {
        $data = $request->validate([
            'name'=>'required|string|max:120',
            'email'=>'required|email',
            'subject'=>'nullable|string|max:180',
            'message'=>'required|string|max:5000',
        ]);

        ContactMessage::create($data);

        return back()->with('success','Messaggio inviato! Ti risponderemo a breve.');
    }
}
