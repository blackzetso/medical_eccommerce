<style scoped>
    .product-single-qty {
    display: inline-block;
    max-width: 104px;
    vertical-align: middle
}

.product-single-qty .bootstrap-touchspin.input-group {
    -ms-flex-wrap: nowrap;
    flex-wrap: nowrap;
    max-width: none;
    padding-right: 0
}

.product-single-qty .bootstrap-touchspin .form-control {
    width: 2.7em;
    height: 36px;
    padding: 10px 2px;
    color: #222529;
    font-size: 1.4rem;
    font-family: Poppins,sans-serif;
    text-align: center
}

.product-single-qty .bootstrap-touchspin .form-control,.product-single-qty .bootstrap-touchspin .form-control:not(:focus),.product-single-qty .btn-outline:not(:disabled):not(.disabled):active {
    border-color: #dae2e6
}

.product-single-qty .btn {
    width: 2.2em;
    padding: 0
}

.product-single-qty .btn.btn-down-icon:hover:after,.product-single-qty .btn.btn-down-icon:hover:before,.product-single-qty .btn.btn-up-icon:hover:after,.product-single-qty .btn.btn-up-icon:hover:before {
    background-color: #000
}

.product-single-qty .btn.btn-outline {
    border-color: #e7e7e7
}

.product-single-qty .btn.btn-down-icon:after,.product-single-qty .btn.btn-up-icon:after,.product-single-qty .btn.btn-up-icon:before {
    display: block;
    position: absolute;
    top: 50%;
    left: 50%;
    width: 9px;
    height: 1px;
    margin-left: -0.55rem;
    background: #222529;
    content: ""
}

.product-single-qty .btn.btn-up-icon:before {
    transform: rotate(90deg)
}

.custom-qty-selector {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 2px;
    width: 100px;
    background: #fff;
    border: 1px solid #dae2e6;
    border-radius: 4px;
    overflow: hidden;
}
.qty-btn {
    border-left: 1px solid #e7e7e7 !important;
    border-right: 1px solid #e7e7e7 !important;
    width: 50px;
    height: 50px;
    border: none;
    background: #fff;
    color: #222529;
    font-size: 18px;
    cursor: pointer;
    transition: background 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}
.qty-btn:hover {
    background: #e7e7e7;
}
.qty-input {
    width: 28px;
    height: 28px;
    text-align: center;
    border: none;
    font-size: 16px;
    color: #222529;
    background: transparent;
    outline: none;
}

.cart-color-wrapper {
    display: flex;
    align-items: center;
    justify-content: flex-start;
}

.cart-color-swatch {
    width: 24px;
    height: 24px;
    border-radius: 4px;
    border: 1px solid #ccc;
    display: inline-block;
}

input[type=number] {
    -moz-appearance: textfield;
}

input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* تحسين تصميم شريط التقدم */
.checkout-progress-bar {
    margin: 2rem 0 3rem;
    padding: 0;
    list-style: none;
}

.checkout-progress-bar li {
    display: inline-block;
    position: relative;
    margin: 0;
    font-size: 1.6rem;
    font-weight: 500;
    letter-spacing: normal;
}

.checkout-progress-bar li.active a {
    color: #08C !important;
    font-weight: 600;
}

.checkout-progress-bar li.disabled a,
.checkout-progress-bar li.active + li a {
    color: #919292 !important;
    cursor: default;
    pointer-events: none;
}

.checkout-progress-bar li:not(:first-child) {
    margin-right: 1.5rem;
    padding-right: 2.5rem;
}

.checkout-progress-bar li:not(:first-child):before {
    content: '<';
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    font-size: 1.4rem;
    font-weight: 400;
    font-family: Arial, sans-serif;
}

.checkout-progress-bar li a {
    text-decoration: none;
    transition: color 0.3s ease;
}

.checkout-progress-bar li.active a:hover {
    color: #08C !important;
}

/* تنسيق خيارات التوصيل */
.delivery-options {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    background-color: #f8f9fa;
}

.delivery-options .form-check {
    padding: 12px;
    border: 2px solid #e9ecef;
    border-radius: 6px;
    transition: all 0.3s ease;
    background-color: #fff;
}

