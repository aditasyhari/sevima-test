<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tagihans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institusi_id')->constrained('institusis')->cascadeOnDelete();
            $table->foreignUuid('mahasiswa_id')->constrained('users')->cascadeOnDelete();
            $table->string('nomor_tagihan', 100);
            $table->string('semester', 50); // Contoh: "2024/2025 Ganjil"
            $table->decimal('nominal', 15, 2);
            $table->dateTime('tanggal_kedaluwarsa');
            $table->string('status', 50)->default('belum_bayar'); // 'belum_bayar', 'lunas', 'kedaluwarsa'
            $table->string('virtual_account', 50)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            // Composite unique index: Nomor tagihan unik di institusi yang sama
            $table->unique(['institusi_id', 'nomor_tagihan'], 'uq_institusi_nomor_tagihan');

            // Index pencarian performa
            $table->index(['institusi_id', 'status']);
            $table->index(['mahasiswa_id', 'semester']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tagihans');
    }
};
