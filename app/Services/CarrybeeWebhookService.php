<?php

namespace App\Services;

use App\Constants\CommonConstant;
use App\Models\GeneralSetting;
use App\Models\PaymentMethod;
use App\Models\Sales;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Carrybee webhooks: POST JSON to {@see \App\Http\Controllers\Admin\SalesController::handleCarrybeeWebhook}
 * route name `carrybeeWebhook`, path `/carrybee-webhook` (GET/HEAD for URL checks).
 *
 * Fee updates (`shipping_cost` = delivery_fee, `cod_charge` = cod_fee) use events such as
 * `order.created`, `order.updated`, and may also read nested keys under `data` / `order`.
 */
class CarrybeeWebhookService
{
    public function __construct(
        protected StockService $stockService
    ) {}

    /**
     * @return array{status: string, message: string, code: int}
     */
    public function processPayload(array $payload): array
    {
        $event = $payload['event'] ?? null;
        if (! is_string($event) || $event === '') {
            // Carrybee URL / integration checks often POST without an order payload — respond 202 Accepted.
            return $this->successResponse('Webhook endpoint OK.');
        }

        if ((int) GeneralSetting::query()->value('enable_carrybee') !== 1) {
            // Carrybee’s validator sends real-looking payloads; still require HTTP 202 for the check to pass.
            return $this->successResponse('Webhook acknowledged (Carrybee sending is disabled in app settings).');
        }

        $consignmentId = isset($payload['consignment_id']) ? (string) $payload['consignment_id'] : '';
        $merchantOrderId = isset($payload['merchant_order_id']) && $payload['merchant_order_id'] !== null
            ? (string) $payload['merchant_order_id']
            : '';

        if ($consignmentId === '' && $merchantOrderId === '') {
            // Carrybee’s validator and some events ship without IDs — still require 202 + integration header.
            return $this->successResponse('Webhook acknowledged (no order identifiers in payload).');
        }

        $sale = $this->findCarrybeeSale($consignmentId, $merchantOrderId);
        if (! $sale) {
            return $this->successResponse('Webhook acknowledged (no matching Carrybee sale in this system).');
        }

        try {
            return $this->dispatchEvent($sale, $event, $payload);
        } catch (\Throwable $e) {
            Log::error('Carrybee webhook failed', [
                'event' => $event,
                'consignment_id' => $consignmentId,
                'message' => $e->getMessage(),
            ]);

            return $this->errorResponse('Internal server error.', 500);
        }
    }

    private function findCarrybeeSale(string $consignmentId, string $merchantOrderId): ?Sales
    {
        $scope = function ($query) use ($consignmentId, $merchantOrderId): void {
            if ($consignmentId !== '' && $merchantOrderId !== '') {
                $query->where(function ($q) use ($consignmentId, $merchantOrderId) {
                    $q->where('consignment_id', $consignmentId)
                        ->orWhere('invoice_no', $merchantOrderId);
                });

                return;
            }
            if ($consignmentId !== '') {
                $query->where('consignment_id', $consignmentId);

                return;
            }
            if ($merchantOrderId !== '') {
                $query->where('invoice_no', $merchantOrderId);
            }
        };

        $byCourier = Sales::query()
            ->where($scope)
            ->where('courier_name', 'carrybee')
            ->orderByDesc('id')
            ->first();

        if ($byCourier) {
            return $byCourier;
        }

        // order.created can arrive before `courier_name` is persisted as carrybee
        return Sales::query()
            ->where($scope)
            ->orderByRaw("CASE WHEN courier_name = 'carrybee' THEN 0 ELSE 1 END")
            ->orderByDesc('id')
            ->first();
    }

