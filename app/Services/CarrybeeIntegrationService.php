<?php

namespace App\Services;

use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Http;

class CarrybeeIntegrationService
{
    /**
     * Raw configured accounts (secrets included) — use only server-side.
     * Database (`carrybee_accounts` on general_settings) wins when non-empty; otherwise .env via config.
     *
     * @return array<int, array{key: string, suffix: string, label: string, account_name: string, api_url: string, client_id: string, client_secret: string, client_context: string, store_id: string}>
     */
    public static function accounts(): array
    {
        $stored = GeneralSetting::query()->value('carrybee_accounts');

        if (is_array($stored) && count($stored) > 0) {
            return array_values(array_filter(array_map(
                static function (array $row): ?array {
                    $id = trim((string) ($row['id'] ?? ''));
                    $apiUrl = trim((string) ($row['api_url'] ?? ''));
                    if ($id === '' || $apiUrl === '') {
                        return null;
                    }

                    $clientId = trim((string) ($row['client_id'] ?? ''));
                    $clientSecret = trim((string) ($row['client_secret'] ?? ''));
                    $clientContext = trim((string) ($row['client_context'] ?? ''));
                    if ($clientId === '' || $clientSecret === '' || $clientContext === '') {
                        return null;
                    }

                    $label = trim((string) ($row['account_name'] ?? $row['label'] ?? '')) ?: 'Carrybee account';
                    $storeId = trim((string) ($row['store_id'] ?? ''));

                    return [
                        'key' => $id,
                        'suffix' => strtoupper(substr(preg_replace('/\s+/', '_', $label), 0, 24)) ?: 'ACCOUNT',
                        'label' => $label,
                        'account_name' => $label,
                        'api_url' => rtrim($apiUrl, '/'),
                        'client_id' => $clientId,
                        'client_secret' => $clientSecret,
                        'client_context' => $clientContext,
                        'store_id' => $storeId,
                    ];
                },
                $stored
            )));
        }

        return config('carrybee.accounts', []);
    }

    /**
     * Safe fields for the integration UI (masked secrets).
     *
     * @return array<int, array<string, string>>
     */
    public static function accountsForDisplay(): array
    {
        return array_map(static function (array $account): array {
            return [
                'key' => $account['key'],
                'suffix' => $account['suffix'],
                'label' => $account['label'],
                'account_name' => $account['account_name'] ?? $account['label'],
                'api_url' => $account['api_url'],
                'client_id' => self::maskMiddle($account['client_id']),
                'client_secret' => self::maskMiddle($account['client_secret']),
                'client_context' => self::maskMiddle($account['client_context']),
                'store_id' => $account['store_id'] ?? '',
            ];
        }, self::accounts());
    }

    public static function maskMiddle(string $value): string
    {
        $len = strlen($value);
        if ($len <= 8) {
            return str_repeat('*', $len);
        }

        return substr($value, 0, 4) . str_repeat('*', $len - 8) . substr($value, -4);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function accountByKey(string $key): ?array
    {
        foreach (self::accounts() as $account) {
            if ($account['key'] === $key) {
                return $account;
            }
        }

        return null;
    }

    /**
     * HTTP client for Carrybee API v2 (requires Client-ID / Client-Secret / Client-Context on every request).
     *
     * @param  array<string, string>  $account
     */
    public static function httpForAccount(array $account)
    {
        return Http::baseUrl($account['api_url'])
            ->withHeaders([
                'Client-ID' => $account['client_id'],
                'Client-Secret' => $account['client_secret'],
                'Client-Context' => $account['client_context'],
            ])
            ->timeout(45)
            ->withOptions(['verify' => true])
            ->acceptJson()
            ->asJson();
    }
}
