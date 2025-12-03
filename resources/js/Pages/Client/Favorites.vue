<template>
    <Head title="قائمة الرغبات" />
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
                        قائمة الرغبات
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
                    <div class="products-content">
                        <h3 class="account-sub-title d-none d-md-block mb-4">
                            <i class="sicon-heart align-middle mr-3"></i>قائمة الرغبات
                        </h3>
                        <div v-if="favorites && favorites.length > 0" class="row">
                            <div v-for="favorite in favorites" :key="favorite.id" class="col-6 col-sm-4 col-md-3 col-xl-5col">
                                <div class="product-default inner-quickview inner-icon">
                                    <figure>
                                        <Link :href="route('web.product', favorite.product?.id)">
                                            <img :src="getProductImage(favorite.product)" width="273" height="273" :alt="favorite.product?.name" />
                                        </Link>
                                        <div class="label-group" v-if="favorite.product?.is_featured || favorite.product?.sale_price">
                                            <div v-if="favorite.product?.is_featured" class="product-label label-hot">جديد</div>
                                            <div v-if="favorite.product?.sale_price" class="product-label label-sale">تخفيض</div>
                                        </div>
                                        <div class="btn-icon-group">
                                            <a href="#" @click.prevent="addToCart(favorite.product)" class="btn-icon btn-add-cart product-type-simple">
                                                <i class="icon-shopping-cart"></i>
                                            </a>
                                            <a href="#" @click.prevent="removeFromFavorites(favorite.id)" class="btn-icon btn-icon-wish" title="حذف من المفضلة">
                                                <i class="icon-heart"></i>
                                            </a>
                                        </div>
                                    </figure>
                                    <div class="product-details">
                                        <div class="category-wrap">
                                            <div class="category-list">
                                                <Link :href="route('web.category', favorite.product?.category_id)" class="product-category">
                                                    {{ favorite.product?.category?.name || 'فئة' }}
                                                </Link>
                                            </div>
                                        </div>
                                        <h3 class="product-title">
                                            <Link :href="route('web.product', favorite.product?.id)">{{ favorite.product?.name }}</Link>
                                        </h3> 
                                        <div class="price-box">
                                            <span v-if="favorite.product?.sale_price || favorite.product?.final_price" class="product-price">
                                                {{ formatPrice(favorite.product?.sale_price || favorite.product?.final_price) }}
                                            </span>
                                            <span v-else class="product-price">
                                                {{ formatPrice(favorite.product?.price) }}
                                            </span>
                                            <span v-if="favorite.product?.sale_price || favorite.product?.final_price" class="old-price">
                                                {{ formatPrice(favorite.product?.price) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center">
                            <p class="mb-5 mt-5">لا توجد منتجات في قائمة الرغبات.</p>
                            <Link :href="route('web.categories')" class="btn btn-dark">اذهب للتسوق</Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </FrontLayout>
</template>

<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import FrontLayout from '@/Pages/Front/Theme1/Layout/App.vue';
import ClientSidebar from '@/Components/ClientSidebar.vue';

const props = defineProps({
    favorites: {
        type: Array,
        default: () => []
    }
});


function getProductImage(product) {
    if (!product) {
        return '/front/theme1/images/demoes/demo3/products/product-1.jpg';
    }
    if (product.images && product.images.length > 0) {
        let img = product.images[0];
        if (!img.startsWith('http') && !img.startsWith('/')) {
            img = '/' + img;
        }
        return img;
    }
    return '/front/theme1/images/demoes/demo3/products/product-1.jpg';
}

function formatPrice(price) {
    if (!price) return '0.00';
    return parseFloat(price).toFixed(2);
}

function addToCart(product) {
    if (!product) return;
    
    router.post(route('cart.add'), {
        product_id: product.id,
        quantity: 1
    }, {
        preserveScroll: true,
        onSuccess: () => {
            const success = usePage().props.flash?.success;
            if (success) {
                if (window.$toast) {
                    window.$toast.success(success);
                } else {
                    alert(success);
                }
            }
        },
        onError: () => {
            // Handle error
        }
    });
}

function removeFromFavorites(favoriteId) {
    const favorite = props.favorites.find(f => f.id === favoriteId);
    if (!favorite || !favorite.product) return;

    router.post(route('favorites.remove'), {
        product_id: favorite.product.id
    }, {
        preserveScroll: true,
        onSuccess: () => {
            // The page will reload with updated favorites
        }
    });
}
</script>