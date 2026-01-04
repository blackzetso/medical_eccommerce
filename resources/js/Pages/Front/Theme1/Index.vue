<script setup>
import { ref } from 'vue'
import AppLayout from '@/Pages/Front/Theme1/Layout/App.vue'
import { Head, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { useTranslations } from '@/composables/translations'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Autoplay, EffectFade } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/effect-fade'

const { t } = useTranslations()

const props = defineProps({
    sliders: Array,
    categories: Array
})

// Swiper instance
const swiperInstance = ref(null)

// دالة لضمان تنسيق مسار الصورة بشكل صحيح
const formatImagePath = (imagePath) => {
    if (!imagePath) return '';
    
    // إذا كان المسار يبدأ بـ http أو https، إرجاعه كما هو
    if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
        return imagePath;
    }
    
    // إذا كان المسار يبدأ بـ /storage/، إرجاعه كما هو (للدعم العكسي)
    if (imagePath.startsWith('/storage/')) {
        return imagePath;
    }
    
    // إذا كان المسار يبدأ بـ /uploads/، إرجاعه كما هو
    if (imagePath.startsWith('/uploads/')) {
        return imagePath;
    }
    
    // إذا كان المسار لا يبدأ بـ /، إضافة /
    if (!imagePath.startsWith('/')) {
        return '/' + imagePath;
    }
    
    return imagePath;
};

// Swiper modules (without Navigation to avoid SVG)
const modules = [Autoplay, EffectFade]

// Swiper onSwiper callback
const onSwiper = (swiper) => {
    swiperInstance.value = swiper
}

// Navigation functions
const goToPrev = () => {
    if (swiperInstance.value) {
        swiperInstance.value.slidePrev()
    }
}

const goToNext = () => {
    if (swiperInstance.value) {
        swiperInstance.value.slideNext()
    }
}
</script>

<template>
    <Head :title="t('home')" />
    <AppLayout>
        <!-- Dynamic Sliders with Swiper -->
        <div v-if="sliders && sliders.length > 0" class="home-slider-wrapper">
            <swiper
                :modules="modules"
                :slides-per-view="1"
                :loop="true"
                :autoplay="{
                    delay: 5000,
                    disableOnInteraction: false,
                }"
                :effect="'fade'"
                :fade-effect="{ crossFade: true }"
                @swiper="onSwiper"
                class="home-slider-swiper"
            >
                <swiper-slide v-for="slider in sliders" :key="slider.id" class="home-slide banner d-flex align-items-center position-relative">
                    <a v-if="slider.link" :href="slider.link" class="slider-link" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 3; cursor: pointer;"></a>
                    <img 
                        class="slide-bg" 
                        :src="formatImagePath(slider.image)" 
                        :alt="slider.description || 'Slider'" 
                        @error="console.error('Failed to load slider image:', slider.image)"
                    >
                </swiper-slide>
            </swiper>
            
            <!-- Navigation buttons -->
            <div @click="goToPrev" class="home-slider-nav-btn home-slider-nav-prev">
                <i class="icon-left-open-big"></i>
            </div>
            <div @click="goToNext" class="home-slider-nav-btn home-slider-nav-next">
                <i class="icon-right-open-big"></i>
            </div>
        </div>

        <!-- Fallback static sliders if no dynamic sliders -->
        <div v-else class="home-slider-wrapper">
            <swiper
                :modules="modules"
                :slides-per-view="1"
                :loop="true"
                :autoplay="{
                    delay: 5000,
                    disableOnInteraction: false,
                }"
                :effect="'fade'"
                :fade-effect="{ crossFade: true }"
                class="home-slider-swiper"
            >
                <swiper-slide class="home-slide home-slide1 banner d-flex align-items-center">
                    <img class="slide-bg" style="background-color: #ecc;" src="/front/theme1/images/demoes/demo3/slider/slide1.jpg" alt="home banner">
                    <div class="banner-layer appear-animate" data-animation-name="fadeInUpShorter">
                    </div>
                </swiper-slide>
                <swiper-slide class="home-slide home-slide2 banner d-flex align-items-center">
                    <img class="slide-bg" style="background-color: #bfcec9;" src="/front/theme1/images/demoes/demo3/slider/slide2.jpg" alt="home banner">
                    <div class="banner-layer appear-animate" data-animation-name="fadeInUpShorter">
                    </div>
                </swiper-slide>
            </swiper>
        </div>

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
/* Swiper Slider Styles */
.home-slider-wrapper {
    position: relative;
    width: 100%;
    height: 50vh; /* Default height for mobile */
}

.home-slider-swiper {
    width: 100%;
    height: 100%;
}

.home-slider-swiper :deep(.swiper-slide) {
    position: relative;
    height: 100%;
}

.home-slider-swiper :deep(.slide-bg) {
    width: 100%;
    height: 100%;
    object-fit: cover;
    position: absolute;
    top: 0;
    left: 0;
}

/* Desktop: Increase height */
@media (min-width: 992px) {
    .home-slider-wrapper {
        height: 70vh; /* Larger height for desktop */
    }
}

/* Slider link overlay */
.slider-link {
    display: block;
    text-decoration: none;
}

/* No need to hide anything - Navigation module is removed */

/* Custom Navigation Buttons */
.home-slider-nav-btn {
    width: 50px !important;
    height: 50px !important;
    background: rgba(0, 0, 0, 0.5) !important;
    border-radius: 50% !important;
    transition: all 0.3s ease !important;
    color: white !important;
    z-index: 10 !important;
    margin-top: 0 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    cursor: pointer !important;
    position: absolute !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    pointer-events: auto !important;
}

.home-slider-nav-btn:hover {
    background: rgba(0, 0, 0, 0.7) !important;
}

.home-slider-nav-btn i {
    font-size: 24px;
    color: white;
    line-height: 1;
}

.home-slider-nav-prev {
    left: 20px !important;
    right: auto !important;
}

.home-slider-nav-next {
    right: 20px !important;
    left: auto !important;
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
