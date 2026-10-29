<?php

namespace App\Models;

use App\Models\Concerns\HasUuidV7;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Institusi extends Model
{
    use HasFactory, HasUuidV7;

    protected $fillable = [
        'kode',
        'nama',
        'webhook_secret',
    ];

    protected $hidden = [
        'webhook_secret',
    ];

    /**
     * Relationship to Users in this institution.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'institusi_id');
    }

    /**
     * Relationship to UKT bills in this institution.
     */
    public function tagihans(): HasMany
    {
        return $this->hasMany(Tagihan::class, 'institusi_id');
    }

    /**
     * Relationship to audit logs.
     */
    public function webhookAuditLogs(): HasMany
    {
        return $this->hasMany(WebhookAuditLog::class, 'institusi_id');
    }
}