.delivery-options .form-check:hover {
    border-color: #667eea;
}

.delivery-options .form-check-input:checked + .form-check-label {
    color: #667eea;
    font-weight: 600;
}

.delivery-options .form-check-input:checked {
    background-color: #667eea;
    border-color: #667eea;
}

</style>
<template>
    <Head title="لوحة التحكم" />
    <FrontLayout>
        <div class="container">
            <ul class="checkout-progress-bar d-flex justify-content-center flex-wrap" dir="rtl">
                <li class="active">
                    <Link :href="route('client.cart')">سلة التسوق</Link>
                </li>
                
                <li class="disabled">
                    <a href="javascript:void(0)">تتبع الطلب</a>
                </li>
            </ul>

            <div class="row">
                <div class="col-lg-8">
                    <div v-if="cartItemsRef.length" class="cart-table-container">
                        <table class="table table-cart">
                            <thead>
                                <tr>
                                    <th class="thumbnail-col"></th>
                                    <th class="product-col">المنتج</th>
                                    <th class="attributes-col">الخصائص</th>
                                    <th class="color-col">اللون</th>
                                    <th class="price-col">السعر</th>
                                    <th class="qty-col">الكمية</th>
                                    <th class="text-right">الإجمالي</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in cartItemsRef" :key="item.id" class="product-row">
                                    <td>
                                        <figure class="product-image-container">
                                            <Link :href="route('web.product', item.id)" class="product-image">
                                                <img :src="getProductImage(item.product)" width="273" height="273" :alt="item.product.name" />
                                            </Link>
                                            <Link :href="route('cart.remove', item.id)" class="btn-remove icon-cancel" title="Remove Product" @click.prevent="removeFromCart(item.id)"></Link>
                                        </figure>
                                    </td>
                                    <td class="product-col">
                                        <h5 class="product-title">
                                            <Link :href="route('web.product', item.id)">{{ item.product.name }}</Link>
                                        </h5>
                                    </td>
                                    <!-- عمود الخصائص -->
                                    <td>
                                        <ul v-if="getSelectedOptions(item).length" dir="rtl" class="list-unstyled mb-0">
                                            <li v-for="opt in getSelectedOptions(item)" :key="opt.attribute_id">
                                                <small class="text-muted">
                                                    {{ opt.attribute_name }}:
                                                    <span class="font-weight-bold">{{ opt.value_label }}</span>
                                                    <span v-if="opt.price && Number(opt.price) > 0" class="text-success">
                                                        (+{{ formatPrice(opt.price) }})
                                                    </span>
                                                </small>
                                            </li>
                                        </ul>
                                    </td>
                                    <!-- عمود اللون -->
                                    <td>
                                        <div v-if="item.color" class="cart-color-wrapper">
                                            <span
                                                class="cart-color-swatch"
                                                :style="{ backgroundColor: getColorCss(item.color) }"
                                                :title="item.color"
                                            ></span>
                                        </div>
                                    </td>
                                    <td>{{ formatPrice(item.unit_price ?? item.product.price) }}</td>
                                    <td>
                                        <div class="custom-qty-selector">
                                            <button class="qty-btn" @click.prevent="removeQty(item)">-</button>
                                            <input type="number" min="1" :value="item.quantity" class="qty-input" @change="updateQty(item, $event)" />
                                            <button class="qty-btn" @click.prevent="addQty(item)">+</button>
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        <span class="subtotal-price">
                                            {{ formatPrice((item.unit_price ?? item.product.price) * item.quantity) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div><!-- End .cart-table-container -->

                    <!-- حالة السلة الفارغة -->
                    <div v-else class="text-center py-5">
                        <h4 class="mb-3">سلة المشتريات فارغة</h4>
                        <p class="text-muted mb-4">لم تقم بإضافة أي منتجات حتى الآن.</p>
                        <Link :href="route('web.products')" class="btn btn-outline-dark">
                            ابدأ التسوق الآن
                        </Link>
                    </div>
                </div><!-- End .col-lg-8 -->

                <div class="col-lg-4">
                    <div class="cart-summary">
                        <!-- اختيار نوع التوصيل -->
                        <div class="delivery-options mb-4">
                            <h4 class="mb-3">اختر نوع الاستلام</h4>
                            <div class="form-check mb-2">
                                <input 
                                    class="form-check-input" 
                                    type="radio" 
                                    name="deliveryType" 
                                    id="delivery" 
                                    value="delivery" 
                                    v-model="deliveryType"
                                />
                                <label class="form-check-label" for="delivery">
                                    <i class="icon-truck" style="margin-left: 8px;"></i>
                                    توصيل 
                                    <small class="d-block text-muted">رسوم التوصيل: {{ (props.shippingCost || 15).toFixed(2) }} جنيه</small>
                                </label>
                            </div>
                            <div class="form-check">
                                <input 
                                    class="form-check-input" 
                                    type="radio" 
                                    name="deliveryType" 
                                    id="pickup" 
                                    value="pickup" 
                                    v-model="deliveryType"
                                />
                                <label class="form-check-label" for="pickup">
                                    <i class="icon-store mr-4" style="margin-left: 8px;"></i>
                                    استلام من المخزن
                                    <small class="d-block text-muted">مجاني</small>
                                </label>
                            </div>
                        </div>

                        <h3> إجمالي الفاتورة </h3>

                        <table class="table table-totals">
                            <tbody>
                                <tr>
                                    <td>المجموع الفرعي</td>
                                    <td>{{ Number(subtotal).toFixed(2) }} جنيه</td>
                                </tr>
                                <tr>
                                    <td>{{ deliveryType === 'delivery' ? 'رسوم التوصيل' : 'رسوم الاستلام' }}</td>
                                    <td>{{ shippingCost.toFixed(2) }} جنيه</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td>الإجمالي</td>
                                    <td>{{ Number(total).toFixed(2) }} جنيه</td>
                                </tr>
                            </tfoot>
                        </table>

                        <div class="checkout-methods">
                            <button type="button" class="btn btn-block btn-dark" @click="createOrder">إتمام الطلب
                                <i class="fa fa-arrow-right"></i>
                            </button>
                        </div>
                    </div><!-- End .cart-summary -->
                </div><!-- End .col-lg-4 -->
            </div><!-- End .row -->
        </div><!-- End .container -->

        <div class="mb-6"></div>
    </FrontLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import FrontLayout from '@/Pages/Front/Theme1/Layout/App.vue';

function logout() {
	router.post(route('logout'));
}

import Swal from 'sweetalert2';

const removeFromCart = (id) => {
    router.post(route('cart.remove'), { id }, {
        onSuccess: () => {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'تم حذف المنتج من السلة',
                showConfirmButton: false,
                timer: 2000
            });
        }
    });
};

