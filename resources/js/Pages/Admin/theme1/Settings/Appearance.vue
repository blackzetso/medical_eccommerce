<script setup>
import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'
import Sidebar from '@/Pages/Admin/theme1/Settings/Partials/Sidebar.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { toast } from 'vue3-toastify'

const props = defineProps({
  settings: Object
})

const form = useForm({
  settings: {
    site_logo: props.settings?.site_logo || '',
    site_favicon: props.settings?.site_favicon || '',
    primary_color: props.settings?.primary_color || '#0d6efd',
    secondary_color: props.settings?.secondary_color || '#6c757d',
    theme_mode: props.settings?.theme_mode || 'light',
  }
})

function submit() {
  form.put(route('admin.settings.update'), {
    onSuccess: () => {
      toast.success("تم تحديث الإعدادات بنجاح")
    }
  })
}
</script>

<template>
  <Head title="إعدادات المظهر" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <div class="row">
        <div class="col-12 mb-3">
          <h1 class="h3 mb-2 mb-sm-0">إعدادات المظهر</h1>
        </div>
      </div>
      <div class="row g-4">
        <div class="col-xl-3">
          <Sidebar/>
        </div>
        <div class="col-xl-9">
          <div class="card shadow">
            <div class="card-header border-bottom p-4">
              <h5 class="card-header-title mb-0">إعدادات المظهر</h5>
            </div>
            <div class="card-body p-4">
              <form @submit.prevent="submit">
                <div class="row g-4">
                  <div class="col-md-6">
                    <label class="form-label">اللون الأساسي</label>
                    <input type="color" class="form-control form-control-color" v-model="form.settings.primary_color" />
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">اللون الثانوي</label>
                    <input type="color" class="form-control form-control-color" v-model="form.settings.secondary_color" />
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">وضع المظهر</label>
                    <select class="form-select" v-model="form.settings.theme_mode">
                      <option value="light">فاتح</option>
                      <option value="dark">داكن</option>
                      <option value="auto">تلقائي</option>
                    </select>
                  </div>
                </div>
                <div class="d-flex justify-content-end mt-4">
                  <button type="submit" class="btn btn-primary" :disabled="form.processing">حفظ</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

