<script setup>
import RupiahFormat from '@/Components/Atoms/RupiahFormat.vue';

defineProps({
    item: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div class="relative pl-6 pb-5 border-l-2 border-neutral-900 last:pb-1">
        <!-- Dot circle -->
        <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full border-1.5 border-neutral-900 bg-retro-yellow"></div>

        <div class="bg-white p-3.5 rounded-xl border-1.5 border-neutral-900 shadow-retro-sm">
            <div class="flex items-center justify-between gap-2 mb-1">
                <span class="text-xs font-bold text-neutral-900 font-mono">
                    {{ item.referensi_gateway }}
                </span>
                <span
                    class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase border"
                    :class="item.event_type === 'payment.success'
                        ? 'bg-retro-green-light text-retro-green-dark border-retro-green'
                        : 'bg-retro-coral-light text-retro-coral-dark border-retro-coral'"
                >
                    {{ item.event_type }}
                </span>
            </div>

            <div class="flex items-baseline justify-between text-xs text-neutral-600">
                <span class="font-bold text-neutral-900">
                    <RupiahFormat :value="item.nominal_bayar" />
                </span>
                <span class="text-[11px] text-neutral-500">
                    {{ new Date(item.waktu_transaksi).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) }}
                </span>
            </div>
        </div>
    </div>
</template>

