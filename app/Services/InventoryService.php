<?php

/**
 * FILE: app/Services/InventoryService.php
 * 
 * SERVICE LAYER untuk inventory management
 * Centralize semua inventory logic di sini
 */

namespace App\Services;

use App\Models\Product;
use App\Models\Variant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    /**
     * GET: Products dengan stock yang tidak sinkron
     * 
     * Find all products di mana product->stock ≠ sum(variant->stock)
     * Berguna untuk monitoring dan debugging
     * 
     * @return Collection
     */
    public static function getDesyncedProducts(): Collection
    {
        $query = DB::table('products as p')
            ->leftJoin(
                'variants as v',
                function ($join) {
                    $join->on('p.id', '=', 'v.product_id')
                        ->where('v.is_active', '=', true);
                }
            )
            ->groupBy('p.id', 'p.name', 'p.stock')
            ->selectRaw(
                'p.id, 
                p.name, 
                p.stock as product_stock, 
                COALESCE(SUM(v.stock), 0) as variant_total,
                (p.stock - COALESCE(SUM(v.stock), 0)) as difference'
            )
            ->havingRaw('p.stock != COALESCE(SUM(v.stock), 0)')
            ->orderByDesc('ABS(difference)');

        return collect($query->get())->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'product_name' => $item->name,
                'product_stock' => (int) $item->product_stock,
                'variant_total' => (int) $item->variant_total,
                'difference' => (int) $item->difference,
                'status' => $item->difference > 0 ? 'OVERSTOCK' : 'UNDERSTOCK',
            ];
        });
    }

    /**
     * GET: Products dengan low stock
     * 
     * Find products dengan stok ≤ threshold
     * Berguna untuk inventory alert
     * 
     * @param int $threshold Default 5
     * @return Collection
     */
    public static function getLowStockProducts(int $threshold = 5): Collection
    {
        return Product::query()
            ->where('stock', '<=', $threshold)
            ->where('stock', '>', 0)
            ->where('is_active', true)
            ->orderBy('stock', 'asc')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'stock' => (int) $product->stock,
                    'variant_count' => $product->variants()->where('is_active', true)->count(),
                    'stock_value' => $product->stock * $product->price,
                ];
            });
    }

    /**
     * GET: Products yang sold out (stok = 0)
     * 
     * @return Collection
     */
    public static function getSoldOutProducts(): Collection
    {
        return Product::query()
            ->where('stock', 0)
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sold_out_since' => $product->updated_at,
                    'variant_count' => $product->variants->count(),
                ];
            });
    }

    /**
     * GET: Variants dengan stock yang tidak sinkron ke product
     * 
     * @return Collection
     */
    public static function getVariantsNeedingRestock(): Collection
    {
        return Variant::query()
            ->where('is_active', true)
            ->where('stock', '<=', 3)
            ->where('stock', '>', 0)
            ->with('product')
            ->orderBy('stock', 'asc')
            ->get()
            ->map(function ($variant) {
                return [
                    'variant_id' => $variant->id,
                    'variant_name' => $variant->name,
                    'product_name' => $variant->product->name,
                    'stock' => (int) $variant->stock,
                    'price' => (float) $variant->price,
                ];
            });
    }

    /**
     * CHECK: Apakah product stock sudah sinkron dengan variant?
     * 
     * @param Product $product
     * @return bool
     */
    public static function isProductSynced(Product $product): bool
    {
        return (int) $product->stock === $product->getTotalVariantStock();
    }

    /**
     * GET: Stock info lengkap untuk product
     * 
     * Return array dengan breakdown detail stock
     * 
     * @param Product $product
     * @return array
     */
    public static function getProductStockInfo(Product $product): array
    {
        $variants = $product->variants()
            ->where('is_active', true)
            ->get();

        $variantTotal = $variants->sum('stock');
        $productStock = (int) $product->stock;
        $isSynced = $productStock === $variantTotal;

        return [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'stock' => $productStock,
                'is_active' => (bool) $product->is_active,
            ],
            'variants' => $variants->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'stock' => (int) $variant->stock,
                    'price' => (float) $variant->price,
                ];
            })->toArray(),
            'summary' => [
                'variant_count' => $variants->count(),
                'variant_total' => (int) $variantTotal,
                'is_synced' => $isSynced,
                'sync_status' => $isSynced ? 'SYNCED' : 'DESYNC',
                'sync_difference' => $productStock - $variantTotal,
            ],
        ];
    }

    /**
     * VALIDATE: Check sebelum order diproses
     * 
     * Validate apakah stok cukup untuk order
     * 
     * @param Product $product
     * @param int $quantity
     * @param Variant|null $variant
     * @return array ['valid' => bool, 'message' => string]
     */
    public static function validateStockForOrder(
        Product $product,
        int $quantity,
        ?Variant $variant = null
    ): array {
        
        // Check variant stock (jika ada)
        if ($variant) {
            if ((int) $variant->stock < $quantity) {
                return [
                    'valid' => false,
                    'message' => "Stok {$variant->name} hanya {$variant->stock} unit, Anda mau {$quantity} unit",
                ];
            }
        } else {
            // Check product stock (jika tidak ada variant)
            if ((int) $product->stock < $quantity) {
                return [
                    'valid' => false,
                    'message' => "Stok {$product->name} hanya {$product->stock} unit, Anda mau {$quantity} unit",
                ];
            }
        }

        return [
            'valid' => true,
            'message' => 'Stok cukup',
        ];
    }

    /**
     * REPORT: Generate inventory report
     * 
     * @return array
     */
    public static function generateInventoryReport(): array
    {
        $allProducts = Product::query()->where('is_active', true)->count();
        $totalStock = Product::query()->where('is_active', true)->sum('stock');
        $totalValue = Product::query()
            ->where('is_active', true)
            ->selectRaw('SUM(stock * COALESCE(sale_price, price)) as value')
            ->first()
            ->value ?? 0;

        $desyncedCount = self::getDesyncedProducts()->count();
        $lowStockCount = self::getLowStockProducts()->count();
        $soldOutCount = self::getSoldOutProducts()->count();

        return [
            'timestamp' => now()->toIso8601String(),
            'summary' => [
                'total_products' => $allProducts,
                'total_stock_units' => (int) $totalStock,
                'total_stock_value' => (float) $totalValue,
                'low_stock_products' => $lowStockCount,
                'sold_out_products' => $soldOutCount,
                'desynced_products' => $desyncedCount,
            ],
            'alerts' => [
                'has_desynced' => $desyncedCount > 0,
                'has_low_stock' => $lowStockCount > 0,
                'has_sold_out' => $soldOutCount > 0,
            ],
        ];
    }

    /**
     * MAINTENANCE: Manual fix untuk product yang desynced
     * 
     * @param Product $product
     * @return array ['success' => bool, 'message' => string]
     */
    public static function fixProductStockDesync(Product $product): array
    {
        try {
            $oldStock = (int) $product->stock;
            
            $product->syncStockFromVariants();
            
            $newStock = (int) $product->fresh()->stock;

            return [
                'success' => true,
                'message' => "Stock fixed: {$oldStock} → {$newStock}",
                'product_id' => $product->id,
                'old_stock' => $oldStock,
                'new_stock' => $newStock,
            ];
        } catch (\Exception $e) {
            Log::error("Error fixing product stock", [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => "Error: {$e->getMessage()}",
            ];
        }
    }

    /**
     * LOG: Detailed stock change logging
     * 
     * Catat setiap perubahan stock untuk audit trail
     * 
     * @param string $action
     * @param Product|Variant $item
     * @param int $quantity
     * @param string $reason
     * @return void
     */
    public static function logStockChange(
        string $action,
        $item,
        int $quantity,
        string $reason = 'manual'
    ): void {
        $itemType = $item instanceof Product ? 'Product' : 'Variant';
        $itemId = $item->id;
        $itemName = $item->name;
        $oldStock = (int) $item->stock;

        Log::info(
            "Stock change: {$itemType}",
            [
                'type' => $itemType,
                'item_id' => $itemId,
                'item_name' => $itemName,
                'action' => $action,
                'old_stock' => $oldStock,
                'quantity' => $quantity,
                'new_stock' => $action === 'increase' ? $oldStock + $quantity : max(0, $oldStock - $quantity),
                'reason' => $reason,
                'user_id' => auth()->id(),
                'ip' => request()->ip(),
            ]
        );
    }
}