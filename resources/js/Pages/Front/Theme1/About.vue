<script setup>
import AppLayout from '@/Pages/Front/Theme1/Layout/App.vue'
import { Head, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
    content: String
})
</script>

<template>
    <Head title="من نحن" />
    <AppLayout>
        <nav aria-label="breadcrumb" class="breadcrumb-nav">
            <div class="container">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a :href="route('/')">الرئيسية</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">من نحن</li>
                </ol>
            </div>
        </nav>

        <div class="page-content">
            <div class="container">
                <h2 class="page-title mb-4">من نحن</h2>
                <div class="content-wrapper" v-html="content || '<p>المحتوى قيد التحديث...</p>'"></div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.page-content {
    padding: 3rem 0;
}

.page-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #333;
}

.content-wrapper {
    background: #fff;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    line-height: 1.8;
    color: #555;
}

.content-wrapper :deep(p) {
    margin-bottom: 1rem;
}

.content-wrapper :deep(h1),
.content-wrapper :deep(h2),
.content-wrapper :deep(h3) {
    margin-top: 2rem;
    margin-bottom: 1rem;
    color: #333;
}

.content-wrapper :deep(ul),
.content-wrapper :deep(ol) {
    margin-bottom: 1rem;
    padding-right: 2rem;
}

@media (max-width: 576px) {
    .page-title {
        font-size: 2rem;
    }
    
    .content-wrapper {
        padding: 1.5rem;
    }
}
</style>

