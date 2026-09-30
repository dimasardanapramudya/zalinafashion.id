<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | SETTINGS INDEX
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        return view('admin.settings.index', [
            'settings' => Setting::pluck('value', 'key'),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SETTINGS UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        $data = $request->validate([
            /*
            |--------------------------------------------------------------------------
            | BRANDING
            |--------------------------------------------------------------------------
            */

            'site_name' => [
                'required',
                'string',
                'max:120',
            ],

            'site_tagline' => [
                'nullable',
                'string',
                'max:255',
            ],

            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],

            /*
            |--------------------------------------------------------------------------
            | STORE / SENDER INFORMATION
            |--------------------------------------------------------------------------
            */

            'sender_name' => [
                'required',
                'string',
                'max:120',
            ],

            'sender_address' => [
                'required',
                'string',
                'max:1000',
            ],

            'sender_city' => [
                'required',
                'string',
                'max:120',
            ],

            'sender_province' => [
                'required',
                'string',
                'max:120',
            ],

            'sender_postal_code' => [
                'required',
                'string',
                'max:20',
            ],

            'sender_phone' => [
                'required',
                'string',
                'max:30',
            ],

            /*
            |--------------------------------------------------------------------------
            | PROMO AND SHIPPING
            |--------------------------------------------------------------------------
            */

            'announcement_text' => [
                'nullable',
                'string',
                'max:255',
            ],

            'free_shipping_enabled' => [
                'nullable',
                'boolean',
            ],

            'free_shipping_minimum' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'shipping_cost' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'admin_fee' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            /*
            |--------------------------------------------------------------------------
            | SLIDER
            |--------------------------------------------------------------------------
            */

            'slider_autoplay_ms' => [
                'nullable',
                'integer',
                'min:1000',
                'max:20000',
            ],

            /*
            |--------------------------------------------------------------------------
            | SOCIAL MEDIA
            |--------------------------------------------------------------------------
            */

            'instagram_url' => [
                'nullable',
                'url',
                'max:255',
            ],

            'instagram_label' => [
                'nullable',
                'string',
                'max:100',
            ],

            'shopee_url' => [
                'nullable',
                'url',
                'max:255',
            ],

            'whatsapp_url' => [
                'nullable',
                'url',
                'max:255',
            ],

            'whatsapp_label' => [
                'nullable',
                'string',
                'max:100',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | NORMALIZE CHECKBOX
        |--------------------------------------------------------------------------
        |
        | Checkbox HTML tidak mengirim value ketika tidak dicentang.
        | Karena itu kita paksa menjadi 0 atau 1.
        |
        */

        $data['free_shipping_enabled'] = $request->boolean(
            'free_shipping_enabled'
        ) ? '1' : '0';

        /*
        |--------------------------------------------------------------------------
        | NORMALIZE NUMERIC SETTINGS
        |--------------------------------------------------------------------------
        */

        $data['free_shipping_minimum'] = (string) max(
            (float) ($data['free_shipping_minimum'] ?? 0),
            0
        );

        $data['shipping_cost'] = (string) max(
            (float) ($data['shipping_cost'] ?? 0),
            0
        );

        /*
        | Biaya admin: hanya dinormalisasi kalau field-nya memang dikirim form,
        | supaya nilai yang sudah tersimpan tidak tertimpa 0 oleh request yang
        | tidak membawa field ini. Dikosongkan = Rp0 (tanpa biaya admin).
        */
        if ($request->has('admin_fee')) {
            $data['admin_fee'] = (string) max(
                (float) ($data['admin_fee'] ?? 0),
                0
            );
        }

        /*
        |--------------------------------------------------------------------------
        | LOGO UPLOAD
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('logo')) {
            $oldLogo = Setting::value('site_logo');

            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }

            $data['site_logo'] = $request
                ->file('logo')
                ->store('settings', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | SETTINGS KEY LIST
        |--------------------------------------------------------------------------
        */

        $settingKeys = [
            /*
            | Branding
            */

            'site_name',
            'site_tagline',
            'site_logo',

            /*
            | Sender
            */

            'sender_name',
            'sender_address',
            'sender_city',
            'sender_province',
            'sender_postal_code',
            'sender_phone',

            /*
            | Promo and shipping
            */

            'announcement_text',
            'free_shipping_enabled',
            'free_shipping_minimum',
            'shipping_cost',
            'admin_fee',

            /*
            | Slider
            */

            'slider_autoplay_ms',

            /*
            | Social media
            */

            'instagram_url',
            'instagram_label',
            'shopee_url',
            'whatsapp_url',
            'whatsapp_label',
        ];

        /*
        |--------------------------------------------------------------------------
        | SAVE SETTINGS
        |--------------------------------------------------------------------------
        */

        foreach ($settingKeys as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }

            Setting::updateOrCreate(
                [
                    'key' => $key,
                ],
                [
                    'value' => $data[$key],
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | RESPONSE
        |--------------------------------------------------------------------------
        */

        return back()->with(
            'success',
            'Pengaturan toko berhasil disimpan.'
        );
    }
}