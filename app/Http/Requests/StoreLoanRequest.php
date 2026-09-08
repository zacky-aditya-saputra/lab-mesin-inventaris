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
            'client_type' => ['required', 'in:mahasiswa,umum'],
            'affiliation_type' => ['nullable', 'in:instansi,individu'],
            'borrower_name' => ['required', 'string', 'max:255'],
            'borrower_phone' => ['required', 'string', 'max:30'],
            'borrower_identity' => ['required', 'string', 'min:8', 'max:30'],
            'institution' => ['required', 'string', 'max:150'],
            'identity_card' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date', 'before_or_equal:'.$this->resolveMaximumEndDate()],
            'purpose' => ['required', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.tool_id' => ['required', 'distinct', 'exists:tools,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Configure the validator instance to check the dynamic stock availability.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $items = $this->input('items');

            if (! is_array($items)) {
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
