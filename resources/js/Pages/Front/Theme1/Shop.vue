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
                    <!-- Debug Info (remove in production) -->
                  
                    <!-- Products Grid -->
                    <div class="row" v-if="allProducts && allProducts.length > 0">
                        <div v-for="product in allProducts" :key="product.id" class="col-6 col-sm-4 col-md-3 col-xl-5col">
                            <div class="product-default inner-quickview inner-icon">
                                <figure>
                                    <Link :href="route('web.product', product.id)">
                                        <img :src="getProductImage(product)" width="273" height="273" :alt="product.name" />
                                    </Link>
                                    <div class="label-group" v-if="product.is_featured || product.sale_price">
                                        <div v-if="product.is_featured" class="product-label label-hot">جديد</div>
                                        <div v-if="product.sale_price" class="product-label label-sale">تخفيض</div>
                                    </div>
                                    <div class="btn-icon-group">
                                        <a 
                                            v-if="product.manage_stock && (product.total_stock || 0) > 0 || !product.manage_stock"
                                            href="#" 
                                            @click.prevent="addToCart(product)" 
                                            class="btn-icon btn-add-cart product-type-simple"
                                        >
                                            <i class="icon-shopping-cart"></i>
                                        </a>
                                        <span 
                                            v-else 
                                            class="btn-icon btn-add-cart product-type-simple disabled" 
                                            style="opacity: 0.5; cursor: not-allowed;"
                                            title="المنتج غير متوفر"
                                        >
                                            <i class="icon-shopping-cart"></i>
                                        </span>
                                    </div>
                                    <!-- <Link :href="route('web.product', product.id)" class="btn-quickview" title="عرض سريع" @click="hideLoadingOverlay">عرض سريع</Link> -->
                                </figure>
                                <div class="product-details">
                                    <div class="category-wrap">
                                        <div class="category-list">
                                            <Link v-if="product.category?.id" :href="route('web.category', product.category.id)" class="product-category">{{ product.category.name || 'عام' }}</Link>
                                            <span v-else class="product-category" style="cursor: default;">{{ product.category?.name || 'عام' }}</span>
                                        </div>
                                    </div>
                                    <h3 class="product-title">
                                        <Link :href="route('web.product', product.id)">{{ product.name }}</Link>
                                    </h3>

                                    <div v-if="isAuthenticated" class="price-box">
                                        <span v-if="product.sale_price" class="old-price">{{ product.price }} جنيه</span>
                                        <span class="product-price">{{ product.sale_price || product.price }} جنيه</span>
                                    </div><!-- End .price-box -->
                                    <div v-else class="price-box">
                                        <p class="text-muted" style="font-size: 0.9rem; margin: 0;">
                                            <Link :href="route('client.login')" class="text-primary">يرجى تسجيل الدخول</Link> لعرض الأسعار
                                        </p>
                                    </div>
                                    <!-- Stock Information -->
                                    <div class="stock-info" style="margin-top: 8px; font-size: 1rem;">
                                        <span v-if="product.manage_stock" 
                                              :class="(product.total_stock || 0) > 0 ? 'text-success' : 'text-danger'"
                                              style="font-weight: 600;">
                                            <i :class="(product.total_stock || 0) > 0 ? 'icon-check-circle' : 'icon-close-circle'" 
                                               style="margin-left: 4px; font-size: 1.1rem;"></i>
                                            <span v-if="(product.total_stock || 0) > 0">
                                                متوفر ({{ product.total_stock }} قطعة)
                                            </span>
                                            <span v-else>
                                                غير متوفر
                                            </span>
                                        </span>
                                        <span v-else class="text-info" style="font-weight: 600;">
                                            <i class="icon-check-circle" style="margin-left: 4px; font-size: 1.1rem;"></i>
                                            متوفر
                                        </span>
                                    </div>
                                </div><!-- End .product-details -->
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else-if="!loading" class="text-center py-5">
                        <i class="icon-folder-open" style="font-size: 4rem; color: #ccc; margin-bottom: 1rem;"></i>
                        <h3>لا توجد منتجات</h3>
                        <p class="text-muted">لم يتم العثور على منتجات في هذا القسم حالياً.</p>
                    </div>

                    <!-- Loading Indicator -->
                    <div v-if="loading" class="text-center py-5">
                        <div class="loading-spinner">
                            <i class="icon-spinner icon-spin" style="font-size: 2rem; color: #667eea;"></i>
                            <p class="mt-3">جاري تحميل المزيد من المنتجات...</p>
                        </div>
                    </div>

                    <!-- Manual Load More Button (for debugging) -->
                    <div v-if="hasMore && !loading" class="text-center py-4">
                        <button @click="loadMoreProducts" class="btn btn-primary btn-lg">
                            تحميل المزيد من المنتجات
                            <small class="d-block">الصفحة {{ currentPage }} من {{ pagination?.last_page || '?' }}</small>
                        </button>
                    </div>

                    <!-- Scroll Trigger Element -->
                    <div ref="scrollTrigger" class="scroll-trigger" v-if="hasMore && !loading"></div>
                </div><!-- End .main-content -->


            </div><!-- End .row -->
        </div><!-- End .container -->
    </FrontLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import FrontLayout from '@/Pages/Front/Theme1/Layout/App.vue';
