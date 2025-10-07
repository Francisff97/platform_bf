<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PartnerController extends Controller
{
    public function index()
    {
        $partners = Partner::query()->orderBy('order')->paginate(24);
        return view('admin.partners.index', compact('partners'));
    }

    public function create()
    {
        return view('admin.partners.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'   => ['required','string','max:255'],
            'url'    => ['nullable','url','max:255'],
            'order'  => ['nullable','integer'],
            'status' => ['required','in:draft,published'],
            'logo'   => ['nullable','image','mimes:jpg,jpeg,png,webp,avif','max:4096'],
        ]);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('partners','public');
        }

        Partner::create($data);

        return redirect()->route('admin.partners.index')->with('success','Partner created.');
    }

    public function edit(Partner $partner)
    {
        return view('admin.partners.edit', compact('partner'));
    }

    public function update(Request $request, Partner $partner)
    {
        $data = $request->validate([
            'name'   => ['required','string','max:255'],
            'url'    => ['nullable','url','max:255'],
            'order'  => ['nullable','integer'],
            'status' => ['required','in:draft,published'],
            'logo'   => ['nullable','image','mimes:jpg,jpeg,png,webp,avif','max:4096'],
            'remove_logo' => ['nullable','boolean'],
        ]);

        if ($request->boolean('remove_logo') && $partner->logo_path) {
            Storage::disk('public')->delete($partner->logo_path);
            $data['logo_path'] = null;
        }
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('partners','public');
            if ($partner->logo_path) {
                Storage::disk('public')->delete($partner->logo_path);
            }
            $data['logo_path'] = $path;
        }

        $partner->update($data);

        return redirect()->route('admin.partners.index')->with('success','Partner updated.');
    }

    public function destroy(Partner $partner)
    {
        if ($partner->logo_path) {
            Storage::disk('public')->delete($partner->logo_path);
        }
        $partner->delete();
        return back()->with('success','Partner removed.');
    }
}
