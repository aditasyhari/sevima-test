<?php

use App\Http\Controllers\Api\StudentBillApiController;
use App\Http\Controllers\Webhook\PaymentWebhookController;
use App\Models\Institusi;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Payment Gateway Webhook Endpoint
Route::post('/v1/webhook/payment', [PaymentWebhookController::class, 'handle'])->name('api.webhook.payment');
Route::post('/webhook/payment', [PaymentWebhookController::class, 'handle']);

// Student Billing Status & Transaction History Endpoint
Route::get('/v1/students/{studentId}/bills', [StudentBillApiController::class, 'show'])->name('api.students.bills');

// Helper endpoint for listing institutions (useful for testing & simulator)
Route::get('/v1/institutions', function () {
    return response()->json([
        'status' => 'success',
        'data' => Institusi::select('id', 'kode', 'nama')->get(),
    ]);
});
