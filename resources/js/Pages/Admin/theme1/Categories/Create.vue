<script setup>
import { ref } from 'vue'
import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import Swal from 'sweetalert2'
import CategoryOptions from './CategoryOptions.vue'

const props = defineProps({
  categories: Array
})

const form = useForm({
  name: '',
  parent_id: null,
  image: null
})

const imagePreview = ref(null)

function handleImageChange(event) {
  const file = event.target.files[0]
  if (file) {
    form.image = file
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

function saveForm() {
  form.post(route('admin.categories.store'), {
    onSuccess: () => {
      Swal.fire('تم الحفظ!', 'تم إنشاء القسم.', 'success')
    },
    onError: () => {
      Swal.fire('خطأ!', 'حدثت مشكلة أثناء الحفظ.', 'error')
    }
  })
}
</script>

<template>
  <Head title="Add Category" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <div class="card-body px-1 px-sm-4">
        <h4>إضافة قسم جديد</h4>
        <Link :href="route('admin.categories.index')">
          <i class="fas fa-arrow-left"></i> رجوع
        </Link>
        <hr />

        <div class="row g-4">
          <!-- اسم القسم -->
          <div class="col-12">
            <label class="form-label">اسم القسم</label>
            <input
              class="form-control"
              v-model="form.name"
              type="text"
              placeholder="اكتب اسم القسم"
            />
            <div v-if="form.errors.name" class="text-danger">{{ form.errors.name }}</div>
          </div>

          <!-- اختيار القسم الأب -->
          <div class="col-12">
            <label class="form-label">القسم الرئيسي (اختياري)</label>
            <select v-model="form.parent_id" class="form-select">
              <option :value="null">قسم رئيسي</option>
              <CategoryOptions :categories="props.categories" />
            </select>
            <div v-if="form.errors.parent_id" class="text-danger">{{ form.errors.parent_id }}</div>
          </div>

          <!-- رفع الصورة -->
          <div class="col-12">
            <label class="form-label">صورة القسم (اختياري)</label>
            <input
              type="file"
              class="form-control"
              accept="image/*"
              @change="handleImageChange"
            />
            <div v-if="form.errors.image" class="text-danger">{{ form.errors.image }}</div>

            <!-- معاينة الصورة -->
            <div v-if="imagePreview" class="mt-3">
              <img :src="imagePreview" alt="معاينة الصورة" class="img-thumbnail" style="max-width: 200px; max-height: 200px;" />
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
              حفظ القسم
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
