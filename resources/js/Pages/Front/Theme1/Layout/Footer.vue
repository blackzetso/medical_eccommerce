<template>
    <footer class="footer">
        <div class="container">
            <div class="footer-top top-border d-flex align-items-center justify-content-between flex-wrap">
                <div class="footer-left widget-newsletter d-md-flex align-items-center">
                    <div class="widget-newsletter-info">
                        <h5 class="widget-newsletter-title text-uppercase m-b-1">{{ t('subscribe_to_newsletter') }}</h5>
                        <p class="widget-newsletter-content mb-0">{{ t('get_latest_info') }}</p>
                    </div>
                    <form action="#" style="margin-right: 2rem; margin-left: 2rem;">
                        <div class="footer-submit-wrapper d-flex">
                            <input type="email" class="form-control" :placeholder="t('email') + '...'" size="40"
                                required>
                            <button type="submit" class="btn btn-dark btn-sm">{{ t('subscribe') }}</button>
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
                            <h4 class="widget-title">{{ t('contact_info') }}</h4>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="contact-widget">
                                        <h4 class="widget-title">{{ t('address') }}:</h4>
                                        <a href="#" v-if="footerSettings.address">{{ footerSettings.address }}</a>
                                        <span v-else class="text-muted">-</span>
                                    </div>
                                </div>

                                <div class="col-sm-6 pl-xl-0">
                                    <div class="contact-widget">
                                        <h4 class="widget-title">{{ t('phone') }}:</h4>
                                        <a :href="`tel:${footerSettings.phone}`" v-if="footerSettings.phone">{{ footerSettings.phone }}</a>
                                        <span v-else class="text-muted">-</span>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="contact-widget email">
                                        <h4 class="widget-title">{{ t('email') }}:</h4>
                                        <a :href="`mailto:${footerSettings.email}`" v-if="footerSettings.email">{{ footerSettings.email }}</a>
                                        <span v-else class="text-muted">-</span>
                                    </div>
                                </div>

                                <div class="col-sm-6 pl-xl-0">
                                    <div class="contact-widget">
                                        <h4 class="widget-title">{{ t('working_hours') }}:</h4>
                                        <a href="#" v-if="footerSettings.working_hours">{{ footerSettings.working_hours }}</a>
                                        <span v-else class="text-muted">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3 col-xl-4">
                        <div class="widget">
                            <h4 class="widget-title">{{ t('my_account') }}</h4>
                            <div class="links link-parts row">
                                <ul class="link-part col-xl-4 mb-0">
                                    <li><Link :href="route('web.about')">{{ t('about_us') }}</Link></li>
                                    <li><Link :href="route('web.contact')">{{ t('contact_us') }}</Link></li>
                                    <li><Link :href="route('client.dashboard')">{{ t('my_account') }}</Link></li>
                                </ul> 
                            </div>
                        </div><!-- End .widget -->
                    </div>
                    <div class="col-sm-6 col-lg-3 col-xl-4">
                        <div class="widget">
                            <h4 class="widget-title text-center">{{ t('quick_links') }}</h4>
                            <div class="links link-parts text-center row">
                                <ul class="link-part col-xl-6 mb-0">
                                    <li><Link :href="route('web.about')">{{ t('about_us') }}</Link></li>
                                    <li><Link :href="route('web.privacy')">{{ t('privacy_policy') }}</Link></li>
                                </ul>
                                <ul class="link-part col-xl-6 pl-xl-2">
                                    <li><Link :href="route('web.terms')">{{ t('terms_of_use') }}</Link></li>
                                    <li><Link :href="route('web.refund')">{{ t('refund_policy') }}</Link></li>
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
import { useTranslations } from '@/composables/translations'

const { t } = useTranslations()
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
