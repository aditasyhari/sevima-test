<?php

namespace App\Services\PaymentGateway;

use App\Actions\Webhook\VerifyHmacSignatureAction;
use App\Models\Institusi;
use App\Models\Tagihan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class MockPaymentGatewayService
{
    public function __construct(
        protected VerifyHmacSignatureAction $signatureAction
    ) {}

    /**
     * Build mock webhook payload for a bill.
     */
    public function buildPayload(
        Institusi $institusi,
        Tagihan $tagihan,
        string $event = 'payment.success',
        ?float $customAmount = null
    ): array {
        return [
            'event' => $event,
            'institution_code' => $institusi->kode,
            'bill_number' => $tagihan->nomor_tagihan,
            'amount' => $customAmount ?? (float) $tagihan->nominal,
            'transaction_reference' => 'MOCK-'.strtoupper(substr(md5(uniqid()), 0, 10)),
            'payment_time' => Carbon::now()->toIso8601String(),
        ];
    }

    /**
     * Calculate HMAC-SHA256 signature for payload string.
     */
    public function generateSignature(string $rawPayload, string $secret): string
    {
        return $this->signatureAction->generateSignature($rawPayload, $secret);
    }

    /**
     * Dispatch webhook HTTP request to a target URL.
     */
    public function dispatchWebhook(string $targetUrl, array $payload, string $secret): array
    {
        $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $signature = $this->generateSignature($jsonPayload, $secret);

        // Docker loopback translation: If targeting localhost:8000 from within Docker container,
        // port 8000 may not be reachable if Nginx only binds to port 80. Fall back to port 80 if needed.
        $requestUrl = $targetUrl;
        $parsed = parse_url($targetUrl);
        $host = $parsed['host'] ?? '';
        $port = $parsed['port'] ?? null;

        if (in_array($host, ['localhost', '127.0.0.1']) && $port == 8000) {
            $fp = @fsockopen('127.0.0.1', 8000, $errno, $errstr, 0.1);
            if (! $fp) {
                $path = $parsed['path'] ?? '';
                $query = isset($parsed['query']) ? '?'.$parsed['query'] : '';
                $requestUrl = "http://127.0.0.1{$path}{$query}";
            } else {
                fclose($fp);
            }
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'X-Signature' => $signature,
                'User-Agent' => 'SEVIMA-Mock-Payment-Gateway/1.0',
            ])
                ->timeout(10)
                ->withBody($jsonPayload, 'application/json')
                ->post($requestUrl);

            return [
                'sent_payload' => $payload,
                'raw_payload' => $jsonPayload,
                'sent_signature' => $signature,
                'status_code' => $response->status(),
                'response_body' => $response->json() ?? $response->body(),
            ];
        } catch (\Throwable $e) {
            return [
                'sent_payload' => $payload,
                'raw_payload' => $jsonPayload,
                'sent_signature' => $signature,
                'status_code' => 502,
                'response_body' => [
                    'status' => 'error',
                    'message' => 'Gagal menghubungi endpoint webhook target: '.$e->getMessage(),
                ],
            ];
        }
    }
}
