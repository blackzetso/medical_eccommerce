<script setup>
import AppLayout from '@/Pages/Front/Theme1/Layout/App.vue'
import { Head, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
    sliders: Array,
    categories: Array
})
</script>

<template>
    <Head title="الرئيسية" />
    <AppLayout>
        <!-- Dynamic Sliders -->
        <div v-if="sliders && sliders.length > 0" style="height: 50vh !important;" class="home-slider slide-animate owl-carousel owl-theme show-nav-hover nav-big">
            <div v-for="slider in sliders" :key="slider.id" class="home-slide banner d-flex align-items-center position-relative">
                <img class="slide-bg" style="height: 50vh !important; width: 100%; object-fit: cover;" :src="slider.image" :alt="slider.title">

                <!-- Overlay for better text visibility -->
                <div class="slider-overlay"></div>

                <div class="banner-layer appear-animate" data-animation-name="fadeInUpShorter">
                    <div class="container">
                        <h2 v-if="slider.title" class="slider-title text-white mb-3" style="font-size: 3rem; font-weight: bold; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">
                            {{ slider.title }}
                        </h2>
                        <p v-if="slider.description" class="slider-description text-white mb-4" style="font-size: 1.2rem; text-shadow: 1px 1px 3px rgba(0,0,0,0.5); max-width: 600px;">
                            {{ slider.description }}
                        </p>
                        <a v-if="slider.link && slider.button_text"
                           :href="slider.link"
                           class="btn btn-primary btn-lg shadow"
                           style="padding: 12px 35px; font-size: 1.1rem;">
                            {{ slider.button_text }}
                        </a>
                    </div>
                </div><!-- End .banner-layer -->
            </div><!-- End .home-slide -->
        </div>

        <!-- Fallback static sliders if no dynamic sliders -->
        <div v-else style="height: 50vh !important;" class="home-slider slide-animate owl-carousel owl-theme show-nav-hover nav-big">
            <div class="home-slide home-slide1 banner d-flex align-items-center">
                <img class="slide-bg" style="height: 50vh !important;background-color: #ecc;" src="/front/theme1/images/demoes/demo3/slider/slide1.jpg" alt="home banner">
                <div class="banner-layer appear-animate" data-animation-name="fadeInUpShorter">
                </div>
            </div>
            <div class="home-slide home-slide2 banner d-flex align-items-center">
                <img class="slide-bg" style="height: 50vh !important;background-color: #bfcec9;" src="/front/theme1/images/demoes/demo3/slider/slide2.jpg" alt="home banner">
                <div class="banner-layer appear-animate" data-animation-name="fadeInUpShorter">
                </div>
            </div>
        </div><!-- End .home-slider -->

        <!-- Categories for Desktop only -->
        <section class="container d-none d-lg-block">
            <h2 class="section-title ls-n-15 text-center pt-2 m-b-4">Shop By Category</h2>

            <!-- Dynamic categories as horizontal carousel -->
            <div class="owl-carousel owl-theme nav-image-center show-nav-hover nav-outer cats-slider appear-animate"
                 data-animation-name="fadeInUpShorter" data-animation-delay="200" data-animation-duration="1000">
                <div v-for="cat in categories" :key="cat.id" class="product-category">
                    <Link :href="route('web.category', cat.id)" class="category-card d-block">
                        <figure>
                            <img v-if="cat.image" :src="'/' + cat.image" :alt="cat.name" width="273" height="273" />
                            <img v-else :src="'/default-placeholder.png'" :alt="cat.name" width="273" height="273" />
                        </figure>
                        <div class="category-content">
                            <h3>{{ cat.name }}</h3>
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Categories for Mobile and Tablet only -->
        <section class="container d-lg-none py-4">
            <h2 class="section-title ls-n-15 text-center pt-2 mb-4">Shop By Category</h2>

            <!-- Grid layout for mobile/tablet - 2 columns -->
            <div class="row g-3 mobile-categories-grid">
                <div v-for="cat in categories" :key="cat.id" class="col-6">
                    <Link :href="route('web.category', cat.id)" class="mobile-category-card d-block">
                        <div class="mobile-category-image">
                            <img v-if="cat.image"
                                 :src="'/' + cat.image"
                                 :alt="cat.name"
                                 width="200"
                                 height="200" />
                            <img v-else
                                 :src="'/default-placeholder.png'"
                                 :alt="cat.name"
                                 width="200"
                                 height="200" />
                        </div>
                        <div class="mobile-category-content text-center mt-2">
                            <h5>{{ cat.name }}</h5>
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <section class="bg-gray banners-section text-center">
            <div class="container py-2">
                <div class="row">
                    <div class="col-sm-6 col-lg-3 appear-animate" data-animation-name="fadeInLeftShorter"
                        data-animation-delay="100" data-animation-duration="1000">
                        <div class="home-banner banner banner-sm-vw mb-2">
                            <img src="/front/theme1/images/demoes/demo3/banners/home-banner1.jpg"
                                style="background-color: #ccc;" width="419" height="629" alt="Banner" />
                            <div class="banner-layer banner-layer-bottom text-left">
                                <h3 class="m-b-2">Sunglasses Sale</h3>
                                <h4 class="m-b-3">See all and find yours</h4>
                                <a href="demo3-shop.html" class="btn  btn-dark" role="button">Shop By Glasses</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3 appear-animate" data-animation-name="fadeInLeftShorter"
                        data-animation-delay="300" data-animation-duration="1000">
                        <div class="home-banner banner banner-sm-vw mb-2">
                            <img src="/front/theme1/images/demoes/demo3/banners/home-banner2.jpg"
                                style="background-color: #ccc;" width="419" height="629" alt="Banner" />
                            <div class="banner-layer banner-layer-top ">
                                <h3 class="mb-0">Cosmetics Trends</h3>
                                <h4 class="m-b-4">Browse in all our categories</h4>
                                <a href="demo3-shop.html" class="btn  btn-dark" role="button">Shop By Glasses</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3 appear-animate" data-animation-name="fadeInLeftShorter"
                        data-animation-delay="500" data-animation-duration="1000">
                        <div class="home-banner banner banner-sm-vw mb-2">
                            <img src="/front/theme1/images/demoes/demo3/banners/home-banner3.jpg"
                                style="background-color: #444;" width="419" height="629" alt="Banner" />
                            <div class="banner-layer banner-layer-middle">
                                <h3 class="text-white m-b-1">Fashion Summer Sale</h3>
                                <h4 class="text-white m-b-4">Browse in all our categories</h4>
                                <a href="demo3-shop.html" class="btn btn-light bg-white" role="button">Shop By
                                    Fashion</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3 appear-animate" data-animation-name="fadeInLeftShorter"
                        data-animation-delay="700" data-animation-duration="1000">
                        <div class="home-banner banner banner-sm-vw mb-2">
                            <img src="/front/theme1/images/demoes/demo3/banners/home-banner4.jpg"
                                style="background-color: #ccc;" width="419" height="629" alt="Banner" />
                            <div class="banner-layer banner-layer-bottom banner-layer-boxed">
                                <h3 class="m-b-2">UP TO 70% IN ALL BAGS</h3>
                                <h4>Starting at $99</h4>
                                <a href="demo3-shop.html" class="btn  btn-dark" role="button">Shop By Bags</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="container pb-3 mb-1">
            <h2 class="section-title ls-n-15 text-center pb-2 m-b-4">Popular Products</h2>

            <div class="row py-4">
                <div class="col-6 col-sm-4 col-md-3 col-xl-2 appear-animate" data-animation-name="fadeIn"
                    data-animation-delay="300" data-animation-duration="1000">
                    <div class="product-default inner-quickview inner-icon">
                        <figure>
                            <a href="demo3-product.html">
                                <img src="/front/theme1/images/demoes/demo3/products/product-1.jpg" width="273"
                                    height="273" alt="productr" />
                            </a>
                            <div class="label-group">
                                <div class="product-label label-hot">HOT</div>
                                <div class="product-label label-sale">-20%</div>
                            </div>
                            <div class="btn-icon-group">
                                <a href="#" class="btn-icon btn-add-cart product-type-simple"><i
                                        class="icon-shopping-cart"></i></a>
                            </div>
                            <a href="ajax/product-quick-view.html" class="btn-quickview" title="Quick View">Quick
                                View</a>
                        </figure>
                        <div class="product-details">
                            <div class="category-wrap">
                                <div class="category-list">
                                    <a href="demo3-shop.html" class="product-category">category</a>
                                </div>
                            </div>
                            <h3 class="product-title">
                                <a href="demo3-product.html">Women Shoes</a>
                            </h3>
                            <div class="ratings-container">
                                <div class="product-ratings">
                                    <span class="ratings" style="width:100%"></span><!-- End .ratings -->
                                    <span class="tooltiptext tooltip-top"></span>
                                </div><!-- End .product-ratings -->
                            </div><!-- End .product-container -->
                            <div class="price-box">
                                <span class="old-price">$59.00</span>
                                <span class="product-price">$49.00</span>
                            </div><!-- End .price-box -->
                        </div><!-- End .product-details -->
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-md-3 col-xl-2 appear-animate" data-animation-name="fadeIn"
                    data-animation-delay="300" data-animation-duration="1000">
                    <div class="product-default inner-quickview inner-icon">
                        <figure>
                            <a href="demo3-product.html">
                                <img src="/front/theme1/images/demoes/demo3/products/product-2.jpg" width="273"
                                    height="273" alt="productr" />
                                <img src="/front/theme1/images/demoes/demo3/products/product-2-2.jpg" width="273"
                                    height="273" alt="productr" />
                            </a>
                            <div class="label-group">
                                <div class="product-label label-hot">HOT</div>
                                <div class="product-label label-sale">-20%</div>
                            </div>
                            <div class="btn-icon-group">
                                <a href="demo3-product.html" class="btn-icon btn-add-cart"><i
                                        class="fa fa-arrow-right"></i>
                                </a>
                            </div>
                            <a href="ajax/product-quick-view.html" class="btn-quickview" title="Quick View">Quick
                                View</a>
                        </figure>
                        <div class="product-details">
                            <div class="category-wrap">
                                <div class="category-list">
                                    <a href="demo3-shop.html" class="product-category">category</a>
                                </div>
                            </div>
                            <h3 class="product-title">
                                <a href="demo3-product.html">Porto Transparent Images</a>
                            </h3>
                            <div class="ratings-container">
                                <div class="product-ratings">
                                    <span class="ratings" style="width:100%"></span><!-- End .ratings -->
                                    <span class="tooltiptext tooltip-top"></span>
                                </div><!-- End .product-ratings -->
                            </div><!-- End .product-container -->
                            <div class="price-box">
                                <span class="old-price">$59.00</span>
                                <span class="product-price">$49.00</span>
                            </div><!-- End .price-box -->
                        </div><!-- End .product-details -->
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-md-3 col-xl-2 appear-animate" data-animation-name="fadeIn"
                    data-animation-delay="300" data-animation-duration="1000">
                    <div class="product-default inner-quickview inner-icon">
                        <figure>
                            <a href="demo3-product.html">
                                <img src="/front/theme1/images/demoes/demo3/products/product-3.jpg" width="273"
                                    height="273" alt="productr" />
                            </a>
                            <div class="btn-icon-group">
                                <a href="#" class="btn-icon btn-add-cart product-type-simple"><i
                                        class="icon-shopping-cart"></i></a>
                            </div>
                            <a href="ajax/product-quick-view.html" class="btn-quickview" title="Quick View">Quick
                                View</a>
                        </figure>
                        <div class="product-details">
                            <div class="category-wrap">
                                <div class="category-list">
                                    <a href="demo3-shop.html" class="product-category">category</a>
                                </div>
                            </div>
                            <h3 class="product-title">
                                <a href="demo3-product.html">Ideapad</a>
                            </h3>
                            <div class="ratings-container">
                                <div class="product-ratings">
                                    <span class="ratings" style="width:100%"></span><!-- End .ratings -->
                                    <span class="tooltiptext tooltip-top"></span>
                                </div><!-- End .product-ratings -->
                            </div><!-- End .product-container -->
                            <div class="price-box">
                                <span class="old-price">$59.00</span>
                                <span class="product-price">$49.00</span>
                            </div><!-- End .price-box -->
                        </div><!-- End .product-details -->
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-md-3 col-xl-2 appear-animate" data-animation-name="fadeIn"
                    data-animation-delay="300" data-animation-duration="1000">
                    <div class="product-default inner-quickview inner-icon">
                        <figure>
                            <a href="demo3-product.html">
                                <img src="/front/theme1/images/demoes/demo3/products/product-4.jpg" width="273"
                                    height="273" alt="productr" />
                                <img src="/front/theme1/images/demoes/demo3/products/product-4-2.jpg" width="273"
                                    height="273" alt="productr" />
                            </a>
                            <div class="btn-icon-group">
                                <a href="demo3-product.html" class="btn-icon btn-add-cart"><i
                                        class="fa fa-arrow-right"></i>
                                </a>
                            </div>
                            <a href="ajax/product-quick-view.html" class="btn-quickview" title="Quick View">Quick
                                View</a>
                        </figure>
                        <div class="product-details">
                            <div class="category-wrap">
                                <div class="category-list">
                                    <a href="demo3-shop.html" class="product-category">category</a>
                                </div>
                            </div>
                            <h3 class="product-title">
                                <a href="demo3-product.html">Black Men Shoes</a>
                            </h3>
                            <div class="ratings-container">
                                <div class="product-ratings">
                                    <span class="ratings" style="width:100%"></span><!-- End .ratings -->
                                    <span class="tooltiptext tooltip-top"></span>
                                </div><!-- End .product-ratings -->
                            </div><!-- End .product-container -->
                            <div class="price-box">
                                <span class="old-price">$59.00</span>
                                <span class="product-price">$49.00</span>
                            </div><!-- End .price-box -->
                        </div><!-- End .product-details -->
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-md-3 col-xl-2 appear-animate" data-animation-name="fadeIn"
                    data-animation-delay="300" data-animation-duration="1000">
                    <div class="product-default inner-quickview inner-icon">
                        <figure>
                            <a href="demo3-product.html">
                                <img src="/front/theme1/images/demoes/demo3/products/product-5.jpg" width="273"
                                    height="273" alt="productr" />
                            </a>
                            <div class="btn-icon-group">
                                <a href="#" class="btn-icon btn-add-cart product-type-simple"><i
                                        class="icon-shopping-cart"></i></a>
                            </div>
                            <a href="ajax/product-quick-view.html" class="btn-quickview" title="Quick View">Quick
                                View</a>
                        </figure>
                        <div class="product-details">
                            <div class="category-wrap">
                                <div class="category-list">
                                    <a href="demo3-shop.html" class="product-category">category</a>
                                </div>
                            </div>
                            <h3 class="product-title">
                                <a href="demo3-product.html">Watch</a>
                            </h3>
                            <div class="ratings-container">
                                <div class="product-ratings">
                                    <span class="ratings" style="width:100%"></span><!-- End .ratings -->
                                    <span class="tooltiptext tooltip-top"></span>
                                </div><!-- End .product-ratings -->
                            </div><!-- End .product-container -->
                            <div class="price-box">
                                <span class="old-price">$59.00</span>
                                <span class="product-price">$49.00</span>
                            </div><!-- End .price-box -->
                        </div><!-- End .product-details -->
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-md-3 col-xl-2 appear-animate" data-animation-name="fadeIn"
                    data-animation-delay="300" data-animation-duration="1000">
                    <div class="product-default inner-quickview inner-icon">
                        <figure>
                            <a href="demo3-product.html">
                                <img src="/front/theme1/images/demoes/demo3/products/product-6.jpg" width="273"
                                    height="273" alt="productr" />
                                <img src="/front/theme1/images/demoes/demo3/products/product-6-2.jpg" width="273"
                                    height="273" alt="productr" />
                            </a>
                            <div class="label-group">
                                <span class="product-label label-hot">HOT</span>
                            </div>
                            <div class="btn-icon-group">
                                <a href="#" class="btn-icon btn-add-cart product-type-simple"><i
                                        class="icon-shopping-cart"></i></a>
                            </div>
                            <a href="ajax/product-quick-view.html" class="btn-quickview" title="Quick View">Quick
                                View</a>
                        </figure>
                        <div class="product-details">
                            <div class="category-wrap">
                                <div class="category-list">
                                    <a href="demo3-shop.html" class="product-category">category</a>
                                </div>
                            </div>
                            <h3 class="product-title">
                                <a href="demo3-product.html">Woman Dress</a>
                            </h3>
                            <div class="ratings-container">
                                <div class="product-ratings">
                                    <span class="ratings" style="width:100%"></span><!-- End .ratings -->
                                    <span class="tooltiptext tooltip-top"></span>
                                </div><!-- End .product-ratings -->
                            </div><!-- End .product-container -->
                            <div class="price-box">
                                <span class="old-price">$59.00</span>
                                <span class="product-price">$49.00</span>
                            </div><!-- End .price-box -->
                        </div><!-- End .product-details -->
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-md-3 col-xl-2 appear-animate" data-animation-name="fadeIn"
                    data-animation-delay="300" data-animation-duration="1000">
                    <div class="product-default inner-quickview inner-icon">
                        <figure>
                            <a href="demo3-product.html">
                                <img src="/front/theme1/images/demoes/demo3/products/product-7.jpg" width="273"
                                    height="273" alt="productr" />
                            </a>
                            <div class="btn-icon-group">
                                <a href="demo3-product.html" class="btn-icon btn-add-cart"><i
                                        class="fa fa-arrow-right"></i>
                                </a>
                            </div>
                            <a href="ajax/product-quick-view.html" class="btn-quickview" title="Quick View">Quick
                                View</a>
                        </figure>
                        <div class="product-details">
                            <div class="category-wrap">
                                <div class="category-list">
                                    <a href="demo3-shop.html" class="product-category">category</a>
                                </div>
                            </div>
                            <h3 class="product-title">
                                <a href="demo3-product.html">Black Bag</a>
                            </h3>
                            <div class="ratings-container">
                                <div class="product-ratings">
                                    <span class="ratings" style="width:100%"></span><!-- End .ratings -->
                                    <span class="tooltiptext tooltip-top"></span>
                                </div><!-- End .product-ratings -->
                            </div><!-- End .product-container -->
                            <div class="price-box">
                                <span class="old-price">$59.00</span>
                                <span class="product-price">$49.00</span>
                            </div><!-- End .price-box -->
                        </div><!-- End .product-details -->
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-md-3 col-xl-2 appear-animate" data-animation-name="fadeIn"
                    data-animation-delay="300" data-animation-duration="1000">
                    <div class="product-default inner-quickview inner-icon">
                        <figure>
                            <a href="demo3-product.html">
                                <img src="/front/theme1/images/demoes/demo3/products/product-8.jpg" width="273"
                                    height="273" alt="productr" />
                                <img src="/front/theme1/images/demoes/demo3/products/product-8-2.jpg" width="273"
                                    height="273" alt="productr" />
                            </a>
                            <div class="btn-icon-group">
                                <a href="#" class="btn-icon btn-add-cart product-type-simple"><i
                                        class="icon-shopping-cart"></i></a>
                            </div>
                            <a href="ajax/product-quick-view.html" class="btn-quickview" title="Quick View">Quick
                                View</a>
                        </figure>
                        <div class="product-details">
                            <div class="category-wrap">
                                <div class="category-list">
                                    <a href="demo3-shop.html" class="product-category">category</a>
                                </div>
                            </div>
                            <h3 class="product-title">
                                <a href="demo3-product.html">Porto Both Sticky Info</a>
                            </h3>
                            <div class="ratings-container">
                                <div class="product-ratings">
                                    <span class="ratings" style="width:100%"></span><!-- End .ratings -->
                                    <span class="tooltiptext tooltip-top"></span>
                                </div><!-- End .product-ratings -->
                            </div><!-- End .product-container -->
                            <div class="price-box">
                                <span class="old-price">$59.00</span>
                                <span class="product-price">$49.00</span>
                            </div><!-- End .price-box -->
                        </div><!-- End .product-details -->
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-md-3 col-xl-2 appear-animate" data-animation-name="fadeIn"
                    data-animation-delay="300" data-animation-duration="1000">
                    <div class="product-default inner-quickview inner-icon">
                        <figure>
                            <a href="demo3-product.html">
                                <img src="/front/theme1/images/demoes/demo3/products/product-9.jpg" width="273"
                                    height="273" alt="productr" />
                                <img src="/front/theme1/images/demoes/demo3/products/product-9-2.jpg" width="273"
                                    height="273" alt="productr" />
                            </a>
                            <div class="btn-icon-group">
                                <a href="demo3-product.html" class="btn-icon btn-add-cart"><i
                                        class="fa fa-arrow-right"></i>
                                </a>
                            </div>
                            <a href="ajax/product-quick-view.html" class="btn-quickview" title="Quick View">Quick
                                View</a>
                        </figure>
                        <div class="product-details">
                            <div class="category-wrap">
                                <div class="category-list">
                                    <a href="demo3-shop.html" class="product-category">category</a>
                                </div>
                            </div>
                            <h3 class="product-title">
                                <a href="demo3-product.html">Glasses</a>
                            </h3>
                            <div class="ratings-container">
                                <div class="product-ratings">
                                    <span class="ratings" style="width:100%"></span><!-- End .ratings -->
                                    <span class="tooltiptext tooltip-top"></span>
                                </div><!-- End .product-ratings -->
                            </div><!-- End .product-container -->
                            <div class="price-box">
                                <span class="old-price">$59.00</span>
                                <span class="product-price">$49.00</span>
                            </div><!-- End .price-box -->
                        </div><!-- End .product-details -->
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-md-3 col-xl-2 appear-animate" data-animation-name="fadeIn"
                    data-animation-delay="300" data-animation-duration="1000">
                    <div class="product-default inner-quickview inner-icon">
                        <figure>
                            <a href="demo3-product.html">
                                <img src="/front/theme1/images/demoes/demo3/products/product-10.jpg" width="273"
                                    height="273" alt="productr" />
                            </a>
                            <div class="btn-icon-group">
                                <a href="#" class="btn-icon btn-add-cart product-type-simple"><i
                                        class="icon-shopping-cart"></i></a>
                            </div>
                            <a href="ajax/product-quick-view.html" class="btn-quickview" title="Quick View">Quick
                                View</a>
                        </figure>
                        <div class="product-details">
                            <div class="category-wrap">
                                <div class="category-list">
                                    <a href="demo3-shop.html" class="product-category">category</a>
                                </div>
                            </div>
                            <h3 class="product-title">
                                <a href="demo3-product.html">Black Men Coat</a>
                            </h3>
                            <div class="ratings-container">
                                <div class="product-ratings">
                                    <span class="ratings" style="width:100%"></span><!-- End .ratings -->
                                    <span class="tooltiptext tooltip-top"></span>
                                </div><!-- End .product-ratings -->
                            </div><!-- End .product-container -->
                            <div class="price-box">
                                <span class="old-price">$59.00</span>
                                <span class="product-price">$49.00</span>
                            </div><!-- End .price-box -->
                        </div><!-- End .product-details -->
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-md-3 col-xl-2 appear-animate" data-animation-name="fadeIn"
                    data-animation-delay="300" data-animation-duration="1000">
                    <div class="product-default inner-quickview inner-icon">
                        <figure>
                            <a href="demo3-product.html">
                                <img src="/front/theme1/images/demoes/demo3/products/product-11.jpg" width="273"
                                    height="273" alt="productr" />
                            </a>
                            <div class="btn-icon-group">
                                <a href="#" class="btn-icon btn-add-cart product-type-simple"><i
                                        class="icon-shopping-cart"></i></a>
                            </div>
                            <a href="ajax/product-quick-view.html" class="btn-quickview" title="Quick View">Quick
                                View</a>
                        </figure>
                        <div class="product-details">
                            <div class="category-wrap">
                                <div class="category-list">
                                    <a href="demo3-shop.html" class="product-category">category</a>
                                </div>
                            </div>
                            <h3 class="product-title">
                                <a href="demo3-product.html">Girl High Shoes</a>
                            </h3>
                            <div class="ratings-container">
                                <div class="product-ratings">
                                    <span class="ratings" style="width:100%"></span><!-- End .ratings -->
                                    <span class="tooltiptext tooltip-top"></span>
                                </div><!-- End .product-ratings -->
                            </div><!-- End .product-container -->
                            <div class="price-box">
                                <span class="old-price">$59.00</span>
                                <span class="product-price">$49.00</span>
                            </div><!-- End .price-box -->
                        </div><!-- End .product-details -->
                    </div>
                </div>
                <div class="col-6 col-sm-4 col-md-3 col-xl-2 appear-animate" data-animation-name="fadeIn"
                    data-animation-delay="300" data-animation-duration="1000">
                    <div class="product-default inner-quickview inner-icon">
                        <figure>
                            <a href="demo3-product.html">
                                <img src="/front/theme1/images/demoes/demo3/products/product-12.jpg" width="273"
                                    height="273" alt="productr" />
                                <img src="/front/theme1/images/demoes/demo3/products/product-12-2.jpg" width="273"
                                    height="273" alt="productr" />
                            </a>
                            <div class="btn-icon-group">
                                <a href="demo3-product.html" class="btn-icon btn-add-cart"><i
                                        class="fa fa-arrow-right"></i>
                                </a>
                            </div>
                            <a href="ajax/product-quick-view.html" class="btn-quickview" title="Quick View">Quick
                                View</a>
                        </figure>
                        <div class="product-details">
                            <div class="category-wrap">
                                <div class="category-list">
                                    <a href="demo3-shop.html" class="product-category">category</a>
                                </div>
                            </div>
                            <h3 class="product-title">
                                <a href="demo3-product.html">Girl Low Shoes</a>
                            </h3>
                            <div class="ratings-container">
                                <div class="product-ratings">
                                    <span class="ratings" style="width:100%"></span><!-- End .ratings -->
                                    <span class="tooltiptext tooltip-top"></span>
                                </div><!-- End .product-ratings -->
                            </div><!-- End .product-container -->
                            <div class="price-box">
                                <span class="old-price">$59.00</span>
                                <span class="product-price">$49.00</span>
                            </div><!-- End .price-box -->
                        </div><!-- End .product-details -->
                    </div>
                </div>
            </div>

            <hr class="mt-3 mb-6">

            <div class="row feature-boxes-container pt-2">
                <div class="col-sm-6 col-lg-3 appear-animate" data-animation-name="fadeInRightShorter"
                    data-animation-delay="300" data-animation-duration="1000">
                    <div class="feature-box feature-box-simple text-center">
                        <i class="sicon-earphones-alt"></i>

                        <div class="feature-box-content p-0">
                            <h3 class="text-uppercase">Customer Support</h3>
                            <h5>Need Assistence?</h5>

                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis nec vestibulum magna,
                                et dapibus lacus. Lorem ipsum dolor sit amet.</p>
                        </div><!-- End .feature-box-content -->
                    </div><!-- End .feature-box -->
                </div><!-- End .col-lg-3 -->

                <div class="col-sm-6 col-lg-3 appear-animate" data-animation-name="fadeInRightShorter"
                    data-animation-delay="100" data-animation-duration="1000">
                    <div class="feature-box feature-box-simple text-center">
                        <i class="sicon-credit-card"></i>

                        <div class="feature-box-content p-0">
                            <h3 class="text-uppercase">Secured Payment</h3>
                            <h5>Safe & Fast</h5>

                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis nec vestibulum magna,
                                et dapibus lacus. Lorem ipsum dolor sit amet.</p>
                        </div><!-- End .feature-box-content -->
                    </div><!-- End .feature-box -->
                </div><!-- End .col-lg-3 -->

                <div class="col-sm-6 col-lg-3 appear-animate" data-animation-name="fadeInLeftShorter"
                    data-animation-delay="100" data-animation-duration="1000">
                    <div class="feature-box feature-box-simple text-center">
                        <i class="sicon-action-undo"></i>

                        <div class="feature-box-content p-0">
                            <h3 class="text-uppercase">Free Returns</h3>
                            <h5>Easy & Free</h5>

                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis nec vestibulum magna,
                                et dapibus lacus. Lorem ipsum dolor sit amet.</p>
                        </div><!-- End .feature-box-content -->
                    </div><!-- End .feature-box -->
                </div><!-- End .col-lg-3 -->

                <div class="col-sm-6 col-lg-3 appear-animate" data-animation-name="fadeInLeftShorter"
                    data-animation-delay="300" data-animation-duration="1000">
                    <div class="feature-box feature-box-simple text-center">
                        <i class="icon-shipping"></i>

                        <div class="feature-box-content p-0">
                            <h3 class="text-uppercase">Free Shipping</h3>
                            <h5>Orders Over $99</h5>

                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis nec vestibulum magna,
                                et dapibus lacus. Lorem ipsum dolor sit amet.</p>
                        </div><!-- End .feature-box-content -->
                    </div><!-- End .feature-box -->
                </div><!-- End .col-lg-3 -->
            </div><!-- End .row .feature-boxes-container-->
        </section>
    </AppLayout>
