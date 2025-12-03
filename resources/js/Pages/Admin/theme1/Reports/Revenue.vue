<script setup>
import { ref } from 'vue'
import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'
import { Head, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
  revenue: Array,
  summary: Object,
  filters: Object
})

const period = ref(props.filters?.period || 'monthly')
const startDate = ref(props.filters?.start_date || '')
const endDate = ref(props.filters?.end_date || '')

function applyFilters() {
  router.get(route('admin.reports.revenue'), {
    period: period.value,
    start_date: startDate.value,
    end_date: endDate.value
  }, {
    preserveState: true,
    replace: true
  })
}

function formatCurrency(amount) {
  return Number(amount || 0).toFixed(2)
}
</script>

<template>
  <Head title="تقارير الإيرادات" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <div class="row mb-4">
        <div class="col-12">
          <h1 class="h3 mb-0">تقارير الإيرادات</h1>
        </div>
      </div>

      <!-- Filters -->
      <div class="card mb-4">
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label">الفترة</label>
              <select class="form-select" v-model="period" @change="applyFilters">
                <option value="daily">يومي</option>
                <option value="weekly">أسبوعي</option>
                <option value="monthly">شهري</option>
                <option value="yearly">سنوي</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">من تاريخ</label>
              <input type="date" class="form-control" v-model="startDate" @change="applyFilters" />
            </div>
            <div class="col-md-3">
              <label class="form-label">إلى تاريخ</label>
              <input type="date" class="form-control" v-model="endDate" @change="applyFilters" />
            </div>
            <div class="col-md-3 d-flex align-items-end">
              <button class="btn btn-primary w-100" @click="applyFilters">تطبيق</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Summary -->
      <div class="row g-4 mb-4">
        <div class="col-md-3">
          <div class="card card-body bg-success bg-opacity-10 p-4">
            <h2 class="mb-0 fw-bold">{{ formatCurrency(summary.total_revenue) }} ر.س</h2>
            <span class="h6 fw-light">إجمالي الإيرادات</span>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card card-body bg-primary bg-opacity-10 p-4">
            <h2 class="mb-0 fw-bold">{{ summary.total_orders || 0 }}</h2>
            <span class="h6 fw-light">إجمالي الطلبات</span>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card card-body bg-info bg-opacity-10 p-4">
            <h2 class="mb-0 fw-bold">{{ formatCurrency(summary.average_order_value) }} ر.س</h2>
            <span class="h6 fw-light">متوسط قيمة الطلب</span>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card card-body bg-warning bg-opacity-10 p-4">
            <h2 class="mb-0 fw-bold">{{ formatCurrency(summary.total_discount) }} ر.س</h2>
            <span class="h6 fw-light">إجمالي الخصومات</span>
          </div>
        </div>
      </div>

      <!-- Revenue Table -->
      <div class="card">
        <div class="card-header border-bottom p-4">
          <h5 class="card-header-title mb-0">تفاصيل الإيرادات</h5>
        </div>
        <div class="card-body p-4">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>الشهر</th>
                  <th>عدد الطلبات</th>
                  <th>المجموع الفرعي</th>
                  <th>الضرائب</th>
                  <th>الشحن</th>
                  <th>الخصومات</th>
                  <th>الإيرادات</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in revenue" :key="item.month">
                  <td>{{ item.month }}</td>
                  <td>{{ item.orders_count }}</td>
                  <td>{{ formatCurrency(item.subtotal) }} ر.س</td>
                  <td>{{ formatCurrency(item.tax) }} ر.س</td>
                  <td>{{ formatCurrency(item.shipping) }} ر.س</td>
                  <td>{{ formatCurrency(item.discount) }} ر.س</td>
                  <td><strong>{{ formatCurrency(item.revenue) }} ر.س</strong></td>
                </tr>
                <tr v-if="!revenue || revenue.length === 0">
                  <td colspan="7" class="text-center text-muted">لا توجد بيانات</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

