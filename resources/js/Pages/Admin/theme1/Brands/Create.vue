<script setup>
import { ref } from 'vue'
import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import Swal from 'sweetalert2'

const form = useForm({
  name: '',
  slug: '',
  description: '',
  website: '',
  logo: null,
  status: true
})

const logoPreview = ref(null)

// ✅ توليد slug تلقائياً من الاسم
const generateSlug = () => {
  form.slug = form.name
    .toLowerCase()
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
    .trim('-')
}

// ✅ رفع اللوجو
const handleLogoUpload = (event) => {
  const file = event.target.files[0]
  if (file && file.type.startsWith('image/')) {
    form.logo = file
    
    // إنشاء معاينة
    const reader = new FileReader()
    reader.onload = (e) => {
      logoPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

// ✅ حذف اللوجو
const removeLogo = () => {
  form.logo = null
  logoPreview.value = null
  // مسح input file
  document.querySelector('input[type="file"]').value = ''
}

function saveForm() {
  form.post(route('admin.brands.store'), {
    onSuccess: () => {
      Swal.fire('تم الحفظ!', 'تم إنشاء العلامة التجارية بنجاح.', 'success')
    },
    onError: () => {
      Swal.fire('خطأ!', 'حدثت مشكلة أثناء الحفظ.', 'error')
    }
  })
}
</script>

<template>
  <Head title="Add Brand" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <div class="card-body px-1 px-sm-4">
        <h4>إضافة علامة تجارية جديدة</h4>
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

          <!-- رفع اللوجو -->
          <div class="col-12">
            <label class="form-label">اللوجو</label>
            <input
              class="form-control"
              type="file"
              accept="image/*"
              @change="handleLogoUpload"
            />
            <small class="text-muted">يُفضل أن يكون اللوجو مربع الشكل</small>
            <div v-if="form.errors.logo" class="text-danger">{{ form.errors.logo }}</div>
          </div>

          <!-- معاينة اللوجو -->
          <div class="col-12" v-if="logoPreview">
            <div class="position-relative d-inline-block">
              <img 
                :src="logoPreview" 
                class="img-fluid rounded border" 
                style="max-width: 200px; max-height: 200px; object-fit: contain;"
              />
              <button
                type="button"
                class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1"
                @click="removeLogo"
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
          
          <!-- زر الحفظ -->
          <div class="d-flex justify-content-end mt-3">
            <button
              type="button"
              class="btn btn-primary mb-0"
              :disabled="form.processing"
              @click="saveForm"
            >
              حفظ العلامة التجارية
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>