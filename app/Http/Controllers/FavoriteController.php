<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FavoriteController extends Controller
{
    public function index()
    {
        $favorites = Favorite::with('product')
            ->where('user_id', session('zalina_user_id'))
            ->latest()
            ->get();

        return view('store.favorites', compact('favorites'));
    }

    public function toggle(Request $request, Product $product): JsonResponse
    {
        $favorite = Favorite::where('user_id', session('zalina_user_id'))
            ->where('product_id', $product->id)
            ->first();

        if ($favorite) {
            $favorite->delete();

            return response()->json([
                'success' => true,
                'favorited' => false,
                'message' => 'Produk dihapus dari favorit.',
            ]);
        }

        Favorite::create([
            'user_id' => session('zalina_user_id'),
            'product_id' => $product->id,
        ]);

        return response()->json([
            'success' => true,
            'favorited' => true,
            'message' => 'Produk ditambahkan ke favorit.',
        ]);
    }
}
