<?php

namespace App\Http\Requests\Sync;

class StockUpdateEventRequest extends BaseEventRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'payload.product_id' => ['nullable', 'string'],
            'payload.remote_product_id' => ['nullable', 'string'],
            'payload.product_code' => ['nullable', 'string', 'max:255'],
            'payload.variant_id' => ['nullable', 'string'],
            'payload.remote_variant_id' => ['nullable', 'string'],
            'payload.stock.available' => ['required', 'integer'],
            'payload.stock.reserved' => ['nullable', 'integer'],
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $hasVariant = $this->input('payload.variant_id') || $this->input('payload.remote_variant_id');

            if (!$hasVariant) {
                $validator->errors()->add('payload.variant_id', 'Either variant_id or remote_variant_id is required.');
            }
        });
    }
}
