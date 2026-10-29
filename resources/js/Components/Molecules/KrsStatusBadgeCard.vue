<script setup>
defineProps({
    isEligible: {
        type: Boolean,
        required: true,
    },
    label: {
        type: String,
        default: '',
    },
    description: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div
        class="p-5 rounded-2xl border-1.5 border-neutral-900 shadow-retro transition-all"
        :class="isEligible ? 'bg-retro-green-light' : 'bg-retro-yellow-light'"
    >
        <div class="flex items-start gap-4">
            <div
                class="w-12 h-12 rounded-2xl border-1.5 border-neutral-900 flex items-center justify-center text-2xl shadow-retro-sm shrink-0"
                :class="isEligible ? 'bg-retro-green text-white' : 'bg-retro-yellow text-neutral-900'"
            >
                <span v-if="isEligible">🎓</span>
                <span v-else>🔒</span>
            </div>

            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-bold uppercase tracking-wider text-neutral-600">
                        Status Akademik & KRS
                    </span>
                    <span
                        class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold uppercase border-1.5"
                        :class="isEligible
                            ? 'bg-retro-green text-white border-neutral-900'
                            : 'bg-retro-yellow text-neutral-900 border-neutral-900'"
                    >
                        {{ label || (isEligible ? 'SIAP ISI KRS' : 'KRS TERKUNCI') }}
                    </span>
                </div>

                <h4 class="text-base font-bold text-neutral-900 font-retro">
                    {{ isEligible ? 'Akses KRS Terbuka Penuh' : 'Pengisian KRS Belum Aktif' }}
                </h4>

                <p class="text-xs text-neutral-700 mt-1 leading-relaxed">
                    {{ description || (isEligible
                        ? 'Tagihan UKT Anda telah lunas terverifikasi oleh Payment Gateway. Silakan lanjutkan pengisian mata kuliah pada portal KRS kampus.'
                        : 'Selesaikan pembayaran tagihan UKT aktif Anda untuk membuka gembok pengisian Kartu Rencana Studi.') }}
                </p>
            </div>
        </div>
    </div>
</template>
