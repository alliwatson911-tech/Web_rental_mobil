<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:1000'],
            'sender_name' => ['required', 'string', 'max:100'],
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:'.config('padaidi.max_upload_kb')],
        ];
    }

    public function attributes(): array
    {
        return ['amount' => 'jumlah transfer', 'sender_name' => 'nama pengirim', 'proof' => 'bukti transfer'];
    }
}
