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
                <Link class="nav-link"  :href="route('admin.settings.index')" :class="{ active: route().current('admin.settings.index') }"><i class="fas fa-cog fa-fw me-2"></i>
                    {{ t('general_settings') }}
                </Link>
            </li>
            <li class="nav-item"> <a class="nav-link"  href="#tab-1"><i class="fas fa-globe fa-fw me-2"></i>{{ t('apperance_settings') }}</a> </li>
            <li class="nav-item">
                <Link class="nav-link" :href="route('admin.language.index')" :class="{ active: route().current('admin.language.index') }" ><i class="fas fa-globe fa-fw me-2"></i>Language</Link>
            </li>
            <li class="nav-item"> <a class="nav-link"  href="#tab-3"><i class="fas fa-bell fa-fw me-2"></i>Notification Settings</a> </li>
            <li class="nav-item"> <a class="nav-link"  href="#tab-4"><i class="fas fa-user-circle fa-fw me-2"></i>Account Settings</a> </li>
            <li class="nav-item"> <a class="nav-link"  href="#tab-5"><i class="fas fa-sliders-h fa-fw me-2"></i>Social Settings</a> </li>
            <li class="nav-item"> <a class="nav-link mb-0"  href="#tab-6"><i class="fas fa-envelope-open-text fa-fw me-2"></i>Email Settings</a> </li>
        </ul>
</template>
