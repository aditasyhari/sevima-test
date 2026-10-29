<script setup>
import { Head } from '@inertiajs/vue3';
import RetroNavbar from '@/Components/Organisms/RetroNavbar.vue';
import StudentHeroBillCard from '@/Components/Organisms/StudentHeroBillCard.vue';
import KrsStatusBadgeCard from '@/Components/Molecules/KrsStatusBadgeCard.vue';
import TransactionLedgerList from '@/Components/Organisms/TransactionLedgerList.vue';
import RetroBadge from '@/Components/Atoms/RetroBadge.vue';
import RupiahFormat from '@/Components/Atoms/RupiahFormat.vue';

defineProps({
    mahasiswa: Object,
    krs_status: Object,
    active_bill: Object,
    past_bills: Array,
});
</script>

<template>
    <Head title="Dashboard Mahasiswa - Status UKT & KRS" />

    <div class="min-h-screen bg-retro-bg flex flex-col">
        <!-- Persistent Retro Navbar -->
        <RetroNavbar />

        <!-- Main Container -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Student Header Profile Banner -->
            <div class="bg-white rounded-3xl border-2 border-neutral-900 shadow-retro p-6 mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-retro-yellow border-2 border-neutral-900 flex items-center justify-center text-2xl shadow-retro-sm shrink-0 font-retro">
                        🎓
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl sm:text-2xl font-extrabold text-neutral-900 font-retro">
                                Halo, {{ mahasiswa.name }}! 👋
                            </h1>
                            <span class="px-2.5 py-0.5 rounded-full bg-neutral-900 text-white text-xs font-mono font-bold">
                                {{ mahasiswa.nim }}
                            </span>
                        </div>
                        <p class="text-xs sm:text-sm text-neutral-600 font-medium mt-0.5">
                            {{ mahasiswa.prodi }} • Semester {{ mahasiswa.semester_aktif }} • {{ mahasiswa.institusi?.nama }}
                        </p>
                    </div>
                </div>

                <!-- Academic Year Pill -->
                <div class="flex items-center gap-2 self-start md:self-auto px-4 py-2 rounded-full bg-retro-bg border-1.5 border-neutral-900 shadow-retro-sm text-xs font-bold text-neutral-800">
                    <span>📅</span>
                    <span>Tahun Akademik: 2024/2025 Ganjil</span>
                </div>
            </div>

            <!-- 2-Column Responsive Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8">
                <!-- Left Main Column (Active Bill Hero Card) -->
                <div class="lg:col-span-8 space-y-6">
                    <StudentHeroBillCard :bill="active_bill" />

                    <!-- Past Bills Section -->
                    <div class="bg-white rounded-3xl border-2 border-neutral-900 shadow-retro p-6 sm:p-7">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-base font-extrabold text-neutral-900 font-retro flex items-center gap-2">
                                <span>📚</span>
                                <span>Riwayat Tagihan Semester Lalu</span>
                            </h3>
                            <span class="text-xs font-bold text-neutral-500">
                                {{ past_bills?.length || 0 }} Semester
                            </span>
                        </div>

                        <div v-if="past_bills && past_bills.length > 0" class="space-y-3">
                            <div
                                v-for="past in past_bills"
                                :key="past.id"
                                class="p-4 rounded-2xl bg-neutral-50 border-1.5 border-neutral-900 flex flex-wrap items-center justify-between gap-3 hover:bg-neutral-100/60 transition-colors"
                            >
                                <div>
                                    <span class="font-bold text-sm text-neutral-900 block font-retro">
                                        {{ past.semester }}
                                    </span>
                                    <span class="text-xs text-neutral-500 font-mono">
                                        No. {{ past.nomor_tagihan }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-4">
                                    <span class="font-bold text-sm text-neutral-900">
                                        <RupiahFormat :value="past.nominal" />
                                    </span>
                                    <RetroBadge :status="past.status" />
                                </div>
                            </div>
                        </div>

                        <div v-else class="text-center py-6 text-neutral-400">
                            <p class="text-xs font-bold text-neutral-600">Belum ada riwayat tagihan semester sebelumnya.</p>
                        </div>
                    </div>
                </div>

                <!-- Right Rail Column (KRS Clearance Card & Transaction History) -->
                <div class="lg:col-span-4 space-y-6">
                    <!-- KRS Eligibility Card (Decisive Domain Feature) -->
                    <KrsStatusBadgeCard
                        :isEligible="krs_status.is_eligible"
                        :label="krs_status.label"
                        :description="krs_status.description"
                    />

                    <!-- Transaction Ledger -->
                    <TransactionLedgerList
                        :transactions="active_bill?.riwayat_transaksi || []"
                        title="Log Transaksi Gateway"
                    />
                </div>
            </div>
        </main>
    </div>
</template>

