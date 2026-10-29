<?php

namespace App\Actions\Webhook;

class VerifyHmacSignatureAction
{
    /**
     * Verify HMAC-SHA256 signature for an institution.
     *
     * @param  string  $rawPayload  The exact raw request body.
     * @param  string  $signature  The provided signature from X-Signature header.
     * @param  string  $secret  The institution's private HMAC secret.
     * @return bool True if signature matches, false otherwise.
     */
    public function execute(string $rawPayload, string $signature, string $secret): bool
    {
        if (empty($signature) || empty($secret)) {
            return false;
        }

        // Clean signature if prefixed with 'sha256='
        if (str_starts_with($signature, 'sha256=')) {
            $signature = substr($signature, 7);
        }

        $computedSignature = hash_hmac('sha256', $rawPayload, $secret);

        // Timing-safe comparison to prevent timing attacks
        return hash_equals($computedSignature, $signature);
    }

    /**
     * Generate HMAC signature (used by Mock Gateway simulator and tests).
     */
    public function generateSignature(string $rawPayload, string $secret): string
    {
        return hash_hmac('sha256', $rawPayload, $secret);
    }
}
