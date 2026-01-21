# Bi-directional Sync API (Store ↔ ERP)

## المبدأ
- أي تعديل (سعر/مخزون/خصائص/منتج) يصدر كـ Event JSON.
- كل Event يحتوي `source` (store|erp) و `external_id` فريد (UUID).
- المستقبل:
  - إذا كان `source` نفس الجهة → Ignore.
  - إذا كان `source` الطرف الآخر → Apply مع Idempotency.
- كل POST محمي بـ Bearer Token + Idempotency-Key.
- كل Event يدخل Queue مع Retry + DLQ.
- المفتاح المشترك بين المتجر و ERP: `product_code` (قيمة موحّدة يستخدمها الطرفان لتحديد نفس المنتج).

## Event Envelope
```json
{
  "external_id": "8c2f9d2e-1d2e-4c4a-b3f5-0b67d7e45c90",
  "source": "store",
  "occurred_at": "2026-01-08T09:12:11Z",
  "payload": { /* حسب نوع الحدث */ }
}
```

## Endpoints
- POST `/api/events/product_update`
- POST `/api/events/variant_update`
- POST `/api/events/stock_update`
- POST `/api/events/price_update`
- GET  `/api/products/{id}`
- GET  `/api/variants/{id}`

المجموعة محمية بـ `auth:sanctum` + `Authorization: Bearer <token>` + `Idempotency-Key`.

### POST /api/events/product_update
Request:
```json
{
  "external_id": "{{uuid}}",
  "source": "store",
  "occurred_at": "{{iso}}",
  "payload": {
    "product_id": "store-123",
    "product_code": "PC-0001",
    "remote_product_id": "erp-555",
    "name": "Panadol 500mg",
    "description": "Pain relief",
    "status": "active"
  }
}
```
Response 202:
```json
{ "queued": true, "duplicate": false, "external_id": "{{uuid}}", "event_type": "product_update" }
```

### POST /api/events/variant_update
```json
{
  "external_id": "{{uuid}}",
  "source": "erp",
  "occurred_at": "{{iso}}",
  "payload": {
    "product_id": "store-123",
    "product_code": "PC-0001",
    "remote_product_id": "erp-555",
    "variant_id": "var-1",
    "remote_variant_id": "erp-var-9",
    "attributes": { "size": "500mg", "form": "tablet" },
    "status": "active",
    "price": { "amount": 79.50, "currency": "SAR", "tax_included": true },
    "stock": { "available": 120, "reserved": 10 }
  }
}
```

### POST /api/events/stock_update
```json
{
  "external_id": "{{uuid}}",
  "source": "erp",
  "occurred_at": "{{iso}}",
  "payload": {
    "product_id": null,
    "product_code": "PC-0001",
    "remote_product_id": "erp-555",
    "variant_id": null,
    "remote_variant_id": "erp-var-9",
    "stock": { "available": 90, "reserved": 5 }
  }
}
```

### POST /api/events/price_update
```json
{
  "external_id": "{{uuid}}",
  "source": "store",
  "occurred_at": "{{iso}}",
  "payload": {
    "product_id": "store-123",
    "product_code": "PC-0001",
    "variant_id": "var-1",
    "price": { "amount": 85.00, "currency": "SAR", "tax_included": true }
  }
}
```

### GET /api/products/{id}
Returns product + variants + remote mappings:
```json
{
  "id": "store-123",
  "remote_product_id": "erp-555",
  "name": "Panadol 500mg",
  "description": "Pain relief",
  "status": "active",
  "variants": [
    {
      "id": "var-1",
      "remote_variant_id": "erp-var-9",
      "product_id": "store-123",
      "attributes": { "size": "500mg", "form": "tablet" },
      "status": "active",
      "stock": { "available": 120, "reserved": 10 },
      "price": { "amount": 79.50, "currency": "SAR", "tax_included": true }
    }
  ]
}
```

### GET /api/variants/{id}
```json
{
  "id": "var-1",
  "remote_variant_id": "erp-var-9",
  "product_id": "store-123",
  "remote_product_id": "erp-555",
  "attributes": { "size": "500mg", "form": "tablet" },
  "status": "active",
  "stock": { "available": 120, "reserved": 10 },
  "price": { "amount": 79.50, "currency": "SAR", "tax_included": true }
}
```

## Idempotency
- Header: `Idempotency-Key` (UUID). يسجل `method + path + body_hash`.
- إذا تكرر المفتاح بنفس الجسم → 200 duplicate.
- إذا تكرر بمحتوى مختلف → 409 conflict.
- `external_id` يبقى فريد لكل حدث.

## Queue + Retry
- جميع POST تكتب EventLog بالحالة pending ثم Job `ProcessSyncEventJob`.
- Backoff: 1m, 5m, 15m ثم DLQ (حسب إعداد queue).
- `status`: pending | processed | ignored | failed.
- Loop-guard: إذا كان `source == config('sync.source')` → ignored.

## جداول جديدة
- `product_variants`، `variant_prices`، `variant_stocks`
- `external_references(local_type, local_id, remote_id, source_system)` مع فهارس فريدة
- `idempotency_keys(key, method, path, body_hash, response)`
- `event_logs(external_id, event_type, source, status, attempts, payload, error)`
- `outbox_events(event_type, payload, target_system, status, attempts, last_error)`

## Security
- Bearer Token (Sanctum).
- Optional: IP allowlist، HMAC Signature (`X-Signature: hmac_sha256(body, secret)`).
- Rate limit: use `throttle:api`.
- TLS إجباري.

## سيناريوهات مختصرة
- سعر من المتجر → ERP: تعديل داخلي يولّد outbox event (`source=store`) ويرسل إلى `/api/events/price_update`.
- مخزون من ERP → متجر: ERP يرسل stock_update (`source=erp`) → worker يطبق ويحدّث mapping.
- إنشاء Variant من ERP: يرسل variant_update بـ `remote_variant_id` فقط → المتجر ينشئ variant جديد ويعيده في الرد عند الاستعلام GET.
- Loop prevention: لا نعيد إرسال حدث بنفس `source`.

## Postman
- ملف جاهز: `docs/postman/bi-directional-sync.postman_collection.json`
- متغيرات: `base_url`, `bearer_token`, `uuid`, `iso_datetime`.
