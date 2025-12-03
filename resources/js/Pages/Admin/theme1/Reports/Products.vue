<script setup>
import { ref, watch } from 'vue'
import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'
import { Head, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
  products: Array,
  filters: Object
})

const type = ref(props.filters?.type || 'best_selling')

function applyFilters() {
  router.get(route('admin.reports.products'), {
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
  <Head title="تقارير المنتجات" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <div class="row mb-4">
        <div class="col-12">
          <h1 class="h3 mb-0">تقارير المنتجات</h1>
        </div>
      </div>

      <!-- Filters -->
      <div class="card mb-4">
        <div class="card-body">
          <div class="row">
            <div class="col-md-4">
              <label class="form-label">نوع التقرير</label>
              <select class="form-select" v-model="type" @change="applyFilters">
                <option value="best_selling">الأكثر مبيعاً</option>
                <option value="least_selling">الأقل مبيعاً</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Products Table -->
      <div class="card">
        <div class="card-header border-bottom p-4">
          <h5 class="card-header-title mb-0">المنتجات</h5>
        </div>
        <div class="card-body p-4">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>#</th>
                  <th>اسم المنتج</th>
                  <th>الكمية المباعة</th>
                  <th>عدد الطلبات</th>
                  <th>إجمالي الإيرادات</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(product, index) in products" :key="product.product_id">
                  <td>{{ index + 1 }}</td>
                  <td>{{ product.product_name }}</td>
                  <td>{{ product.total_quantity }}</td>
                  <td>{{ product.orders_count }}</td>
                  <td>{{ formatCurrency(product.total_revenue) }} ر.س</td>
                </tr>
                <tr v-if="!products || products.length === 0">
                  <td colspan="5" class="text-center text-muted">لا توجد بيانات</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

