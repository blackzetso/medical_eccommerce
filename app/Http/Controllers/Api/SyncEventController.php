<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sync\PriceUpdateEventRequest;
use App\Http\Requests\Sync\ProductUpdateEventRequest;
use App\Http\Requests\Sync\StockUpdateEventRequest;
use App\Http\Requests\Sync\VariantUpdateEventRequest;
use App\Models\ExternalReference;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\SyncEventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncEventController extends Controller
{
    public function __construct(private readonly SyncEventService $service)
    {
    }

    public function productUpdate(ProductUpdateEventRequest $request): JsonResponse
    {
        return $this->respond('product_update', $request);
    }

    public function variantUpdate(VariantUpdateEventRequest $request): JsonResponse
    {
        return $this->respond('variant_update', $request);
    }

    public function stockUpdate(StockUpdateEventRequest $request): JsonResponse
    {
        return $this->respond('stock_update', $request);
    }

    public function priceUpdate(PriceUpdateEventRequest $request): JsonResponse
    {
        return $this->respond('price_update', $request);
    }

    public function showProduct(string $id): JsonResponse
    {
        $product = Product::with(['variants.price', 'variants.stock'])->findOrFail($id);
        $targetSystem = config('sync.default_target_system', 'erp');

        $remoteProductId = ExternalReference::where([
            'local_type' => 'product',
            'local_id' => $product->id,
            'source_system' => $targetSystem,
        ])->value('remote_id');

        $variants = $product->variants->map(function (ProductVariant $variant) use ($targetSystem) {
            $remoteVariantId = ExternalReference::where([
                'local_type' => 'variant',
                'local_id' => $variant->id,
                'source_system' => $targetSystem,
            ])->value('remote_id');

            return [
                'id' => (string) $variant->id,
                'remote_variant_id' => $remoteVariantId,
                'product_id' => (string) $variant->product_id,
                'attributes' => $variant->attributes,
                'status' => $variant->status,
                'stock' => [
                    'available' => $variant->stock?->available ?? 0,
                    'reserved' => $variant->stock?->reserved ?? 0,
                ],
                'price' => [
                    'amount' => $variant->price?->amount,
                    'currency' => $variant->price?->currency,
                    'tax_included' => $variant->price?->tax_included,
                ],
            ];
        });

        return response()->json([
            'id' => (string) $product->id,
            'remote_product_id' => $remoteProductId,
            'name' => $product->name,
            'description' => $product->description,
            'status' => $product->status ? 'active' : 'inactive',
            'variants' => $variants,
        ]);
    }

    public function showVariant(string $id): JsonResponse
    {
        $variant = ProductVariant::with(['product', 'price', 'stock'])->findOrFail($id);
        $targetSystem = config('sync.default_target_system', 'erp');

        $remoteVariantId = ExternalReference::where([
            'local_type' => 'variant',
            'local_id' => $variant->id,
            'source_system' => $targetSystem,
        ])->value('remote_id');

        $remoteProductId = ExternalReference::where([
            'local_type' => 'product',
            'local_id' => $variant->product_id,
            'source_system' => $targetSystem,
        ])->value('remote_id');

        return response()->json([
            'id' => (string) $variant->id,
            'remote_variant_id' => $remoteVariantId,
            'product_id' => (string) $variant->product_id,
            'remote_product_id' => $remoteProductId,
            'attributes' => $variant->attributes,
            'status' => $variant->status,
            'stock' => [
                'available' => $variant->stock?->available ?? 0,
                'reserved' => $variant->stock?->reserved ?? 0,
            ],
            'price' => [
                'amount' => $variant->price?->amount,
                'currency' => $variant->price?->currency,
                'tax_included' => $variant->price?->tax_included,
            ],
        ]);
    }

    private function respond(string $eventType, Request $request): JsonResponse
    {
        $idempotencyKey = $request->header('Idempotency-Key') ?? $request->input('external_id');

        if (!$idempotencyKey) {
            return response()->json([
                'message' => 'Idempotency-Key header is required.',
            ], 400);
        }

        $result = $this->service->ingest(
            $eventType,
            $request->validated(),
            $idempotencyKey,
            $request->path(),
            $request->method()
        );

        return response()->json($result['response'], $result['status']);
    }
}
