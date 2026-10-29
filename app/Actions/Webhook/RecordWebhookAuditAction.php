<?php

namespace App\Actions\Webhook;

use App\Models\WebhookAuditLog;

class RecordWebhookAuditAction
{
    /**
     * Record all incoming webhook payloads into the audit log for accountability.
     */
    public function execute(
        ?string $institusiId,
        ?string $kodeInstitusiPayload,
        ?string $eventType,
        ?string $ipAddress,
        array $headers,
        array $payload,
        ?string $signatureHeader,
        bool $isSignatureValid,
        int $responseCode,
        ?string $processingNotes = null
    ): WebhookAuditLog {
        return WebhookAuditLog::create([
            'institusi_id' => $institusiId,
            'kode_institusi_payload' => $kodeInstitusiPayload,
            'event_type' => $eventType,
            'ip_address' => $ipAddress,
            'headers' => $headers,
            'payload' => $payload,
            'signature_header' => $signatureHeader,
            'is_signature_valid' => $isSignatureValid,
            'response_code' => $responseCode,
            'processing_notes' => $processingNotes,
        ]);
    }
}

