# شرح payload حدث stock_update ولماذا قد لا يتغير عمود المخزون في جدول المنتجات

## معنى الـ payload

```json
{
  "external_id": "{{uuid}}",
  "source": "erp",
  "occurred_at": "{{iso_datetime}}",
  "payload": {
    "product_id": null,
    "product_code": "951357",
    "remote_product_id": "erp-555",
    "variant_id": null,
    "remote_variant_id": "erp-var-9",
    "stock": { "available": 90, "reserved": 5 }
  }
}
```

| الحقل | المعنى |
|-------|--------|
| **product_id** (null) | الـ ID المحلي للمنتج في جدول `products` — لو مش معروف نتركه null ونعتمد على product_code أو remote_product_id |
| **product_code** | كود المنتج في نظامك (عمود `product_code` في جدول `products`) — يستخدم لتحديد المنتج لو مفيش product_id |
| **remote_product_id** | معرف المنتج في نظام الـ ERP (لربط المنتج بين النظامين عبر `external_references`) |
| **variant_id** (null) | الـ ID المحلي للـ variant في جدول `product_variants` — لو مش معروف نتركه null |
| **remote_variant_id** | معرف الـ variant في نظام الـ ERP — يُستخدم للربط في `external_references` أو لإنشاء variant جديد مربوط به |
| **stock** | الكميات: available (متاح)، reserved (محجوز) |

**الفكرة:** النظام يقدر يحدد المنتج والـ variant من أي تركيبة: إما الـ IDs المحلية، أو من خلال `product_code` / `remote_product_id` / `remote_variant_id` والجدول `external_references`.

---

## أين يُحفظ الستوك فعلياً؟

1. **جدول `variant_stocks`**  
   الستوك يُحفظ أولاً هنا (لكل variant من جدول `product_variants`):  
   `available`, `reserved`.

2. **جدول `products` (عمود `stock_quantity`)**  
   بعد تحديث الـ variant، الدالة `syncProductFromVariants` تجمع كل الـ `available` من كل الـ variants التابعة لنفس المنتج وتضع المجموع في `product.stock_quantity` وتحدّث `in_stock`.

يعني: **الرقم اللي المفروض يتغير من الـ sync هو `products.stock_quantity`** (ومعه `in_stock`)، وليس جدولاً آخر مثل `product_attributes` إلا لو تمت إضافة منطق إضافي.

---

## لماذا قد يبقى "الرقم في جدول المنتجات زي ما هو"؟

### 1) المنتج الذي تشاهده ليس نفسه الذي حدده الـ sync

الـ sync يحدد المنتج من:
- `product_code` = `"951357"` أو  
- الربط عبر `remote_product_id` = `"erp-555"` في `external_references`.

- تأكد أنك تنظر إلى المنتج الذي **product_code** = `951357` (أو المرتبط بـ `erp-555` في `external_references`).
- لو بتشوف منتج آخر، فطبيعي أن `stock_quantity` له لا يتغير.

### 2) المنتج له "خصائص" (attributes) والواجهة تعرض `total_stock`

في الموديل `Product`:

- لو المنتج **ما لهوش attributes**:  
  `total_stock` = `stock_quantity` من جدول `products` (وهو اللي الـ sync بيحدّثه).
- لو المنتج **له attributes**:  
  `total_stock` = مجموع `stock_quantity` من جدول **`product_attributes`** (pivot)، و**لا** من `products.stock_quantity`.

الـ sync حالياً يحدّث فقط:
- `variant_stocks`
- ثم `products.stock_quantity` (من مجموع الـ variants).

ولا يحدّث جدول `product_attributes`.  
لذلك:
- عمود **`products.stock_quantity`** قد يتغير فعلاً (مثلاً إلى 90).
- لكن إن كانت الواجهة أو التقارير تعرض **`total_stock`** والمنتج له attributes، فستشاهد القيمة القديمة لأنها مأخوذة من `product_attributes`.

**الحل المقترح:**  
إما التأكد أنك تنظر إلى عمود **`stock_quantity`** في جدول **`products`** للمنتج الصحيح (product_code 951357)، أو إذا أردت أن يعكس الـ sync على المنتجات ذات الخصائص فعليك إضافة منطق يحدّث أيضاً `product_attributes` (أو يوحّد مصدر عرض المخزون).

### 3) الـ event تم تجاهله (ignored)

لو `config('sync.source')` = `'erp'` والـ payload فيه `"source": "erp"`، فإن الـ event يُعتبر من نفس المصدر ويُرجَع له `event_status: 'ignored'` ولن يُحدَّث أي ستوك.

أنت قلت أن الرد كان `"event_status": "processed"`، ففي هذه الحالة الـ event تم معالجته ولا يُفترض أن يكون السبب.

---

## التحقق عملياً

1. **من قاعدة البيانات**
   - جدول **`products`**: ابحث عن الصف الذي `product_code = '951357'` (أو المرتبط بـ `erp-555` في `external_references`) وتحقق من قيمة **`stock_quantity`** بعد إرسال الـ stock_update.
   - جدول **`product_variants`**: ابحث عن الـ variant المرتبط بهذا المنتج (أو الذي له `external_references` مع `remote_id = 'erp-var-9'`).
   - جدول **`variant_stocks`**: تحقق من وجود صف لهذا الـ variant وقيم `available` و `reserved` (مثلاً 90 و 5).

2. **من الكود**
   - الـ response الذي فيه `"processed": true` و `"event_status": "processed"` يعني أن `handleStockUpdate` تمت بدون exception وأن `syncProductFromVariants` استُدعيت للمنتج المرتبط بالـ variant الذي تم تحديث ستوكه.

باختصار: الـ ID من الـ payload يُستنتج من جداول `products` (عبر product_code أو external_references) و`product_variants` و`external_references`، والستوك يُحفظ في `variant_stocks` ثم يُجمع في `products.stock_quantity`. لو "الرقم في جدول المنتجات" لم يتغير فغالباً إما تشاهد منتجاً آخر أو تعتمد على `total_stock` لمنتج له attributes (يُحسب من `product_attributes`).
