<template>
    <FrontLayout>
        <Head>
            <title> تتبع الطلب </title>
        </Head>
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
                        تتبع الطلب
                    </li>
                </ol>
            </div>
        </nav>

        <div class="container account-container custom-account-container mt-5 mb-5">
            <div class="row">
                <div class="col-lg-3 order-0 mb-lg-0 mb-3">
                    <ClientSidebar />
                </div>
                <div class="col-lg-9 order-lg-last order-1">
                    <div class="order-content">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="account-sub-title mb-0">
                                <i class="icon-location align-middle mr-3"></i>تتبع الطلب
                            </h3>
                            <Link :href="route('client.myorders')" class="btn btn-outline-secondary btn-sm">
                                <i class="icon-arrow-right"></i> رجوع للطلبات
                            </Link>
                        </div>

                        <!-- معلومات الطلب -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-4 mb-3 mb-md-0">
                                        <strong class="d-block mb-2">رقم الطلب</strong>
                                        <span class="text-primary font-weight-bold">#{{ orderNumber || orderId }}</span>
                                    </div>
                                    <div class="col-md-4 mb-3 mb-md-0">
                                        <strong class="d-block mb-2">تاريخ الطلب</strong>
                                        <span>{{ formatDate(createdAt) }}</span>
                                    </div>
                                    <div class="col-md-4">
                                        <strong class="d-block mb-2">الحالة الحالية</strong>
                                        <span :class="getStatusBadge(orderStatus)">{{ getStatusLabel(orderStatus) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- مراحل التتبع -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">مراحل تتبع الطلب</h5>
                            </div>
                            <div class="card-body">
                                <div class="tracking-timeline">
                                    <!-- مرحلة 1: قيد الانتظار -->
                                    <div class="tracking-step" :class="{'active': ['pending', 'processing', 'shipped', 'delivered'].includes(orderStatus), 'completed': ['processing', 'shipped', 'delivered'].includes(orderStatus)}">
                                        <div class="step-icon mr-5" >
                                            <i class="icon-clock" v-if="!['processing', 'shipped', 'delivered'].includes(orderStatus)"></i>
                                            <i class="icon-check" v-else></i>
                                        </div>
                                        <div class="step-content">
                                            <h6 class="step-title">قيد الانتظار</h6>
                                            <p class="step-description">تم استلام طلبك وسيتم مراجعته قريباً</p>
                                        </div>
                                    </div>

                                    <!-- مرحلة 2: قيد التحضير -->
                                    <div class="tracking-step" :class="{'active': ['processing', 'shipped', 'delivered'].includes(orderStatus), 'completed': ['shipped', 'delivered'].includes(orderStatus)}">
                                        <div class="step-icon mr-5" >
                                            <i class="icon-bag" v-if="!['shipped', 'delivered'].includes(orderStatus)"></i>
                                            <i class="icon-check" v-else></i>
                                        </div>
                                        <div class="step-content mr-5">
                                            <h6 class="step-title">قيد التحضير</h6>
                                            <p class="step-description">جارٍ تحضير طلبك للتوصيل</p>
                                        </div>
                                    </div>

                                    <!-- مرحلة 3: خرج للتوصيل -->
                                    <div class="tracking-step" :class="{'active': ['shipped', 'delivered'].includes(orderStatus), 'completed': orderStatus === 'delivered'}">
                                        <div class="step-icon mr-5">
                                            <i class="icon-truck" v-if="orderStatus !== 'delivered'"></i>
                                            <i class="icon-check" v-else></i>
                                        </div>
                                        <div class="step-content">
                                            <h6 class="step-title">خرج للتوصيل</h6>
                                            <p class="step-description">طلبك في الطريق إليك</p>
                                        </div>
                                    </div>

                                    <!-- مرحلة 4: تم الاستلام -->
                                    <div class="tracking-step" :class="{'active': orderStatus === 'delivered', 'completed': orderStatus === 'delivered'}">
                                        <div class="step-icon mr-5">
                                            <i class="icon-check"></i>
                                        </div>
                                        <div class="step-content">
                                            <h6 class="step-title">تم الاستلام</h6>
                                            <p class="step-description">تم تسليم طلبك بنجاح</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- أزرار الإجراءات -->
                        <div class="mt-4 text-center">
                            <Link :href="route('client.order.show', orderId)" class="btn btn-primary">
                                <i class="icon-eye"></i> عرض تفاصيل الطلب
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </FrontLayout>
</template>
<script setup>
    import { ref, onMounted } from 'vue';
    import { Head, Link, router } from '@inertiajs/vue3';
    import { route } from 'ziggy-js';
    import FrontLayout from '@/Pages/Front/Theme1/Layout/App.vue';
    import ClientSidebar from '@/Components/ClientSidebar.vue';

    const props = defineProps({
        orderId: {
            type: [Number, String],
            default: null,
        },
        orderNumber: {
            type: String,
            default: null,
        },
        orderStatus: {
            type: String,
            default: 'pending',
        },
        createdAt: {
            type: String,
            default: null,
        },
    });


    function formatDate(date) {
        if (!date) return 'غير محدد';
        const d = new Date(date);
        return d.toLocaleDateString('ar-EG', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    const statusLabels = {
        'pending': 'جديد',
        'processing': 'قيد المعالجة',
        'shipped': 'خرج للتوصيل',
        'delivered': 'تم التسليم',
        'cancelled': 'ملغي',
    };

    function getStatusLabel(status) {
        return statusLabels[status] || status;
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
</script>

<style scoped>
.tracking-timeline {
    position: relative;
    padding: 20px 0;
}

.tracking-step {
    display: flex;
    align-items: flex-start;
    margin-bottom: 40px;
    position: relative;
    opacity: 0.5;
    transition: all 0.3s ease;
}

.tracking-step:last-child {
    margin-bottom: 0;
}

.tracking-step::before {
    content: '';
    position: absolute;
    right: 20px;
    top: 50px;
    width: 2px;
    height: calc(100% + 20px);
    background-color: #e0e0e0;
    z-index: 0;
}

.tracking-step:last-child::before {
    display: none;
}

.tracking-step.active,
.tracking-step.completed {
    opacity: 1;
}

.tracking-step.completed::before {
    background-color: #28a745;
}

.step-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background-color: #e0e0e0;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: 20px;
    position: relative;
    z-index: 1;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.step-icon i {
    font-size: 20px;
    color: #999;
}

.tracking-step.active .step-icon {
    background-color: #007bff;
    box-shadow: 0 0 0 8px rgba(0, 123, 255, 0.1);
}

.tracking-step.active .step-icon i {
    color: #fff;
}

.tracking-step.completed .step-icon {
    background-color: #28a745;
}

.tracking-step.completed .step-icon i {
    color: #fff;
}

.step-content {
    flex: 1;
    padding-top: 5px;
}

.step-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 5px;
    color: #333;
}

.tracking-step.active .step-title {
    color: #007bff;
}

.tracking-step.completed .step-title {
    color: #28a745;
}

.step-description {
    font-size: 14px;
    color: #666;
    margin-bottom: 0;
}

@media (max-width: 768px) {
    .tracking-step {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .step-icon {
        margin-left: 0;
        margin-bottom: 15px;
    }

    .tracking-step::before {
        display: none;
    }
}

</style>

