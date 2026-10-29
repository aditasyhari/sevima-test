<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import RetroNavbar from '@/Components/Organisms/RetroNavbar.vue';
import StatCard from '@/Components/Molecules/StatCard.vue';
import StatusFilterPills from '@/Components/Molecules/StatusFilterPills.vue';
import TagihanTable from '@/Components/Organisms/TagihanTable.vue';
import CreateTagihanModal from '@/Components/Organisms/CreateTagihanModal.vue';
import RetroButton from '@/Components/Atoms/RetroButton.vue';
import RetroInput from '@/Components/Atoms/RetroInput.vue';
import TransactionLedgerList from '@/Components/Organisms/TransactionLedgerList.vue';

const props = defineProps({
    tagihans: Object,
    filters: Object,
    stats: Object,
    students: Array,
});

const page = usePage();
const flash = computed(() => page.props.flash);

const selectedStatus = ref(props.filters.status || 'semua');
const searchQuery = ref(props.filters.search || '');
const showCreateModal = ref(false);

// Modal Riwayat Transaksi
const selectedTagihanForHistory = ref(null);
const showHistoryModal = ref(false);

const handleStatusChange = (status) => {
    selectedStatus.value = status;
    applyFilter();
};

const handleSearch = () => {
    applyFilter();
};

const applyFilter = () => {
    router.get(
        route('admin.tagihan.index'),
        {
            status: selectedStatus.value === 'semua' ? '' : selectedStatus.value,
            search: searchQuery.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

const openHistoryModal = (tagihan) => {
    selectedTagihanForHistory.value = tagihan;
    showHistoryModal.value = true;
};
</script>

<template>
    <Head title="Manajemen Tagihan UKT - Admin Keuangan" />

    <div class="min-h-screen bg-retro-bg flex flex-col">
        <!-- Persistent Retro Navbar -->
        <RetroNavbar />

        <!-- Main Workspace -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Flash Toast Notification -->
            <div
                v-if="flash?.success"
                class="mb-6 p-4 rounded-2xl bg-retro-green-light border-1.5 border-retro-green text-retro-green-dark text-sm font-bold shadow-retro flex items-center justify-between"
            >
                <div class="flex items-center gap-2">
                    <span>✅</span>
                    <span>{{ flash.success }}</span>
                </div>
            </div>

            <!-- Page Title Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-neutral-900 tracking-tight font-retro">
                        Tagihan UKT Kampus
                    </h1>
                    <p class="text-xs sm:text-sm text-neutral-600 font-medium mt-0.5">
                        Kelola data penagihan semester dan pantau status pelunasan mahasiswa.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <RetroButton
                        variant="primary"
                        @click="showCreateModal = true"
                    >
                        ➕ Buat Tagihan UKT
                    </RetroButton>
                </div>
            </div>

            <!-- Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <StatCard
                    title="Total Tagihan Diterbitkan"
                    :value="stats.total_nominal"
                    :isCurrency="true"
                    :subtitle="`${tagihans.total} total tagihan`"
                    variant="default"
                    icon="💰"
                />
                <StatCard
                    title="Terbayar Lunas"
                    :value="stats.total_lunas"
                    :isCurrency="true"
                    :subtitle="`${stats.count_lunas} mahasiswa lunas`"
                    variant="green"
                    icon="✅"
                />
                <StatCard
                    title="Belum Bayar"
                    :value="stats.total_belum_bayar"
                    :isCurrency="true"
                    :subtitle="`${stats.count_belum_bayar} mahasiswa menunggu`"
                    variant="yellow"
                    icon="⏳"
                />
                <StatCard
                    title="Kedaluwarsa"
                    :value="stats.count_expired"
                    subtitle="Perlu tindak lanjut / perpanjangan"
                    variant="coral"
                    icon="⚠️"
                />
            </div>

            <!-- Filters & Search Toolbar (Retro Pill Style from Reference) -->
            <div class="bg-white p-4 rounded-2xl border-1.5 border-neutral-900 shadow-retro mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <!-- Status Filter Pills -->
                <StatusFilterPills
                    v-model="selectedStatus"
                    :counts="{
                        semua: tagihans.total,
                        belum_bayar: stats.count_belum_bayar,
                        lunas: stats.count_lunas,
                        kedaluwarsa: stats.count_expired,
                    }"
                    @change="handleStatusChange"
                />

                <!-- Search Input -->
                <div class="w-full md:w-72 flex items-center gap-2">
                    <RetroInput
                        v-model="searchQuery"
                        placeholder="Cari NIM, Mahasiswa, No. Tagihan..."
                        @keyup.enter="handleSearch"
                    />
                    <RetroButton
                        variant="secondary"
                        size="md"
                        @click="handleSearch"
                    >
                        🔍
                    </RetroButton>
                </div>
            </div>

            <!-- Data Table -->
            <TagihanTable
                :tagihans="tagihans"
                @viewHistory="openHistoryModal"
            />
        </main>

        <!-- Create Modal -->
        <CreateTagihanModal
            :show="showCreateModal"
            :students="students"
            @close="showCreateModal = false"
        />

        <!-- Transaction History Modal -->
        <div
            v-if="showHistoryModal && selectedTagihanForHistory"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-neutral-900/60 backdrop-blur-xs"
            @click.self="showHistoryModal = false"
        >
            <div class="bg-white rounded-3xl border-2 border-neutral-900 shadow-retro-lg max-w-lg w-full overflow-hidden">
                <div class="p-5 border-b-1.5 border-neutral-900 bg-retro-bg flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-extrabold text-neutral-900 font-retro">
                            Riwayat Transaksi Tagihan
                        </h3>
                        <p class="text-xs text-neutral-600 font-mono">
                            {{ selectedTagihanForHistory.nomor_tagihan }} • {{ selectedTagihanForHistory.mahasiswa?.name }}
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="showHistoryModal = false"
                        class="w-8 h-8 rounded-full border-1.5 border-neutral-900 bg-white hover:bg-neutral-100 flex items-center justify-center font-bold text-sm shadow-retro-sm cursor-pointer"
                    >
                        ✕
                    </button>
                </div>

                <div class="p-6">
                    <TransactionLedgerList
                        :transactions="selectedTagihanForHistory.riwayat_transaksis || []"
                        :title="`Log Gateway (${selectedTagihanForHistory.nomor_tagihan})`"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
