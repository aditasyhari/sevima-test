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
        Schema::create('webhook_audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('institusi_id')->nullable()->constrained('institusis')->nullOnDelete();
            $table->string('kode_institusi_payload', 50)->nullable();
            $table->string('event_type', 100)->nullable();
            $table->string('ip_address', 50)->nullable();
            $table->json('headers');
            $table->json('payload');
            $table->string('signature_header', 255)->nullable();
            $table->boolean('is_signature_valid')->default(false);
            $table->unsignedSmallInteger('response_code');
            $table->text('processing_notes')->nullable();
            $table->timestamps();

            $table->index(['institusi_id', 'created_at']);
            $table->index(['event_type', 'is_signature_valid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_audit_logs');
    }
};
