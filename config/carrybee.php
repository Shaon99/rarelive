<?php

/**
 * Carrybee courier — fallback when no accounts are stored in the database
 * (`general_settings.carrybee_accounts`). If that JSON column has at least one
 * account, the app uses DB only. Otherwise these .env suffixes are loaded.
 *
 * Per account, set all of:
 *   CARRYBEE_API_URL_{SUFFIX}
 *   CARRYBEE_CLIENT_ID_{SUFFIX}
 *   CARRYBEE_CLIENT_SECRET_{SUFFIX}
 *   CARRYBEE_CLIENT_CONTEXT_{SUFFIX}
 *
 * Optional display name:
 *   CARRYBEE_LABEL_{SUFFIX}
 *
 * Optional pickup store id (from GET /api/v2/stores):
 *   CARRYBEE_STORE_ID_{SUFFIX}
 *
 * Default suffixes scanned: FIRST, SECOND, THIRD, FOURTH, FIFTH.
 * Override list (comma-separated): CARRYBEE_ACCOUNT_SUFFIXES=FIRST,SECOND,WAREHOUSE_B
 */
$suffixEnv = env('CARRYBEE_ACCOUNT_SUFFIXES', 'FIRST,SECOND,THIRD,FOURTH,FIFTH');
$suffixes = array_values(array_filter(array_map(
    static fn(string $s): string => strtoupper(trim($s)),
    explode(',', (string) $suffixEnv)
)));

$accounts = [];
foreach ($suffixes as $suffix) {
    $apiUrl = env("CARRYBEE_API_URL_{$suffix}");
    $clientId = env("CARRYBEE_CLIENT_ID_{$suffix}");
    $clientSecret = env("CARRYBEE_CLIENT_SECRET_{$suffix}");
    $clientContext = env("CARRYBEE_CLIENT_CONTEXT_{$suffix}");

    if (! $apiUrl || ! $clientId || ! $clientSecret || ! $clientContext) {
        continue;
    }

    $displayName = env("CARRYBEE_LABEL_{$suffix}") ?: "Carrybee ({$suffix})";
    $storeId = trim((string) env("CARRYBEE_STORE_ID_{$suffix}", ''));

    $accounts[] = [
        'key' => strtolower($suffix),
        'suffix' => $suffix,
        'label' => $displayName,
        'account_name' => $displayName,
        'api_url' => rtrim((string) $apiUrl, '/'),
        'client_id' => (string) $clientId,
        'client_secret' => (string) $clientSecret,
        'client_context' => (string) $clientContext,
        'store_id' => $storeId,
    ];
}

return [
    'accounts' => $accounts,
];
