<script setup>
import { ref, onMounted, computed, watch, nextTick } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import FrontLayout from '@/Pages/Front/Theme1/Layout/App.vue';
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
    product: Object
});

const currency = 'جنيه';
const selectedAttributes = ref({});
const selectedColor = ref(null);
const quantity = ref(1);
const baseProductPrice = ref(0);

const getProductImage = (product) => {
    if (product.images && product.images.length > 0) {
        let img = product.images[0];
        if (!img.startsWith('http') && !img.startsWith('/')) {
            img = '/' + img;
        }
        return img;
    }
    return '/front/theme1/images/demoes/demo3/products/product-1.jpg';
};

// التحقق من أن الخاصية هي لون
const isColorAttribute = (attribute) => {
    if (!attribute) return false;
    
    const name = String(attribute.name || '').toLowerCase().trim();
    const type = String(attribute.type || '').toLowerCase().trim();
    const slug = String(attribute.slug || '').toLowerCase().trim();
    
    // التحقق من النوع
    if (type === 'color' || type === 'colour' || type === 'colors' || type === 'colours') {
        console.log('✅ تم التعرف على اللون من النوع:', type);
        return true;
    }
    
    // التحقق من الاسم (بما في ذلك الأسماء العربية)
    const colorKeywords = ['color', 'colour', 'colors', 'colours', 'لون', 'ألوان'];
    if (colorKeywords.some(keyword => name.includes(keyword))) {
        console.log('✅ تم التعرف على اللون من الاسم:', name);
        return true;
    }
    
    // التحقق من الـ slug
    if (colorKeywords.some(keyword => slug.includes(keyword))) {
        console.log('✅ تم التعرف على اللون من الـ slug:', slug);
        return true;
    }
    
    // التحقق من وجود color_code في القيم (الأهم)
    if (attribute.values && attribute.values.length > 0) {
        const hasColorCode = attribute.values.some(v => {
            const colorCode = v.color_code || '';
            return colorCode && String(colorCode).trim() !== '';
        });
        if (hasColorCode) {
            console.log('✅ تم التعرف على اللون من color_code في القيم');
            return true;
        }
    }
    
    return false;
};

// الحصول على قيمة اللون من string (لـ product.colors)
const getColorFromString = (colorString) => {
    if (!colorString) return '#ccc';
    
    const color = String(colorString).trim();
    
    // إذا كان hex code بالفعل
    if (color.startsWith('#')) {
        return color;
    }
    
    // محاولة تحويل اسم اللون إلى hex
    const colorName = color.toLowerCase();
    const colorMap = {
        'red': '#ff0000', 'أحمر': '#ff0000',
        'blue': '#0000ff', 'أزرق': '#0000ff',
        'green': '#00ff00', 'أخضر': '#00ff00',
        'yellow': '#ffff00', 'أصفر': '#ffff00',
        'white': '#ffffff', 'أبيض': '#ffffff',
        'black': '#000000', 'أسود': '#000000',
        'orange': '#ffa500', 'برتقالي': '#ffa500',
        'purple': '#800080', 'بنفسجي': '#800080',
        'pink': '#ffc0cb', 'وردي': '#ffc0cb',
        'gray': '#808080', 'grey': '#808080', 'رمادي': '#808080',
        'brown': '#a52a2a', 'بني': '#a52a2a',
        'cyan': '#00ffff', 'سماوي': '#00ffff',
        'magenta': '#ff00ff', 'أرجواني': '#ff00ff'
    };
    
    if (colorMap[colorName]) {
        return colorMap[colorName];
    }
    
    return '#ccc'; // لون افتراضي
};

// الحصول على قيمة اللون من value object (لـ attributes)
const getColorValue = (value) => {
    if (value.color_code && value.color_code.trim() !== '') {
        return value.color_code;
    }
    if (value.value && value.value.trim() !== '') {
        return getColorFromString(value.value);
    }
    return '#ccc';
};

const selectAttribute = (attributeId, value) => {
    selectedAttributes.value[attributeId] = value.id;
};

const clearAttributes = () => {
    selectedAttributes.value = {};
};

const getAdditionalPrice = computed(() => {
    let total = 0;
    if (props.product && props.product.attributes) {
        for (const attr of props.product.attributes) {
            const selectedId = selectedAttributes.value[attr.id];
            if (selectedId) {
                const value = attr.values.find(v => v.id === selectedId);
                if (value && value.price !== null && value.price !== undefined) {
                    const priceValue = parseFloat(value.price) || 0;
                    if (priceValue > 0) {
                        total += priceValue;
                    }
                }
            }
        }
    }
    return total;
});

