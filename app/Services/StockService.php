<?php

namespace App\Services;

use App\Models\Combo;
use App\Models\Product;
use App\Models\Sales;
use App\Models\WarehouseProducts;

class StockService
{
    /**
     * Adjust stock levels when a sale is cancelled.
     */
    public function adjustStockForCancelledSale(Sales $sale)
    {
        foreach ($sale->salesProduct as $salesProduct) {
            if ($salesProduct->product_id) {
                $this->adjustProductStock($salesProduct);
            } elseif ($salesProduct->combo_id) {
                $this->adjustComboStock($salesProduct);
            }
        }
    }

    /**
     * Put inventory back when a sale row is removed from the database.
     * Uses line quantity minus already-returned qty (sale return adjustment), so stock is not double-restored.
     */
    public function restoreStockForSaleDeletion(Sales $sale): void
    {
        foreach ($sale->salesProduct as $salesProduct) {
            $returned = (float) ($salesProduct->returned_quantity ?? 0);
            $qty = max(0.0, (float) $salesProduct->quantity - $returned);
            if ($qty <= 0) {
                continue;
            }
            if ($salesProduct->product_id) {
                $this->incrementProductWarehouseStock(
                    (int) $salesProduct->product_id,
                    (int) $salesProduct->warehouse_id,
                    $qty
                );
            } elseif ($salesProduct->combo_id) {
                $combo = Combo::find($salesProduct->combo_id);
                if ($combo) {
                    $combo->quantity += $qty;
                    $combo->save();
                }
            }
        }
    }

    private function incrementProductWarehouseStock(int $productId, int $warehouseId, float $qty): void
    {
        $product = Product::find($productId);
        if (! $product) {
            return;
        }
        $product->quantity += $qty;
        $product->save();

        $warehouse = WarehouseProducts::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->first();
        if ($warehouse) {
            $warehouse->quantity += $qty;
            $warehouse->save();
        }
    }

    /**
     * Adjust stock for a product.
     *
     * @param  object  $salesProduct
     */
    private function adjustProductStock($salesProduct)
    {
        $product = Product::find($salesProduct->product_id);
        if ($product) {
            $product->quantity += $salesProduct->quantity;
            $product->save();

            // Update warehouse quantity
            $warehouse = WarehouseProducts::where('warehouse_id', $salesProduct->warehouse_id)
                ->where('product_id', $salesProduct->product_id)
                ->first();

            if ($warehouse) {
                $warehouse->quantity += $salesProduct->quantity;
                $warehouse->save();
            }
        }
    }

    /**
     * Adjust stock for a combo.
     *
     * @param  object  $salesProduct
     */
    private function adjustComboStock($salesProduct)
    {
        $combo = Combo::find($salesProduct->combo_id);
        if ($combo) {
            $combo->quantity += $salesProduct->quantity;
            $combo->save();
        }
    }

    public function markAsDelivered(Sales $sale)
    {
        $sale->payment_status = 1;
        if ($sale->consignment_id) {
            $sale->cod_charge = $sale->grand_total * 0.01;
        }
        $sale->due_amount = 0;
        $sale->paid_amount = $sale->grand_total;
        $sale->system_status = 'completed';

        $sale->transactions()->create([
            'amount' => $sale->grand_total,
            'debit' => 'debit',
            'credit' => null,
            'transaction_type' => 'payment_received',
            'transaction_date' => now(),
            'note' => 'PAYMENT RECEIVED: '.$sale->customerName,
            'payment_method_id' => 1,
        ]);
    }
}
