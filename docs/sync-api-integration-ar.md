# دليل تكامل Bi-Directional Sync API

## نظرة عامة

هذا الـ API يسمح لأنظمة خارجية (مثل ERP أو OrgaSoft) بإرسال تحديثات للمنتجات والمتغيرات والمخزون والأسعار إلى المتجر الإلكتروني، وكذلك قراءة بيانات المنتجات منه.

---

## المصادقة (Authentication)

كل الطلبات تحتاج إلى مفتاح API في الـ Header:

```
API-KEY: AHMED_ADEL
```

> يمكن تغيير هذا المفتاح من لوحة الإعدادات في المتجر (حقل `orgasoft_api_key`).

---

## قواعد أساسية قبل البدء

### 1. حقل `source`
- يجب أن تكون قيمته `"erp"` دائماً عند الإرسال من النظام الخارجي.
- إذا كانت القيمة `"store"` سيتجاهل السيرفر الطلب تلقائياً (`event_status: "ignored"`) لتجنب الـ loop.

### 2. حقل `external_id`
- UUID خاص بالحدث من النظام الخارجي.
- يُستخدم فقط للتتبع والـ logging، لا يؤثر على الـ idempotency.

### 3. حقل `occurred_at`
- تاريخ ووقت الحدث بصيغة ISO 8601.
- مثال: `"2026-03-04T10:30:00Z"`

### 4. Idempotency (منع التكرار)
- اختياري: أرسل Header باسم `Idempotency-Key` بقيمة UUID فريدة لكل عملية.
- إذا أرسلت نفس الـ Key مع **نفس الـ body** → يرجع الرد المحفوظ بدون إعادة المعالجة.
- إذا أرسلت نفس الـ Key مع **body مختلف** → يُعالج الطلب الجديد ويُحذف القديم.
- إذا لم ترسل الـ Header → يُعالج الطلب دائماً بدون أي تحقق من التكرار.

---

## تحديد المنتج والمتغير

كل endpoint يحتاج تحديد المنتج والمتغير. يمكن استخدام أي من هذه الطرق:

| الحقل | الوصف |
|---|---|
| `product_id` | الـ ID المحلي للمنتج في قاعدة بيانات المتجر |
| `remote_product_id` | الـ ID الخاص بالمنتج في نظامك الخارجي (ERP) |
| `product_code` | كود المنتج (SKU) المشترك بين النظامين |
| `variant_id` | الـ ID المحلي للمتغير في قاعدة بيانات المتجر |
| `remote_variant_id` | الـ ID الخاص بالمتغير في نظامك الخارجي (ERP) |

> **ملاحظة مهمة:** إذا لم يجد السيرفر المنتج أو المتغير بالمعرّفات المُرسَلة، سيُنشئ سجلاً جديداً تلقائياً ويحفظ الربط (العلاقة) للمرة القادمة.

---

## Endpoints

### Base URL
```
https://your-store.test
```

---

### 1. تحديث بيانات المنتج

**POST** `/api/events/product_update`

يُستخدم لتحديث الاسم والوصف والحالة.

**Request Body:**
```json
{
  "external_id": "550e8400-e29b-41d4-a716-446655440000",
  "source": "erp",
  "occurred_at": "2026-03-04T10:00:00Z",
  "payload": {
    "product_id": "1",
    "remote_product_id": "erp-555",
    "product_code": null,
    "name": "باراسيتامول 500mg",
    "description": "مسكن ألم وخافض حرارة",
    "status": "active"
  }
}
```

**حقول الـ payload:**

| الحقل | نوع | مطلوب | الوصف |
|---|---|---|---|
| `product_id` | string | اختياري* | ID المنتج في المتجر |
| `remote_product_id` | string | اختياري* | ID المنتج في نظامك |
| `product_code` | string | اختياري* | كود المنتج المشترك |
| `name` | string | **مطلوب** | اسم المنتج |
| `description` | string | اختياري | وصف المنتج |
| `status` | string | اختياري | `active` أو `inactive` |

> *يجب توفير واحد على الأقل من: `product_id` أو `remote_product_id` أو `product_code`.

