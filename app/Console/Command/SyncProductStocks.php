<?php

/**
 * FILE: app/Console/Commands/SyncProductStocks.php
 *
 * php artisan inventory:sync-stocks
 *
 * Menyamakan kolom products.stock dengan total stok varian AKTIF.
 * Produk yang tidak punya varian sama sekali DILEWATI (stok manualnya
 * dari form admin tidak boleh ditimpa jadi 0).
 *
 * USAGE:
 *   php artisan inventory:sync-stocks                  (sync yang tidak sinkron)
 *   php artisan inventory:sync-stocks --product=5      (satu produk saja)
 *   php artisan inventory:sync-stocks --dry-run        (preview, tidak menyimpan)
 *   php artisan inventory:sync-stocks --fix-all        (paksa sync semua produk bervarian)
 *   php artisan inventory:sync-stocks -v               (tampilkan detail error)
 *
 * Catatan: opsi --verbose / -v sudah disediakan Artisan, jadi tidak
 * didefinisikan ulang di $signature (kalau didefinisikan, command error).
 */

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncProductStocks extends Command
{
    protected $signature = 'inventory:sync-stocks
                            {--product= : Product ID spesifik}
                            {--dry-run : Preview only, jangan simpan}
                            {--fix-all : Paksa sync semua produk bervarian}';

    protected $description = 'Sync product stock dengan total variant stock';

    public function handle(): int
    {
        $this->newLine();
        $this->info('Mulai sync product stocks dengan variant stocks...');
        $this->newLine();

        try {
            $productId = $this->option('product');
            $dryRun = (bool) $this->option('dry-run');
            $fixAll = (bool) $this->option('fix-all');
            $verbose = $this->getOutput()->isVerbose();

            $query = Product::with('variants');

            if ($productId) {
                $query->where('id', (int) $productId);
            }

            $products = $query->get();

            if ($products->isEmpty()) {
                $this->warn('Tidak ada produk yang ditemukan');

                return self::FAILURE;
            }

            $this->info("Total produk: {$products->count()}");
            $this->newLine();

            $updated = 0;
            $skipped = 0;
            $noVariants = 0;
            $errors = 0;
            $changes = [];

            $progressBar = $this->output->createProgressBar($products->count());
            $progressBar->start();

            foreach ($products as $product) {
                try {
                    // Produk tanpa varian: stok manual, jangan disentuh.
                    if ($product->variants->isEmpty()) {
                        $noVariants++;
                        $progressBar->advance();
                        continue;
                    }

                    $oldStock = (int) $product->stock;
                    $variantTotal = $product->getTotalVariantStock();

                    if ($oldStock === $variantTotal && !$fixAll) {
                        $skipped++;
                        $progressBar->advance();
                        continue;
                    }

                    $changes[] = [
                        $product->id,
                        mb_substr($product->name, 0, 30),
                        $oldStock,
                        $product->variants->where('is_active', true)->count(),
                        $variantTotal,
                        ($variantTotal - $oldStock > 0 ? '+' : '') . ($variantTotal - $oldStock),
                    ];

                    if ($dryRun) {
                        $skipped++;
                    } else {
                        $product->syncStockFromVariants();
                        $updated++;
                    }
                } catch (\Throwable $e) {
                    $errors++;

                    if ($verbose) {
                        $this->newLine();
                        $this->error("{$product->name} (ID: {$product->id}): {$e->getMessage()}");
                    }
                }

                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine(2);

            $this->info('SUMMARY:');
            $this->line("  Updated       : {$updated}");
            $this->line("  Skipped       : {$skipped}");
            $this->line("  Tanpa varian  : {$noVariants}");
            $this->line("  Errors        : {$errors}");

            if (!empty($changes)) {
                $this->newLine();
                $this->info('PERUBAHAN:');
                $this->table(
                    ['Product ID', 'Product Name', 'Old Stock', 'Variants', 'New Stock', 'Difference'],
                    $changes
                );
            }

            if ($dryRun) {
                $this->newLine();
                $this->comment('DRY RUN - tidak ada perubahan yang disimpan.');
                $this->comment('Jalankan tanpa --dry-run untuk menyimpan.');
            }

            $this->newLine();

            return $errors === 0 ? self::SUCCESS : self::FAILURE;
        } catch (\Throwable $e) {
            $this->error('Fatal error: ' . $e->getMessage());

            Log::error('SyncProductStocks command failed', [
                'error' => $e->getMessage(),
                'exception' => class_basename($e),
            ]);

            return self::FAILURE;
        }
    }
}

/*
 * PENJADWALAN (Laravel 11/12) — tambahkan di routes/console.php:
 *
 * use Illuminate\Support\Facades\Schedule;
 *
 * Schedule::command('inventory:sync-stocks')
 *     ->dailyAt('02:00')
 *     ->onOneServer()
 *     ->withoutOverlapping();
 */