
<script setup>
const asset = (path) => {
    return '/' + path
}
import Header from './Header.vue'
import Footer from './Footer.vue'
import MobileMenu from './MobileMenu.vue'
import StickyNavbar from './StickyNavbar.vue'
import NewsletterPopup from './NewsletterPopup.vue'

// Dynamically load legacy theme script after Vue has mounted to avoid appendChild on undefined
import { onMounted } from 'vue'

onMounted(() => {
    // Ensure the page-wrapper element is present
    const wrapper = document.querySelector('.page-wrapper')
    if (!wrapper) {
        return
    }
        // Avoid duplicate script injection
        if (document.querySelector('script[data-legacy="main"]')) {
                return
        }
        const script = document.createElement('script')
        script.src = '/front/theme1/js/main.min.js'
        script.async = true
        script.setAttribute('data-legacy','main')
        //console.log('العنصر قبل appendChild:', wrapper);
        if (wrapper) {
            wrapper.appendChild(script);
        } else {
            console.warn('عنصر .page-wrapper غير موجود، لم يتم إضافة السكريبت!');
        }
})
</script>
<template>
    <div class="page-wrapper">
        <Header />
        <main class="main">
            <slot />
        </main>
        <Footer />
        <MobileMenu />
        <StickyNavbar />
        <NewsletterPopup />
        <a id="scroll-top" href="#top" title="Top" role="button">
            <i class="icon-angle-up"></i>
        </a>
    </div>
</template>
