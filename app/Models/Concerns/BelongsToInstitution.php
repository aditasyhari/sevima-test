<?php

namespace App\Models\Concerns;

use App\Models\Institusi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToInstitution
{
    /**
     * Boot the trait to enforce multi-tenant isolation automatically.
     */
    protected static function bootBelongsToInstitution(): void
    {
        static::addGlobalScope('institution', function (Builder $builder) {
            if (app()->bound('request') && Auth::check() && Auth::user()?->institusi_id) {
                $builder->where($builder->getModel()->getTable().'.institusi_id', Auth::user()->institusi_id);
            }
        });

        static::creating(function ($model) {
            if (app()->bound('request') && Auth::check() && Auth::user()?->institusi_id && empty($model->institusi_id)) {
                $model->institusi_id = Auth::user()->institusi_id;
            }
        });
    }

    /**
     * Relationship to the institution (tenant).
     */
    public function institusi(): BelongsTo
    {
        return $this->belongsTo(Institusi::class, 'institusi_id');
    }
}
