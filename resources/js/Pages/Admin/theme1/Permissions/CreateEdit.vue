<script setup>
import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import Swal from 'sweetalert2'

const props = defineProps({
  permission: {
    type: Object,
    default: null,
  },
})

const form = useForm({
  name: props.permission?.name ?? '',
})

const isEdit = !!props.permission

const submit = () => {
  const action = isEdit
    ? form.put(route('admin.permissions.update', props.permission.id))
    : form.post(route('admin.permissions.store'))

  action.then(() => {
    if (!form.hasErrors) {
      Swal.fire({
        icon: 'success',
        title: 'تم الحفظ',
        text: isEdit ? 'تم تحديث الصلاحية بنجاح' : 'تم إنشاء الصلاحية بنجاح',
        timer: 2000,
      })
    }
  })
}
</script>

<template>
  <Head :title="isEdit ? 'تعديل صلاحية' : 'إضافة صلاحية'" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <div class="row mb-3 align-items-center">
        <div class="col-md-6">
          <h1 class="h3 mb-0">
            {{ isEdit ? 'تعديل صلاحية' : 'إضافة صلاحية جديدة' }}
          </h1>
        </div>
        <div class="col-md-6 text-md-end mt-2 mt-md-0">
          <Link :href="route('admin.permissions.index')" class="btn btn-light">
            رجوع
          </Link>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-lg-8">
          <div class="card card-body bg-transparent border">
            <form @submit.prevent="submit" class="row g-3">
              <div class="col-12">
                <label class="form-label">الاسم <span class="text-danger">*</span></label>
                <input
                  v-model="form.name"
                  type="text"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors.name }"
                  placeholder="أدخل اسم الصلاحية (مثل manage_staff)"
                  required
                />
                <div class="invalid-feedback" v-if="form.errors.name">
                  {{ form.errors.name }}
                </div>
              </div>

              <div class="col-12 d-flex gap-2">
                <button class="btn btn-success" type="submit" :disabled="form.processing">
                  {{ isEdit ? 'تحديث' : 'حفظ' }}
                </button>
                <Link :href="route('admin.permissions.index')" class="btn btn-outline-secondary">
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