const updateQty = (item, event) => {
    let newQty = parseInt(event.target.value);
    if (isNaN(newQty) || newQty < 1) newQty = 1;
    
    // التحقق من المخزون المتاح
    if (item.product && item.product.manage_stock) {
        if (newQty > item.product.stock_quantity) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: 'الكمية المطلوبة (' + newQty + ') تتجاوز المخزون المتاح (' + item.product.stock_quantity + ')',
                showConfirmButton: false,
                timer: 3000
            });
            // إعادة تعيين القيمة إلى الكمية الحالية
            event.target.value = item.quantity;
            return;
        }
    }
    
    router.post(route('cart.update'), { id: item.id, quantity: newQty }, {
        onSuccess: () => {
            item.quantity = newQty;
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'تم تحديث الكمية',
                showConfirmButton: false,
                timer: 2000
            });
        },
        onError: (errors) => {
            // إعادة تعيين القيمة في حالة الخطأ
            event.target.value = item.quantity;
            let errorMessage = 'حدث خطأ أثناء تحديث الكمية';
            if (errors.message) {
                errorMessage = errors.message;
            }
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: errorMessage,
                showConfirmButton: false,
                timer: 3000
            });
        }
    });
};

const addQty = (item) => {
    const newQty = item.quantity + 1;
    
    // التحقق من المخزون المتاح
    if (item.product && item.product.manage_stock) {
        if (newQty > item.product.stock_quantity) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: 'الكمية المطلوبة (' + newQty + ') تتجاوز المخزون المتاح (' + item.product.stock_quantity + ')',
                showConfirmButton: false,
                timer: 3000
            });
            return;
        }
    }
    
    router.post(route('cart.update'), { id: item.id, quantity: newQty }, {
        onSuccess: () => {
            item.quantity = newQty;
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'تم تحديث الكمية',
                showConfirmButton: false,
                timer: 2000
            });
        },
        onError: (errors) => {
            let errorMessage = 'حدث خطأ أثناء تحديث الكمية';
            if (errors.message) {
                errorMessage = errors.message;
            }
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: errorMessage,
                showConfirmButton: false,
                timer: 3000
            });
        }
    });
};

