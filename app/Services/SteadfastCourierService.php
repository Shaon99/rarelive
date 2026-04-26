<?php

namespace App\Services;

use App\Models\GeneralSetting;
use App\Models\PaymentMethod;
use App\Models\Sales;
use Illuminate\Support\Facades\DB;
use SteadFast\SteadFastCourierLaravelPackage\SteadfastCourier;

class SteadfastCourierService extends SteadfastCourier
{
    public function __construct()
    {
        $generalSetting = cache()->remember('general_setting_courier', 60 * 60, function () {
            return GeneralSetting::select('id', 'steadfast_api_key', 'steadfast_api_secret')->first();
        });

        // Use database values if available, otherwise use config values
        $this->baseUrl = config('steadfast-courier.base_url');
        $this->apiKey = $generalSetting ? $generalSetting->steadfast_api_key : config('steadfast-courier.api_key');
        $this->secretKey = $generalSetting ? $generalSetting->steadfast_api_secret : config('steadfast-courier.secret_key');
    }

    /**
     * Send sales orders to Steadfast courier.
     *
     * @return array
     */
    public function sendToSteadfast(array $saleIds)
    {
        // Fetch sales that are valid for consignment
        $sales = Sales::whereIn('id', $saleIds)
            ->whereNull('consignment_id')
            ->where('system_status', '!=', 'cancelled')
            ->get();

        if ($sales->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No valid sales records found or all selected sales already have consignment IDs.',
                'status' => 404,
            ];
        }

        // Prepare orders data for Steadfast API
        $ordersData = [];
        $salesMap = [];

        foreach ($sales as $sale) {
            $customer = $sale->customer;
            $ordersData[] = [
                'invoice' => $sale->invoice_no,
                'recipient_name' => $customer->name,
                'recipient_phone' => $customer->phone,
                'recipient_address' => $sale->delivery_address,
                'cod_amount' => $sale->due_amount,
                'note' => $sale->note ?? 'Handel with care',
            ];
            $salesMap[$sale->invoice_no] = $sale->id;

            $sale->update([
                'system_status' => 'pending',
            ]);

            $paymentAccount = PaymentMethod::where('type', 'STEADFAST')->first();

            if (! $paymentAccount) {
                $paymentAccount = PaymentMethod::create([
                    'type' => 'STEADFAST',
                    'name' => 'STEADFAST',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $sale->transactions()->create([
                'amount' => $sale->grand_total,
                'debit' => null,
                'credit' => 'credit',
                'transaction_type' => 'sales_on_credit',
                'transaction_date' => now(),
                'note' => 'SALES: '.$customer->name,
                'payment_method_id' => $paymentAccount->id,
            ]);
        }

        // Send bulk orders to Steadfast
        $response = SteadfastCourier::bulkCreateOrders($ordersData);

        return $this->handleSteadfastResponse($response, $salesMap);
    }

    /**
     * Handle the response from Steadfast API.
     *
     * @param  array  $response
     * @param  array  $salesMap
     * @return array
     */
    private function handleSteadfastResponse($response, $salesMap)
    {
        $errors = [];
        $updatedSales = [];

        if (isset($response['data']) && is_array($response['data'])) {
            foreach ($response['data'] as $consignment) {
                $invoice = $consignment['invoice'] ?? null;
                $consignmentId = $consignment['consignment_id'] ?? null;
                $status = 'in_review';
                $error = $consignment['error'] ?? null;

                if ($error) {
                    $decodedError = json_decode($error, true);
                    $errorMessage = is_array($decodedError) ? implode(', ', $decodedError) : ($error ?: 'Unknown error');
                    $errors[] = "Invoice {$invoice}: {$errorMessage}";
                } elseif ($invoice && isset($salesMap[$invoice])) {
                    $updatedSales[] = [
                        'id' => $salesMap[$invoice],
                        'invoice_no' => $invoice,
                        'consignment_id' => $consignmentId,
                        'status' => $status,
                        'courier_name' => 'steadfast',
                        'updated_at' => now(),
                    ];
                }
            }
        }

        // Handle errors
        if (! empty($errors)) {
            return [
                'success' => false,
                'message' => 'Some orders failed to process.',
                'errors' => $errors,
                'status' => 422,
            ];
        }

        // Update sales table if there are successful orders
        if (! empty($updatedSales)) {
            DB::table('sales')->upsert(
                $updatedSales,
                ['id'],
                ['invoice_no', 'consignment_id', 'status', 'courier_name', 'updated_at']
            );
        }

        return [
            'success' => true,
            'message' => 'Orders sent and updated successfully.',
            'response' => $response,
            'status' => 200,
        ];
    }

    /**
     * Get the current balance for Steadfast.
     *
     * @return array
     */
    public function getSteadfastCurrentBalance()
    {
        $response = SteadfastCourier::getCurrentBalance();

        if (isset($response['status']) && $response['status'] === 200) {
            return [
                'success' => true,
                'current_balance' => $response['current_balance'],
                'status' => 200,
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to fetch current balance.',
            'status' => $response['status'] ?? 500,
        ];
    }

    public function sendToSteadfastSingleOrder($orderId)
    {
        try {
            // Begin transaction
            DB::beginTransaction();

            $sale = Sales::with('customer')
                ->whereNull('consignment_id')
                ->where('system_status', '!=', 'cancelled')
                ->lockForUpdate()
                ->find($orderId);

            if (! $sale) {
                return [
                    'success' => false,
                    'message' => 'No valid sales records found or sales already have consignment IDs.',
                    'status' => 404,
                ];
            }

            $customer = $sale->customer;
            $ordersData = [
                'invoice' => $sale->invoice_no,
                'recipient_name' => $customer->name,
                'recipient_phone' => $customer->phone,
                'recipient_address' => $sale->delivery_address,
                'cod_amount' => $sale->due_amount,
                'note' => $sale->note ?? 'Handel with care',
            ];

            // Send order to Steadfast
            $response = SteadfastCourier::placeOrder($ordersData);

            if (! isset($response['consignment']['consignment_id'])) {
                DB::rollBack();

                return [
                    'success' => false,
                    'message' => 'Courier service did not return a valid consignment ID.',
                    'error' => $response['error'] ?? 'Unknown API response issue.',
                    'status' => 422,
                ];
            }

            // Update sale record
            $sale->update([
                'consignment_id' => $response['consignment']['consignment_id'],
                'courier_name' => 'steadfast',
                'status' => 'in_review',
                'due_amount' => $response['consignment']['cod_amount'] ?? $sale->due_amount,
                'system_status' => 'pending',
                'updated_at' => now(),
            ]);

            // Create transaction record
            $paymentAccount = PaymentMethod::where('type', 'STEADFAST')->first();
            if (! $paymentAccount) {
                DB::rollBack();

                return [
                    'success' => false,
                    'message' => 'Payment method not found.',
                    'status' => 422,
                ];
            }

            $sale->transactions()->create([
                'amount' => $sale->grand_total,
                'debit' => null,
                'credit' => 'credit',
                'transaction_type' => 'sales_on_credit',
                'transaction_date' => now(),
                'note' => 'SALES: '.$customer->name,
                'payment_method_id' => $paymentAccount->id,
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Order successfully sent to Steadfast.',
                'consignment_id' => $response['consignment']['consignment_id'],
                'status' => 200,
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'An error occurred while processing the order.',
                'error' => $e->getMessage(),
                'status' => 500,
            ];
        }
    }
}
