<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { toast } from 'vue3-toastify'
import Swal from 'sweetalert2'

const page = usePage()

const props = defineProps({
  categories: Array,
  brands: Array
})

const form = useForm({
  csv_file: null
})

const importResults = ref(null)
const showInstructions = ref(true)

// Watch for flash messages and import results
import { watch } from 'vue'

// Check if there are import results from the previous request
onMounted(() => {
  checkFlashMessages()
})

// Watch for flash messages changes
watch(() => page.props.flash, (flash) => {
  checkFlashMessages()
}, { deep: true })

function checkFlashMessages() {
  if (page.props.flash?.import_results) {
    importResults.value = page.props.flash.import_results
    showInstructions.value = false
    
    // Debug: Log import results
    console.log('Import Results:', importResults.value)
    console.log('Errors:', importResults.value?.errors)
    
    // Show toast with summary
    const results = page.props.flash.import_results
    if (results.success > 0 || results.updated > 0) {
      toast.success(`تم استيراد ${results.success} منتج جديد وتحديث ${results.updated} منتج`, {
        position: "top-right",
        autoClose: 5000,
      })
    }
    if (results.failed > 0) {
      const errorCount = results.errors?.length || 0
      const errorMsg = errorCount > 0 
        ? `فشل استيراد ${results.failed} منتج (${errorCount} خطأ)` 
        : `فشل استيراد ${results.failed} منتج`
      toast.warning(errorMsg, {
        position: "top-right",
        autoClose: 7000,
      })
    }
  }
  
  if (page.props.flash?.success) {
    toast.success(page.props.flash.success, {
      position: "top-right",
      autoClose: 5000,
    })
  }
  
  if (page.props.flash?.error) {
    toast.error(page.props.flash.error, {
      position: "top-right",
      autoClose: 5000,
    })
  }
}

// Handle file selection
const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (file) {
    form.csv_file = file
  }
}

// Download template
const downloadTemplate = () => {
  window.location.href = route('admin.products.import.template')
}

// Submit import
const submitImport = () => {
  if (!form.csv_file) {
    Swal.fire('تحذير!', 'يرجى اختيار ملف CSV للاستيراد', 'warning')
    return
  }

  form.post(route('admin.products.import.process'), {
    forceFormData: true,
    onSuccess: (page) => {
      // Results will be in flash message
      showInstructions.value = false
      
      // Check for results in response
      if (page.props.flash?.import_results) {
        importResults.value = page.props.flash.import_results
      }
    },
    onError: (errors) => {
      console.error('Import errors:', errors)
      let errorMessage = 'حدثت مشكلة أثناء الاستيراد'
      if (errors.csv_file) {
        errorMessage = errors.csv_file
      } else if (errors.message) {
        errorMessage = errors.message
      }
      Swal.fire('خطأ!', errorMessage, 'error')
    }
  })
}
</script>

