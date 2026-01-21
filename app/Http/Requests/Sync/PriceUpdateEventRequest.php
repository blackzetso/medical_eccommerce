<?php

namespace App\Http\Requests\Sync;

class PriceUpdateEventRequest extends BaseEventRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'payload.product_id' => ['nullable', 'string'],
            'payload.remote_product_id' => ['nullable', 'string'],
            'payload.product_code' => ['nullable', 'string', 'max:255'],
            'payload.variant_id' => ['nullable', 'string'],
            'payload.remote_variant_id' => ['nullable', 'string'],
            'payload.price.amount' => ['required', 'numeric'],
            'payload.price.currency' => ['nullable', 'string', 'max:8'],
            'payload.price.tax_included' => ['nullable', 'boolean'],
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
