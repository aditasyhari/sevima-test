<?php

namespace App\Http\Controllers\Simulator;

use App\Http\Controllers\Controller;
use App\Models\Institusi;
use App\Models\Tagihan;
use App\Models\WebhookAuditLog;
use App\Services\PaymentGateway\MockPaymentGatewayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GatewaySimulatorController extends Controller
{
    public function __construct(
        protected MockPaymentGatewayService $gatewayService
    ) {}

    /**
     * Display Mock Payment Gateway Simulator.
     */
    public function index(): Response
    {
        $institutions = Institusi::select('id', 'kode', 'nama', 'webhook_secret')
            ->with(['tagihans' => function ($q) {
                $q->with('mahasiswa:id,name,nim')
                  ->latest('created_at')
                  ->limit(10);
            }])
            ->get();

        $recentAuditLogs = WebhookAuditLog::with('institusi:id,kode,nama')
            ->latest('created_at')
            ->limit(15)
            ->get();

        return Inertia::render('Simulator/Index', [
            'institutions' => $institutions,
            'recentLogs' => $recentAuditLogs,
            'webhookUrl' => url('/api/v1/webhook/payment'),
        ]);
    }

    /**
     * Trigger a mock webhook call with real HMAC calculation.
     */
    public function trigger(Request $request): JsonResponse
    {
        $request->validate([
            'institusi_id' => 'required|exists:institusis,id',
            'tagihan_id' => 'required|exists:tagihans,id',
            'event' => 'required|in:payment.success,payment.expired',
            'tamper_signature' => 'boolean',
        ]);

        $institusi = Institusi::findOrFail($request->institusi_id);
        $tagihan = Tagihan::withoutGlobalScope('institution')->findOrFail($request->tagihan_id);

        $payload = $this->gatewayService->buildPayload(
            $institusi,
            $tagihan,
            $request->event
        );

        $secret = $request->tamper_signature
            ? 'tampered_invalid_secret_key'
            : $institusi->webhook_secret;

        $targetUrl = url('/api/v1/webhook/payment');
        $dispatchResult = $this->gatewayService->dispatchWebhook($targetUrl, $payload, $secret);

        return response()->json([
            'status' => 'success',
            'dispatch_result' => $dispatchResult,
            'institusi' => [
                'kode' => $institusi->kode,
                'nama' => $institusi->nama,
            ],
            'tampered' => (bool) $request->tamper_signature,
        ]);
    }
}
