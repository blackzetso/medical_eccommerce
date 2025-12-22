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
  sliders: Object,
  filters: Object
})

// ✅ حذف سلايدر
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
      router.delete(route('admin.sliders.destroy', id), {
        onSuccess: () => {
          Swal.fire('تم الحذف!', 'تم حذف السلايدر بنجاح.', 'success')
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
  router.patch(route('admin.sliders.status', id), {}, {
    onSuccess: () => {
      toast.success("تم تعديل حالة السلايدر", {
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
    router.get(route('admin.sliders.index'), { search: value }, {
      preserveState: true,
      replace: true,
    })
  } else {
    router.get(route('admin.sliders.index'), {}, {
      preserveState: true,
      replace: true,
    })
  }
})

// ✅ عرض الصورة الافتراضية
function getSliderImage(slider) {
  if (slider.image) {
    return slider.image
  }
  return '/admin/theme1/images/placeholder-image.png'
}
</script>

<template>
  <Head title="Sliders" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <!-- Title & Actions -->
      <div class="row mb-3">
        <div class="col-3">
          <h1 class="h3 mb-0">السلايدرز</h1>
        </div>
        <div class="col-7">
          <input
            class="form-control"
            v-model="search"
            name="search"
            placeholder="ابحث عن سلايدر..."
          />
        </div>
        <div class="col-2 text-center">
          <Link v-if="can('create_sliders')" :href="route('admin.sliders.create')" class="btn btn-success-soft btn-round">
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
                <th>الصورة</th>
                <th>العنوان</th>
                <th>الوصف</th>
                <th>الرابط</th>
                <th>نص الزر</th>
                <th>الترتيب</th>
                <th>الحالة</th>
                <th>الإجراءات</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="(slider, index) in props.sliders.data"
                :key="slider.id"
                class="text-center"
              >
                <td>{{ index + 1 }}</td>

                <!-- الصورة -->
                <td>
                  <img
                    :src="getSliderImage(slider)"
                    alt="slider image"
                    class="rounded"
                    style="width: 80px; height: 50px; object-fit: cover;"
                  />
                </td>

                <!-- العنوان -->
                <td>
                  <h6 class="mb-0">{{ slider.title }}</h6>
                </td>

                <!-- الوصف -->
                <td>
                  <div class="text-muted" style="max-width: 200px;">
                    {{ slider.description ? slider.description.substring(0, 50) + (slider.description.length > 50 ? '...' : '') : '-' }}
                  </div>
                </td>

                <!-- الرابط -->
                <td>
                  <a v-if="slider.link" :href="slider.link" target="_blank" class="text-primary">
                    <i class="bi bi-link-45deg"></i> رابط
                  </a>
                  <span v-else class="text-muted">-</span>
                </td>

                <!-- نص الزر -->
                <td>
                  <span v-if="slider.button_text" class="badge bg-info">
                    {{ slider.button_text }}
                  </span>
                  <span v-else class="text-muted">-</span>
                </td>

                <!-- الترتيب -->
                <td>
                  <span class="badge bg-secondary">
                    {{ slider.sort_order }}
                  </span>
                </td>

                <!-- الحالة -->
                <td>
                  <div class="form-check form-switch d-flex justify-content-center">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      :checked="slider.status"
                      @change="toggleStatus(slider.id)"
                    />
                  </div>
                </td>

                <!-- الإجراءات -->
                <td>
                  <Link
                    v-if="can('update_sliders')"
                    :href="route('admin.sliders.edit', slider.id)"
                    class="btn btn-success-soft btn-round me-1"
                  >
                    <i class="bi bi-pencil-square"></i>
                  </Link>
                  <button
                    v-if="can('delete_sliders')"
                    class="btn btn-danger-soft btn-round"
                    @click="confirmDelete(slider.id)"
                  >
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>

              <!-- لما مفيش بيانات -->
              <tr v-if="!props.sliders.data.length" class="text-center">
                <td colspan="9" class="text-center py-4">
                  <i class="bi bi-inbox text-muted fs-4 d-block mb-2"></i>
                  <span class="text-muted">لا توجد سلايدرز</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="card-footer bg-transparent pt-0">
          <div class="d-sm-flex justify-content-sm-between align-items-sm-center">
            <p class="mb-0 text-center text-sm-start">
              Showing {{ props.sliders.from }} to {{ props.sliders.to }} of
              {{ props.sliders.total }} entries
            </p>
            <nav class="d-flex justify-content-center mb-0" aria-label="navigation">
              <ul
                class="pagination pagination-sm pagination-primary-soft d-inline-block d-md-flex rounded mb-0"
              >
                <li
                  v-for="(link, key) in props.sliders.links"
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
