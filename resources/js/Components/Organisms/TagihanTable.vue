<script setup>
import { Link } from '@inertiajs/vue3';
import RetroBadge from '@/Components/Atoms/RetroBadge.vue';
import RupiahFormat from '@/Components/Atoms/RupiahFormat.vue';

defineProps({
    tagihans: {
        type: Object,
        required: true,
    },
});

defineEmits(['viewHistory']);
</script>

<template>
    <div class="bg-white rounded-2xl border-1.5 border-neutral-900 shadow-retro overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-neutral-50 border-b-1.5 border-neutral-900 text-[11px] font-extrabold uppercase tracking-wider text-neutral-600">
                        <th class="py-3.5 px-4">No. Tagihan</th>
                        <th class="py-3.5 px-4">Mahasiswa</th>
                        <th class="py-3.5 px-4">Semester</th>
                        <th class="py-3.5 px-4">Nominal</th>
                        <th class="py-3.5 px-4">Batas Pembayaran</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-center">Riwayat</th>
                    </tr>
                </thead>
                <tbody class="divide-y border-neutral-200 text-sm">
                    <tr
                        v-for="item in tagihans.data"
                        :key="item.id"
                        class="hover:bg-neutral-50/80 transition-colors"
                    >
                        <!-- No. Tagihan -->
                        <td class="py-4 px-4 font-mono font-bold text-neutral-900">
                            {{ item.nomor_tagihan }}
                            <div v-if="item.virtual_account" class="text-[11px] font-normal text-neutral-500 font-sans">
                                VA: {{ item.virtual_account }}
                            </div>
                        </td>

                        <!-- Mahasiswa -->
                        <td class="py-4 px-4">
                            <div class="font-bold text-neutral-900">{{ item.mahasiswa?.name || '-' }}</div>
                            <div class="text-xs text-neutral-500 font-mono">
                                NIM: {{ item.mahasiswa?.nim || '-' }} • {{ item.mahasiswa?.prodi || '-' }}
                            </div>
                        </td>

                        <!-- Semester -->
                        <td class="py-4 px-4 text-xs font-semibold text-neutral-700">
                            {{ item.semester }}
                        </td>

                        <!-- Nominal -->
                        <td class="py-4 px-4 font-bold text-neutral-900">
                            <RupiahFormat :value="item.nominal" />
                        </td>

                        <!-- Jatuh Tempo -->
                        <td class="py-4 px-4 text-xs text-neutral-600">
                            <div>{{ new Date(item.tanggal_kedaluwarsa).toLocaleDateString('id-ID', { dateStyle: 'medium' }) }}</div>
                            <div class="text-[11px] text-neutral-400">
                                {{ new Date(item.tanggal_kedaluwarsa).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }} WIB
                            </div>
                        </td>

                        <!-- Status Badge -->
                        <td class="py-4 px-4">
                            <RetroBadge :status="item.status" />
                        </td>

                        <!-- Aksi / Riwayat -->
                        <td class="py-4 px-4 text-center">
                            <button
                                type="button"
                                @click="$emit('viewHistory', item)"
                                class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold border-1.5 border-neutral-900 bg-white hover:bg-retro-yellow transition-all shadow-retro-sm cursor-pointer"
                            >
                                <span>📜</span>
                                <span>{{ item.riwayat_transaksis?.length || 0 }} Trx</span>
                            </button>
                        </td>
                    </tr>

                    <!-- Empty State -->
                    <tr v-if="!tagihans.data || tagihans.data.length === 0">
                        <td colspan="7" class="py-12 text-center text-neutral-500">
                            <div class="text-4xl mb-2">📭</div>
                            <p class="font-bold text-base text-neutral-800">Tidak ada data tagihan</p>
                            <p class="text-xs mt-1 text-neutral-500">Sesuaikan filter atau buat tagihan UKT baru untuk institusi ini.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="tagihans.links && tagihans.links.length > 3" class="p-4 border-t-1.5 border-neutral-900 bg-neutral-50 flex items-center justify-between">
            <span class="text-xs text-neutral-600 font-medium">
                Menampilkan <span class="font-bold">{{ tagihans.from || 0 }}</span> - <span class="font-bold">{{ tagihans.to || 0 }}</span> dari <span class="font-bold">{{ tagihans.total }}</span> tagihan
            </span>

            <div class="flex items-center gap-1">
                <template v-for="(link, key) in tagihans.links" :key="key">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        class="px-3 py-1 rounded-lg text-xs font-bold border-1.5 border-neutral-900 transition-all"
                        :class="link.active ? 'bg-retro-yellow text-neutral-900 shadow-retro-sm' : 'bg-white text-neutral-700 hover:bg-neutral-100'"
                        v-html="link.label"
                    />
                    <span
                        v-else
                        class="px-3 py-1 rounded-lg text-xs font-bold text-neutral-400 border border-neutral-300"
                        v-html="link.label"
                    />
                </template>
            </div>
        </div>
    </div>
</template>
