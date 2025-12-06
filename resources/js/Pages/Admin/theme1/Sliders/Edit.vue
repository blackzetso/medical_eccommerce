<script setup>
import { ref } from 'vue'
import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import Swal from 'sweetalert2'

const props = defineProps({
  slider: Object
})

const form = useForm({
  description: props.slider.description,
  image: null,
  existing_image: props.slider.image,
  link: props.slider.link,
  sort_order: props.slider.sort_order,
  status: props.slider.status
})

const imagePreview = ref(props.slider.image)

// ✅ رفع صورة جديدة
const handleImageUpload = (event) => {
  const file = event.target.files[0]

  if (file && file.type.startsWith('image/')) {
    form.image = file

    // إنشاء معاينة جديدة
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

// ✅ حذف الصورة الحالية
const removeImage = () => {
  form.image = null
  form.existing_image = null
  imagePreview.value = null

  // إعادة تعيين input file
  const fileInput = document.querySelector('input[type="file"]')
  if (fileInput) fileInput.value = ''
}

// ✅ إرسال النموذج
const submit = () => {
  form.post(route('admin.sliders.update', props.slider.id), {
    forceFormData: true,
    _method: 'PUT',
    onSuccess: () => {
      Swal.fire({
        icon: 'success',
        title: 'تم التحديث',
        text: 'تم تحديث السلايدر بنجاح',
        timer: 2000
      })
    },
    onError: (errors) => {
      Swal.fire({
        icon: 'error',
        title: 'خطأ',
        text: 'حدثت مشكلة أثناء التحديث',
      })
    }
  })
}
</script>

<template>
  <Head title="تعديل سلايدر" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <!-- Title -->
      <div class="row mb-3">
        <div class="col-12">
          <h1 class="h3 mb-2 mb-sm-0">تعديل السلايدر</h1>
        </div>
      </div>

      <div class="row g-4">
        <!-- Main Form -->
        <div class="col-lg-8">
          <div class="card card-body bg-transparent border">
            <form @submit.prevent="submit">
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
                <label class="form-label">الصورة</label>

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
                  <div class="mt-2">
                    <small class="text-muted">
                      <i class="bi bi-info-circle me-1"></i>
                      {{ form.image ? 'صورة جديدة محددة' : 'الصورة الحالية' }}
                    </small>
                  </div>
                </div>

                <!-- رفع صورة جديدة -->
                <input
                  type="file"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors.image }"
                  @change="handleImageUpload"
                  accept="image/*"
                />
                <small class="text-muted">اترك الحقل فارغاً للاحتفاظ بالصورة الحالية. الحجم المثالي: 1920x600 بكسل</small>
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
                    جاري التحديث...
                  </span>
                  <span v-else>
                    <i class="bi bi-check2 me-2"></i>
                    تحديث السلايدر
                  </span>
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Sidebar - معلومات السلايدر -->
        <div class="col-lg-4">
          <div class="card card-body bg-light border">
            <h5 class="mb-3">
              <i class="bi bi-info-circle me-2"></i>
              معلومات السلايدر
            </h5>
            <ul class="list-unstyled">
              <li class="mb-2">
                <strong>تاريخ الإنشاء:</strong><br>
                <small class="text-muted">{{ slider.created_at }}</small>
              </li>
              <li class="mb-2">
                <strong>آخر تحديث:</strong><br>
                <small class="text-muted">{{ slider.updated_at }}</small>
              </li>
              <li class="mb-2">
                <strong>الحالة:</strong><br>
                <span class="badge" :class="slider.status ? 'bg-success' : 'bg-secondary'">
                  {{ slider.status ? 'نشط' : 'غير نشط' }}
                </span>
              </li>
            </ul>

            <hr>

            <h6 class="mb-2">
              <i class="bi bi-lightbulb me-2"></i>
              نصائح للتعديل
            </h6>
            <ul class="list-unstyled small">
              <li class="mb-2">
                <i class="bi bi-check-circle text-success me-2"></i>
                يمكنك تغيير الصورة أو الإبقاء على الحالية
              </li>
              <li class="mb-2">
                <i class="bi bi-check-circle text-success me-2"></i>
                تأكد من ترتيب السلايدرز بشكل صحيح
              </li>
              <li class="mb-2">
                <i class="bi bi-check-circle text-success me-2"></i>
                قم بتعطيل السلايدرز القديمة بدلاً من حذفها
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
