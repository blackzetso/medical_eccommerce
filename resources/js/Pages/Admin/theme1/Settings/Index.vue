<script setup>
import { ref } from 'vue'
import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'
import Sidebar from '@/Pages/Admin/theme1/Settings/Partials/Sidebar.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import Swal from 'sweetalert2'
import { toast } from 'vue3-toastify'

const props = defineProps({
  settings: Object
})

const form = useForm({
  settings: {
    site_name: props.settings?.site_name || '',
    site_email: props.settings?.site_email || '',
    site_phone: props.settings?.site_phone || '',
    site_address: props.settings?.site_address || '',
    site_working_hours: props.settings?.site_working_hours || '',
    site_description: props.settings?.site_description || '',
    site_copyright: props.settings?.site_copyright || '',
    currency: props.settings?.currency || 'EGP',
    timezone: props.settings?.timezone || 'Africa/Cairo',
    footer_features: props.settings?.footer_features || '',
  }
})

function submit() {
  form.put(route('admin.settings.update'), {
    onSuccess: () => {
      toast.success("تم تحديث الإعدادات بنجاح", {
        position: "top-right",
        autoClose: 3000,
      })
    }
  })
}
</script>

<template>
  <Head title="الإعدادات العامة" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <div class="row">
        <div class="col-12 mb-3">
          <h1 class="h3 mb-2 mb-sm-0">الإعدادات العامة</h1>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-xl-3">
          <Sidebar/>
        </div>

        <div class="col-xl-9">
          <div class="card shadow">
            <div class="card-header border-bottom p-4">
              <h5 class="card-header-title mb-0">الإعدادات العامة</h5>
            </div>
            <div class="card-body p-4">
              <form @submit.prevent="submit">
                <div class="row g-4">
                  <div class="col-md-6">
                    <label class="form-label">اسم الموقع <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" v-model="form.settings.site_name" required />
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">بريد الموقع <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" v-model="form.settings.site_email" required />
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">هاتف الموقع</label>
                    <input type="text" class="form-control" v-model="form.settings.site_phone" />
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">العملة</label>
                    <select class="form-select" v-model="form.settings.currency">
                      <option value="EGP">جنيه مصري (EGP)</option>
                      <option value="SAR">ريال سعودي (SAR)</option>
                      <option value="USD">دولار أمريكي (USD)</option>
                      <option value="EUR">يورو (EUR)</option>
                    </select>
                  </div>
                  <div class="col-12">
                    <label class="form-label">عنوان الشركة</label>
                    <textarea class="form-control" rows="2" v-model="form.settings.site_address" placeholder="أدخل عنوان الشركة الكامل"></textarea>
                  </div>
                  <div class="col-12">
                    <label class="form-label">أيام وساعات العمل</label>
                    <input type="text" class="form-control" v-model="form.settings.site_working_hours" placeholder="مثال: السبت - الخميس: 9 صباحاً - 5 مساءً" />
                  </div>
                  <div class="col-12">
                    <label class="form-label">وصف الموقع</label>
                    <textarea class="form-control" rows="3" v-model="form.settings.site_description"></textarea>
                  </div>
                  <div class="col-12">
                    <label class="form-label">المميزات الرئيسية في الفوتر</label>
                    <textarea class="form-control" rows="4" v-model="form.settings.footer_features" placeholder="أدخل المميزات (كل مميزة في سطر جديد)"></textarea>
                    <small class="form-text text-muted">أدخل كل مميزة في سطر جديد. سيتم عرضها في الفوتر.</small>
                  </div>
                   
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
