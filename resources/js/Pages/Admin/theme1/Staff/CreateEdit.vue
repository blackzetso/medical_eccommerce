<script setup>
import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import Swal from 'sweetalert2'

const props = defineProps({
  staff: {
    type: Object,
    default: null,
  },
  roles: {
    type: Array,
    default: () => [],
  },
  permissions: {
    type: Array,
    default: () => [],
  },
})

const form = useForm({
  name: props.staff?.name ?? '',
  email: props.staff?.email ?? '',
  phone: props.staff?.phone ?? '',
  password: '',
  password_confirmation: '',
  roles: props.staff?.roles?.map((r) => r.id) ?? [],
})

const isEdit = !!props.staff

const submit = () => {
  if (isEdit) {
    form
      .transform((data) => {
        const payload = { ...data }
        if (!payload.password) {
          delete payload.password
          delete payload.password_confirmation
        }
        return payload
      })
      .put(route('admin.staff.update', props.staff.id), {
        onFinish: () => {
          form.reset('password', 'password_confirmation')
          form.transform((data) => data)
        },
        onSuccess: () => {
          Swal.fire({
            icon: 'success',
            title: 'تم الحفظ',
            text: 'تم تحديث بيانات الموظف بنجاح',
            timer: 2000,
          })
        },
      })
  } else {
    form.post(route('admin.staff.store'), {
      onFinish: () => form.reset('password', 'password_confirmation'),
      onSuccess: () => {
        Swal.fire({
          icon: 'success',
          title: 'تم الحفظ',
          text: 'تم إضافة الموظف بنجاح',
          timer: 2000,
        })
      },
    })
  }
}
</script>

<template>
  <Head :title="isEdit ? 'تعديل موظف' : 'إضافة موظف'" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <div class="row mb-3 align-items-center">
        <div class="col-md-6">
          <h1 class="h3 mb-0">
            {{ isEdit ? 'تعديل موظف' : 'إضافة موظف جديد' }}
          </h1>
        </div>
        <div class="col-md-6 text-md-end mt-2 mt-md-0">
          <Link :href="route('admin.staff.index')" class="btn btn-light">
            رجوع
          </Link>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-12">
          <div class="card card-body bg-transparent border">
            <form @submit.prevent="submit" class="row g-3">
              <div class="col-md-6">
                <label class="form-label">الاسم <span class="text-danger">*</span></label>
                <input
                  v-model="form.name"
                  type="text"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors.name }"
                  placeholder="اسم الموظف"
                  required
                />
                <div class="invalid-feedback" v-if="form.errors.name">
                  {{ form.errors.name }}
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label">البريد الإلكتروني <span class="text-danger">*</span></label>
                <input
                  v-model="form.email"
                  type="email"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors.email }"
                  placeholder="email@example.com"
                  required
                />
                <div class="invalid-feedback" v-if="form.errors.email">
                  {{ form.errors.email }}
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label">رقم الهاتف</label>
                <input
                  v-model="form.phone"
                  type="text"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors.phone }"
                  placeholder="اختياري"
                />
                <div class="invalid-feedback" v-if="form.errors.phone">
                  {{ form.errors.phone }}
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label">
                  كلمة المرور <span v-if="!isEdit" class="text-danger">*</span>
                </label>
                <input
                  v-model="form.password"
                  type="password"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors.password }"
                  :placeholder="isEdit ? 'اتركها فارغة للإبقاء على الحالية' : 'كلمة المرور'"
                  :required="!isEdit"
                />
                <div class="invalid-feedback" v-if="form.errors.password">
                  {{ form.errors.password }}
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label">
                  تأكيد كلمة المرور <span v-if="!isEdit" class="text-danger">*</span>
                </label>
                <input
                  v-model="form.password_confirmation"
                  type="password"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors.password }"
                  :placeholder="isEdit ? 'اتركها فارغة للإبقاء على الحالية' : 'تأكيد كلمة المرور'"
                  :required="!isEdit"
                />
              </div>

              <div class="col-12">
                <label class="form-label">الأدوار</label>
                <select
                  v-model="form.roles"
                  class="form-select"
                  multiple
                  :class="{ 'is-invalid': form.errors.roles }"
                >
                  <option v-for="role in roles" :key="role.id" :value="role.id">
                    {{ role.name }}
                  </option>
                </select>
                <div class="text-danger small" v-if="form.errors.roles">
                  {{ form.errors.roles }}
                </div>
              </div>

              <div class="col-12 d-flex gap-2">
                <button class="btn btn-success" type="submit" :disabled="form.processing">
                  {{ isEdit ? 'تحديث' : 'حفظ' }}
                </button>
                <Link :href="route('admin.staff.index')" class="btn btn-outline-secondary">
                  إلغاء
                </Link>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
