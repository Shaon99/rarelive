<?php

namespace App\Http\Middleware;

use App\Models\GeneralSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CarrybeeWebhookMiddleware
{
    /**
     * Carrybee may send:
     * - X-CB-Webhook-Integration-Header: <uuid> (dashboard “integration” value — used by their URL tester)
     * - X-Carrybee-Webhook-Signature: <shared secret> (older / alternate docs)
     *
     * At least one of integration ID or secret must be stored; the request must match one of them.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $row = GeneralSetting::query()->select(
            'carrybee_webhook_secret',
            'carrybee_webhook_integration_id'
        )->first();

        $storedSecret = is_string($row?->carrybee_webhook_secret) ? trim($row->carrybee_webhook_secret) : '';
        $storedIntegration = is_string($row?->carrybee_webhook_integration_id)
            ? trim($row->carrybee_webhook_integration_id)
            : '';

        $withCbHeader = static function (Response $response) use ($storedIntegration): Response {
            if ($storedIntegration !== '') {
                $response->headers->set('X-CB-Webhook-Integration-Header', $storedIntegration);
            }

            return $response;
        };

        if ($storedSecret === '' && $storedIntegration === '') {
            return $withCbHeader(response()->json(
                ['error' => 'Carrybee webhook is not configured.'],
                Response::HTTP_SERVICE_UNAVAILABLE
            ));
        }

        $cbHeader = trim((string) $request->header('X-CB-Webhook-Integration-Header', ''));
        $legacySig = trim((string) $request->header('X-Carrybee-Webhook-Signature', ''));

        $integrationOk = $storedIntegration !== '' && $cbHeader !== '' && hash_equals($storedIntegration, $cbHeader);
        $signatureOk = $storedSecret !== '' && $legacySig !== '' && hash_equals($storedSecret, $legacySig);

        if (! $integrationOk && ! $signatureOk) {
            return $withCbHeader(response()->json(
                ['error' => 'Invalid webhook credentials'],
                Response::HTTP_UNAUTHORIZED
            ));
        }

        return $withCbHeader($next($request));
    }
}
