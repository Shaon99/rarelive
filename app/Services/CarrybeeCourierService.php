<?php

namespace App\Services;

use App\Models\Sales;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CarrybeeCourierService
{
    /**
     * Book each sale with Carrybee (single-order API). Updates DB only on per-order success.
     *
     * @param  array<string, string>  $account
     * @return array{success: bool, status: int, message: string, errors: ?array<int, string>}
     */
    public function sendOrders(array $account, Collection $sales): array
    {
        $client = CarrybeeIntegrationService::httpForAccount($account);

        $configuredStoreId = trim((string) ($account['store_id'] ?? ''));

        $storesResponse = $client->get('/api/v2/stores');
        $stores = $storesResponse->successful() ? ($storesResponse->json('data.stores') ?? []) : [];

        if ($configuredStoreId !== '') {
            if ($stores !== [] && ! $this->storeIdExistsInList($stores, $configuredStoreId)) {
                return [
                    'success' => false,
                    'status' => 422,
                    'message' => 'The Carrybee store ID saved for this account was not found in GET /api/v2/stores. Check the ID in Carrybee.',
                    'errors' => null,
                ];
            }
            $storeId = $configuredStoreId;
        } else {
            if (! $storesResponse->successful()) {
                return [
                    'success' => false,
                    'status' => $storesResponse->status() ?: 502,
                    'message' => $this->extractMessage($storesResponse->json()) ?? 'Could not load Carrybee stores.',
                    'errors' => null,
                ];
            }

            $storeId = $this->pickPickupStoreId($stores);

            if ($storeId === null) {
                return [
                    'success' => false,
                    'status' => 422,
                    'message' => 'No Carrybee pickup store found. Create a store in Carrybee, set a default pickup store, or enter a Store ID on this account in Integration settings.',
                    'errors' => null,
                ];
            }
        }

        $errors = [];
        $successCount = 0;

        foreach ($sales as $sale) {
            $sale->loadMissing(['customer', 'salesProduct.product', 'salesProduct.combo']);
            $row = $this->sendSingleSale($client, $storeId, $sale);
            if ($row['ok']) {
                $successCount++;
            } else {
                $errors[] = $row['error'];
            }
        }

        if ($successCount === 0) {
            return [
                'success' => false,
                'status' => 422,
                'message' => 'Carrybee did not create any orders.',
                'errors' => $errors,
            ];
        }

        $message = $successCount === 1
            ? '1 order booked with Carrybee.'
            : "{$successCount} orders booked with Carrybee.";
        if ($errors !== []) {
            $message .= ' Some orders failed; see error list.';
        }

        return [
            'success' => true,
            'status' => 200,
            'message' => $message,
            'errors' => $errors !== [] ? $errors : null,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $stores
     */
    private function storeIdExistsInList(array $stores, string $storeId): bool
    {
        foreach ($stores as $store) {
            if (isset($store['id']) && (string) $store['id'] === $storeId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<array<string, mixed>>  $stores
     */
    private function pickPickupStoreId(array $stores): ?string
    {
        foreach ($stores as $store) {
            if (($store['is_default_pickup_store'] ?? false)
                && ($store['is_active'] ?? false)
                && ($store['is_approved'] ?? false)
                && ! empty($store['id'])
            ) {
                return (string) $store['id'];
            }
        }
        foreach ($stores as $store) {
            if (($store['is_active'] ?? false) && ($store['is_approved'] ?? false) && ! empty($store['id'])) {
                return (string) $store['id'];
            }
        }
        foreach ($stores as $store) {
            if (! empty($store['id'])) {
                return (string) $store['id'];
            }
        }

        return null;
    }

    /**
     * @param  \Illuminate\Http\Client\PendingRequest  $client
     * @return array{ok: bool, error: string}
     */
    private function sendSingleSale($client, string $storeId, Sales $sale): array
    {
        $customer = $sale->customer;
        if (! $customer) {
            return ['ok' => false, 'error' => "Invoice {$sale->invoice_no}: missing customer."];
        }

        $resolved = $this->resolveCityZoneArea($client, $sale, $customer);
        if ($resolved === null) {
            return ['ok' => false, 'error' => "Invoice {$sale->invoice_no}: Carrybee could not resolve city/zone from the delivery address. Try a fuller address (area, city)."];
        }

        $recipientPhone = $this->normalizeBdPhone($customer->phone);
        if ($recipientPhone === null) {
            return ['ok' => false, 'error' => "Invoice {$sale->invoice_no}: invalid customer phone."];
        }

        $recipientName = $this->normalizeRecipientName($customer->name);
        $recipientAddress = $this->normalizeRecipientAddress($sale->delivery_address ?? '');

        $itemQty = max(1, (int) round((float) $sale->salesProduct->sum('quantity') ?: 1.0));
        $itemWeight = 500;

        $merchantOrderId = mb_substr((string) $sale->invoice_no, 0, 49);

        $collectable = (int) round(max(0.0, min(100000.0, (float) $sale->due_amount)));

        $payload = [
            'store_id' => $storeId,
            'merchant_order_id' => $merchantOrderId,
            'delivery_type' => 1,
            'product_type' => 1,
            'recipient_phone' => $recipientPhone,
            'recipient_name' => $recipientName,
            'recipient_address' => $recipientAddress,
            'city_id' => $resolved['city_id'],
            'zone_id' => $resolved['zone_id'],
            'item_weight' => $itemWeight,
            'item_quantity' => $itemQty,
            'is_closed' => false,
        ];

        if (($resolved['area_id'] ?? 0) > 0) {
            $payload['area_id'] = $resolved['area_id'];
        }

        if ($collectable > 0) {
            $payload['collectable_amount'] = $collectable;
        }

        if (! empty($sale->note)) {
            $payload['special_instruction'] = mb_substr(strip_tags((string) $sale->note), 0, 255);
        }

        $desc = $this->buildProductDescription($sale);
        if ($desc !== '') {
            $payload['product_description'] = mb_substr($desc, 0, 255);
        }

        $orderRes = $client->post('/api/v2/orders', $payload);
        $orderJson = $orderRes->json();

        if ($orderRes->status() !== 201 || ($orderJson['error'] ?? true)) {
            $msg = $this->extractMessage($orderJson) ?? 'Order creation failed.';
            $causes = $this->formatCauses($orderJson['causes'] ?? null);
            if ($causes !== '') {
                $msg .= ' ' . $causes;
            }
            Log::warning('Carrybee order failed', [
                'invoice' => $sale->invoice_no,
                'status' => $orderRes->status(),
                'body' => $orderJson,
            ]);

            return ['ok' => false, 'error' => "Invoice {$sale->invoice_no}: {$msg}"];
        }

        $consignmentId = $orderJson['data']['order']['consignment_id'] ?? null;
        if (! is_string($consignmentId) || $consignmentId === '') {
            return ['ok' => false, 'error' => "Invoice {$sale->invoice_no}: Carrybee returned no consignment ID."];
        }

        $sale->update([
            'consignment_id' => $consignmentId,
            'courier_name' => 'carrybee',
            'status' => 'in_review',
            'system_status' => 'pending',
        ]);

        return ['ok' => true, 'error' => ''];
    }

    /**
     * @return array{city_id: int, zone_id: int, area_id: ?int}|null
     */
    private function resolveCityZoneArea($client, Sales $sale, $customer): ?array
    {
        $query = $this->buildAddressQuery($sale, $customer);
        $addrRes = $client->post('/api/v2/address-details', ['query' => $query]);
        $addrJson = $addrRes->json();

        if ($addrRes->successful() && ! ($addrJson['error'] ?? true)) {
            $cityId = (int) ($addrJson['data']['city_id'] ?? 0);
            $zoneId = (int) ($addrJson['data']['zone_id'] ?? 0);
            if ($cityId > 0 && $zoneId > 0) {
                return ['city_id' => $cityId, 'zone_id' => $zoneId, 'area_id' => null];
            }
        }

        $search = mb_substr(preg_replace('/\s+/u', ' ', trim((string) ($sale->delivery_address ?? ''))), 0, 80);
        if (mb_strlen($search) < 3) {
            $search = mb_substr(trim((string) $customer->name) . ' Dhaka', 0, 80);
        }

        $sug = $client->get('/api/v2/area-suggestion', ['search' => $search]);
        if (! $sug->successful()) {
            return null;
        }
        $items = $sug->json('data.items') ?? [];
        if (! isset($items[0]) || ! is_array($items[0])) {
            return null;
        }
        $item = $items[0];
        $cityId = (int) ($item['city_id'] ?? 0);
        $zoneId = (int) ($item['zone_id'] ?? 0);
        $areaId = isset($item['area_id']) ? (int) $item['area_id'] : 0;

        if ($cityId < 1 || $zoneId < 1) {
            return null;
        }

        return [
            'city_id' => $cityId,
            'zone_id' => $zoneId,
            'area_id' => $areaId > 0 ? $areaId : null,
        ];
    }

    private function buildAddressQuery(Sales $sale, $customer): string
    {
        $parts = array_filter([
            trim((string) ($sale->delivery_address ?? '')),
            trim((string) $customer->name),
        ]);
        $query = implode(', ', $parts);
        $query = preg_replace('/\s+/u', ' ', $query) ?? $query;
        if (mb_strlen($query) < 10) {
            $query .= ', Bangladesh';
        }

        return mb_substr($query, 0, 500);
    }

    private function normalizeRecipientName(string $name): string
    {
        $name = trim(preg_replace('/\s+/u', ' ', $name) ?? '');
        if (mb_strlen($name) < 2) {
            return 'Customer';
        }

        return mb_substr($name, 0, 99);
    }

    private function normalizeRecipientAddress(string $address): string
    {
        $address = trim(preg_replace('/\s+/u', ' ', $address) ?? '');
        if ($address === '') {
            $address = 'Delivery address to be confirmed; please contact the customer.';
        }
        while (mb_strlen($address) < 10) {
            $address .= ' —';
        }

        return mb_substr($address, 0, 200);
    }

    private function normalizeBdPhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if ($digits === '') {
            return null;
        }
        if (str_starts_with($digits, '880') && strlen($digits) >= 12) {
            return $digits;
        }
        if (str_starts_with($digits, '01') && strlen($digits) === 11) {
            return '880' . substr($digits, 1);
        }
        if (str_starts_with($digits, '1') && strlen($digits) === 10) {
            return '880' . $digits;
        }

        return $digits;
    }

    private function buildProductDescription(Sales $sale): string
    {
        $lines = [];
        foreach ($sale->salesProduct as $line) {
            $qty = (float) ($line->quantity ?? 0);
            $title = $line->product?->name ?? $line->combo?->name ?? 'Item';
            $lines[] = $title . ' x' . $qty;
        }

        return implode('; ', array_slice($lines, 0, 8));
    }

    /**
     * @param  array<string, mixed>|null  $json
     */
    private function extractMessage(?array $json): ?string
    {
        if ($json === null) {
            return null;
        }
        $m = $json['message'] ?? null;

        return is_string($m) && $m !== '' ? $m : null;
    }

    /**
     * @param  mixed  $causes
     */
    private function formatCauses($causes): string
    {
        if (! is_array($causes) || $causes === []) {
            return '';
        }

        $parts = [];
        foreach ($causes as $field => $list) {
            if (! is_array($list)) {
                continue;
            }
            foreach ($list as $entry) {
                if (is_array($entry)) {
                    $type = $entry['type'] ?? 'error';
                    $parts[] = "{$field}: {$type}";
                }
            }
        }

        return implode('; ', array_slice($parts, 0, 6));
    }
}
