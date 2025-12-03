<template>
    <div class="sticky-navbar fixed">
        <div class="sticky-info">
            <Link :href="route('/')">
                <i class="icon-home"></i>الرئيسية
            </Link>
        </div>
        <div class="sticky-info">
            <Link :href="route('web.categories')" class="">
                <i class="icon-bars"></i>الفئات
            </Link>
        </div>
        <div class="sticky-info">
            <Link :href="route('client.favorites')" class="">
                <i class="icon-wishlist-2"></i>قائمة الرغبات
            </Link>
        </div>
        <div class="sticky-info">
            <Link :href="route('client.dashboard')" class="">
                <i class="icon-user-2"></i>الحساب
            </Link>
        </div>
        <div class="sticky-info">
            <Link :href="route('client.cart')" class="">
                <i class="icon-shopping-cart position-relative">
                    <span class="cart-count badge-circle">3</span>
                </i> سلة المشتريات
            </Link>
        </div>
    </div>
</template>

<script setup>
const asset = (path) => {
    return '/' + path
}
import { Link } from '@inertiajs/vue3'
</script>