const basePrice = computed(() => {
    if (baseProductPrice.value > 0) {
        return baseProductPrice.value;
    }
    if (props.product && props.product.price) {
        const price = parseFloat(props.product.price);
        if (!isNaN(price) && price > 0) {
            return price;
        }
    }
    return 0;
});

const getFinalPrice = computed(() => {
    const base = Number(basePrice.value) || 0;
    const additional = Number(getAdditionalPrice.value) || 0;
    const final = base + additional;
    return final;
});

// السعر الإجمالي مضروباً في الكمية
const getTotalPrice = computed(() => {
    return getFinalPrice.value * quantity.value;
});

// التحقق من أن المنتج متوفر في المخزون فقط
const isProductAvailable = computed(() => {
    return props.product.stock_quantity && props.product.stock_quantity > 0;
});

// التحقق من أن جميع الخصائص محددة
const areAllAttributesSelected = computed(() => {
    if (!props.product.attributes || props.product.attributes.length === 0) return true;
    for (const attr of props.product.attributes) {
        if (!selectedAttributes.value[attr.id]) return false;
    }
    return true;
});

// التحقق من وجود أي خاصية محددة
const hasAnyAttributeSelected = computed(() => {
    if (!props.product.attributes || props.product.attributes.length === 0) return false;
    return Object.keys(selectedAttributes.value).length > 0;
});

// التحقق من أن المنتج يحتوي على خصائص
const hasAttributes = computed(() => {
    return props.product.attributes && props.product.attributes.length > 0;
});

// تحديد حالة زر Add to Cart
// الزر يكون enabled افتراضياً، وعند تحديد أي خاصية (دون إكمال جميعها) يصبح disabled
const isAddToCartDisabled = computed(() => {
    // إذا كان المنتج يحتوي على خصائص
    if (hasAttributes.value) {
        // إذا تم تحديد أي خاصية ولكن لم يتم تحديد جميعها، يكون disabled
        if (hasAnyAttributeSelected.value && !areAllAttributesSelected.value) {
            return true;
        }
        // إذا لم يتم تحديد أي خاصية، يكون enabled
        // إذا تم تحديد جميع الخصائص، يكون enabled
    }
    // إذا لم يكن هناك خصائص، يكون enabled
    return false;
});

const formatPrice = (price) => {
    const numPrice = parseFloat(price) || 0;
    return numPrice.toFixed(2);
};

import { router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';

// زيادة الكمية
const increaseQuantity = () => {
    if (props.product.stock_quantity && quantity.value < props.product.stock_quantity) {
        quantity.value++;
    }
};

// تقليل الكمية
const decreaseQuantity = () => {
    if (quantity.value > 1) {
        quantity.value--;
    }
};

const addToCart = (product) => {
    // التحقق من المخزون
    if (!isProductAvailable.value) {
        if (window.$toast) {
            window.$toast.error('المنتج غير متوفر في المخزون');
        } else {
            alert('المنتج غير متوفر في المخزون');
        }
        return;
    }
    
    // التحقق من الخصائص - سيتم التحقق في الـ controller أيضاً
    if (!areAllAttributesSelected.value) {
        if (window.$toast) {
            window.$toast.error('يرجى تحديد جميع الخصائص المطلوبة');
        } else {
            alert('يرجى تحديد جميع الخصائص المطلوبة');
        }
        return;
    }
    
    sendToCart(product, selectedAttributes.value);
};

const sendToCart = (product, attributes) => {
    router.post(route('cart.add'), {
        product_id: product.id,
        quantity: quantity.value,
        attributes: attributes,
        color: selectedColor.value,
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
            } else {
                // رسالة نجاح افتراضية
                if (window.$toast) {
                    window.$toast.success('تم إضافة المنتج إلى السلة بنجاح');
                } else {
                    alert('تم إضافة المنتج إلى السلة بنجاح');
                }
            }
        },
        onError: (errors) => {
            console.error('خطأ في إضافة المنتج إلى السلة:', errors);
            let errorMessage = 'حدث خطأ أثناء إضافة المنتج إلى السلة';
            
            if (errors.message) {
                errorMessage = errors.message;
            } else if (typeof errors === 'string') {
                errorMessage = errors;
            } else if (errors && Object.keys(errors).length > 0) {
                errorMessage = Object.values(errors)[0];
            }
            
            if (window.$toast) {
                window.$toast.error(errorMessage);
            } else {
                alert(errorMessage);
            }
            
            // إذا كان الخطأ بسبب عدم تسجيل الدخول، إعادة توجيه إلى صفحة تسجيل الدخول
            if (errorMessage.includes('تسجيل الدخول') || errorMessage.includes('login') || errorMessage.includes('401')) {
                router.visit(route('client.login'));
            }
        }
    });
};

