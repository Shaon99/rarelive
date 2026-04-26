<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessProductCsvChunk;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Combo;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\Transfer;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Models\WarehouseProducts;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $data['pageTitle'] = 'Product';
        $data['other'] = 'active';
        $data['product'] = 'active';

        $data['categories'] = Category::all();
        $data['brands'] = Brand::all();

        if ($request->ajax()) {
            $products = Product::with(['brand', 'createdBy:id,name', 'category', 'unit', 'warehouses', 'supplier']);

            // Apply filters if any filters are present in the request
            if ($request->has('search') && $request->search['value']) {
                $searchValue = $request->search['value'];
                $products->where(function ($query) use ($searchValue) {
                    $query->where('name', 'like', "%$searchValue%")
                        ->orWhere('code', 'like', "%$searchValue%")
                        ->orWhereHas('brand', function ($query) use ($searchValue) {
                            $query->where('name', 'like', "%$searchValue%");
                        })
                        ->orWhereHas('category', function ($query) use ($searchValue) {
                            $query->where('name', 'like', "%$searchValue%");
                        })
                        ->orWhereHas('supplier', function ($query) use ($searchValue) {
                            $query->where('name', 'like', "%$searchValue%");
                        });
                });
            }

            if ($request->filled('category')) {
                $products->where('category_id', $request->category);
            }

            if ($request->filled('brand')) {
                $products->where('brand_id', $request->brand);
            }

            // Sorting - default is created_at DESC
            $sortColumnIndex = $request->get('order')[12]['column'] ?? 0;
            $sortDirection = $request->get('order')[0]['dir'] ?? 'desc';
            // Define the columns we can sort by
            $sortableColumns = ['created_at', 'name', 'code', 'purchase_price', 'sale_price'];

            // Apply sorting
            $sortColumn = $sortableColumns[$sortColumnIndex] ?? 'created_at';
            $products->orderBy($sortColumn, $sortDirection);

            // Get total count of records before pagination
            $totalRecords = $products->count();

            // Paginate the query
            $pageStart = $request->start;
            $perPage = $request->length;

            $filteredProducts = $products->skip($pageStart)->take($perPage)->get();
            $trashed = $request->trashed;

            return response()->json([
                'draw' => $request->get('draw'),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $filteredProducts->map(function ($item, $key) use ($pageStart, $trashed) {
                    $warehouses = $item->warehouses->map(function ($warehouse) {
                        return $warehouse->warehouse->name.' ('.$warehouse->quantity.')';
                    })->join('<br>') ?: 'Unavailable';

                    return [
                        'sl' => $pageStart + $key + 1,
                        'action' => view('backend.partial.action_buttons', compact('item', 'trashed'))->render(),
                        'created_at' => $item->created_at->format('d M, Y') ?? 'N/A',
                        'code' => $item->code,
                        'image' => $item->image
                            ? '<img class="rounded" height="50px" width="50px" src="'.$item->image.'">'
                            : '<img class="rounded" height="50px" width="50px" src="'.getFile('default', generalSetting()->default_image).'">',
                        'name' => $item->name,
                        'quantity' => number_format($item->quantity, 0).' '.$item->unit->name,
                        'purchase_price' => number_format($item->purchase_price, 2).' '.generalSetting()->site_currency,
                        'sale_price' => number_format($item->sale_price, 2).' '.generalSetting()->site_currency,
                        'warehouse' => $warehouses,
                        'category' => $item->category->name ?? 'N/A',
                        'brand' => $item->brand->name ?? 'N/A',
                        'supplier' => $item->supplier->name ?? 'N/A',
                    ];
                }),
            ]);
        }

        return view('backend.products.product.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['pageTitle'] = 'Create A Product';
        $data['other'] = 'active';
        $data['product'] = 'active';
        $data['units'] = Unit::all();
        $data['brands'] = Brand::all();
        $data['categories'] = Category::all();

        $data['productAttributes'] = ProductAttribute::with('values')->get();

        return view('backend.products.product.create')->with($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, ProductService $productService)
    {
        try {
            // Wrap the entire operation in a database transaction
            DB::beginTransaction();

            // Create the product
            $product = $productService->createProduct($request);

            // Handle variations if the product has variations
            if ($product->has_variation) {
                $productService->createProductVariations($request, $product);
            }

            // Commit the transaction
            DB::commit();

            return redirect()->route('admin.product.index')->with('success', 'Product added successfully');
        } catch (\Exception $e) {
            // Rollback transaction in case of an error
            DB::rollBack();

            return back()->with('error', 'Failed to add product: '.$e->getMessage());
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $data['other'] = 'active';
        $data['product'] = 'active';
        $data['units'] = Unit::all();
        $data['brands'] = Brand::all();
        $data['categories'] = Category::all();
        $data['singleProduct'] = Product::with([
            'productGallery',
            'variations.variationValues.attribute',
            'variations.variationValues.attributeValue',
        ])->findOrFail($id);

        // Fetch all attributes for dropdown
        $data['allAttributes'] = ProductAttribute::with('values')->get();

        // Aggregate attribute values across all variations
        $data['attributesData'] = [];
        $data['attributeValuesData'] = [];
        foreach ($data['singleProduct']->variations as $variation) {
            $variationAttributes = [];
            $variationValues = [];
            foreach ($variation->variationValues as $variationValue) {
                if (! $variationValue->attribute || ! $variationValue->attributeValue) {
                    continue;
                }

                $attributeId = $variationValue->attribute->id;
                $attributeName = $variationValue->attribute->name;
                $attributeValueId = $variationValue->attributeValue->id;
                $attributeValueName = $variationValue->attributeValue->value;

                if (! isset($data['attributesData'][$attributeId])) {
                    $data['attributesData'][$attributeId] = [
                        'name' => $attributeName,
                        'values' => [],
                    ];
                }

                $data['attributesData'][$attributeId]['values'][$attributeValueId] = $attributeValueName;

                // Store variation-specific attributes and values
                $variationAttributes[] = $attributeId;
                $variationValues[$attributeId][] = $attributeValueId;
            }

            $data['attributeValuesData'][$variation->id] = [
                'attributes' => $variationAttributes,
                'values' => $variationValues,
            ];
        }

        $data['pageTitle'] = $data['singleProduct']->name.' Edit';
        $data['productAttributes'] = ProductAttribute::with('values')->get();

        if ($request->ajax()) {
            return response()->json($data['singleProduct']);
        }

        return view('backend.products.product.edit')->with($data);
    }

    /**
     * Update a product and its variations
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id, ProductService $productService)
    {
        $product = Product::with('variations.variationValues.attribute')->findOrFail($id);
        try {
            DB::beginTransaction();
            $productService->updateProductAndVariations($request, $product);
            DB::commit();

            return redirect()->route('admin.product.index')->with('success', 'Product updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to update product: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Product::find($id)->delete();

        return back()->with('success', 'Product Deleted successfully');
    }

    public function productRestore($id)
    {
        Product::withTrashed()->find($id)->restore();

        return back()->with('success', 'Product Successfully Restore');
    }

    public function productDelete($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);

        if ($product->image) {
            removeFile(filePath('product').'/'.@$product->image);
        }
        $product->forceDelete();

        return back()->with('success', 'Product Deleted successfully');
    }

    public function productTransfer()
    {
        $data['pageTitle'] = 'Product Transfer To Branchs';
        $data['other'] = 'active';
        $data['productTransferActive'] = 'active';
        $data['product'] = Product::select('id', 'name')->where('quantity', '>=', 0)->get();
        $data['warehouse'] = Warehouse::with('warehouseProducts')->get();

        $data['transferProduct'] = Transfer::with('product', 'warehouseFrom', 'warehouseTo')
            ->latest()
            ->get();

        return view('backend.products.product.transfer')->with($data);
    }

    public function productTransferPost(Request $request)
    {
        $request->validate([
            'product' => 'required|exists:products,id',
            'transfer_quantity' => 'required|integer|min:1',
            'from_warehouse' => 'required|exists:warehouses,id',
            'to_warehouse' => 'required|exists:warehouses,id|different:from_warehouse',
        ]);

        DB::beginTransaction();
        try {
            // Check if stock is available in the source warehouse
            $sourceStock = WarehouseProducts::where('warehouse_id', $request->from_warehouse)
                ->where('product_id', $request->product)
                ->first();

            if (! $sourceStock || $sourceStock->quantity < $request->transfer_quantity) {
                return redirect()->back()->with('error', 'Insufficient stock in the source warehouse.');
            }

            // Deduct from source warehouse
            $sourceStock->decrement('quantity', $request->transfer_quantity);

            // Add to destination warehouse
            $destinationStock = WarehouseProducts::where('warehouse_id', $request->to_warehouse)
                ->where('product_id', $request->product)
                ->first();

            if ($destinationStock) {
                $destinationStock->increment('quantity', $request->transfer_quantity);
            } else {
                // If no record exists, create a new one
                WarehouseProducts::create([
                    'warehouse_id' => $request->to_warehouse,
                    'product_id' => $request->product,
                    'quantity' => $request->transfer_quantity,
                ]);
            }

            // Record the transfer
            $transfer = Transfer::create([
                'product_id' => $request->product,
                'quantity' => $request->transfer_quantity,
                'from_warehouse_id' => $request->from_warehouse,
                'to_warehouse_id' => $request->to_warehouse,
            ]);

            DB::commit();

            activity()
                ->performedOn($transfer)
                ->causedBy(auth()->guard('admin')->user())
                ->event('product_transfer')
                ->withProperties([
                    'product_id' => $transfer->product->name,
                    'quantity' => $transfer->quantity,
                    'from_warehouse' => $transfer->warehouseFrom->name,
                    'to_warehouse' => $transfer->warehouseTo->name,

                ])
                ->log('product Transfered and processed');

            return redirect()->back()->with('success', 'Product successfully transferred.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'An error occurred: '.$e->getMessage());
        }
    }

    public function getWarehousesByProduct($productId)
    {
        $warehouses = WarehouseProducts::with('warehouse')->where('product_id', $productId)->get();

        return response()->json($warehouses);
    }

    public function PTDelete($id)
    {
        Transfer::find($id)->delete();

        return back()->with('success', 'Product Transfer Deleted successfully');
    }

    public function productTransferRestore($id)
    {
        Transfer::withTrashed()->find($id)->restore();

        return redirect()->route('admin.product.transfer')->with('success', 'Product Transfer  Successfully Restore');
    }

    public function productTransferDelete($id)
    {
        $product = Transfer::onlyTrashed()->findOrFail($id);

        $product->forceDelete();

        return redirect()->route('admin.product.transfer')->with('success', 'Product Transfer  Successfully Restore');
    }

    public function comboProduct()
    {
        $data['pageTitle'] = 'Combo Products';
        $data['other'] = 'active';
        $data['comboProduct'] = 'active';
        $data['combos'] = Combo::with('products')->get();

        return view('backend.products.product.comboProduct.index')->with($data);
    }

    public function comboProductCreate()
    {
        $data['pageTitle'] = 'Combo Product Create';
        $data['other'] = 'active';
        $data['comboProduct'] = 'active';

        $data['products'] = Product::select('id', 'name', 'sale_price', 'quantity')->where('quantity', '>', 0)->get();

        return view('backend.products.product.comboProduct.create')->with($data);
    }

    public function storeCombo(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'price' => 'required',
            'quantity' => 'required|integer|min:1',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            $combo = Combo::create([
                'name' => $validated['name'],
                'price' => $validated['price'],
                'quantity' => $validated['quantity'],
            ]);

            foreach ($validated['products'] as $productData) {
                $product = Product::findOrFail($productData['id']);
                $deductQuantity = $productData['quantity'] * $validated['quantity'];

                // Check if there is sufficient stock
                if ($product->quantity < $deductQuantity) {
                    // If insufficient stock, throw an exception
                    throw new \Exception("Insufficient stock for product ID: {$productData['id']}");
                }

                // Deduct the quantity from the product stock
                $product->quantity -= $deductQuantity;
                $product->save();

                // Attach the product to the combo with the specified quantity
                $combo->products()->attach($productData['id'], ['quantity' => $productData['quantity']]);

                // Create or update the warehouse-product relationship
                WarehouseProducts::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'warehouse_id' => $product->warehouse_id,
                    ],
                    [
                        'quantity' => $product->quantity,
                    ]
                );
            }

            // Commit the transaction
            DB::commit();

            return redirect()->route('admin.comboProduct.index')->with('success', 'Combo product created successfully');
        } catch (\Exception $e) {
            // Rollback the transaction in case of any error
            DB::rollBack();

            // Detach any products that were attached to the combo
            if (isset($combo)) {
                $combo->products()->detach();
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function viewCombo($id)
    {
        $data['combo'] = Combo::with('products')->find($id);
        $data['pageTitle'] = $data['combo']->name.' View';
        $data['other'] = 'active';
        $data['comboProduct'] = 'active';

        return view('backend.products.product.comboProduct.view')->with($data);
    }

    public function editCombo(Request $request, $id)
    {
        $data['combo'] = Combo::with('products')->find($id);
        $data['products'] = Product::select('id', 'name', 'sale_price', 'quantity')->where('quantity', '>', 0)->get();

        $data['pageTitle'] = $data['combo']->name.' Edit';
        $data['other'] = 'active';
        $data['comboProduct'] = 'active';

        if ($request->ajax()) {
            return response($data['combo']);
        }

        return view('backend.products.product.comboProduct.edit')->with($data);
    }

    public function updateCombo(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'price' => 'required',
            'quantity' => 'required|integer|min:1',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        // Start transaction
        DB::beginTransaction();

        try {
            // Find the existing combo
            $combo = Combo::findOrFail($id);

            // Restore previous product stock
            foreach ($combo->products as $oldProduct) {
                $oldQuantity = $oldProduct->pivot->quantity * $combo->quantity;
                $oldProduct->increment('quantity', $oldQuantity);

                // Update warehouse stock
                WarehouseProducts::where('product_id', $oldProduct->id)
                    ->where('warehouse_id', $oldProduct->warehouse_id)
                    ->increment('quantity', $oldQuantity);
            }

            // Detach old products
            $combo->products()->detach();

            // Update combo details
            $combo->update([
                'name' => $validated['name'],
                'price' => $validated['price'],
                'quantity' => $validated['quantity'],
            ]);

            // Process new products
            foreach ($validated['products'] as $productData) {
                $product = Product::findOrFail($productData['id']);
                $deductQuantity = $productData['quantity'] * $validated['quantity'];

                // Check if there is enough stock
                if ($product->quantity < $deductQuantity) {
                    throw new \Exception("Insufficient stock for product ID: {$productData['id']}");
                }

                // Deduct new stock
                $product->decrement('quantity', $deductQuantity);

                // Attach new products to combo
                $combo->products()->attach($productData['id'], ['quantity' => $productData['quantity']]);

                // Update warehouse stock
                WarehouseProducts::updateOrCreate(
                    ['product_id' => $product->id, 'warehouse_id' => $product->warehouse_id],
                    ['quantity' => $product->quantity]
                );
            }

            // Commit transaction
            DB::commit();

            return redirect()->route('admin.comboProduct.index')->with('success', 'Combo product updated successfully');
        } catch (\Exception $e) {
            // Rollback in case of error
            DB::rollBack();

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function deleteCombo($id)
    {
        $comboProduct = Combo::findOrFail($id);

        foreach ($comboProduct->products as $product) {
            $comboProductQuantity = $product->pivot->quantity;
            // Multiply by the total quantity of the combo being deleted
            $totalRestoredQuantity = $comboProductQuantity * $comboProduct->quantity;
            // Restore stock for the product
            $product->quantity += $totalRestoredQuantity;
            $product->save();
        }
        // Delete the combo product
        $comboProduct->products()->detach();
        $comboProduct->delete();

        return back()->with('success', 'Combo product deleted successfully, and stock quantities restored.');
    }

    public function search(Request $request)
    {
        $search = $request->get('q');        // Search query
        $page = $request->get('page', 1);  // Page number for pagination

        $productsQuery = Product::query()
            ->where('name', 'like', "%{$search}%")
            ->orWhere('code', 'like', "%{$search}%");

        $products = $productsQuery->paginate(20, ['id', 'name', 'code'], 'page', $page);

        return response()->json([
            'products' => $products->items(),
            'pagination' => [
                'more' => $products->hasMorePages(),
            ],
        ]);
    }

    public function import(Request $request, ProductService $productService)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt,xlsx,xls',
        ]);

        $adminId = auth()->guard('admin')->user()->id;
        $path = $request->file('csv_file')->getRealPath();
        $rows = array_map('str_getcsv', file($path));
        $header = array_map('trim', array_shift($rows));
        $chunks = array_chunk($rows, 100);

        $queueEnabled = config('queue.enabled', false);

        foreach ($chunks as $chunk) {
            if ($queueEnabled) {
                ProcessProductCsvChunk::dispatch($chunk, $header, $adminId)->onQueue('default');

                return back()->with('success', 'CSV file has been successfully uploaded and is now being processed. Please wait while we import the products.');
            } else {
                $productService->processChunkNow($chunk, $header, $adminId);
            }
        }

        return back()->with('success', 'CSV file has been successfully processed');
    }

    public function generateDescriptionAI(Request $request)
    {
        $prompt = $request->input('prompt');

        if (! $prompt) {
            return response()->json([
                'error' => 'Prompt is required',
            ], 422);
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.config('services.openai.key'),
            'Content-Type' => 'application/json',
        ])->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => 'deepseek/deepseek-chat:free',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => "Generate a professional product description in clean HTML using <h1>, <h2>, <p>, and <ul> tags. Structure it like a modern eCommerce product page. Product info: {$prompt}",
                ],
            ],
        ]);

        $description = $response['choices'][0]['message']['content'] ?? 'AI could not generate description.';

        return response()->json(['description' => $description]);
    }
}