<template>
  <Head title="استيراد المنتجات" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <div class="card-body px-1 px-sm-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4>استيراد المنتجات من CSV</h4>
          <Link :href="route('admin.products.index')" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> رجوع
          </Link>
        </div>
        <hr />

        <!-- Import Results -->
        <div v-if="importResults" class="mb-4">
          <div :class="importResults.failed > 0 ? 'alert alert-warning' : 'alert alert-success'">
            <h5 class="alert-heading">نتائج الاستيراد</h5>
            <ul class="mb-0">
              <li>تم إضافة <strong>{{ importResults.success }}</strong> منتج جديد</li>
              <li>تم تحديث <strong>{{ importResults.updated }}</strong> منتج موجود</li>
              <li v-if="importResults.failed > 0" class="text-danger">
                فشل استيراد <strong>{{ importResults.failed }}</strong> منتج
              </li>
            </ul>
          </div>
          
          <div v-if="importResults.errors && importResults.errors.length > 0" class="alert alert-danger mt-3">
            <h6 class="alert-heading">
              <i class="bi bi-exclamation-triangle me-2"></i>
              تفاصيل الأخطاء ({{ importResults.errors.length }}):
            </h6>
            <ul class="list-unstyled mb-0">
              <li v-for="(error, index) in importResults.errors" :key="index" class="mb-2">
                <i class="bi bi-x-circle-fill text-danger me-2"></i>
                <span>{{ error }}</span>
              </li>
            </ul>
          </div>
          
          <div v-else-if="importResults.failed > 0" class="alert alert-warning mt-3">
            <h6 class="alert-heading">تحذير</h6>
            <p class="mb-0">
              فشل استيراد {{ importResults.failed }} منتج لكن لم يتم تسجيل تفاصيل الأخطاء. 
              يرجى التحقق من ملف الـ logs أو التحقق من صحة البيانات في ملف CSV.
            </p>
          </div>
        </div>

        <!-- Instructions Card -->
        <div v-if="showInstructions" class="card mb-4">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
              <i class="bi bi-info-circle me-2"></i>
              تعليمات الاستيراد
            </h5>
          </div>
          <div class="card-body">
            <div class="alert alert-warning">
              <strong>ملاحظة مهمة:</strong> سيتم إضافة المنتجات بدون صور. يمكنك إضافة الصور لاحقاً من صفحة تعديل المنتج.
            </div>

            <h6 class="mb-3">خطوات الاستيراد:</h6>
            <ol>
              <li>قم بتحميل قالب CSV الجاهز من الزر أدناه</li>
              <li>املأ القالب بالبيانات المطلوبة (راجع الجدول أدناه للحقول المطلوبة)</li>
              <li>احفظ الملف بصيغة CSV</li>
              <li>ارفع الملف من خلال النموذج أدناه</li>
            </ol>

            <h6 class="mb-3 mt-4">الحقول المطلوبة والاختيارية:</h6>
            <div class="table-responsive">
              <table class="table table-bordered table-sm">
                <thead class="table-light">
                  <tr>
                    <th>اسم الحقل</th>
                    <th>النوع</th>
                    <th>الوصف</th>
                    <th>مثال</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><code>name</code></td>
                    <td><span class="badge bg-danger">مطلوب</span></td>
                    <td>اسم المنتج بالعربية</td>
                    <td>منتج تجريبي</td>
                  </tr>
                  <tr>
                    <td><code>name_en</code></td>
                    <td><span class="badge bg-secondary">اختياري</span></td>
                    <td>اسم المنتج بالإنجليزية</td>
                    <td>Test Product</td>
                  </tr>
                  <tr>
                    <td><code>slug</code></td>
                    <td><span class="badge bg-danger">مطلوب</span></td>
                    <td>رابط المنتج (يجب أن يكون فريداً)</td>
                    <td>test-product</td>
                  </tr>
                  <tr>
                    <td><code>description</code></td>
                    <td><span class="badge bg-secondary">اختياري</span></td>
                    <td>الوصف الكامل للمنتج</td>
                    <td>وصف المنتج...</td>
                  </tr>
                  <tr>
                    <td><code>short_description</code></td>
                    <td><span class="badge bg-secondary">اختياري</span></td>
                    <td>وصف مختصر</td>
                    <td>وصف مختصر...</td>
                  </tr>
                  <tr>
                    <td><code>price</code></td>
                    <td><span class="badge bg-danger">مطلوب</span></td>
                    <td>السعر الأصلي (رقم)</td>
                    <td>100</td>
                  </tr>
                  <tr>
                    <td><code>sale_price</code></td>
                    <td><span class="badge bg-secondary">اختياري</span></td>
                    <td>سعر البيع (سيتم حسابه تلقائياً عند وجود خصم)</td>
                    <td>80</td>
                  </tr>
                  <tr>
                    <td><code>discount_type</code></td>
                    <td><span class="badge bg-secondary">اختياري</span></td>
                    <td>نوع الخصم: <code>none</code> أو <code>fixed</code> أو <code>percentage</code></td>
                    <td>percentage</td>
                  </tr>
                  <tr>
                    <td><code>discount_value</code></td>
                    <td><span class="badge bg-secondary">اختياري</span></td>
                    <td>قيمة الخصم (رقم)</td>
                    <td>20</td>
                  </tr>
                  <tr>
                    <td><code>stock_quantity</code></td>
                    <td><span class="badge bg-danger">مطلوب</span></td>
                    <td>الكمية في المخزون (رقم صحيح)</td>
                    <td>50</td>
                  </tr>
                  <tr>
                    <td><code>manage_stock</code></td>
                    <td><span class="badge bg-secondary">اختياري</span></td>
                    <td>إدارة المخزون: <code>true</code> أو <code>false</code></td>
                    <td>true</td>
                  </tr>
                  <tr>
                    <td><code>sku</code></td>
                    <td><span class="badge bg-secondary">اختياري</span></td>
                    <td>رمز المنتج (فريد، إذا كان موجوداً سيتم تحديث المنتج)</td>
                    <td>SKU-001</td>
                  </tr>
                  <tr>
                    <td><code>weight</code></td>
                    <td><span class="badge bg-secondary">اختياري</span></td>
                    <td>الوزن بالكيلوجرام (رقم)</td>
                    <td>1.5</td>
                  </tr>
                  <tr>
                    <td><code>dimensions</code></td>
                    <td><span class="badge bg-secondary">اختياري</span></td>
                    <td>الأبعاد (نص)</td>
                    <td>10x10x5</td>
                  </tr>
                  <tr>
                    <td><code>category_id</code></td>
                    <td><span class="badge bg-secondary">اختياري</span></td>
                    <td>رقم القسم (راجع الجدول أدناه)</td>
                    <td>1</td>
                  </tr>
                  <tr>
                    <td><code>brand_id</code></td>
                    <td><span class="badge bg-secondary">اختياري</span></td>
                    <td>رقم العلامة التجارية (راجع الجدول أدناه)</td>
                    <td>1</td>
                  </tr>
                  <tr>
                    <td><code>is_featured</code></td>
                    <td><span class="badge bg-secondary">اختياري</span></td>
                    <td>منتج مميز: <code>true</code> أو <code>false</code></td>
                    <td>false</td>
                  </tr>
                  <tr>
                    <td><code>status</code></td>
                    <td><span class="badge bg-secondary">اختياري</span></td>
                    <td>حالة المنتج: <code>true</code> أو <code>false</code></td>
                    <td>true</td>
                  </tr>
                  <tr>
                    <td><code>meta_title</code></td>
                    <td><span class="badge bg-secondary">اختياري</span></td>
                    <td>عنوان SEO</td>
                    <td>عنوان SEO</td>
                  </tr>
                  <tr>
                    <td><code>meta_description</code></td>
                    <td><span class="badge bg-secondary">اختياري</span></td>
                    <td>وصف SEO</td>
                    <td>وصف SEO</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="alert alert-info mt-3">
              <h6 class="mb-2">ملاحظات مهمة:</h6>
              <ul class="mb-0">
                <li>القيم المنطقية (true/false) يجب أن تكون نصية: <code>true</code> أو <code>false</code></li>
                <li>إذا كان المنتج موجوداً بنفس SKU، سيتم تحديثه بدلاً من إنشاء منتج جديد</li>
                <li>الرابط (slug) يجب أن يكون فريداً، سيتم تعديله تلقائياً إذا كان مكرراً</li>
                <li>سيتم حساب سعر البيع تلقائياً عند وجود خصم</li>
              </ul>
            </div>

            <!-- Categories Table -->
            <div v-if="categories && categories.length > 0" class="mt-4">
              <h6 class="mb-2">الأقسام المتاحة:</h6>
              <div class="table-responsive">
                <table class="table table-bordered table-sm">
                  <thead class="table-light">
                    <tr>
                      <th>رقم القسم (category_id)</th>
                      <th>اسم القسم</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="category in categories" :key="category.id">
                      <td><code>{{ category.id }}</code></td>
                      <td>{{ category.name }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Brands Table -->
            <div v-if="brands && brands.length > 0" class="mt-4">
              <h6 class="mb-2">العلامات التجارية المتاحة:</h6>
              <div class="table-responsive">
                <table class="table table-bordered table-sm">
                  <thead class="table-light">
                    <tr>
                      <th>رقم العلامة (brand_id)</th>
                      <th>اسم العلامة التجارية</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="brand in brands" :key="brand.id">
                      <td><code>{{ brand.id }}</code></td>
                      <td>{{ brand.name }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Import Form -->
        <div class="card">
          <div class="card-header bg-success text-white">
            <h5 class="mb-0">
              <i class="bi bi-upload me-2"></i>
              رفع ملف CSV
            </h5>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <button 
                @click="downloadTemplate" 
                class="btn btn-primary mb-3"
                type="button"
              >
                <i class="bi bi-download me-2"></i>
                تحميل قالب CSV
              </button>
            </div>

            <form @submit.prevent="submitImport">
              <div class="mb-3">
                <label for="csv_file" class="form-label">اختر ملف CSV</label>
                <input
                  id="csv_file"
                  type="file"
                  class="form-control"
                  accept=".csv,.txt"
                  @change="handleFileSelect"
                  required
                />
                <div class="form-text">الحد الأقصى لحجم الملف: 10MB</div>
                <div v-if="form.errors.csv_file" class="text-danger mt-1">
                  {{ form.errors.csv_file }}
                </div>
              </div>

              <div v-if="form.csv_file" class="alert alert-info">
                <i class="bi bi-file-earmark-spreadsheet me-2"></i>
                الملف المحدد: <strong>{{ form.csv_file.name }}</strong>
              </div>

              <div class="d-flex justify-content-end gap-2">
                <Link 
                  :href="route('admin.products.index')" 
                  class="btn btn-secondary"
                >
                  إلغاء
                </Link>
                <button 
                  type="submit" 
                  class="btn btn-success"
                  :disabled="form.processing || !form.csv_file"
                >
                  <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
                  <i v-else class="bi bi-upload me-2"></i>
                  {{ form.processing ? 'جاري الاستيراد...' : 'بدء الاستيراد' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

