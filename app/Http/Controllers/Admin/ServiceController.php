<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::orderBy('order')->paginate(50);
        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        return view('admin.services.create');
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name'=>'required|max:160',
            'slug'=>'nullable|alpha_dash|unique:services,slug',
            'excerpt'=>'nullable|max:255',
            'image'=>'nullable|image|max:2048',
            'body'=>'nullable',
            'order'=>'integer|min:0',
            'status'=>'required|in:draft,published',
        ]);
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        Service::create($data);

        return redirect()->route('admin.services.index')->with('success','Service creato.');
    }

    public function edit(Service $service)
    {
        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $r, Service $service)
    {
        $data = $r->validate([
            'name'=>'required|max:160',
            'slug'=>"required|alpha_dash|unique:services,slug,{$service->id}",
            'excerpt'=>'nullable|max:255',
            'body'=>'nullable',
            'image'=>'nullable|image|max:2048',
            'order'=>'integer|min:0',
            'status'=>'required|in:draft,published',
        ]);
        
        if ($r->hasFile('image')) {
            $data['image_path'] = $r->file('image')->store('services', 'public');
          }
        $service->update($data);

        return back()->with('success','Service aggiornato.');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return back()->with('success','Service eliminato.');
    }
}
