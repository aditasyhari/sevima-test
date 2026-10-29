<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import RetroNavbar from '@/Components/Organisms/RetroNavbar.vue';
import RetroButton from '@/Components/Atoms/RetroButton.vue';
import RetroSelect from '@/Components/Atoms/RetroSelect.vue';
import RetroBadge from '@/Components/Atoms/RetroBadge.vue';
import RupiahFormat from '@/Components/Atoms/RupiahFormat.vue';
import axios from 'axios';

const props = defineProps({
    institutions: Array,
    recentLogs: Array,
    webhookUrl: String,
});

const selectedInstitutionId = ref(props.institutions[0]?.id || '');
const selectedBillId = ref('');
const selectedEvent = ref('payment.success');
const tamperSignature = ref(false);
const isSending = ref(false);

const dispatchResult = ref(null);
const logs = ref([...props.recentLogs]);

const currentInstitution = computed(() => {
    return props.institutions.find(i => i.id === selectedInstitutionId.value);
});

const currentBills = computed(() => {
    return currentInstitution.value?.tagihans || [];
});

// Auto select first bill when institution changes
const onInstitutionChange = () => {
    selectedBillId.value = currentBills.value[0]?.id || '';
};

// Initial bill selection
if (currentBills.value.length > 0) {
    selectedBillId.value = currentBills.value[0].id;
}

const sendWebhook = async () => {
    if (!selectedInstitutionId.value || !selectedBillId.value) {
        alert('Silakan pilih institusi dan tagihan terlebih dahulu.');
        return;
    }

    isSending.value = true;
    dispatchResult.value = null;

    try {
        const response = await axios.post(route('simulator.trigger'), {
            institusi_id: selectedInstitutionId.value,
            tagihan_id: selectedBillId.value,
            event: selectedEvent.value,
            tamper_signature: tamperSignature.value,
        });

        dispatchResult.value = response.data;

        // Auto reload log
        setTimeout(() => {
            window.location.reload();
        }, 2000);
    } catch (err) {
        dispatchResult.value = {
            status: 'error',
            error: err.response?.data || err.message,
        };
    } finally {
        isSending.value = false;
    }
};
</script>

