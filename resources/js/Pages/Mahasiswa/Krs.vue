<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import RetroNavbar from '@/Components/Organisms/RetroNavbar.vue';
import RetroButton from '@/Components/Atoms/RetroButton.vue';

const props = defineProps({
    mahasiswa: Object,
    krs_status: Object,
    courses: Array,
});

// Selected course IDs state
const selectedCourseIds = ref(
    (props.courses || []).filter(c => c.selected).map(c => c.id)
);

const isSubmitted = ref(false);
const showSuccessToast = ref(false);

const toggleCourse = (courseId) => {
    if (selectedCourseIds.value.includes(courseId)) {
        selectedCourseIds.value = selectedCourseIds.value.filter(id => id !== courseId);
    } else {
        const candidateCourse = props.courses.find(c => c.id === courseId);
        if (totalSks.value + (candidateCourse?.sks || 0) > props.krs_status.total_sks_max) {
            alert(`Maksimal pengambilan SKS semester ini adalah ${props.krs_status.total_sks_max} SKS.`);
            return;
        }
        selectedCourseIds.value.push(courseId);
    }
};

const totalSks = computed(() => {
    return (props.courses || [])
        .filter(c => selectedCourseIds.value.includes(c.id))
        .reduce((sum, c) => sum + (c.sks || 0), 0);
});

const submitKrs = () => {
    if (selectedCourseIds.value.length === 0) {
        alert('Silakan pilih minimal 1 mata kuliah untuk diajukan.');
        return;
    }
    isSubmitted.value = true;
    showSuccessToast.value = true;
    setTimeout(() => {
        showSuccessToast.value = false;
    }, 4000);
};

const printKrs = () => {
    window.print();
};
</script>

