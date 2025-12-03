<script setup>
import { ref, watch, onMounted } from 'vue'
import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { usePage } from '@inertiajs/vue3'
import Swal from 'sweetalert2'
import { toast } from 'vue3-toastify'
import { useTranslations } from '@/composables/translations'

const page = usePage()
const { t } = useTranslations()

const props = defineProps({
  products: Object, 
  filters: Object,
  categories: Array,
  brands: Array
})

// Debug: Log props when component mounts
onMounted(() => {
  console.log('=== COMPONENT MOUNTED ===')
  console.log('Props products:', props.products)
  console.log('Products data:', props.products.data)
  if (props.products.data && props.products.data.length > 0) {
    console.log('First product:', props.products.data[0])
    console.log('First product images:', props.products.data[0].images)
  }
})

// ✅ حذف منتج
function confirmDelete(id) {
  Swal.fire({
    title: t('are_you_sure'),
    text: t('this_action_cannot_be_undone'),
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: t('yes') + '، ' + t('delete'),
    cancelButtonText: t('cancel')
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(route('admin.products.destroy', id), {
        onSuccess: () => {
          Swal.fire(t('success'), t('product_deleted_successfully'), 'success')
        },
        onError: () => {
          Swal.fire(t('error'), t('operation_failed'), 'error')
        }
      })
    }
  })
}

// ✅ تفعيل/تعطيل
function toggleStatus(id) {
  router.patch(route('admin.products.status', id), {}, {
    onSuccess: () => {
      toast.success(t('product_status_updated'), {
        position: "top-right",
        autoClose: 3000,
      })
    },
    onError: () => {
      toast.error(t('operation_failed'), {
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
    router.get(route('admin.products.index'), { search: value }, {
      preserveState: true,
      replace: true,
    })
  } else {
    router.get(route('admin.products.index'), {}, {
      preserveState: true,
      replace: true,
    })
  }
})

// ✅ عرض الصورة الافتراضية
function getMainImage(product) {
  if (product.images && Array.isArray(product.images) && product.images.length > 0) {
    return product.images[0]
  }
  return '/admin/theme1/images/placeholder-image.png'
}

// ✅ عرض التقييم بالنجوم
function renderStars(rating) {
  const stars = []
  const fullStars = Math.floor(rating)
  const hasHalfStar = rating % 1 !== 0

  for (let i = 0; i < fullStars; i++) {
    stars.push('★')
  }
  if (hasHalfStar) {
    stars.push('☆')
  }
  return stars.join('')
}
</script>

<template>
  <Head title="Products" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <!-- Title & Actions -->
      <div class="row mb-3">
        <div class="col-3">
          <h1 class="h3 mb-0">{{ t('products') }}</h1>
        </div>
        <div class="col-5">
          <input
            class="form-control"
            v-model="search"
            name="search"
            :placeholder="t('search') + '...'"
          />
        </div>
        <div class="col-4 text-center d-flex gap-2 justify-content-end">
          <Link :href="route('admin.products.import')" class="btn btn-info-soft btn-round" :title="t('import_products')">
            <i class="bi bi-upload"></i>
          </Link>
          <Link :href="route('admin.products.create')" class="btn btn-success-soft btn-round" :title="t('add_product')">
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
                <th>{{ t('image') }}</th>
                <th>{{ t('product_name') }}</th>
                <th>{{ t('price') }}</th>
                <th>{{ t('stock') }}</th>
                <th>{{ t('categories') }}</th>
                <th>{{ t('brands') }}</th>
                <th>{{ t('reviews') }}</th>
                <th>{{ t('is_featured') }}</th>
                <th>{{ t('status') }}</th>
                <th>{{ t('actions') }}</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="(product, index) in props.products.data"
                :key="product.id"
                class="text-center"
              >
                <td>{{ index + 1 }}</td>

                <!-- الصورة -->
                <td>
                  <img
                    :src="getMainImage(product)"
                    alt="product image"
                    class="rounded"
                    style="width: 50px; height: 50px; object-fit: cover;"
                  />
                </td>

                <!-- اسم المنتج -->
                <td>
                  <h6 class="mb-0">{{ product.name }}</h6>
                  <small class="text-muted" v-if="product.name_en">
                    <i class="fas fa-globe"></i> {{ product.name_en }}
                  </small>
                  <small class="text-muted d-block">{{ product.sku }}</small>
                </td>

                <!-- السعر -->
                <td>
                  <div v-if="product.discount_type !== 'none' && product.discount_value > 0">
                    <span class="text-decoration-line-through text-muted">${{ product.price }}</span>
                    <br>
                    <span class="text-success fw-bold">${{ product.sale_price }}</span>
                    <br>
                    <small class="text-info">
                      {{ product.discount_type === 'percentage' ? `خصم ${product.discount_value}%` : `خصم $${product.discount_value}` }}
                    </small>
                  </div>
                  <div v-else>
                    <span class="fw-bold">${{ product.price }}</span>
                  </div>
                </td>

                <!-- المخزون -->
                <td>
                  <span
                    class="badge"
                    :class="product.stock_quantity > 0 ? 'bg-success' : 'bg-danger'"
                  >
                    {{ product.stock_quantity }}
                  </span>
                </td>

                <!-- القسم -->
                <td>
                  <span v-if="product.category">{{ product.category.name }}</span>
                  <span v-else class="text-muted">{{ t('no_data_available') }}</span>
                </td>

                <!-- العلامة التجارية -->
                <td>
                  <span v-if="product.brand">{{ product.brand.name }}</span>
                  <span v-else class="text-muted">{{ t('no_data_available') }}</span>
                </td>

                <!-- التقييم -->
                <td>
                  <div class="text-warning">
                    {{ renderStars(product.rating || 0) }}
                  </div>
                  <small class="text-muted">({{ product.reviews_count || 0 }})</small>
                </td>

                <!-- مميز -->
                <td>
                  <span
                    class="badge"
                    :class="product.is_featured ? 'bg-warning' : 'bg-secondary'"
                  >
                    {{ product.is_featured ? t('is_featured') : t('inactive') }}
                  </span>
                </td>

                <!-- الحالة -->
                <td>
                  <div class="form-check form-switch d-flex justify-content-center">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      :checked="product.status"
                      @change="toggleStatus(product.id)"
                    />
                  </div>
                </td>

                <!-- الإجراءات -->
                <td>
                  <Link
                    :href="route('admin.products.edit', product.id)"
                    class="btn btn-success-soft btn-round me-1"
                  >
                    <i class="bi bi-pencil-square"></i>
                  </Link>
                  <button
                    class="btn btn-danger-soft btn-round"
                    @click="confirmDelete(product.id)"
                  >
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>

              <!-- لما مفيش بيانات -->
              <tr v-if="!props.products.data.length" class="text-center">
                <td colspan="11" class="text-center py-4">
                  <i class="bi bi-inbox text-muted fs-4 d-block mb-2"></i>
                  <span class="text-muted">{{ t('no_data_available') }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="card-footer bg-transparent pt-0">
          <div class="d-sm-flex justify-content-sm-between align-items-sm-center">
            <p class="mb-0 text-center text-sm-start">
              Showing {{ props.products.from }} to {{ props.products.to }} of
              {{ props.products.total }} entries
            </p>
            <nav class="d-flex justify-content-center mb-0" aria-label="navigation">
              <ul
                class="pagination pagination-sm pagination-primary-soft d-inline-block d-md-flex rounded mb-0"
              >
                <li
                  v-for="(link, key) in props.products.links"
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
