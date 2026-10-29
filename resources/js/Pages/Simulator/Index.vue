<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
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
    initialSelection: Object,
});

const selectedInstitutionId = ref(props.initialSelection?.institusi_id || props.institutions[0]?.id || '');
const selectedBillId = ref(props.initialSelection?.tagihan_id || '');
const selectedEvent = ref(props.initialSelection?.event || 'payment.success');
const tamperSignature = ref(false);
const isSending = ref(false);
const showSecret = ref(false);
const showDocsModal = ref(false);
const copySuccess = ref(false);

const dispatchResult = ref(null);
const logs = ref([...props.recentLogs]);

const currentInstitution = computed(() => {
    return props.institutions.find(i => i.id === selectedInstitutionId.value);
});

const currentBills = computed(() => {
    return currentInstitution.value?.tagihans || [];
});

const selectedBill = computed(() => {
    return currentBills.value.find(b => b.id === selectedBillId.value);
});

// Auto select first bill when institution changes
const onInstitutionChange = () => {
    selectedBillId.value = currentBills.value[0]?.id || '';
};

// Initial bill selection
onMounted(() => {
    if (!selectedBillId.value && currentBills.value.length > 0) {
        selectedBillId.value = currentBills.value[0].id;
    }
});

// Watch for initialSelection changes if redirected
watch(() => props.initialSelection, (newVal) => {
    if (newVal?.institusi_id) selectedInstitutionId.value = newVal.institusi_id;
    if (newVal?.tagihan_id) selectedBillId.value = newVal.tagihan_id;
    if (newVal?.event) selectedEvent.value = newVal.event;
}, { immediate: true });

// Live Payload Preview (Reactive)
const previewPayload = computed(() => {
    const bill = selectedBill.value;
    return {
        event: selectedEvent.value,
        institution_code: currentInstitution.value?.kode || 'UNIV-A',
        bill_number: bill?.nomor_tagihan || 'TAG-SAMPLE-001',
        amount: bill ? parseFloat(bill.nominal) : 4500000,
        transaction_reference: 'MOCK-GW-' + (bill ? bill.nomor_tagihan.replace(/[^a-zA-Z0-9]/g, '') : 'TRX') + '-' + Math.floor(1000 + Math.random() * 9000),
        payment_time: new Date().toISOString(),
    };
});

// Live cURL Command Generator
const liveCurlCommand = computed(() => {
    const payloadStr = JSON.stringify(previewPayload.value);
    const secret = tamperSignature.value ? 'tampered_invalid_secret_key' : (currentInstitution.value?.webhook_secret || 'secret_key');
    
    return `# Pemicu Webhook via cURL:\n` +
        `curl -X POST "${props.webhookUrl}" \\\n` +
        `     -H "Content-Type: application/json" \\\n` +
        `     -H "Accept: application/json" \\\n` +
        `     -H "X-Signature: [DIHITUNG_DENGAN_HMAC_SHA256]" \\\n` +
        `     -d '${payloadStr}'`;
});