import { usePage } from '@inertiajs/vue3';

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
    },
    search: {
        type: String,
        default: ''
    }
});

const page = usePage();

// التحقق من تسجيل الدخول
const isAuthenticated = computed(() => {
    return page.props.auth?.user !== null && page.props.auth?.user !== undefined;
});

// Reactive data for infinite scroll
const allProducts = ref([...props.products]);
const currentPage = ref(props.pagination?.current_page || 1);
const hasMore = ref(props.pagination?.has_more || (props.pagination?.last_page > props.pagination?.current_page) || false);
const loading = ref(false);
const scrollTrigger = ref(null);
let observer = null;

// Watch for new products from props (when page changes)
watch(() => props.products, (newProducts, oldProducts) => {
    if (newProducts && newProducts.length > 0) {
        // If this is the first page, replace all products
        if (props.pagination?.current_page === 1) {
            allProducts.value = [...newProducts];
        } else {
            // For subsequent pages, add only unique products
            const newProductIds = newProducts.map(p => p.id);
            const existingIds = allProducts.value.map(p => p.id);
            
            const uniqueProducts = newProducts.filter(p => !existingIds.includes(p.id));
            
            if (uniqueProducts.length > 0) {
                allProducts.value = [...allProducts.value, ...uniqueProducts];
            }
        }
    }
    
    // Update pagination state
    currentPage.value = props.pagination?.current_page || 1;
    hasMore.value = props.pagination?.has_more || (props.pagination?.current_page < props.pagination?.last_page) || false;
    loading.value = false;
    
    // Re-setup observer if needed
    if (hasMore.value && observer && scrollTrigger.value) {
        setTimeout(() => {
            if (scrollTrigger.value && !observer.takeRecords().length) {
                observer.observe(scrollTrigger.value);
            }
        }, 100);
    }
}, { deep: true });

// Methods
const loadMoreProducts = () => {
    if (loading.value || !hasMore.value) {
        return;
    }
    
    loading.value = true;
    const nextPage = currentPage.value + 1;
    
    // Build the URL with query parameters
    let url;
    if (props.category?.id) {
        url = route('web.category', props.category.id);
    } else {
        url = route('web.products');
    }
    
    const params = new URLSearchParams();
    params.append('page', nextPage);
    if (props.search) {
        params.append('search', props.search);
    }
    
    router.get(`${url}?${params.toString()}`, {}, {
        preserveScroll: true,
        preserveState: true,
        only: ['products', 'pagination'],
        onSuccess: (page) => {
            // State will be updated via watch
        },
        onError: (errors) => {
            loading.value = false;
        }
    });
};

const setupIntersectionObserver = () => {
    if (!scrollTrigger.value) {
        return;
    }
    
    // Disconnect existing observer if any
    if (observer) {
        observer.disconnect();
    }
    
    observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && hasMore.value && !loading.value) {
                loadMoreProducts();
            }
        });
    }, {
        root: null,
        rootMargin: '100px',
        threshold: 0.1
    });
    
    observer.observe(scrollTrigger.value);
};

const addToCart = (product) => {
    router.post(route('cart.add'), {
        product_id: product.id,
        quantity: 1
    }, {
        preserveScroll: true,
        onSuccess: () => {
            const success = page.props.flash?.success;
            if (success) {
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
    // استخدام main_image إذا كان موجوداً
    if (product.main_image) {
        let img = product.main_image;
        if (!img.startsWith('http') && !img.startsWith('/')) {
            img = '/' + img;
        }
        return img;
    }
    // وإلا استخدم أول صورة من المصفوفة
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
    // Setup intersection observer after a short delay to ensure DOM is ready
    setTimeout(() => {
        setupIntersectionObserver();
    }, 500);
});

onUnmounted(() => {
    if (observer) {
        observer.disconnect();
    }
});
</script>

<style scoped>
.scroll-trigger {
    height: 20px;
    width: 100%;
}

.loading-spinner {
    padding: 40px 0;
}

.loading-spinner i {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}
</style>


