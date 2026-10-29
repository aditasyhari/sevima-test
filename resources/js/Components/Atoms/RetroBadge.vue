<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        default: 'neutral', // 'lunas', 'belum_bayar', 'kedaluwarsa', 'neutral', 'info'
    },
    label: {
        type: String,
        default: '',
    },
});

const statusClasses = computed(() => {
    switch (props.status) {
        case 'lunas':
            return 'bg-retro-green-light text-retro-green-dark border-retro-green';
        case 'belum_bayar':
            return 'bg-retro-yellow-light text-retro-yellow-dark border-retro-yellow-dark';
        case 'kedaluwarsa':
            return 'bg-retro-coral-light text-retro-coral-dark border-retro-coral';
        case 'info':
            return 'bg-retro-blue-light text-retro-blue border-retro-blue';
        case 'neutral':
        default:
            return 'bg-neutral-100 text-neutral-800 border-neutral-800';
    }
});

const displayLabel = computed(() => {
    if (props.label) return props.label;
    switch (props.status) {
        case 'lunas':
            return 'Lunas';
        case 'belum_bayar':
            return 'Belum Bayar';
        case 'kedaluwarsa':
            return 'Kedaluwarsa';
        default:
            return props.status;
    }
});
</script>

<template>
    <span
        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border-1.5 uppercase tracking-wider select-none shadow-retro-sm"
        :class="statusClasses"
    >
        <slot>{{ displayLabel }}</slot>
    </span>
</template>
