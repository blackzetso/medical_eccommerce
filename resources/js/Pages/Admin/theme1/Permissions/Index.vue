<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import Swal from 'sweetalert2'
import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'

const props = defineProps({
  permissions: Object,
  filters: Object,
})

const search = ref(props.filters?.search ?? '')

watch(search, (value) => {
  router.get(
    route('admin.permissions.index'),
    { search: value },
    { preserveState: true, replace: true },
  )
})

function confirmDelete(id) {
  Swal.fire({
    title: 'تأكيد الحذف',
    text: 'هل أنت متأكد من حذف الصلاحية؟',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'حذف',
    cancelButtonText: 'إلغاء',
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(route('admin.permissions.destroy', id), {
        preserveScroll: true,
      })
    }
  })
}
</script>

<template>
  <Head title="الصلاحيات" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <div class="row mb-3 align-items-center">
        <div class="col-md-6">
          <h1 class="h3 mb-0">إدارة الصلاحيات</h1>
        </div>
        <div class="col-md-4 mb-2 mb-md-0">
          <input
            class="form-control"
            v-model="search"
            placeholder="ابحث عن صلاحية..."
            name="search"
          />
        </div>
        <div class="col-md-2 text-md-end text-start">
          <Link
            :href="route('admin.permissions.create')"
            class="btn btn-success-soft btn-round w-100"
          >
            <i class="bi bi-plus"></i>
          </Link>
        </div>
      </div>

      <div class="card card-body bg-transparent pb-0 border mb-4">
        <div class="table-responsive border-0">
          <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
            <thead>
              <tr class="text-center">
                <th>#</th>
                <th>الاسم</th>
                <th>الحارس</th>
                <th>الإجراء</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(permission, index) in props.permissions.data"
                :key="permission.id"
                class="text-center"
              >
                <td>{{ props.permissions.from + index }}</td>
                <td class="fw-semibold">{{ permission.name }}</td>
                <td><span class="badge bg-light text-dark">{{ permission.guard_name }}</span></td>
                <td>
                  <div class="d-flex justify-content-center gap-2">
                    <Link
                      :href="route('admin.permissions.edit', permission.id)"
                      class="btn btn-success-soft btn-round"
                    >
                      <i class="bi bi-pencil-square"></i>
                    </Link>
                    <button
                      type="button"
                      class="btn btn-danger-soft btn-round"
                      @click="confirmDelete(permission.id)"
                    >
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!props.permissions.data.length" class="text-center">
                <td colspan="4" class="py-4 text-muted">
                  لا توجد صلاحيات مضافة حتى الآن
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="card-footer bg-transparent pt-0">
          <div class="d-sm-flex justify-content-sm-between align-items-sm-center">
            <p class="mb-0 text-center text-sm-start">
              Showing {{ props.permissions.from }} to {{ props.permissions.to }} of
              {{ props.permissions.total }} entries
            </p>
            <nav class="d-flex justify-content-center mb-0" aria-label="navigation">
              <ul
                class="pagination pagination-sm pagination-primary-soft d-inline-block d-md-flex rounded mb-0"
              >
                <li
                  v-for="(link, key) in props.permissions.links"
                  :key="key"
                  class="page-item mb-0"
                  :class="{ active: link.active, disabled: !link.url }"
                >
                  <Link v-if="link.url" class="page-link" :href="link.url">
                    <template v-if="link.label.includes('Previous')">
                      <i class="bi bi-chevron-left"></i>
                    </template>
                    <template v-else-if="link.label.includes('Next')">
                      <i class="bi bi-chevron-right"></i>
                    </template>
                    <template v-else>
                      <span v-html="link.label"></span>
                    </template>
                  </Link>

                  <span v-else class="page-link">
                    <template v-if="link.label.includes('Previous')">
                      <i class="bi bi-chevron-left"></i>
                    </template>
                    <template v-else-if="link.label.includes('Next')">
                      <i class="bi bi-chevron-right"></i>
                    </template>
                    <template v-else>
                      <span v-html="link.label"></span>
                    </template>
                  </span>
                </li>
              </ul>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