<template>
    <Head title="Mock Payment Gateway Simulator" />

    <div class="min-h-screen bg-retro-bg flex flex-col">
        <RetroNavbar />

        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header Banner -->
            <div class="bg-white rounded-3xl border-2 border-neutral-900 shadow-retro p-6 mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2.5 py-0.5 rounded-full bg-retro-yellow text-neutral-900 text-xs font-bold border border-neutral-900">
                            🛠️ Evaluasi Tahap 1 & 2
                        </span>
                        <span class="text-xs font-bold text-neutral-500 font-mono">
                            Webhook Receiver Endpoint: {{ webhookUrl }}
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-neutral-900 font-retro">
                        Mock Payment Gateway Simulator
                    </h1>
                    <p class="text-xs sm:text-sm text-neutral-600 font-medium mt-0.5">
                        Simulator resmi untuk memicu event webhook notifikasi pembayaran dan verifikasi signature HMAC-SHA256 multi-tenant.
                    </p>
                </div>

                <div class="px-4 py-2 rounded-2xl bg-neutral-50 border-1.5 border-neutral-900 text-xs font-mono">
                    <span class="text-neutral-500 block text-[10px] uppercase font-bold">Spesifikasi HMAC:</span>
                    <span class="font-bold text-neutral-900">Header: X-Signature (SHA256)</span>
                </div>
            </div>

            <!-- 2-Column Grid: Left Controls, Right Inspector -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8">
                <!-- Simulator Control Panel -->
                <div class="lg:col-span-6 bg-white rounded-3xl border-2 border-neutral-900 shadow-retro p-6 sm:p-7">
                    <h3 class="text-lg font-extrabold text-neutral-900 font-retro mb-5 flex items-center gap-2">
                        <span>⚡</span>
                        <span>Konfigurasi Payload & Event</span>
                    </h3>

                    <div class="space-y-4">
                        <!-- Select Institusi -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1">
                                1. Pilih Institusi Kampus (Multi-Tenant) *
                            </label>
                            <RetroSelect v-model="selectedInstitutionId" @change="onInstitutionChange">
                                <option v-for="inst in institutions" :key="inst.id" :value="inst.id">
                                    {{ inst.nama }} (Kode: {{ inst.kode }})
                                </option>
                            </RetroSelect>

                            <div v-if="currentInstitution" class="mt-1.5 p-2 rounded-xl bg-neutral-50 border border-neutral-200 text-xs font-mono text-neutral-600 flex items-center justify-between">
                                <span>Secret HMAC:</span>
                                <span class="font-bold text-neutral-900 truncate max-w-[220px]">{{ currentInstitution.webhook_secret }}</span>
                            </div>
                        </div>

                        <!-- Select Tagihan -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1">
                                2. Pilih Tagihan yang Akan Dinotifikasi *
                            </label>
                            <RetroSelect v-model="selectedBillId">
                                <option v-for="bill in currentBills" :key="bill.id" :value="bill.id">
                                    {{ bill.nomor_tagihan }} - {{ bill.mahasiswa?.name }} (Status: {{ bill.status }})
                                </option>
                            </RetroSelect>
                        </div>

                        <!-- Select Event Type -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1">
                                3. Jenis Event Notifikasi *
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <label
                                    class="p-3 rounded-2xl border-1.5 border-neutral-900 cursor-pointer flex items-center gap-2 text-xs font-bold transition-all"
                                    :class="selectedEvent === 'payment.success' ? 'bg-retro-green-light shadow-retro-sm' : 'bg-white hover:bg-neutral-50'"
                                >
                                    <input type="radio" v-model="selectedEvent" value="payment.success" class="accent-retro-green" />
                                    <span>✅ payment.success (Pelunasan)</span>
                                </label>

                                <label
                                    class="p-3 rounded-2xl border-1.5 border-neutral-900 cursor-pointer flex items-center gap-2 text-xs font-bold transition-all"
                                    :class="selectedEvent === 'payment.expired' ? 'bg-retro-coral-light shadow-retro-sm' : 'bg-white hover:bg-neutral-50'"
                                >
                                    <input type="radio" v-model="selectedEvent" value="payment.expired" class="accent-retro-coral" />
                                    <span>⚠️ payment.expired (Kedaluwarsa)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Security Test Toggle: Tamper Signature -->
                        <div class="p-4 rounded-2xl bg-retro-yellow-light/60 border-1.5 border-neutral-900">
                            <label class="flex items-start gap-2.5 cursor-pointer text-xs font-bold text-neutral-900">
                                <input type="checkbox" v-model="tamperSignature" class="mt-0.5 rounded border-neutral-900 text-retro-coral focus:ring-retro-yellow" />
                                <div>
                                    <span>Uji Keamanan: Sengaja Rusak Signature HMAC (Tampering Test)</span>
                                    <p class="text-[11px] font-normal text-neutral-600 mt-0.5">
                                        Jika dicentang, webhook akan dikirim dengan secret yang salah untuk menguji bahwa sistem menolak dengan HTTP 401 Unauthorized.
                                    </p>
                                </div>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-2">
                            <RetroButton
                                variant="primary"
                                size="lg"
                                class="w-full"
                                :disabled="isSending"
                                @click="sendWebhook"
                            >
                                <span v-if="isSending">⏳ Mengirim & Memverifikasi...</span>
                                <span v-else>🚀 Hitung HMAC & Kirim Webhook Notifikasi</span>
                            </RetroButton>
                        </div>
                    </div>
                </div>

                <!-- Live Webhook Inspector Output -->
                <div class="lg:col-span-6 bg-white rounded-3xl border-2 border-neutral-900 shadow-retro p-6 sm:p-7 flex flex-col justify-between">
                    <div>
                        <h3 class="text-lg font-extrabold text-neutral-900 font-retro mb-5 flex items-center gap-2">
                            <span>🔍</span>
                            <span>Inspektur Respon Webhook (Live)</span>
                        </h3>

                        <div v-if="dispatchResult" class="space-y-4">
                            <!-- Status Code Banner -->
                            <div
                                class="p-4 rounded-2xl border-1.5 border-neutral-900 flex items-center justify-between"
                                :class="dispatchResult.dispatch_result?.status_code === 200
                                    ? 'bg-retro-green-light text-retro-green-dark'
                                    : 'bg-retro-coral-light text-retro-coral-dark'"
                            >
                                <div class="flex items-center gap-2 font-bold text-sm">
                                    <span>{{ dispatchResult.dispatch_result?.status_code === 200 ? '✅' : '🚫' }}</span>
                                    <span>HTTP Status: {{ dispatchResult.dispatch_result?.status_code }}</span>
                                </div>
                                <span class="text-xs font-mono uppercase font-bold">
                                    {{ dispatchResult.tampered ? 'Signature Dirusak (Tampered)' : 'Signature Valid' }}
                                </span>
                            </div>

                            <!-- Signature Generated -->
                            <div>
                                <span class="text-[11px] font-bold text-neutral-500 uppercase block mb-1">
                                    Header X-Signature yang Dihitung:
                                </span>
                                <pre class="p-3 rounded-xl bg-neutral-900 text-retro-yellow text-xs font-mono overflow-x-auto">{{ dispatchResult.dispatch_result?.sent_signature }}</pre>
                            </div>

                            <!-- Payload Sent -->
                            <div>
                                <span class="text-[11px] font-bold text-neutral-500 uppercase block mb-1">
                                    Payload JSON yang Dikirim:
                                </span>
                                <pre class="p-3 rounded-xl bg-neutral-900 text-green-400 text-xs font-mono overflow-x-auto">{{ JSON.stringify(dispatchResult.dispatch_result?.sent_payload, null, 2) }}</pre>
                            </div>

                            <!-- Server Response Body -->
                            <div>
                                <span class="text-[11px] font-bold text-neutral-500 uppercase block mb-1">
                                    Respon dari Server:
                                </span>
                                <pre class="p-3 rounded-xl bg-neutral-900 text-neutral-100 text-xs font-mono overflow-x-auto">{{ JSON.stringify(dispatchResult.dispatch_result?.response_body, null, 2) }}</pre>
                            </div>
                        </div>

                        <div v-else class="py-16 text-center text-neutral-400">
                            <span class="text-4xl block mb-2">📡</span>
                            <p class="text-sm font-bold text-neutral-700">Belum Ada Pengiriman Webhook</p>
                            <p class="text-xs text-neutral-500 max-w-xs mx-auto mt-1">
                                Tekan tombol kirim di panel sebelah kiri untuk melihat inspeksi payload, HMAC-SHA256, dan respon server secara real-time.
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-neutral-200 text-center">
                        <span class="text-xs text-neutral-500">
                            Tabel audit log di bawah akan mencatat setiap transaksi webhook yang masuk.
                        </span>
                    </div>
                </div>
            </div>

            <!-- Webhook Audit Logs Table (Live Observability for Interview Stage 2) -->
            <div class="bg-white rounded-3xl border-2 border-neutral-900 shadow-retro p-6 sm:p-7">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="text-lg font-extrabold text-neutral-900 font-retro flex items-center gap-2">
                            <span>📋</span>
                            <span>Audit Log Notifikasi Webhook (Live Trail)</span>
                        </h3>
                        <p class="text-xs text-neutral-600 font-medium">
                            Dicatat secara otomatis untuk keperluan investigasi insiden dan kepatuhan finansial.
                        </p>
                    </div>
                    <button
                        type="button"
                        onclick="window.location.reload()"
                        class="px-3 py-1.5 rounded-full text-xs font-bold border-1.5 border-neutral-900 bg-white hover:bg-neutral-100 shadow-retro-sm cursor-pointer"
                    >
                        🔄 Refresh Log
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-neutral-50 border-b-1.5 border-neutral-900 text-[11px] font-extrabold uppercase tracking-wider text-neutral-600">
                                <th class="py-3 px-3">Waktu</th>
                                <th class="py-3 px-3">Institusi</th>
                                <th class="py-3 px-3">Event</th>
                                <th class="py-3 px-3">Signature Valid</th>
                                <th class="py-3 px-3">HTTP Code</th>
                                <th class="py-3 px-3">Catatan Audit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y border-neutral-200">
                            <tr v-for="log in logs" :key="log.id" class="hover:bg-neutral-50">
                                <td class="py-3 px-3 font-mono text-neutral-600">
                                    {{ new Date(log.created_at).toLocaleString('id-ID', { timeStyle: 'medium', dateStyle: 'short' }) }}
                                </td>
                                <td class="py-3 px-3 font-bold text-neutral-900 font-mono">
                                    {{ log.institusi?.kode || log.kode_institusi_payload || '-' }}
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold border"
                                        :class="log.event_type === 'payment.success'
                                            ? 'bg-retro-green-light text-retro-green-dark border-retro-green'
                                            : 'bg-retro-yellow-light text-retro-yellow-dark border-retro-yellow-dark'"
                                    >
                                        {{ log.event_type || '-' }}
                                    </span>
                                </td>
                                <td class="py-3 px-3">
                                    <span v-if="log.is_signature_valid" class="text-retro-green font-bold flex items-center gap-1">
                                        <span>✅</span> Valid
                                    </span>
                                    <span v-else class="text-retro-coral font-bold flex items-center gap-1">
                                        <span>🚫</span> Invalid
                                    </span>
                                </td>
                                <td class="py-3 px-3 font-mono font-bold" :class="log.response_code === 200 ? 'text-retro-green' : 'text-retro-coral'">
                                    {{ log.response_code }}
                                </td>
                                <td class="py-3 px-3 text-neutral-700 max-w-xs truncate" :title="log.processing_notes">
                                    {{ log.processing_notes || '-' }}
                                </td>
                            </tr>

                            <tr v-if="!logs || logs.length === 0">
                                <td colspan="6" class="py-8 text-center text-neutral-400">
                                    Belum ada catatan log notifikasi webhook yang masuk.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</template>
