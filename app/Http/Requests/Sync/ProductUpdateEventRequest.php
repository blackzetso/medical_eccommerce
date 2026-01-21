<?php

namespace App\Http\Requests\Sync;

class ProductUpdateEventRequest extends BaseEventRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'payload.product_id' => ['nullable', 'string'],
            'payload.remote_product_id' => ['nullable', 'string'],
            'payload.product_code' => ['nullable', 'string', 'max:255'],
            'payload.name' => ['required', 'string', 'max:255'],
            'payload.description' => ['nullable', 'string'],
            'payload.status' => ['nullable', 'in:active,inactive'],
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $hasLocal = (bool) $this->input('payload.product_id');
            $hasRemote = (bool) $this->input('payload.remote_product_id');
            $hasCode = (bool) $this->input('payload.product_code');

            if (!($hasLocal || $hasRemote || $hasCode)) {
                $validator->errors()->add('payload.product_id', 'Provide at least one of product_id, remote_product_id, or product_code.');
            }
        });
    }
}