const removeQty = (item) => {
    const newQty = item.quantity > 1 ? item.quantity - 1 : 1;
    router.post(route('cart.update'), { id: item.id, quantity: newQty }, {
        onSuccess: () => {
            item.quantity = newQty;
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'تم تحديث الكمية',
                showConfirmButton: false,
                timer: 2000
            });
        }
    });
};


// Props from the controller
const props = defineProps({
    cartItems: Array,
    totalPrice: Number,
    shippingCost: {
        type: Number,
        default: 15
    },
});

// متغير محلي reactive للسلة
const cartItemsRef = ref(props.cartItems.map(item => ({ ...item })));

// تنسيق السعر
const formatPrice = (price) => {
    const num = Number(price) || 0;
    return num.toFixed(2);
};

// تحويل قيمة اللون إلى قيمة صالحة للـ CSS (مع دعم بعض الأسماء العربية)
const getColorCss = (color) => {
    if (!color) return '#ccc';

    const c = String(color).trim();
    if (c.startsWith('#')) {
        return c;
    }

    const map = {
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

    const lower = c.toLowerCase();
    return map[lower] || c;
};

// إرجاع قائمة بالخصائص المختارة لكل عنصر في السلة
const getSelectedOptions = (item) => {
    if (!item || !item.product || !item.product.attributes || !item.attributes) {
        return [];
    }

    const result = [];

    item.product.attributes.forEach((attr) => {
        const selectedValueId = item.attributes[attr.id];
        if (!selectedValueId) return;

        const value = attr.values?.find(v => v.id === selectedValueId);
        if (!value) return;

        result.push({
            attribute_id: attr.id,
            attribute_name: attr.name,
            value_label: value.label || value.value || value.name,
            price: value.price ?? 0,
        });
    });

    return result;
};

// حساب subtotal و total ديناميكيًا بناءً على cartItemsRef
const subtotal = computed(() => {
    return cartItemsRef.value.reduce((sum, item) => {
        const unit = item.unit_price ?? item.product.price;
        return sum + (unit * item.quantity);
    }, 0);
});

// نوع التوصيل المختار (افتراضياً: توصيل)
const deliveryType = ref('delivery');

// حساب رسوم التوصيل بناءً على النوع المختار
const shippingCost = computed(() => {
    return deliveryType.value === 'delivery' ? (props.shippingCost || 15) : 0;
});

const total = computed(() => {
    return subtotal.value + shippingCost.value;
});

// دالة لإنشاء الأوردر مباشرة عند الضغط على زر المتابعة
const createOrder = () => {
    router.post(route('order.create'), {
        items: cartItemsRef.value,
        total: total.value,
        delivery_type: deliveryType.value
    }, {
        onSuccess: () => {
            Swal.fire({
                icon: 'success',
                title: 'تم إنشاء الطلب بنجاح',
                showConfirmButton: false,
                timer: 2000
            }); 
        },
        onError: () => {
            Swal.fire({
                icon: 'error',
                title: 'حدث خطأ أثناء إنشاء الطلب',
                showConfirmButton: false,
                timer: 2000
            });
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
    // Initialize component
    console.log('Shop component mounted');
    if (props.category) {
        console.log('Current category:', props.category);
    }
});
</script>


