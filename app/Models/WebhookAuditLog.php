<?php

namespace App\Models;

use App\Models\Concerns\HasUuidV7;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookAuditLog extends Model
{
    use HasFactory, HasUuidV7;

    protected $fillable = [
        'institusi_id',
        'kode_institusi_payload',
        'event_type',
        'ip_address',
        'headers',
        'payload',
        'signature_header',
        'is_signature_valid',
        'response_code',
        'processing_notes',
    ];

    protected function casts(): array
    {
        return [
            'headers' => 'array',
            'payload' => 'array',
            'is_signature_valid' => 'boolean',
            'response_code' => 'integer',
        ];
    }

    /**
     * Relationship to the resolved institution (nullable if unknown institution).
     */
    public function institusi(): BelongsTo
    {
        return $this->belongsTo(Institusi::class, 'institusi_id');
    }
}

