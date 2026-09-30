<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class DiscountController extends Controller
{

    public function index()
    {
        return view('admin.discounts.index',[
            'discounts'=>Discount::latest()->get()
        ]);
    }



    public function store(Request $r)
    {

        $d = $r->validate([

            'name'=>'required|string|max:255',
            'code'=>'nullable|string|max:255',

            'type'=>'required|in:percent,fixed',

            'value'=>'required|numeric|min:0',

            'minimum_order'=>'nullable|numeric|min:0',

            'starts_at'=>'nullable|date',

            'ends_at'=>'nullable|date|after_or_equal:starts_at',

            'image'=>'nullable|image|max:2048',

            // NEW: tema warna & bentuk kartu promo di homepage
            'theme'=>'nullable|in:maroon_gold,rose,emerald,midnight,cream',

            'card_style'=>'nullable|in:ticket,banner',

        ]);



        // upload gambar promo
        if($r->hasFile('image')){

            $d['image'] = 
                $r->file('image')
                ->store('promos','public');

        }



        $d['is_active'] =
            $r->boolean('is_active',true);


        // fallback default kalau admin belum pilih tema/bentuk kartu
        $d['theme'] = $r->input('theme', 'maroon_gold');
        $d['card_style'] = $r->input('card_style', 'ticket');


        Discount::create($d);



        return back()
            ->with('success','Promo berhasil ditambahkan.');

    }





    public function update(Request $r, Discount $discount)
    {

        $d = $r->validate([

            'name'=>'required|string|max:255',
            'code'=>'nullable|string|max:255',

            'type'=>'required|in:percent,fixed',

            'value'=>'required|numeric|min:0',

            'minimum_order'=>'nullable|numeric|min:0',

            'starts_at'=>'nullable|date',

            'ends_at'=>'nullable|date|after_or_equal:starts_at',

            'image'=>'nullable|image|max:2048',

            // NEW: tema warna & bentuk kartu promo di homepage
            'theme'=>'nullable|in:maroon_gold,rose,emerald,midnight,cream',

            'card_style'=>'nullable|in:ticket,banner',

        ]);




        // jika upload gambar baru
        if($r->hasFile('image')){


            if($discount->image){

                Storage::disk('public')
                    ->delete($discount->image);

            }


            $d['image'] =
                $r->file('image')
                ->store('promos','public');

        }




        $d['is_active'] =
            $r->boolean('is_active');


        // fallback default kalau admin belum pilih tema/bentuk kartu
        $d['theme'] = $r->input('theme', $discount->theme ?? 'maroon_gold');
        $d['card_style'] = $r->input('card_style', $discount->card_style ?? 'ticket');


        $discount->update($d);



        return back()
            ->with('success','Promo diperbarui.');

    }





    public function destroy(Discount $discount)
    {


        if($discount->image){

            Storage::disk('public')
                ->delete($discount->image);

        }


        $discount->delete();



        return back()
            ->with('success','Promo dihapus.');

    }


}