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

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-Signature' => $signature,
            'User-Agent' => 'SEVIMA-Mock-Payment-Gateway/1.0',
        ])
            ->timeout(10)
            ->withBody($jsonPayload, 'application/json')
            ->post($targetUrl);

        return [
            'sent_payload' => $payload,
            'raw_payload' => $jsonPayload,
            'sent_signature' => $signature,
            'status_code' => $response->status(),
            'response_body' => $response->json() ?? $response->body(),
        ];
    }
}
