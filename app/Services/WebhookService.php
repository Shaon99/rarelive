<?php

namespace App\Services;

use App\Constants\CommonConstant;
use App\Models\GeneralSetting;
use App\Models\PaymentMethod;
use App\Models\Sales;

class WebhookService
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Process webhook from courier
     *
     * @return array
     */
    public function processWebhook(string $courier, array $data)
    {
        try {
            if (! $this->isValidWebhookData($data)) {
                return $this->errorResponse('Invalid request data', 400);
            }

            $sale = $this->getSale($data['consignment_id'], $data['invoice']);
            if (! $sale) {
                return $this->errorResponse('Invalid consignment ID.', 404);
            }

            $this->updateSaleStatus($sale, $data);

            activity()
                ->performedOn($sale)
                ->causedBy(auth()->guard('admin')->user())
                ->event('sales_status_update')
                ->withProperties([
                    'customer_name' => $sale->customer->name ?? 'N/A',
                    'customer_phone' => $sale->customer->phone ?? 'N/A',
                    'courier_response' => $data ?? 'N/A',
                    'courier' => $courier,
                ])
                ->log('Sale status updated: Invoice No. '.$sale->invoice_no.', Status: '.$sale->status);

            return $this->successResponse('Webhook processed successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse('Internal server error.', 500);
        }
    }

    /**
     * Validate webhook data
     *
     * @return bool
     */
    private function isValidWebhookData(array $data)
    {
        return isset($data['notification_type'], $data['invoice'], $data['consignment_id']) &&
            $data['notification_type'] === 'delivery_status';
    }

    /**
     * Retrieve sale based on consignment ID and invoice number
     *
     * @return Sales|null
     */
    private function getSale(string $consignmentId, string $invoiceNo)
    {
        return Sales::where('consignment_id', $consignmentId)
            ->where('invoice_no', $invoiceNo)
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->first();
    }

    /**
     * Update sale status based on webhook data
     *
     * @return void
     */
    private function updateSaleStatus(Sales $sale, array $data)
    {
        $sale->status = $data['status'];
        $sale->shipping_cost = $data['delivery_charge'] ?? 0;

        if ($data['status'] === 'pending') {
            $this->handlePendingStatus($sale, $data);
        }

        switch ($sale->status) {
            case 'delivered':
                $this->markAsDelivered($sale, $data);
                break;

            case 'cancelled':
                $this->markAsCancelled($sale, $data);
                break;

            case 'partial_delivered':
                $this->markAsPartialDelivered($sale, $data);
                break;

            default:
                $sale->system_status = 'pending';
        }

        $sale->save();
    }

    /**
     * Handle 'pending' status updates
     *
     * @return void
     */
    private function handlePendingStatus(Sales $sale, array $data)
    {
        $paymentAccount = PaymentMethod::where('type', 'STEADFAST')->first();
        $totalCost = $sale->shipping_cost;

        $sale->transactions()->create([
            'amount' => $totalCost,
            'debit' => 'debit',
            'credit' => null,
            'transaction_type' => 'courier_shipping_charge',
            'transaction_date' => now(),
            'note' => 'COURIER SHIPPING CHARGE: '.$sale->invoice_no,
            'payment_method_id' => $paymentAccount->id,
        ]);
    }

    private function markAsDelivered(Sales $sale, array $data)
    {
        $sale->payment_status = 1;
        $sale->cod_charge = $data['cod_amount'] > 0 ? ($data['cod_amount'] - $sale->shipping_cost) * (generalSetting()->steadfast_cod_charge / 100) ?? 0.01 : 0;
        $sale->due_amount = 0.00;
        $sale->paid_amount = $data['cod_amount'];
        $sale->system_status = 'completed';
        $sale->updated_at = $data['updated_at'] ?? now();

        $paymentAccount = PaymentMethod::where('type', 'STEADFAST')->first();
        $totalCost = $sale->cod_charge;

        $sale->transactions()->create([
            'amount' => $totalCost,
            'debit' => 'debit',
            'credit' => null,
            'transaction_type' => 'courier_cod_charge',
            'transaction_date' => now(),
            'note' => 'COURIER COD CHARGE: '.$sale->invoice_no,
            'payment_method_id' => $paymentAccount->id,
        ]);

        $receivedAmount = $sale->paid_amount - ($totalCost + $sale->shipping_cost);
        if ($sale->cod_charge > 0) {
            $sale->transactions()->create([
                'amount' => $receivedAmount,
                'debit' => 'debit',
                'credit' => null,
                'transaction_type' => 'payment_received',
                'transaction_date' => now(),
                'note' => 'PAYMENT RECEIVED: '.$sale->invoice_no,
                'payment_method_id' => $paymentAccount->id,
            ]);
        }
    }

    private function markAsCancelled(Sales $sale, array $data)
    {
        $sale->system_status = 'cancelled';
        $sale->cod_charge = 0.00;
        $sale->due_amount = 0.00;
        $sale->paid_amount = 0.00;
        $sale->updated_at = $data['updated_at'] ?? now();
        $sale->return_status = CommonConstant::PENDING;
    }

    private function markAsPartialDelivered(Sales $sale, array $data)
    {
        $sale->system_status = 'partial_delivered';
        $sale->payment_status = 3;
        $sale->cod_charge = $data['cod_amount'] > 0 ? ($data['cod_amount'] - $sale->shipping_cost) * (generalSetting()->steadfast_cod_charge / 100) ?? 0.01 : 0;
        $sale->due_amount = 0.00;
        $sale->paid_amount = $data['cod_amount'];

        $paymentAccount = PaymentMethod::where('type', 'STEADFAST')->first();
        $totalCost = $sale->cod_charge;

        $sale->transactions()->create([
            'amount' => $totalCost,
            'debit' => 'debit',
            'credit' => null,
            'transaction_type' => 'courier_cod_charge',
            'transaction_date' => now(),
            'note' => 'COURIER COD CHARGE: '.$sale->invoice_no,
            'payment_method_id' => $paymentAccount->id,
        ]);

        $receivedAmount = $sale->paid_amount - ($totalCost + $sale->shipping_cost);

        if ($sale->cod_charge > 0) {
            $sale->transactions()->create([
                'amount' => $receivedAmount,
                'debit' => null,
                'credit' => 'credit',
                'transaction_type' => 'payment_received',
                'transaction_date' => now(),
                'note' => 'PAYMENT RECEIVED: '.$sale->invoice_no,
                'payment_method_id' => $paymentAccount->id,
            ]);
        }

        $this->stockService->adjustStockForCancelledSale($sale);
    }

    private function successResponse($message)
    {
        return ['status' => 'success', 'message' => $message, 'code' => 200];
    }

    private function errorResponse($message, $code)
    {
        return ['status' => 'error', 'message' => $message, 'code' => $code];
    }
}
