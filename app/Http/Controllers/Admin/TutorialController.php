<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tutorial;
use App\Models\Pack;
use App\Models\Coach;
use Illuminate\Http\Request;

class TutorialController extends Controller
{
    public function index()
    {
        $packs  = Pack::orderBy('title')->withCount('tutorials')->get();
        $coaches= Coach::orderBy('name')->withCount('tutorials')->get();

        $tutorials = Tutorial::latest()->with('tutorialable')->paginate(20);

        return view('admin.addons.tutorials.index', compact('packs','coaches','tutorials'));
    }

    public function create()
    {
        $packs = Pack::orderBy('title')->get(['id','title']);
        $coaches = Coach::orderBy('name')->get(['id','name']);
        return view('admin.addons.tutorials.form', [
            'tutorial' => new Tutorial(),
            'packs' => $packs,
            'coaches' => $coaches,
            'mode' => 'create',
        ]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'title' => ['required','string','max:255'],
            'provider' => ['nullable','in:youtube,vimeo,url'],
            'video_url' => ['required','url','max:1024'],
            'is_public' => ['boolean'],
            'sort_order' => ['nullable','integer','min:0'],
            'target_type' => ['required','in:pack,coach'],
            'target_id'   => ['required','integer'],
        ]);

        $tutorial = new Tutorial();
        $tutorial->fill($data);
        $tutorial->is_public = (bool)($data['is_public'] ?? false);

        if ($data['target_type'] === 'pack') {
            $entity = Pack::findOrFail($data['target_id']);
        } else {
            $entity = Coach::findOrFail($data['target_id']);
        }
        $entity->tutorials()->save($tutorial);

        return redirect()->route('admin.addons.tutorials')->with('success','Tutorial creato.');
    }

    public function edit(Tutorial $tutorial)
    {
        $packs = Pack::orderBy('title')->get(['id','title']);
        $coaches = Coach::orderBy('name')->get(['id','name']);
        return view('admin.addons.tutorials.form', [
            'tutorial'=>$tutorial,
            'packs'=>$packs,
            'coaches'=>$coaches,
            'mode'=>'edit',
        ]);
    }

    public function update(Request $r, Tutorial $tutorial)
    {
        $data = $r->validate([
            'title' => ['required','string','max:255'],
            'provider' => ['nullable','in:youtube,vimeo,url'],
            'video_url' => ['required','url','max:1024'],
            'is_public' => ['boolean'],
            'sort_order' => ['nullable','integer','min:0'],
            'target_type' => ['required','in:pack,coach'],
            'target_id'   => ['required','integer'],
        ]);

        $tutorial->fill($data);
        $tutorial->is_public = (bool)($data['is_public'] ?? false);

        // Se è cambiato il target, ri-aggancia
        $currentKey = $tutorial->tutorialable_type.'#'.$tutorial->tutorialable_id;
        $newKey = ($data['target_type']==='pack' ? Pack::class : Coach::class) . '#'.$data['target_id'];
        if ($currentKey !== $newKey) {
            $tutorial->tutorialable()->dissociate();
            $entity = $data['target_type']==='pack'
                ? Pack::findOrFail($data['target_id'])
                : Coach::findOrFail($data['target_id']);
            $entity->tutorials()->save($tutorial);
        }
        $tutorial->save();

        return redirect()->route('admin.addons.tutorials')->with('success','Tutorial aggiornato.');
    }

    public function destroy(Tutorial $tutorial)
    {
        $tutorial->delete();
        return back()->with('success','Tutorial eliminato.');
    }
}