**Response ناجح:**
```json
{
  "processed": true,
  "duplicate": false,
  "external_id": "550e8400-e29b-41d4-a716-446655440000",
  "event_type": "product_update",
  "event_status": "processed"
}
```

---

### 2. تحديث بيانات المتغير (Variant)

**POST** `/api/events/variant_update`

يُستخدم لتحديث خصائص المتغير (size/color) والسعر والمخزون معاً في طلب واحد.

**Request Body:**
```json
{
  "external_id": "550e8400-e29b-41d4-a716-446655440001",
  "source": "erp",
  "occurred_at": "2026-03-04T10:01:00Z",
  "payload": {
    "product_id": "1",
    "remote_product_id": "erp-555",
    "product_code": null,
    "variant_id": null,
    "remote_variant_id": "erp-var-9",
    "attributes": { "size": "500mg", "form": "tablet" },
    "status": "active",
    "price": {
      "amount": 79.50,
      "currency": "SAR",
      "tax_included": true
    },
    "stock": {
      "available": 110,
      "reserved": 10
    }
  }
}
```

**حقول الـ payload:**

| الحقل | نوع | مطلوب | الوصف |
|---|---|---|---|
| `product_id` | string | اختياري* | تحديد المنتج الأب |
| `remote_product_id` | string | اختياري* | تحديد المنتج الأب عبر ID خارجي |
| `product_code` | string | اختياري* | تحديد المنتج الأب عبر الكود |
| `variant_id` | string | اختياري** | ID المتغير في المتجر |
| `remote_variant_id` | string | اختياري** | ID المتغير في نظامك |
| `attributes` | object | اختياري | خصائص المتغير (size, color, ...) |
| `status` | string | اختياري | `active` أو `inactive` |
| `price.amount` | numeric | اختياري | السعر |
| `price.currency` | string | اختياري | العملة (افتراضي: SAR) |
| `price.tax_included` | boolean | اختياري | هل الضريبة مضمنة؟ |
| `stock.available` | integer | اختياري | الكمية المتاحة |
| `stock.reserved` | integer | اختياري | الكمية المحجوزة |

> *يجب توفير واحد على الأقل لتحديد المنتج.
> **يجب توفير واحد على الأقل لتحديد المتغير.

> **تأثير جانبي:** بعد تحديث المتغير، يُحدَّث المنتج الأب تلقائياً بمجموع المخزون وأول سعر متاح.

---

### 3. تحديث المخزون فقط

**POST** `/api/events/stock_update`

يُستخدم عند تغيير الكمية فقط بدون تغيير السعر أو الخصائص.

**Request Body:**
```json
{
  "external_id": "550e8400-e29b-41d4-a716-446655440002",
  "source": "erp",
  "occurred_at": "2026-03-04T10:02:00Z",
  "payload": {
    "variant_id": "1",
    "remote_variant_id": "erp-var-9",
    "stock": {
      "available": 90,
      "reserved": 3
    }
  }
}
```

**حقول الـ payload:**

| الحقل | نوع | مطلوب | الوصف |
|---|---|---|---|
| `variant_id` | string | اختياري* | ID المتغير في المتجر |
| `remote_variant_id` | string | اختياري* | ID المتغير في نظامك |
| `product_id` | string | اختياري | لتحديد المنتج إذا لزم |
| `remote_product_id` | string | اختياري | لتحديد المنتج عبر ID خارجي |
| `product_code` | string | اختياري | لتحديد المنتج عبر الكود |
| `stock.available` | integer | **مطلوب** | الكمية المتاحة الجديدة |
| `stock.reserved` | integer | اختياري | الكمية المحجوزة |

> *يجب توفير واحد على الأقل لتحديد المتغير.

---

### 4. تحديث السعر فقط

**POST** `/api/events/price_update`

يُستخدم عند تغيير السعر فقط بدون تغيير المخزون.

**Request Body:**
```json
{
  "external_id": "550e8400-e29b-41d4-a716-446655440003",
  "source": "erp",
  "occurred_at": "2026-03-04T10:03:00Z",
  "payload": {
    "variant_id": "1",
    "remote_variant_id": "erp-var-9",
    "price": {
      "amount": 60.00,
      "currency": "SAR",
      "tax_included": true
    }
  }
}
```

