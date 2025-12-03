<script setup>
import { ref } from 'vue'
import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'
import Sidebar from '@/Pages/Admin/theme1/Settings/Partials/Sidebar.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { toast } from 'vue3-toastify'

const props = defineProps({
  content: String
})

const form = useForm({
  content: props.content || ''
})

function submit() {
  form.post(route('admin.settings.privacy.update'), {
    onSuccess: () => {
      toast.success("تم تحديث محتوى صفحة سياسة الخصوصية بنجاح", {
        position: "top-right",
        autoClose: 3000,
      })
    }
  })
}
</script>

<template>
  <Head title="سياسة الخصوصية" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <div class="row">
        <div class="col-12 mb-3">
          <h1 class="h3 mb-2 mb-sm-0">سياسة الخصوصية</h1>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-xl-3">
          <Sidebar/>
        </div>

        <div class="col-xl-9">
          <div class="card shadow">
            <div class="card-header border-bottom p-4">
              <h5 class="card-header-title mb-0">محتوى صفحة سياسة الخصوصية</h5>
            </div>
            <div class="card-body p-4">
              <form @submit.prevent="submit">
                <div class="mb-3">
                  <label class="form-label">المحتوى <span class="text-danger">*</span></label>
                  <textarea 
                    class="form-control" 
                    rows="15" 
                    v-model="form.content" 
                    placeholder="أدخل محتوى صفحة سياسة الخصوصية..."
                    required
                  ></textarea>
                  <small class="form-text text-muted">يمكنك استخدام HTML لتنسيق المحتوى</small>
                </div>
                <div class="d-flex justify-content-end mt-4">
                  <button type="submit" class="btn btn-primary" :disabled="form.processing">
                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
                    حفظ التغييرات
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

