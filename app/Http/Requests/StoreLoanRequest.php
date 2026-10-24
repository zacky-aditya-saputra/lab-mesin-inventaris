<?php

namespace App\Http\Requests;

use App\Models\Tool;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreLoanRequest extends FormRequest
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
            'laboratory_id' => ['required', 'exists:laboratories,id'],
            'loan_letter' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'identity_card_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'start_date' => ['required', 'date', 'after_or_equal:'.now()->addDays(2)->toDateString()],
            'end_date' => ['required', 'date', 'after_or_equal:start_date', 'before_or_equal:'.$this->resolveMaximumEndDate()],
            'purpose' => ['required', 'string', 'max:1000'],
            'integrity_pledge' => ['sometimes', 'accepted'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.tool_id' => ['required', 'distinct', 'exists:tools,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
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
            'loan_letter.required' => 'Surat peminjaman wajib diunggah.',
            'start_date.after_or_equal' => 'Tanggal mulai peminjaman minimal diajukan 2 hari sebelum pemakaian (H-2) untuk persiapan alat oleh petugas laboratorium.',
            'items.*.tool_id.distinct' => 'Terdapat alat yang sama dipilih lebih dari satu kali. Harap gabungkan kuantitasnya dalam satu baris.',
            'integrity_pledge.accepted' => 'Anda wajib menyetujui pernyataan kebenaran data dan kepatuhan tata tertib laboratorium.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'loan_letter' => 'surat peminjaman',
        ];
    }

    /**
     * Configure the validator instance to check the tool-laboratory match and the dynamic stock availability.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $items = $this->input('items');

            if (! is_array($items)) {
                return;
            }

            $laboratoryId = $this->integer('laboratory_id');

            if ($laboratoryId <= 0) {
                return;
            }

            $tools = Tool::query()
                ->whereIn('id', collect($items)->pluck('tool_id')->filter()->unique())
                ->get()
                ->keyBy('id');

            foreach ($items as $index => $item) {
                if (! is_array($item)) {
                    continue;
                }

                $tool = $tools->get($item['tool_id'] ?? null);

                if (! $tool instanceof Tool) {
                    continue;
                }

                if ($tool->laboratory_id !== $laboratoryId) {
                    $validator->errors()->add(
                        "items.{$index}.tool_id",
                        "Alat {$tool->name} bukan milik laboratorium tujuan yang dipilih."
                    );

                    continue;
                }

                $quantity = (int) ($item['quantity'] ?? 0);

                if ($quantity > $tool->available_stock) {
                    $validator->errors()->add(
                        "items.{$index}.quantity",
                        "Jumlah pinjam untuk alat {$tool->name} melebihi stok tersedia ({$tool->available_stock} unit)."
                    );
                }
            }
        });
    }

    /**
     * Resolve the latest allowed return date: start date plus fourteen days.
     */
    private function resolveMaximumEndDate(): string
    {
        $startDate = $this->input('start_date');

        if (! is_string($startDate) || $startDate === '') {
            return now()->addDays(14)->format('Y-m-d');
        }

        try {
            return now()->parse($startDate)->addDays(14)->format('Y-m-d');
        } catch (InvalidFormatException) {
            return now()->addDays(14)->format('Y-m-d');
        }
    }
}