<template>
    <Head title="Kartu Rencana Studi (KRS) - Portal Akademik" />

    <div class="min-h-screen bg-retro-bg flex flex-col print:bg-white">
        <!-- Persistent Retro Navbar (hidden on print) -->
        <div class="print:hidden">
            <RetroNavbar />
        </div>

        <!-- Main Content -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Breadcrumb Navigation -->
            <div class="mb-6 flex items-center justify-between flex-wrap gap-3 print:hidden">
                <div class="flex items-center gap-2 text-xs font-bold text-neutral-600">
                    <Link :href="route('mahasiswa.dashboard')" class="hover:text-neutral-900 transition-colors">
                        🏠 Dashboard Mahasiswa
                    </Link>
                    <span>/</span>
                    <span class="text-neutral-900 font-extrabold font-retro">Kartu Rencana Studi (KRS)</span>
                </div>

                <Link
                    :href="route('mahasiswa.dashboard')"
                    class="inline-flex items-center gap-1.5 text-xs font-bold text-neutral-700 hover:text-neutral-900 bg-white border-1.5 border-neutral-900 px-3 py-1.5 rounded-xl shadow-retro-sm transition-all"
                >
                    <span>←</span>
                    <span>Kembali ke Status UKT</span>
                </Link>
            </div>

            <!-- Student Academic Header Card -->
            <div class="bg-white rounded-3xl border-2 border-neutral-900 shadow-retro p-6 sm:p-7 mb-8 relative overflow-hidden">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-retro-yellow border-2 border-neutral-900 flex items-center justify-center text-2xl shadow-retro-sm shrink-0 font-retro">
                            📝
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h1 class="text-xl sm:text-2xl font-extrabold text-neutral-900 font-retro">
                                    Kartu Rencana Studi (KRS)
                                </h1>
                                <span class="px-2.5 py-0.5 rounded-full bg-neutral-900 text-white text-xs font-mono font-bold">
                                    {{ mahasiswa.nim }}
                                </span>
                            </div>
                            <p class="text-xs sm:text-sm text-neutral-600 font-medium mt-1">
                                {{ mahasiswa.name }} • {{ mahasiswa.prodi }} • Semester {{ mahasiswa.semester_aktif }}
                            </p>
                            <p class="text-[11px] text-neutral-500 font-medium mt-0.5">
                                Dosen Pembimbing Akademik: <strong class="text-neutral-800">{{ mahasiswa.dosen_pa }}</strong>
                            </p>
                        </div>
                    </div>

                    <!-- Payment Verification Gating Badge -->
                    <div class="flex flex-col items-start md:items-end gap-1.5">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-neutral-500">
                            Status Kelayakan UKT (Gating Access)
                        </span>
                        <div
                            class="px-3.5 py-1.5 rounded-full text-xs font-extrabold border-2 flex items-center gap-2 shadow-retro-sm"
                            :class="krs_status.is_eligible
                                ? 'bg-retro-green-light text-retro-green-dark border-neutral-900'
                                : 'bg-retro-coral-light text-retro-coral-dark border-neutral-900'"
                        >
                            <span v-if="krs_status.is_eligible">✅ UKT Lunas - Akses KRS Terbuka</span>
                            <span v-else>🔒 UKT Belum Lunas - Akses Terkunci</span>
                        </div>
                        <span class="text-[10px] text-neutral-500 font-mono">
                            Semester: {{ krs_status.active_semester }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- SCENARIO 1: KRS IS LOCKED (UNPAID UKT) -->
            <div
                v-if="!krs_status.is_eligible"
                class="bg-white rounded-3xl border-2 border-neutral-900 shadow-retro-lg p-8 sm:p-12 text-center max-w-2xl mx-auto"
            >
                <div class="w-20 h-20 rounded-3xl bg-retro-coral-light border-2 border-neutral-900 mx-auto flex items-center justify-center text-4xl shadow-retro mb-6">
                    🔒
                </div>

                <h2 class="text-2xl font-extrabold text-neutral-900 font-retro mb-3">
                    Akses Pengisian KRS Terkunci!
                </h2>

                <p class="text-sm text-neutral-700 leading-relaxed max-w-md mx-auto mb-6">
                    Berdasarkan Peraturan Akademik Universitas, mahasiswa wajib melunasi tagihan <strong>Uang Kuliah Tunggal (UKT)</strong> semester aktif sebelum dapat menyusun atau memvalidasi Kartu Rencana Studi (KRS).
                </p>

                <!-- Current Bill Status Notice Box -->
                <div class="bg-neutral-50 rounded-2xl border-1.5 border-neutral-900 p-4 max-w-md mx-auto mb-8 text-left text-xs space-y-1.5">
                    <div class="flex justify-between">
                        <span class="text-neutral-500 font-bold">Nomor Tagihan:</span>
                        <span class="font-mono font-bold text-neutral-900">{{ krs_status.bill_number || '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-500 font-bold">Status Pembayaran:</span>
                        <span class="uppercase font-bold text-retro-coral-dark">{{ krs_status.bill_status }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-500 font-bold">Hak Akses KRS:</span>
                        <span class="font-extrabold text-neutral-900">TERTANGGUH</span>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                    <Link
                        :href="route('mahasiswa.dashboard')"
                        class="w-full sm:w-auto px-6 py-2.5 rounded-xl border-1.5 border-neutral-900 bg-retro-yellow hover:bg-yellow-400 text-neutral-900 font-bold text-sm shadow-retro transition-all cursor-pointer"
                    >
                        💳 Lihat Tagihan & Cara Bayar
                    </Link>

                    <Link
                        :href="route('simulator.index')"
                        class="w-full sm:w-auto px-6 py-2.5 rounded-xl border-1.5 border-neutral-900 bg-white hover:bg-neutral-100 text-neutral-900 font-bold text-sm shadow-retro-sm transition-all cursor-pointer"
                    >
                        🧪 Uji Pelunasan di Mock Gateway
                    </Link>
                </div>
            </div>

            <!-- SCENARIO 2: KRS IS ELIGIBLE (UKT IS PAID) -->
            <div v-else class="space-y-6">
                <!-- Toast Notification when submitted -->
                <div
                    v-if="showSuccessToast"
                    class="p-4 rounded-2xl bg-retro-green-light border-2 border-neutral-900 shadow-retro flex items-center justify-between gap-3 animate-in fade-in slide-in-from-top-4 duration-200"
                >
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">🎉</span>
                        <div>
                            <strong class="text-sm font-extrabold text-retro-green-dark block font-retro">
                                KRS Semester 2024/2025 Ganjil Berhasil Diajukan!
                            </strong>
                            <span class="text-xs text-neutral-700">
                                Berkas KRS Anda telah diteruskan ke Dosen Pembimbing Akademik (Dr. Eng. Supriyanto) untuk validasi.
                            </span>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="showSuccessToast = false"
                        class="w-7 h-7 rounded-full border-1.5 border-neutral-900 bg-white flex items-center justify-center font-bold text-xs shadow-retro-sm cursor-pointer"
                    >
                        ✕
                    </button>
                </div>

                <!-- Academic Stats Summary Bar -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white p-5 rounded-2xl border-2 border-neutral-900 shadow-retro">
                        <span class="text-xs font-bold text-neutral-500 uppercase tracking-wider block mb-1">
                            Total SKS Diambil
                        </span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl font-extrabold text-neutral-900 font-retro">
                                {{ totalSks }}
                            </span>
                            <span class="text-xs font-bold text-neutral-500">
                                / {{ krs_status.total_sks_max }} SKS Maksimal
                            </span>
                        </div>
                        <div class="w-full bg-neutral-100 h-2 rounded-full border border-neutral-900 overflow-hidden mt-3">
                            <div
                                class="h-full bg-retro-green transition-all duration-300"
                                :style="{ width: `${Math.min(100, (totalSks / krs_status.total_sks_max) * 100)}%` }"
                            ></div>
                        </div>
                    </div>

                    <div class="bg-white p-5 rounded-2xl border-2 border-neutral-900 shadow-retro">
                        <span class="text-xs font-bold text-neutral-500 uppercase tracking-wider block mb-1">
                            Mata Kuliah Dipilih
                        </span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl font-extrabold text-neutral-900 font-retro">
                                {{ selectedCourseIds.length }}
                            </span>
                            <span class="text-xs font-bold text-neutral-500">
                                dari {{ courses?.length || 0 }} Mata Kuliah Ditawarkan
                            </span>
                        </div>
                        <span class="text-[11px] font-bold text-retro-green-dark mt-2 block">
                            ✓ Syarat Prasyarat Semester 5 Terpenuhi
                        </span>
                    </div>

                    <div class="bg-white p-5 rounded-2xl border-2 border-neutral-900 shadow-retro">
                        <span class="text-xs font-bold text-neutral-500 uppercase tracking-wider block mb-1">
                            Status Pengajuan KRS
                        </span>
                        <div class="flex items-center gap-2 mt-1">
                            <span
                                class="px-3 py-1 rounded-full text-xs font-extrabold border-1.5 border-neutral-900 shadow-retro-sm"
                                :class="isSubmitted
                                    ? 'bg-retro-green-light text-retro-green-dark'
                                    : 'bg-retro-yellow-light text-neutral-900'"
                            >
                                {{ isSubmitted ? '✅ DIAJUKAN KE DPA' : '✏️ DRAFT (SIAP DIAJUKAN)' }}
                            </span>
                        </div>
                        <span class="text-[11px] text-neutral-500 font-medium mt-2 block">
                            Batas Revisi: 15 November 2026
                        </span>
                    </div>
                </div>

                <!-- Course Selection Table (Rencana Studi) -->
                <div class="bg-white rounded-3xl border-2 border-neutral-900 shadow-retro p-6 sm:p-7">
                    <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
                        <div>
                            <h3 class="text-lg font-extrabold text-neutral-900 font-retro flex items-center gap-2">
                                <span>📚</span>
                                <span>Daftar Mata Kuliah Semester 5 (2024/2025 Ganjil)</span>
                            </h3>
                            <p class="text-xs text-neutral-600 mt-0.5">
                                Centang mata kuliah yang ingin Anda ambil pada semester ini.
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-2 print:hidden">
                            <button
                                type="button"
                                @click="printKrs"
                                class="px-3.5 py-1.5 rounded-xl border-1.5 border-neutral-900 bg-neutral-100 hover:bg-neutral-200 text-xs font-bold text-neutral-800 shadow-retro-sm transition-all cursor-pointer flex items-center gap-1.5"
                            >
                                <span>🖨️</span>
                                <span>Cetak KRS</span>
                            </button>

                            <button
                                type="button"
                                @click="submitKrs"
                                class="px-4 py-1.5 rounded-xl border-1.5 border-neutral-900 bg-retro-green hover:bg-green-400 text-white font-bold text-xs shadow-retro transition-all cursor-pointer flex items-center gap-1.5"
                            >
                                <span>🚀</span>
                                <span>{{ isSubmitted ? 'Simpan Pembaruan KRS' : 'Ajukan KRS ke DPA' }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Table Container -->
                    <div class="overflow-x-auto border-1.5 border-neutral-900 rounded-2xl">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-neutral-100 border-b-1.5 border-neutral-900 font-bold uppercase text-neutral-700 tracking-wider">
                                <tr>
                                    <th class="p-3 text-center w-12 print:hidden">Ambil</th>
                                    <th class="p-3">Kode</th>
                                    <th class="p-3">Mata Kuliah</th>
                                    <th class="p-3 text-center">SKS</th>
                                    <th class="p-3">Kelas / Ruang</th>
                                    <th class="p-3">Jadwal Kuliah</th>
                                    <th class="p-3">Dosen Pengampu</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-200">
                                <tr
                                    v-for="course in courses"
                                    :key="course.id"
                                    class="hover:bg-neutral-50 transition-colors"
                                    :class="selectedCourseIds.includes(course.id) ? 'bg-retro-green-light/20' : 'bg-white opacity-70'"
                                >
                                    <td class="p-3 text-center print:hidden">
                                        <input
                                            type="checkbox"
                                            :checked="selectedCourseIds.includes(course.id)"
                                            @change="toggleCourse(course.id)"
                                            class="w-4 h-4 rounded border-1.5 border-neutral-900 text-neutral-900 focus:ring-0 cursor-pointer"
                                        />
                                    </td>
                                    <td class="p-3 font-mono font-bold text-neutral-900">
                                        {{ course.kode }}
                                    </td>
                                    <td class="p-3 font-bold text-neutral-900">
                                        {{ course.nama }}
                                    </td>
                                    <td class="p-3 text-center font-extrabold text-neutral-900">
                                        {{ course.sks }}
                                    </td>
                                    <td class="p-3 font-medium text-neutral-700">
                                        Kelas {{ course.kelas }} • {{ course.ruang }}
                                    </td>
                                    <td class="p-3 font-medium text-neutral-700">
                                        {{ course.hari }}, {{ course.jam }}
                                    </td>
                                    <td class="p-3 text-neutral-600">
                                        {{ course.dosen }}
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-neutral-100 border-t-1.5 border-neutral-900 font-extrabold">
                                <tr>
                                    <td colspan="3" class="p-3 text-right text-neutral-900">
                                        TOTAL BEBAN STUDI:
                                    </td>
                                    <td class="p-3 text-center text-sm text-neutral-900 font-mono">
                                        {{ totalSks }} SKS
                                    </td>
                                    <td colspan="3" class="p-3 text-neutral-600 text-[11px] font-normal">
                                        * Status: Pembayaran UKT Lunas terverifikasi secara sistemik via Payment Gateway.
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Approval Signature Footer (visible on print) -->
                    <div class="hidden print:grid grid-cols-2 gap-8 mt-12 pt-6 border-t-2 border-neutral-900 text-center text-xs">
                        <div>
                            <p class="font-bold">Mahasiswa Yang Bersangkutan,</p>
                            <div class="h-20"></div>
                            <p class="font-extrabold underline">{{ mahasiswa.name }}</p>
                            <p class="font-mono">NIM. {{ mahasiswa.nim }}</p>
                        </div>
                        <div>
                            <p class="font-bold">Dosen Pembimbing Akademik,</p>
                            <div class="h-20"></div>
                            <p class="font-extrabold underline">{{ mahasiswa.dosen_pa }}</p>
                            <p class="text-neutral-600">Status: Disetujui Otomatis</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

