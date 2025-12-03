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
    email_notifications: props.settings?.email_notifications || '1',
    sms_notifications: props.settings?.sms_notifications || '0',
    order_notifications: props.settings?.order_notifications || '1',
    payment_notifications: props.settings?.payment_notifications || '1',
    admin_email: props.settings?.admin_email || '',
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
  <Head title="إعدادات الإشعارات" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <div class="row">
        <div class="col-12 mb-3">
          <h1 class="h3 mb-2 mb-sm-0">إعدادات الإشعارات</h1>
        </div>
      </div>
      <div class="row g-4">
        <div class="col-xl-3">
          <Sidebar/>
        </div>
        <div class="col-xl-9">
          <div class="card shadow">
            <div class="card-header border-bottom p-4">
              <h5 class="card-header-title mb-0">إعدادات الإشعارات</h5>
            </div>
            <div class="card-body p-4">
              <form @submit.prevent="submit">
                <div class="row g-4">
                  <div class="col-md-6">
                    <label class="form-label">بريد الإدارة</label>
                    <input type="email" class="form-control" v-model="form.settings.admin_email" />
                  </div>
                  <div class="col-12">
                    <h6 class="mb-3">تفعيل الإشعارات</h6>
                    <div class="form-check form-switch mb-3">
                      <input class="form-check-input" type="checkbox" id="email_notifications" v-model="form.settings.email_notifications" true-value="1" false-value="0" />
                      <label class="form-check-label" for="email_notifications">الإشعارات عبر البريد الإلكتروني</label>
                    </div>
                    <div class="form-check form-switch mb-3">
                      <input class="form-check-input" type="checkbox" id="sms_notifications" v-model="form.settings.sms_notifications" true-value="1" false-value="0" />
                      <label class="form-check-label" for="sms_notifications">الإشعارات عبر الرسائل النصية</label>
                    </div>
                    <div class="form-check form-switch mb-3">
                      <input class="form-check-input" type="checkbox" id="order_notifications" v-model="form.settings.order_notifications" true-value="1" false-value="0" />
                      <label class="form-check-label" for="order_notifications">إشعارات الطلبات</label>
                    </div>
                    <div class="form-check form-switch mb-3">
                      <input class="form-check-input" type="checkbox" id="payment_notifications" v-model="form.settings.payment_notifications" true-value="1" false-value="0" />
                      <label class="form-check-label" for="payment_notifications">إشعارات الدفع</label>
                    </div>
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

