<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitution;
use App\Models\Concerns\HasUuidV7;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatTransaksi extends Model
{
    use HasFactory, HasUuidV7, BelongsToInstitution;

    protected $fillable = [
        'tagihan_id',
        'institusi_id',
        'event_type',
        'nominal_bayar',
        'referensi_gateway',
        'waktu_transaksi',
        'payload_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'nominal_bayar' => 'decimal:2',
            'waktu_transaksi' => 'datetime',
            'payload_snapshot' => 'array',
        ];
    }

    /**
     * Relationship to the related UKT bill.
     */
    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class, 'tagihan_id');
    }
}
