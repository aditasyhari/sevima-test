<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;

trait HasUuidV7
{
    use HasUuids;

    /**
     * Generate a new UUIDv7 for the model.
     * UUIDv7 is time-ordered, preventing B-tree index fragmentation in PostgreSQL
     * while guaranteeing unpredictable global uniqueness.
     */
    public function newUniqueId(): string
    {
        return (string) Str::uuid7();
    }
}

