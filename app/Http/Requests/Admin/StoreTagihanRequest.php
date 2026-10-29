<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTagihanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdminKeuangan();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $institusiId = auth()->user()->institusi_id;

        return [
            'mahasiswa_id' => [
                'required',
                'uuid',
                Rule::exists('users', 'id')->where(function ($query) use ($institusiId) {
                    $query->where('institusi_id', $institusiId)->where('role', 'mahasiswa');
                }),
            ],
            'nomor_tagihan' => [
                'required',
                'string',
                'max:100',
                Rule::unique('tagihans', 'nomor_tagihan')->where(function ($query) use ($institusiId) {
                    $query->where('institusi_id', $institusiId);
                }),
            ],
            'semester' => ['required', 'string', 'max:50'],
            'nominal' => ['required', 'numeric', 'min:10000'],
            'tanggal_kedaluwarsa' => ['required', 'date', 'after:now'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'mahasiswa_id.exists' => 'Mahasiswa yang dipilih tidak terdaftar di institusi Anda.',
            'nomor_tagihan.unique' => 'Nomor tagihan sudah digunakan di institusi ini.',
            'tanggal_kedaluwarsa.after' => 'Tanggal kedaluwarsa harus setelah waktu sekarang.',
        ];
    }
}
