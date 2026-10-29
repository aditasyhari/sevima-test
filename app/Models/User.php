<?php

namespace App\Models;

use App\Models\Concerns\HasUuidV7;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, HasUuidV7, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'institusi_id',
        'role',
        'name',
        'email',
        'password',
        'nim',
        'prodi',
        'semester_aktif',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'semester_aktif' => 'integer',
        ];
    }

    /**
     * Relationship to the user's institution.
     */
    public function institusi(): BelongsTo
    {
        return $this->belongsTo(Institusi::class, 'institusi_id');
    }

    /**
     * Check if user is an Admin Keuangan.
     */
    public function isAdminKeuangan(): bool
    {
        return $this->role === 'admin_keuangan';
    }

    /**
     * Check if user is a Mahasiswa.
     */
    public function isMahasiswa(): bool
    {
        return $this->role === 'mahasiswa';
    }

    /**
     * Tagihan UKT milik mahasiswa ini.
     */
    public function tagihans(): HasMany
    {
        return $this->hasMany(Tagihan::class, 'mahasiswa_id');
    }
}
