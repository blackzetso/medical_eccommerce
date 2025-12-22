<script setup>
import { ref, computed, onMounted } from 'vue'
import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'
import { Head, useForm, router, usePage } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import Swal from 'sweetalert2'

const page = usePage()
const can = (permission) => page.props.auth?.permissions?.includes(permission)

const props = defineProps({
  attributes: Object,
  filters: Object
})

const searchForm = useForm({
  search: props.filters.search || '',
})

// البحث
const search = () => {
  searchForm.get(route('admin.attributes.index'), {
    preserveState: true,
    replace: true,
  })
}

// مسح البحث
const clearSearch = () => {
  searchForm.search = ''
  searchForm.get(route('admin.attributes.index'), {
    preserveState: true,
    replace: true,
  })
}

// تغيير حالة الخاصية
const toggleStatus = (id, currentStatus) => {
  Swal.fire({
    title: currentStatus ? 'إلغاء تفعيل الخاصية؟' : 'تفعيل الخاصية؟',
    text: currentStatus ? 'سيتم إلغاء تفعيل هذه الخاصية' : 'سيتم تفعيل هذه الخاصية',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: currentStatus ? '#dc3545' : '#28a745',
    cancelButtonColor: '#6c757d',
    confirmButtonText: currentStatus ? 'إلغاء التفعيل' : 'تفعيل',
    cancelButtonText: 'إلغاء'
  }).then((result) => {
    if (result.isConfirmed) {
      router.patch(route('admin.attributes.status', id))
    }
  })
}

// حذف خاصية
const deleteAttribute = (id, name) => {
  Swal.fire({
    title: 'هل أنت متأكد؟',
    text: `سيتم حذف خاصية "${name}" نهائياً`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'حذف',
    cancelButtonText: 'إلغاء'
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(route('admin.attributes.destroy', id))
    }
  })
}

// عرض قيم الخاصية
const getAttributeValues = (attribute) => {
  if (!attribute.values || attribute.values.length === 0) {
    return 'لا توجد قيم'
  }
  return attribute.values.slice(0, 3).map(v => v.value).join(', ') +
         (attribute.values.length > 3 ? ` +${attribute.values.length - 3}` : '')
}
</script>

<template>
  <Head title="Attributes" />
  <AppLayout>
    <div class="page-content-wrapper border">


        <div class="card-body px-1 px-sm-4">
        <!-- Title & Actions -->
        <div class="row g-4 justify-content-between align-items-center">
          <div class="col">
            <h1 class="h3 mb-0">خصائص المنتجات</h1>
            <span class="text-muted">إدارة خصائص المنتجات مثل الأحجام والألوان</span>
          </div>
          <div class="col-auto">
            <Link
              v-if="can('create_attributes')"
              :href="route('admin.attributes.create')"
              class="btn btn-primary mb-0"
            >
              <i class="fas fa-plus me-2"></i>
              إضافة خاصية جديدة
            </Link>
          </div>
        </div>

        <!-- Search & Filters -->
        <div class="row g-3 align-items-center justify-content-between mb-4 mt-4">
          <div class="col-md-6 col-lg-4">
            <form @submit.prevent="search">
              <div class="input-group">
                <input
                  type="text"
                  class="form-control"
                  placeholder="البحث في الخصائص..."
                  v-model="searchForm.search"
                />
                <button class="btn btn-primary-soft" type="submit">
                  <i class="fas fa-search fa-fw"></i>
                </button>
                <button
                  class="btn btn-outline-secondary"
                  type="button"
                  @click="clearSearch"
                  v-if="searchForm.search"
                >
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </form>
          </div>
          <div class="col-md-6 col-lg-8 d-flex justify-content-end">
            <span class="text-muted">
              إجمالي {{ props.attributes.total || 0 }} خاصية
            </span>
          </div>
        </div>

      <!-- Table -->
      <div class="card card-body bg-transparent pb-0 border mb-4">
        <div class="table-responsive border-0">
          <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
            <thead>
              <tr class="text-center">
                <th>#</th>
                <th>اسم الخاصية</th>
                <th>النوع</th>
                <th>القيم</th>
                <th>الحالة</th>
                <th>الإجراءات</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(attribute, index) in props.attributes.data"
                :key="attribute.id"
                class="text-center"
              >
                <td>{{ index + 1 }}</td>

                <!-- اسم الخاصية -->
                <td>
                  <h6 class="mb-0">{{ attribute.name }}</h6>
                  <small class="text-muted">{{ attribute.slug }}</small>
                </td>

                <!-- النوع -->
                <td>
                  <span class="badge bg-info">
                    {{ attribute.type === 'select' ? 'قائمة' :
                       attribute.type === 'radio' ? 'اختيار واحد' : 'اختيار متعدد' }}
                  </span>
                </td>

                <!-- القيم -->
                <td>
                  <div class="d-flex flex-wrap gap-1 justify-content-center">
                    <span
                      v-for="value in attribute.values.slice(0, 3)"
                      :key="value.id"
                      class="badge bg-secondary"
                    >
                      {{ value.value }}
                    </span>
                    <span v-if="attribute.values.length > 3" class="badge bg-light text-dark">
                      +{{ attribute.values.length - 3 }}
                    </span>
                  </div>
                  <small class="text-muted">{{ attribute.values.length }} قيمة</small>
                </td>

                <!-- الحالة -->
                <td>
                  <div class="form-check form-switch d-flex justify-content-center">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      :checked="attribute.status"
                      @change="toggleStatus(attribute.id, attribute.status)"
                    />
                  </div>
                </td>

                <!-- الإجراءات -->
                <td>
                  <Link
                    v-if="can('update_attributes')"
                    :href="route('admin.attributes.edit', attribute.id)"
                    class="btn btn-success-soft btn-round me-1"
                  >
                    <i class="bi bi-pencil-square"></i>
                  </Link>
                  <button
                    v-if="can('delete_attributes')"
                    class="btn btn-danger-soft btn-round"
                    @click="deleteAttribute(attribute.id, attribute.name)"
                  >
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div class="d-sm-flex justify-content-sm-between align-items-sm-center mt-4 mt-sm-3" v-if="props.attributes.links">
        <p class="mb-0 text-muted">
          عرض {{ props.attributes.from }} إلى {{ props.attributes.to }} من {{ props.attributes.total }} خاصية
        </p>
        <nav class="d-flex justify-content-center mb-0">
          <ul class="pagination pagination-sm pagination-primary-soft mb-0 pb-0">
            <li v-for="link in props.attributes.links" :key="link.label" class="page-item" :class="{ active: link.active, disabled: !link.url }">
              <Link v-if="link.url" :href="link.url" class="page-link" v-html="link.label"></Link>
              <span v-else class="page-link" v-html="link.label"></span>
            </li>
          </ul>
        </nav>
      </div>

      <!-- Empty State -->
      <div v-if="!props.attributes.data.length" class="text-center py-5">
        <i class="fas fa-tags fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">لا توجد خصائص</h5>
        <p class="text-muted">قم بإضافة خاصية جديدة للمنتجات</p>
        <Link
          v-if="can('create_attributes')"
          :href="route('admin.attributes.create')"
          class="btn btn-primary"
        >
          إضافة خاصية جديدة
        </Link>
      </div>
      </div>
    </div>
  </AppLayout>
</template>