const copyCurl = async () => {
    try {
        await navigator.clipboard.writeText(liveCurlCommand.value);
        copySuccess.value = true;
        setTimeout(() => {
            copySuccess.value = false;
        }, 2000);
    } catch (e) {
        alert('Gagal menyalin perintah cURL.');
    }
};

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

        // Auto reload page after 2 seconds to reflect status updates
        setTimeout(() => {
            window.location.reload();
        }, 2500);
    } catch (err) {
        dispatchResult.value = {
            status: 'error',
            dispatch_result: {
                status_code: err.response?.status || 500,
                response_body: err.response?.data || err.message,
            },
            tampered: tamperSignature.value,
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
                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                        <span class="px-2.5 py-0.5 rounded-full bg-retro-yellow text-neutral-900 text-xs font-bold border border-neutral-900 font-retro">
                            🛠️ Evaluasi Tahap 1 & 2
                        </span>
                        <span class="text-xs font-bold text-neutral-500 font-mono">
                            Endpoint: {{ webhookUrl }}
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-neutral-900 font-retro">
                        Mock Payment Gateway Simulator
                    </h1>
                    <p class="text-xs sm:text-sm text-neutral-600 font-medium mt-0.5">
                        Simulator resmi untuk memicu event webhook notifikasi pembayaran dan verifikasi signature HMAC-SHA256 multi-tenant.
                    </p>
                </div>

                <div class="flex items-center gap-3 self-start md:self-auto">
                    <button
                        type="button"
                        @click="showDocsModal = true"
                        class="px-4 py-2 rounded-2xl bg-retro-bg hover:bg-neutral-100 border-1.5 border-neutral-900 shadow-retro-sm text-xs font-bold text-neutral-900 flex items-center gap-1.5 cursor-pointer"
                    >
                        <span>📖</span>
                        <span>Spesifikasi & Dok. Webhook</span>
                    </button>
                </div>
            </div>

            <!-- 2-Column Grid: Left Controls, Right Inspector -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8">
                <!-- Simulator Control Panel -->
                <div class="lg:col-span-6 bg-white rounded-3xl border-2 border-neutral-900 shadow-retro p-6 sm:p-7 flex flex-col justify-between">
                    <div>
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

                                <div v-if="currentInstitution" class="mt-1.5 p-2.5 rounded-xl bg-neutral-50 border border-neutral-200 text-xs font-mono text-neutral-600 flex items-center justify-between">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-neutral-500 font-bold">Secret HMAC:</span>
                                        <span class="font-bold text-neutral-900 font-mono">
                                            {{ showSecret ? currentInstitution.webhook_secret : '••••••••••••••••••••' }}
                                        </span>
                                    </div>
                                    <button
                                        type="button"
                                        @click="showSecret = !showSecret"
                                        class="text-xs px-2 py-0.5 rounded border border-neutral-300 hover:bg-neutral-200 text-neutral-700 font-sans cursor-pointer font-bold"
                                    >
                                        {{ showSecret ? '🙈 Sembunyikan' : '👁️ Tampilkan' }}
                                    </button>
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

                                <div v-if="selectedBill" class="mt-1.5 p-2 rounded-xl bg-neutral-50 border border-neutral-200 text-[11px] text-neutral-600 flex items-center justify-between">
                                    <span>Mahasiswa: <strong>{{ selectedBill.mahasiswa?.name }}</strong> (NIM: {{ selectedBill.mahasiswa?.nim }})</span>
                                    <span>Nominal: <strong><RupiahFormat :value="selectedBill.nominal" /></strong></span>
                                </div>
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
                                            Jika dicentang, webhook akan dikirim dengan signature yang tidak valid untuk menguji bahwa sistem menolak request dengan HTTP 401 Unauthorized.
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

                    <!-- Live cURL Code Generator -->
                    <div class="mt-6 pt-5 border-t-1.5 border-neutral-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[11px] font-extrabold uppercase tracking-wider text-neutral-600">
                                💻 Salin Perintah cURL (Uji Terminal / Postman):
                            </span>
                            <button
                                type="button"
                                @click="copyCurl"
                                class="px-2.5 py-1 rounded-lg border border-neutral-900 bg-white hover:bg-retro-yellow text-xs font-bold shadow-retro-sm cursor-pointer transition-colors"
                            >
                                {{ copySuccess ? '✅ Tersalin!' : '📋 Salin cURL' }}
                            </button>
                        </div>
                        <pre class="p-3 rounded-xl bg-neutral-900 text-neutral-300 text-[11px] font-mono overflow-x-auto whitespace-pre-wrap">{{ liveCurlCommand }}</pre>
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
                                <pre class="p-3 rounded-xl bg-neutral-900 text-retro-yellow text-xs font-mono overflow-x-auto">{{ dispatchResult.dispatch_result?.sent_signature || 'Signature Error' }}</pre>
                            </div>

                            <!-- Payload Sent -->
                            <div>
                                <span class="text-[11px] font-bold text-neutral-500 uppercase block mb-1">
                                    Payload JSON yang Dikirim:
                                </span>
                                <pre class="p-3 rounded-xl bg-neutral-900 text-green-400 text-xs font-mono overflow-x-auto">{{ JSON.stringify(dispatchResult.dispatch_result?.sent_payload || previewPayload, null, 2) }}</pre>
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
                            Setiap request webhook (sukses maupun gagal) otomatis tercatat pada tabel audit log di bawah.
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
                            Dicatat secara otomatis untuk keperluan investigasi insiden dan kepatuhan finansial perbankan.
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

        <!-- In-App Documentation Modal -->
        <div
            v-if="showDocsModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-neutral-900/60 backdrop-blur-xs"
            @click.self="showDocsModal = false"
        >
            <div class="bg-white rounded-3xl border-2 border-neutral-900 shadow-retro-lg max-w-2xl w-full max-h-[85vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                <div class="p-5 border-b-1.5 border-neutral-900 bg-retro-yellow flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-extrabold text-neutral-900 font-retro">
                            📖 Dokumentasi Teknis Webhook Gateway
                        </h3>
                        <p class="text-xs text-neutral-800 font-medium">
                            Spesifikasi integrasi resmi sesuai standar SEVIMA Project M2.
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="showDocsModal = false"
                        class="w-8 h-8 rounded-full border-1.5 border-neutral-900 bg-white hover:bg-neutral-100 flex items-center justify-center font-bold text-sm shadow-retro-sm cursor-pointer"
                    >
                        ✕
                    </button>
                </div>

                <div class="p-6 overflow-y-auto space-y-5 text-xs text-neutral-700 leading-relaxed font-sans">
                    <div>
                        <h4 class="font-extrabold text-sm text-neutral-900 font-retro mb-1">1. Endpoint & Header Wajib</h4>
                        <div class="p-3 rounded-xl bg-neutral-50 border border-neutral-300 font-mono text-[11px] space-y-1">
                            <div><strong class="text-neutral-900">URL:</strong> POST /api/v1/webhook/payment</div>
                            <div><strong class="text-neutral-900">Content-Type:</strong> application/json</div>
                            <div><strong class="text-neutral-900">X-Signature:</strong> HMAC-SHA256(raw_json_body, secret_institusi)</div>
                        </div>
                    </div>

                    <div>
                        <h4 class="font-extrabold text-sm text-neutral-900 font-retro mb-1">2. Format Payload (Bilingual Support)</h4>
                        <pre class="p-3 rounded-xl bg-neutral-900 text-neutral-100 text-[11px] font-mono overflow-x-auto">{
  "event": "payment.success", // atau "payment.expired"
  "institution_code": "UNIV-A", // alias: "kode_institusi"
  "bill_number": "TAG-2024-001", // alias: "nomor_tagihan"
  "amount": 4500000.00, // alias: "nominal"
  "transaction_reference": "GW-REF-99812", // alias: "referensi_gateway"
  "payment_time": "2026-10-29T10:00:00Z" // alias: "waktu_transaksi"
}</pre>
                    </div>

                    <div>
                        <h4 class="font-extrabold text-sm text-neutral-900 font-retro mb-1">3. Status Respon Server</h4>
                        <div class="grid grid-cols-2 gap-2 text-[11px]">
                            <div class="p-2 rounded-lg bg-green-50 border border-green-200">
                                <strong class="text-green-800">200 OK:</strong> Signature valid, status diperbarui / diabaikan secara idempotent.
                            </div>
                            <div class="p-2 rounded-lg bg-red-50 border border-red-200">
                                <strong class="text-red-800">401 Unauthorized:</strong> Signature HMAC tidak cocok dengan secret institusi.
                            </div>
                            <div class="p-2 rounded-lg bg-amber-50 border border-amber-200">
                                <strong class="text-amber-800">404 Not Found:</strong> Institusi atau tagihan tidak ditemukan.
                            </div>
                            <div class="p-2 rounded-lg bg-purple-50 border border-purple-200">
                                <strong class="text-purple-800">422 Unprocessable:</strong> Event tidak dikenal atau payload malformed.
                            </div>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-retro-yellow-light/60 border border-neutral-900 text-[11px]">
                        <strong>🔒 Aturan Keamanan Secret:</strong> Sesuai instruksi soal, secret HMAC tidak dicantumkan di repositori publik dan dikirimkan terpisah melalui email konfirmasi submission.
                    </div>
                </div>

                <div class="p-4 border-t border-neutral-200 bg-neutral-50 flex justify-end">
                    <RetroButton variant="primary" size="sm" @click="showDocsModal = false">
                        Tutup Dokumentasi
                    </RetroButton>
                </div>
            </div>
        </div>
    </div>
</template>
