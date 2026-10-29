<script setup>
import { useForm } from '@inertiajs/vue3';
import RetroButton from '@/Components/Atoms/RetroButton.vue';
import RetroInput from '@/Components/Atoms/RetroInput.vue';
import RetroSelect from '@/Components/Atoms/RetroSelect.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    students: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['close']);

// Default due date: 14 days from now formatted for datetime-local
const defaultDueDate = () => {
    const d = new Date();
    d.setDate(d.getDate() + 14);
    d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
    return d.toISOString().slice(0, 16);
};

const form = useForm({
    mahasiswa_id: '',
    nomor_tagihan: 'TAG-' + Math.floor(10000 + Math.random() * 90000),
    semester: '2024/2025 Ganjil',
    nominal: 4500000,
    tanggal_kedaluwarsa: defaultDueDate(),
    catatan: '',
});

const submit = () => {
    form.post(route('admin.tagihan.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            form.nomor_tagihan = 'TAG-' + Math.floor(10000 + Math.random() * 90000);
            form.tanggal_kedaluwarsa = defaultDueDate();
            emit('close');
        },
    });
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-neutral-900/60 backdrop-blur-xs"
        @click.self="$emit('close')"
    >
        <div class="bg-white rounded-3xl border-2 border-neutral-900 shadow-retro-lg max-w-lg w-full overflow-hidden animate-in fade-in zoom-in-95 duration-150">
            <!-- Header -->
            <div class="p-5 border-b-1.5 border-neutral-900 bg-retro-yellow flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-extrabold text-neutral-900 font-retro">
                        ➕ Buat Tagihan UKT Baru
                    </h3>
                    <p class="text-xs text-neutral-800 font-medium">
                        Terbitkan tagihan semester untuk mahasiswa di institusi ini.
                    </p>
                </div>
                <button
                    type="button"
                    @click="$emit('close')"
                    class="w-8 h-8 rounded-full border-1.5 border-neutral-900 bg-white hover:bg-neutral-100 flex items-center justify-center font-bold text-sm shadow-retro-sm cursor-pointer"
                >
                    ✕
                </button>
            </div>

            <!-- Form Body -->
            <form @submit.prevent="submit" class="p-6 space-y-4">
                <!-- Mahasiswa -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1">
                        Pilih Mahasiswa *
                    </label>
                    <RetroSelect v-model="form.mahasiswa_id" :error="form.errors.mahasiswa_id">
                        <option value="" disabled>-- Pilih Mahasiswa --</option>
                        <option v-for="std in students" :key="std.id" :value="std.id">
                            {{ std.name }} (NIM: {{ std.nim || '-' }} • {{ std.prodi || 'Reguler' }})
                        </option>
                    </RetroSelect>
                </div>

                <!-- Nomor Tagihan & Semester -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1">
                            Nomor Tagihan *
                        </label>
                        <RetroInput
                            v-model="form.nomor_tagihan"
                            placeholder="Contoh: TAG-2024-001"
                            :error="form.errors.nomor_tagihan"
                        />
                        <span class="text-[11px] text-neutral-500 mt-0.5 block">Unik per institusi</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1">
                            Semester *
                        </label>
                        <RetroInput
                            v-model="form.semester"
                            placeholder="Contoh: 2024/2025 Ganjil"
                            :error="form.errors.semester"
                        />
                    </div>
                </div>

                <!-- Nominal & Tanggal Kedaluwarsa -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1">
                            Nominal (Rp) *
                        </label>
                        <RetroInput
                            type="number"
                            v-model="form.nominal"
                            placeholder="4500000"
                            :error="form.errors.nominal"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1">
                            Batas Kedaluwarsa *
                        </label>
                        <RetroInput
                            type="datetime-local"
                            v-model="form.tanggal_kedaluwarsa"
                            :error="form.errors.tanggal_kedaluwarsa"
                        />
                    </div>
                </div>

                <!-- Catatan -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1">
                        Catatan / Keterangan (Opsional)
                    </label>
                    <RetroInput
                        v-model="form.catatan"
                        placeholder="Contoh: UKT Golongan 3"
                        :error="form.errors.catatan"
                    />
                </div>

                <!-- Modal Actions -->
                <div class="pt-3 border-t border-neutral-200 flex items-center justify-end gap-3">
                    <RetroButton
                        type="button"
                        variant="secondary"
                        @click="$emit('close')"
                    >
                        Batal
                    </RetroButton>
                    <RetroButton
                        type="submit"
                        variant="primary"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Menyimpan...' : '💾 Simpan & Terbitkan' }}
                    </RetroButton>
                </div>
            </form>
        </div>
    </div>
</template>
