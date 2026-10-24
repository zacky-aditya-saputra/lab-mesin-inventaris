<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isLabStaff = $this->user()->isLaboran() || $this->user()->isKepalaLab();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'identity_type' => ['nullable', Rule::in($isLabStaff ? ['pegawai'] : ['mahasiswa', 'umum'])],
            'identity_number' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique(User::class, 'identity_number')->ignore($this->user()->id),
            ],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'institution' => ['nullable', 'string', 'max:255'],
            'identity_card_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap tidak boleh lebih dari 255 karakter.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email ini sudah digunakan oleh akun lain.',
            'identity_number.unique' => 'Nomor identitas (NIM/NIK) ini sudah digunakan oleh akun lain.',
            'identity_number.max' => 'Nomor identitas maksimal 30 karakter.',
            'identity_card_file.mimes' => 'Berkas kartu identitas harus berformat JPG, JPEG, PNG, WEBP, atau PDF.',
            'identity_card_file.max' => 'Ukuran berkas kartu identitas tidak boleh melebihi 5MB.',
        ];
    }
}
