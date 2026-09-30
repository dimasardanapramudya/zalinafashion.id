<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller; use App\Models\HomeSlider; use Illuminate\Http\Request; use Illuminate\Support\Facades\Storage;
class HomeSliderController extends Controller {
 public function index(){return view('admin.sliders.index',['sliders'=>HomeSlider::orderBy('sort_order')->get()]);}
 public function create(){return view('admin.sliders.form',['slider'=>new HomeSlider(['theme'=>'maroon_gold','is_active'=>true])]);}
 private function rules(){return ['eyebrow'=>'nullable|max:120','title'=>'required|max:255','accent_text'=>'nullable|max:120','description'=>'nullable|max:1000','button_text'=>'nullable|max:80','button_url'=>'nullable|max:255','theme'=>'required|in:maroon_gold,rose,emerald,midnight,cream','sort_order'=>'nullable|integer|min:0','image'=>'nullable|image|max:6144'];}
 public function store(Request $r){$d=$r->validate($this->rules());$d['is_active']=$r->boolean('is_active',true);if($r->hasFile('image'))$d['image']=$r->file('image')->store('sliders','public');HomeSlider::create($d);return redirect()->route('admin.sliders.index')->with('success','Slider ditambahkan.');}
 public function edit(HomeSlider $slider){return view('admin.sliders.form',compact('slider'));}
 public function update(Request $r,HomeSlider $slider){$d=$r->validate($this->rules());$d['is_active']=$r->boolean('is_active');if($r->hasFile('image')){if($slider->image)Storage::disk('public')->delete($slider->image);$d['image']=$r->file('image')->store('sliders','public');}$slider->update($d);return back()->with('success','Slider diperbarui.');}
 public function destroy(HomeSlider $slider){if($slider->image)Storage::disk('public')->delete($slider->image);$slider->delete();return back()->with('success','Slider dihapus.');}
}
