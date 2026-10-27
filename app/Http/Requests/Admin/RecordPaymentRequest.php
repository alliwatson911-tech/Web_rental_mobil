<?php

namespace App\Http\Requests\Admin;

use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:1'],
            'method' => ['required', Rule::in(array_keys(Payment::METHODS))],
            'paid_at' => ['required', 'date'],
            'sender_name' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
            'proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:'.config('padaidi.max_upload_kb')],
        ];
    }

    public function attributes(): array
    {
        return ['amount' => 'jumlah', 'method' => 'metode', 'paid_at' => 'tanggal bayar', 'sender_name' => 'nama pengirim', 'notes' => 'catatan', 'proof' => 'bukti'];
    }
}
