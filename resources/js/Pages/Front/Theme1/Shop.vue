<template>
    <Head title="المتجر" />
    <FrontLayout>
        <nav aria-label="breadcrumb" class="breadcrumb-nav">
            <div class="container">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <Link :href="route('/')">الرئيسية</Link>
                    </li>
                    <li class="breadcrumb-item">
                        <Link href="/categories">الأقسام</Link>
                    </li>
                    <li v-if="category" class="breadcrumb-item active" aria-current="page">
                        {{ category.name }}
                    </li>
                    <li v-else class="breadcrumb-item active" aria-current="page">
                        المتجر
                    </li>
                </ol>
            </div><!-- End .container -->
        </nav>

        <div class="container mb-3">
            <div class="row">
                <div class="col-lg-12 main-content">
                    <!-- Products Grid -->
                    <div class="row" v-if="products && products.length > 0">
                        <div v-for="product in products" :key="product.id" class="col-6 col-sm-4 col-md-3 col-xl-5col">
                            <div class="product-default inner-quickview inner-icon">
                                <figure>
                                    <a href="#">
                                        <img :src="getProductImage(product)" width="273" height="273" :alt="product.name" />
                                    </a>
                                    <div class="label-group" v-if="product.is_featured || product.sale_price">
                                        <div v-if="product.is_featured" class="product-label label-hot">جديد</div>
                                        <div v-if="product.sale_price" class="product-label label-sale">تخفيض</div>
                                    </div>
                                    <div class="btn-icon-group">
                                        <a href="#" @click.prevent="addToCart(product)" class="btn-icon btn-add-cart product-type-simple">
                                            <i class="icon-shopping-cart"></i>
                                        </a>
                                    </div>
                                    <a href="#" class="btn-quickview" title="عرض سريع">عرض سريع</a>
                                </figure>
                                <div class="product-details">
                                    <div class="category-wrap">
                                        <div class="category-list">
                                            <a href="#" class="product-category">{{ product.category?.name || 'عام' }}</a>
                                        </div>
                                    </div>
                                    <h3 class="product-title">
                                        <a href="#">{{ product.name }}</a>
                                    </h3>

                                    <div class="price-box">
                                        <span v-if="product.sale_price" class="old-price">{{ product.price }} جنيه</span>
                                        <span class="product-price">{{ product.sale_price || product.price }} جنيه</span>
                                    </div><!-- End .price-box -->
                                </div><!-- End .product-details -->
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-5">
                        <i class="icon-folder-open" style="font-size: 4rem; color: #ccc; margin-bottom: 1rem;"></i>
                        <h3>لا توجد منتجات</h3>
                        <p class="text-muted">لم يتم العثور على منتجات في هذا القسم حالياً.</p>
                    </div>

                    <!-- Pagination -->
                    <nav v-if="products && products.length > 0" class="toolbox toolbox-pagination">


                        <ul class="pagination toolbox-item" v-if="pagination && pagination.last_page > 1">
                            <li class="page-item" :class="{ active: pagination.current_page === 1 }">
                                <a class="page-link" href="#">1 <span v-if="pagination.current_page === 1" class="sr-only">(current)</span></a>
                            </li>
                            <li v-if="pagination.last_page > 1" class="page-item">
                                <a class="page-link" href="#">2</a>
                            </li>
                            <li v-if="pagination.last_page > 2" class="page-item">
                                <a class="page-link" href="#">3</a>
                            </li>
                            <li v-if="pagination.last_page > 3" class="page-item">
                                <span class="page-link">...</span>
                            </li>
                            <li v-if="pagination.current_page < pagination.last_page" class="page-item">
                                <a class="page-link page-link-btn" href="#"><i class="icon-angle-right"></i></a>
                            </li>
                        </ul>
                    </nav>
                </div><!-- End .main-content -->


            </div><!-- End .row -->
        </div><!-- End .container -->
    </FrontLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import FrontLayout from '@/Pages/Front/Theme1/Layout/App.vue';

// Props from the controller
const props = defineProps({
    category: Object,
    categories: Array,
    products: {
        type: Array,
        default: () => []
    },
    pagination: {
        type: Object,
        default: () => ({})
    }
});

// Reactive data
const selectedCategories = ref([]);
const minPrice = ref(null);
const maxPrice = ref(null);
const sortBy = ref('latest');
const perPage = ref(12);

// Methods
const filterProducts = () => {
    // Implement product filtering logic here
    console.log('Filtering products...');
};

import { router } from '@inertiajs/vue3';

import { usePage } from '@inertiajs/vue3';

const addToCart = (product) => {
    router.post(route('cart.add'), {
        product_id: product.id,
        quantity: 1
    }, {
        preserveScroll: true,
        onSuccess: () => {
            const success = usePage().props.flash?.success;
            if (success) {
                // استخدم مكتبة toaster مثل vue-toastification أو أي مكتبة لديك
                // مثال:
                if (window.$toast) {
                    window.$toast.success(success);
                } else {
                    alert(success);
                }
            }
        },
        onError: () => {
            // يمكن عرض رسالة خطأ
        }
    });
};

const getProductImage = (product) => {
    if (product.images && product.images.length > 0) {
        let img = product.images[0];
        // إذا كان المسار لا يبدأ بـ http أو /
        if (!img.startsWith('http') && !img.startsWith('/')) {
            img = '/' + img;
        }
        return img;
    }
    return '/front/theme1/images/demoes/demo3/products/product-1.jpg';
};

onMounted(() => {
    // Initialize component
    console.log('Shop component mounted');
    if (props.category) {
        console.log('Current category:', props.category);
    }
});
</script>


