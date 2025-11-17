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

input[type=number] {
    -moz-appearance: textfield;
}

input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

</style>
<template>
    <Head title="لوحة التحكم" />
    <FrontLayout>
        <div class="container">
            <ul class="checkout-progress-bar d-flex justify-content-center flex-wrap">
                <li class="active">
                    <Link :href="route('client.cart')">سلة التسوق</Link>
                </li>
                <li>
                    <Link :href="route('client.checkout')">الدفع</Link>
                </li>
                <li class="disabled">
                    <a href="cart.html">تتبع الطلب</a>
                </li>
            </ul>

            <div class="row">
                <div class="col-lg-8">
                    <div class="cart-table-container">
                        <table class="table table-cart">
                            <thead>
                                <tr>
                                    <th class="thumbnail-col"></th>
                                    <th class="product-col">المنتج</th>
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
                                                <img :src="item.image ? item.image : '/default-placeholder.png'" alt="product">
                                            </Link>
                                            <Link :href="route('cart.remove', item.id)" class="btn-remove icon-cancel" title="Remove Product" @click.prevent="removeFromCart(item.id)"></Link>
                                        </figure>
                                    </td>
                                    <td class="product-col">
                                        <h5 class="product-title">
                                            <Link :href="route('web.product', item.id)">{{ item.product.name }}</Link>
                                        </h5>
                                    </td>
                                    <td>{{ item.product.price }}</td>
                                    <td>
                                        <div class="custom-qty-selector">
                                            <button class="qty-btn" @click.prevent="removeQty(item)">-</button>
                                            <input type="number" min="1" :value="item.quantity" class="qty-input" @change="updateQty(item, $event)" />
                                            <button class="qty-btn" @click.prevent="addQty(item)">+</button>
                                        </div>
                                    </td>
                                    <td class="text-right"><span class="subtotal-price">{{ item.product.price * item.quantity }}</span></td>
                                </tr>
                            </tbody>


                          
                        </table>
                    </div><!-- End .cart-table-container -->
                </div><!-- End .col-lg-8 -->

                <div class="col-lg-4">
                    <div class="cart-summary">
                        <h3> إجمالي الفاتورة </h3>

                        <table class="table table-totals">
                            <tbody>
                                <tr>
                                    <td>المجموع الفرعي</td>
                                    <td> {{  Number(totalPrice) }}</td>
                                </tr>
                                <tr>
                                    <td>توصيل</td>
                                    <td>15</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td>الإجمالي</td>
                                    <td>{{ Number(totalPrice) + 15 }}</td>
                                </tr>
                            </tfoot>
                        </table>

                        <div class="checkout-methods">
                            <button type="button" class="btn btn-block btn-dark" @click="createOrder">المتابعة إلى الدفع
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

const addQty = (item) => {
    const newQty = item.quantity + 1;
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
});

// متغير محلي reactive للسلة
const cartItemsRef = ref(props.cartItems.map(item => ({ ...item })));

// حساب subtotal و total ديناميكيًا بناءً على cartItemsRef
const subtotal = computed(() => {
    return cartItemsRef.value.reduce((sum, item) => sum + (item.product.price * item.quantity), 0);
});

const shippingCost = 0;
const total = computed(() => {
    return subtotal.value + shippingCost;
});

// دالة لإنشاء الأوردر مباشرة عند الضغط على زر المتابعة
const createOrder = () => {
    router.post(route('order.create'), {
        items: cartItemsRef.value,
        total: total.value
    }, {
        onSuccess: () => {
            Swal.fire({
                icon: 'success',
                title: 'تم إنشاء الطلب بنجاح',
                showConfirmButton: false,
                timer: 2000
            });
            // يمكن إعادة التوجيه لصفحة الطلبات أو صفحة الدفع
            router.visit(route('client.myorders'));
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





// const getProductImage = (product) => {
//     if (product.images && product.images.length > 0) {
//         let img = product.images[0];
//         // إذا كان المسار لا يبدأ بـ http أو /
//         if (!img.startsWith('http') && !img.startsWith('/')) {
//             img = '/' + img;
//         }
//         return img;
//     }
//     return '/front/theme1/images/demoes/demo3/products/product-1.jpg';
// };

onMounted(() => {
    // Initialize component
    console.log('Shop component mounted');
    if (props.category) {
        console.log('Current category:', props.category);
    }
});
</script>


