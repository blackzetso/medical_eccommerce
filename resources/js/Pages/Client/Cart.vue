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
                    <a href="cart.html">Shopping Cart</a>
                </li>
                <li>
                    <a href="checkout.html">Checkout</a>
                </li>
                <li class="disabled">
                    <a href="cart.html">Order Complete</a>
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
                                <tr v-for="item in cartItems" :key="item.id" class="product-row">
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
                                            <button class="qty-btn" type="button" @click="item.quantity = Math.max(1, item.quantity - 1)">-</button>
                                            <input type="number" min="1" :value="item.quantity" @input="e => item.quantity = Math.max(1, Number(e.target.value))" class="qty-input" />
                                            <button class="qty-btn" type="button" @click="item.quantity++">+</button>
                                        </div>
                                    </td>
                                    <td class="text-right"><span class="subtotal-price">{{ item.product.price * item.quantity }}</span></td>
                                </tr>
                            </tbody>


                            <tfoot>
                                <tr>
                                    <td colspan="5" class="clearfix">
                                        <div class="float-right">
                                            <button type="submit" class="btn btn-shop btn-update-cart">
                                                Update Cart
                                            </button>
                                        </div><!-- End .float-right -->
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div><!-- End .cart-table-container -->
                </div><!-- End .col-lg-8 -->

                <div class="col-lg-4">
                    <div class="cart-summary">
                        <h3>CART TOTALS</h3>

                        <table class="table table-totals">
                            <tbody>
                                <tr>
                                    <td>Subtotal</td>
                                    <td>$17.90</td>
                                </tr>

                                <tr>
                                    <td colspan="2" class="text-left">
                                        <h4>Shipping</h4>

                                        <div class="form-group form-group-custom-control">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" class="custom-control-input" name="radio"
                                                    checked>
                                                <label class="custom-control-label">Local pickup</label>
                                            </div><!-- End .custom-checkbox -->
                                        </div><!-- End .form-group -->

                                        <div class="form-group form-group-custom-control mb-0">
                                            <div class="custom-control custom-radio mb-0">
                                                <input type="radio" name="radio" class="custom-control-input">
                                                <label class="custom-control-label">Flat rate</label>
                                            </div><!-- End .custom-checkbox -->
                                        </div><!-- End .form-group -->

                                        <form action="#">
                                            <div class="form-group form-group-sm">
                                                <label>Shipping to <strong>NY.</strong></label>
                                                <div class="select-custom">
                                                    <select class="form-control form-control-sm">
                                                        <option value="USA">United States (US)</option>
                                                        <option value="Turkey">Turkey</option>
                                                        <option value="China">China</option>
                                                        <option value="Germany">Germany</option>
                                                    </select>
                                                </div><!-- End .select-custom -->
                                            </div><!-- End .form-group -->

                                            <div class="form-group form-group-sm">
                                                <div class="select-custom">
                                                    <select class="form-control form-control-sm">
                                                        <option value="NY">New York</option>
                                                        <option value="CA">California</option>
                                                        <option value="TX">Texas</option>
                                                    </select>
                                                </div><!-- End .select-custom -->
                                            </div><!-- End .form-group -->

                                            <div class="form-group form-group-sm">
                                                <input type="text" class="form-control form-control-sm"
                                                    placeholder="Town / City">
                                            </div><!-- End .form-group -->

                                            <div class="form-group form-group-sm">
                                                <input type="text" class="form-control form-control-sm"
                                                    placeholder="ZIP">
                                            </div><!-- End .form-group -->

                                            <button type="submit" class="btn btn-shop btn-update-total">
                                                Update Totals
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            </tbody>

                            <tfoot>
                                <tr>
                                    <td>Total</td>
                                    <td>$17.90</td>
                                </tr>
                            </tfoot>
                        </table>

                        <div class="checkout-methods">
                            <a href="cart.html" class="btn btn-block btn-dark">Proceed to Checkout
                                <i class="fa fa-arrow-right"></i></a>
                        </div>
                    </div><!-- End .cart-summary -->
                </div><!-- End .col-lg-4 -->
            </div><!-- End .row -->
        </div><!-- End .container -->

        <div class="mb-6"></div>
    </FrontLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
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

// Props from the controller
const props = defineProps({
    cartItems: Array,
    totalPrice: Number,
});

// Reactive data
const selectedCategories = ref([]);
const minPrice = ref(null);
const maxPrice = ref(null);
const sortBy = ref('latest');
const perPage = ref(12);





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


