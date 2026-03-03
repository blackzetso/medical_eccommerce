<?php

namespace App\Services;

use App\Models\EventLog;
use App\Models\ExternalReference;
use App\Models\IdempotencyKey;
use App\Models\OutboxEvent;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantPrice;
use App\Models\VariantStock;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncEventService
{
    public function ingest(string $eventType, array $data, string $idempotencyKey, string $path, string $method): array
    {
        $bodyHash = hash('sha256', json_encode($data));
        $existingKey = IdempotencyKey::find($idempotencyKey);

        if ($existingKey) {
            if ($existingKey->body_hash !== $bodyHash) {
                return [
                    'status' => 409,
                    'response' => [
                        'message' => 'Idempotency conflict: body differs for the same key.',
                    ],
                ];
            }

            return [
                'status' => 200,
                'response' => array_merge(
                    $existingKey->response ?? [],
                    ['duplicate' => true]
                ),
            ];
        }

        IdempotencyKey::create([
            'key' => $idempotencyKey,
            'method' => $method,
            'path' => $path,
            'body_hash' => $bodyHash,
            'response' => null,
        ]);

        $log = EventLog::create([
            'external_id' => $data['external_id'],
            'event_type' => $eventType,
            'source' => $data['source'],
            'status' => 'pending',
            'payload' => $data,
        ]);

        // Process synchronously so the request completes with result without depending on queue worker.
        $this->processByLogId($log->id);

        $log->refresh();
        $response = [
            'processed' => true,
            'duplicate' => false,
            'external_id' => $data['external_id'],
            'event_type' => $eventType,
            'event_status' => $log->status,
        ];

        IdempotencyKey::where('key', $idempotencyKey)->update(['response' => $response]);

        return [
            'status' => 200,
            'response' => $response,
        ];
    }

    public function processByLogId(int $eventLogId): void
    {
        $log = EventLog::find($eventLogId);

        if (!$log) {
            return;
        }

        $log->increment('attempts');

        try {
            $result = $this->process($log->event_type, $log->payload);

            $log->status = $result['status'];
            $log->processed_at = Carbon::now();
            $log->error = $result['error'] ?? null;
            $log->save();
        } catch (\Throwable $throwable) {
            $log->status = 'failed';
            $log->error = $throwable->getMessage();
            $log->save();

            throw $throwable;
        }
    }

    public function process(string $eventType, array $data): array
    {
        $localSource = config('sync.source', 'store');

        if (($data['source'] ?? null) === $localSource) {
            return [
                'status' => 'ignored',
            ];
        }

        return match ($eventType) {
            'product_update' => $this->handleProductUpdate($data),
            'variant_update' => $this->handleVariantUpdate($data),
            'stock_update' => $this->handleStockUpdate($data),
            'price_update' => $this->handlePriceUpdate($data),
            default => [
                'status' => 'failed',
                'error' => 'Unsupported event type.',
            ],
        };
    }

    private function handleProductUpdate(array $data): array
    {
        $payload = $data['payload'] ?? [];
        $sourceSystem = $data['source'] ?? 'unknown';

        return DB::transaction(function () use ($payload, $sourceSystem) {
            $product = $this->resolveProduct(
                $payload['product_id'] ?? null,
                $payload['remote_product_id'] ?? null,
                $sourceSystem,
                $payload['product_code'] ?? null
            );

            if (isset($payload['name'])) {
                $product->name = $payload['name'];
                $product->slug = $product->slug ?: Str::slug($product->name) . '-' . Str::random(4);
            }

            if (isset($payload['description'])) {
                $product->description = $payload['description'];
            }

            if (isset($payload['status'])) {
                $product->status = $payload['status'] === 'active';
            }

            $product->save();

            if (!empty($payload['remote_product_id'])) {
                $this->ensureExternalReference('product', $product->id, $payload['remote_product_id'], $sourceSystem);
            }

            return [
                'status' => 'processed',
            ];
        });
    }

    private function handleVariantUpdate(array $data): array
    {
        $payload = $data['payload'] ?? [];
        $sourceSystem = $data['source'] ?? 'unknown';

        return DB::transaction(function () use ($payload, $sourceSystem) {
            $product = $this->resolveProduct(
                $payload['product_id'] ?? null,
                $payload['remote_product_id'] ?? null,
                $sourceSystem,
                $payload['product_code'] ?? null
            );

            $variant = $this->resolveVariant(
                $payload['variant_id'] ?? null,
                $payload['remote_variant_id'] ?? null,
                $sourceSystem,
                $product->id,
                null,
                $payload['remote_product_id'] ?? null,
                $payload['product_code'] ?? null
            );

            if (isset($payload['attributes'])) {
                $variant->attributes = $payload['attributes'];
            }

            if (isset($payload['status'])) {
                $variant->status = $payload['status'];
            }

            $variant->product_id = $product->id;
            $variant->save();

            if (!empty($payload['remote_variant_id'])) {
                $this->ensureExternalReference('variant', $variant->id, $payload['remote_variant_id'], $sourceSystem);
            }

            if (!empty($payload['price'])) {
                $this->storeVariantPrice($variant, $payload['price']);
            }

            if (!empty($payload['stock'])) {
                $this->storeVariantStock($variant, $payload['stock']);
            }

            $this->syncProductFromVariants($variant->product);

            return [
                'status' => 'processed',
            ];
        });
    }

    private function handleStockUpdate(array $data): array
    {
        $payload = $data['payload'] ?? [];
        $sourceSystem = $data['source'] ?? 'unknown';

        return DB::transaction(function () use ($payload, $sourceSystem) {
            $variant = $this->resolveVariant(
                $payload['variant_id'] ?? null,
                $payload['remote_variant_id'] ?? null,
                $sourceSystem,
                null,
                $payload['product_id'] ?? null,
                $payload['remote_product_id'] ?? null,
                $payload['product_code'] ?? null
            );

            if (!empty($payload['stock'])) {
                $this->storeVariantStock($variant, $payload['stock']);
            }

            $this->syncProductFromVariants($variant->product);

            return [
                'status' => 'processed',
            ];
        });
    }

    private function handlePriceUpdate(array $data): array
    {
        $payload = $data['payload'] ?? [];
        $sourceSystem = $data['source'] ?? 'unknown';

        return DB::transaction(function () use ($payload, $sourceSystem) {
            $variant = $this->resolveVariant(
                $payload['variant_id'] ?? null,
                $payload['remote_variant_id'] ?? null,
                $sourceSystem,
                null,
                $payload['product_id'] ?? null,
                $payload['remote_product_id'] ?? null,
                $payload['product_code'] ?? null
            );

            if (!empty($payload['price'])) {
                $this->storeVariantPrice($variant, $payload['price']);
            }

            $this->syncProductFromVariants($variant->product);

            return [
                'status' => 'processed',
            ];
        });
    }

    /**
     * Push variant-level price and stock to the Product so the storefront reflects sync updates.
     */
    private function syncProductFromVariants(Product $product): void
    {
        $product->load(['variants.price', 'variants.stock']);
        $variants = $product->variants;

        if ($variants->isEmpty()) {
            return;
        }

        $totalAvailable = 0;
        $firstPriceAmount = null;

        foreach ($variants as $variant) {
            if ($variant->stock) {
                $totalAvailable += (int) ($variant->stock->available ?? 0);
            }
            if ($firstPriceAmount === null && $variant->price !== null) {
                $firstPriceAmount = (float) $variant->price->amount;
            }
        }

        $product->stock_quantity = $totalAvailable;
        $product->in_stock = $totalAvailable > 0;
        if ($firstPriceAmount !== null) {
            $product->price = $firstPriceAmount;
        }
        $product->save();
    }

    private function resolveProduct(?string $productId, ?string $remoteProductId, string $sourceSystem, ?string $productCode = null): Product
    {
        if ($productCode) {
            $product = Product::where('product_code', $productCode)->first();
            if ($product) {
                return $product;
            }
        }

        if ($productId) {
            $product = is_numeric($productId)
                ? Product::find($productId)
                : Product::where('slug', $productId)->first();
            if ($product) {
                return $product;
            }
        }

        if ($remoteProductId) {
            $ref = ExternalReference::where([
                'local_type' => 'product',
                'remote_id' => $remoteProductId,
                'source_system' => $sourceSystem,
            ])->first();

            if ($ref) {
                $product = Product::find($ref->local_id);
                if ($product) {
                    return $product;
                }
            }
        }

        $product = new Product([
            'name' => 'Synced Product ' . ($remoteProductId ?: $productCode ?: Str::random(5)),
            'slug' => Str::slug('synced-' . ($remoteProductId ?: $productCode ?: Str::random(5))),
            'product_code' => $productCode,
            'price' => 0,
            'stock_quantity' => 0,
            'manage_stock' => true,
            'in_stock' => true,
            'status' => true,
        ]);
        $product->save();

        if ($remoteProductId) {
            $this->ensureExternalReference('product', $product->id, $remoteProductId, $sourceSystem);
        }

        return $product;
    }

    private function resolveVariant(
        ?string $variantId,
        ?string $remoteVariantId,
        string $sourceSystem,
        ?int $productId = null,
        ?string $productLocalId = null,
        ?string $remoteProductId = null,
        ?string $productCode = null
    ): ProductVariant {
        if ($variantId) {
            $variant = is_numeric($variantId)
                ? ProductVariant::find($variantId)
                : ProductVariant::where('sku', $variantId)->first();
            if ($variant) {
                return $variant;
            }
        }

        if ($remoteVariantId) {
            $ref = ExternalReference::where([
                'local_type' => 'variant',
                'remote_id' => $remoteVariantId,
                'source_system' => $sourceSystem,
            ])->first();

            if ($ref) {
                $variant = ProductVariant::find($ref->local_id);
                if ($variant) {
                    return $variant;
                }
            }
        }

        if (!$productId && $productLocalId) {
            $product = is_numeric($productLocalId)
                ? Product::find($productLocalId)
                : Product::where('slug', $productLocalId)->first();
            if ($product) {
                $productId = $product->id;
            }
        }

        if (!$productId && $remoteProductId) {
            $product = $this->resolveProduct(null, $remoteProductId, $sourceSystem);
            $productId = $product->id;
        }

        if (!$productId && $productCode) {
            $product = $this->resolveProduct(null, null, $sourceSystem, $productCode);
            $productId = $product->id;
        }

        if (!$productId) {
            $product = $this->resolveProduct(null, null, $sourceSystem);
            $productId = $product->id;
        }

        $variant = new ProductVariant([
            'product_id' => $productId,
            'status' => 'active',
        ]);
        $variant->save();

        if ($remoteVariantId) {
            $this->ensureExternalReference('variant', $variant->id, $remoteVariantId, $sourceSystem);
        }

        return $variant;
    }

    private function storeVariantPrice(ProductVariant $variant, array $pricePayload): void
    {
        $price = $variant->price ?: new VariantPrice(['variant_id' => $variant->id]);
        $price->amount = $pricePayload['amount'] ?? $price->amount ?? 0;
        $price->currency = $pricePayload['currency'] ?? config('sync.default_currency', 'SAR');
        $price->tax_included = $pricePayload['tax_included'] ?? true;
        $price->save();
    }

    private function storeVariantStock(ProductVariant $variant, array $stockPayload): void
    {
        $stock = $variant->stock ?: new VariantStock(['variant_id' => $variant->id]);
        $stock->available = $stockPayload['available'] ?? $stock->available ?? 0;
        $stock->reserved = $stockPayload['reserved'] ?? $stock->reserved ?? 0;
        $stock->save();
    }

    private function ensureExternalReference(string $localType, int $localId, string $remoteId, string $sourceSystem): void
    {
        ExternalReference::updateOrCreate(
            [
                'local_type' => $localType,
                'source_system' => $sourceSystem,
                'local_id' => $localId,
            ],
            [
                'remote_id' => $remoteId,
            ]
        );
    }

    public function createOutboxEvent(string $eventType, array $payload, ?string $targetSystem = null): OutboxEvent
    {
        return OutboxEvent::create([
            'event_type' => $eventType,
            'payload' => $payload,
            'target_system' => $targetSystem ?: config('sync.default_target_system', 'erp'),
            'source' => config('sync.source', 'store'),
            'status' => 'pending',
        ]);
    }
}
