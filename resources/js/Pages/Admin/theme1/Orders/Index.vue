<script setup>
import { ref, watch, onMounted } from 'vue'
import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { usePage } from '@inertiajs/vue3'
import Swal from 'sweetalert2'
import { toast } from 'vue3-toastify'
import axios from 'axios'

const page = usePage()
const can = (permission) => page.props.auth?.permissions?.includes(permission)

const props = defineProps({
  orders: Object,
  filters: Object,
  statuses: Object,
  paymentStatuses: Object
})

// تحديث حالة notification_seen عند تحميل الصفحة
const markOrdersAsSeen = async () => {
  try {
    await axios.post(route('admin.orders.markAsSeen'))
  } catch (error) {
    // Error marking orders as seen
  }
}

onMounted(() => {
  // تحديث حالة notification_seen عند تحميل الصفحة
  markOrdersAsSeen()
})

// البحث
const search = ref(props.filters?.search ?? '')
const statusFilter = ref(props.filters?.status ?? '')
const paymentStatusFilter = ref(props.filters?.payment_status ?? '')

// دالة البحث
watch([search, statusFilter, paymentStatusFilter], ([searchValue, statusValue, paymentStatusValue]) => {
  const params = {}
  if (searchValue) params.search = searchValue
  if (statusValue) params.status = statusValue
  if (paymentStatusValue) params.payment_status = paymentStatusValue

  router.get(route('admin.orders.index'), params, {
    preserveState: true,
    replace: true,
  })
})

// تحديث حالة الطلب
function updateOrderStatus(orderId, newStatus) {
  Swal.fire({
    title: 'تأكيد تحديث الحالة',
    text: `هل تريد تحديث حالة الطلب؟`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'نعم، حدث',
    cancelButtonText: 'إلغاء'
  }).then((result) => {
    if (result.isConfirmed) {
      router.patch(route('admin.orders.updateStatus', orderId), {
        status: newStatus
      }, {
        onSuccess: () => {
          toast.success("تم تحديث حالة الطلب بنجاح", {
            position: "top-right",
            autoClose: 3000,
          })
        },
        onError: () => {
          toast.error("حدثت مشكلة أثناء التحديث", {
            position: "top-right",
            autoClose: 3000,
          })
        }
      })
    }
  })
}

// تحديث حالة الدفع
function updatePaymentStatus(orderId, newStatus) {
  Swal.fire({
    title: 'تأكيد تحديث حالة الدفع',
    text: `هل تريد تحديث حالة الدفع؟`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'نعم، حدث',
    cancelButtonText: 'إلغاء'
  }).then((result) => {
    if (result.isConfirmed) {
      router.patch(route('admin.orders.updatePaymentStatus', orderId), {
        payment_status: newStatus
      }, {
        onSuccess: () => {
          toast.success("تم تحديث حالة الدفع بنجاح", {
            position: "top-right",
            autoClose: 3000,
          })
        },
        onError: () => {
          toast.error("حدثت مشكلة أثناء التحديث", {
            position: "top-right",
            autoClose: 3000,
          })
        }
      })
    }
  })
}

// دالة لتحديد لون الحالة
function getStatusBadge(status) {
  const badges = {
    'pending': 'badge-warning',
    'processing': 'badge-info',
    'shipped': 'badge-purple',
    'delivered': 'badge-success',
    'cancelled': 'badge-danger'
  }
  return badges[status] || 'badge-secondary'
}

function getPaymentStatusBadge(status) {
  const badges = {
    'pending': 'badge-warning',
    'paid': 'badge-success',
    'failed': 'badge-danger',
    'refunded': 'badge-purple'
  }
  return badges[status] || 'badge-secondary'
}

