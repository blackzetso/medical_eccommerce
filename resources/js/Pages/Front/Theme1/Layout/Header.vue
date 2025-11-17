<template>
<header class="header font-sans">
            <div class="header-middle sticky-header">
                <div class="container">
                    <div class="header-left">
                        <button class="mobile-menu-toggler" type="button">
                            <i class="fas fa-bars"></i>
                        </button>

                        <a href="demo3.html" class="logo">
                            <img :src="asset('front/theme1/images/logo-black.png')" class="d-none d-sm-block" alt="Porto Logo">
                        </a>

                        <nav class="main-nav font2">
                            <ul class="menu">
                                <li class="active">
                                    <Link :href="route('/')">الرئيسية</Link>
                                </li>
                                <li>
                                    <Link :href="route('web.categories')">الأقسام</Link>
                                </li>
                                <li>
                                    <Link :href="route('web.products')">المنتجات</Link>
                                </li>
                            </ul>
                        </nav>
                    </div><!-- End .header-left -->

                    <div class="header-right  ">
                            <!-- <input type="search" class="form-control mr-5" style="width: 90px;margin-bottom: unset;" name="q" id="q" placeholder="ابحث هنا..." required=""> -->
                            <!-- <button class="btn text-dark icon-magnifier" type="submit"></button> -->

                            <template v-if="user">
                                <Link :href="route('client.dashboard')" class="header-icon  " title="حسابي"><i class="icon-user-2"></i></Link>
                                <a href="wishlist.html" class="header-icon header-icon-wishlist" title="Wishlist"><i class="icon-wishlist-2"></i></a>
                                <CartDropdown />
                            </template>
                            <template v-else>
                                <Link :href="route('client.login')" class="btn  btn-primary mr-3" title="تسجيل الدخول">
                                    تسجيل الدخول
                                </Link>
                                <Link :href="route('client.register')" class="btn btn-outline-primary ml-3 mr-5 ms-2" title="إنشاء حساب">
                                    إنشاء حساب
                                </Link>
                            </template>


                    </div><!-- End .header-right -->
                </div><!-- End .container -->
            </div><!-- End .header-middle -->
        </header>
</template>

<script setup>
import { ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import CartDropdown from './CartDropdown.vue'
const showSearch = ref(false)
const asset = (path) => '/' + path

const page = usePage()
const user = page.props.auth?.user
</script>