**حقول الـ payload:**

| الحقل | نوع | مطلوب | الوصف |
|---|---|---|---|
| `variant_id` | string | اختياري* | ID المتغير في المتجر |
| `remote_variant_id` | string | اختياري* | ID المتغير في نظامك |
| `product_id` | string | اختياري | لتحديد المنتج إذا لزم |
| `remote_product_id` | string | اختياري | لتحديد المنتج عبر ID خارجي |
| `product_code` | string | اختياري | لتحديد المنتج عبر الكود |
| `price.amount` | numeric | **مطلوب** | السعر الجديد |
| `price.currency` | string | اختياري | العملة (افتراضي: SAR) |
| `price.tax_included` | boolean | اختياري | هل الضريبة مضمنة؟ |

> *يجب توفير واحد على الأقل لتحديد المتغير.

---

### 5. قراءة بيانات منتج

**GET** `/api/products/{id}`

`{id}` يمكن أن يكون الـ ID المحلي أو الـ slug.

**مثال:**
```
GET /api/products/1
GET /api/products/paracetamol
```

**Response:**
```json
{
  "id": "1",
  "remote_product_id": "erp-555",
  "name": "باراسيتامول 500mg",
  "description": "مسكن ألم",
  "status": "active",
  "variants": [
    {
      "id": "1",
      "remote_variant_id": "erp-var-9",
      "product_id": "1",
      "attributes": { "size": "500mg", "form": "tablet" },
      "status": "active",
      "stock": { "available": 90, "reserved": 3 },
      "price": { "amount": "60.00", "currency": "SAR", "tax_included": true }
    }
  ]
}
```

---

### 6. قراءة بيانات متغير

**GET** `/api/variants/{id}`

`{id}` هو الـ ID المحلي للمتغير.

**مثال:**
```
GET /api/variants/1
```

---

## حالات الـ Response

| `event_status` | المعنى |
|---|---|
| `processed` | تم التنفيذ بنجاح وتحديث قاعدة البيانات |
| `ignored` | تم تجاهل الطلب لأن `source` يساوي `"store"` |
| `failed` | حدث خطأ أثناء المعالجة (يرجع حقل `error` بالتفاصيل) |

| HTTP Status | المعنى |
|---|---|
| `200 OK` | الطلب نجح (أو كان مكرراً مع نفس الـ body) |
| `401 Unauthorized` | مفتاح الـ API غير صحيح أو غائب |
| `422 Unprocessable` | خطأ في التحقق من البيانات (validation error) |
| `500 Server Error` | خطأ داخلي في المعالجة |

---

## الترتيب الموصى به عند التكامل لأول مرة

1. **أرسل `variant_update`** أولاً لكل متغير — هذا ينشئ المنتج والمتغير وربطهما بنظامك في خطوة واحدة.
2. **استخدم `remote_product_id` و `remote_variant_id`** الخاصة بنظامك في كل الطلبات — السيرفر يحفظ هذا الربط تلقائياً.
3. **للتحديثات الدورية:** استخدم `stock_update` و `price_update` بشكل منفصل لتقليل حجم البيانات المرسلة.
4. **لا تُرسل `source: "store"`** — هذا مخصص للمتجر فقط لإرسال الأحداث لنظامك، ليس العكس.

---

## إعداد Postman

1. استورد ملف `Bi-Directional Sync API.postman_collection.json`
2. عدّل متغيرات الـ Collection:

| المتغير | القيمة |
|---|---|
| `base_url` | عنوان المتجر مثل `https://your-store.test` |
| `uuid` | احذف هذا المتغير من الـ Headers أو استبدل قيمته بـ UUID فريد لكل طلب |

3. أضف Header لكل الطلبات:
```
API-KEY: AHMED_ADEL
```

> **مهم:** الـ `Idempotency-Key` header في الـ Collection يستخدم `{{uuid}}` الذي قيمته ثابتة (`00000000-...`). إما احذف هذا الـ Header أو غيّر قيمته لـ `{{$guid}}` لضمان UUID جديد في كل طلب.
