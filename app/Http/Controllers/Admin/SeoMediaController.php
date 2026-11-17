<?php

namespace App\Http\Controllers\Admin;

// app/Http/Controllers/Admin/SeoMediaController.php
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Controller;
use App\Models\MediaAsset;
use Illuminate\Http\Request;

class SeoMediaController extends Controller
{
    public function index(Request $r)
    {
        $q = MediaAsset::query()
            ->when($r->filled('search'), fn($q)=>$q->where('path','like','%'.$r->string('search').'%'));

        $assets = $q->orderBy('path')->paginate(30);

        return view('admin.seo.media.index', compact('assets'));
    }

    public function edit(MediaAsset $mediaAsset)
    {
        return view('admin.seo.media.edit', compact('mediaAsset'));
    }

    public function update(Request $r, MediaAsset $mediaAsset)
    {
        $data = $r->validate([
            'alt_text' => ['nullable','string','max:512'],
            'is_lazy'  => ['nullable','boolean'],
        ]);

        $mediaAsset->fill([
            'alt_text' => $data['alt_text'] ?? null,
            'is_lazy'  => (bool)($data['is_lazy'] ?? false),
        ])->save();

        return back()->with('success','Media updated.');
    }

    public function bulk(Request $r)
    {
        $data = $r->validate([
            'ids'      => ['required','array'],
            'ids.*'    => ['integer','exists:media_assets,id'],
            'is_lazy'  => ['nullable','boolean'],
            'alt_text' => ['nullable','string','max:512'],
        ]);

        if ($r->has('is_lazy')) {
            MediaAsset::whereIn('id',$data['ids'])->update(['is_lazy'=>(bool)$data['is_lazy']]);
        }
        if ($r->filled('alt_text')) {
            MediaAsset::whereIn('id',$data['ids'])->update(['alt_text'=>$data['alt_text']]);
        }

        return back()->with('success','Bulk applied.');
    }
    

public function sync()
{
    // puoi anche passare opzioni al comando:
    // Artisan::call('seo:media-backfill', ['--no-prune' => true]);
    Artisan::call('seo:media-backfill');

    return back()->with('success', 'Media synchronized successfully!');
}
}
