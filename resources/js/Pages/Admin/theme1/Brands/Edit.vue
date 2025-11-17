<script setup>
import { ref } from 'vue'
import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import Swal from 'sweetalert2'

const props = defineProps({
  brand: Object
})

const form = useForm({
  name: props.brand.name,
  slug: props.brand.slug,
  description: props.brand.description,
  website: props.brand.website,
  logo: null,
  existing_logo: props.brand.logo,
  status: props.brand.status
})

const logoPreview = ref(null)
const showExistingLogo = ref(!!props.brand.logo)

// ✅ توليد slug تلقائياً من الاسم
const generateSlug = () => {
  form.slug = form.name
    .toLowerCase()
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
    .trim('-')
}

// ✅ رفع اللوجو الجديد
const handleLogoUpload = (event) => {
  const file = event.target.files[0]
  if (file && file.type.startsWith('image/')) {
    form.logo = file
    showExistingLogo.value = false

    // إنشاء معاينة
    const reader = new FileReader()
    reader.onload = (e) => {
      logoPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

// ✅ حذف اللوجو الجديد
const removeNewLogo = () => {
  form.logo = null
  logoPreview.value = null
  document.querySelector('input[type="file"]').value = ''

  // إذا كان في لوجو موجود، اعرضه
  if (form.existing_logo) {
    showExistingLogo.value = true
  }
}

// ✅ حذف اللوجو الموجود
const removeExistingLogo = () => {
  form.existing_logo = null
  showExistingLogo.value = false
}

function updateForm() {
  form.put(route('admin.brands.update', props.brand.id), {
    onSuccess: () => {
      Swal.fire('تم التحديث!', 'تم تحديث العلامة التجارية بنجاح.', 'success')
    },
    onError: () => {
      Swal.fire('خطأ!', 'حدثت مشكلة أثناء التحديث.', 'error')
    }
  })
}
</script>

<template>
  <Head title="Edit Brand" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <div class="card-body px-1 px-sm-4">
        <h4>تعديل العلامة التجارية: {{ props.brand.name }}</h4>
        <Link :href="route('admin.brands.index')">
          <i class="fas fa-arrow-left"></i> رجوع
        </Link>
        <hr />

        <div class="row g-4">
          <!-- اسم العلامة التجارية -->
          <div class="col-md-6">
            <label class="form-label">اسم العلامة التجارية *</label>
            <input
              class="form-control"
              v-model="form.name"
              @input="generateSlug"
              type="text"
              placeholder="اكتب اسم العلامة التجارية"
            />
            <div v-if="form.errors.name" class="text-danger">{{ form.errors.name }}</div>
          </div>

          <!-- الرابط الثابت -->
          <div class="col-md-6">
            <label class="form-label">الرابط الثابت (Slug)</label>
            <input
              class="form-control"
              v-model="form.slug"
              type="text"
              placeholder="brand-slug"
            />
            <div v-if="form.errors.slug" class="text-danger">{{ form.errors.slug }}</div>
          </div>

          <!-- الوصف -->
          <div class="col-12">
            <label class="form-label">الوصف</label>
            <textarea
              class="form-control"
              v-model="form.description"
              rows="4"
              placeholder="وصف العلامة التجارية"
            ></textarea>
            <div v-if="form.errors.description" class="text-danger">{{ form.errors.description }}</div>
          </div>

          <!-- الموقع الإلكتروني -->
          <div class="col-12">
            <label class="form-label">الموقع الإلكتروني</label>
            <input
              class="form-control"
              v-model="form.website"
              type="url"
              placeholder="https://example.com"
            />
            <div v-if="form.errors.website" class="text-danger">{{ form.errors.website }}</div>
          </div>

          <!-- اللوجو الحالي -->
          <div class="col-12" v-if="showExistingLogo && form.existing_logo">
            <label class="form-label">اللوجو الحالي</label>
            <div class="position-relative d-inline-block">
              <img
                :src="form.existing_logo"
                class="img-fluid rounded border"
                style="max-width: 200px; max-height: 200px; object-fit: contain;"
              />
              <button
                type="button"
                class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1"
                @click="removeExistingLogo"
              >
                <i class="bi bi-x"></i>
              </button>
            </div>
          </div>

          <!-- رفع لوجو جديد -->
          <div class="col-12">
            <label class="form-label">
              {{ showExistingLogo && form.existing_logo ? 'تغيير اللوجو' : 'رفع اللوجو' }}
            </label>
            <input
              class="form-control"
              type="file"
              accept="image/*"
              @change="handleLogoUpload"
            />
            <small class="text-muted">يُفضل أن يكون اللوجو مربع الشكل</small>
            <div v-if="form.errors.logo" class="text-danger">{{ form.errors.logo }}</div>
          </div>

          <!-- معاينة اللوجو الجديد -->
          <div class="col-12" v-if="logoPreview">
            <label class="form-label">معاينة اللوجو الجديد</label>
            <div class="position-relative d-inline-block">
              <img
                :src="logoPreview"
                class="img-fluid rounded border"
                style="max-width: 200px; max-height: 200px; object-fit: contain;"
              />
              <button
                type="button"
                class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1"
                @click="removeNewLogo"
              >
                <i class="bi bi-x"></i>
              </button>
            </div>
          </div>

          <!-- حالة العلامة التجارية -->
          <div class="col-12">
            <div class="form-check">
              <input
                class="form-check-input"
                type="checkbox"
                v-model="form.status"
                id="status"
              />
              <label class="form-check-label" for="status">
                نشطة
              </label>
            </div>
          </div>

          <!-- زر التحديث -->
          <div class="d-flex justify-content-end mt-3">
            <button
              type="button"
              class="btn btn-primary mb-0"
              :disabled="form.processing"
              @click="updateForm"
            >
              تحديث العلامة التجارية
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
