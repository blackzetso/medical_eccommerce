<template>
    <Head :title="`تفاصيل الطلب #${order.order_number}`" />
    <FrontLayout>
        <nav aria-label="breadcrumb" class="breadcrumb-nav">
            <div class="container">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <Link :href="route('/')">الرئيسية</Link>
                    </li>
                    <li class="breadcrumb-item">
                        <Link :href="route('client.dashboard')">لوحة التحكم</Link>
                    </li>
                    <li class="breadcrumb-item">
                        <Link :href="route('client.myorders')">الطلبات</Link>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        تفاصيل الطلب
                    </li>
                </ol>
            </div>
        </nav>

        <div class="container account-container custom-account-container mt-4">
            <div class="row">
                <div class="sidebar widget widget-dashboard mb-lg-0 mb-3 col-lg-3 order-0">
                    <h2 class="text-uppercase">حسابي</h2>
                    <ul class="nav nav-tabs list flex-column mb-0" role="tablist">
                        <li class="nav-item">
                            <Link :href="route('client.dashboard')" class="nav-link" :class="{ active: $page.url === '/client/dashboard' }">لوحة التحكم</Link>
                        </li>
                        <li class="nav-item">
                            <Link :href="route('client.myorders')" class="nav-link" :class="{ active: $page.url.includes('/myorders') || $page.url.includes('/order/') }">الطلبات</Link>
                        </li>
                        <li class="nav-item">
                            <Link :href="route('client.dashboard') + '#edit'" class="nav-link">تفاصيل الحساب</Link>
                        </li>
                        <li class="nav-item">
                            <Link :href="route('client.favorites')" class="nav-link">قائمة الرغبات</Link>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" @click.prevent="logout">تسجيل الخروج</a>
                        </li>
                    </ul>
                </div>
                <div class="col-lg-9 order-lg-last order-1">
                    <div class="order-content">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="account-sub-title mb-0">
                                <i class="sicon-social-dropbox align-middle mr-3"></i>تفاصيل الطلب #{{ order.order_number }}
                            </h3>
                            <Link :href="route('client.myorders')" class="btn btn-outline-secondary btn-sm">
                                <i class="icon-arrow-right"></i> رجوع للطلبات
                            </Link>
                        </div>

                        <!-- معلومات الطلب الرئيسية -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">معلومات الطلب</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 col-6 mb-3">
                                        <strong>رقم الطلب:</strong>
                                        <div class="text-primary">#{{ order.order_number }}</div>
                                    </div>
                                    <div class="col-md-3 col-6 mb-3">
                                        <strong>تاريخ الطلب:</strong>
                                        <div>{{ formatDate(order.created_at) }}</div>
                                    </div>
                                    <div class="col-md-3 col-6 mb-3">
                                        <strong>حالة الطلب:</strong>
                                        <div>
                                            <span :class="getStatusBadge(order.status)">
                                                {{ getStatusLabel(order.status) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6 mb-3">
                                        <strong>حالة الدفع:</strong>
                                        <div>
                                            <span :class="getPaymentStatusBadge(order.payment_status)">
                                                {{ getPaymentStatusLabel(order.payment_status) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6 mb-3">
                                        <strong>المبلغ الإجمالي:</strong>
                                        <div class="fw-bold text-primary">{{ formatPrice(order.total_amount) }} {{ order.currency || 'USD' }}</div>
                                    </div>
                                    <div class="col-md-3 col-6 mb-3">
                                        <strong>طريقة الدفع:</strong>
                                        <div>{{ order.payment_method || 'غير محدد' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- عناصر الطلب -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">عناصر الطلب</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>المنتج</th>
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
                                                            v-if="item.product"
                                                            :src="getProductImage(item.product)"
                                                            :alt="item.product_name"
                                                            class="rounded"
                                                            style="width: 60px; height: 60px; object-fit: cover; margin-left: 15px;"
                                                            @error="$event.target.src='/front/theme1/images/demoes/demo3/products/product-1.jpg'"
                                                        />
                                                        <img
                                                            v-else
                                                            src="/front/theme1/images/demoes/demo3/products/product-1.jpg"
                                                            :alt="item.product_name"
                                                            class="rounded"
                                                            style="width: 60px; height: 60px; object-fit: cover; margin-left: 15px;"
                                                        />
                                                        <div>
                                                            <div class="fw-bold">{{ item.product_name }}</div>
                                                            <small class="text-muted" v-if="item.product_sku">
                                                                كود: {{ item.product_sku }}
                                                            </small>
                                                            <div v-if="item.attributes" class="mt-1">
                                                                <small class="text-muted" v-for="(value, key) in parseAttributes(item.attributes)" :key="key">
                                                                    {{ key }}: {{ value }}<span v-if="Object.keys(parseAttributes(item.attributes)).indexOf(key) < Object.keys(parseAttributes(item.attributes)).length - 1">, </span>
                                                                </small>
                                                            </div>
                                                            <div v-if="item.color" class="mt-1">
                                                                <span
                                                                    class="d-inline-block rounded-circle me-1"
                                                                    :style="{
                                                                        width: '12px',
                                                                        height: '12px',
                                                                        backgroundColor: item.color,
                                                                        border: '1px solid #ccc'
                                                                    }"
                                                                ></span>
                                                                <small class="text-muted">{{ item.color }}</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ item.quantity }}</td>
                                                <td>{{ formatPrice(item.price) }} {{ order.currency || 'USD' }}</td>
                                                <td class="fw-bold">{{ formatPrice(item.total) }} {{ order.currency || 'USD' }}</td>
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
                                                <td class="text-end">{{ formatPrice(order.subtotal) }} {{ order.currency || 'USD' }}</td>
                                            </tr>
                                            <tr v-if="order.tax_amount > 0">
                                                <td>الضرائب:</td>
                                                <td class="text-end">{{ formatPrice(order.tax_amount) }} {{ order.currency || 'USD' }}</td>
                                            </tr>
                                            <tr v-if="order.shipping_amount > 0">
                                                <td>رسوم الشحن:</td>
                                                <td class="text-end">{{ formatPrice(order.shipping_amount) }} {{ order.currency || 'USD' }}</td>
                                            </tr>
                                            <tr v-if="order.discount_amount > 0">
                                                <td>الخصم:</td>
                                                <td class="text-end text-success">-{{ formatPrice(order.discount_amount) }} {{ order.currency || 'USD' }}</td>
                                            </tr>
                                            <tr class="table-active">
                                                <td class="fw-bold">المجموع الكلي:</td>
                                                <td class="text-end fw-bold">{{ formatPrice(order.total_amount) }} {{ order.currency || 'USD' }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- العناوين -->
                        <div class="row">
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
                                            <i class="icon-phone"></i> {{ order.billing_address.phone }}
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
                                            <i class="icon-phone"></i> {{ order.shipping_address.phone }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- خريطة الموقع -->
                        <div class="card mt-4" v-if="order.user?.location_url">
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

                        <!-- ملاحظات الطلب -->
                        <div class="card mt-4" v-if="order.notes">
                            <div class="card-header">
                                <h5 class="card-title mb-0">ملاحظات الطلب</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info mb-0">
                                    {{ order.notes }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </FrontLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import FrontLayout from '@/Pages/Front/Theme1/Layout/App.vue';

const props = defineProps({
    order: {
        type: Object,
        required: true
    }
});

function logout() {
    router.post(route('logout'));
}

function formatDate(date) {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleDateString('ar-EG', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatPrice(price) {
    return parseFloat(price || 0).toFixed(2);
}

const statusLabels = {
    'pending': 'جديد',
    'processing': 'قيد التجهيز',
    'shipped': 'خرج للتوصيل',
    'delivered': 'تم التسليم',
    'cancelled': 'ملغي',
};

const paymentStatusLabels = {
    'pending': 'في الانتظار',
    'paid': 'مدفوع',
    'failed': 'فشل الدفع',
    'refunded': 'مسترد',
};

function getStatusLabel(status) {
    return statusLabels[status] || status;
}

function getPaymentStatusLabel(status) {
    return paymentStatusLabels[status] || status;
}

function getStatusBadge(status) {
    const badges = {
        'pending': 'badge badge-warning',
        'processing': 'badge badge-info',
        'shipped': 'badge badge-primary',
        'delivered': 'badge badge-success',
        'cancelled': 'badge badge-danger'
    };
    return badges[status] || 'badge badge-secondary';
}

function getPaymentStatusBadge(status) {
    const badges = {
        'pending': 'badge badge-warning',
        'paid': 'badge badge-success',
        'failed': 'badge badge-danger',
        'refunded': 'badge badge-info'
    };
    return badges[status] || 'badge badge-secondary';
}

function getProductImage(product) {
    if (!product) {
        return '/front/theme1/images/demoes/demo3/products/product-1.jpg';
    }

    // استخدام main_image إذا كان موجوداً
    if (product.main_image && product.main_image !== 'null' && product.main_image !== '') {
        let img = product.main_image;
        // إذا كان المسار لا يبدأ بـ http أو /
        if (!img.startsWith('http') && !img.startsWith('/')) {
            img = '/' + img;
        }
        return img;
    }
    
    // استخدام images array إذا كان موجوداً
    let images = product.images;

    // If images is a string (JSON), parse it
    if (typeof images === 'string') {
        try {
            images = JSON.parse(images);
        } catch (e) {
            // إذا فشل parsing، جرب كـ string عادي
            if (images && images.trim() !== '' && images !== 'null') {
                let img = images;
                if (!img.startsWith('http') && !img.startsWith('/')) {
                    img = '/' + img;
                }
                return img;
            }
            return '/front/theme1/images/demoes/demo3/products/product-1.jpg';
        }
    }

    // وإلا استخدم أول صورة من المصفوفة
    if (images && Array.isArray(images) && images.length > 0) {
        let img = images[0];
        // التحقق من أن الصورة موجودة وصحيحة
        if (!img || img === 'null' || img === '') {
            return '/front/theme1/images/demoes/demo3/products/product-1.jpg';
        }
        // إذا كان المسار لا يبدأ بـ http أو /
        if (typeof img === 'string' && !img.startsWith('http') && !img.startsWith('/')) {
            img = '/' + img;
        }
        return img;
    }
    
    return '/front/theme1/images/demoes/demo3/products/product-1.jpg';
}

function parseAttributes(attributes) {
    if (!attributes) return {};
    
    if (typeof attributes === 'string') {
        try {
            attributes = JSON.parse(attributes);
        } catch (e) {
            return {};
        }
    }
    
    if (typeof attributes === 'object' && attributes !== null) {
        return attributes;
    }
    
    return {};
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
        // للأسف، Google Maps لا تسمح بتحويل روابط goo.gl مباشرة إلى embed
        // الحل: نحتاج لاستخدام Google Maps Embed API مع place_id
        // لكن يمكننا محاولة استخراج المعلومات من الرابط
        
        // إذا كان الرابط يحتوي على place_id
        const placeIdMatch = url.match(/place_id=([^&]+)/);
        if (placeIdMatch) {
            return `https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3000!2d0!3d0!4m2!3m1!1s${placeIdMatch[1]}!5e0!3m2!1sen!2sus!4v1234567890!5m2!1sen!2sus`;
        }
        
        // إذا كان الرابط يحتوي على coordinates
        const coordMatch = url.match(/@(-?\d+\.?\d*),(-?\d+\.?\d*)/);
        if (coordMatch) {
            const lat = coordMatch[1];
            const lng = coordMatch[2];
            return `https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3000!2d${lng}!3d${lat}!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2z${lat}%2C${lng}!5e0!3m2!1sen!2sus!4v1234567890!5m2!1sen!2sus`;
        }
        
        // إذا لم نتمكن من التحويل، نعيد الرابط الأصلي مع رسالة خطأ
        return null;
    }
    
    // إذا كان رابط Google Maps عادي
    if (url.includes('google.com/maps')) {
        // محاولة استخراج place_id أو coordinates
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
    
    // إذا كان الرابط embed بالفعل أو أي رابط آخر
    return url;
}
</script>

