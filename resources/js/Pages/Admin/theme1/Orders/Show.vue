<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { usePage } from '@inertiajs/vue3'
import Swal from 'sweetalert2'
import { toast } from 'vue3-toastify'

const page = usePage()

const props = defineProps({
  order: Object
})

// طباعة الفاتورة
function printInvoice() {
  window.print()
}

onMounted(() => {
  // Component mounted
})

// تحديث حالة الطلب
function updateOrderStatus(newStatus) {
  Swal.fire({
    title: 'تأكيد تحديث الحالة',
    text: `هل تريد تحديث حالة الطلب؟`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'نعم، حدث',
    cancelButtonText: 'إلغاء'
  }).then((result) => {
    if (result.isConfirmed) {
      router.patch(route('admin.orders.updateStatus', props.order.id), {
        status: newStatus
      }, {
        onSuccess: () => {
          toast.success("تم تحديث حالة الطلب بنجاح", {
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
  })
}

// تحديث حالة الدفع
function updatePaymentStatus(newStatus) {
  Swal.fire({
    title: 'تأكيد تحديث حالة الدفع',
    text: `هل تريد تحديث حالة الدفع؟`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'نعم، حدث',
    cancelButtonText: 'إلغاء'
  }).then((result) => {
    if (result.isConfirmed) {
      router.patch(route('admin.orders.updatePaymentStatus', props.order.id), {
        payment_status: newStatus
      }, {
        onSuccess: () => {
          toast.success("تم تحديث حالة الدفع بنجاح", {
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
  })
}

// دالة لتحديد لون الحالة
function getStatusBadge(status) {
  const badges = {
    'pending': 'badge bg-warning',
    'processing': 'badge bg-info',
    'shipped': 'badge bg-purple',
    'delivered': 'badge bg-success',
    'cancelled': 'badge bg-danger'
  }
  return badges[status] || 'badge bg-secondary'
}

function getPaymentStatusBadge(status) {
  const badges = {
    'pending': 'badge bg-warning',
    'paid': 'badge bg-success',
    'failed': 'badge bg-danger',
    'refunded': 'badge bg-purple'
  }
  return badges[status] || 'badge bg-secondary'
}

// تنسيق التاريخ
function formatDate(dateString) {
  return new Date(dateString).toLocaleDateString('ar-EG', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// ترجمة الحالات
const statusLabels = {
  'pending': 'جديد',
  'processing': 'قيد التجهيز',
  'shipped': 'خرج للتوصيل',
  'delivered': 'تم التسليم',
  'cancelled': 'ملغي'
}

const paymentStatusLabels = {
  'pending': 'في الانتظار',
  'paid': 'مدفوع',
  'failed': 'فشل الدفع',
  'refunded': 'مسترد'
}

// تحويل رابط مشاركة Google Maps إلى رابط embed
function getEmbedUrl(url) {
  if (!url) return '';
  
  // إذا كان الرابط بالفعل embed URL
  if (url.includes('google.com/maps/embed')) {
    return url;
  }
  
  // إذا كان رابط مشاركة (maps.app.goo.gl أو goo.gl)
  if (url.includes('maps.app.goo.gl') || url.includes('goo.gl/maps')) {
    // محاولة استخراج place_id أو coordinates من الرابط
    const placeIdMatch = url.match(/place_id=([^&]+)/);
    if (placeIdMatch) {
      return `https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3000!2d0!3d0!4m2!3m1!1s${placeIdMatch[1]}!5e0!3m2!1sen!2sus!4v1234567890!5m2!1sen!2sus`;
    }
    
    const coordMatch = url.match(/@(-?\d+\.?\d*),(-?\d+\.?\d*)/);
    if (coordMatch) {
      const lat = coordMatch[1];
      const lng = coordMatch[2];
      return `https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3000!2d${lng}!3d${lat}!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2z${lat}%2C${lng}!5e0!3m2!1sen!2sus!4v1234567890!5m2!1sen!2sus`;
    }
    
    return null;
  }
  
  // إذا كان رابط Google Maps عادي
  if (url.includes('google.com/maps')) {
    const placeIdMatch = url.match(/place\/([^\/]+)/);
    if (placeIdMatch) {
      const placeId = placeIdMatch[1];
      return `https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3000!2d0!3d0!4m2!3m1!1s${placeId}!5e0!3m2!1sen!2sus!4v1234567890!5m2!1sen!2sus`;
    }
    
    const coordMatch = url.match(/@(-?\d+\.?\d*),(-?\d+\.?\d*)/);
    if (coordMatch) {
      const lat = coordMatch[1];
      const lng = coordMatch[2];
      return `https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3000!2d${lng}!3d${lat}!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2z${lat}%2C${lng}!5e0!3m2!1sen!2sus!4v1234567890!5m2!1sen!2sus`;
    }
  }
  
  return url;
}

// الحصول على أول صورة للمنتج
function getProductImage(product) {
  if (!product) {
    return '/admin/theme1/images/placeholder-image.png'
  }

  let images = product.images

  // If images is null or undefined
  if (!images) {
    return '/admin/theme1/images/placeholder-image.png'
  }

  // If images is a string (JSON), parse it
  if (typeof images === 'string') {
    console.log('Images is string, parsing...')
    try {
      images = JSON.parse(images)
      console.log('Parsed images:', images)
    } catch (e) {
      console.error('Failed to parse images:', e)
      return '/admin/theme1/images/placeholder-image.png'
    }
  }

  // Check if images is an array with at least one item
  if (Array.isArray(images) && images.length > 0) {
    const imagePath = images[0]

    // Clean the path and ensure correct format
    let cleanPath = imagePath

    // Remove leading slashes
    if (cleanPath.startsWith('/')) {
      cleanPath = cleanPath.substring(1)
    }

    // If path already includes storage/, use it as is
    if (cleanPath.startsWith('storage/')) {
      return `/${cleanPath}`
    }

    // If path starts with public/, replace with storage/
    if (cleanPath.startsWith('public/')) {
      return `/${cleanPath.replace('public/', 'storage/')}`
    }

    // Otherwise, add storage/ prefix
    return `/storage/${cleanPath}`
  }

  return '/admin/theme1/images/placeholder-image.png'
}
</script>

<template>
  <Head :title="`تفاصيل الطلب #${order.order_number}`" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <div class="card-body px-1 px-sm-4">
        <!-- Header -->
        <div class="row mb-4 no-print page-title-row">
          <div class="col-6">
            <h4 class="page-title">تفاصيل الطلب #{{ order.order_number }}</h4>
            <small class="text-muted">طلب بتاريخ {{ formatDate(order.created_at) }}</small>
          </div>
          <div class="col-6 text-end">
            <button @click="printInvoice" class="btn btn-success me-2">
              <i class="fas fa-print me-2"></i> طباعة الفاتورة
            </button>
            <Link :href="route('admin.orders.index')" class="btn btn-secondary">
              <i class="fas fa-arrow-left me-2"></i> رجوع للقائمة
            </Link>
          </div>
        </div>

        <!-- Invoice Header for Print -->
        <div class="invoice-header print-only mb-3">
          <div class="text-center">
            <h3 class="invoice-title">فاتورة</h3>
            <div class="invoice-meta">
              <span>رقم: #{{ order.order_number }}</span>
              <span>التاريخ: {{ formatDate(order.created_at) }}</span>
            </div>
          </div>
          <div class="invoice-customer mt-2">
            <div><strong>العميل:</strong> {{ order.user?.name || 'غير محدد' }}</div>
            <div v-if="order.billing_address?.phone"><strong>الهاتف:</strong> {{ order.billing_address.phone }}</div>
            <div><strong>نوع التوصيل:</strong> {{ order.delivery_type === 'delivery' ? 'توصيل' : 'استلام من المحل' }}</div>
            <div v-if="order.billing_address?.address">
              <strong>العنوان:</strong>
              {{ order.billing_address.address }}
              <span v-if="order.billing_address.city"> - {{ order.billing_address.city }}</span>
              <span v-if="order.billing_address.state">، {{ order.billing_address.state }}</span>
              <span v-if="order.billing_address.postal_code"> {{ order.billing_address.postal_code }}</span>
              <span v-if="order.billing_address.country"> - {{ order.billing_address.country }}</span>
            </div>
          </div>
        </div>

        <div class="row g-4">
          <!-- معلومات الطلب الرئيسية -->
          <div class="col-12">
            <div class="card">
              <div class="card-header no-print">
                <h5 class="card-title mb-0">معلومات الطلب</h5>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-3 col-6 order-number-block">
                    <strong>رقم الطلب:</strong>
                    <div class="text-primary">#{{ order.order_number }}</div>
                  </div>
                  <div class="col-md-3 col-6">
                    <strong>العميل:</strong>
                    <div>{{ order.user?.name || 'غير محدد' }}</div>
                    <small class="text-muted">{{ order.user?.email || '' }}</small>
                  </div>
                  <div class="col-md-3 col-6">
                    <strong>المبلغ الإجمالي:</strong>
                    <div class="fw-bold">{{ Number(order.total_amount).toFixed(2) }} {{ order.currency }}</div>
                    <small class="text-muted">{{ order.items?.length || 0 }} عنصر</small>
                  </div>
                  <div class="col-md-3 col-6">
                    <strong>نوع التوصيل:</strong>
                    <div>
                      <span class="badge" :class="order.delivery_type === 'delivery' ? 'bg-info' : 'bg-success'">
                        <i :class="order.delivery_type === 'delivery' ? 'fas fa-truck' : 'fas fa-store'" class="me-1"></i>
                        {{ order.delivery_type === 'delivery' ? 'توصيل' : 'استلام من المحل' }}
                      </span>
                    </div>
                  </div>
                  <div class="col-md-3 col-6">
                    <strong>طريقة الدفع:</strong>
                    <div>{{ order.payment_method || 'غير محدد' }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- إدارة الحالات -->
          <div class="col-12 no-print">
            <div class="card">
              <div class="card-header">
                <h5 class="card-title mb-0">إدارة حالة الطلب</h5>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">حالة الطلب</label>
                    <div class="d-flex align-items-center gap-3">
                      <span :class="getStatusBadge(order.status)">
                        {{ statusLabels[order.status] }}
                      </span>
                      <select
                        :value="order.status"
                        @change="updateOrderStatus($event.target.value)"
                        class="form-select form-select-sm"
                      >
                        <option v-for="(label, key) in statusLabels" :key="key" :value="key">
                          {{ label }}
                        </option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">حالة الدفع</label>
                    <div class="d-flex align-items-center gap-3">
                      <span :class="getPaymentStatusBadge(order.payment_status)">
                        {{ paymentStatusLabels[order.payment_status] }}
                      </span>
                      <select
                        :value="order.payment_status"
                        @change="updatePaymentStatus($event.target.value)"
                        class="form-select form-select-sm"
                      >
                        <option v-for="(label, key) in paymentStatusLabels" :key="key" :value="key">
                          {{ label }}
                        </option>
                      </select>
                    </div>
                  </div>
                </div>

                <!-- التواريخ المهمة -->
                <div class="row mt-3" v-if="order.shipped_at || order.delivered_at">
                  <div class="col-md-4" v-if="order.shipped_at">
                    <small class="text-muted">تاريخ الشحن:</small>
                    <div>{{ formatDate(order.shipped_at) }}</div>
                  </div>
                  <div class="col-md-4" v-if="order.delivered_at">
                    <small class="text-muted">تاريخ التسليم:</small>
                    <div>{{ formatDate(order.delivered_at) }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- عناصر الطلب -->
          <div class="col-12">
            <div class="card">
              <div class="card-header no-print">
                <h5 class="card-title mb-0">عناصر الطلب</h5>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-hover invoice-table">
                    <thead>
                      <tr>
                        <th>المنتج</th>
                        <th>الخصائص</th>
                        <th>اللون</th>
                        <th>الكمية</th>
                        <th>السعر</th>
                        <th>الإجمالي</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="item in order.items" :key="item.id">
                        <td>
                          <div class="d-flex align-items-center">
                            <img
                              :src="getProductImage(item.product)"
                              :alt="item.product_name"
                              class="rounded me-3 no-print"
                              style="width: 50px; height: 50px; object-fit: cover;"
                            />
                            <div>
                              <div class="fw-bold">{{ item.product_name }}</div>
                              <small class="text-muted" v-if="item.product_sku">
                                كود: {{ item.product_sku }}
                              </small>
                            </div>
                          </div>
                        </td>
                        <!-- عمود الخصائص -->
                        <td>
                          <div v-if="item.selected_attributes && item.selected_attributes.length">
                            <small
                              v-for="attr in item.selected_attributes"
                              :key="`${item.id}-attr-${attr.attribute_id}`"
                              class="d-block text-muted"
                            >
                              {{ attr.attribute_name }}:
                              <span v-if="attr.value_color_code" class="ms-1">
                                <span
                                  class="d-inline-block rounded-circle me-1"
                                  :style="{
                                    width: '10px',
                                    height: '10px',
                                    backgroundColor: attr.value_color_code,
                                    border: '1px solid #ccc'
                                  }"
                                ></span>
                              </span>
                              {{ attr.value_label }}
                            </small>
                          </div>
                        </td>
                        <!-- عمود اللون -->
                        <td>
                          <div v-if="item.color" class="d-flex align-items-center">
                            <span
                              class="d-inline-block rounded-circle me-2"
                              :style="{
                                width: '16px',
                                height: '16px',
                                backgroundColor: item.color,
                                border: '1px solid #ccc'
                              }"
                            ></span>
                            <small class="text-muted">{{ item.color }}</small>
                          </div>
                        </td>
                        <td>{{ item.quantity }}</td>
                        <td>{{ Number(item.price).toFixed(2) }} {{ order.currency }}</td>
                        <td class="fw-bold">{{ Number(item.total).toFixed(2) }} {{ order.currency }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- ملخص الطلب -->
                <div class="row mt-4">
                  <div class="col-md-6 offset-md-6">
                    <table class="table table-sm">
                      <tr>
                        <td>المجموع الفرعي:</td>
                        <td class="text-end">{{ Number(order.subtotal).toFixed(2) }} {{ order.currency }}</td>
                      </tr>
                      <tr v-if="order.tax_amount > 0">
                        <td>الضرائب:</td>
                        <td class="text-end">{{ Number(order.tax_amount).toFixed(2) }} {{ order.currency }}</td>
                      </tr>
                      <tr v-if="order.shipping_amount > 0">
                        <td>رسوم الشحن:</td>
                        <td class="text-end">{{ Number(order.shipping_amount).toFixed(2) }} {{ order.currency }}</td>
                      </tr>
                      <tr v-if="order.discount_amount > 0">
                        <td>الخصم:</td>
                        <td class="text-end text-success">-{{ Number(order.discount_amount).toFixed(2) }} {{ order.currency }}</td>
                      </tr>
                      <tr class="table-active">
                        <td class="fw-bold">المجموع الكلي:</td>
                        <td class="text-end fw-bold">{{ Number(order.total_amount).toFixed(2) }} {{ order.currency }}</td>
                      </tr>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- العناوين -->
          <div class="col-md-6" v-if="order.billing_address">
            <div class="card">
              <div class="card-header">
                <h5 class="card-title mb-0">عنوان الفواتير</h5>
              </div>
              <div class="card-body">
                <div v-if="order.billing_address.name">
                  <strong>{{ order.billing_address.name }}</strong>
                </div>
                <div v-if="order.billing_address.address">{{ order.billing_address.address }}</div>
                <div v-if="order.billing_address.city">
                  {{ order.billing_address.city }}{{ order.billing_address.state ? ', ' + order.billing_address.state : '' }}
                </div>
                <div v-if="order.billing_address.postal_code">{{ order.billing_address.postal_code }}</div>
                <div v-if="order.billing_address.country">{{ order.billing_address.country }}</div>
                <div v-if="order.billing_address.phone" class="mt-2">
                  <i class="bi bi-telephone"></i> {{ order.billing_address.phone }}
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-6" v-if="order.shipping_address">
            <div class="card">
              <div class="card-header">
                <h5 class="card-title mb-0">عنوان الشحن</h5>
              </div>
              <div class="card-body">
                <div v-if="order.shipping_address.name">
                  <strong>{{ order.shipping_address.name }}</strong>
                </div>
                <div v-if="order.shipping_address.address">{{ order.shipping_address.address }}</div>
                <div v-if="order.shipping_address.city">
                  {{ order.shipping_address.city }}{{ order.shipping_address.state ? ', ' + order.shipping_address.state : '' }}
                </div>
                <div v-if="order.shipping_address.postal_code">{{ order.shipping_address.postal_code }}</div>
                <div v-if="order.shipping_address.country">{{ order.shipping_address.country }}</div>
                <div v-if="order.shipping_address.phone" class="mt-2">
                  <i class="bi bi-telephone"></i> {{ order.shipping_address.phone }}
                </div>
              </div>
            </div>
          </div>

          <!-- خريطة الموقع -->
          <div class="col-12 no-print" v-if="order.user?.location_url">
            <div class="card">
              <div class="card-header">
                <h5 class="card-title mb-0">موقع العميل</h5>
              </div>
              <div class="card-body p-0">
                <div v-if="getEmbedUrl(order.user.location_url)">
                  <iframe
                    :src="getEmbedUrl(order.user.location_url)"
                    width="100%"
                    height="450"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                  ></iframe>
                </div>
                <div v-else class="p-4 text-center">
                  <div class="alert alert-warning mb-0">
                    <strong>تنبيه:</strong> الرابط المدخل هو رابط مشاركة وليس رابط embed.
                    <br>
                    <small>يرجى استخدام رابط embed من Google Maps. يمكنك الحصول عليه من: مشاركة → تضمين خريطة</small>
                    <br>
                    <a :href="order.user.location_url" target="_blank" class="btn btn-sm btn-primary mt-2">
                      فتح الموقع في Google Maps
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- ملاحظات الطلب -->
          <div class="col-12" v-if="order.notes">
            <div class="card">
              <div class="card-header">
                <h5 class="card-title mb-0">ملاحظات الطلب</h5>
              </div>
              <div class="card-body">
                <div class="alert alert-info">
                  {{ order.notes }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
/* Print Styles for POS 80mm Printer */
@media print {
  /* Hide elements that shouldn't be printed */
  .no-print {
    display: none !important;
  }

  /* Show print-only elements */
  .print-only {
    display: block !important;
  }

  /* Set page size to 80mm width (thermal printer) */
  @page {
    size: 80mm auto;
    margin: 5mm;
  }

  body {
    width: 80mm;
    font-size: 10px;
    line-height: 1.3;
    color: #000;
    background: white;
  }

  /* Invoice header styling */
  .invoice-header {
    border-bottom: 1px dashed #000;
    padding-bottom: 6px;
    margin-bottom: 8px;
    font-size: 10px;
  }
  .invoice-title { margin:0; font-size:12px; font-weight:700; }
  .invoice-meta { display:flex; justify-content:space-between; font-size:9px; margin-top:2px; }
  .invoice-meta span { display:inline-block; }
  .invoice-customer { font-size:9px; line-height:1.3; }
  .invoice-customer strong { font-weight:600; }

  /* Card styling */
  .card {
    border: none !important;
    box-shadow: none !important;
    margin-bottom: 10px;
  }

  .card-header {
    display: none;
  }

  .card-body {
    padding: 5px !important;
  }

  /* Table styling */
  .invoice-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 9px;
    margin-bottom: 5px;
  }

  .invoice-table th,
  .invoice-table td {
    padding: 4px 2px;
    border-bottom: 1px solid #ddd;
    text-align: right;
  }

  .invoice-table thead {
    border-bottom: 2px solid #000;
  }

  .invoice-table th {
    font-weight: bold;
    font-size: 10px;
  }

  .invoice-table td .fw-bold {
    font-size: 9px;
  }

  /* Hide product images in print */
  .invoice-table img {
    display: none !important;
  }

  /* Hide duplicate order number block inside info section */
  .order-number-block { display:none !important; }

  /* Order info section: avoid forcing rows that are marked no-print */
  .row:not(.no-print) {
    display: block !important;
  }

  .col-md-3,
  .col-6 {
    width: 50% !important;
    float: right;
    padding: 2px !important;
    font-size: 9px;
  }

  /* Summary table */
  .table-sm {
    font-size: 9px;
  }

  .table-sm td {
    padding: 3px !important;
  }

  .table-active {
    background-color: #f0f0f0 !important;
    font-weight: bold;
  }

  /* Address cards */
  .col-md-6 {
    width: 100% !important;
    float: none;
    page-break-inside: avoid;
    margin-bottom: 10px;
  }

  /* Alert and notes */
  .alert {
    border: 1px solid #000;
    padding: 5px;
    font-size: 9px;
    background: white !important;
  }

  /* Remove colors for print */
  .text-primary,
  .text-success,
  .text-muted {
    color: #000 !important;
  }

  /* Clean margins */
  .page-content-wrapper {
    border: none !important;
    margin: 0 !important;
    padding: 0 !important;
  }

  .card-body.px-1 {
    padding: 0 !important;
  }

  /* Adjust spacing */
  .mb-4,
  .mt-4,
  .g-4 {
    margin: 5px 0 !important;
  }

  /* Hide buttons and links */
  button,
  .btn,
  a.btn {
    display: none !important;
  }

  /* Hide Link components */
  .text-end {
    display: none !important;
  }
  /* Hide any generic page title rows just in case */
  /* Force hide specific screen header row and title */
  .page-title-row, .page-title { display:none !important; }
  .row.mb-4 { display:none !important; }
  .page-content-wrapper h1,
  .page-content-wrapper h2,
  .page-content-wrapper h3,
  .page-content-wrapper h4 { display:none !important; }
  /* keep invoice header title visible */
  .invoice-header h3 { display:block !important; }
  /* Hide general navigation and sidebars from layout */
  .navbar, .sidebar, .top-bar, nav.navbar, .page-content > nav { display:none !important; }
}

/* Screen-only styles */
@media screen {
  .print-only {
    display: none;
  }
}
</style>

<!-- Global print CSS (unscoped) to affect layout elements like navbar/sidebar -->
<style>
@media print {
  .navbar, .sidebar, .top-bar, nav.navbar, .page-content > nav, .page-content .navbar, .page-content .top-bar { display: none !important; }
  .page-content { padding: 0 !important; margin: 0 !important; }
}
</style>
