<script setup>
import { Link } from '@inertiajs/vue3';
import RetroBadge from '@/Components/Atoms/RetroBadge.vue';
import RupiahFormat from '@/Components/Atoms/RupiahFormat.vue';

defineProps({
    bill: {
        type: Object,
        default: null,
    },
});
</script>

<template>
    <div
        v-if="bill"
        class="bg-white rounded-3xl border-2 border-neutral-900 shadow-retro-lg p-6 sm:p-8 relative overflow-hidden"
    >
        <!-- Background decorative badge -->
        <div class="absolute -right-6 -bottom-6 w-32 h-32 rounded-full bg-retro-yellow/20 -z-0 pointer-events-none"></div>

        <div class="relative z-10">
            <!-- Header bar -->
            <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-neutral-500">
                        Tagihan Semester Aktif
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full bg-neutral-100 text-neutral-800 text-xs font-extrabold border border-neutral-800 font-mono">
                        {{ bill.semester }}
                    </span>
                </div>

                <RetroBadge :status="bill.status" />
            </div>

            <!-- Big Nominal Display -->
            <div class="mb-6">
                <span class="text-xs font-bold text-neutral-500 block mb-1">Total Tagihan UKT</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-neutral-900 tracking-tight font-retro">
                    <RupiahFormat :value="bill.nominal" />
                </h2>
            </div>

            <!-- Bill Details Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 p-4 rounded-2xl bg-neutral-50 border-1.5 border-neutral-900 mb-6">
                <div>
                    <span class="text-[11px] font-bold text-neutral-500 block">Nomor Tagihan (Invoice)</span>
                    <span class="text-sm font-bold text-neutral-900 font-mono">{{ bill.nomor_tagihan }}</span>
                </div>

                <div>
                    <span class="text-[11px] font-bold text-neutral-500 block">Batas Jatuh Tempo</span>
                    <span class="text-sm font-bold" :class="bill.is_expired && bill.status !== 'lunas' ? 'text-retro-coral' : 'text-neutral-900'">
                        {{ bill.tanggal_kedaluwarsa }}
                    </span>
                </div>

                <div>
                    <span class="text-[11px] font-bold text-neutral-500 block">Nomor Virtual Account</span>
                    <span class="text-sm font-extrabold text-neutral-900 font-mono bg-retro-yellow/40 px-2 py-0.5 rounded-md border border-neutral-800 inline-block">
                        {{ bill.virtual_account || '-' }}
                    </span>
                </div>
            </div>

            <!-- Instructions based on status -->
            <div v-if="bill.status === 'belum_bayar'" class="p-4 rounded-2xl bg-retro-yellow-light border-1.5 border-neutral-900 space-y-3">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">💡</span>
                    <p class="text-xs text-neutral-800 leading-relaxed font-medium">
                        Silakan lakukan transfer ke nomor Virtual Account di atas sebelum batas waktu berakhir. Notifikasi pembayaran dari payment gateway akan otomatis memperbarui status tagihan ini menjadi <strong>Lunas</strong> dan membuka akses KRS.
                    </p>
                </div>

                <div class="pt-2 border-t border-neutral-300 flex items-center justify-between flex-wrap gap-2">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-neutral-600">
                        ⚡ Pintasan Uji Evaluasi (Reviewer):
                    </span>
                    <Link
                        :href="route('simulator.index', { tagihan_id: bill.id })"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border-1.5 border-neutral-900 bg-white hover:bg-retro-yellow text-neutral-900 font-bold text-xs shadow-retro-sm transition-all cursor-pointer"
                    >
                        <span>🧪 Uji Pelunasan di Mock Gateway</span>
                        <span>➔</span>
                    </Link>
                </div>
            </div>

            <div v-else-if="bill.status === 'lunas'" class="p-4 rounded-2xl bg-retro-green-light border-1.5 border-neutral-900 space-y-3">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🎉</span>
                    <p class="text-xs text-retro-green-dark leading-relaxed font-medium">
                        Pembayaran UKT semester ini telah berhasil diverifikasi oleh sistem gateway. Kartu Rencana Studi (KRS) Anda siap diisi pada portal akademik.
                    </p>
                </div>
                <div class="pt-2 border-t border-neutral-300 flex items-center justify-between flex-wrap gap-2">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-retro-green-dark">
                        🎓 Hak Akses Akademik Terbuka:
                    </span>
                    <Link
                        :href="route('mahasiswa.krs.index')"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl border-1.5 border-neutral-900 bg-neutral-900 text-white hover:bg-neutral-800 font-bold text-xs shadow-retro-sm transition-all cursor-pointer"
                    >
                        <span>📝 Buka Lembar KRS Semester Ini</span>
                        <span>➔</span>
                    </Link>
                </div>
            </div>

            <div v-else-if="bill.status === 'kedaluwarsa'" class="flex items-center gap-3 p-4 rounded-xl bg-retro-coral-light border-1.5 border-neutral-900">
                <span class="text-2xl">⚠️</span>
                <p class="text-xs text-retro-coral-dark leading-relaxed font-medium">
                    Masa berlaku pembayaran tagihan ini telah kedaluwarsa. Mohon segera hubungi Admin Keuangan kampus untuk perpanjangan masa aktif atau penerbitan tagihan baru.
                </p>
            </div>
        </div>
    </div>

    <!-- Empty state if no bill -->
    <div v-else class="bg-white rounded-3xl border-2 border-neutral-900 shadow-retro-lg p-12 text-center">
        <div class="text-5xl mb-3">🎓</div>
        <h3 class="text-xl font-bold text-neutral-900 font-retro">Belum Ada Tagihan Aktif</h3>
        <p class="text-xs text-neutral-500 mt-1 max-w-sm mx-auto">
            Anda belum memiliki tagihan UKT aktif untuk semester ini. Hubungi Bagian Keuangan kampus jika Anda memerlukan penerbitan tagihan.
        </p>
    </div>
</template>

