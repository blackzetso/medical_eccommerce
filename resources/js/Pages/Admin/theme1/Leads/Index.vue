<script setup>
import { ref, watch } from 'vue'
import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import Swal from 'sweetalert2'
import { toast } from 'vue3-toastify'

const props = defineProps({
  leads: Object,
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

  router.get(route('admin.leads.index'), params, {
    preserveState: true,
    replace: true,
  })
})

// تحويل lead إلى user
function convertToUser(leadId) {
  Swal.fire({
    title: 'تحويل إلى حساب عميل',
    html: `
      <p>سيتم إنشاء حساب جديد للعميل. يرجى إدخال كلمة المرور:</p>
      <input type="password" id="password" class="swal2-input" placeholder="كلمة المرور" required>
    `,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'تحويل',
    cancelButtonText: 'إلغاء',
    preConfirm: () => {
      const password = Swal.getPopup().querySelector('#password').value
      if (!password || password.length < 6) {
        Swal.showValidationMessage('كلمة المرور يجب أن تكون 6 أحرف على الأقل')
        return false
      }
      return { password }
    }
  }).then((result) => {
    if (result.isConfirmed) {
      router.post(route('admin.leads.convert', leadId), {
        password: result.value.password
      }, {
        onSuccess: () => {
          Swal.fire('نجح!', 'تم تحويل الـ lead إلى حساب عميل بنجاح', 'success')
        },
        onError: (errors) => {
          Swal.fire('خطأ!', errors.password || 'حدثت مشكلة أثناء التحويل', 'error')
        }
      })
    }
  })
}

