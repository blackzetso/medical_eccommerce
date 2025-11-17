<script setup>
import { ref } from 'vue'
import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'
import { Head, useForm, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import Swal from 'sweetalert2'
import CategoryOptions from './CategoryOptions.vue'

const props = defineProps({
  category: Object,   // القسم الحالي
  categories: Array   // الأقسام كلها (متدرجة مع children)
})

const form = useForm({
  name: props.category.name ?? '',
  parent_id: props.category.parent_id ?? null,
  image: null
})

const imagePreview = ref(props.category.image ? '/' + props.category.image : null)

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

function submit() {
  form.post(route('admin.categories.update', props.category.id), {
    forceFormData: true,
    onSuccess: () => {
      Swal.fire({
        title: 'تم الحفظ!',
        text: 'تم حفظ القسم بنجاح',
        icon: 'success',
        confirmButtonText: 'تمام'
      })
    },
    onError: () => {
      Swal.fire({
        title: 'خطأ!',
        text: 'حدثت مشكلة أثناء الحفظ',
        icon: 'error',
        confirmButtonText: 'موافق'
      })
    }
  })
}
</script>

<template>
  <Head title="Edit Category" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <div class="card-body px-1 px-sm-4">
        <form @submit.prevent="submit">
          <h4>Edit Category</h4>
          <Link :href="route('admin.categories.index')">
            <i class="fas fa-arrow-left"></i> رجوع
          </Link>
          <hr />

          <div class="row g-4">
            <!-- اسم القسم -->
            <div class="col-12">
              <label class="form-label">Category Name</label>
              <input
                class="form-control"
                v-model="form.name"
                type="text"
                placeholder="Enter category name"
              />
              <div v-if="form.errors.name" class="text-danger">
                {{ form.errors.name }}
              </div>
            </div>

            <!-- القسم الأب -->
            <div class="col-12">
              <label class="form-label">Parent Category (اختياري)</label>
              <select v-model="form.parent_id" class="form-select">
                <option value="">قسم رئيسي</option>
                <CategoryOptions
                  :categories="props.categories"
                  :current-id="props.category.id"
                />
              </select>
              <div v-if="form.errors.parent_id" class="text-danger">
                {{ form.errors.parent_id }}
              </div>
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
                <p class="mb-2">{{ form.image ? 'الصورة الجديدة:' : 'الصورة الحالية:' }}</p>
                <img :src="imagePreview" alt="معاينة الصورة" class="img-thumbnail" style="max-width: 200px; max-height: 200px;" />
              </div>
            </div>

            <!-- زر الحفظ -->
            <div class="d-flex justify-content-end mt-3">
              <button
                type="submit"
                class="btn btn-primary"
                :disabled="form.processing"
              >
                Save
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
