<script setup>
import { ref, computed } from 'vue'
import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import Swal from 'sweetalert2'

const props = defineProps({
  categories: Array,
  brands: Array,
  attributes: Array
})

const form = useForm({
  name: '',
  name_en: '',
  slug: '',
  description: '',
  short_description: '',
  price: '',
  sale_price: '',
  discount_type: 'none',
  discount_value: '',
  stock_quantity: '',
  manage_stock: true,
  sku: '',
  weight: '',
  dimensions: '',
  category_id: null,
  brand_id: null,
  is_featured: false,
  status: true,
  meta_title: '',
  meta_description: '',
  images: [],
  attributes: []
})

const imagePreview = ref([])

// ✅ توليد slug تلقائياً من الاسم
const generateSlug = () => {
  form.slug = form.name
    .toLowerCase()
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
    .trim('-')
}

// ✅ رفع الصور
const handleImageUpload = (event) => {
  const files = Array.from(event.target.files)
  console.log('Selected files:', files)

  files.forEach(file => {
    if (file.type.startsWith('image/')) {
      // إضافة الملف للفورم
      form.images.push(file)
      console.log('Added file to form:', file.name)

      // إنشاء معاينة
      const reader = new FileReader()
      reader.onload = (e) => {
        imagePreview.value.push({
          url: e.target.result,
          file: file
        })
        console.log('Added preview for:', file.name)
      }
      reader.readAsDataURL(file)
    }
  })

  console.log('Total images in form:', form.images.length)
}

// ✅ حذف صورة
const removeImage = (index) => {
  form.images.splice(index, 1)
  imagePreview.value.splice(index, 1)
}

// ✅ حساب السعر النهائي
const calculateFinalPrice = () => {
  if (!form.price || form.discount_type === 'none' || !form.discount_value) {
    return parseFloat(form.price || 0).toFixed(2)
  }

  const price = parseFloat(form.price)
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

// ✅ إدارة الخصائص الجديدة
const toggleAttributeValue = (attributeId, valueId, isChecked) => {
  if (isChecked) {
    // إضافة الخاصية
    form.attributes.push({
      attribute_id: attributeId,
      attribute_value_id: valueId,
      price_adjustment: 0
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

const getSelectedAttributesSummary = () => {
  return form.attributes.map(attr => {
    const attribute = props.attributes.find(a => a.id == attr.attribute_id)
    const value = attribute?.values.find(v => v.id == attr.attribute_value_id)
    return {
      attribute_id: attr.attribute_id,
      attribute_value_id: attr.attribute_value_id,
      attribute_name: attribute?.name || '',
      value_name: value?.value || '',
      price_adjustment: attr.price_adjustment || 0
    }
  })
}

function saveForm() {
  console.log('Form data before submit:', form.data())
  console.log('Images:', form.images)

  form.post(route('admin.products.store'), {
    onSuccess: () => {
      Swal.fire('تم الحفظ!', 'تم إنشاء المنتج بنجاح.', 'success')
    },
    onError: (errors) => {
      console.log('Errors:', errors)
      Swal.fire('خطأ!', 'حدثت مشكلة أثناء الحفظ.', 'error')
    }
  })
}
</script>

<template>
  <Head title="Add Product" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <div class="card-body px-1 px-sm-4">
        <h4>إضافة منتج جديد</h4>
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
              @input="generateSlug"
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

          <!-- السعر الأساسي -->
          <div class="col-md-4">
            <label class="form-label">السعر الأصلي *</label>
            <input
              class="form-control"
              v-model="form.price"
              type="number"
              step="0.01"
              placeholder="0.00"
            />
            <div v-if="form.errors.price" class="text-danger">{{ form.errors.price }}</div>
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
            <div v-if="form.price && form.discount_value" class="mt-2">
              <small class="text-success">
                السعر النهائي: ${{ calculateFinalPrice() }}
              </small>
            </div>
          </div>

          <!-- كمية المخزون -->
          <div class="col-md-4">
            <label class="form-label">كمية المخزون</label>
            <input
              class="form-control"
              v-model="form.stock_quantity"
              type="number"
              placeholder="0"
            />
            <div v-if="form.errors.stock_quantity" class="text-danger">{{ form.errors.stock_quantity }}</div>
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

          <!-- الصور -->
          <div class="col-12">
            <h5>صور المنتج</h5>
          </div>

          <div class="col-12">
            <label class="form-label">رفع الصور</label>
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

          <!-- معاينة الصور -->
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

          <!-- زر الحفظ -->
          <div class="d-flex justify-content-end mt-3">
            <button
              type="button"
              class="btn btn-primary mb-0"
              :disabled="form.processing"
              @click="saveForm"
            >
              حفظ المنتج
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
