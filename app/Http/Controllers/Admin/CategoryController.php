<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Menampilkan daftar kategori.
     */
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | SEMUA KATEGORI
        |--------------------------------------------------------------------------
        */

        $categories = Category::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | KATEGORI YANG TAMPIL DI BERANDA
        |--------------------------------------------------------------------------
        |
        | Data dibuat menjadi key berdasarkan home_position:
        |
        | 1 => kategori posisi pertama
        | 2 => kategori posisi kedua
        | 3 => kategori posisi ketiga
        |
        |--------------------------------------------------------------------------
        */

        $homeCategories = Category::query()
            ->where('show_on_home', true)
            ->orderBy('home_position')
            ->get()
            ->keyBy('home_position');

        return view(
            'admin.categories.index',
            compact(
                'categories',
                'homeCategories'
            )
        );
    }

    /**
     * Menyimpan kategori baru.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:categories,name',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | DATA DEFAULT KATEGORI
        |--------------------------------------------------------------------------
        */

        $data['slug'] = $this->generateUniqueSlug(
            $data['name']
        );

        $data['is_active'] = true;

        $data['sort_order'] = $this->getNextSortOrder();

        /*
        |--------------------------------------------------------------------------
        | UPLOAD GAMBAR
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('image')) {
            $data['image'] = $request
                ->file('image')
                ->store(
                    'categories',
                    'public'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | DEFAULT TIDAK DITAMPILKAN DI BERANDA
        |--------------------------------------------------------------------------
        */

        $data['show_on_home'] = false;
        $data['home_position'] = null;

        Category::create($data);

        return back()->with(
            'success',
            'Kategori berhasil ditambahkan.'
        );
    }

    /**
     * Memperbarui kategori.
     */
    public function update(
        Request $request,
        Category $category
    ) {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(
                    'categories',
                    'name'
                )->ignore($category->id),
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'remove_image' => [
                'nullable',
                'boolean',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | SLUG OTOMATIS UNIK
        |--------------------------------------------------------------------------
        */

        $data['slug'] = $this->generateUniqueSlug(
            $data['name'],
            $category->id
        );

        /*
        |--------------------------------------------------------------------------
        | UPLOAD GAMBAR BARU
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('image')) {
            /*
            | Hapus gambar lama jika tersedia.
            */

            if (
                !empty($category->image)
            ) {
                Storage::disk('public')->delete(
                    $category->image
                );
            }

            /*
            | Simpan gambar baru.
            */

            $data['image'] = $request
                ->file('image')
                ->store(
                    'categories',
                    'public'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | HAPUS GAMBAR
        |--------------------------------------------------------------------------
        */

        if (
            $request->boolean('remove_image')
            && !$request->hasFile('image')
        ) {
            if (
                !empty($category->image)
            ) {
                Storage::disk('public')->delete(
                    $category->image
                );
            }

            $data['image'] = null;
        }

        /*
        |--------------------------------------------------------------------------
        | JAGA DATA BERANDA
        |--------------------------------------------------------------------------
        |
        | Update kategori tidak mengubah pilihan Beranda.
        | Pilihan Beranda hanya diubah melalui updateHomeCategories().
        |
        |--------------------------------------------------------------------------
        */

        $category->update($data);

        return back()->with(
            'success',
            'Kategori berhasil diperbarui.'
        );
    }

    /**
     * Menyimpan pilihan kategori yang tampil di Beranda.
     *
     * Format request:
     *
     * home_categories[0] = category_id
     * home_categories[1] = category_id
     * home_categories[2] = category_id
     *
     * Posisi:
     *
     * home_categories[0] => posisi 1
     * home_categories[1] => posisi 2
     * home_categories[2] => posisi 3
     */
    public function updateHomeCategories(
        Request $request
    ) {
        $data = $request->validate([
            'home_categories' => [
                'nullable',
                'array',
                'max:3',
            ],

            'home_categories.*' => [
                'nullable',
                'integer',
                'distinct',
                'exists:categories,id',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | AMBIL PILIHAN KATEGORI
        |--------------------------------------------------------------------------
        */

        $selectedCategories = collect(
            $data['home_categories'] ?? []
        )
            ->filter(
                fn ($categoryId) =>
                    filled($categoryId)
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | VALIDASI MAKSIMAL 3 KATEGORI
        |--------------------------------------------------------------------------
        */

        if (
            $selectedCategories->count() > 3
        ) {
            return back()->with(
                'error',
                'Maksimal hanya 3 kategori yang dapat ditampilkan di Beranda.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SIMPAN DENGAN TRANSAKSI
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $selectedCategories
            ) {
                /*
                |--------------------------------------------------------------------------
                | RESET SEMUA KATEGORI
                |--------------------------------------------------------------------------
                */

                Category::query()->update([
                    'show_on_home' => false,
                    'home_position' => null,
                ]);

                /*
                |--------------------------------------------------------------------------
                | SIMPAN PILIHAN BARU
                |--------------------------------------------------------------------------
                */

                foreach (
                    $selectedCategories as $index => $categoryId
                ) {
                    Category::query()
                        ->where(
                            'id',
                            $categoryId
                        )
                        ->update([
                            'show_on_home' => true,
                            'home_position' => $index + 1,
                        ]);
                }
            }
        );

        return back()->with(
            'success',
            'Kategori Beranda berhasil diperbarui.'
        );
    }

    /**
     * Menghapus kategori.
     */
    public function destroy(
        Category $category
    ) {
        /*
        |--------------------------------------------------------------------------
        | HAPUS GAMBAR KATEGORI
        |--------------------------------------------------------------------------
        */

        if (
            !empty($category->image)
        ) {
            Storage::disk('public')->delete(
                $category->image
            );
        }

        /*
        |--------------------------------------------------------------------------
        | HAPUS DATA KATEGORI
        |--------------------------------------------------------------------------
        */

        $category->delete();

        return back()->with(
            'success',
            'Kategori berhasil dihapus.'
        );
    }

    /**
     * Membuat slug unik.
     */
    private function generateUniqueSlug(
        string $name,
        ?int $ignoreId = null
    ): string {
        $baseSlug = Str::slug($name);

        /*
        | Jika nama hanya berisi karakter yang tidak
        | dapat dibuat menjadi slug, gunakan fallback.
        */

        if (
            $baseSlug === ''
        ) {
            $baseSlug = 'kategori';
        }

        $slug = $baseSlug;

        $counter = 1;

        while (
            Category::query()
                ->where(
                    'slug',
                    $slug
                )
                ->when(
                    $ignoreId !== null,
                    function ($query) use (
                        $ignoreId
                    ) {
                        $query->where(
                            'id',
                            '!=',
                            $ignoreId
                        );
                    }
                )
                ->exists()
        ) {
            $slug =
                $baseSlug
                . '-'
                . $counter;

            $counter++;
        }

        return $slug;
    }

    /**
     * Mengambil sort order berikutnya.
     */
    private function getNextSortOrder(): int
    {
        return (
            (int) Category::max(
                'sort_order'
            )
        ) + 1;
    }
}