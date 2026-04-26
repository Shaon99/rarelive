<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Purchases;
use App\Models\PurchasesProduct;
use App\Models\StockHistory;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\WarehouseProducts;
use App\Services\PurchasesService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchasesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data['pageTitle'] = 'Purchase';
        $data['purchasesManagement'] = 'active';

        if ($request->has('duepurchases')) {
            $data['duepurchasesActive'] = 'active';
        } else {
            $data['purchasesActive'] = 'active';
        }

        $data['allPurchases'] = Purchases::with(['paymentMethod', 'createdBy', 'transactions', 'supplier' => function ($query) {
            return $query->select('id', 'name');
        }])->when($request->has('trashed'), function ($query) {
            $query->onlyTrashed();
        })->when($request->has('duepurchases'), function ($query) {
            $query->where('payment_status', 0);
        })
            ->latest()
            ->get();

        return view('backend.products.purchases.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['pageTitle'] = 'Purchase Create';
        $data['purchasesManagement'] = 'active';
        $data['purchasesActive'] = 'active';
        $data['warehouses'] = Warehouse::select('id', 'name')->get();
        $data['suppliers'] = Supplier::select('id', 'name')->get();
        $data['products'] = Product::select('id', 'name')->latest()->limit(25)->get();
        $data['payment_account'] = PaymentMethod::select('id', 'type', 'name', 'account_number')
            ->where('type', '!=', 'STEADFAST')
            ->get()
            ->values();
        $data['voucherNo'] = generateUniqueSerial('purchases', 'invoice_no', 'PR.');

        return view('backend.products.purchases.create')->with($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, PurchasesService $purchasesService)
    {
        try {
            $purchasesService->storePurchases($request);

            return redirect()->route('admin.purchases.index')->with('success', 'Purchases created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while creating the purchase: '.$e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data['purchasesManagement'] = 'active';
        $data['purchasesActive'] = 'active';
        $data['singlePurchases'] = Purchases::with('supplier', 'transactions.paymentMethod', 'warehouse', 'purchasesProduct')->findOrFail($id);
        $data['pageTitle'] = 'Purchases From - '.@$data['singlePurchases']->supplier->name;
        // purchases return
        $data['purchaseReturns'] = PurchaseReturn::with('createdBy', 'purchase', 'purchaseReturnItems.product')->where('purchase_id', $id)->get();

        return view('backend.products.purchases.view')->with($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['purchasesManagement'] = 'active';
        $data['purchasesActive'] = 'active';
        $data['warehouses'] = Warehouse::select('id', 'name')->get();
        $data['suppliers'] = Supplier::select('id', 'name')->get();
        $data['products'] = Product::select('id', 'name')->get();
        $data['singlePurchases'] = Purchases::with('supplier', 'warehouse', 'purchasesProduct')->findOrFail($id);
        $data['pageTitle'] = 'Purchases Edit For - '.@$data['singlePurchases']->supplier->name;

        return view('backend.products.purchases.edit')->with($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id, PurchasesService $purchasesService)
    {
        // $purchasesService->updatePurchases($request, $id);
        return redirect()->route('admin.purchases.index')->with('success', 'Purchases updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Purchases::find($id)->delete();

        return back()->with('success', 'Purchases Deleted successfully');
    }

    public function purchasesRestore($id)
    {
        Purchases::withTrashed()->find($id)->restore();

        return back()->with('success', 'purchases Successfully Restore');
    }

    public function purchasesDelete($id)
    {
        $product = Purchases::onlyTrashed()->findOrFail($id);

        $product->forceDelete();

        return back()->with('success', 'Purchases Deleted successfully');
    }

    public function checkStock()
    {
        $data['pageTitle'] = 'Stocks';
        $data['purchasesManagement'] = 'active';
        $data['stock'] = 'active';
        $data['stockProducts'] = Product::select('id', 'name', 'quantity', 'sale_price', 'purchase_price', 'average_unit_price')->with([
            'salesProducts' => function ($query) {
                $query->select('id', 'quantity');
            },
            'stockProduct' => function ($query) {
                $query->select('id', 'product_id', 'created_at', 'quantity', 'current_net_unit_price', 'purchase_id');
            },
            'stockProduct.purchase' => function ($query) {
                $query->select('id', 'supplier_id');
            },
            'stockProduct.purchase.supplier' => function ($query) {
                $query->select('id', 'name');
            },

        ])->get();

        return view('backend.stock.index')->with($data);
    }

    public function purchasesDuePayment(Request $request, $id)
    {
        $request->validate([
            'payment_amount' => 'required|gte:0',
            'payment_method' => 'required|exists:payment_methods,id',
        ]);

        try {
            DB::beginTransaction();

            // Lock the purchase record for update
            $purchase = Purchases::lockForUpdate()->findOrFail($id);
            $paymentAmount = floatval($request->payment_amount);

            if ($paymentAmount <= 0) {
                throw new \Exception('Payment amount must be greater than 0.');
            }

            // Update paid and due amounts
            $newDue = $purchase->due_amount - $paymentAmount;
            $newPaid = $purchase->paid_amount + $paymentAmount;

            // Update purchase record with new due and paid amounts
            $purchase->update([
                'due_amount' => $newDue,
                'paid_amount' => $newPaid,
                'payment_method' => $request->payment_method,
                'payment_status' => $newDue == 0 ? 1 : $purchase->payment_status,
            ]);

            // Check and deduct the payment amount from the selected payment method account
            $paymentAccount = PaymentMethod::lockForUpdate()->findOrFail($request->payment_method);

            if ($paymentAccount->current_balance < $paymentAmount) {
                throw new \Exception('Insufficient balance in the selected payment account.');
            }

            // Deduct the payment from the payment method account
            $paymentAccount->decrement('current_balance', $paymentAmount);

            // Record the payment transaction
            $purchase->transactions()->create([
                'amount' => $paymentAmount,
                'debit' => null,
                'credit' => 'credit',
                'payment_method_id' => $paymentAccount->id,
                'transaction_type' => 'purchases_payment_paid',
                'note' => 'PAYMENT: '.optional($purchase->supplier)->name,
            ]);

            // Update the supplier's due balance (this is the fix you were asking for)
            $supplier = Supplier::findOrFail($purchase->supplier_id);
            $supplier->due -= $paymentAmount;
            $supplier->save();

            DB::commit();

            return back()->with('success', 'Due amount paid successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Error processing payment: '.$e->getMessage());
        }
    }

    public function transfer(Request $request, $id)
    {
        $product = Product::find($id);

        $product->quantity += $request->transfer_quantity;
        $product->sale_price = $request->update_saleprice;
        $product->purchase_price = $request->current_purchases;

        $product->save();

        $purchases_product = PurchasesProduct::where('product_id', $id)->where('quantity', '>', 0)->first();

        $remain = $purchases_product->quantity - $request->transfer_quantity;

        $purchases_product->total = $remain * $purchases_product->current_net_unit_price;

        $purchases_product->save();

        StockHistory::create([
            'product_id' => $id,
            'old_purchases' => $purchases_product->quantity,
            'transfer_purchases' => $request->transfer_quantity,
            'remain_purchases' => $remain,
        ]);

        $purchases_product->quantity = $remain;

        $purchases_product->save();

        return back()->with('success', 'Quantity transfer successfully');
    }

    public function stockHistory($id)
    {
        $data['pageTitle'] = 'Stocks Transfer History';
        $data['purchasesManagement'] = 'active';
        $data['stock'] = 'active';
        $data['stockHistory'] = StockHistory::with('product')->get();

        return view('backend.stock.history')->with($data);
    }

    public function statusChange(Request $request, $id)
    {
        $data = Purchases::find($id);

        $data->order_status = $request->status;

        $data->save();

        return response()->json(['success', 'status change']);
    }

    public function purchasesLedger(Request $request)
    {
        // Set active classes and page title based on request
        $accountsActiveClass = 'active';
        $pageTitle = $request->has('supplierLedger') ? 'Supplier Ledger' : 'Purchases Ledger';
        $supplierLedgerActiveClass = $request->has('supplierLedger') ? 'active' : '';
        $purchasesLedgerActiveClass = ! $request->has('supplierLedger') ? 'active' : '';
        $type = $request->has('supplierLedger') ? 'supplier' : 'purchases';

        // Get request parameters
        $supplierId = $request->input('supplier_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Initialize variables
        $suppliers = null;
        if ($type == 'supplier') {
            $suppliers = Supplier::select('id', 'name')->get();
        }
        $openingBalance = 0;
        $closingBalance = 0;
        $totals = (object) [
            'total_debit' => 0,
            'total_credit' => 0,
        ];
        $transactions = collect();

        if ($startDate && $endDate) {
            try {
                $start = Carbon::parse($startDate)->startOfDay();
                $end = Carbon::parse($endDate)->endOfDay();

                // Calculate opening balance
                $openingBalanceQuery = DB::table('transactions')
                    ->where('transactionable_type', Purchases::class)
                    ->where('created_at', '<', $start)
                    ->selectRaw('COALESCE(SUM(CASE WHEN debit IS NOT NULL THEN amount ELSE -amount END), 0) as balance')
                    ->first();

                $openingBalance = $openingBalanceQuery->balance;

                // Get transactions
                $query = DB::table('transactions as t')
                    ->leftJoin('purchases as p', 't.transactionable_id', '=', 'p.id')
                    ->leftJoin('payment_methods as pm', 't.payment_method_id', '=', 'pm.id')
                    ->where('t.transactionable_type', Purchases::class)
                    ->whereBetween('t.created_at', [$start, $end]);

                if ($supplierId) {
                    $query->where('p.supplier_id', $supplierId);
                }

                $transactions = $query->select([
                    't.created_at as date',
                    't.note',
                    't.amount',
                    't.transaction_type',
                    't.debit',
                    't.credit',
                    't.transactionable_id',
                    't.payment_method_id',
                    'p.invoice_no',
                    'pm.name as payment_methods',
                    DB::raw('CASE WHEN t.debit IS NOT NULL THEN t.amount ELSE 0 END as debit_amount'),
                    DB::raw('CASE WHEN t.credit IS NOT NULL THEN t.amount ELSE 0 END as credit_amount'),
                ])
                    ->orderBy('t.created_at')
                    ->get();

                // Calculate running balance and totals
                $runningBalance = $openingBalance;
                foreach ($transactions as $transaction) {
                    $runningBalance += ($transaction->debit_amount - $transaction->credit_amount);
                    $transaction->balance = $runningBalance;
                }

                $totals->total_debit = $transactions->sum('debit_amount');
                $totals->total_credit = $transactions->sum('credit_amount');
                $closingBalance = $runningBalance;
            } catch (\Exception $e) {
                return back()->with('error', 'Error processing ledger: '.$e->getMessage());
            }
        }

        if ($request->ajax()) {
            return view('backend.accounts.purchases-ledger-table', compact(
                'startDate',
                'endDate',
                'transactions',
                'totals',
                'openingBalance',
                'closingBalance',
                'supplierId',
                'type'
            ))->render();
        }

        return view('backend.accounts.purchases-ledger', [
            'transactions' => $transactions,
            'openingBalance' => $openingBalance,
            'closingBalance' => $closingBalance,
            'totals' => $totals,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'purchasesLedgerActiveClass' => $purchasesLedgerActiveClass,
            'supplierLedgerActiveClass' => $supplierLedgerActiveClass,
            'accountsActiveClass' => $accountsActiveClass,
            'pageTitle' => $pageTitle,
            'suppliers' => $suppliers,
            'supplierId' => $supplierId,
            'type' => $type,
        ]);
    }

    public function purchasesReturn($id)
    {
        $data['purchasesManagement'] = 'active';
        $data['purchasesActive'] = 'active';
        $data['purchase'] = Purchases::with('purchasesProduct')->findOrFail($id);
        $data['pageTitle'] = 'Purchases Return - '.$data['purchase']->supplier->name;
        $data['payment_account'] = PaymentMethod::select('id', 'name', 'account_number')->get();

        $data['returnedQuantities'] = PurchaseReturnItem::whereHas('purchaseReturn', function ($q) use ($id) {
            $q->where('purchase_id', $id);
        })
            ->select('product_id', DB::raw('SUM(quantity) as returned'))
            ->groupBy('product_id')
            ->pluck('returned', 'product_id');

        return view('backend.products.purchases.purchase_returns')->with($data);
    }

    public function purchaseReturnStore(Request $request)
    {
        $validated = $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'items' => 'required|array|min:1',
        ]);

        DB::beginTransaction();

        try {
            $adminId = auth()->guard('admin')->id();
            $purchase = Purchases::with('supplier')->findOrFail($validated['purchase_id']);
            $warehouseId = $validated['warehouse_id'];

            $purchaseReturn = PurchaseReturn::create([
                'created_by' => $adminId,
                'purchase_id' => $purchase->id,
                'warehouse_id' => $warehouseId,
                'supplier_id' => $purchase->supplier_id,
                'return_date' => now(),
                'note' => $request->note,
            ]);

            $totalReturnAmount = 0;

            foreach ($validated['items'] as $item) {
                $productId = $item['product_id'];
                $returnQty = (float) $item['quantity'];

                if ($returnQty <= 0) {
                    continue;
                }

                $purchaseProduct = PurchasesProduct::where([
                    'purchase_id' => $purchase->id,
                    'product_id' => $productId,
                ])->firstOrFail();

                // Track total returned quantity for the product
                $returnedQty = PurchaseReturnItem::where('product_id', $productId)
                    ->whereHas('purchaseReturn', function ($q) use ($purchase) {
                        $q->where('purchase_id', $purchase->id);
                    })
                    ->sum('quantity');

                // Calculate available quantity for return
                $availableForReturn = $purchaseProduct->quantity - $returnedQty;

                // Validate that the return quantity does not exceed the available stock
                if ($returnQty > $availableForReturn) {
                    $productName = Product::findOrFail($productId)->name;

                    return back()->with('error', "Return quantity for product '{$productName}' exceeds available quantity. Available for return: {$availableForReturn}.");
                }

                $unitCost = $purchaseProduct->current_net_unit_price;
                $lineTotal = $returnQty * $unitCost;

                // Create the return item record
                PurchaseReturnItem::create([
                    'purchase_return_id' => $purchaseReturn->id,
                    'purchase_product_id' => $purchaseProduct->id,
                    'product_id' => $productId,
                    'warehouse_id' => $warehouseId,
                    'quantity' => $returnQty,
                    'unit_cost' => $unitCost,
                    'total_amount' => $lineTotal,
                ]);

                // Decrease product stock
                Product::where('id', $productId)->decrement('quantity', $returnQty);

                // Decrease warehouse stock
                WarehouseProducts::where([
                    'product_id' => $productId,
                    'warehouse_id' => $warehouseId,
                ])->decrement('quantity', $returnQty);

                $totalReturnAmount += $lineTotal;
            }

            // Update purchase return total amount
            $purchaseReturn->update(['total_amount' => $totalReturnAmount]);

            // Create the transaction for the return
            $purchase->transactions()->create([
                'amount' => $totalReturnAmount,
                'debit' => 'debit',
                'transaction_type' => 'purchases_return_on_due',
                'note' => 'PURCHASE RETURN DUE: '.optional($purchase->supplier)->name,
            ]);

            if ($request->filled('payment_method')) {
                // Handle payment
                $paymentAccount = PaymentMethod::findOrFail($request->payment_method);
                $paymentAccount->increment('current_balance', $totalReturnAmount);

                // Record the payment transaction
                $purchase->transactions()->create([
                    'amount' => $totalReturnAmount,
                    'credit' => 'credit',
                    'payment_method_id' => $paymentAccount->id,
                    'transaction_type' => 'payment_received',
                    'transaction_date' => now(),
                    'note' => "PURCHASES RETURNED PAYMENT PAID: {$purchase->supplier->name}",
                ]);

            }

            DB::commit();

            return redirect()
                ->route('admin.purchases.show', $purchase->id)
                ->with('success', 'Purchase return processed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Return failed: '.$e->getMessage()]);
        }
    }
}
