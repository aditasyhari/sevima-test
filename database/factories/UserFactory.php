<?php

namespace Database\Factories;

use App\Models\Institusi;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'institusi_id' => Institusi::factory(),
            'role' => 'mahasiswa',
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'nim' => fake()->unique()->numerify('2024######'),
            'prodi' => fake()->randomElement(['Teknik Informatika', 'Sistem Informasi', 'Teknik Elektro']),
            'semester_aktif' => fake()->numberBetween(1, 8),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * State for Admin Keuangan.
     */
    public function adminKeuangan(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin_keuangan',
            'nim' => null,
            'prodi' => null,
            'semester_aktif' => null,
        ]);
    }
}