// تحديث حالة الـ lead
function updateStatus(leadId, currentStatus) {
  const statusOptions = {
    'pending': 'قيد الانتظار',
    'contacted': 'تم التواصل',
    'converted': 'تم التحويل',
    'rejected': 'مرفوض'
  }

  Swal.fire({
    title: 'تحديث الحالة',
    text: 'اختر الحالة الجديدة:',
    input: 'select',
    inputOptions: statusOptions,
    inputValue: currentStatus,
    showCancelButton: true,
    confirmButtonText: 'تحديث',
    cancelButtonText: 'إلغاء',
    inputValidator: (value) => {
      if (!value) {
        return 'يجب اختيار حالة'
      }
    }
  }).then((result) => {
    if (result.isConfirmed) {
      router.patch(route('admin.leads.updateStatus', leadId), {
        status: result.value
      }, {
        onSuccess: () => {
          toast.success("تم تحديث الحالة بنجاح", {
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

// حذف lead
function confirmDelete(id) {
  Swal.fire({
    title: 'هل أنت متأكد؟',
    text: "لن تتمكن من التراجع عن هذا الإجراء!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'نعم، احذف',
    cancelButtonText: 'إلغاء'
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(route('admin.leads.destroy', id), {
        onSuccess: () => {
          Swal.fire('تم الحذف!', 'تم حذف الـ lead بنجاح.', 'success')
        },
        onError: () => {
          Swal.fire('خطأ!', 'حدثت مشكلة أثناء الحذف.', 'error')
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
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// الحصول على لون الحالة
function getStatusBadgeClass(status) {
  const classes = {
    'pending': 'bg-warning',
    'contacted': 'bg-info',
    'converted': 'bg-success',
    'rejected': 'bg-danger'
  }
  return classes[status] || 'bg-secondary'
}

// الحصول على نص الحالة
function getStatusText(status) {
  const texts = {
    'pending': 'قيد الانتظار',
    'contacted': 'تم التواصل',
    'converted': 'تم التحويل',
    'rejected': 'مرفوض'
  }
  return texts[status] || status
}
</script>

<template>
  <Head title="طلبات التسجيل" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <!-- Title & Actions -->
      <div class="row mb-3">
        <div class="col-12 col-md-3">
          <h1 class="h3 mb-0">طلبات التسجيل (Leads)</h1>
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
            <option value="pending">قيد الانتظار</option>
            <option value="contacted">تم التواصل</option>
            <option value="converted">تم التحويل</option>
            <option value="rejected">مرفوض</option>
          </select>
        </div>
        <div class="col-12 col-md-2 text-center">
          <span class="badge bg-primary">{{ leads.total }} طلب</span>
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
                <th>اسم الصيدلية</th>
                <th>العنوان</th>
                <th>تاريخ الطلب</th>
                <th>الحالة</th>
                <th>الإجراءات</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="(lead, index) in leads.data"
                :key="lead.id"
                class="text-center"
              >
                <td>{{ (leads.current_page - 1) * leads.per_page + index + 1 }}</td>

                <!-- الاسم -->
                <td>
                  <div>
                    <h6 class="mb-0">{{ lead.name || 'غير محدد' }}</h6>
                  </div>
                </td>

                <!-- البريد الإلكتروني -->
                <td>
                  <small>{{ lead.email }}</small>
                </td>

                <!-- الهاتف -->
                <td>
                  <small>{{ lead.phone || 'غير محدد' }}</small>
                </td>

                <!-- اسم الصيدلية -->
                <td>
                  <small>{{ lead.pharmacy_name || 'غير محدد' }}</small>
                </td>

                <!-- العنوان -->
                <td>
                  <small class="text-muted">{{ lead.address || 'غير محدد' }}</small>
                </td>

                <!-- تاريخ الطلب -->
                <td>
                  <small class="text-muted">{{ formatDate(lead.created_at) }}</small>
                </td>

                <!-- الحالة -->
                <td>
                  <span
                    class="badge"
                    :class="getStatusBadgeClass(lead.status)"
                  >
                    {{ getStatusText(lead.status) }}
                  </span>
                </td>

                <!-- الإجراءات -->
                <td>
                  <div class="btn-group" role="group">
                    <Link
                      :href="route('admin.leads.show', lead.id)"
                      class="btn btn-sm btn-light"
                      data-bs-toggle="tooltip"
                      data-bs-placement="top"
                      title="عرض التفاصيل"
                    >
                      <i class="bi bi-eye"></i>
                    </Link>
                    <button
                      v-if="lead.status !== 'converted'"
                      @click="convertToUser(lead.id)"
                      class="btn btn-sm btn-success"
                      data-bs-toggle="tooltip"
                      data-bs-placement="top"
                      title="تحويل إلى حساب عميل"
                    >
                      <i class="bi bi-person-plus"></i>
                    </button>
                    <button
                      @click="updateStatus(lead.id, lead.status)"
                      class="btn btn-sm btn-info"
                      data-bs-toggle="tooltip"
                      data-bs-placement="top"
                      title="تحديث الحالة"
                    >
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button
                      @click="confirmDelete(lead.id)"
                      class="btn btn-sm btn-danger"
                      data-bs-toggle="tooltip"
                      data-bs-placement="top"
                      title="حذف"
                    >
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- رسالة في حالة عدم وجود leads -->
        <div v-if="!leads.data || leads.data.length === 0" class="text-center py-4">
          <div class="mb-3">
            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
          </div>
          <h5 class="text-muted">لا توجد طلبات تسجيل</h5>
          <p class="text-muted">لم يتم العثور على أي طلبات تطابق البحث.</p>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="leads.links && leads.links.length > 3" class="d-sm-flex justify-content-sm-between align-items-sm-center mt-4 mt-sm-3">
        <p class="mb-0 text-center text-sm-start">
          عرض {{ leads.from }} إلى {{ leads.to }} من أصل {{ leads.total }} طلب
        </p>
        <nav class="d-flex justify-content-center mb-0" aria-label="navigation">
          <ul class="pagination pagination-sm pagination-primary-soft d-flex justify-content-center mb-0">
            <li
              v-for="link in leads.links"
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

