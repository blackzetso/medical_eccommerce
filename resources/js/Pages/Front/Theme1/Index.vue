<script setup>
import AppLayout from '@/Pages/Front/Theme1/Layout/App.vue'
import { Head, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { useTranslations } from '@/composables/translations'

const { t } = useTranslations()

const props = defineProps({
    sliders: Array,
    categories: Array
})
</script>

<template>
    <Head :title="t('home')" />
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
            <h2 class="section-title ls-n-15 text-center pt-2 mb-4">{{ t('categories') }}</h2>

            <!-- Dynamic categories as horizontal carousel -->
            <div class="owl-carousel owl-theme nav-image-center show-nav-hover nav-outer cats-slider appear-animate"
                 data-animation-name="fadeInUpShorter" data-animation-delay="200" data-animation-duration="1000">
                <div v-for="cat in categories" :key="cat.id" class="product-category">
                    <Link :href="route('web.category', cat.id)" class="category-card d-block">
                        <figure>
                            <img v-if="cat.image" :src="cat.image" :alt="cat.name" width="273" height="273" />
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
            <h2 class="section-title ls-n-15 text-center pt-2 mb-4">{{ t('categories') }}</h2>

            <!-- Grid layout for mobile/tablet - 2 columns -->
            <div class="row g-3 mobile-categories-grid">
                <div v-for="cat in categories" :key="cat.id" class="col-6">
                    <Link :href="route('web.category', cat.id)" class="mobile-category-card d-block">
                        <div class="mobile-category-image">
                            <img v-if="cat.image"
                                 :src="cat.image"
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