// تنسيق التاريخ
function formatDate(dateString) {
  return new Date(dateString).toLocaleDateString('ar-EG', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}
</script>

<template>
  <Head title="الطلبات" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <!-- Title & Actions -->
      <div class="row mb-3">
        <div class="col-2">
          <h1 class="h3 mb-0">الطلبات</h1>
        </div>
        <div class="col-3">
          <input
            class="form-control"
            v-model="search"
            placeholder="ابحث برقم الطلب أو اسم العميل..."
          />
        </div>
        <div class="col-3">
          <select class="form-select" v-model="statusFilter">
            <option value="">جميع حالات الطلب</option>
            <option v-for="(label, key) in statuses" :key="key" :value="key">
              {{ label }}
            </option>
          </select>
        </div>
        <div class="col-3">
          <select class="form-select" v-model="paymentStatusFilter">
            <option value="">جميع حالات الدفع</option>
            <option v-for="(label, key) in paymentStatuses" :key="key" :value="key">
              {{ label }}
            </option>
          </select>
        </div>
        <div class="col-1 text-center">
          <span class="badge bg-primary">{{ orders.total }} طلب</span>
        </div>
      </div>

      <!-- Table -->
      <div class="card card-body bg-transparent pb-0 border mb-4">
        <div class="table-responsive border-0">
          <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
            <thead>
              <tr class="text-center">
                <th>#</th>
                <th>رقم الطلب</th>
                <th>العميل</th>
                <th>المبلغ الإجمالي</th>
                <th>نوع التوصيل</th>
                <th>حالة الطلب</th>
                <th>حالة الدفع</th>
                <th>تاريخ الطلب</th>
                <th>الإجراءات</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="(order, index) in orders.data"
                :key="order.id"
                class="text-center"
              >
                <td>{{ (orders.current_page - 1) * orders.per_page + index + 1 }}</td>

                <!-- رقم الطلب -->
                <td>
                  <h6 class="mb-0 text-primary">#{{ order.order_number }}</h6>
                </td>

                <!-- العميل -->
                <td>
                  <div>
                    <h6 class="mb-0">{{ order.user?.name || 'غير محدد' }}</h6>
                    <small class="text-muted">{{ order.user?.email || '' }}</small>
                  </div>
                </td>

                <!-- المبلغ الإجمالي -->
                <td>
                  <div>
                    <span class="fw-bold">{{ Number(order.total_amount).toFixed(2) }} {{ order.currency }}</span>
                    <br>
                    <small class="text-muted">{{ order.items?.length || 0 }} عنصر</small>
                  </div>
                </td>

                <!-- نوع التوصيل -->
                <td>
                  <span class="badge" :class="order.delivery_type === 'delivery' ? 'bg-info' : 'bg-success'">
                    <i :class="order.delivery_type === 'delivery' ? 'fas fa-truck' : 'fas fa-store'" class="me-1"></i>
                    {{ order.delivery_type === 'delivery' ? 'توصيل' : 'استلام من المحل' }}
                  </span>
                </td>

                <!-- حالة الطلب -->
                <td>
                  <select
                    v-if="can('update_orders')"
                    :value="order.status"
                    @change="updateOrderStatus(order.id, $event.target.value)"
                    class="form-select form-select-sm"
                    :class="getStatusBadge(order.status)"
                  >
                    <option v-for="(label, key) in statuses" :key="key" :value="key">
                      {{ label }}
                    </option>
                  </select>
                  <span v-else class="badge" :class="getStatusBadge(order.status)">
                    {{ statuses[order.status] }}
                  </span>
                </td>

                <!-- حالة الدفع -->
                <td>
                  <select
                    v-if="can('update_orders')"
                    :value="order.payment_status"
                    @change="updatePaymentStatus(order.id, $event.target.value)"
                    class="form-select form-select-sm"
                    :class="getPaymentStatusBadge(order.payment_status)"
                  >
                    <option v-for="(label, key) in paymentStatuses" :key="key" :value="key">
                      {{ label }}
                    </option>
                  </select>
                  <span v-else class="badge" :class="getPaymentStatusBadge(order.payment_status)">
                    {{ paymentStatuses[order.payment_status] }}
                  </span>
                </td>

                <!-- تاريخ الطلب -->
                <td>
                  <small class="text-muted">{{ formatDate(order.created_at) }}</small>
                </td>

                <!-- الإجراءات -->
                <td>
                  <Link
                    :href="route('admin.orders.show', order.id)"
                    class="btn btn-sm btn-light"
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    title="عرض الطلب"
                  >
                    <i class="bi bi-eye"></i>
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- رسالة في حالة عدم وجود طلبات -->
        <div v-if="!orders.data || orders.data.length === 0" class="text-center py-4">
          <div class="mb-3">
            <i class="bi bi-folder2-open text-muted" style="font-size: 3rem;"></i>
          </div>
          <h5 class="text-muted">لا توجد طلبات</h5>
          <p class="text-muted">لم يتم العثور على أي طلبات تطابق البحث.</p>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="orders.links && orders.links.length > 3" class="d-sm-flex justify-content-sm-between align-items-sm-center mt-4 mt-sm-3">
        <p class="mb-0 text-center text-sm-start">
          عرض {{ orders.from }} إلى {{ orders.to }} من أصل {{ orders.total }} طلب
        </p>
        <nav class="d-flex justify-content-center mb-0" aria-label="navigation">
          <ul class="pagination pagination-sm pagination-primary-soft d-flex justify-content-center mb-0">
            <li
              v-for="link in orders.links"
              :key="link.label"
              class="page-item"
              :class="{ active: link.active, disabled: !link.url }"
            >
              <Link
                v-if="link.url"
                :href="link.url"
                class="page-link"
                v-html="link.label"
              />
              <span v-else class="page-link" v-html="link.label" />
            </li>
          </ul>
        </nav>
      </div>
    </div>
  </AppLayout>
</template>