</template>

<style scoped>
/* Slider overlay for better text visibility */
.slider-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to right, rgba(0,0,0,0.5) 0%, rgba(0,0,0,0.2) 100%);
    z-index: 1;
}

.banner-layer {
    position: relative;
    z-index: 2;
}

/* Mobile Categories Grid */
.mobile-categories-grid {
    margin: 0 -8px;
}

.mobile-category-card {
    text-decoration: none;
    display: block;
    padding: 10px;
    transition: transform 0.3s ease;
}

.mobile-category-card:hover {
    transform: scale(1.05);
    text-decoration: none;
}

.mobile-category-image {
    width: 100%;
    aspect-ratio: 1 / 1;
    overflow: hidden;
    border-radius: 12px;
    background-color: #f5f5f5;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.mobile-category-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.mobile-category-content h5 {
    font-size: 2.3rem;
    font-weight: 600;
    color: #333;
    margin: 0;
    line-height: 1.3;
}

/* Responsive text sizes */
@media (max-width: 768px) {
    .slider-title {
        font-size: 2rem !important;
    }

    .slider-description {
        font-size: 1rem !important;
    }
}

@media (max-width: 576px) {
    .slider-title {
        font-size: 1.5rem !important;
    }

    .slider-description {
        font-size: 0.9rem !important;
    }

    .mobile-category-card {
        padding: 6px;
    }

    .mobile-category-content h5 {
        font-size: 2.3rem;
    }
}
</style>
