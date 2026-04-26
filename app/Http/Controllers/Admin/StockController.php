<?php

namespace App\Http\Controllers\Admin;

use App\Constants\CommonConstant;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SaleProductReturn;
use App\Models\Sales;
use App\Models\SalesProduct;
use App\Models\StockTransaction;
use App\Models\Warehouse;
use App\Models\WarehouseProducts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function adjustmentForm(Request $request)
    {
        $data['pageTitle'] = 'Product';
        $data['purchasesManagement'] = 'active';
        $data['stockAdjustmentActive'] = 'active';

        $data['products'] = Product::select('id', 'name', 'code')->latest()->limit(25)->get();
        $data['warehouses'] = Warehouse::all();

        $query = StockTransaction::with('product', 'warehouse')
            ->orderBy('created_at', 'desc');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $adjustments = $query->paginate(10);

        if ($request->ajax()) {
            return view('backend.products.adjustments-table', compact('adjustments'))->render();
        }

        $data['adjustments'] = $adjustments;

        return view('backend.products.stock-adjustment')->with($data);
    }

    public function addStock(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required',
            'type' => 'required|in:initial,damage,update',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'nullable|numeric',
            'sale_price' => 'nullable|numeric',
            'reason' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $productId = $validated['product_id'];
            $warehouseId = $validated['warehouse_id'];
            $quantity = abs($validated['quantity']);
            $type = $validated['type'];

            // Fetch current quantity if exists
            $currentStock = DB::table('warehouse_products')
                ->where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->value('quantity') ?? 0;

            $newStock = match ($type) {
                'initial' => $quantity,                     // Replace with initial quantity
                'update' => $currentStock + $quantity,      // Increment existing quantity
                'damage' => $currentStock - $quantity,      // Decrement existing quantity
            };

            // Update or Insert the warehouse stock
            DB::table('warehouse_products')->updateOrInsert(
                ['product_id' => $productId, 'warehouse_id' => $warehouseId],
                ['quantity' => $newStock]
            );

            // Save stock transaction
            StockTransaction::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'type' => $type,
                'quantity' => $type === 'damage' ? -$quantity : $quantity, // Save negative quantity for damage
                'unit_price' => $validated['unit_price'],
                'sale_price' => $validated['sale_price'],
                'reason' => $validated['reason'],
            ]);

            // Update main product's total quantity across all warehouses
            $totalQty = DB::table('warehouse_products')
                ->where('product_id', $productId)
                ->sum('quantity');

            $updateData = ['quantity' => $totalQty];

            if (! is_null($validated['unit_price'])) {
                $updateData['purchase_price'] = $validated['unit_price'];
            }

            if (! is_null($validated['sale_price'])) {
                $updateData['sale_price'] = $validated['sale_price'];
            }

            Product::where('id', $productId)->update($updateData);
        });

        return redirect()->back()->with('success', 'Stock Updated Successfully');
    }

    public function returnList(Request $request)
    {
        $data['pageTitle'] = 'Sale Return List';
        $data['salesReturnNav'] = 'active';

        $query = SaleProductReturn::with('items', 'product')
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            })->orWhereHas('items', function ($q) use ($search) {
                $q->whereHas('sale', function ($q2) use ($search) {
                    $q2->where('invoice_no', 'like', "%$search%");
                });
            });
        }

        $returns = $query->paginate(10);

        if ($request->ajax()) {
            return view('backend.sales.sale-return-adjustments-table', compact('returns'))->render();
        }

        $data['returns'] = $returns;

        return view('backend.sales.sale-return-adjustments-list')->with($data);
    }

    public function saleReturnAdjustment($id)
    {
        $data['sale'] = Sales::select('id', 'invoice_no')->with('salesProduct')->find($id);

        $data['pageTitle'] = 'Sale Return Adjustment For -'.$data['sale']->invoice_no;
        $data['salesNav'] = 'active';

        return view('backend.sales.sale-return-adjustment')->with($data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice' => 'required|string|exists:sales,invoice_no',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.qty' => 'required|numeric|min:0',
            'products.*.warehouse' => 'required|numeric|min:0',
            'reason' => 'nullable|string|max:255',
        ]);
        DB::transaction(function () use ($validated) {
            $invoice = Sales::where('invoice_no', $validated['invoice'])->first();
            $returnedProducts = array_filter($validated['products'], fn ($prod) => $prod['qty'] > 0);
            $saleReturns = array_map(function ($prod) use ($invoice, $validated) {
                return [
                    'sale_id' => $invoice->id,
                    'product_id' => $prod['id'],
                    'return_qty' => $prod['qty'],
                    'type' => 'return',
                    'reason' => $validated['reason'] ?? 'Return Successful',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, $returnedProducts);

            SaleProductReturn::insert($saleReturns);

            foreach ($returnedProducts as $prod) {
                Product::where('id', $prod['id'])->increment('quantity', $prod['qty']);

                WarehouseProducts::updateOrCreate(
                    [
                        'warehouse_id' => $prod['warehouse'],
                        'product_id' => $prod['id'],
                    ],
                    [
                        'quantity' => DB::raw('quantity + '.$prod['qty']),
                    ]
                );

                SalesProduct::where('product_id', $prod['id'])
                    ->where('sales_id', $invoice->id)
                    ->update(['returned_quantity' => $prod['qty']]);
            }

            $invoice->update(['return_status' => CommonConstant::RETURNED]);

            activity()
                ->performedOn($invoice)
                ->causedBy(auth()->guard('admin')->user())
                ->event('stock_adjustment')
                ->withProperties([
                    'new_status' => CommonConstant::RETURNED,
                    'changed_by' => auth()->guard('admin')->user()->name,
                ])
                ->log('sales_status_changed');
        });

        return response()->json(['success' => true]);
    }
}
