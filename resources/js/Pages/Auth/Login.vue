<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import RetroButton from '@/Components/Atoms/RetroButton.vue';
import RetroInput from '@/Components/Atoms/RetroInput.vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const fillDemo = (email, password = 'password') => {
    form.email = email;
    form.password = password;
    submit();
};
</script>

<template>
    <Head title="Masuk - SEVIMA UKT Gateway" />

    <div class="min-h-screen bg-retro-bg flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-retro-yellow border-2 border-neutral-900 shadow-retro mb-4 font-retro text-2xl font-black">
                S.
            </div>
            <h2 class="text-3xl font-extrabold text-neutral-900 tracking-tight font-retro">
                SEVIMA UKT Gateway
            </h2>
            <p class="mt-1 text-xs text-neutral-600 font-medium">
                Sistem Penagihan UKT & Integrasi Webhook Multi-Kampus
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4">
            <!-- Main Login Card -->
            <div class="bg-white py-8 px-6 sm:px-10 rounded-3xl border-2 border-neutral-900 shadow-retro-lg">
                <div v-if="status" class="mb-4 font-medium text-xs text-retro-green">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label for="email" class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1">
                            Email Kampus / Akun
                        </label>
                        <RetroInput
                            id="email"
                            type="email"
                            v-model="form.email"
                            placeholder="nama@sevima.test"
                            :error="form.errors.email"
                            required
                            autofocus
                        />
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1">
                            Kata Sandi
                        </label>
                        <RetroInput
                            id="password"
                            type="password"
                            v-model="form.password"
                            placeholder="••••••••"
                            :error="form.errors.password"
                            required
                        />
                    </div>

                    <div class="pt-2">
                        <RetroButton
                            type="submit"
                            variant="primary"
                            class="w-full"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Memproses...' : 'Masuk ke Portal 🚀' }}
                        </RetroButton>
                    </div>
                </form>

                <!-- Quick Demo Login Section (Huge Reviewer Value) -->
                <div class="mt-8 pt-6 border-t-1.5 border-neutral-200">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-neutral-500 block mb-3 text-center">
                        ⚡ Quick Demo Login (Satu Klik untuk Reviewer)
                    </span>

                    <div class="space-y-2">
                        <div class="text-[10px] font-bold text-neutral-400 uppercase">Peran Admin Keuangan:</div>
                        <div class="grid grid-cols-3 gap-2">
                            <button
                                type="button"
                                @click="fillDemo('admin.univa@sevima.test')"
                                class="px-2 py-1.5 rounded-xl border-1.5 border-neutral-900 bg-neutral-50 hover:bg-retro-yellow text-[11px] font-bold transition-all shadow-retro-sm cursor-pointer"
                            >
                                🏛️ Univ A
                            </button>
                            <button
                                type="button"
                                @click="fillDemo('admin.univb@sevima.test')"
                                class="px-2 py-1.5 rounded-xl border-1.5 border-neutral-900 bg-neutral-50 hover:bg-retro-yellow text-[11px] font-bold transition-all shadow-retro-sm cursor-pointer"
                            >
                                🏛️ Univ B
                            </button>
                            <button
                                type="button"
                                @click="fillDemo('admin.univc@sevima.test')"
                                class="px-2 py-1.5 rounded-xl border-1.5 border-neutral-900 bg-neutral-50 hover:bg-retro-yellow text-[11px] font-bold transition-all shadow-retro-sm cursor-pointer"
                            >
                                🏛️ Univ C
                            </button>
                        </div>

                        <div class="text-[10px] font-bold text-neutral-400 uppercase pt-2">Peran Mahasiswa:</div>
                        <div class="grid grid-cols-3 gap-2">
                            <button
                                type="button"
                                @click="fillDemo('budi.univa@sevima.test')"
                                class="px-2 py-1.5 rounded-xl border-1.5 border-neutral-900 bg-neutral-50 hover:bg-retro-green-light text-[11px] font-bold transition-all shadow-retro-sm cursor-pointer"
                            >
                                🎓 Budi (A)
                            </button>
                            <button
                                type="button"
                                @click="fillDemo('andi.univb@sevima.test')"
                                class="px-2 py-1.5 rounded-xl border-1.5 border-neutral-900 bg-neutral-50 hover:bg-retro-green-light text-[11px] font-bold transition-all shadow-retro-sm cursor-pointer"
                            >
                                🎓 Andi (B)
                            </button>
                            <button
                                type="button"
                                @click="fillDemo('maya.univc@sevima.test')"
                                class="px-2 py-1.5 rounded-xl border-1.5 border-neutral-900 bg-neutral-50 hover:bg-retro-green-light text-[11px] font-bold transition-all shadow-retro-sm cursor-pointer"
                            >
                                🎓 Maya (C)
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Simulator Link -->
                <div class="mt-6 pt-4 border-t border-neutral-100 text-center">
                    <Link
                        :href="route('simulator.index')"
                        class="text-xs font-bold text-neutral-700 hover:text-neutral-900 underline"
                    >
                        Buka Mock Payment Gateway Simulator ➔
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
