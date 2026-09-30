<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReturnManagementController extends Controller
{
    /**
     * ============================================================
     * DAFTAR PENGAJUAN RETUR
     * ============================================================
     *
     * PERBAIKAN:
     * Sebelumnya query memakai whereNotNull('return_status')
     * dan where('return_status', '!=', ''), sehingga order yang
     * riwayat returnya sudah "dihapus" (return_status = 'none')
     * tetap lolos filter dan muncul kembali di daftar admin
     * dengan label "Belum Diajukan".
     *
     * Sekarang query hanya mengambil order dengan status retur
     * yang benar-benar aktif (requested, approved, rejected,
     * completed). Status 'none' otomatis tidak akan pernah
     * muncul di daftar admin.
     */
    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = Order::query()
            /*
            |--------------------------------------------------------------
            | EAGER LOAD ITEMS + PRODUK
            |--------------------------------------------------------------
            | Sebelumnya relasi ini TIDAK di-eager-load. Akibatnya, bagian
            | "Produk Diretur" di index_blade.php (yang membaca $order->items)
            | bisa tampil kosong:
            |
            |  - Kalau aplikasi mengaktifkan Model::preventLazyLoading()
            |    (umum di lingkungan staging/production), mengakses
            |    $order->items tanpa eager load akan melempar
            |    LazyLoadingViolationException untuk SETIAP order di
            |    halaman ini.
            |  - relationLoaded('images') di $resolveProductImage (blade)
            |    juga selalu false kalau relasi ini tidak dimuat di sini,
            |    sehingga galeri foto produk tidak pernah ikut dicek.
            |
            | 'items.product' & 'items.product.images' ditambahkan supaya
            | preview produk & foto retur di halaman admin selalu punya
            | data yang lengkap, tanpa N+1 query per baris.
            */
            ->with([
                'items.product',
                'items.product.images',
            ])
            ->whereIn('return_status', [
                'requested',
                'approved',
                'rejected',
                'completed',
            ])
            ->orderByRaw("
                CASE
                    WHEN return_status = 'requested' THEN 1
                    WHEN return_status = 'approved' THEN 2
                    WHEN return_status = 'completed' THEN 3
                    WHEN return_status = 'rejected' THEN 4
                    ELSE 5
                END
            ")
            ->orderByDesc('return_approved_at')
            ->orderByDesc('return_rejected_at')
            ->orderByDesc('return_completed_at')
            ->orderByDesc('id');

        if (
            $status &&
            in_array(
                $status,
                [
                    'requested',
                    'approved',
                    'rejected',
                    'completed',
                ],
                true
            )
        ) {
            $query->where('return_status', $status);
        }

        $returns = $query
            ->paginate(15)
            ->withQueryString();

        return view('admin.returns.index', [
            'returns' => $returns,
            'activeStatus' => $status,
        ]);
    }

    /**
     * ============================================================
     * SETUJUI RETUR
     * ============================================================
     */
    public function approve(
        Request $request,
        Order $order
    ): RedirectResponse {
        if ($order->return_status !== 'requested') {
            return back()->with(
                'error',
                'Retur ini sudah diproses sebelumnya.'
            );
        }

        $order->forceFill([
            'return_status' => 'approved',
            'return_admin_note' => $request->input('admin_note'),
            'return_approved_at' => now(),
            'return_rejected_at' => null,
            'return_completed_at' => null,
        ])->save();

        Log::info('Retur disetujui.', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
        ]);

        return back()->with(
            'success',
            'Retur berhasil disetujui.'
        );
    }

    /**
     * ============================================================
     * TOLAK RETUR
     * ============================================================
     */
    public function reject(
        Request $request,
        Order $order
    ): RedirectResponse {
        if ($order->return_status !== 'requested') {
            return back()->with(
                'error',
                'Retur ini sudah diproses sebelumnya.'
            );
        }

        $validated = $request->validate([
            'admin_note' => [
                'required',
                'string',
                'max:1000',
            ],
        ], [
            'admin_note.required' => 'Alasan penolakan wajib diisi.',
            'admin_note.string' => 'Alasan penolakan harus berupa teks.',
            'admin_note.max' => 'Alasan penolakan maksimal 1000 karakter.',
        ]);

        $order->forceFill([
            'return_status' => 'rejected',
            'return_admin_note' => $validated['admin_note'],
            'return_rejected_at' => now(),
            'return_approved_at' => null,
            'return_completed_at' => null,
        ])->save();

        Log::info('Retur ditolak.', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
        ]);

        return back()->with(
            'success',
            'Retur berhasil ditolak.'
        );
    }

    /**
     * ============================================================
     * SELESAIKAN RETUR
     * ============================================================
     */
    public function complete(
        Request $request,
        Order $order
    ): RedirectResponse {
        if ($order->return_status !== 'approved') {
            return back()->with(
                'error',
                'Retur harus disetujui terlebih dahulu sebelum diselesaikan.'
            );
        }

        $order->forceFill([
            'return_status' => 'completed',
            'return_completed_at' => now(),
        ])->save();

        Log::info('Retur diselesaikan.', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
        ]);

        return back()->with(
            'success',
            'Retur berhasil diselesaikan.'
        );
    }

    /**
     * ============================================================
     * HAPUS RIWAYAT RETUR
     * ============================================================
     *
     * Catatan:
     * return_status tidak boleh diisi NULL karena kolom database
     * memiliki aturan NOT NULL.
     *
     * Oleh karena itu, status retur dikembalikan menjadi "none".
     * Nilai "none" berarti pesanan tidak memiliki pengajuan retur,
     * dan method index() di atas sudah memastikan status ini
     * tidak akan pernah ditampilkan lagi di daftar admin.
     */
    public function destroy(Order $order): RedirectResponse
    {
        if (
            $order->return_status === null ||
            $order->return_status === '' ||
            $order->return_status === 'none'
        ) {
            return back()->with(
                'error',
                'Riwayat retur tidak ditemukan.'
            );
        }

        $order->forceFill([
            /*
             * JANGAN gunakan null untuk return_status.
             * Database mengharuskan kolom ini memiliki nilai.
             */
            'return_status' => 'none',

            'return_reason' => null,
            'return_customer_note' => null,
            'return_image' => null,
            'return_admin_note' => null,
            'return_approved_at' => null,
            'return_rejected_at' => null,
            'return_completed_at' => null,
        ])->save();

        Log::info('Riwayat retur dihapus.', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
        ]);

        return back()->with(
            'success',
            'Riwayat retur berhasil dihapus.'
        );
    }
}