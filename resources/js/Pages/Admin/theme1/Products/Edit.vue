<script setup>
import { ref, computed, onMounted } from 'vue'
import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import Swal from 'sweetalert2'

const props = defineProps({
  product: Object,
  categories: Array,
  brands: Array,
  attributes: Array,
  productAttributes: Array
})

const form = useForm({
  name: props.product.name,
  name_en: props.product.name_en || '',
  slug: props.product.slug,
  description: props.product.description,
  short_description: props.product.short_description,
  cost: props.product.cost || '',
  profit_margin: props.product.profit_margin || '',
  price: props.product.price,
  sale_price: props.product.sale_price,
  discount_type: props.product.discount_type || 'none',
  discount_value: props.product.discount_value || '',
  stock_quantity: props.product.stock_quantity,
  manage_stock: props.product.manage_stock,
  sku: props.product.sku,
  product_code: props.product.product_code || '',
  weight: props.product.weight,
  dimensions: props.product.dimensions,
  category_id: props.product.category_id,
  brand_id: props.product.brand_id,
  is_featured: props.product.is_featured,
  status: props.product.status,
  meta_title: props.product.meta_title,
  meta_description: props.product.meta_description,
  main_image: null,
  existing_main_image: props.product.main_image || null,
  images: [],
  existing_images: props.product.images || [],
  attributes: props.productAttributes || [],
  colors: props.product.colors || [] // إضافة الألوان
})

const imagePreview = ref([])
const existingImages = ref([...form.existing_images])
const mainImagePreview = ref(props.product.main_image || null)
const mainImageFile = ref(null) // حفظ الملف مباشرة في ref بدلاً من form
const colorCount = ref(props.product.colors ? props.product.colors.length : 0); // عدد الألوان
const colorInputs = ref(props.product.colors || []); // قائمة الألوان

// ✅ توليد slug تلقائياً من الاسم الإنجليزي
const generateSlug = () => {
  if (form.name_en && form.name_en.trim()) {
    form.slug = form.name_en
      .toLowerCase()
      .replace(/[^a-z0-9\s-]/g, '')
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-')
      .trim('-')
  }
}

