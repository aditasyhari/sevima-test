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
        Schema::create('riwayat_transaksis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tagihan_id')->constrained('tagihans')->cascadeOnDelete();
            $table->foreignUuid('institusi_id')->constrained('institusis')->cascadeOnDelete();
            $table->string('event_type', 50); // 'payment.success', 'payment.expired'
            $table->decimal('nominal_bayar', 15, 2);
            $table->string('referensi_gateway', 255);
            $table->dateTime('waktu_transaksi');
            $table->json('payload_snapshot');
            $table->timestamps();

            $table->index(['tagihan_id', 'waktu_transaksi']);
            $table->index(['institusi_id', 'event_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_transaksis');
    }
};

