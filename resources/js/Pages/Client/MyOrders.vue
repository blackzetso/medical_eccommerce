<template>
    <Head title="طلباتي" />
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
                    <li class="breadcrumb-item active" aria-current="page">
                        الطلبات
                    </li>
                </ol>
            </div>
        </nav>

        <div class="container account-container custom-account-container">
            <div class="row">
                <div class="col-lg-3 order-0 mb-lg-0 mb-3">
                    <ClientSidebar />
                </div>
                <div class="col-lg-9 order-lg-last order-1">
                    <div class="order-content">
                        <h3 class="account-sub-title d-none d-md-block">
                            <i class="sicon-social-dropbox align-middle mr-3"></i>الطلبات
                        </h3>
                        <div class="order-table-container text-center">
                            <table class="table table-order" v-if="orders && orders.length > 0">
                                <thead>
                                    <tr>
                                        <th class="order-id">رقم الطلب</th>
                                        <th class="order-date">التاريخ</th>
                                        <th class="order-status">الحالة</th>
                                        <th class="order-price">الإجمالي</th>
                                        <th class="order-action">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="order in orders" :key="order.id">
                                        <td class="order-id">#{{ order.order_number }}</td>
                                        <td class="order-date">{{ formatDate(order.created_at) }}</td>
                                        <td class="order-status">
                                            <span class="badge badge-info">{{ getStatusLabel(order.status) }}</span>
                                        </td>
                                        <td class="order-price">{{ formatPrice(order.total_amount) }} {{ order.currency || 'USD' }}</td>
                                        <td class="order-action">
                                            <div class="d-flex gap-2 justify-content-center">
                                                <Link :href="route('client.order.show', order.id)" class="btn btn-sm btn-outline-primary">
                                                    <i class="icon-eye"></i> عرض التفاصيل
                                                </Link>
                                                <Link :href="route('client.tracking', order.id)" class="btn btn-sm btn-outline-secondary">
                                                    <i class="icon-location"></i> تتبع الطلب
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-else class="text-center">
                                <p class="mb-5 mt-5">لم يتم إجراء أي طلب بعد.</p>
                            </div>
                            <hr class="mt-0 mb-3 pb-2" v-if="orders && orders.length > 0" />
                            <Link :href="route('web.categories')" class="btn btn-dark">اذهب للتسوق</Link>
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
import ClientSidebar from '@/Components/ClientSidebar.vue';

const props = defineProps({
    orders: {
        type: Array,
        default: () => []
    }
});


const statusLabels = {
    'pending': 'جديد',
    'processing': 'قيد التجهيز',
    'shipped': 'خرج للتوصيل',
    'delivered': 'تم التسليم',
    'cancelled': 'ملغي',
};

function getStatusLabel(status) {
    return statusLabels[status] || status;
}

function formatDate(date) {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleDateString('ar-EG', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function formatPrice(price) {
    return parseFloat(price).toFixed(2);
}
</script>