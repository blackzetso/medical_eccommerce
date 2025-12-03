<script setup>
// دالة لإرجاع رابط صورة القسم بشكل صحيح
const getCategoryImage = (category) => {
  if (category.image) {
    let img = category.image;
    // إذا كان المسار يبدأ بـ http://uploads أو https://uploads، حوله لمسار نسبي
    if (img.startsWith('http://uploads') || img.startsWith('https://uploads')) {
      // استخراج اسم الصورة فقط
      const parts = img.split('/');
      img = '/uploads/categories/' + parts[parts.length - 1];
    } else if (!img.startsWith('/')) {
      img = '/uploads/categories/' + img;
    }
    return img;
  }
  return '/default-placeholder.png';
};
import { ref, watch } from 'vue'
import CategoryCard from '@/Components/CategoryCard.vue'
import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { usePage } from '@inertiajs/vue3'
import Swal from 'sweetalert2'
import { toast } from 'vue3-toastify'

const page = usePage()

const props = defineProps({
  categories: Object,
  filters: Object
})

// ✅ حذف قسم
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
      router.delete(route('admin.categories.destroy', id), {
        onSuccess: () => {
          Swal.fire('تم الحذف!', 'تم حذف القسم بنجاح.', 'success')
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
  router.patch(route('admin.categories.status', id), {}, {
    onSuccess: () => {
      toast.success("تم تعديل حالة القسم", {
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
    router.get(route('admin.categories.search', value), {}, {
      preserveState: true,
      replace: true,
    })
  } else {
    router.get(route('admin.categories.index'), {}, {
      preserveState: true,
      replace: true,
    })
  }
})
</script>

<template>
  <Head title="Categories" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <!-- Title & Actions -->
      <div class="row mb-3">
        <div class="col-3">
          <h1 class="h3 mb-0">Categories</h1>
        </div>
        <div class="col-7">
          <input
            class="form-control"
            v-model="search"
            name="search"
            placeholder="ابحث عن قسم..."
          />
        </div>
        <div class="col-2 text-center">
          <Link :href="route('admin.categories.create')" class="btn btn-success-soft btn-round">
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
                <th>Image</th>
                <th>Category Name</th>
                <th>Parent Category</th>
                <th>Enable/Disable</th>
                <th>Action</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="(category, index) in props.categories.data"
                :key="category.id"
                class="text-center"
              >
                <td>{{ index + 1 }}</td>
                <td>
                  <CategoryCard
                    :image="getCategoryImage(category)"
                    :name="category.name"
                  />
                </td>
                <td>
                  <h6 class="mb-0">{{ category.name }}</h6>
                </td>
                <td>
                  <span v-if="category.parent">{{ category.parent.name }}</span>
                  <span v-else class="text-muted">قسم رئيسي</span>
                </td>
                <td>
                  <div class="form-check form-switch d-flex justify-content-center">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      :checked="category.status === 'enable'"
                      @change="toggleStatus(category.id)"
                    />
                  </div>
                </td>
                <td>
                  <Link
                    :href="route('admin.categories.edit', category.id)"
                    class="btn btn-success-soft btn-round me-1"
                  >
                    <i class="bi bi-pencil-square"></i>
                  </Link>
                  <button
                    class="btn btn-danger-soft btn-round"
                    @click="confirmDelete(category.id)"
                  >
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>

              <!-- لما مفيش بيانات -->
              <tr v-if="!props.categories.data.length" class="text-center">
                <td colspan="6" class="text-center py-4">
                  <i class="bi bi-inbox text-muted fs-4 d-block mb-2"></i>
                  <span class="text-muted">لا توجد بيانات</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="card-footer bg-transparent pt-0">
          <div class="d-sm-flex justify-content-sm-between align-items-sm-center">
            <p class="mb-0 text-center text-sm-start">
              Showing {{ props.categories.from }} to {{ props.categories.to }} of
              {{ props.categories.total }} entries
            </p>
            <nav class="d-flex justify-content-center mb-0" aria-label="navigation">
              <ul
                class="pagination pagination-sm pagination-primary-soft d-inline-block d-md-flex rounded mb-0"
              >
                <li
                  v-for="(link, key) in props.categories.links"
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