const addToFavorites = (product) => {
    router.post(route('favorites.add'), {
        product_id: product.id
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
            } else {
                // رسالة نجاح افتراضية
                if (window.$toast) {
                    window.$toast.success('تم إضافة المنتج إلى المفضلة بنجاح');
                } else {
                    alert('تم إضافة المنتج إلى المفضلة بنجاح');
                }
            }
        },
        onError: (errors) => {
            console.error('خطأ في إضافة المنتج إلى المفضلة:', errors);
            let errorMessage = 'حدث خطأ أثناء إضافة المنتج إلى المفضلة';
            
            if (errors.message) {
                errorMessage = errors.message;
            } else if (typeof errors === 'string') {
                errorMessage = errors;
            } else if (errors && Object.keys(errors).length > 0) {
                errorMessage = Object.values(errors)[0];
            }
            
            if (window.$toast) {
                window.$toast.error(errorMessage);
            } else {
                alert(errorMessage);
            }
            
            // إذا كان الخطأ بسبب عدم تسجيل الدخول، إعادة توجيه إلى صفحة تسجيل الدخول
            if (errorMessage.includes('تسجيل الدخول') || errorMessage.includes('login') || errorMessage.includes('401')) {
                router.visit(route('client.login'));
            }
        }
    });
};

onMounted(() => {
    if (props.product && props.product.price) {
        const price = parseFloat(props.product.price);
        if (!isNaN(price) && price > 0) {
            baseProductPrice.value = price;
        }
    }
    
    // للتشخيص - عرض بيانات الألوان والخصائص
    if (props.product) {
        console.log('📦 بيانات المنتج:', {
            'colors': props.product.colors,
            'colors_type': typeof props.product.colors,
            'colors_isArray': Array.isArray(props.product.colors),
            'colors_length': props.product.colors?.length
        });
        
        if (props.product.attributes) {
            console.log('📋 جميع الخصائص:', props.product.attributes);
        }
    }
});

</script>
<style scoped>
    .color-option {
        display: inline-flex !important;
        align-items: center;
        cursor: pointer;
        margin: 5px;
        position: relative;
    }

    .color-option input {
        display: none;
    }

    .color-option span {
        width: 40px;
        height: 40px;
        border: 2px solid #a4a1a1;
        display: inline-block;
        transition: 0.2s;
        border-radius: 4px;
    }

    .color-option.selected span {
        border-color: #2196f3;
        box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.3);
    }

    .color-option:hover span {
        border-color: #666;
        transform: scale(1.1);
    }

    .quantity-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background: #f5f5f5 !important;
    }

    .quantity-btn:hover:not(:disabled) {
        background: #f0f0f0;
    }

    .quantity-btn:active:not(:disabled) {
        background: #e0e0e0;
    }

    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        appearance: none;
        margin: 0;
    }

    .btn.add-cart:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        background-color: #6c757d !important;
    }

    .btn.add-cart:disabled:hover {
        background-color: #6c757d !important;
    }

