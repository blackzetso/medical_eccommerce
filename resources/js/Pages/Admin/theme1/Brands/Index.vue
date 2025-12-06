<script setup>
import { ref, watch } from 'vue'
import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { usePage } from '@inertiajs/vue3'
import Swal from 'sweetalert2'
import { toast } from 'vue3-toastify'

const page = usePage()

const props = defineProps({
  brands: Object,
  filters: Object
})

// ✅ حذف علامة تجارية
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
      router.delete(route('admin.brands.destroy', id), {
        onSuccess: () => {
          Swal.fire('تم الحذف!', 'تم حذف العلامة التجارية بنجاح.', 'success')
        },
        onError: () => {
          Swal.fire('خطأ!', 'حدثت مشكلة أثناء الحذف.', 'error')
        }
      })
    }
  })
}

// ✅ تفعيل/تعطيل
function toggleStatus(id) {
  router.patch(route('admin.brands.status', id), {}, {
    onSuccess: () => {
      toast.success("تم تعديل حالة العلامة التجارية", {
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

// ✅ البحث
const search = ref(props.filters?.search ?? '')
watch(search, (value) => {
  if (value) {
    router.get(route('admin.brands.index'), { search: value }, {
      preserveState: true,
      replace: true,
    })
  } else {
    router.get(route('admin.brands.index'), {}, {
      preserveState: true,
      replace: true,
    })
  }
})

// ✅ عرض اللوجو باستخدام logo_url من النموذج
function getLogo(brand) {
  if (brand.logo_url && brand.logo_url.trim() !== '') {
    return brand.logo_url
  }
  // استخدام صورة placeholder إذا لم يكن هناك لوجو
  return 'https://via.placeholder.com/50x50/e9ecef/6c757d?text=Logo'
}
</script>

<template>
  <Head title="Brands" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <!-- Title & Actions -->
      <div class="row mb-3">
        <div class="col-3">
          <h1 class="h3 mb-0">العلامات التجارية</h1>
        </div>
        <div class="col-7">
          <input
            class="form-control"
            v-model="search"
            name="search"
            placeholder="ابحث عن علامة تجارية..."
          />
        </div>
        <div class="col-2 text-center">
          <Link :href="route('admin.brands.create')" class="btn btn-success-soft btn-round">
            <i class="bi bi-plus"></i>
          </Link>
        </div>
      </div>

      <!-- Table -->
      <div class="card card-body bg-transparent pb-0 border mb-4">
        <div class="table-responsive border-0">
          <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
            <thead>
              <tr class="text-center">
                <th>#</th>
                <th>اللوجو</th>
                <th>اسم العلامة التجارية</th>
                <th>الرابط الثابت</th>
                <th>الموقع الإلكتروني</th>
                <th>الحالة</th>
                <th>الإجراءات</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="(brand, index) in props.brands.data"
                :key="brand.id"
                class="text-center"
              >
                <td>{{ index + 1 }}</td>

                <!-- اللوجو -->
                <td>
                  <img
                    :src="getLogo(brand)"
                    alt="brand logo"
                    class="rounded"
                    style="width: 50px; height: 50px; object-fit: contain;"
                  />
                </td>

                <!-- اسم العلامة التجارية -->
                <td>
                  <h6 class="mb-0">{{ brand.name }}</h6>
                </td>

                <!-- الرابط الثابت -->
                <td>
                  <span class="text-muted">{{ brand.slug }}</span>
                </td>

                <!-- الموقع الإلكتروني -->
                <td>
                  <a
                    v-if="brand.website"
                    :href="brand.website"
                    target="_blank"
                    class="text-primary"
                  >
                    <i class="bi bi-globe"></i>
                  </a>
                  <span v-else class="text-muted">-</span>
                </td>

                <!-- الحالة -->
                <td>
                  <div class="form-check form-switch d-flex justify-content-center">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      :checked="brand.status"
                      @change="toggleStatus(brand.id)"
                    />
                  </div>
                </td>

                <!-- الإجراءات -->
                <td>
                  <Link
                    :href="route('admin.brands.edit', brand.id)"
                    class="btn btn-success-soft btn-round me-1"
                  >
                    <i class="bi bi-pencil-square"></i>
                  </Link>
                  <button
                    class="btn btn-danger-soft btn-round"
                    @click="confirmDelete(brand.id)"
                  >
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>

              <!-- لما مفيش بيانات -->
              <tr v-if="!props.brands.data.length" class="text-center">
                <td colspan="7" class="text-center py-4">
                  <i class="bi bi-inbox text-muted fs-4 d-block mb-2"></i>
                  <span class="text-muted">لا توجد علامات تجارية</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="card-footer bg-transparent pt-0">
          <div class="d-sm-flex justify-content-sm-between align-items-sm-center">
            <p class="mb-0 text-center text-sm-start">
              Showing {{ props.brands.from }} to {{ props.brands.to }} of
              {{ props.brands.total }} entries
            </p>
            <nav class="d-flex justify-content-center mb-0" aria-label="navigation">
              <ul
                class="pagination pagination-sm pagination-primary-soft d-inline-block d-md-flex rounded mb-0"
              >
                <li
                  v-for="(link, key) in props.brands.links"
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
