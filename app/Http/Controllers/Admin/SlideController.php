<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Slide;
use Illuminate\Http\Request;

class SlideController extends Controller {
  public function index(){ $slides = Slide::orderBy('sort_order')->get(); return view('admin.slides.index',compact('slides')); }
  public function create(){ return view('admin.slides.create'); }
  public function store(Request $r){
    $data = $r->validate([
      'title'=>'nullable|max:180','subtitle'=>'nullable|max:240',
      'image'=>'required|image|max:8192',
      'cta_label'=>'nullable|max:60','cta_url'=>'nullable|url',
      'sort_order'=>'integer','is_active'=>'boolean'
    ]);
    $data['image_path']=$r->file('image')->store('slides','public');
    $data['is_active']=$r->boolean('is_active');
    Slide::create($data);
    return redirect()->route('admin.slides.index')->with('success','Slide creata');
  }
  public function edit(Slide $slide){ return view('admin.slides.edit',compact('slide')); }
  public function update(Request $r, Slide $slide){
    $data = $r->validate([
      'title'=>'nullable|max:180','subtitle'=>'nullable|max:240',
      'image'=>'nullable|image|max:8192',
      'cta_label'=>'nullable|max:60','cta_url'=>'nullable|url',
      'sort_order'=>'integer','is_active'=>'boolean'
    ]);
    if($r->hasFile('image')) $data['image_path']=$r->file('image')->store('slides','public');
    $data['is_active']=$r->boolean('is_active');
    $slide->update($data);
    return back()->with('success','Slide aggiornata');
  }
  public function destroy(Slide $slide){ $slide->delete(); return back()->with('success','Slide eliminata');}
}
