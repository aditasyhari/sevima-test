<?php

namespace App\Http\Controllers\Webhook;

use App\Actions\Webhook\ProcessPaymentWebhookAction;
use App\Actions\Webhook\RecordWebhookAuditAction;
use App\Actions\Webhook\VerifyHmacSignatureAction;
use App\Http\Controllers\Controller;
use App\Models\Institusi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class PaymentWebhookController extends Controller
{
    public function __construct(
        protected VerifyHmacSignatureAction $verifySignatureAction,
        protected ProcessPaymentWebhookAction $processPaymentAction,
        protected RecordWebhookAuditAction $recordAuditAction
    ) {}

    /**
     * Webhook receiver for Payment Gateway notifications.
     */
    public function handle(Request $request): JsonResponse
    {
        $rawPayload = $request->getContent();
        $signatureHeader = $request->header('X-Signature') ?? $request->header('x-signature');
        $headers = $request->headers->all();
        $ip = $request->ip();

        // 1. Parse JSON payload securely
        $payload = json_decode($rawPayload, true);
        if (! is_array($payload)) {
            $this->recordAuditAction->execute(
                institusiId: null,
                kodeInstitusiPayload: null,
                eventType: null,
                ipAddress: $ip,
                headers: $headers,
                payload: ['raw' => $rawPayload],
                signatureHeader: $signatureHeader,
                isSignatureValid: false,
                responseCode: 400,
                processingNotes: 'Malformed JSON payload'
            );

            return response()->json([
                'status' => 'error',
                'message' => 'Malformed JSON payload.',
            ], 400);
        }

        $institutionCode = $payload['institution_code'] ?? null;
        $event = $payload['event'] ?? null;

        // 2. Resolve institution
        $institusi = $institutionCode
            ? Institusi::where('kode', $institutionCode)->first()
            : null;

        if (! $institusi) {
            $this->recordAuditAction->execute(
                institusiId: null,
                kodeInstitusiPayload: $institutionCode,
                eventType: $event,
                ipAddress: $ip,
                headers: $headers,
                payload: $payload,
                signatureHeader: $signatureHeader,
                isSignatureValid: false,
                responseCode: 404,
                processingNotes: "Institusi dengan kode '{$institutionCode}' tidak ditemukan."
            );

            return response()->json([
                'status' => 'error',
                'message' => "Institusi dengan kode '{$institutionCode}' tidak ditemukan.",
            ], 404);
        }

        // 3. Verify HMAC-SHA256 signature using this specific institution's secret
        $isSignatureValid = $signatureHeader && $this->verifySignatureAction->execute(
            rawPayload: $rawPayload,
            signature: $signatureHeader,
            secret: $institusi->webhook_secret
        );

        if (! $isSignatureValid) {
            $this->recordAuditAction->execute(
                institusiId: $institusi->id,
                kodeInstitusiPayload: $institutionCode,
                eventType: $event,
                ipAddress: $ip,
                headers: $headers,
                payload: $payload,
                signatureHeader: $signatureHeader,
                isSignatureValid: false,
                responseCode: 401,
                processingNotes: 'Signature HMAC tidak valid atau tidak cocok dengan secret institusi.'
            );

            return response()->json([
                'status' => 'error',
                'message' => 'Signature HMAC tidak valid.',
            ], 401);
        }

        // 4. Process payment notification with concurrency lock & idempotency
        try {
            $result = $this->processPaymentAction->execute($institusi, $payload);

            $this->recordAuditAction->execute(
                institusiId: $institusi->id,
                kodeInstitusiPayload: $institutionCode,
                eventType: $event,
                ipAddress: $ip,
                headers: $headers,
                payload: $payload,
                signatureHeader: $signatureHeader,
                isSignatureValid: true,
                responseCode: $result['http_code'],
                processingNotes: $result['message']
            );

            return response()->json([
                'status' => $result['status'],
                'message' => $result['message'],
                'data' => $result['tagihan'] ? [
                    'nomor_tagihan' => $result['tagihan']->nomor_tagihan,
                    'status' => $result['tagihan']->status,
                    'nominal' => (float) $result['tagihan']->nominal,
                ] : null,
            ], $result['http_code']);
        } catch (Throwable $e) {
            $this->recordAuditAction->execute(
                institusiId: $institusi->id,
                kodeInstitusiPayload: $institutionCode,
                eventType: $event,
                ipAddress: $ip,
                headers: $headers,
                payload: $payload,
                signatureHeader: $signatureHeader,
                isSignatureValid: true,
                responseCode: 500,
                processingNotes: 'Internal Error: '.$e->getMessage()
            );

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem internal: '.$e->getMessage(),
            ], 500);
        }
    }
}
