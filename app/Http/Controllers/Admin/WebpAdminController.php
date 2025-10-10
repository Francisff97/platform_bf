<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class WebpAdminController extends Controller
{
    public function index()
    {
        return view('admin.webp');
    }

    public function generateMissing(Request $request)
    {
        try {
            // NIENTE --only-missing: la maggior parte dei nostri comandi già
            // salta i file esistenti se non c'è --force.
            Artisan::call('images:to-webp', [
                '--disk'    => 'public',
                '--quality' => 75,
                // nessun --force  => solo mancanti
            ]);
            $out = trim(Artisan::output());
            return back()->with('success', $out !== '' ? $out : 'Generazione WebP (solo mancanti) completata.');
        } catch (Throwable $e) {
            return back()->with('error', 'Errore durante la generazione: '.$e->getMessage());
        }
    }

    public function rebuildAll(Request $request)
    {
        try {
            Artisan::call('images:to-webp', [
                '--disk'    => 'public',
                '--quality' => 75,
                '--force'   => true,   // rigenera tutti
            ]);
            $out = trim(Artisan::output());
            return back()->with('success', $out !== '' ? $out : 'Rigenerazione WebP completata (tutti i file).');
        } catch (Throwable $e) {
            return back()->with('error', 'Errore durante la rigenerazione: '.$e->getMessage());
        }
    }
}