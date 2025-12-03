<script setup>
import { ref } from 'vue'
import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'
import { Head, useForm, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import Swal from 'sweetalert2'
import { toast } from 'vue3-toastify'

const props = defineProps({
  client: Object
})

const form = useForm({
  name: props.client.name || '',
  email: props.client.email || '',
  phone: props.client.phone || '',
  password: '',
  password_confirmation: ''
})

const showPassword = ref(false)

function submit() {
  Swal.fire({
    title: 'تأكيد التحديث',
    text: 'هل تريد تحديث بيانات العميل؟',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'نعم، حدث',
    cancelButtonText: 'إلغاء'
  }).then((result) => {
    if (result.isConfirmed) {
      form.put(route('admin.clients.update', props.client.id), {
        onSuccess: () => {
          toast.success("تم تحديث بيانات العميل بنجاح", {
            position: "top-right",
            autoClose: 3000,
          })
        },
        onError: (errors) => {
          toast.error("حدثت مشكلة أثناء التحديث", {
            position: "top-right",
            autoClose: 3000,
          })
        }
      })
    }
  })
}
</script>

<template>
  <Head :title="`تعديل العميل - ${client.name}`" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <!-- Header -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h1 class="h3 mb-0">تعديل العميل</h1>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><Link :href="route('admin.clients.index')">العملاء</Link></li>
                  <li class="breadcrumb-item"><Link :href="route('admin.clients.show', client.id)">{{ client.name }}</Link></li>
                  <li class="breadcrumb-item active">تعديل</li>
                </ol>
              </nav>
            </div>
            <div>
              <Link :href="route('admin.clients.show', client.id)" class="btn btn-light">
                <i class="bi bi-arrow-right me-1"></i>رجوع
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Form -->
      <div class="row">
        <div class="col-lg-8 mx-auto">
          <div class="card shadow">
            <div class="card-header border-bottom p-4">
              <h5 class="card-header-title mb-0">بيانات العميل</h5>
            </div>
            <div class="card-body p-4">
              <form @submit.prevent="submit">
                <!-- الاسم -->
                <div class="mb-4">
                  <label class="form-label">الاسم <span class="text-danger">*</span></label>
                  <input
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': form.errors.name }"
                    v-model="form.name"
                    required
                  />
                  <div v-if="form.errors.name" class="invalid-feedback">
                    {{ form.errors.name }}
                  </div>
                </div>

                <!-- البريد الإلكتروني -->
                <div class="mb-4">
                  <label class="form-label">البريد الإلكتروني <span class="text-danger">*</span></label>
                  <input
                    type="email"
                    class="form-control"
                    :class="{ 'is-invalid': form.errors.email }"
                    v-model="form.email"
                    required
                  />
                  <div v-if="form.errors.email" class="invalid-feedback">
                    {{ form.errors.email }}
                  </div>
                </div>

                <!-- الهاتف -->
                <div class="mb-4">
                  <label class="form-label">الهاتف</label>
                  <input
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': form.errors.phone }"
                    v-model="form.phone"
                  />
                  <div v-if="form.errors.phone" class="invalid-feedback">
                    {{ form.errors.phone }}
                  </div>
                </div>

                <!-- كلمة المرور -->
                <div class="mb-4">
                  <label class="form-label">كلمة المرور الجديدة</label>
                  <div class="input-group">
                    <input
                      :type="showPassword ? 'text' : 'password'"
                      class="form-control"
                      :class="{ 'is-invalid': form.errors.password }"
                      v-model="form.password"
                      placeholder="اتركه فارغاً إذا لم تريد تغيير كلمة المرور"
                    />
                    <button
                      class="btn btn-outline-secondary"
                      type="button"
                      @click="showPassword = !showPassword"
                    >
                      <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                    </button>
                  </div>
                  <div v-if="form.errors.password" class="invalid-feedback">
                    {{ form.errors.password }}
                  </div>
                  <small class="form-text text-muted">اتركه فارغاً إذا لم تريد تغيير كلمة المرور</small>
                </div>

                <!-- تأكيد كلمة المرور -->
                <div class="mb-4" v-if="form.password">
                  <label class="form-label">تأكيد كلمة المرور</label>
                  <input
                    type="password"
                    class="form-control"
                    :class="{ 'is-invalid': form.errors.password_confirmation }"
                    v-model="form.password_confirmation"
                  />
                  <div v-if="form.errors.password_confirmation" class="invalid-feedback">
                    {{ form.errors.password_confirmation }}
                  </div>
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-end gap-2">
                  <Link :href="route('admin.clients.show', client.id)" class="btn btn-light">
                    إلغاء
                  </Link>
                  <button type="submit" class="btn btn-primary" :disabled="form.processing">
                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
                    حفظ التغييرات
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

