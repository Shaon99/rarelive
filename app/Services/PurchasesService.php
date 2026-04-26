<?php

namespace App\Services;

use App\Models\GeneralSetting;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Purchases;
use App\Models\PurchasesProduct;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\WarehouseProducts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchasesService
{
    public function storePurchases(Request $request): Purchases
    {
        // Validate request inputs
        $this->validatePurchaseRequest($request);

        // Start database transaction
        DB::beginTransaction();

        try {
            // Create purchase entry
            $purchases = $this->createPurchaseEntry($request);

            // Process purchased products and update stocks
            $this->processPurchasedProducts($request, $purchases);

            // Handle debit and credit transactions
            $this->handleTransactions($purchases, $request);

            // Commit the transaction
            DB::commit();

            // Log the purchase activity
            $this->logPurchaseActivity($purchases, $request);

            return $purchases;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function validatePurchaseRequest(Request $request)
    {
        $request->validate([
            'warehouse' => 'required',
            'supplier' => 'required',
            'product_id' => 'required',
        ], [
            'product_id.required' => 'Please add some product first',
        ]);
    }

    private function createPurchaseEntry(Request $request): Purchases
    {
        // Calculate payment status and due amount
        $payment_status = $request->grand_total == $request->payment ? 1 : 0;
        $due_amount = $payment_status == 0 ? $request->duepayment : 0;

        // Create the purchase entry
        $purchase = Purchases::create([
            'reference_no' => $request->reference_no,
            'invoice_no' => $request->invoice_no,
            'warehouse_id' => $request->warehouse,
            'supplier_id' => $request->supplier,
            'created_by' => auth()->guard('admin')->user()->id,
            'purchase_date' => $request->purchase_date,
            'discount' => $request->discount,
            'other_cost' => $request->shipping,
            'payment_method' => $request->payment_by,
            'payment_status' => $payment_status,
            'sub_total' => $request->subtotals,
            'total_qty' => $request->total_qty,
            'grand_total' => $request->grand_total,
            'paid_amount' => $request->payment,
            'due_amount' => $due_amount,
            'note' => $request->note ?? 'Purchases Materials',
        ]);

        // If there's a due amount, create a transaction entry
        if ($due_amount > 0) {
            $supplier = Supplier::find($purchase->supplier_id);
            $supplier->due += $due_amount;
            $supplier->save();
        }

        return $purchase;
    }

    private function processPurchasedProducts(Request $request, Purchases $purchases)
    {
        foreach ($request->product_id as $index => $product_id) {
            $product = Product::findOrFail($product_id);
            $purchaseQty = $request->qty[$index];
            $purchaseTotal = $purchaseQty * $request->current_net_unit_price[$index];

            // Update warehouse stock
            $this->updateWarehouseProductStock($request, $product_id, $purchaseQty);

            // Update product average prices
            $this->updateProductPrices($product, $purchaseQty, $purchaseTotal, $request, $index);

            // Create purchase product entry
            $this->createPurchaseProductEntry($purchases, $product, $purchaseQty, $purchaseTotal, $request, $index);
        }
    }

    private function updateWarehouseProductStock(Request $request, $product_id, $purchaseQty)
    {
        $warehouseProduct = WarehouseProducts::firstOrNew([
            'warehouse_id' => $request->warehouse,
            'product_id' => $product_id,
        ]);
        $warehouseProduct->quantity = ($warehouseProduct->quantity ?? 0) + $purchaseQty;
        $warehouseProduct->save();
    }

    private function updateProductPrices(Product $product, $purchaseQty, $purchaseTotal, Request $request, $index)
    {
        $oldQuantity = $product->quantity ?? 0;
        $oldStockValue = $oldQuantity * $product->average_stock_price;
        $newPurchaseValue = $purchaseTotal;

        $totalStockQty = $oldQuantity + $purchaseQty;
        $totalStockValue = $oldStockValue + $newPurchaseValue;

        $averageUnitPrice = $totalStockQty > 0
            ? (($product->average_unit_price * $oldQuantity) + $newPurchaseValue) / $totalStockQty
            : $request->current_net_unit_price[$index];

        $averageStockPrice = $totalStockQty > 0
            ? $totalStockValue / $totalStockQty
            : $request->current_net_unit_price[$index];

        $product->update([
            'purchase_price' => $request->current_net_unit_price[$index],
            'sale_price' => $request->current_sales_unit_price[$index],
            'average_unit_price' => $averageUnitPrice,
            'average_stock_price' => $averageStockPrice,
            'quantity' => $totalStockQty,
        ]);
    }

    private function createPurchaseProductEntry(Purchases $purchases, Product $product, $purchaseQty, $purchaseTotal, Request $request, $index)
    {
        PurchasesProduct::create([
            'purchase_id' => $purchases->id,
            'product_id' => $product->id,
            'quantity' => $purchaseQty,
            'current_net_unit_price' => $request->current_net_unit_price[$index],
            'previous_net_unit_price' => $request->previous_net_unit_price[$index],
            'current_sales_unit_price' => $request->current_sales_unit_price[$index],
            'previous_sales_unit_price' => $request->previous_sales_unit_price[$index],
            'total' => $purchaseTotal,
        ]);
    }

    private function handleTransactions(Purchases $purchases, Request $request)
    {
        // Record debit entry for purchases
        $purchases->transactions()->create([
            'amount' => $purchases->grand_total,
            'debit' => 'debit',
            'credit' => null,
            'transaction_type' => 'purchases_on_due',
            'note' => 'PURCHASES: '.optional($purchases->supplier)->name,
        ]);

        // Handle payment if applicable
        if ($request->payment) {
            $this->handlePayment($purchases, $request);
        }
    }

    private function handlePayment(Purchases $purchases, Request $request)
    {
        $paymentAccount = PaymentMethod::findOrFail($request->payment_by);

        if ($paymentAccount->current_balance < $request->payment) {
            throw new \Exception('Insufficient balance in the payment account.');
        }

        $paymentAccount->decrement('current_balance', $request->payment);

        // Record payment transaction
        $purchases->transactions()->create([
            'amount' => $purchases->paid_amount,
            'debit' => null,
            'credit' => 'credit',
            'payment_method_id' => $paymentAccount->id,
            'transaction_type' => 'purchases_payment_paid',
            'note' => 'PAYMENT: '.optional($purchases->supplier)->name,
        ]);

        GeneralSetting::first()->decrement('opening_balance', $request->payment);
    }

    private function logPurchaseActivity(Purchases $purchases, Request $request)
    {
        activity()
            ->performedOn($purchases)
            ->causedBy(auth()->guard('admin')->user())
            ->event('purchases_created')
            ->withProperties([
                'supplier_name' => $purchases->supplier->name ?? 'N/A',
                'supplier_phone' => $purchases->supplier->phone ?? 'N/A',
                'warehouse_address' => $purchases->warehouse->address ?? 'N/A',
                'product_ids' => $request->product_id,
                'product_type' => $request->type,
                'payment_status' => $purchases->payment_status == 0 ? 'Due' : 'Paid',
            ])
            ->log('Purchase created and processed: Invoice No. '.$purchases->invoice_no);
    }
}
