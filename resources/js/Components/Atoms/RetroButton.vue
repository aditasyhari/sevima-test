<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'primary', // 'primary', 'secondary', 'danger', 'outline', 'ghost'
    },
    size: {
        type: String,
        default: 'md', // 'sm', 'md', 'lg'
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    type: {
        type: String,
        default: 'button',
    },
});

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'primary':
            return 'bg-retro-yellow text-neutral-900 border-neutral-900 shadow-retro hover:bg-yellow-400 active:translate-x-[1px] active:translate-y-[1px] active:shadow-none';
        case 'secondary':
            return 'bg-white text-neutral-900 border-neutral-900 shadow-retro hover:bg-neutral-50 active:translate-x-[1px] active:translate-y-[1px] active:shadow-none';
        case 'danger':
            return 'bg-retro-coral text-white border-neutral-900 shadow-retro hover:bg-red-600 active:translate-x-[1px] active:translate-y-[1px] active:shadow-none';
        case 'outline':
            return 'bg-transparent text-neutral-900 border-neutral-900 hover:bg-neutral-100 active:bg-neutral-200';
        case 'ghost':
            return 'bg-transparent text-neutral-700 border-transparent hover:bg-neutral-200/50';
        default:
            return 'bg-retro-yellow text-neutral-900 border-neutral-900 shadow-retro';
    }
});

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm':
            return 'px-3 py-1.5 text-xs font-semibold rounded-full';
        case 'lg':
            return 'px-6 py-3 text-base font-bold rounded-full';
        case 'md':
        default:
            return 'px-4 py-2 text-sm font-bold rounded-full';
    }
});
</script>

<template>
    <button
        :type="type"
        :disabled="disabled"
        class="inline-flex items-center justify-center border-1.5 transition-all duration-150 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed select-none"
        :class="[variantClasses, sizeClasses]"
    >
        <slot />
    </button>
</template>

