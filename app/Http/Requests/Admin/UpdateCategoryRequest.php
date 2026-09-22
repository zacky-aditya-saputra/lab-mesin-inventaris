<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($this->route('category')),
            ],
            'description' => ['nullable', 'string'],
            'code' => [
                'required',
                'string',
                'max:10',
                'uppercase',
                Rule::unique('categories', 'code')->ignore($this->route('category')),
            ],
        ];
    }

    /**
     * Get the custom error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Kode singkatan kategori wajib diisi.',
            'code.uppercase' => 'Kode singkatan kategori harus berupa huruf kapital.',
            'code.unique' => 'Kode singkatan kategori sudah digunakan.',
            'code.max' => 'Kode singkatan kategori maksimal 10 karakter.',
        ];
    }
}
