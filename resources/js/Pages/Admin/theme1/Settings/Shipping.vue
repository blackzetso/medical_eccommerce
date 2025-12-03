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
    shipping_method: props.settings?.shipping_method || 'flat_rate',
    shipping_cost: props.settings?.shipping_cost || '0',
    free_shipping_threshold: props.settings?.free_shipping_threshold || '0',
    shipping_zones: props.settings?.shipping_zones || '',
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
  <Head title="إعدادات الشحن" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <div class="row">
        <div class="col-12 mb-3">
          <h1 class="h3 mb-2 mb-sm-0">إعدادات الشحن</h1>
        </div>
      </div>
      <div class="row g-4">
        <div class="col-xl-3">
          <Sidebar/>
        </div>
        <div class="col-xl-9">
          <div class="card shadow">
            <div class="card-header border-bottom p-4">
              <h5 class="card-header-title mb-0">إعدادات الشحن</h5>
            </div>
            <div class="card-body p-4">
              <form @submit.prevent="submit">
                <div class="row g-4">
                  <div class="col-md-6">
                    <label class="form-label">طريقة الشحن</label>
                    <select class="form-select" v-model="form.settings.shipping_method">
                      <option value="flat_rate">سعر ثابت</option>
                      <option value="free">شحن مجاني</option>
                      <option value="weight_based">حسب الوزن</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">تكلفة الشحن (ر.س)</label>
                    <input type="number" step="0.01" class="form-control" v-model="form.settings.shipping_cost" />
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">حد الشحن المجاني (ر.س)</label>
                    <input type="number" step="0.01" class="form-control" v-model="form.settings.free_shipping_threshold" />
                    <small class="form-text text-muted">إذا كان إجمالي الطلب أكبر من هذا المبلغ، سيتم تطبيق الشحن المجاني</small>
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

