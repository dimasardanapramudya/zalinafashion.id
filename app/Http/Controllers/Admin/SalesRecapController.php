<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesClosing;
use App\Models\SalesLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SalesRecapController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'harian');
        $includeClosed = $request->boolean('include_closed');

        $date = $request->filled('date')
            ? Carbon::parse($request->get('date'))
            : now();

        [$from, $to] = $this->resolveRange($period, $date, $request);

        $query = SalesLedger::query()->rentang(
            $from->toDateString(),
            $to->toDateString()
        );

        if (!$includeClosed) {
            $query->berjalan();
        }

        $summary = $this->summarize((clone $query)->get());
        $breakdown = $this->breakdown((clone $query)->get(), $period);

        $entries = (clone $query)
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $closings = SalesClosing::query()
            ->orderByDesc('closed_at')
            ->limit(12)
            ->get();

        $unclosedCount = SalesLedger::query()->berjalan()->count();

        return view('admin.sales-recap.index', [
            'period' => $period,
            'date' => $date,
            'from' => $from,
            'to' => $to,
            'includeClosed' => $includeClosed,
            'summary' => $summary,
            'breakdown' => $breakdown,
            'entries' => $entries,
            'closings' => $closings,
            'unclosedCount' => $unclosedCount,
        ]);
    }

    public function storeOffline(Request $request)
    {
        $data = $request->validate([
            'entry_date' => ['required', 'date'],
            'customer_name' => ['nullable', 'string', 'max:150'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:150'],
            'items.*.variant' => ['nullable', 'string', 'max:100'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'discount_total' => ['nullable', 'numeric', 'min:0'],
            'shipping_total' => ['nullable', 'numeric', 'min:0'],
            'shipping_cost_actual' => ['nullable', 'numeric', 'min:0'],
            'admin_fee' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $items = collect($data['items'])->map(function ($item) {
            $item['qty'] = (int) $item['qty'];
            $item['price'] = (float) $item['price'];
            $item['subtotal'] = $item['qty'] * $item['price'];

            return $item;
        });

        $subtotal = (float) $items->sum('subtotal');
        $discount = (float) ($data['discount_total'] ?? 0);
        $shipping = (float) ($data['shipping_total'] ?? 0);
        $shippingActual = (float) ($data['shipping_cost_actual'] ?? $shipping);
        $adminFee = (float) ($data['admin_fee'] ?? 0);

        $grandTotal = $subtotal - $discount + $shipping + $adminFee;
        $netRevenue = $grandTotal - $shippingActual;

        SalesLedger::create([
            'entry_date' => $data['entry_date'],
            'channel' => 'offline',
            'order_id' => null,
            'order_number' => 'OFFLINE-' . now()->format('YmdHis'),
            'payment_method' => $data['payment_method'] ?? 'Tunai',
            'items_snapshot' => $items->toArray(),
            'items_count' => $items->count(),
            'quantity_total' => (int) $items->sum('qty'),
            'subtotal' => (int) round($subtotal),
            'discount_total' => (int) round($discount),
            'shipping_total' => (int) round($shipping),
            'shipping_cost_actual' => (int) round($shippingActual),
            'admin_fee' => (int) round($adminFee),
            'grand_total' => (int) round($grandTotal),
            'net_revenue' => (int) round($netRevenue),
            'customer_name' => $data['customer_name'] ?? null,
            'note' => $data['note'] ?? null,
            'recorded_by' => $request->user()->name ?? 'Admin',
        ]);

        return back()->with(
            'success',
            'Penjualan offline berhasil dicatat ke rekap.'
        );
    }

    public function close(Request $request)
    {
        $request->validate([
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $unclosed = SalesLedger::query()->berjalan()->get();

        if ($unclosed->isEmpty()) {
            return back()->with(
                'info',
                'Tidak ada transaksi berjalan untuk ditutup buku.'
            );
        }

        DB::transaction(function () use ($unclosed, $request) {
            $summary = $this->summarize($unclosed);

            $closing = SalesClosing::create([
                'closed_at' => now(),
                'period_from' => $unclosed->min('entry_date'),
                'period_to' => $unclosed->max('entry_date'),
                'total_entries' => $unclosed->count(),
                'total_quantity' => $summary['quantity_total'],
                'total_gross' => $summary['grand_total'],
                'total_discount' => $summary['discount_total'],
                'total_shipping' => $summary['shipping_total'],
                'total_shipping_actual' => $summary['shipping_cost_actual'],
                'total_admin_fee' => $summary['admin_fee'],
                'total_net' => $summary['net_revenue'],
                'closed_by' => $request->user()->name ?? 'Admin',
                'note' => $request->get('note'),
            ]);

            SalesLedger::query()
                ->berjalan()
                ->update([
                    'sales_closing_id' => $closing->id,
                ]);
        });

        return back()->with(
            'success',
            'Tutup buku berhasil. Rekap berjalan direset, seluruh data tetap tersimpan di riwayat closing.'
        );
    }

    public function exportExcel(Request $request)
    {
        $period = $request->get('period', 'harian');
        $includeClosed = $request->boolean('include_closed');

        $date = $request->filled('date')
            ? Carbon::parse($request->get('date'))
            : now();

        [$from, $to] = $this->resolveRange($period, $date, $request);

        $query = SalesLedger::query()->rentang(
            $from->toDateString(),
            $to->toDateString()
        );

        if (!$includeClosed) {
            $query->berjalan();
        }

        $entries = (clone $query)
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

        $summary = $this->summarize($entries);
        $breakdown = $this->breakdown($entries, $period);

        $rp = fn ($n) =>
            'Rp' . number_format((float) $n, 0, ',', '.');

        $periodLabel = [
            'harian' => 'Harian',
            'mingguan' => 'Mingguan',
            'bulanan' => 'Bulanan',
            'tahunan' => 'Tahunan',
            'custom' => 'Kustom',
        ][$period] ?? ucfirst($period);

        $spreadsheet = new Spreadsheet();

        $wineArgb = 'FF631F2B';

        $headerStyle = function ($sheet, string $range) use ($wineArgb) {
            $sheet->getStyle($range)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => $wineArgb],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FFEADCDF'],
                    ],
                ],
            ]);
        };

        /*
        |--------------------------------------------------------------------------
        | SHEET 1 — RINGKASAN
        |--------------------------------------------------------------------------
        */

        $s1 = $spreadsheet->getActiveSheet();
        $s1->setTitle('Ringkasan');

        $s1->setCellValue(
            'B2',
            'LAPORAN KEUANGAN — ZALINA FASHION'
        );

        $s1->getStyle('B2')->getFont()
            ->setBold(true)
            ->setSize(16)
            ->getColor()
            ->setARGB($wineArgb);

        $s1->setCellValue(
            'B3',
            'Periode ' . $periodLabel . ': ' .
            $from->translatedFormat('d F Y') .
            ' – ' .
            $to->translatedFormat('d F Y')
        );

        $s1->setCellValue(
            'B4',
            'Dicetak: ' .
            now()->translatedFormat('d F Y, H:i') .
            ' WIB'
        );

        $s1->setCellValue(
            'B5',
            $includeClosed
                ? 'Cakupan: Seluruh histori (termasuk yang sudah ditutup buku)'
                : 'Cakupan: Periode berjalan (belum ditutup buku)'
        );

        $rows = [
            [
                'Jumlah Transaksi',
                $entries->count() . ' transaksi',
            ],
            [
                'Jumlah Item Terjual',
                number_format($summary['quantity_total']) . ' pcs',
            ],
            ['', ''],
            [
                'Subtotal Produk',
                $rp($summary['subtotal']),
            ],
            [
                'Diskon Diberikan',
                '- ' . $rp($summary['discount_total']),
            ],
            [
                'Ongkos Kirim Ditagihkan ke Pembeli',
                $rp($summary['shipping_total']),
            ],
            [
                'Biaya Admin Terkumpul',
                $rp($summary['admin_fee']),
            ],
            [
                'PENDAPATAN KOTOR (Grand Total)',
                $rp($summary['grand_total']),
            ],
            ['', ''],
            [
                'Ongkos Kirim Riil Dibayar ke Kurir (Pengeluaran)',
                '- ' . $rp($summary['shipping_cost_actual']),
            ],
            [
                'PENDAPATAN BERSIH',
                $rp($summary['net_revenue']),
            ],
            ['', ''],
            [
                'Penjualan Online',
                $entries->where('channel', 'online')->count() .
                ' transaksi — ' .
                $rp($entries->where('channel', 'online')->sum('grand_total')),
            ],
            [
                'Penjualan Offline',
                $entries->where('channel', 'offline')->count() .
                ' transaksi — ' .
                $rp($entries->where('channel', 'offline')->sum('grand_total')),
            ],
        ];

        $r = 7;

        foreach ($rows as [$label, $value]) {
            $s1->setCellValue('B' . $r, $label);
            $s1->setCellValue('D' . $r, $value);

            if ($label !== '') {
                $isIncome = str_contains($label, 'PENDAPATAN');

                $s1->getStyle('B' . $r)
                    ->getFont()
                    ->setBold($isIncome);

                $s1->getStyle('D' . $r)
                    ->getFont()
                    ->setBold($isIncome);

                if ($label === 'PENDAPATAN BERSIH') {
                    $s1->getStyle('B' . $r . ':D' . $r)
                        ->getFont()
                        ->getColor()
                        ->setARGB($wineArgb);

                    $s1->getStyle('B' . $r . ':D' . $r)
                        ->getFont()
                        ->setSize(13);
                }
            }

            $r++;
        }

        foreach (['B', 'C', 'D'] as $col) {
            $s1->getColumnDimension($col)
                ->setWidth($col === 'B' ? 42 : 20);
        }

        /*
        |--------------------------------------------------------------------------
        | SHEET 2 — REKAP PER PERIODE
        |--------------------------------------------------------------------------
        */

        $s2 = $spreadsheet->createSheet();
        $s2->setTitle('Rekap ' . $periodLabel);

        $s2->fromArray([
            'Periode',
            'Jumlah Transaksi',
            'Qty Terjual',
            'Subtotal',
            'Diskon',
            'Ongkir Ditagihkan',
            'Ongkir Riil',
            'Biaya Admin',
            'Pendapatan Kotor',
            'Pendapatan Bersih',
        ], null, 'A1');

        $headerStyle($s2, 'A1:J1');

        $row = 2;

        foreach ($breakdown as $b) {
            $s2->fromArray([
                $b['label'],
                $b['count'],
                $b['quantity_total'],
                $b['subtotal'],
                $b['discount_total'],
                $b['shipping_total'],
                $b['shipping_cost_actual'],
                $b['admin_fee'],
                $b['grand_total'],
                $b['net_revenue'],
            ], null, 'A' . $row);

            $row++;
        }

        foreach (range('D', 'J') as $col) {
            $s2->getStyle(
                $col . '2:' . $col . ($row - 1)
            )->getNumberFormat()
                ->setFormatCode('#,##0');
        }

        foreach (range('A', 'J') as $col) {
            $s2->getColumnDimension($col)
                ->setAutoSize(true);
        }

        /*
        |--------------------------------------------------------------------------
        | SHEET 3 — DETAIL TRANSAKSI
        |--------------------------------------------------------------------------
        */

        $s3 = $spreadsheet->createSheet();
        $s3->setTitle('Detail Transaksi');

        $s3->fromArray([
            'Tanggal',
            'Channel',
            'No. Referensi',
            'Metode Bayar',
            'Produk & Varian',
            'Qty',
            'Subtotal',
            'Diskon',
            'Ongkir Ditagihkan',
            'Ongkir Riil',
            'Biaya Admin',
            'Grand Total',
            'Pendapatan Bersih',
            'Pelanggan',
            'Catatan',
        ], null, 'A1');

        $headerStyle($s3, 'A1:O1');

        $row = 2;

        foreach ($entries as $e) {
            $productLines = collect($e->items_snapshot ?? [])
                ->map(function ($it) {
                    $name = $it['name']
                        ?? ($it['product_name'] ?? 'Produk');

                    $variant = !empty($it['variant'])
                        ? ' (' . $it['variant'] . ')'
                        : '';

                    $qty = $it['qty']
                        ?? ($it['quantity'] ?? 1);

                    return $qty . 'x ' . $name . $variant;
                })
                ->implode('; ');

            $s3->fromArray([
                optional($e->entry_date)->format('d-m-Y'),
                $e->channel === 'online' ? 'Online' : 'Offline',
                $e->order_number ?: ('#' . $e->id),
                $e->payment_method,
                $productLines,
                $e->quantity_total,
                $e->subtotal,
                $e->discount_total,
                $e->shipping_total,
                $e->shipping_cost_actual,
                $e->admin_fee,
                $e->grand_total,
                $e->net_revenue,
                $e->customer_name,
                $e->note,
            ], null, 'A' . $row);

            $row++;
        }

        foreach (range('G', 'M') as $col) {
            $s3->getStyle(
                $col . '2:' . $col . max($row - 1, 2)
            )->getNumberFormat()
                ->setFormatCode('#,##0');
        }

        foreach (range('A', 'O') as $col) {
            $s3->getColumnDimension($col)
                ->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $filename =
            'rekap-penjualan-zalina-' .
            $period .
            '-' .
            now()->format('Ymd_His') .
            '.xlsx';

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(
            function () use ($writer) {
                $writer->save('php://output');
            },
            $filename,
            [
                'Content-Type' =>
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }

    private function resolveRange(
        string $period,
        Carbon $date,
        Request $request
    ): array {
        return match ($period) {
            'mingguan' => [
                $date->copy()->startOfWeek(),
                $date->copy()->endOfWeek(),
            ],

            'bulanan' => [
                $date->copy()->startOfMonth(),
                $date->copy()->endOfMonth(),
            ],

            'tahunan' => [
                $date->copy()->startOfYear(),
                $date->copy()->endOfYear(),
            ],

            'custom' => [
                Carbon::parse(
                    $request->get(
                        'from',
                        $date->toDateString()
                    )
                )->startOfDay(),

                Carbon::parse(
                    $request->get(
                        'to',
                        $date->toDateString()
                    )
                )->endOfDay(),
            ],

            default => [
                $date->copy()->startOfDay(),
                $date->copy()->endOfDay(),
            ],
        };
    }

    private function summarize($entries): array
    {
        return [
            'count' => $entries->count(),
            'quantity_total' => (int) $entries->sum('quantity_total'),
            'subtotal' => (int) $entries->sum('subtotal'),
            'discount_total' => (int) $entries->sum('discount_total'),
            'shipping_total' => (int) $entries->sum('shipping_total'),
            'shipping_cost_actual' => (int) $entries->sum('shipping_cost_actual'),
            'admin_fee' => (int) $entries->sum('admin_fee'),
            'grand_total' => (int) $entries->sum('grand_total'),
            'net_revenue' => (int) $entries->sum('net_revenue'),
        ];
    }

    private function breakdown($entries, string $period): array
    {
        $keyFormat = match ($period) {
            'tahunan' => 'Y-m',
            'bulanan' => 'Y-m-d',
            'mingguan' => 'Y-m-d',
            default => 'Y-m-d',
        };

        $labelFormat = match ($period) {
            'tahunan' => 'F Y',
            default => 'd F Y',
        };

        $groups = $entries->groupBy(
            fn ($e) => optional($e->entry_date)->format($keyFormat)
        );

        return $groups
            ->map(function ($group, $key) use ($labelFormat) {
                $summary = $this->summarize($group);

                return array_merge($summary, [
                    'key' => $key,
                    'label' => $key
                        ? Carbon::parse($key)->translatedFormat($labelFormat)
                        : '—',
                ]);
            })
            ->sortBy('key')
            ->values()
            ->all();
    }
}
