<script setup>
import { ref } from 'vue'
import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
  products: Object,
  summary: Object,
  filters: Object
})

const type = ref(props.filters?.type || 'all')

function applyFilters() {
  router.get(route('admin.reports.inventory'), {
    type: type.value
  }, {
    preserveState: true,
    replace: true
  })
}
</script>

<template>
  <Head title="تقارير المخزون" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <div class="row mb-4">
        <div class="col-12">
          <h1 class="h3 mb-0">تقارير المخزون</h1>
        </div>
      </div>

      <!-- Filters -->
      <div class="card mb-4">
        <div class="card-body">
          <div class="row">
            <div class="col-md-4">
              <label class="form-label">نوع التقرير</label>
              <select class="form-select" v-model="type" @change="applyFilters">
                <option value="all">جميع المنتجات</option>
                <option value="in_stock">متوفر</option>
                <option value="low_stock">مخزون منخفض</option>
                <option value="out_of_stock">نفد المخزون</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Summary -->
      <div class="row g-4 mb-4">
        <div class="col-md-3">
          <div class="card card-body bg-primary bg-opacity-10 p-4">
            <h2 class="mb-0 fw-bold">{{ summary.total_products || 0 }}</h2>
            <span class="h6 fw-light">إجمالي المنتجات</span>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card card-body bg-success bg-opacity-10 p-4">
            <h2 class="mb-0 fw-bold">{{ summary.in_stock || 0 }}</h2>
            <span class="h6 fw-light">متوفر</span>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card card-body bg-warning bg-opacity-10 p-4">
            <h2 class="mb-0 fw-bold">{{ summary.low_stock || 0 }}</h2>
            <span class="h6 fw-light">مخزون منخفض</span>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card card-body bg-danger bg-opacity-10 p-4">
            <h2 class="mb-0 fw-bold">{{ summary.out_of_stock || 0 }}</h2>
            <span class="h6 fw-light">نفد المخزون</span>
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
                  <th>اسم المنتج</th>
                  <th>الفئة</th>
                  <th>العلامة التجارية</th>
                  <th>الكمية المتاحة</th>
                  <th>الحالة</th>
                  <th>الإجراءات</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="product in products.data" :key="product.id">
                  <td>{{ product.name }}</td>
                  <td>{{ product.category?.name || 'غير محدد' }}</td>
                  <td>{{ product.brand?.name || 'غير محدد' }}</td>
                  <td>
                    <span :class="product.stock_quantity <= 10 && product.manage_stock ? 'text-warning' : ''">
                      {{ product.manage_stock ? product.stock_quantity : 'غير محدود' }}
                    </span>
                  </td>
                  <td>
                    <span class="badge" :class="product.in_stock ? 'bg-success' : 'bg-danger'">
                      {{ product.in_stock ? 'متوفر' : 'غير متوفر' }}
                    </span>
                  </td>
                  <td>
                    <Link :href="route('admin.products.edit', product.id)" class="btn btn-sm btn-light">
                      <i class="bi bi-eye"></i>
                    </Link>
                  </td>
                </tr>
                <tr v-if="!products.data || products.data.length === 0">
                  <td colspan="6" class="text-center text-muted">لا توجد بيانات</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="products.links && products.links.length > 3" class="mt-4">
            <nav>
              <ul class="pagination justify-content-center">
                <li
                  v-for="link in products.links"
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

