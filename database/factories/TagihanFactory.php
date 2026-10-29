<?php

namespace Database\Factories;

use App\Models\Institusi;
use App\Models\Tagihan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tagihan>
 */
class TagihanFactory extends Factory
{
    protected $model = Tagihan::class;

    public function definition(): array
    {
        return [
            'institusi_id' => Institusi::factory(),
            'mahasiswa_id' => fn (array $attributes) => User::factory()->create([
                'institusi_id' => $attributes['institusi_id'],
                'role' => 'mahasiswa',
            ])->id,
            'nomor_tagihan' => 'TAG-' . fake()->unique()->numerify('#####'),
            'semester' => '2024/2025 Ganjil',
            'nominal' => fake()->randomElement([3500000.00, 4500000.00, 5000000.00]),
            'tanggal_kedaluwarsa' => Carbon::now()->addDays(14),
            'status' => Tagihan::STATUS_BELUM_BAYAR,
            'virtual_account' => '8801' . fake()->numerify('##########'),
            'catatan' => 'Tagihan UKT Reguler',
        ];
    }

    public function lunas(): static
    {
        return $this->state(fn () => [
            'status' => Tagihan::STATUS_LUNAS,
        ]);
    }

    public function kedaluwarsa(): static
    {
        return $this->state(fn () => [
            'status' => Tagihan::STATUS_KEDALUWARSA,
            'tanggal_kedaluwarsa' => Carbon::now()->subDays(2),
        ]);
    }
}
