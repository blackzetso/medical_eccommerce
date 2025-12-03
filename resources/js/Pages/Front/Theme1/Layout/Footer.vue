<template>
    <footer class="footer">
        <div class="container">
            <div class="footer-top top-border d-flex align-items-center justify-content-between flex-wrap">
                <div class="footer-left widget-newsletter d-md-flex align-items-center">
                    <div class="widget-newsletter-info">
                        <h5 class="widget-newsletter-title text-uppercase m-b-1">اشترك في النشرة البريدية</h5>
                        <p class="widget-newsletter-content mb-0">احصل على أحدث المعلومات حول الفعاليات والعروض والخصومات.</p>
                    </div>
                    <form action="#">
                        <div class="footer-submit-wrapper d-flex">
                            <input type="email" class="form-control" placeholder="البريد الإلكتروني..." size="40"
                                required>
                            <button type="submit" class="btn btn-dark btn-sm">اشترك</button>
                        </div>
                    </form>
                </div>
                <div class="footer-right">
                    <div class="social-icons">
                        <a :href="footerSettings.facebook_url" class="social-icon social-facebook icon-facebook" target="_blank"></a>
                        <a :href="footerSettings.twitter_url" class="social-icon social-twitter icon-twitter" target="_blank"></a>
                        <a :href="footerSettings.instagram_url" class="social-icon social-instagram icon-instagram" target="_blank"></a>
                    </div><!-- End .social-icons -->
                </div>
            </div>

            <div class="footer-middle">
                <div class="row">
                    <div class="col-lg-6 col-xl-4">
                        <div class="widget">
                            <h4 class="widget-title">معلومات التواصل</h4>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="contact-widget">
                                        <h4 class="widget-title">العنوان:</h4>
                                        <a href="#" v-if="footerSettings.address">{{ footerSettings.address }}</a>
                                        <span v-else class="text-muted">-</span>
                                    </div>
                                </div>

                                <div class="col-sm-6 pl-xl-0">
                                    <div class="contact-widget">
                                        <h4 class="widget-title">الهاتف:</h4>
                                        <a :href="`tel:${footerSettings.phone}`" v-if="footerSettings.phone">{{ footerSettings.phone }}</a>
                                        <span v-else class="text-muted">-</span>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="contact-widget email">
                                        <h4 class="widget-title">البريد الإلكتروني:</h4>
                                        <a :href="`mailto:${footerSettings.email}`" v-if="footerSettings.email">{{ footerSettings.email }}</a>
                                        <span v-else class="text-muted">-</span>
                                    </div>
                                </div>

                                <div class="col-sm-6 pl-xl-0">
                                    <div class="contact-widget">
                                        <h4 class="widget-title">أيام وساعات العمل:</h4>
                                        <a href="#" v-if="footerSettings.working_hours">{{ footerSettings.working_hours }}</a>
                                        <span v-else class="text-muted">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3 col-xl-4">
                        <div class="widget">
                            <h4 class="widget-title">حسابي</h4>
                            <div class="links link-parts row">
                                <ul class="link-part col-xl-4 mb-0">
                                    <li><a href="demo3-about.html">من نحن</a></li>
                                    <li><Link :href="route('web.contact')">اتصل بنا</Link></li>
                                    <li><a href="dashboard.html">حسابي</a></li>
                                </ul> 
                            </div>
                        </div><!-- End .widget -->
                    </div>
                    <div class="col-sm-6 col-lg-3 col-xl-4">
                        <div class="widget">
                            <h4 class="widget-title text-center">روابط سريعة</h4>
                            <div class="links link-parts text-center row">
                                <ul class="link-part col-xl-6 mb-0">
                                    <li><Link :href="route('web.about')">من نحن</Link></li>
                                    <li><Link :href="route('web.privacy')">سياسة الخصوصية</Link></li>
                                </ul>
                                <ul class="link-part col-xl-6 pl-xl-2">
                                    <li><Link :href="route('web.terms')">شروط الاستخدام</Link></li>
                                    <li><Link :href="route('web.refund')">سياسة الاسترداد</Link></li>
                                </ul>
                            </div>
                        </div><!-- End .widget -->
                    </div>
                </div>
            </div>

            <div class="footer-bottom d-sm-flex align-items-center">
                <div class="footer-left">
                    <span class="footer-copyright">
                        <a href="https://semicolon-solution.net" target="_blank" rel="noopener noreferrer">
                            تم التصميم والبرمجه بواسطة سيمي كولن للحلول الرقمية
                        </a>
                    </span>
                </div>
            </div>
        </div>
    </footer>
</template>
<script setup>
import { usePage, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { computed } from 'vue'

const page = usePage()
const asset = (path) => {
    return '/' + path
}

// Get footer settings from shared props
const footerSettings = computed(() => {
    return page.props.footerSettings || {
        address: '',
        phone: '',
        email: '',
        working_hours: '',
        footer_features: [],
        facebook_url: '#',
        twitter_url: '#',
        instagram_url: '#'
    }
})

// Get current year
const currentYear = computed(() => {
    return new Date().getFullYear()
})
</script>
