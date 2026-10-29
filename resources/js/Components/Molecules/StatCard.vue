<script setup>
import RupiahFormat from '@/Components/Atoms/RupiahFormat.vue';

defineProps({
    title: {
        type: String,
        required: true,
    },
    value: {
        type: [Number, String],
        required: true,
    },
    isCurrency: {
        type: Boolean,
        default: false,
    },
    subtitle: {
        type: String,
        default: '',
    },
    variant: {
        type: String,
        default: 'default', // 'default', 'yellow', 'green', 'coral'
    },
    icon: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div
        class="p-5 rounded-2xl border-1.5 border-neutral-900 shadow-retro transition-all flex flex-col justify-between"
        :class="[
            variant === 'yellow' ? 'bg-retro-yellow-light/60' : '',
            variant === 'green' ? 'bg-retro-green-light/60' : '',
            variant === 'coral' ? 'bg-retro-coral-light/60' : '',
            variant === 'default' ? 'bg-white' : '',
        ]"
    >
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold uppercase tracking-wider text-neutral-600">
                {{ title }}
            </span>
            <span v-if="icon" class="text-xl p-2 rounded-xl border-1.5 border-neutral-900 bg-white shadow-retro-sm">
                {{ icon }}
            </span>
        </div>

        <div>
            <div class="text-2xl font-extrabold text-neutral-900 font-retro">
                <RupiahFormat v-if="isCurrency" :value="value" />
                <span v-else>{{ value }}</span>
            </div>
            <p v-if="subtitle" class="text-xs font-medium text-neutral-500 mt-1">
                {{ subtitle }}
            </p>
        </div>
    </div>
</template>