    /**
     * @return array{status: string, message: string, code: int}
     */
    private function dispatchEvent(Sales $sale, string $event, array $payload): array
    {
        return match ($event) {
            'order.delivered' => $this->handleDelivered($sale, $payload),
            'order.partial-delivery' => $this->handlePartialDelivery($sale, $payload),
            'order.returned' => $this->handleReturnedOrCancelled($sale, $payload),
            'order.returned-to-merchant' => $this->handleReturnedOrCancelled($sale, $payload),
            'order.pickup-cancelled' => $this->handleReturnedOrCancelled($sale, $payload),
            'order.delivery-failed' => $this->handleDeliveryFailed($sale, $payload),
            'order.in-transit' => $this->handleInProgress($sale, $event),
            'order.assigned-for-delivery' => $this->handleInProgress($sale, $event),
            'order.at-the-sorting-hub' => $this->handleInProgress($sale, $event),
            'order.on-the-way-to-central-warehouse' => $this->handleInProgress($sale, $event),
            'order.at-central-warehouse' => $this->handleInProgress($sale, $event),
            'order.received-at-last-mile-hub' => $this->handleInProgress($sale, $event),
            'order.picked' => $this->handleInProgress($sale, $event),
            'order.pickup-requested' => $this->handleInProgress($sale, $event),
            'order.assigned-for-pickup' => $this->handleInProgress($sale, $event),
            'order.created' => $this->handleCreatedOrUpdated($sale, $event, $payload),
            'order.updated' => $this->handleCreatedOrUpdated($sale, $event, $payload),
            'order.delivery-on-hold' => $this->noopOk($event),
            'order.paid' => $this->noopOk($event),
            default => $this->noopOk($event),
        };
    }

    /**
     * @return array{status: string, message: string, code: int}
     */
    private function handleDelivered(Sales $sale, array $payload): array
    {
        if ($sale->status === 'delivered' && $sale->system_status === 'completed') {
            return $this->successResponse('Already delivered.');
        }

        $codAmount = $this->parseAmount($payload['collected_amount'] ?? '0');
        $fees = $this->resolveCarrybeeFees($payload, $sale);
        $codFee = $fees['cod_fee'];
        $deliveryFee = $fees['delivery_fee'];

        DB::transaction(function () use ($sale, $codAmount, $codFee, $deliveryFee) {
            $sale->refresh();
            $paymentAccount = $this->ensureCarrybeePaymentMethod();

            $sale->status = 'delivered';
            $sale->payment_status = 1;
            if ($deliveryFee !== null) {
                $sale->shipping_cost = $deliveryFee;
            }
            $sale->cod_charge = $codFee ?? (float) $sale->cod_charge;
            if ($sale->cod_charge < 0) {
                $sale->cod_charge = 0;
            }
            $sale->due_amount = 0.00;
            $sale->paid_amount = $codAmount;
            $sale->system_status = 'completed';

            $sale->transactions()->create([
                'amount' => $sale->cod_charge,
                'debit' => 'debit',
                'credit' => null,
                'transaction_type' => 'courier_cod_charge',
                'transaction_date' => now(),
                'note' => 'COURIER COD CHARGE (Carrybee): ' . $sale->invoice_no,
                'payment_method_id' => $paymentAccount->id,
            ]);

            $receivedAmount = (float) $sale->paid_amount - ((float) $sale->cod_charge + (float) $sale->shipping_cost);
            if ($sale->cod_charge > 0 && $receivedAmount > 0) {
                $sale->transactions()->create([
                    'amount' => $receivedAmount,
                    'debit' => 'debit',
                    'credit' => null,
                    'transaction_type' => 'payment_received',
                    'transaction_date' => now(),
                    'note' => 'PAYMENT RECEIVED (Carrybee): ' . $sale->invoice_no,
                    'payment_method_id' => $paymentAccount->id,
                ]);
            }

            $sale->save();
        });

        $this->logActivity($sale, 'order.delivered', $payload);

        return $this->successResponse('Delivered status recorded.');
    }

