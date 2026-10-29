<script setup>
import { computed } from 'vue';

const props = defineProps({
    value: {
        type: [Number, String],
        required: true,
    },
    prefix: {
        type: Boolean,
        default: true,
    },
});

const formatted = computed(() => {
    const num = Number(props.value) || 0;
    const formattedNum = new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(num);

    return props.prefix ? `Rp ${formattedNum}` : formattedNum;
});
</script>

<template>
    <span>{{ formatted }}</span>
</template>
