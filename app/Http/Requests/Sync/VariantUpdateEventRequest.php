<?php

namespace App\Http\Requests\Sync;

class VariantUpdateEventRequest extends BaseEventRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'payload.product_id' => ['nullable', 'string'],
            'payload.remote_product_id' => ['nullable', 'string'],
            'payload.product_code' => ['nullable', 'string', 'max:255'],
            'payload.variant_id' => ['nullable', 'string'],
            'payload.remote_variant_id' => ['nullable', 'string'],
            'payload.attributes' => ['nullable', 'array'],
            'payload.status' => ['nullable', 'in:active,inactive'],
            'payload.price.amount' => ['nullable', 'numeric'],
            'payload.price.currency' => ['nullable', 'string', 'max:8'],
            'payload.price.tax_included' => ['nullable', 'boolean'],
            'payload.stock.available' => ['nullable', 'integer'],
            'payload.stock.reserved' => ['nullable', 'integer'],
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $hasProduct = $this->input('payload.product_id')
                || $this->input('payload.remote_product_id')
                || $this->input('payload.product_code');
            $hasVariant = $this->input('payload.variant_id') || $this->input('payload.remote_variant_id');

            if (!$hasProduct) {
                $validator->errors()->add('payload.product_id', 'Provide at least one of product_id, remote_product_id, or product_code.');
            }

            if (!$hasVariant) {
                $validator->errors()->add('payload.variant_id', 'Either variant_id or remote_variant_id is required.');
            }
        });
    }
}
