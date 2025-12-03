<script setup>
import { ref } from 'vue'
import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
  orders: Object,
  summary: Object,
  statuses: Object,
  filters: Object
})

const status = ref(props.filters?.status || '')
const startDate = ref(props.filters?.start_date || '')
const endDate = ref(props.filters?.end_date || '')

function applyFilters() {
  router.get(route('admin.reports.orders'), {
    status: status.value,
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

function formatDate(dateString) {
  if (!dateString) return 'غير محدد'
  return new Date(dateString).toLocaleDateString('ar-EG')
}
</script>

<template>
  <Head title="تقارير الطلبات" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <div class="row mb-4">
        <div class="col-12">
          <h1 class="h3 mb-0">تقارير الطلبات</h1>
        </div>
      </div>

      <!-- Filters -->
      <div class="card mb-4">
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label">الحالة</label>
              <select class="form-select" v-model="status" @change="applyFilters">
                <option value="">جميع الحالات</option>
                <option v-for="(label, key) in statuses" :key="key" :value="key">{{ label }}</option>
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
          <div class="card card-body bg-primary bg-opacity-10 p-4">
            <h2 class="mb-0 fw-bold">{{ summary.total_orders || 0 }}</h2>
            <span class="h6 fw-light">إجمالي الطلبات</span>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card card-body bg-success bg-opacity-10 p-4">
            <h2 class="mb-0 fw-bold">{{ formatCurrency(summary.total_revenue) }} ر.س</h2>
            <span class="h6 fw-light">إجمالي الإيرادات</span>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card card-body bg-warning bg-opacity-10 p-4">
            <h2 class="mb-0 fw-bold">{{ summary.pending_orders || 0 }}</h2>
            <span class="h6 fw-light">طلبات معلقة</span>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card card-body bg-info bg-opacity-10 p-4">
            <h2 class="mb-0 fw-bold">{{ summary.completed_orders || 0 }}</h2>
            <span class="h6 fw-light">طلبات مكتملة</span>
          </div>
        </div>
      </div>

      <!-- Orders Table -->
      <div class="card">
        <div class="card-header border-bottom p-4">
          <h5 class="card-header-title mb-0">الطلبات</h5>
        </div>
        <div class="card-body p-4">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>رقم الطلب</th>
                  <th>العميل</th>
                  <th>المبلغ</th>
                  <th>الحالة</th>
                  <th>التاريخ</th>
                  <th>الإجراءات</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="order in orders.data" :key="order.id">
                  <td>#{{ order.order_number }}</td>
                  <td>{{ order.user?.name || 'غير محدد' }}</td>
                  <td>{{ formatCurrency(order.total_amount) }} {{ order.currency }}</td>
                  <td><span class="badge bg-info">{{ order.status }}</span></td>
                  <td>{{ formatDate(order.created_at) }}</td>
                  <td>
                    <Link :href="route('admin.orders.show', order.id)" class="btn btn-sm btn-light">
                      <i class="bi bi-eye"></i>
                    </Link>
                  </td>
                </tr>
                <tr v-if="!orders.data || orders.data.length === 0">
                  <td colspan="6" class="text-center text-muted">لا توجد بيانات</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="orders.links && orders.links.length > 3" class="mt-4">
            <nav>
              <ul class="pagination justify-content-center">
                <li
                  v-for="link in orders.links"
                  :key="link.label"
                  class="page-item"
                  :class="{ active: link.active, disabled: !link.url }"
                >
                  <Link v-if="link.url" :href="link.url" class="page-link" v-html="link.label" />
                  <span v-else class="page-link" v-html="link.label" />
                </li>
              </ul>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

