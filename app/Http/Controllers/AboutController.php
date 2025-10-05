<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AboutSection;

class AboutController extends Controller
{
    public function show()
    {
        // Carica SOLO le sezioni attive, ordinate
        $sections = AboutSection::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        // Passa SEMPRE $sections alla view (anche se vuoto)
        return view('public.about', ['sections' => $sections]);
    }
}