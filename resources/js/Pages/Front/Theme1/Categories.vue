<script setup>
import AppLayout from '@/Pages/Front/Theme1/Layout/App.vue'
import { Head, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
    sliders: Array,
    childrens: Object,
    parent: Object,
})
</script>

<template>
    <Head title="الأقسام" />
    <AppLayout>
        <main class="main">
            <nav aria-label="breadcrumb" class="breadcrumb-nav">
                <div class="container">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="demo3.html">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="#">قسم رئسسي</a></li>
                        <li class="breadcrumb-item active" aria-current="page">قسم فرعى</li>
                    </ol>
                </div><!-- End .container -->
            </nav>

            <div class="container mb-3">
                <div class="row">
                    <div class="col-lg-12 main-content">
                        <div class="row">
                            <template v-if="childrens?.data && childrens.data.length">
                                <template v-for="cat in childrens.data" :key="cat.id">
                                    <div class="col-6 col-sm-4 col-md-3 col-xl-5col">
                                        <div class="product-default inner-icon">
                                            <figure>
                                                <Link :href="route('web.category', cat.id)">
                                                    <img v-if="cat.image_url" :src="cat.image_url" :alt="cat.name" width="273" height="273" />
                                                    <img v-else :src="'/default-placeholder.png'" :alt="cat.name" width="273" height="273" />
                                                </Link>
                                            </figure>
                                            <div class="product-details">
                                                <h3 class="product-title">
                                                    <Link :href="route('web.category', cat.id)">{{ cat.name }}</Link>
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </template>
                            <template v-else>
                                <div class="col-12 text-center py-5">
                                    <h4>لا يوجد أقسام حتى الآن</h4>
                                </div>
                            </template>
                        </div>

                        <nav class="toolbox toolbox-pagination">
                            <ul class="toolbox-item">
                                <li v-if="childrens?.prev_page_url" class="page-item">
                                    <Link class="page-link page-link-btn" :href="childrens.prev_page_url"><i class="icon-angle-left"></i></Link>
                                </li>
                                <li v-for="page in childrens?.last_page || 0" :key="page" :class="['page-item', { active: page === (childrens?.current_page || 1) }]">
                                    <Link class="page-link" :href="(childrens?.path || '') + '?page=' + page">{{ page }}</Link>
                                </li>
                                <li v-if="childrens?.next_page_url" class="page-item">
                                    <Link class="page-link page-link-btn" :href="childrens.next_page_url"><i class="icon-angle-right"></i></Link>
                                </li>
                            </ul>
                        </nav>
                    </div><!-- End .main-content -->
                </div><!-- End .row -->
            </div>
        </main>
    </AppLayout>
</template>
