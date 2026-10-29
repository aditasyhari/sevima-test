<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import RetroButton from '@/Components/Atoms/RetroButton.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const institusi = computed(() => user.value?.institusi);
</script>

<template>
    <header class="bg-white border-b-2 border-neutral-900 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 gap-4">
                <!-- Brand / Logo -->
                <div class="flex items-center gap-3">
                    <Link href="/dashboard" class="flex items-center gap-2 group">
                        <div class="w-10 h-10 rounded-xl bg-retro-yellow border-1.5 border-neutral-900 flex items-center justify-center font-extrabold text-lg shadow-retro-sm group-hover:bg-yellow-400 transition-all font-retro">
                            S.
                        </div>
                        <div class="hidden sm:block">
                            <span class="font-extrabold text-lg text-neutral-900 tracking-tight font-retro">SEVIMA.</span>
                            <span class="block text-[10px] font-bold text-neutral-500 uppercase tracking-wider -mt-1">UKT Gateway</span>
                        </div>
                    </Link>

                    <!-- Active Tenant Badge (Crucial Multi-tenant Visual Proof) -->
                    <div v-if="institusi" class="hidden md:flex items-center gap-1.5 px-3 py-1 rounded-full bg-retro-bg border-1.5 border-neutral-900 shadow-retro-sm text-xs font-bold text-neutral-800">
                        <span>🏛️</span>
                        <span class="truncate max-w-[200px]">{{ institusi.nama }}</span>
                        <span class="px-1.5 py-0.2 rounded-full bg-neutral-900 text-white text-[10px] font-mono">
                            {{ institusi.kode }}
                        </span>
                    </div>
                </div>

                <!-- Center Nav Links -->
                <nav class="flex items-center gap-2">
                    <Link
                        v-if="user?.role === 'admin_keuangan'"
                        :href="route('admin.tagihan.index')"
                        class="px-3.5 py-1.5 rounded-full text-xs md:text-sm font-bold border-1.5 border-neutral-900 transition-all"
                        :class="route().current('admin.tagihan.*') ? 'bg-retro-yellow text-neutral-900 shadow-retro-sm' : 'bg-white text-neutral-700 hover:bg-neutral-50'"
                    >
                        📋 Tagihan UKT
                    </Link>

                    <Link
                        v-if="user?.role === 'mahasiswa'"
                        :href="route('mahasiswa.dashboard')"
                        class="px-3.5 py-1.5 rounded-full text-xs md:text-sm font-bold border-1.5 border-neutral-900 transition-all"
                        :class="route().current('mahasiswa.*') ? 'bg-retro-yellow text-neutral-900 shadow-retro-sm' : 'bg-white text-neutral-700 hover:bg-neutral-50'"
                    >
                        🎓 Status UKT
                    </Link>

                    <Link
                        :href="route('simulator.index')"
                        class="px-3.5 py-1.5 rounded-full text-xs md:text-sm font-bold border-1.5 border-neutral-900 transition-all"
                        :class="route().current('simulator.*') ? 'bg-retro-yellow text-neutral-900 shadow-retro-sm' : 'bg-white text-neutral-700 hover:bg-neutral-50'"
                    >
                        🛠️ Mock Gateway
                    </Link>
                </nav>

                <!-- User Profile & Logout -->
                <div class="flex items-center gap-3">
                    <div class="hidden lg:flex flex-col text-right">
                        <span class="text-xs font-bold text-neutral-900 truncate max-w-[140px]">{{ user?.name }}</span>
                        <span class="text-[10px] font-semibold text-neutral-500 uppercase">{{ user?.role === 'admin_keuangan' ? 'Admin Keuangan' : 'Mahasiswa' }}</span>
                    </div>

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="inline-flex items-center justify-center p-2 rounded-full border-1.5 border-neutral-900 bg-white hover:bg-neutral-100 shadow-retro-sm text-xs font-bold cursor-pointer"
                        title="Keluar"
                    >
                        🚪
                    </Link>
                </div>
            </div>
        </div>
    </header>
</template>

