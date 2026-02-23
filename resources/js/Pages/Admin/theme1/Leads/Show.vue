<script setup>
import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import Swal from 'sweetalert2'
import { toast } from 'vue3-toastify'

const props = defineProps({
  lead: Object
})

// تحويل lead إلى user
function convertToUser() {
  Swal.fire({
    title: 'تحويل إلى حساب عميل',
    html: `
      <div style="text-align:right; margin-bottom:4px;">
        <label for="orgasoft_id" style="font-weight:600;">كود العميل في أورجا سوفت</label>
      </div>
      <input type="text" id="orgasoft_id" class="swal2-input" autocomplete="off" name="orgasoft_id_field">
      <div style="text-align:right; margin-top:12px; margin-bottom:4px;">
        <label for="password" style="font-weight:600;">كلمة المرور</label>
      </div>
      <input type="password" id="password" class="swal2-input" autocomplete="new-password" name="new_password_field">
    `,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'تحويل',
    cancelButtonText: 'إلغاء',
    preConfirm: () => {
      const orgasoft_id = Swal.getPopup().querySelector('#orgasoft_id').value
      const password = Swal.getPopup().querySelector('#password').value
      if (!orgasoft_id) {
        Swal.showValidationMessage('كود العميل في أورجا سوفت مطلوب')
        return false
      }
      if (!password || password.length < 6) {
        Swal.showValidationMessage('كلمة المرور يجب أن تكون 6 أحرف على الأقل')
        return false
      }
      return { orgasoft_id, password }
    }
  }).then((result) => {
    if (result.isConfirmed) {
      router.post(route('admin.leads.convert', props.lead.id), {
        orgasoft_id: result.value.orgasoft_id,
        password: result.value.password
      }, {
        onSuccess: () => {
          Swal.fire('نجح!', 'تم تحويل الـ lead إلى حساب عميل بنجاح', 'success').then(() => {
            router.visit(route('admin.leads.index'))
          })
        },
        onError: (errors) => {
          Swal.fire('خطأ!', errors.orgasoft_id || errors.password || 'حدثت مشكلة أثناء التحويل', 'error')
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
    month: 'long',
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
  <Head title="تفاصيل طلب التسجيل" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <div class="row mb-3">
        <div class="col-12">
          <Link :href="route('admin.leads.index')" class="btn btn-sm btn-outline-primary mb-3">
            <i class="bi bi-arrow-right"></i> العودة للقائمة
          </Link>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h4 class="card-title mb-0">تفاصيل طلب التسجيل</h4>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">الاسم الكامل</label>
              <p class="form-control-plaintext">{{ lead.name }}</p>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">البريد الإلكتروني</label>
              <p class="form-control-plaintext">{{ lead.email }}</p>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">رقم الهاتف</label>
              <p class="form-control-plaintext">{{ lead.phone || 'غير محدد' }}</p>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">اسم الصيدلية</label>
              <p class="form-control-plaintext">{{ lead.pharmacy_name || 'غير محدد' }}</p>
            </div>
            <div class="col-12 mb-3">
              <label class="form-label fw-bold">العنوان</label>
              <p class="form-control-plaintext">{{ lead.address || 'غير محدد' }}</p>
            </div>
            <div class="col-12 mb-3" v-if="lead.notes">
              <label class="form-label fw-bold">ملاحظات إضافية</label>
              <p class="form-control-plaintext">{{ lead.notes }}</p>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">الحالة</label>
              <p>
                <span class="badge" :class="getStatusBadgeClass(lead.status)">
                  {{ getStatusText(lead.status) }}
                </span>
              </p>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">تاريخ الطلب</label>
              <p class="form-control-plaintext">{{ formatDate(lead.created_at) }}</p>
            </div>
            <div class="col-md-6 mb-3" v-if="lead.converted_at">
              <label class="form-label fw-bold">تاريخ التحويل</label>
              <p class="form-control-plaintext">{{ formatDate(lead.converted_at) }}</p>
            </div>
            <div class="col-md-6 mb-3" v-if="lead.converted_by">
              <label class="form-label fw-bold">تم التحويل بواسطة</label>
              <p class="form-control-plaintext">{{ lead.converted_by?.name || 'غير محدد' }}</p>
            </div>
          </div>

          <div class="mt-4" v-if="lead.status !== 'converted'">
            <button @click="convertToUser" class="btn btn-success">
              <i class="bi bi-person-plus"></i> تحويل إلى حساب عميل
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

