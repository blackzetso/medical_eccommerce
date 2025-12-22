<script setup>
import { ref, watch } from 'vue'
import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import Swal from 'sweetalert2'
import { toast } from 'vue3-toastify'

const page = usePage()
const can = (permission) => page.props.auth?.permissions?.includes(permission)

const props = defineProps({
  clients: Object,
  filters: Object
})

// البحث
const search = ref(props.filters?.search ?? '')
const statusFilter = ref(props.filters?.status ?? '')

// دالة البحث
watch([search, statusFilter], ([searchValue, statusValue]) => {
  const params = {}
  if (searchValue) params.search = searchValue
  if (statusValue) params.status = statusValue

  router.get(route('admin.clients.index'), params, {
    preserveState: true,
    replace: true,
  })
})

// تفعيل/تعطيل حساب العميل
function toggleStatus(clientId) {
  Swal.fire({
    title: 'تأكيد التغيير',
    text: `هل تريد تغيير حالة حساب العميل؟`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'نعم',
    cancelButtonText: 'إلغاء'
  }).then((result) => {
    if (result.isConfirmed) {
      router.patch(route('admin.clients.status', clientId), {}, {
        onSuccess: () => {
          toast.success("تم تحديث حالة الحساب بنجاح", {
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

// تنسيق التاريخ
function formatDate(dateString) {
  if (!dateString) return 'غير محدد'
  return new Date(dateString).toLocaleDateString('ar-EG', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// تنسيق المبلغ
function formatCurrency(amount) {
  return Number(amount || 0).toFixed(2)
}
</script>

<template>
  <Head title="العملاء" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <!-- Title & Actions -->
      <div class="row mb-3">
        <div class="col-12 col-md-3">
          <h1 class="h3 mb-0">العملاء</h1>
        </div>
        <div class="col-12 col-md-4">
          <input
            class="form-control"
            v-model="search"
            placeholder="ابحث بالاسم أو البريد الإلكتروني أو الهاتف..."
          />
        </div>
        <div class="col-12 col-md-3">
          <select class="form-select" v-model="statusFilter">
            <option value="">جميع الحالات</option>
            <option value="active">نشط</option>
            <option value="inactive">غير نشط</option>
          </select>
        </div>
        <div class="col-12 col-md-2 text-center">
          <span class="badge bg-primary">{{ clients.total }} عميل</span>
        </div>
      </div>

      <!-- Table -->
      <div class="card card-body bg-transparent pb-0 border mb-4">
        <div class="table-responsive border-0">
          <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
            <thead>
              <tr class="text-center">
                <th>#</th>
                <th>الاسم</th>
                <th>البريد الإلكتروني</th>
                <th>الهاتف</th>
                <th>عدد الطلبات</th>
                <th>إجمالي المشتريات</th>
                <th>تاريخ التسجيل</th>
                <th>الحالة</th>
                <th>الإجراءات</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="(client, index) in clients.data"
                :key="client.id"
                class="text-center"
              >
                <td>{{ (clients.current_page - 1) * clients.per_page + index + 1 }}</td>

                <!-- الاسم -->
                <td>
                  <div>
                    <h6 class="mb-0">{{ client.name || 'غير محدد' }}</h6>
                  </div>
                </td>

                <!-- البريد الإلكتروني -->
                <td>
                  <small>{{ client.email }}</small>
                </td>

                <!-- الهاتف -->
                <td>
                  <small>{{ client.phone || 'غير محدد' }}</small>
                </td>

                <!-- عدد الطلبات -->
                <td>
                  <span class="badge bg-info">{{ client.orders_count || 0 }}</span>
                </td>

                <!-- إجمالي المشتريات -->
                <td>
                  <span class="fw-bold">{{ formatCurrency(client.total_spent) }} ر.س</span>
                </td>

                <!-- تاريخ التسجيل -->
                <td>
                  <small class="text-muted">{{ formatDate(client.created_at) }}</small>
                </td>

                <!-- الحالة -->
                <td>
                  <span
                    class="badge"
                    :class="client.email_verified_at ? 'bg-success' : 'bg-danger'"
                  >
                    {{ client.email_verified_at ? 'نشط' : 'غير نشط' }}
                  </span>
                </td>

                <!-- الإجراءات -->
                <td>
                  <div class="btn-group" role="group">
                    <Link
                      :href="route('admin.clients.show', client.id)"
                      class="btn btn-sm btn-light"
                      data-bs-toggle="tooltip"
                      data-bs-placement="top"
                      title="عرض التفاصيل"
                    >
                      <i class="bi bi-eye"></i>
                    </Link>
                    <Link
                      v-if="can('update_clients')"
                      :href="route('admin.clients.edit', client.id)"
                      class="btn btn-sm btn-primary"
                      data-bs-toggle="tooltip"
                      data-bs-placement="top"
                      title="تعديل"
                    >
                      <i class="bi bi-pencil"></i>
                    </Link>
                    <button
                      v-if="can('update_clients')"
                      @click="toggleStatus(client.id)"
                      class="btn btn-sm"
                      :class="client.email_verified_at ? 'btn-warning' : 'btn-success'"
                      data-bs-toggle="tooltip"
                      data-bs-placement="top"
                      :title="client.email_verified_at ? 'تعطيل' : 'تفعيل'"
                    >
                      <i :class="client.email_verified_at ? 'bi bi-x-circle' : 'bi bi-check-circle'"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- رسالة في حالة عدم وجود عملاء -->
        <div v-if="!clients.data || clients.data.length === 0" class="text-center py-4">
          <div class="mb-3">
            <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
          </div>
          <h5 class="text-muted">لا يوجد عملاء</h5>
          <p class="text-muted">لم يتم العثور على أي عملاء تطابق البحث.</p>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="clients.links && clients.links.length > 3" class="d-sm-flex justify-content-sm-between align-items-sm-center mt-4 mt-sm-3">
        <p class="mb-0 text-center text-sm-start">
          عرض {{ clients.from }} إلى {{ clients.to }} من أصل {{ clients.total }} عميل
        </p>
        <nav class="d-flex justify-content-center mb-0" aria-label="navigation">
          <ul class="pagination pagination-sm pagination-primary-soft d-flex justify-content-center mb-0">
            <li
              v-for="link in clients.links"
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

