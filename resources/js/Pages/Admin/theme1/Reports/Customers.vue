<script setup>
import { ref } from 'vue'
import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
  customers: Array,
  filters: Object
})

const type = ref(props.filters?.type || 'top_customers')

function applyFilters() {
  router.get(route('admin.reports.customers'), {
    type: type.value
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
  <Head title="تقارير العملاء" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <div class="row mb-4">
        <div class="col-12">
          <h1 class="h3 mb-0">تقارير العملاء</h1>
        </div>
      </div>

      <!-- Filters -->
      <div class="card mb-4">
        <div class="card-body">
          <div class="row">
            <div class="col-md-4">
              <label class="form-label">نوع التقرير</label>
              <select class="form-select" v-model="type" @change="applyFilters">
                <option value="top_customers">أفضل العملاء</option>
                <option value="new_customers">عملاء جدد</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Customers Table -->
      <div class="card">
        <div class="card-header border-bottom p-4">
          <h5 class="card-header-title mb-0">العملاء</h5>
        </div>
        <div class="card-body p-4">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>#</th>
                  <th>الاسم</th>
                  <th>البريد الإلكتروني</th>
                  <th>عدد الطلبات</th>
                  <th>إجمالي المشتريات</th>
                  <th>الإجراءات</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(customer, index) in customers" :key="customer.id">
                  <td>{{ index + 1 }}</td>
                  <td>{{ customer.name }}</td>
                  <td>{{ customer.email }}</td>
                  <td>{{ customer.orders_count || 0 }}</td>
                  <td>{{ formatCurrency(customer.total_spent || 0) }} ر.س</td>
                  <td>
                    <Link :href="route('admin.clients.show', customer.id)" class="btn btn-sm btn-light">
                      <i class="bi bi-eye"></i>
                    </Link>
                  </td>
                </tr>
                <tr v-if="!customers || customers.length === 0">
                  <td colspan="6" class="text-center text-muted">لا توجد بيانات</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