// ✅ رفع الصورة الرئيسية
const handleMainImageUpload = (event) => {
  const file = event.target.files[0]
  
  if (file && file.type.startsWith('image/')) {
    // حفظ الملف في ref مباشرة
    mainImageFile.value = file
    form.main_image = file // أيضاً في form للتوافق
    
    const reader = new FileReader()
    reader.onload = (e) => {
      mainImagePreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

// ✅ رفع الصور
const handleImageUpload = (event) => {
  const files = Array.from(event.target.files)

  files.forEach(file => {
    if (file.type.startsWith('image/')) {
      // إضافة الملف للفورم
      form.images.push(file)

      // إنشاء معاينة
      const reader = new FileReader()
      reader.onload = (e) => {
        imagePreview.value.push({
          url: e.target.result,
          file: file
        })
      }
      reader.readAsDataURL(file)
    }
  })
}

// ✅ حذف صورة جديدة
const removeImage = (index) => {
  form.images.splice(index, 1)
  imagePreview.value.splice(index, 1)
}

// ✅ حذف صورة موجودة
const removeExistingImage = (index) => {
  existingImages.value.splice(index, 1)
  form.existing_images = [...existingImages.value]
}

// ✅ حساب السعر من التكلفة وهامش الربح
const calculatePrice = computed(() => {
  if (!form.cost || !form.profit_margin) {
    return parseFloat(form.price || 0).toFixed(2)
  }
  const cost = parseFloat(form.cost) || 0
  const profitMargin = parseFloat(form.profit_margin) || 0
  const calculatedPrice = cost * (1 + profitMargin / 100)
  form.price = calculatedPrice.toFixed(2)
  return calculatedPrice.toFixed(2)
})

// ✅ حساب السعر النهائي بعد الخصم
const calculateFinalPrice = () => {
  const price = parseFloat(calculatePrice.value) || 0
  if (form.discount_type === 'none' || !form.discount_value) {
    return price.toFixed(2)
  }

  const discountValue = parseFloat(form.discount_value)

  if (form.discount_type === 'fixed') {
    return Math.max(0, price - discountValue).toFixed(2)
  }

  if (form.discount_type === 'percentage') {
    const discountAmount = (price * discountValue) / 100
    return Math.max(0, price - discountAmount).toFixed(2)
  }

  return price.toFixed(2)
}

// ✅ التحقق من وجود خصائص
const hasAttributes = computed(() => {
  return form.attributes && form.attributes.length > 0
})

// ✅ إدارة الخصائص الجديدة
const toggleAttributeValue = (attributeId, valueId, isChecked) => {
  if (isChecked) {
    // إضافة الخاصية
    form.attributes.push({
      attribute_id: attributeId,
      attribute_value_id: valueId,
      price_adjustment: 0,
      stock_quantity: 0
    })
  } else {
    // حذف الخاصية
    const index = form.attributes.findIndex(
      attr => attr.attribute_id == attributeId && attr.attribute_value_id == valueId
    )
    if (index > -1) {
      form.attributes.splice(index, 1)
    }
  }
}

const isAttributeValueSelected = (attributeId, valueId) => {
  return form.attributes.some(
    attr => attr.attribute_id == attributeId && attr.attribute_value_id == valueId
  )
}

const getAttributeValuePriceAdjustment = (attributeId, valueId) => {
  const attr = form.attributes.find(
    attr => attr.attribute_id == attributeId && attr.attribute_value_id == valueId
  )
  return attr ? attr.price_adjustment : 0
}

const updateAttributeValuePrice = (attributeId, valueId, newPrice) => {
  const attr = form.attributes.find(
    attr => attr.attribute_id == attributeId && attr.attribute_value_id == valueId
  )
  if (attr) {
    attr.price_adjustment = parseFloat(newPrice) || 0
  }
}

const updateAttributeValueStock = (attributeId, valueId, newStock) => {
  const attr = form.attributes.find(
    attr => attr.attribute_id == attributeId && attr.attribute_value_id == valueId
  )
  if (attr) {
    attr.stock_quantity = parseInt(newStock) || 0
  }
}

const getAttributeValueStock = (attributeId, valueId) => {
  const attr = form.attributes.find(
    attr => attr.attribute_id == attributeId && attr.attribute_value_id == valueId
  )
  return attr ? attr.stock_quantity : 0
}

const getSelectedAttributesSummary = () => {
  return form.attributes.map(attr => {
    const attribute = props.attributes.find(a => a.id == attr.attribute_id)
    const value = attribute?.values.find(v => v.id == attr.attribute_value_id)
    return {
      attribute_id: attr.attribute_id,
      attribute_value_id: attr.attribute_value_id,
      attribute_name: attribute?.name || '',
      value_name: value?.value || '',
      price_adjustment: attr.price_adjustment || 0,
      stock_quantity: attr.stock_quantity || 0
    }
  })
}

// تحديث الحقول بناءً على عدد الألوان
const updateColorInputs = () => {
  colorInputs.value = Array.from({ length: colorCount.value }, (_, i) => colorInputs.value[i] || "#000000");
};

// حفظ الألوان في الفورم
const saveColorsToForm = () => {
  form.colors = colorInputs.value;
}

function updateForm() {
  // تحديث form.existing_images من existingImages.value قبل الإرسال
  form.existing_images = [...existingImages.value];
  
  const formData = new FormData();

  // قائمة الحقول المسموح بها فقط (تجنب إرسال methods و properties من useForm)
  const allowedFields = [
    'name', 'name_en', 'slug', 'description', 'short_description',
    'cost', 'profit_margin', 'price', 'sale_price', 'discount_type', 'discount_value',
    'stock_quantity', 'manage_stock', 'sku', 'product_code',
    'weight', 'dimensions', 'category_id', 'brand_id',
    'is_featured', 'status', 'meta_title', 'meta_description'
  ];

  // إضافة الحقول المسموح بها فقط
  allowedFields.forEach(key => {
    const value = form[key];
    if (value !== null && value !== undefined) {
      // تحويل boolean values إلى strings '1' أو '0' أو 'true'/'false'
      if (typeof value === 'boolean') {
        formData.append(key, value ? '1' : '0');
      } else {
        formData.append(key, value);
      }
    }
  });

  // إضافة main_image بشكل منفصل إذا كان File object
  const mainImageToUpload = mainImageFile.value || form.main_image;
  if (mainImageToUpload && mainImageToUpload instanceof File) {
    formData.append('main_image', mainImageToUpload);
  } else {
    // إذا لم يكن هناك ملف جديد، أرسل existing_main_image
    if (form.existing_main_image) {
      formData.append('existing_main_image', form.existing_main_image);
    }
  }

  // إضافة attributes كـ JSON string
  if (form.attributes && Array.isArray(form.attributes)) {
    formData.append('attributes', JSON.stringify(form.attributes));
  } else {
    formData.append('attributes', JSON.stringify([]));
  }

  // إضافة colors كـ JSON string
  if (form.colors && Array.isArray(form.colors)) {
    formData.append('colors', JSON.stringify(form.colors));
  } else {
    formData.append('colors', JSON.stringify([]));
  }

  // إضافة existing_images كـ array في FormData
  // مهم: يجب إرسال existing_images حتى لو كانت فارغة حتى يعرف Backend أن المستخدم حذف جميع الصور
  if (form.existing_images && Array.isArray(form.existing_images)) {
    if (form.existing_images.length > 0) {
      form.existing_images.forEach((image, index) => {
        formData.append(`existing_images[${index}]`, image);
      });
    } else {
      // إرسال array فارغ بشكل صحيح
      formData.append('existing_images', JSON.stringify([]));
    }
  } else {
    // إذا لم تكن array، أرسل array فارغ
    formData.append('existing_images', JSON.stringify([]));
  }

  // إضافة الصور الجديدة إلى FormData
  if (form.images && Array.isArray(form.images) && form.images.length > 0) {
    form.images.forEach((image, index) => {
      formData.append(`images[${index}]`, image);
    });
  }

  // إضافة _method: 'PUT' للـ FormData
  formData.append('_method', 'PUT');

  // استخدام router.post مع _method: 'PUT' لإرسال FormData بشكل صحيح
  router.post(route('admin.products.update', props.product.id), formData, {
    forceFormData: true,
    preserveState: false,
    preserveScroll: false,
    onSuccess: () => {
      Swal.fire('تم التحديث!', 'تم تحديث المنتج بنجاح.', 'success');
    },
    onError: (errors) => {
      Swal.fire('خطأ!', 'حدثت مشكلة أثناء التحديث.', 'error');
    }
  });
}
</script>

<template>
  <Head title="Edit Product" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <form @submit.prevent="updateForm" enctype="multipart/form-data">
        <div class="card-body px-1 px-sm-4">
          <h4>تعديل المنتج: {{ props.product.name }}</h4>
          <Link :href="route('admin.products.index')">
            <i class="fas fa-arrow-left"></i> رجوع
          </Link>
          <hr />

          <div class="row g-4">
            <!-- المعلومات الأساسية -->
            <div class="col-12">
              <h5>المعلومات الأساسية</h5>
            </div>

            <!-- اسم المنتج -->
            <div class="col-md-6">
              <label class="form-label">اسم المنتج (عربي) *</label>
              <input
                class="form-control"
                v-model="form.name"
                type="text"
                placeholder="اكتب اسم المنتج بالعربي"
              />
              <div v-if="form.errors.name" class="text-danger">{{ form.errors.name }}</div>
            </div>

            <!-- اسم المنتج بالإنجليزي -->
            <div class="col-md-6">
              <label class="form-label">اسم المنتج (إنجليزي)</label>
              <input
                class="form-control"
                v-model="form.name_en"
                @input="generateSlug"
                type="text"
                placeholder="Product name in English"
              />
              <div v-if="form.errors.name_en" class="text-danger">{{ form.errors.name_en }}</div>
              <small class="text-muted">ميزة خاصة للعميل - يمكن إخفاؤها لاحقاً</small>
            </div>

            <!-- الرابط الثابت -->
            <div class="col-md-6">
              <label class="form-label">الرابط الثابت (Slug)</label>
              <input
                class="form-control"
                v-model="form.slug"
                type="text"
                placeholder="product-slug"
              />
              <div v-if="form.errors.slug" class="text-danger">{{ form.errors.slug }}</div>
            </div>

            <!-- الوصف المختصر -->
            <div class="col-12">
              <label class="form-label">الوصف المختصر</label>
              <textarea
                class="form-control"
                v-model="form.short_description"
                rows="3"
                placeholder="وصف مختصر للمنتج"
              ></textarea>
              <div v-if="form.errors.short_description" class="text-danger">{{ form.errors.short_description }}</div>
            </div>

            <!-- الوصف الكامل -->
            <div class="col-12">
              <label class="form-label">الوصف الكامل</label>
              <textarea
                class="form-control"
                v-model="form.description"
                rows="5"
                placeholder="الوصف الكامل للمنتج"
              ></textarea>
              <div v-if="form.errors.description" class="text-danger">{{ form.errors.description }}</div>
            </div>

            <!-- التسعير والمخزون -->
            <div class="col-12">
              <h5>التسعير والمخزون</h5>
            </div>

            <!-- التكلفة -->
            <div class="col-md-4">
              <label class="form-label">التكلفة *</label>
              <input
                class="form-control"
                v-model="form.cost"
                type="number"
                step="0.01"
                placeholder="0.00"
              />
              <div v-if="form.errors.cost" class="text-danger">{{ form.errors.cost }}</div>
            </div>

            <!-- هامش الربح -->
            <div class="col-md-4">
              <label class="form-label">هامش الربح (%) *</label>
              <input
                class="form-control"
                v-model="form.profit_margin"
                type="number"
                step="0.01"
                placeholder="0.00"
              />
              <div v-if="form.errors.profit_margin" class="text-danger">{{ form.errors.profit_margin }}</div>
            </div>

            <!-- السعر المحسوب -->
            <div class="col-md-4">
              <label class="form-label">السعر المحسوب</label>
              <input
                class="form-control"
                :value="calculatePrice"
                type="text"
                readonly
                style="background-color: #f8f9fa;"
              />
              <small class="text-muted">السعر = التكلفة × (1 + هامش الربح / 100)</small>
            </div>

            <!-- نوع الخصم -->
            <div class="col-md-4">
              <label class="form-label">نوع الخصم</label>
              <select
                class="form-control"
                v-model="form.discount_type"
                @change="form.discount_value = ''"
              >
                <option value="none">بدون خصم</option>
                <option value="fixed">خصم ثابت</option>
                <option value="percentage">خصم نسبة مئوية</option>
              </select>
              <div v-if="form.errors.discount_type" class="text-danger">{{ form.errors.discount_type }}</div>
            </div>

            <!-- قيمة الخصم -->
            <div class="col-md-4" v-if="form.discount_type !== 'none'">
              <label class="form-label">
                {{ form.discount_type === 'percentage' ? 'نسبة الخصم (%)' : 'مبلغ الخصم ($)' }}
              </label>
              <input
                class="form-control"
                v-model="form.discount_value"
                type="number"
                :step="form.discount_type === 'percentage' ? '1' : '0.01'"
                :placeholder="form.discount_type === 'percentage' ? '0' : '0.00'"
                :max="form.discount_type === 'percentage' ? '100' : undefined"
              />
              <div v-if="form.errors.discount_value" class="text-danger">{{ form.errors.discount_value }}</div>

              <!-- عرض السعر النهائي -->
              <div v-if="calculatePrice && form.discount_value" class="mt-2">
                <small class="text-success">
                  السعر النهائي: ${{ calculateFinalPrice() }}
                </small>
              </div>
            </div>

            <!-- كمية المخزون (فقط إذا لم يكن هناك خصائص) -->
            <div class="col-md-4" v-if="!hasAttributes">
              <label class="form-label">كمية المخزون *</label>
              <input
                class="form-control"
                v-model="form.stock_quantity"
                type="number"
                placeholder="0"
                min="0"
              />
              <div v-if="form.errors.stock_quantity" class="text-danger">{{ form.errors.stock_quantity }}</div>
              <small class="text-muted">يتم إدارة المخزون على مستوى الخصائص إذا كان المنتج له خصائص</small>
            </div>

            <!-- إدارة المخزون -->
            <div class="col-md-6">
              <div class="form-check">
                <input
                  class="form-check-input"
                  type="checkbox"
                  v-model="form.manage_stock"
                  id="manage_stock"
                />
                <label class="form-check-label" for="manage_stock">
                  إدارة المخزون
                </label>
              </div>
            </div>

            <!-- الخصائص -->
            <div class="col-12" v-if="attributes && attributes.length > 0">
              <h5>خصائص المنتج</h5>
            </div>

            <div class="col-12" v-if="attributes && attributes.length > 0">
              <div class="card">
                <div class="card-header">
                  <span>الخصائص المتاحة</span>
                </div>
                <div class="card-body">
                  <div v-if="attributes.length === 0" class="text-muted text-center py-3">
                    لا توجد خصائص متاحة
                  </div>

                  <!-- عرض كل خاصية مع قيمها -->
                  <div v-for="attribute in attributes" :key="attribute.id" class="mb-4 p-3 border rounded">
                    <h6 class="mb-3">{{ attribute.name }}</h6>

                    <div class="row g-2">
                      <div v-for="value in attribute.values" :key="value.id" class="col-md-3">
                        <div class="card h-100">
                          <div class="card-body p-2">
                            <div class="form-check">
                              <input
                                class="form-check-input"
                                type="checkbox"
                                :id="`attr_${attribute.id}_val_${value.id}`"
                                :value="value.id"
                                @change="toggleAttributeValue(attribute.id, value.id, $event.target.checked)"
                                :checked="isAttributeValueSelected(attribute.id, value.id)"
                              />
                              <label class="form-check-label" :for="`attr_${attribute.id}_val_${value.id}`">
                                <strong>{{ value.value }}</strong>
                              </label>
                            </div>

                            <!-- تعديل السعر لهذه القيمة -->
                            <div v-if="isAttributeValueSelected(attribute.id, value.id)" class="mt-2">
                              <label class="form-label small">تعديل السعر ($):</label>
                              <input
                                type="number"
                                step="0.01"
                                class="form-control form-control-sm"
                                :value="getAttributeValuePriceAdjustment(attribute.id, value.id)"
                                @input="updateAttributeValuePrice(attribute.id, value.id, $event.target.value)"
                                placeholder="0.00"
                              />
                            </div>

                            <!-- كمية المخزون لهذه القيمة -->
                            <div v-if="isAttributeValueSelected(attribute.id, value.id)" class="mt-2">
                              <label class="form-label small">كمية المخزون:</label>
                              <input
                                type="number"
                                class="form-control form-control-sm"
                                :value="getAttributeValueStock(attribute.id, value.id)"
                                @input="updateAttributeValueStock(attribute.id, value.id, $event.target.value)"
                                placeholder="0"
                                min="0"
                              />
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- ملخص الخصائص المختارة -->
                  <div v-if="form.attributes.length > 0" class="mt-4 p-3 bg-light rounded">
                    <h6>الخصائص المختارة:</h6>
                    <div class="row g-2">
                      <div v-for="attr in getSelectedAttributesSummary()" :key="`${attr.attribute_id}_${attr.attribute_value_id}`" class="col-auto">
                        <span class="badge bg-primary">
                          {{ attr.attribute_name }}: {{ attr.value_name }}
                          <span v-if="attr.price_adjustment > 0"> (+${{ attr.price_adjustment }})</span>
                          <span v-if="attr.price_adjustment < 0"> (${{ attr.price_adjustment }})</span>
                          <span class="ms-2">المخزون: {{ attr.stock_quantity }}</span>
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>          <!-- رقم المنتج -->
            <div class="col-md-6">
              <label class="form-label">رقم المنتج (SKU)</label>
              <input
                class="form-control"
                v-model="form.sku"
                type="text"
                placeholder="SKU123"
              />
              <div v-if="form.errors.sku" class="text-danger">{{ form.errors.sku }}</div>
            </div>

            <!-- كود المنتج -->
            <div class="col-md-6">
              <label class="form-label">كود المنتج</label>
              <input
                class="form-control"
                v-model="form.product_code"
                type="text"
                placeholder="كود المنتج"
              />
              <div v-if="form.errors.product_code" class="text-danger">{{ form.errors.product_code }}</div>
            </div>

            <!-- الشحن -->
            <div class="col-12">
              <h5>معلومات الشحن</h5>
            </div>

            <!-- الوزن -->
            <div class="col-md-6">
              <label class="form-label">الوزن (كجم)</label>
              <input
                class="form-control"
                v-model="form.weight"
                type="number"
                step="0.01"
                placeholder="0.00"
              />
              <div v-if="form.errors.weight" class="text-danger">{{ form.errors.weight }}</div>
            </div>

            <!-- الأبعاد -->
            <div class="col-md-6">
              <label class="form-label">الأبعاد (الطول × العرض × الارتفاع)</label>
              <input
                class="form-control"
                v-model="form.dimensions"
                type="text"
                placeholder="20 × 15 × 10"
              />
              <div v-if="form.errors.dimensions" class="text-danger">{{ form.errors.dimensions }}</div>
            </div>

            <!-- التصنيف -->
            <div class="col-12">
              <h5>التصنيف</h5>
            </div>

            <!-- القسم -->
            <div class="col-md-6">
              <label class="form-label">القسم</label>
              <select v-model="form.category_id" class="form-select">
                <option :value="null">اختر القسم</option>
                <option
                  v-for="category in props.categories"
                  :key="category.id"
                  :value="category.id"
                >
                  {{ category.name }}
                </option>
              </select>
              <div v-if="form.errors.category_id" class="text-danger">{{ form.errors.category_id }}</div>
              <small class="text-muted">الأقسام معروضة بالتسلسل الهرمي (- رئيسي، -- فرعي)</small>
            </div>

            <!-- العلامة التجارية -->
            <div class="col-md-6">
              <label class="form-label">العلامة التجارية</label>
              <select v-model="form.brand_id" class="form-select">
                <option :value="null">اختر العلامة التجارية</option>
                <option
                  v-for="brand in props.brands"
                  :key="brand.id"
                  :value="brand.id"
                >
                  {{ brand.name }}
                </option>
              </select>
              <div v-if="form.errors.brand_id" class="text-danger">{{ form.errors.brand_id }}</div>
            </div>

            <!-- الصورة الرئيسية -->
            <div class="col-12">
              <h5>الصورة الرئيسية</h5>
            </div>

            <div class="col-12">
              <label class="form-label">الصورة الرئيسية</label>
              <input
                class="form-control"
                type="file"
                accept="image/*"
                @change="handleMainImageUpload"
              />
              <small class="text-muted">الصورة الرئيسية التي ستظهر في صفحة تفاصيل المنتج</small>
              <div v-if="form.errors.main_image" class="text-danger">{{ form.errors.main_image }}</div>
              
              <!-- معاينة الصورة الرئيسية الحالية -->
              <div v-if="mainImagePreview && !form.main_image" class="mt-3">
                <p class="text-muted">الصورة الرئيسية الحالية:</p>
                <img
                  :src="mainImagePreview"
                  class="img-fluid rounded"
                  style="height: 200px; object-fit: cover; width: auto; max-width: 300px;"
                  alt="Current main image"
                />
              </div>
              
              <!-- معاينة الصورة الرئيسية الجديدة -->
              <div v-if="form.main_image && mainImagePreview" class="mt-3">
                <p class="text-muted">الصورة الرئيسية الجديدة:</p>
                <img
                  :src="mainImagePreview"
                  class="img-fluid rounded"
                  style="height: 200px; object-fit: cover; width: auto; max-width: 300px;"
                  alt="New main image preview"
                />
              </div>
            </div>

            <!-- الصور الحالية -->
            <div class="col-12" v-if="existingImages.length">
              <h5>الصور الإضافية الحالية</h5>
              <div class="row g-3">
                <div
                  v-for="(image, index) in existingImages"
                  :key="index"
                  class="col-md-3"
                >
                  <div class="position-relative">
                    <img
                      :src="image"
                      class="img-fluid rounded"
                      style="height: 150px; object-fit: cover; width: 100%;"
                    />
                    <button
                      type="button"
                      class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1"
                      @click="removeExistingImage(index)"
                    >
                      <i class="bi bi-x"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- رفع صور جديدة -->
            <div class="col-12">
              <h5>إضافة صور إضافية جديدة</h5>
            </div>

            <div class="col-12">
              <label class="form-label">رفع الصور الإضافية</label>
              <input
                class="form-control"
                type="file"
                multiple
                accept="image/*"
                @change="handleImageUpload"
              />
              <small class="text-muted">يمكنك رفع عدة صور</small>
              <div v-if="form.errors.images" class="text-danger">{{ form.errors.images }}</div>
            </div>

            <!-- معاينة الصور الجديدة -->
            <div class="col-12" v-if="imagePreview.length">
              <div class="row g-3">
                <div
                  v-for="(preview, index) in imagePreview"
                  :key="index"
                  class="col-md-3"
                >
                  <div class="position-relative">
                    <img
                      :src="preview.url"
                      class="img-fluid rounded"
                      style="height: 150px; object-fit: cover; width: 100%;"
                    />
                    <button
                      type="button"
                      class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1"
                      @click="removeImage(index)"
                    >
                      <i class="bi bi-x"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- الخيارات -->
            <div class="col-12">
              <h5>خيارات إضافية</h5>
            </div>

            <!-- منتج مميز -->
            <div class="col-md-6">
              <div class="form-check">
                <input
                  class="form-check-input"
                  type="checkbox"
                  v-model="form.is_featured"
                  id="is_featured"
                />
                <label class="form-check-label" for="is_featured">
                  منتج مميز
                </label>
              </div>
            </div>

            <!-- حالة المنتج -->
            <div class="col-md-6">
              <div class="form-check">
                <input
                  class="form-check-input"
                  type="checkbox"
                  v-model="form.status"
                  id="status"
                />
                <label class="form-check-label" for="status">
                  نشط
                </label>
              </div>
            </div>

            <!-- SEO -->
            <div class="col-12">
              <h5>تحسين محركات البحث (SEO)</h5>
            </div>

            <!-- عنوان SEO -->
            <div class="col-12">
              <label class="form-label">عنوان الصفحة (Meta Title)</label>
              <input
                class="form-control"
                v-model="form.meta_title"
                type="text"
                placeholder="عنوان المنتج في محركات البحث"
              />
              <div v-if="form.errors.meta_title" class="text-danger">{{ form.errors.meta_title }}</div>
            </div>

            <!-- وصف SEO -->
            <div class="col-12">
              <label class="form-label">وصف الصفحة (Meta Description)</label>
              <textarea
                class="form-control"
                v-model="form.meta_description"
                rows="3"
                placeholder="وصف المنتج في محركات البحث"
              ></textarea>
              <div v-if="form.errors.meta_description" class="text-danger">{{ form.errors.meta_description }}</div>
            </div>

            <!-- عدد الألوان -->
            <div class="col-md-6">
              <label class="form-label">عدد الألوان</label>
              <input
                class="form-control"
                type="number"
                v-model="colorCount"
                @input="updateColorInputs"
                min="0"
                placeholder="أدخل عدد الألوان"
              />
            </div>

            <!-- إدخال الألوان -->
            <div class="col-12" v-if="colorInputs.length > 0">
              <label class="form-label">الألوان</label>
              <div class="row g-2">
                <div class="col-md-3" v-for="(color, index) in colorInputs" :key="index">
                  <input
                    class="form-control form-control-color"
                    type="color"
                    v-model="colorInputs[index]"
                    @change="saveColorsToForm"
                  />
                </div>
              </div>
            </div>

            <!-- زر التحديث -->
            <div class="d-flex justify-content-end mt-3">
              <button
                type="submit"
                class="btn btn-primary mb-0"
                :disabled="form.processing"
              >
                تحديث المنتج
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