    /**
     * @return array{status: string, message: string, code: int}
     */
    private function handlePartialDelivery(Sales $sale, array $payload): array
    {
        if ($sale->status === 'partial_delivered' && $sale->system_status === 'partial_delivered') {
            return $this->successResponse('Already partial delivery.');
        }

        $codAmount = $this->parseAmount($payload['collected_amount'] ?? '0');
        $fees = $this->resolveCarrybeeFees($payload, $sale);
        $codFee = $fees['cod_fee'];
        $deliveryFee = $fees['delivery_fee'];

        DB::transaction(function () use ($sale, $codAmount, $codFee, $deliveryFee) {
            $sale->refresh();
            $paymentAccount = $this->ensureCarrybeePaymentMethod();

            $sale->status = 'partial_delivered';
            $sale->system_status = 'partial_delivered';
            $sale->payment_status = 3;
            if ($deliveryFee !== null) {
                $sale->shipping_cost = $deliveryFee;
            }
            $sale->cod_charge = $codFee ?? (float) $sale->cod_charge;
            if ($sale->cod_charge < 0) {
                $sale->cod_charge = 0;
            }
            $sale->due_amount = 0.00;
            $sale->paid_amount = $codAmount;

            $sale->transactions()->create([
                'amount' => $sale->cod_charge,
                'debit' => 'debit',
                'credit' => null,
                'transaction_type' => 'courier_cod_charge',
                'transaction_date' => now(),
                'note' => 'COURIER COD CHARGE (Carrybee partial): ' . $sale->invoice_no,
                'payment_method_id' => $paymentAccount->id,
            ]);

            $receivedAmount = (float) $sale->paid_amount - ((float) $sale->cod_charge + (float) $sale->shipping_cost);
            if ($sale->cod_charge > 0 && $receivedAmount > 0) {
                $sale->transactions()->create([
                    'amount' => $receivedAmount,
                    'debit' => null,
                    'credit' => 'credit',
                    'transaction_type' => 'payment_received',
                    'transaction_date' => now(),
                    'note' => 'PAYMENT RECEIVED (Carrybee partial): ' . $sale->invoice_no,
                    'payment_method_id' => $paymentAccount->id,
                ]);
            }

            $sale->save();
            $this->stockService->adjustStockForCancelledSale($sale);
        });

        $this->logActivity($sale, 'order.partial-delivery', $payload);

        return $this->successResponse('Partial delivery recorded.');
    }

    /**
     * @return array{status: string, message: string, code: int}
     */
    private function handleReturnedOrCancelled(Sales $sale, array $payload): array
    {
        $sale->status = 'cancelled';
        $sale->system_status = 'cancelled';
        $sale->cod_charge = 0.00;
        $sale->due_amount = 0.00;
        $sale->paid_amount = 0.00;
        $sale->return_status = CommonConstant::PENDING;
        $sale->save();

        $this->logActivity($sale, (string) ($payload['event'] ?? 'returned'), $payload);

        return $this->successResponse('Return/cancel status recorded.');
    }

    /**
     * @return array{status: string, message: string, code: int}
     */
    private function handleDeliveryFailed(Sales $sale, array $payload): array
    {
        $sale->status = 'unknown';
        $sale->save();

        $this->logActivity($sale, 'order.delivery-failed', $payload);

        return $this->successResponse('Delivery failed noted.');
    }

    /**
     * @return array{status: string, message: string, code: int}
     */
    private function handleInProgress(Sales $sale, string $event): array
    {
        if (! in_array($sale->status, ['delivered', 'cancelled', 'partial_delivered'], true)) {
            $sale->status = 'in_review';
            $sale->system_status = 'pending';
            $sale->save();
        }

        $this->logActivity($sale, $event, []);

        return $this->successResponse('Status updated.');
    }

    /**
     * @return array{status: string, message: string, code: int}
     */
    private function handleCreatedOrUpdated(Sales $sale, string $event, array $payload): array
    {
        $fees = $this->resolveCarrybeeFees($payload, $sale);
        $deliveryFee = $fees['delivery_fee'];
        $codFee = $fees['cod_fee'];

        if ($deliveryFee !== null) {
            $sale->shipping_cost = $deliveryFee;
        }
        if ($codFee !== null) {
            $sale->cod_charge = $codFee;
        }
        if ($sale->isDirty(['shipping_cost', 'cod_charge'])) {
            $sale->save();
        }

        $this->logActivity($sale, $event, $payload);

        $effectiveDeliveryFee = (float) $sale->shipping_cost;
        $effectiveCodFee = (float) $sale->cod_charge;
        $courierCost = $effectiveDeliveryFee + $effectiveCodFee;

        return $this->successResponse(sprintf(
            'Courier fees updated. Delivery: %.2f, COD: %.2f, Courier total: %.2f.',
            $effectiveDeliveryFee,
            $effectiveCodFee,
            $courierCost
        ));
    }

