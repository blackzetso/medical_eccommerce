<template>
    <div class="dropdown cart-dropdown">
        <a href="#" class="dropdown-toggle dropdown-arrow cart-toggle" role="button"
            @click.prevent="showCartDrawer = true">
            <i class="icon-cart-thick"></i>
            <span class="cart-count badge-circle">{{ cart.length }}</span>
        </a>
        <div class="cart-overlay" v-show="showCartDrawer" @click="showCartDrawer = false"></div>
        <div class="dropdown-menu mobile-cart" v-show="showCartDrawer">
            <a href="#" title="Close (Esc)" class="btn-close" @click.prevent="showCartDrawer = false">×</a>
            <div class="dropdownmenu-wrapper custom-scrollbar">
                <div class="dropdown-cart-header"> سلة المشتريات </div>
                <div class="dropdown-cart-products">
                    <div v-if="cart.length === 0" class="text-center py-3">السلة فارغة</div>
                    <div v-for="item in cart" :key="item.id" class="product">
                        <div class="product-details">
                            <h4 class="product-title">
                                <a href="#">{{ item.name }}</a>
                            </h4>
                            <span class="cart-product-info">
                                <span class="cart-product-qty">{{ item.quantity }}</span>
                                × {{ item.price }} جنيه
                            </span>
                        </div>
                        <figure class="product-image-container">
                            <a href="#" class="product-image">
                                <img :src="item.images && item.images.length ? item.images[0] : asset('front/theme1/images/products/product-1.jpg')"
                                    alt="product" width="80" height="80">
                            </a>
                            <a href="#" class="btn-remove" title="Remove Product" @click.prevent="removeItem(item.id)"><span>×</span></a>
                        </figure>
                    </div>
                </div>
                <div class="dropdown-cart-total">
                    <span>SUBTOTAL:</span>
                    <span class="cart-total-price float-right">{{ subtotal }} جنيه</span>
                </div>
                <div class="dropdown-cart-action">
                    <Link :href="route('client.cart')" class="btn btn-gray btn-block view-cart"> عرض السلة </Link>
                    <a href="checkout.html" class="btn btn-dark btn-block">Checkout</a>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
const asset = (path) => '/' + path;
const showCartDrawer = ref(false);
const cartItems = computed(() => usePage().props.cartItems || []);
const cart = cartItems;
const subtotal = computed(() => {
    return cart.value.reduce((sum, item) => {
        return sum + ((item.product?.price || 0) * item.quantity);
    }, 0);
});

function removeItem(id) {
    // يمكنك هنا إرسال طلب لحذف المنتج من السلة
    // مثال: router.post(route('cart.remove'), { id })
}
</script>
