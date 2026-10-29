<script setup>
import PaymentTimelineItem from '@/Components/Molecules/PaymentTimelineItem.vue';

defineProps({
    transactions: {
        type: Array,
        default: () => [],
    },
    title: {
        type: String,
        default: 'Riwayat Transaksi Gateway',
    },
});
</script>

<template>
    <div class="bg-white rounded-3xl border-2 border-neutral-900 shadow-retro p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-extrabold text-neutral-900 font-retro flex items-center gap-2">
                <span>📜</span>
                <span>{{ title }}</span>
            </h3>
            <span class="px-2 py-0.5 rounded-full bg-neutral-100 text-neutral-700 text-xs font-bold border border-neutral-800">
                {{ transactions.length }} Transaksi
            </span>
        </div>

        <div v-if="transactions.length > 0" class="pt-2">
            <PaymentTimelineItem
                v-for="trx in transactions"
                :key="trx.id"
                :item="trx"
            />
        </div>

        <div v-else class="py-8 text-center text-neutral-400">
            <span class="text-3xl block mb-1">⏳</span>
            <p class="text-xs font-bold text-neutral-700">Belum Ada Transaksi</p>
            <p class="text-[11px] text-neutral-500 mt-0.5">Notifikasi webhook dari payment gateway akan tercatat otomatis di sini.</p>
        </div>
    </div>
</template>
