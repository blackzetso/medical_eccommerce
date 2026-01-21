<?php

namespace App\Http\Requests\Sync;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'external_id' => ['required', 'uuid'],
            'source' => ['required', 'in:store,erp'],
            'occurred_at' => ['required', 'date'],
            'payload' => ['required', 'array'],
        ];
    }
}
