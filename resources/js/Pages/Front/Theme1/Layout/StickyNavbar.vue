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
        <template v-if="isAuthenticated">
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
        </template>
        <template v-else>
            <div class="sticky-info">
                <Link :href="route('client.login')" class="">
                    <i class="icon-user-2"></i>تسجيل الدخول
                </Link>
            </div>
            <div class="sticky-info">
                <Link :href="route('client.register')" class="">
                    <i class="icon-edit"></i>إنشاء حساب
                </Link>
            </div>
        </template>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const asset = (path) => {
    return '/' + path
}

const page = usePage()
const user = computed(() => page.props.auth?.user)
const isAuthenticated = computed(() => !!(user.value && user.value.id))
</script>
