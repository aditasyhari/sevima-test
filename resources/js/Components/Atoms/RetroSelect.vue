<script setup>
defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    error: {
        type: String,
        default: '',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    id: {
        type: String,
        default: '',
    },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <div class="w-full">
        <select
            :id="id"
            :value="modelValue"
            :disabled="disabled"
            @change="$emit('update:modelValue', $event.target.value)"
            class="w-full px-4 py-2.5 bg-white text-neutral-900 font-medium text-sm rounded-xl border-1.5 border-neutral-900 focus:outline-none focus:ring-2 focus:ring-retro-yellow focus:border-neutral-900 shadow-retro-sm transition-all disabled:opacity-60 disabled:bg-neutral-100"
            :class="{ 'border-retro-coral focus:ring-red-300': error }"
        >
            <slot />
        </select>
        <p v-if="error" class="mt-1 text-xs font-semibold text-retro-coral">
            {{ error }}
        </p>
    </div>
</template>

