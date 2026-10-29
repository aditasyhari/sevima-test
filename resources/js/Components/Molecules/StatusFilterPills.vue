<script setup>
const props = defineProps({
    modelValue: {
        type: String,
        default: 'semua',
    },
    counts: {
        type: Object,
        default: () => ({
            belum_bayar: 0,
            lunas: 0,
            kedaluwarsa: 0,
        }),
    },
});

const emit = defineEmits(['update:modelValue', 'change']);

const filters = [
    { key: 'semua', label: 'Semua Status', icon: '📋' },
    { key: 'belum_bayar', label: 'Belum Bayar', icon: '⏳' },
    { key: 'lunas', label: 'Lunas', icon: '✅' },
    { key: 'kedaluwarsa', label: 'Kedaluwarsa', icon: '⚠️' },
];

const selectFilter = (key) => {
    emit('update:modelValue', key);
    emit('change', key);
};
</script>

<template>
    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
        <button
            v-for="item in filters"
            :key="item.key"
            type="button"
            @click="selectFilter(item.key)"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs md:text-sm font-bold border-1.5 border-neutral-900 transition-all cursor-pointer whitespace-nowrap select-none"
            :class="[
                modelValue === item.key
                    ? 'bg-retro-yellow text-neutral-900 shadow-retro'
                    : 'bg-white text-neutral-700 hover:bg-neutral-50 shadow-retro-sm'
            ]"
        >
            <span>{{ item.icon }}</span>
            <span>{{ item.label }}</span>
            <span
                v-if="counts && counts[item.key] !== undefined"
                class="ml-1 px-1.5 py-0.2 text-[11px] rounded-full bg-neutral-900 text-white font-extrabold"
            >
                {{ counts[item.key] }}
            </span>
        </button>
    </div>
</template>

