<script setup>
import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'
import { Head, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
  client: Object,
  statistics: Object,
  lead: Object
})

// تنسيق التاريخ
function formatDate(dateString) {
  if (!dateString) return 'غير محدد'
  return new Date(dateString).toLocaleDateString('ar-EG', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// تنسيق المبلغ
function formatCurrency(amount) {
  return Number(amount || 0).toFixed(2)
}
</script>

<template>
  <Head :title="`تفاصيل العميل - ${client.name}`" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <!-- Header -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h1 class="h3 mb-0">تفاصيل العميل</h1>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><Link :href="route('admin.clients.index')">العملاء</Link></li>
                  <li class="breadcrumb-item active">{{ client.name }}</li>
                </ol>
              </nav>
            </div>
            <div>
              <Link :href="route('admin.clients.edit', client.id)" class="btn btn-primary me-2">
                <i class="bi bi-pencil me-1"></i>تعديل
              </Link>
              <Link :href="route('admin.clients.index')" class="btn btn-light">
                <i class="bi bi-arrow-right me-1"></i>رجوع
              </Link>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-4">
        <!-- معلومات العميل -->
        <div class="col-lg-4">
          <div class="card shadow h-100">
            <div class="card-header border-bottom p-4">
              <h5 class="card-header-title mb-0">معلومات العميل</h5>
            </div>
            <div class="card-body p-4">
              <div class="text-center mb-4">
                <div class="avatar avatar-xxl mb-3">
                  <div class="avatar-img rounded-circle bg-primary bg-opacity-10">
                    <span class="text-primary position-absolute top-50 start-50 translate-middle fw-bold fs-1">
                      {{ client.name?.charAt(0)?.toUpperCase() || 'U' }}
                    </span>
                  </div>
                </div>
                <h4 class="mb-1">{{ client.name }}</h4>
                <p class="text-muted mb-0">{{ client.email }}</p>
                <span
                  class="badge mt-2"
                  :class="client.email_verified_at ? 'bg-success' : 'bg-danger'"
                >
                  {{ client.email_verified_at ? 'نشط' : 'غير نشط' }}
                </span>
              </div>

              <hr>

              <div class="mb-3">
                <h6 class="mb-2">البريد الإلكتروني</h6>
                <p class="text-muted mb-0">{{ client.email }}</p>
              </div>

              <div class="mb-3">
                <h6 class="mb-2">رقم الهاتف</h6>
                <p class="text-muted mb-0">
                  <i class="bi bi-telephone me-1"></i>
                  {{ client.phone || 'غير محدد' }}
                </p>
              </div>

              <div v-if="client.location_url || (lead && lead.location_url)" class="mb-3">
                <h6 class="mb-2">رابط الموقع</h6>
                <p class="text-muted mb-0">
                  <a 
                    :href="client.location_url || (lead && lead.location_url)" 
                    target="_blank" 
                    class="text-primary text-decoration-none"
                    v-if="client.location_url || (lead && lead.location_url)"
                  >
                    <i class="bi bi-geo-alt me-1"></i>
                    عرض الموقع على الخريطة
                  </a>
                  <span v-else>غير محدد</span>
                </p>
              </div>

              <div v-if="lead && lead.address" class="mb-3">
                <h6 class="mb-2">العنوان</h6>
                <p class="text-muted mb-0">
                  <i class="bi bi-house-door me-1"></i>
                  {{ lead.address }}
                </p>
              </div>

              <div v-if="lead && lead.pharmacy_name" class="mb-3">
                <h6 class="mb-2">اسم الصيدلية</h6>
                <p class="text-muted mb-0">
                  <i class="bi bi-building me-1"></i>
                  {{ lead.pharmacy_name }}
                </p>
              </div>

              <div class="mb-3">
                <h6 class="mb-2">تاريخ التسجيل</h6>
                <p class="text-muted mb-0">{{ formatDate(client.created_at) }}</p>
              </div>

              <div v-if="client.email_verified_at">
                <h6 class="mb-2">تاريخ التفعيل</h6>
                <p class="text-muted mb-0">{{ formatDate(client.email_verified_at) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- الإحصائيات -->
        <div class="col-lg-8">
          <div class="row g-4 mb-4">
            <!-- إجمالي الطلبات -->
            <div class="col-md-6">
              <div class="card card-body bg-primary bg-opacity-10 p-4 h-100">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h2 class="mb-0 fw-bold">{{ statistics.total_orders || 0 }}</h2>
                    <span class="mb-0 h6 fw-light">إجمالي الطلبات</span>
                  </div>
                  <div class="icon-lg rounded-circle bg-primary text-white mb-0">
                    <i class="fas fa-shopping-cart fa-fw"></i>
                  </div>
                </div>
              </div>
            </div>

            <!-- إجمالي المشتريات -->
            <div class="col-md-6">
              <div class="card card-body bg-success bg-opacity-10 p-4 h-100">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h2 class="mb-0 fw-bold">{{ formatCurrency(statistics.total_spent) }}</h2>
                    <span class="mb-0 h6 fw-light">إجمالي المشتريات (ر.س)</span>
                  </div>
                  <div class="icon-lg rounded-circle bg-success text-white mb-0">
                    <i class="fas fa-money-bill-wave fa-fw"></i>
                  </div>
                </div>
              </div>
            </div>

            <!-- متوسط قيمة الطلب -->
            <div class="col-md-6">
              <div class="card card-body bg-info bg-opacity-10 p-4 h-100">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h2 class="mb-0 fw-bold">{{ formatCurrency(statistics.average_order_value) }}</h2>
                    <span class="mb-0 h6 fw-light">متوسط قيمة الطلب (ر.س)</span>
                  </div>
                  <div class="icon-lg rounded-circle bg-info text-white mb-0">
                    <i class="fas fa-chart-line fa-fw"></i>
                  </div>
                </div>
              </div>
            </div>

            <!-- الطلبات المكتملة -->
            <div class="col-md-6">
              <div class="card card-body bg-warning bg-opacity-10 p-4 h-100">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h2 class="mb-0 fw-bold">{{ statistics.completed_orders || 0 }}</h2>
                    <span class="mb-0 h6 fw-light">الطلبات المكتملة</span>
                  </div>
                  <div class="icon-lg rounded-circle bg-warning text-white mb-0">
                    <i class="fas fa-check-circle fa-fw"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- تفاصيل إضافية -->
          <div class="card shadow">
            <div class="card-header border-bottom p-4">
              <h5 class="card-header-title mb-0">تفاصيل إضافية</h5>
            </div>
            <div class="card-body p-4">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <h6 class="mb-2">الطلبات المعلقة</h6>
                  <p class="text-muted mb-0">{{ statistics.pending_orders || 0 }} طلب</p>
                </div>
                <div class="col-md-6 mb-3">
                  <h6 class="mb-2">الطلبات الملغاة</h6>
                  <p class="text-muted mb-0">{{ statistics.cancelled_orders || 0 }} طلب</p>
                </div>
                <div class="col-md-6 mb-3">
                  <h6 class="mb-2">أول طلب</h6>
                  <p class="text-muted mb-0">{{ formatDate(statistics.first_order_date) }}</p>
                </div>
                <div class="col-md-6 mb-3">
                  <h6 class="mb-2">آخر طلب</h6>
                  <p class="text-muted mb-0">{{ formatDate(statistics.last_order_date) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- عرض الخريطة إذا كان متوفر -->
      <div v-if="client.location_url || (lead && lead.location_url)" class="row mt-4">
        <div class="col-12">
          <div class="card shadow">
            <div class="card-header border-bottom p-4">
              <h5 class="card-header-title mb-0">
                <i class="bi bi-map me-2"></i>موقع العميل على الخريطة
              </h5>
            </div>
            <div class="card-body p-0">
              <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden;">
                <iframe 
                  :src="client.location_url || (lead && lead.location_url)" 
                  style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;"
                  allowfullscreen
                  loading="lazy"
                  referrerpolicy="no-referrer-when-downgrade"
                ></iframe>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- طلبات العميل الأخيرة -->
      <div class="row mt-4">
        <div class="col-12">
          <div class="card shadow">
            <div class="card-header border-bottom d-flex justify-content-between align-items-center p-4">
              <h5 class="card-header-title mb-0">الطلبات الأخيرة</h5>
              <Link :href="route('admin.orders.index', { search: client.email })" class="btn btn-link p-0 mb-0">
                عرض الكل
              </Link>
            </div>
            <div class="card-body p-4">
              <div v-if="client.orders && client.orders.length > 0" class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>رقم الطلب</th>
                      <th>التاريخ</th>
                      <th>المبلغ</th>
                      <th>الحالة</th>
                      <th>حالة الدفع</th>
                      <th>الإجراءات</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="order in client.orders" :key="order.id">
                      <td>#{{ order.order_number }}</td>
                      <td>{{ formatDate(order.created_at) }}</td>
                      <td>{{ formatCurrency(order.total_amount) }} {{ order.currency }}</td>
                      <td>
                        <span class="badge bg-info">{{ order.status }}</span>
                      </td>
                      <td>
                        <span class="badge" :class="order.payment_status === 'paid' ? 'bg-success' : 'bg-warning'">
                          {{ order.payment_status }}
                        </span>
                      </td>
                      <td>
                        <Link :href="route('admin.orders.show', order.id)" class="btn btn-sm btn-light">
                          <i class="bi bi-eye"></i>
                        </Link>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div v-else class="text-center py-4">
                <p class="text-muted">لا توجد طلبات لهذا العميل</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

