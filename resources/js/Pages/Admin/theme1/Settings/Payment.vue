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
    payment_method: props.settings?.payment_method || 'cash',
    stripe_key: props.settings?.stripe_key || '',
    stripe_secret: props.settings?.stripe_secret || '',
    paypal_client_id: props.settings?.paypal_client_id || '',
    paypal_secret: props.settings?.paypal_secret || '',
    cash_on_delivery_enabled: props.settings?.cash_on_delivery_enabled || '1',
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
  <Head title="إعدادات الدفع" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <div class="row">
        <div class="col-12 mb-3">
          <h1 class="h3 mb-2 mb-sm-0">إعدادات الدفع</h1>
        </div>
      </div>
      <div class="row g-4">
        <div class="col-xl-3">
          <Sidebar/>
        </div>
        <div class="col-xl-9">
          <div class="card shadow">
            <div class="card-header border-bottom p-4">
              <h5 class="card-header-title mb-0">طرق الدفع</h5>
            </div>
            <div class="card-body p-4">
              <form @submit.prevent="submit">
                <div class="row g-4">
                  <div class="col-md-6">
                    <label class="form-label">طريقة الدفع الافتراضية</label>
                    <select class="form-select" v-model="form.settings.payment_method">
                      <option value="cash">الدفع عند الاستلام</option>
                      <option value="stripe">Stripe</option>
                      <option value="paypal">PayPal</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">تفعيل الدفع عند الاستلام</label>
                    <select class="form-select" v-model="form.settings.cash_on_delivery_enabled">
                      <option value="1">مفعل</option>
                      <option value="0">معطل</option>
                    </select>
                  </div>
                  <div class="col-12">
                    <h6 class="mb-3">Stripe Settings</h6>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Stripe Key</label>
                    <input type="text" class="form-control" v-model="form.settings.stripe_key" />
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Stripe Secret</label>
                    <input type="password" class="form-control" v-model="form.settings.stripe_secret" />
                  </div>
                  <div class="col-12">
                    <h6 class="mb-3">PayPal Settings</h6>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">PayPal Client ID</label>
                    <input type="text" class="form-control" v-model="form.settings.paypal_client_id" />
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">PayPal Secret</label>
                    <input type="password" class="form-control" v-model="form.settings.paypal_secret" />
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

