<?php

namespace Database\Factories;

use App\Models\Institusi;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Institusi>
 */
class InstitusiFactory extends Factory
{
    protected $model = Institusi::class;

    public function definition(): array
    {
        $code = 'UNIV-' . strtoupper(Str::random(4));

        return [
            'kode' => $code,
            'nama' => 'Universitas ' . fake()->company(),
            'webhook_secret' => 'secret_' . Str::random(24),
        ];
    }
}
