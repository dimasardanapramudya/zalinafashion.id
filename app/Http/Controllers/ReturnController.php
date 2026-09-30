<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class ReturnController extends Controller
{


    /**
     * ============================================================
     * CUSTOMER SUBMIT PENGAJUAN RETUR
     * ============================================================
     */
    public function store(
        Request $request,
        Order $order
    ): RedirectResponse {


        $userId =
            session('zalina_user_id')
            ?? Auth::id();



        if (!$userId) {

            abort(
                403,
                'Anda harus login untuk mengajukan retur.'
            );

        }



        if ((int) $order->user_id !== (int) $userId) {

            abort(
                403,
                'Anda tidak memiliki akses ke pesanan ini.'
            );

        }



        if (
            !method_exists(
                $order,
                'canRequestReturn'
            )
        ) {


            Log::error(
                'Order model tidak memiliki method canRequestReturn.',
                [
                    'order_id'=>$order->id
                ]
            );


            return back()->with(
                'error',
                'Fitur retur belum dikonfigurasi.'
            );

        }




        if (!$order->canRequestReturn()) {


            return back()->with(
                'error',
                'Pesanan belum dapat diajukan retur.'
            );


        }




        try {


            $validated =
                $request->validate([

                    'reason'
                        =>
                        'required|string|max:255',

                    'note'
                        =>
                        'nullable|string|max:2000',

                    'description'
                        =>
                        'nullable|string|max:2000',

                    /*
                    |--------------------------------------------------------------------------
                    | MAX UPLOAD FOTO RETUR
                    |--------------------------------------------------------------------------
                    |
                    | Nilai "max" divalidasi dalam KB. 20480 KB = 20 MB.
                    | Disamakan dengan batas 20 MB di validasi JavaScript
                    | pada halaman profile.
                    |--------------------------------------------------------------------------
                    */

                    'image'
                        =>
                        'nullable|image|mimes:jpg,jpeg,png,webp|max:20480',

                ]);



        } catch(ValidationException $exception){



            Log::warning(
                'Validasi retur gagal.',
                [
                    'order_id'=>$order->id,
                    'errors'=>$exception->errors()
                ]
            );


            throw $exception;


        }





        $customerNote =
            $validated['note']
            ??
            $validated['description']
            ??
            null;



        $newImage = null;


        $oldImage =
            $order->return_image;



        if($request->hasFile('image')){


            $newImage =
                $request
                ->file('image')
                ->store(
                    'returns',
                    'public'
                );


        }




        try {



            DB::transaction(function() use(
                $order,
                $validated,
                $customerNote,
                $newImage,
                $oldImage
            ){



                $order->update([


                    'return_status'
                        =>
                        'requested',


                    'return_reason'
                        =>
                        $validated['reason'],


                    'return_customer_note'
                        =>
                        $customerNote,


                    'return_image'
                        =>
                        $newImage ?? $oldImage,


                    'return_admin_note'
                        =>
                        null,


                    'return_approved_at'
                        =>
                        null,


                    'return_rejected_at'
                        =>
                        null,


                    'return_completed_at'
                        =>
                        null,


                ]);





                if(
                    $newImage
                    &&
                    $oldImage
                    &&
                    $newImage !== $oldImage
                    &&
                    Storage::disk('public')
                    ->exists($oldImage)
                ){


                    Storage::disk('public')
                    ->delete($oldImage);


                }



            });





        }catch(Throwable $e){



            if(
                $newImage
                &&
                Storage::disk('public')
                ->exists($newImage)
            ){

                Storage::disk('public')
                ->delete($newImage);

            }



            Log::error(
                'Gagal menyimpan retur.',
                [
                    'order_id'=>$order->id,
                    'message'=>$e->getMessage()
                ]
            );



            return back()->with(
                'error',
                'Gagal menyimpan pengajuan retur.'
            );

        }
        

        /*
        |--------------------------------------------------------------------------
        | NOTIFIKASI WHATSAPP ADMIN
        |--------------------------------------------------------------------------
        */

        try {

            /*
            |--------------------------------------------------------------------------
            | LINK BUKTI FOTO RETUR UNTUK ADMIN
            |--------------------------------------------------------------------------
            |
            | Sebelumnya notifikasi WhatsApp hanya menulis "Ada / Tidak ada" tanpa
            | menyertakan link foto, sehingga admin tidak bisa langsung melihat
            | bukti retur dari WhatsApp dan harus membuka Dashboard Admin secara
            | terpisah lalu mencari pesanan yang bersangkutan secara manual.
            |
            | Sekarang link foto (jika ada) dan link halaman detail retur admin
            | disertakan langsung, supaya bukti retur benar-benar "terhubung"
            | ke proses review admin.
            |
            */

            $returnImageForNotif = $newImage ?? $oldImage;

            $returnImageUrl = $returnImageForNotif
                ? asset('storage/' . ltrim($returnImageForNotif, '/'))
                : null;

            $adminReturnUrl = route('admin.returns.index');

            $whatsappMessage = implode("\n", array_filter([
                '🚨 PENGAJUAN RETUR BARU',
                '',
                'ZALINA FASHION',
                '━━━━━━━━━━━━━━━━━━',
                'Order ID: #' . $order->id,
                'User ID: ' . ($order->user_id ?? '-'),
                'Alasan: ' . ($validated['reason'] ?? '-'),
                'Catatan Customer: ' . ($customerNote ?? '-'),
                'Bukti Foto: ' . ($returnImageUrl ? $returnImageUrl : 'Tidak ada'),
                '',
                'Cek & proses retur di Dashboard Admin:',
                $adminReturnUrl,
            ], static fn ($line) => $line !== null));

            app(WhatsAppService::class)
                ->sendAdminNotification($whatsappMessage);

            Log::info(
                'Notifikasi WhatsApp pengajuan retur berhasil dikirim.',
                [
                    'order_id' => $order->id,
                ]
            );

        } catch (Throwable $e) {

            Log::warning(
                'Notifikasi WhatsApp pengajuan retur gagal dikirim.',
                [
                    'order_id' => $order->id,
                    'message' => $e->getMessage(),
                ]
            );

        }

        return back()->with(
            'success',
            'Pengajuan retur berhasil dikirim.'
        );


    }





    /**
     * ============================================================
     * ADMIN HAPUS RIWAYAT RETUR PERMANEN
     *
     * Retur disimpan di tabel orders
     * ============================================================
     */
    public function destroy(
        Order $order
    ): RedirectResponse {



        try {



            DB::transaction(function() use($order){



                /*
                |--------------------------------------------------------------------------
                | DELETE FOTO RETUR
                |--------------------------------------------------------------------------
                */


                if($order->return_image){



                    if(
                        Storage::disk('public')
                        ->exists(
                            $order->return_image
                        )
                    ){


                        Storage::disk('public')
                        ->delete(
                            $order->return_image
                        );


                    }


                }





                /*
                |--------------------------------------------------------------------------
                | CLEAR DATA RETUR
                |--------------------------------------------------------------------------
                */


                $order->update([


                    'return_status'
                        =>
                        null,


                    'return_reason'
                        =>
                        null,


                    'return_customer_note'
                        =>
                        null,


                    'return_image'
                        =>
                        null,


                    'return_admin_note'
                        =>
                        null,


                    'return_approved_at'
                        =>
                        null,


                    'return_rejected_at'
                        =>
                        null,


                    'return_completed_at'
                        =>
                        null,


                ]);



                /*
                |--------------------------------------------------------------------------
                | REFRESH DATA
                |--------------------------------------------------------------------------
                */


                $order->refresh();



            });






            return redirect()
            ->route(
                'admin.returns.index'
            )
            ->with(
                'success',
                'Riwayat retur berhasil dihapus permanen.'
            );







        }catch(Throwable $e){



            Log::error(
                'Gagal menghapus retur.',
                [

                    'order_id'=>$order->id,

                    'message'=>$e->getMessage(),

                    'line'=>$e->getLine(),

                ]
            );



            return back()->with(
                'error',
                'Riwayat retur gagal dihapus.'
            );



        }


    }



}