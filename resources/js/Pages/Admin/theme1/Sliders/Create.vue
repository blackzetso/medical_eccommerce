<script setup>
import { ref } from 'vue'
import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import Swal from 'sweetalert2'

const form = useForm({
  title: '',
  description: '',
  image: null,
  link: '',
  sort_order: 0,
  status: true
})

const imagePreview = ref(null)

// ✅ رفع الصورة
const handleImageUpload = (event) => {
  const file = event.target.files[0]

  if (file && file.type.startsWith('image/')) {
    form.image = file

    // إنشاء معاينة
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

// ✅ حذف الصورة
const removeImage = () => {
  form.image = null
  imagePreview.value = null
  // إعادة تعيين input file
  const fileInput = document.querySelector('input[type="file"]')
  if (fileInput) fileInput.value = ''
}

// ✅ إرسال النموذج
const submit = () => {
  form.post(route('admin.sliders.store'), {
    onSuccess: () => {
      Swal.fire({
        icon: 'success',
        title: 'تم الحفظ',
        text: 'تم إنشاء السلايدر بنجاح',
        timer: 2000
      })
    },
    onError: (errors) => {
      Swal.fire({
        icon: 'error',
        title: 'خطأ',
        text: 'حدثت مشكلة أثناء الحفظ',
      })
    }
  })
}
</script>

<template>
  <Head title="إضافة سلايدر" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <!-- Title -->
      <div class="row mb-3">
        <div class="col-12">
          <h1 class="h3 mb-2 mb-sm-0">إضافة سلايدر جديد</h1>
        </div>
      </div>

      <div class="row g-4">
        <!-- Main Form -->
        <div class="col-lg-8">
          <div class="card card-body bg-transparent border">
            <form @submit.prevent="submit">
              <!-- العنوان -->
              <div class="mb-3">
                <label class="form-label">العنوان <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control"
                  v-model="form.title"
                  :class="{ 'is-invalid': form.errors.title }"
                  placeholder="أدخل عنوان السلايدر"
                  required
                />
                <div class="invalid-feedback" v-if="form.errors.title">
                  {{ form.errors.title }}
                </div>
              </div>

              <!-- الوصف -->
              <div class="mb-3">
                <label class="form-label">الوصف</label>
                <textarea
                  class="form-control"
                  v-model="form.description"
                  :class="{ 'is-invalid': form.errors.description }"
                  rows="4"
                  placeholder="أدخل وصف السلايدر (اختياري)"
                ></textarea>
                <div class="invalid-feedback" v-if="form.errors.description">
                  {{ form.errors.description }}
                </div>
              </div>

              <!-- الرابط -->
              <div class="mb-3">
                <label class="form-label">الرابط</label>
                <input
                  type="url"
                  class="form-control"
                  v-model="form.link"
                  :class="{ 'is-invalid': form.errors.link }"
                  placeholder="https://example.com (اختياري)"
                />
                <small class="text-muted">عند الضغط على الصورة سيتم الانتقال لهذا الرابط</small>
                <div class="invalid-feedback" v-if="form.errors.link">
                  {{ form.errors.link }}
                </div>
              </div>

              <!-- الترتيب -->
              <div class="mb-3">
                <label class="form-label">ترتيب العرض <span class="text-danger">*</span></label>
                <input
                  type="number"
                  class="form-control"
                  v-model="form.sort_order"
                  :class="{ 'is-invalid': form.errors.sort_order }"
                  min="0"
                  required
                />
                <small class="text-muted">الرقم الأصغر يظهر أولاً</small>
                <div class="invalid-feedback" v-if="form.errors.sort_order">
                  {{ form.errors.sort_order }}
                </div>
              </div>

              <!-- الصورة -->
              <div class="mb-3">
                <label class="form-label">الصورة <span class="text-danger">*</span></label>

                <!-- معاينة الصورة -->
                <div v-if="imagePreview" class="mb-3 position-relative" style="max-width: 400px;">
                  <img
                    :src="imagePreview"
                    alt="Preview"
                    class="img-fluid rounded border"
                  />
                  <button
                    type="button"
                    class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2"
                    @click="removeImage"
                  >
                    <i class="bi bi-x"></i>
                  </button>
                </div>

                <!-- رفع الصورة -->
                <input
                  type="file"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors.image }"
                  @change="handleImageUpload"
                  accept="image/*"
                  required
                />
                <small class="text-muted">الحجم المثالي: 1920x600 بكسل</small>
                <div class="invalid-feedback" v-if="form.errors.image">
                  {{ form.errors.image }}
                </div>
              </div>

              <!-- الحالة -->
              <div class="mb-3">
                <div class="form-check form-switch">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    id="status"
                    v-model="form.status"
                  />
                  <label class="form-check-label" for="status">
                    نشط
                  </label>
                </div>
              </div>

              <!-- الأزرار -->
              <div class="d-flex justify-content-between">
                <Link :href="route('admin.sliders.index')" class="btn btn-secondary">
                  <i class="bi bi-arrow-left me-2"></i>
                  رجوع
                </Link>

                <button
                  type="submit"
                  class="btn btn-primary"
                  :disabled="form.processing"
                >
                  <span v-if="form.processing">
                    <span class="spinner-border spinner-border-sm me-2"></span>
                    جاري الحفظ...
                  </span>
                  <span v-else>
                    <i class="bi bi-check2 me-2"></i>
                    حفظ السلايدر
                  </span>
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Sidebar - نصائح -->
        <div class="col-lg-4">
          <div class="card card-body bg-light border">
            <h5 class="mb-3">
              <i class="bi bi-info-circle me-2"></i>
              نصائح لإنشاء سلايدر جيد
            </h5>
            <ul class="list-unstyled">
              <li class="mb-2">
                <i class="bi bi-check-circle text-success me-2"></i>
                استخدم صور عالية الجودة (1920x600 بكسل)
              </li>
              <li class="mb-2">
                <i class="bi bi-check-circle text-success me-2"></i>
                أضف رابطاً للانتقال عند الضغط على الصورة
              </li>
              <li class="mb-2">
                <i class="bi bi-check-circle text-success me-2"></i>
                رتب السلايدرز بشكل منطقي
              </li>
              <li class="mb-2">
                <i class="bi bi-check-circle text-success me-2"></i>
                لا تستخدم أكثر من 5 سلايدرز نشطة
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