</style>
<template>
    <Head title="المتجر" />
    <FrontLayout>
        <nav aria-label="breadcrumb" class="breadcrumb-nav">
            <div class="container">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><Link :href="route('/')"><i class="icon-home"></i></Link></li>
                    <li class="breadcrumb-item"><Link :href="route('web.category', { id: product.category_id })">القسم</Link></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ product.name }}</li>
                </ol>
            </div><!-- End .container -->
        </nav>
        <div class="container pt-1">
            <div class="row">
                <div class="col-lg-12 main-content">
                    <div class="product-single-container product-single-default">
                        <div class="cart-message d-none">
                            <strong class="single-cart-notice">{{ product.name }}</strong>
                            <span>تم اضافته للسلة</span>
                        </div>

                        <div class="row">
                            <div class="col-md-6 product-single-gallery">
                                <div class="product-slider-container">
                                    <div class="label-group">
                                        <div v-if="product.old_price && product.old_price > product.price" class="product-label label-hot">خصم</div>
                                        <div v-if="product.old_price && product.old_price > product.price" class="product-label label-sale">
                                            -{{ Math.round(((product.old_price - product.price) / product.old_price) * 100) }}%
                                        </div>
                                    </div>

                                    <div class="product-single-carousel owl-carousel owl-theme show-nav-hover">
                                        <template v-if="product.images && product.images.length">
                                            <div class="product-item" v-for="(img, idx) in product.images" :key="idx">
                                                <img class="product-single-image" :src="getProductImage({ images: [img] })" :data-zoom-image="getProductImage({ images: [img] })" width="468" height="468" alt="product" />
                                            </div>
                                        </template>
                                        <template v-else>
                                            <div class="product-item">
                                                <img class="product-single-image" src="/default-placeholder.png" width="468" height="468" alt="default-product" />
                                            </div>
                                        </template>
                                    </div>
                                    <!-- End .product-single-carousel -->
                                    <span class="prod-full-screen">
                                        <i class="icon-plus"></i>
                                    </span>
                                </div>

                                <div class="prod-thumbnail owl-dots">
                                    <template v-if="product.images && product.images.length">
                                        <div class="owl-dot" v-for="(img, idx) in product.images" :key="idx">
                                            <img :src="getProductImage({ images: [img] })" width="110" height="110" alt="product-thumbnail" />
                                        </div>
                                    </template>
                                    <template v-else>
                                        <div class="owl-dot">
                                            <img src="/default-placeholder.png" width="110" height="110" alt="default-thumbnail" />
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <!-- End .product-single-gallery -->

                            <div class="col-md-6 product-single-details">
                                <h1 class="product-title">{{ product.name }}</h1>

                                <!-- End .price-box -->

                                <hr class="short-divider">

                                <div class="price-box">
                                    <span class="product-price">{{ product.price }} {{ currency }}</span>
                                </div>

                                <div class="product-desc">
                                    <p>
                                        {{ product.description }}
                                    </p>
                                </div><!-- End .product-desc -->

                                <ul class="single-info-list">
                                    <li>
                                        SKU:  <strong>{{ product.sku }}</strong>
                                    </li>
                                    <li>
                                        <span>المخزون:</span>
                                        <strong v-if="product.stock_quantity && product.stock_quantity > 0">{{ product.stock_quantity }}</strong>
                                        <strong v-else class="text-danger">غير متوفر</strong>
                                    </li>
                                </ul>

                                <div class="product-filters-container">
                                    <!-- عرض الألوان من product.colors -->
                                    <div v-if="product.colors && product.colors.length > 0" class="product-single-filter flex-column align-items-start">
                                        <label>اللون:</label>
                                        <div class="color-options" style="display: flex; gap: 10px; flex-wrap: wrap;">
                                            <label 
                                                v-for="(color, index) in product.colors" 
                                                :key="index" 
                                                class="color-option" 
                                                :class="{ 'selected': selectedColor === color }"
                                            >
                                                <input 
                                                    type="radio" 
                                                    name="product_color" 
                                                    :value="color"
                                                    :checked="selectedColor === color"
                                                    @change="selectedColor = color"
                                                />
                                                <span 
                                                    :style="`background-color: ${getColorFromString(color)};`"
                                                    :title="color"
                                                ></span>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <!-- عرض الخصائص (Attributes) -->
                                    <div v-for="attribute in product.attributes" :key="attribute.id" class="product-single-filter flex-column align-items-start">
                                        <label>{{ attribute.name }}:</label>
                                        <!-- عرض عادي للخصائص -->
                                        <ul class="config-size-list">
                                            <li v-for="value in attribute.values" :key="value.id">
                                                <a href="javascript:;"
                                                    class="d-flex align-items-center justify-content-center config-checkbox"
                                                    :class="{ 'selected': selectedAttributes[attribute.id] === value.id }"
                                                    @click="selectAttribute(attribute.id, value)"
                                                    :style="{
                                                        border: '1px solid #ccc',
                                                        borderRadius: '4px',
                                                        padding: '6px 12px',
                                                        margin: '2px',
                                                        minWidth: '40px',
                                                        cursor: 'pointer',
                                                        background: selectedAttributes[attribute.id] === value.id ? '#e6f7ff' : '#fff'
                                                    }"
                                                >
                                                    <span :style="{
                                                        display: 'inline-block',
                                                        width: '18px',
                                                        height: '18px',
                                                        border: '1px solid #aaa',
                                                        borderRadius: '3px',
                                                        marginRight: '8px',
                                                        background: selectedAttributes[attribute.id] === value.id ? '#2196f3' : '#fff',
                                                        verticalAlign: 'middle',
                                                        position: 'relative'
                                                    }">
                                                        <span v-if="selectedAttributes[attribute.id] === value.id" :style="{
                                                            position: 'absolute',
                                                            top: '2px',
                                                            left: '4px',
                                                            width: '8px',
                                                            height: '12px',
                                                            borderRight: '2px solid #fff',
                                                            borderBottom: '2px solid #fff',
                                                            transform: 'rotate(45deg)'
                                                        }"></span>
                                                    </span>
                                                    {{ value.label || value.value || value.name }}
                                                    <span v-if="value.price && parseFloat(value.price) > 0" class="ml-2 text-success">+{{ parseFloat(value.price).toFixed(2) }} {{ currency }}</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="product-single-filter">
                                        <a class="font1 text-uppercase clear-btn ml-0" href="#" @click.prevent="clearAttributes">Clear</a>
                                    </div>
                                </div>

                                <!-- عرض السعر الإجمالي -->
                                <div class="custom-total-price-box" style="margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 8px; border: 2px solid #e9ecef;">
                                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                            <span style="font-size: 16px; color: #666;">السعر الأساسي:</span>
                                            <strong style="font-size: 20px; color: #333;">{{ basePrice.toFixed(2) }}</strong>
                                            <span style="font-size: 16px; color: #666;">{{ currency }}</span>
                                        </div>
                                        <div v-if="getAdditionalPrice > 0" style="display: flex; align-items: center; gap: 10px;">
                                            <span style="font-size: 16px; color: #666;">إضافات:</span>
                                            <strong style="font-size: 18px; color: #28a745;">+{{ getAdditionalPrice.toFixed(2) }}</strong>
                                            <span style="font-size: 16px; color: #666;">{{ currency }}</span>
                                        </div>
                                    </div>
                                    <div style="margin-top: 15px; padding-top: 15px; border-top: 2px dashed #dee2e6; display: flex; align-items: center; justify-content: space-between;">
                                        <span style="font-size: 18px; font-weight: 600; color: #333;">الإجمالي:</span>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <strong style="font-size: 28px; font-weight: bold; color: #d26e4b;">{{ getTotalPrice.toFixed(2) }}</strong>
                                            <span style="font-size: 20px; color: #666;">{{ currency }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="product-action" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                     

                                    <div class="input-group" style=" width: 160px;">
                                        <div class="input-group-prepend">
                                            <button class="btn btn-outline-secondary" type="button" @click="decreaseQuantity">-</button>
                                        </div>
                                        <input type="text" style="text-align: center;height: 50px;border-top: 1px solid; border-bottom: 1px solid; border-left: 1px solid; border-right: 1px solid;" class="form-control" v-model="quantity" placeholder="" aria-label="" aria-describedby="basic-addon1">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" @click="increaseQuantity">+</button>
                                        </div> 
                                    </div>
                                    <!-- End .product-single-qty -->

                                    <button
                                        class="btn btn-dark add-cart"
                                        @click="addToCart(product)" 
                                        title="Add to Cart"
                                        style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 20px; height: auto; min-height: 40px; white-space: nowrap;"
                                    > 
                                        <span>أضف للسلة {{ quantity }}</span>
                                    </button>

                                    <Link :href="route('client.cart')" class="btn btn-gray view-cart d-none"> عرض السلة </Link>
                                </div><!-- End .product-action -->

                                <hr class="divider mb-0 mt-0">

                                <div class="product-single-share mb-2">
                                    <label class="sr-only">Share:</label>

                              

                                    <a href="javascript:;" @click="addToFavorites(product)" class="btn-icon-wish add-wishlist"
                                        title="Add to Wishlist"><i class="icon-wishlist-2"></i><span> إضافة للمفضلة </span></a>
                                </div><!-- End .product single-share -->
                            </div><!-- End .product-single-details -->
                        </div><!-- End .row -->
                    </div><!-- End .product-single-container -->
 
                </div><!-- End .main-content -->


            </div><!-- End .row -->
        </div><!-- End .container -->

    
    </FrontLayout>
</template>
