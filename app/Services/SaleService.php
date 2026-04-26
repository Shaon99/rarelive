<?php

namespace App\Services;

use App\Events\ProductLowStock;
use App\Events\SaleCreated;
use App\Models\Combo;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Sales;
use App\Models\SalesProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SteadFast\SteadFastCourierLaravelPackage\Facades\SteadfastCourier;

class SaleService
{
    protected CarrybeeCourierService $carrybeeCourierService;

    public function __construct(CarrybeeCourierService $carrybeeCourierService)
    {
        $this->carrybeeCourierService = $carrybeeCourierService;
    }

    public function storeSale(Request $request)
    {
        DB::beginTransaction();

        try {
            $invoice_no = generateUniqueSerial('sales', 'invoice_no', 'R');
            $customer = $this->getCustomer($request->customer_phone);
            $payment_status = $this->determinePaymentStatus($request);

            $sale = $this->createSaleRecord(
                $request,
                $invoice_no,
                $payment_status,
                null,
                $customer->id,
                null
            );

            $sendSteadfast = $request->has('steadFast') && (int) $request->steadFast === 1;
            $sendCarrybee = (int) $request->input('carrybee_send', 0) === 1;

            if ($sendSteadfast && $sendCarrybee) {
                throw new \Exception('Please choose only one courier: Steadfast or Carrybee.');
            }

            // Step 5: If Steadfast selected, send to courier and update sale
            if ($sendSteadfast) {
                $courier_response = $this->sendOrderToCourier(
                    $invoice_no,
                    $customer,
                    $request->note,
                    $request->duepayment,
                    $request->address
                );

                if (! $courier_response || ! isset($courier_response['success']) || ! $courier_response['success']) {
                    throw new \Exception('Steadfast API failed. Order not sent.');
                }

                $this->updateSaleWithCourierResponse($sale, $courier_response, 'steadfast');
            }

            if ($sendCarrybee) {
                $accountKey = trim((string) $request->input('carrybee_account_key', ''));
                if ($accountKey === '') {
                    throw new \Exception('Carrybee account is required.');
                }

                $account = CarrybeeIntegrationService::accountByKey($accountKey);
                if (! $account) {
                    throw new \Exception('Invalid Carrybee account selected.');
                }

                $carrybeeResult = $this->carrybeeCourierService->sendOrders($account, collect([$sale]));
                if (! ($carrybeeResult['success'] ?? false)) {
                    throw new \Exception($carrybeeResult['message'] ?? 'Carrybee API failed. Order not sent.');
                }
            }

            $this->handleSaleTransactions($sale, $request);
            $this->processPurchasedProducts($request, $sale);

            event(new SaleCreated($sale));

            $this->logSaleActivity($sale, $request, $customer, $payment_status);

            DB::commit();

            return Sales::with(['customer', 'salesProduct.product', 'salesProduct.combo'])->find($sale->id);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Sale creation failed: '.$e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create sale: '.$e->getMessage(),
            ], 422);
        }
    }

    private function updateSaleWithCourierResponse($sale, $response, $courier_name)
    {
        $sale->update([
            'consignment_id' => $response['consignment_id'] ?? null,
            'courier_name' => $courier_name,
            'status' => $response['status'] ?? null,
            'system_status' => 'pending',
            'due_amount' => $response['consignment']['cod_amount'] ?? $sale->grand_total - $sale->paid_amount,
            'created_at' => $response['consignment']['created_at'] ?? now(),
            'updated_at' => $response['consignment']['updated_at'] ?? now(),
        ]);
    }

    private function logSaleActivity($sale, $request, $customer, $payment_status)
    {
        activity()
            ->performedOn($sale)
            ->causedBy(auth()->guard('admin')->user())
            ->event('sales_created')
            ->withProperties([
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_address' => $request->address,
                'product_ids' => $request->product_id,
                'product_type' => $request->type,
                'payment_status' => $payment_status == 0 ? 'Due' : 'Paid',
                'courier_response' => $sale->consignment_id ? 'Sent to Courier' : 'N/A',
            ])
            ->log('Sale created and processed: Invoice No. '.$sale->invoice_no);
    }

    private function handleSaleTransactions(Sales $sales, Request $request)
    {
        if ($request->draft == 1) {
            return;
        }

        $customerName = optional($sales->customer)->name ?? 'Unknown Customer';
        $grandTotal = $sales->grand_total;
        $paidAmount = $sales->paid_amount;

        $isSteadfast = $request->steadFast == 1;
        $isCarrybee = (int) $request->input('carrybee_send', 0) === 1;
        $isCourier = $isSteadfast || $isCarrybee;
        $isDue = $request->due == 1;
        $hasPayment = $paidAmount > 0 && $request->payment_by;

        $paymentMethodId = null;
        $transactionTag = null;
        $noteSuffix = '';

        if ($isSteadfast) {
            $paymentMethodId = PaymentMethod::where('type', 'STEADFAST')->value('id');
            $noteSuffix = ' (STEADFAST)';
            $transactionTag = 'steadfast_credit';
        } elseif ($isCarrybee) {
            $paymentMethodId = PaymentMethod::where('type', 'CARRYBEE')->value('id');
            $noteSuffix = ' (CARRYBEE)';
            $transactionTag = 'carrybee_credit';
        } elseif ($isDue) {
            $noteSuffix = ' (Due)';
            $transactionTag = 'due_credit';
        } else {
            $transactionTag = 'regular_credit';
        }

        if ($hasPayment) {
            $paymentMethodId = $request->payment_by;
        }

        // Shared transaction data base
        $baseTransactionData = [
            'amount' => $grandTotal,
            'debit' => null,
            'credit' => 'credit',
            'transaction_type' => 'sales_on_credit',
            'transaction_tag' => $transactionTag,
            'transaction_date' => now(),
            'note' => 'SALES: '.$customerName.$noteSuffix,
            'payment_method_id' => $paymentMethodId,
        ];

        // Remove customer_id if Steadfast
        if (! $isCourier) {
            $baseTransactionData['customer_id'] = $sales->customer->id;
        }

        // Handle credit transaction (sales_on_credit)
        $existingCreditTransaction = $sales->transactions()
            ->where('transaction_type', 'sales_on_credit')
            ->where('transaction_tag', $transactionTag)
            ->first();

        if ($existingCreditTransaction) {
            $existingCreditTransaction->update($baseTransactionData);
        } else {
            $sales->transactions()->create($baseTransactionData);
        }

        // Handle payment received (debit)
        if ($hasPayment) {
            $paymentAccount = PaymentMethod::findOrFail($request->payment_by);

            $existingPaymentTransaction = $sales->transactions()
                ->where('transaction_type', 'payment_received')
                ->where('transaction_tag', 'payment_received_'.$paymentAccount->id)
                ->where('amount', $paidAmount)
                ->first();

            if (! $existingPaymentTransaction) {
                $sales->transactions()->create([
                    'customer_id' => $sales->customer->id,
                    'amount' => $paidAmount,
                    'debit' => 'debit',
                    'credit' => null,
                    'transaction_type' => 'payment_received',
                    'transaction_tag' => 'payment_received_'.$paymentAccount->id,
                    'transaction_date' => now(),
                    'note' => 'PAYMENT RECEIVED: '.$customerName,
                    'payment_method_id' => $paymentAccount->id,
                ]);

                $paymentAccount->increment('current_balance', $paidAmount);
            }
        }
    }

    // Determine payment status based on the comparison of grand_total and payment
    private function determinePaymentStatus($request)
    {
        if ($request->grand_total > $request->payment) {
            return 2;
        }

        if ($request->grand_total == $request->payment) {
            return 1;
        }

        return 2;
    }

    // Create the sale record in the database
    private function createSaleRecord($request, $invoice_no, $payment_status, $response, $customerID, $courier_name = null)
    {
        if ($request->draft == 1) {
            Cache::forget('sidebar_draft_sales_count');
        }

        return Sales::create([
            'invoice_no' => $invoice_no,
            'user_id' => auth()->guard('admin')->user()->id,
            'lead_id' => $request->lead??null,
            'consignment_id' => $response['consignment_id'] ?? null,
            'courier_name' => $courier_name ?? null,
            'status' => $response['status'] ?? null,
            'system_status' => $request->draft == 1
                ? 'draft'
                : (isset($response['consignment_id'])
                    ? 'pending'
                    : ($payment_status == 1
                        ? 'completed'
                        : 'pending')),
            'customer_id' => $customerID,
            'delivery_address' => $request->address,
            'discount' => $request->discount,
            'warehouse_id' => $request->warehouse,
            'shipping_cost' => $request->shipping_cost,
            'payment_method' => $request->payment_by,
            'payment_status' => $payment_status,
            'sub_total' => $request->subtotal,
            'total_qty' => $request->item,
            'grand_total' => $request->grand_total,
            'paid_amount' => $request->payment,
            'platform' => $request->platform ?? 'Others',
            'due_amount' => $response['consignment']['cod_amount'] ?? ($request->grand_total - $request->payment),
            'note' => $request->note ?? 'Handel with care',
            'created_at' => $response['consignment']['created_at'] ?? now(),
            'updated_at' => $response['consignment']['updated_at'] ?? now(),
        ]);
    }

    // Update customer due balance if applicable
    private function getCustomer($customer_phone)
    {
        $customer = Customer::where('phone', $customer_phone)->first();

        return $customer;
    }

    // Send order data to the courier service
    private function sendOrderToCourier($invoice_no, $customer, $note, $dueAmount, $address)
    {
        try {
            // Validate required data
            if (! $invoice_no || ! $customer || ! $customer->name || ! $customer->phone || ! $address) {
                return [
                    'success' => false,
                    'message' => 'Missing required order data.',
                    'error' => 'Invalid or incomplete data provided.',
                ];
            }

            $orderData = [
                'invoice' => $invoice_no,
                'recipient_name' => $customer->name,
                'recipient_phone' => $customer->phone,
                'recipient_address' => $address,
                'cod_amount' => $dueAmount,
                'note' => $note ?? 'Handel with care',
            ];

            // Attempt to send order to courier service
            $response = SteadfastCourier::placeOrder($orderData);

            // Validate response format
            if (! isset($response['consignment']['consignment_id'])) {
                return [
                    'success' => false,
                    'message' => 'Courier service did not return a valid consignment ID.',
                    'error' => $response['error'] ?? 'Unknown API response issue.',
                ];
            }

            return [
                'success' => true,
                'message' => 'Order placed successfully.',
                'consignment_id' => $response['consignment']['consignment_id'],
                'status' => $response['consignment']['status'] ?? 'Pending',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to place order with courier service. Please try again later.',
                'error' => $e->getMessage(),
            ];
        }
    }

    // Process each purchased product, update the stock, and create associated sales product records
    private function processPurchasedProducts($request, $sales)
    {
        foreach ($request->product_id as $index => $product_id) {
            $type = $request->type[$index];
            $qty = abs($request->qty[$index]);
            $netUnitPrice = $request->net_unit_price[$index];
            $salePrice = $request->sale_price[$index];
            $discount = $request->discount_price[$index];
            $discount_type = $request->discount_type[$index];
            $warehouseId = $request->warehouse;

            if ($type === 'product') {
                $product = Product::with('warehouses')->findOrFail($product_id);
                $warehouseProduct = $product->warehouses->where('warehouse_id', $warehouseId)->first();

                if ($warehouseProduct) {
                    $oldWarehouseQty = $warehouseProduct->quantity;
                    $warehouseProduct->quantity -= $qty;
                    $warehouseProduct->save();

                    activity()
                        ->performedOn($warehouseProduct)
                        ->causedBy(auth()->guard('admin')->user())
                        ->event('warehouse_stock_deducted')
                        ->withProperties([
                            'product_name' => $product->name,
                            'warehouse_id' => $warehouseId,
                            'deducted_qty' => $qty,
                            'old_qty' => $oldWarehouseQty,
                            'new_qty' => $warehouseProduct->quantity,
                        ])
                        ->log("Warehouse stock deducted for '{$product->name}'");
                } else {
                    Log::warning("Warehouse stock not found for product ID {$product_id} in warehouse ID {$warehouseId}");

                    continue;
                }

                $oldQty = $product->quantity;

                $product->quantity -= $qty;
                $product->save();

                if ($product->quantity <= $product->low_quantity_alert) {
                    event(new ProductLowStock($product));
                }

                // Calculate discount amount
                $discountAmount = 0;
                if ($discount_type === 'percent') {
                    $discountAmount = ($salePrice * $qty) * ($discount / 100);
                } else {
                    $discountAmount = $discount * $qty;
                }
                $total = ($salePrice * $qty) - $discountAmount;

                SalesProduct::create([
                    'sales_id' => $sales->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $netUnitPrice,
                    'sale_price' => $salePrice,
                    'discount' => $discount,
                    'discount_type' => $discount_type,
                    'total' => $total,
                    'warehouse_id' => $warehouseId,
                ]);
            }

            if ($type === 'combo') {
                $combo = Combo::findOrFail($product_id);
                $oldQty = $combo->quantity;
                $combo->quantity -= $qty;
                $combo->save();

                activity()
                    ->performedOn($combo)
                    ->causedBy(auth()->guard('admin')->user())
                    ->event('combo_stock_deducted')
                    ->withProperties([
                        'combo_name' => $combo->name,
                        'deducted_qty' => $qty,
                        'old_qty' => $oldQty,
                        'new_qty' => $combo->quantity,
                    ])
                    ->log("Combo stock updated after sale for '{$combo->name}'");

                SalesProduct::create([
                    'sales_id' => $sales->id,
                    'combo_id' => $combo->id,
                    'quantity' => $qty,
                    'unit_price' => $netUnitPrice,
                    'sale_price' => $salePrice,
                    'total' => $qty * $salePrice,
                    'warehouse_id' => $warehouseId,
                ]);
            }
        }
    }

    public function updateSale($request, $id)
    {
        DB::beginTransaction();

        try {
            $sale = Sales::findOrFail($id);

            // Update customer if phone changed
            $customer = $this->getCustomer($request->customer_phone);
            if (! $customer) {
                throw new \Exception('Customer not found.');
            }

            $payment_status = $this->determinePaymentStatus($request);

            // If courier update is needed
            $courier_response = null;
            if ($request->has('steadFast') && $request->steadFast == 1) {
                $courier_response = $this->sendOrderToCourier(
                    $sale->invoice_no,
                    $customer,
                    $request->note,
                    $request->duepayment,
                    $request->address
                );

                if (! $courier_response || ! isset($courier_response['success']) || ! $courier_response['success']) {
                    throw new \Exception('Steadfast API failed. Order not sent.');
                }
            }

            // Update sale record
            $sale->update([
                'lead_id' => $request->lead,
                'customer_id' => $customer->id,
                'delivery_address' => $request->address,
                'discount' => $request->discount,
                'warehouse_id' => $request->warehouse,
                'shipping_cost' => $request->shipping_cost,
                'payment_method' => $request->payment_by,
                'payment_status' => $payment_status,
                'sub_total' => $request->subtotal,
                'total_qty' => $request->item,
                'grand_total' => $request->grand_total,
                'paid_amount' => $request->payment,
                'platform' => $request->platform ?? 'Others',
                'due_amount' => $courier_response['consignment']['cod_amount'] ?? ($request->grand_total - $request->payment),
                'note' => $request->note ?? 'Handle with care',
                'system_status' => $request->draft == 1
                    ? 'draft'
                    : (isset($courier_response['consignment_id'])
                        ? 'pending'
                        : ($payment_status == 1
                            ? 'completed'
                            : 'pending')),
                'consignment_id' => $courier_response['consignment_id'] ?? $sale->consignment_id,
                'courier_name' => $request->steadFast == 1 ? 'steadfast' : null,
                'status' => $courier_response['status'] ?? $sale->status,
                'updated_at' => $courier_response['consignment']['updated_at'] ?? now(),
            ]);

            Cache::forget('sidebar_draft_sales_count');

            // === STEP 1: Restore stock from old saleProducts ===
            foreach ($sale->salesProduct as $salesProduct) {
                if ($salesProduct->product_id) {
                    $product = Product::find($salesProduct->product_id);
                    if ($product) {
                        $warehouseProduct = $product->warehouses()->where('warehouse_id', $salesProduct->warehouse_id)->first();
                        if ($warehouseProduct) {
                            $warehouseProduct->quantity += $salesProduct->quantity;
                            $warehouseProduct->save();
                        }

                        $product->quantity += $salesProduct->quantity;
                        $product->save();

                        activity()
                            ->performedOn($product)
                            ->causedBy(auth()->guard('admin')->user())
                            ->event('sale_update_stock_rollback')
                            ->withProperties([
                                'product_name' => $product->name,
                                'restored_qty' => $salesProduct->quantity,
                                'new_qty' => $product->quantity,
                            ])
                            ->log('Restored stock from old sale during update');
                    }
                }

                if ($salesProduct->combo_id) {
                    $combo = Combo::find($salesProduct->combo_id);
                    if ($combo) {
                        $combo->quantity += $salesProduct->quantity;
                        $combo->save();

                        activity()
                            ->performedOn($combo)
                            ->causedBy(auth()->guard('admin')->user())
                            ->event('sale_update_combo_rollback')
                            ->withProperties([
                                'combo_name' => $combo->name,
                                'restored_qty' => $salesProduct->quantity,
                                'new_qty' => $combo->quantity,
                            ])
                            ->log('Restored combo stock during sale update');
                    }
                }
            }

            // === STEP 3: Add new products and transactions ===
            $this->handleSaleTransactions($sale, $request);
            $this->processPurchasedProducts($request, $sale);

            DB::commit();

            return;
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Sale update failed: '.$e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update sale: '.$e->getMessage(),
            ], 422);
        }
    }
}