    /**
     * Carrybee may send fees at root or under nested objects (e.g. data / order).
     *
     * @return array<string, mixed>
     */
    private function flattenCarrybeePayload(array $payload): array
    {
        $flat = $payload;
        foreach (['data', 'order', 'payload', 'body'] as $key) {
            if (isset($payload[$key]) && is_array($payload[$key])) {
                $flat = array_merge($flat, $payload[$key]);
            }
        }

        return $flat;
    }

    /**
     * Parse a fee only when the key is present with a non-empty value.
     * Prevents null/"" from becoming 0 and wiping DB columns.
     */
    private function optionalFee(array $payload, string $key): ?float
    {
        if (! array_key_exists($key, $payload)) {
            return null;
        }

        $raw = $payload[$key];
        if ($raw === null) {
            return null;
        }
        if (is_string($raw) && trim($raw) === '') {
            return null;
        }

        return $this->parseAmount($raw);
    }

    /**
     * Resolve carrybee fee fields from multiple payload shapes.
     *
     * @return array{delivery_fee: ?float, cod_fee: ?float}
     */
    private function resolveCarrybeeFees(array $payload, Sales $sale): array
    {
        $p = $this->flattenCarrybeePayload($payload);

        $deliveryFee = $this->optionalFee($p, 'delivery_fee');
        $codFee = $this->optionalFee($p, 'cod_fee');

        // Some Carrybee payloads provide only a total fee.
        $totalFee = null;
        foreach (['total_fee', 'courier_fee', 'total_delivery_fee'] as $key) {
            $parsed = $this->optionalFee($p, $key);
            if ($parsed !== null) {
                $totalFee = $parsed;
                break;
            }
        }

        // Derive missing side from total fee whenever possible.
        if ($totalFee !== null) {
            if ($deliveryFee === null && $codFee !== null) {
                $deliveryFee = max(0, $totalFee - $codFee);
            } elseif ($codFee === null && $deliveryFee !== null) {
                $codFee = max(0, $totalFee - $deliveryFee);
            } elseif ($deliveryFee === null && $codFee === null) {
                // Keep COD as previous value if total fee alone arrives, then derive delivery.
                $codFee = (float) $sale->cod_charge;
                $deliveryFee = max(0, $totalFee - $codFee);
            }
        }

        return [
            'delivery_fee' => $deliveryFee,
            'cod_fee' => $codFee,
        ];
    }

    /**
     * @return array{status: string, message: string, code: int}
     */
    private function noopOk(string $event): array
    {
        return $this->successResponse('Event acknowledged: ' . $event);
    }

    private function parseAmount(mixed $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }
        if (is_string($value)) {
            return (float) preg_replace('/[^\d.-]/', '', $value);
        }

        return 0.0;
    }

    private function ensureCarrybeePaymentMethod(): PaymentMethod
    {
        $found = PaymentMethod::where('type', 'CARRYBEE')->first();
        if ($found) {
            return $found;
        }

        return PaymentMethod::create([
            'type' => 'CARRYBEE',
            'name' => 'CARRYBEE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function logActivity(Sales $sale, string $event, array $payload): void
    {
        activity()
            ->performedOn($sale)
            ->causedByAnonymous()
            ->event('carrybee_webhook')
            ->withProperties([
                'carrybee_event' => $event,
                'payload' => $payload,
            ])
            ->log('Carrybee webhook: ' . $event . ' — Invoice ' . $sale->invoice_no);
    }

    /**
     * @return array{status: string, message: string, code: int}
     */
    private function successResponse(string $message, int $code = 202): array
    {
        return ['status' => 'success', 'message' => $message, 'code' => $code];
    }

    /**
     * @return array{status: string, message: string, code: int}
     */
    private function errorResponse(string $message, int $code): array
    {
        return ['status' => 'error', 'message' => $message, 'code' => $code];
    }
}
