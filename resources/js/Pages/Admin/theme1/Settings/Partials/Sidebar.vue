<script setup>
    import { Link } from '@inertiajs/vue3'
    import { usePage } from '@inertiajs/vue3'
    import { route } from 'ziggy-js'

    const page = usePage()

    import { useTranslations } from '@/composables/translations'
    const { t } = useTranslations()
</script>
<template>
        <ul class="nav nav-pills nav-tabs-bg-dark flex-column">
            <li class="nav-item">
                <Link class="nav-link" :href="route('admin.settings.index')" :class="{ active: route().current('admin.settings.index') }">
                    <i class="fas fa-cog fa-fw me-2"></i>{{ t('general_settings') }}
                </Link>
            </li>
            <li class="nav-item">
                <Link class="nav-link" :href="route('admin.settings.appearance')" :class="{ active: route().current('admin.settings.appearance') }">
                    <i class="fas fa-palette fa-fw me-2"></i>{{ t('appearance_settings') }}
                </Link>
            </li>
            <li class="nav-item">
                <Link class="nav-link" :href="route('admin.settings.email')" :class="{ active: route().current('admin.settings.email') }">
                    <i class="fas fa-envelope-open-text fa-fw me-2"></i>{{ t('email_settings') }}
                </Link>
            </li>
            <li class="nav-item">
                <Link class="nav-link" :href="route('admin.settings.payment')" :class="{ active: route().current('admin.settings.payment') }">
                    <i class="fas fa-credit-card fa-fw me-2"></i>{{ t('payment_settings') }}
                </Link>
            </li>
            <li class="nav-item">
                <Link class="nav-link" :href="route('admin.settings.shipping')" :class="{ active: route().current('admin.settings.shipping') }">
                    <i class="fas fa-truck fa-fw me-2"></i>{{ t('shipping_settings') }}
                </Link>
            </li>
            <li class="nav-item">
                <Link class="nav-link" :href="route('admin.settings.notification')" :class="{ active: route().current('admin.settings.notification') }">
                    <i class="fas fa-bell fa-fw me-2"></i>{{ t('notification_settings') }}
                </Link>
            </li>
            <li class="nav-item">
                <Link class="nav-link" :href="route('admin.settings.about')" :class="{ active: route().current('admin.settings.about') }">
                    <i class="fas fa-info-circle fa-fw me-2"></i>{{ t('about_us') }}
                </Link>
            </li>
            <li class="nav-item">
                <Link class="nav-link" :href="route('admin.settings.privacy')" :class="{ active: route().current('admin.settings.privacy') }">
                    <i class="fas fa-shield-alt fa-fw me-2"></i>{{ t('privacy_policy') }}
                </Link>
            </li>
            <li class="nav-item">
                <Link class="nav-link" :href="route('admin.settings.terms')" :class="{ active: route().current('admin.settings.terms') }">
                    <i class="fas fa-file-contract fa-fw me-2"></i>{{ t('terms_of_service') }}
                </Link>
            </li>
            <li class="nav-item">
                <Link class="nav-link" :href="route('admin.settings.refund')" :class="{ active: route().current('admin.settings.refund') }">
                    <i class="fas fa-undo fa-fw me-2"></i>{{ t('refund_policy') }}
                </Link>
            </li>
            <li class="nav-item">
                <Link class="nav-link mb-0" :href="route('admin.language.index')" :class="{ active: route().current('admin.language.index') }">
                    <i class="fas fa-globe fa-fw me-2"></i>{{ t('languages') }}
                </Link>
            </li>
        </ul>
</template>
