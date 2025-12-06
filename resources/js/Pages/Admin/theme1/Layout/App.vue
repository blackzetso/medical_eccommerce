<script setup>
    import { Link, usePage } from '@inertiajs/vue3'
    import { router } from '@inertiajs/vue3'
    import { route } from 'ziggy-js';
    import { useTranslations } from '@/composables/translations'

    const page = usePage()
    const { t } = useTranslations()
    const languages = Array.isArray(page.props.languages) 
        ? page.props.languages.filter(lang => lang !== null && lang !== undefined)
        : []
    const currentLocale = page.props.locale || 'ar'
    
    // Debug: Check if languages and translations are available
    if (import.meta.env.DEV) {
        console.log('=== ADMIN TRANSLATION DEBUG ===')
        console.log('Languages from props:', page.props.languages)
        console.log('Filtered languages:', languages)
        console.log('Languages count:', languages.length)
        console.log('Current locale:', currentLocale)
        console.log('Translations from props:', page.props.translations)
        console.log('Translations count:', Object.keys(page.props.translations || {}).length)
        console.log('Sample translation keys:', Object.keys(page.props.translations || {}).slice(0, 5))
    }

    function changeLang(lang) {
        router.post(route('change.language'), { lang })
    }

    function handleLogout() {
        router.post(route('logout'), {}, {
            onFinish: () => {
                router.visit(route('login'));
            }
        });
    }
</script>
<template>
    <!-- **************** MAIN CONTENT START **************** -->
    <main>
        <!-- Sidebar START -->
        <nav class="navbar sidebar navbar-expand-xl navbar-dark bg-dark">

            <!-- Navbar brand for xl START -->
            <div class="d-flex align-items-center">
                <Link class="navbar-brand" :href="route('admin.dashboard.index')">
                    <img class="navbar-brand-item" style="height: 55px;" src="/admin/theme1/images/jesoor-logo-white.png" alt="">
                </Link>
            </div>
            <!-- Navbar brand for xl END -->

            <div class="offcanvas offcanvas-start flex-row custom-scrollbar h-100" data-bs-backdrop="true" tabindex="-1" id="offcanvasSidebar">
                <div class="offcanvas-body sidebar-content d-flex flex-column bg-dark sidebar-scrollable">

                    <!-- Sidebar menu START -->
                    <ul class="navbar-nav flex-column flex-grow-1" id="navbar-sidebar" style="overflow-y: auto; overflow-x: hidden; min-height: 0; max-height: 100%;">

                        <!-- Menu item 1 -->
                        <li class="nav-item"><Link :href="route('admin.dashboard.index')" class="nav-link active"><i class="bi bi-house fa-fw me-2"></i>{{ t('dashboard') }}</Link></li>

                        <!-- Title -->
                        <li class="nav-item ms-2 my-2">E-Commerce</li>

                        <!-- menu item 2 -->
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="collapse" href="#collapseproducts" role="button" aria-expanded="false" aria-controls="collapseproducts">
                                <i class="bi bi-box fa-fw me-2"></i>{{ t('products') }}
                            </a>
                            <!-- Submenu -->
                            <ul class="nav collapse flex-column" id="collapseproducts" data-bs-parent="#navbar-sidebar">
                                <li class="nav-item"> <Link class="nav-link" :href="route('admin.products.index')">{{ t('all_products') }}</Link></li>
                                <li class="nav-item"> <Link class="nav-link" :href="route('admin.products.import')"><i class="bi bi-upload me-2"></i>{{ t('import_products') }}</Link></li>
                                <li class="nav-item"> <Link class="nav-link" :href="route('admin.categories.index')">{{ t('categories') }}</Link></li>
                                <li class="nav-item"> <Link class="nav-link" :href="route('admin.brands.index')">{{ t('brands') }}</Link></li>
                                <li class="nav-item"> <Link class="nav-link" :href="route('admin.attributes.index')">{{ t('specifications') }}</Link></li>
                            </ul>
                        </li>

                        <!-- Menu item 3 -->
                        <li class="nav-item"> <Link class="nav-link" :href="route('admin.orders.index')"><i class="fas fa-shopping-cart fa-fw me-2"></i>{{ t('orders') }}</Link></li>

                        <!-- Menu item 4 -->
                        <li class="nav-item"> <Link class="nav-link" :href="route('admin.sliders.index')"><i class="fas fa-images fa-fw me-2"></i>{{ t('sliders') }}</Link></li>

                        <!-- Title -->
                        <li class="nav-item ms-2 my-2">{{ t('clients') }}</li>

                        <!-- Menu item 5 -->
                        <li class="nav-item"> <Link class="nav-link" :href="route('admin.clients.index')"><i class="fas fa-users fa-fw me-2"></i>{{ t('clients') }}</Link></li>

                        <!-- Menu item 5.1 -->
                        <li class="nav-item"> <Link class="nav-link" :href="route('admin.leads.index')"><i class="fas fa-user-plus fa-fw me-2"></i>طلبات التسجيل</Link></li>

                        <!-- Title -->
                        <li class="nav-item ms-2 my-2">{{ t('reports') }}</li>

                        <!-- Menu item 6 -->
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="collapse" href="#collapsereports" role="button" aria-expanded="false" aria-controls="collapsereports">
                                <i class="far fa-chart-bar fa-fw me-2"></i>{{ t('reports') }}
                            </a>
                            <!-- Submenu -->
                            <ul class="nav collapse flex-column" id="collapsereports" data-bs-parent="#navbar-sidebar">
                                <li class="nav-item"> <Link class="nav-link" :href="route('admin.reports.sales')">{{ t('reports') }} {{ t('sales') }}</Link></li>
                                <li class="nav-item"> <Link class="nav-link" :href="route('admin.reports.products')">{{ t('reports') }} {{ t('products') }}</Link></li>
                                <li class="nav-item"> <Link class="nav-link" :href="route('admin.reports.customers')">{{ t('reports') }} {{ t('clients') }}</Link></li>
                                <li class="nav-item"> <Link class="nav-link" :href="route('admin.reports.orders')">{{ t('reports') }} {{ t('orders') }}</Link></li>
                                <li class="nav-item"> <Link class="nav-link" :href="route('admin.reports.revenue')">{{ t('reports') }} {{ t('revenue') }}</Link></li>
                                <li class="nav-item"> <Link class="nav-link" :href="route('admin.reports.inventory')">{{ t('reports') }} {{ t('inventory') }}</Link></li>
                            </ul>
                        </li>

                        <!-- Title -->
                        <li class="nav-item ms-2 my-2">{{ t('settings') }}</li>

                        <!-- Menu item 7 -->
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="collapse" href="#collapsesettings" role="button" aria-expanded="false" aria-controls="collapsesettings">
                                <i class="fas fa-cog fa-fw me-2"></i>{{ t('settings') }}
                            </a>
                            <!-- Submenu -->
                            <ul class="nav collapse flex-column" id="collapsesettings" data-bs-parent="#navbar-sidebar">
                                <li class="nav-item"> <Link class="nav-link" :href="route('admin.settings.index')">{{ t('general_settings') }}</Link></li>
                                <li class="nav-item"> <Link class="nav-link" :href="route('admin.settings.appearance')">{{ t('appearance_settings') }}</Link></li>
                                <li class="nav-item"> <Link class="nav-link" :href="route('admin.settings.email')">{{ t('email_settings') }}</Link></li>
                                <li class="nav-item"> <Link class="nav-link" :href="route('admin.settings.payment')">{{ t('payment_settings') }}</Link></li>
                                <li class="nav-item"> <Link class="nav-link" :href="route('admin.settings.shipping')">{{ t('shipping_settings') }}</Link></li>
                                <li class="nav-item"> <Link class="nav-link" :href="route('admin.settings.notification')">{{ t('notification_settings') }}</Link></li>
                                <li class="nav-item"> <Link class="nav-link" :href="route('admin.language.index')">{{ t('languages') }}</Link></li>
                            </ul>
                        </li>
                    </ul>
                    <!-- Sidebar menu end -->

                    <!-- Sidebar footer START -->
                    <div class="px-3 mt-auto pt-3 border-top">
                        <div class="d-flex flex-column gap-2">
                            <!-- Settings and Home Icons -->
                            <div class="d-flex align-items-center justify-content-center gap-3 mb-2">
                                <Link :href="route('admin.settings.index')" class="btn btn-sm btn-light btn-round" data-bs-toggle="tooltip" data-bs-placement="top" title="الإعدادات">
                                    <i class="bi bi-gear-fill"></i>
                                </Link>
                                <a class="btn btn-sm btn-light btn-round" href="/" data-bs-toggle="tooltip" data-bs-placement="top" title="الرئيسية">
                                    <i class="bi bi-globe"></i>
                                </a>
                            </div>
                            <!-- Sign Out Button -->
                            <button @click="handleLogout" class="btn btn-outline-danger btn-sm w-100 d-flex align-items-center justify-content-center gap-2" data-bs-placement="top" title="تسجيل الخروج">
                                <i class="bi bi-power"></i>
                                <span>تسجيل الخروج</span>
                            </button>
                        </div>
                    </div>
                    <!-- Sidebar footer END -->

                </div>
            </div>
        </nav>
        <!-- Sidebar END -->

        <!-- Page content START -->
        <div class="page-content">

            <!-- Top bar START -->
            <nav class="navbar top-bar navbar-light border-bottom py-0 py-xl-3">
                <div class="container-fluid p-0">
                    <div class="d-flex align-items-center w-100">

                        <!-- Logo START -->
                        <div class="d-flex align-items-center d-xl-none">
                            <a class="navbar-brand" href="index.html">
                                <img class="light-mode-item navbar-brand-item h-30px" src="/admin/theme1/images/logo.svg" alt="">
                                <img class="dark-mode-item navbar-brand-item h-30px" src="/admin/theme1/images/logo1.svg" alt="">
                            </a>
                        </div>
                        <!-- Logo END -->

                        <!-- Toggler for sidebar START -->
                        <div class="navbar-expand-xl sidebar-offcanvas-menu">
                            <button class="navbar-toggler me-auto" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar" aria-controls="offcanvasSidebar" aria-expanded="false" aria-label="Toggle navigation" data-bs-auto-close="outside">
                                <i class="bi bi-text-right fa-fw h2 lh-0 mb-0 rtl-flip" data-bs-target="#offcanvasMenu"> </i>
                            </button>
                        </div>
                        <!-- Toggler for sidebar END -->

                        <!-- Top bar left -->
                        <div class="navbar-expand-lg ms-auto ms-xl-0">

                            <!-- Toggler for menubar START -->
                            <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTopContent" aria-controls="navbarTopContent" aria-expanded="false" aria-label="Toggle navigation">
                                <span class="navbar-toggler-animation">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </span>
                            </button>
                            <!-- Toggler for menubar END -->

                            <!-- Topbar menu START -->
                            <div class="collapse navbar-collapse w-100" id="navbarTopContent">
                                <!-- Top search START -->
                                <div class="nav my-3 my-xl-0 flex-nowrap align-items-center">
                                    <div class="nav-item w-100">
                                        <form class="position-relative">
                                            <input class="form-control pe-5 bg-secondary bg-opacity-10 border-0" type="search" :placeholder="t('search')" :aria-label="t('search')">
                                            <button class="bg-transparent px-2 py-0 border-0 position-absolute top-50 end-0 translate-middle-y" type="submit"><i class="fas fa-search fs-6 text-primary"></i></button>
                                        </form>
                                    </div>
                                </div>
                                <!-- Top search END -->
                            </div>
                            <!-- Topbar menu END -->
                        </div>
                        <!-- Top bar left END -->

                        <!-- Top bar right START -->
                        <div class="ms-xl-auto">
                            <ul class="navbar-nav flex-row align-items-center">

                                <!-- Language dropdown START -->
                                <li v-if="languages && languages.length > 0" class="nav-item ms-2 ms-md-3 dropdown" style="display: block !important;">
                                    <!-- Language button -->
                                    <a class="btn btn-light btn-round mb-0" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                                        <i class="bi bi-globe fa-fw"></i>
                                    </a>
                                    <!-- Language dropdown menu START -->
                                    <div class="dropdown-menu dropdown-animation dropdown-menu-end dropdown-menu-size-md p-0 shadow-lg border-0">
                                        <div class="card bg-transparent">
                                            <div class="card-body p-0">
                                                <ul class="list-group list-unstyled list-group-flush">
                                                    <li v-for="(language, index) in languages" :key="language?.id || index">
                                                        <button 
                                                            v-if="language"
                                                            @click="changeLang(language.code)" 
                                                            :class="[
                                                                'list-group-item-action border-0 d-flex p-3 align-items-center justify-content-between',
                                                                language.code === currentLocale ? 'bg-primary bg-opacity-10' : '',
                                                                index !== languages.length - 1 ? 'border-bottom' : ''
                                                            ]"
                                                        >
                                                            <span>{{ language.name }}</span>
                                                            <span v-if="language.code === currentLocale" class="badge bg-primary">✓</span>
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Language dropdown menu END -->
                                </li>
                                <!-- Language dropdown END -->
                                <!-- Notification dropdown START -->
                                <li class="nav-item ms-2 ms-md-3 dropdown">
                                    <!-- Notification button -->
                                    <a class="btn btn-light btn-round mb-0" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                                        <i class="bi bi-bell fa-fw"></i>
                                    </a>
                                    <!-- Notification dote -->
                                    <span class="notif-badge animation-blink"></span>

                                    <!-- Notification dropdown menu START -->
                                    <div class="dropdown-menu dropdown-animation dropdown-menu-end dropdown-menu-size-md p-0 shadow-lg border-0">
                                        <div class="card bg-transparent">
                                            <div class="card-header bg-transparent border-bottom py-4 d-flex justify-content-between align-items-center">
                                                <h6 class="m-0">Notifications <span class="badge bg-danger bg-opacity-10 text-danger ms-2">2 new</span></h6>
                                                <a class="small" href="#">Clear all</a>
                                            </div>
                                            <div class="card-body p-0">
                                                <ul class="list-group list-unstyled list-group-flush">
                                                    <!-- Notif item -->
                                                    <li>
                                                        <a href="#" class="list-group-item-action border-0 border-bottom d-flex p-3">
                                                            <div class="me-3">
                                                                <div class="avatar avatar-md">
                                                                    <img class="avatar-img rounded-circle" src="/admin/theme1/images/avatar/01.jpg" alt="avatar">
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <p class="text-body small m-0">Congratulate <b>Joan Wallace</b> for graduating from <b>Microverse university</b></p>
                                                                <u class="small">Say congrats</u>
                                                            </div>
                                                        </a>
                                                    </li>

                                                    <!-- Notif item -->
                                                    <li>
                                                        <a href="#" class="list-group-item-action border-0 border-bottom d-flex p-3">
                                                            <div class="me-3">
                                                                <div class="avatar avatar-md">
                                                                    <img class="avatar-img rounded-circle" src="/admin/theme1/images/avatar/01.jpg" alt="avatar">
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-1">Larry Lawson Added a new course</h6>
                                                                <p class="small text-body m-0">What's new! Find out about new features</p>
                                                                <u class="small">View detail</u>
                                                            </div>
                                                        </a>
                                                    </li>

                                                    <!-- Notif item -->
                                                    <li>
                                                        <a href="#" class="list-group-item-action border-0 border-bottom d-flex p-3">
                                                            <div class="me-3">
                                                                <div class="avatar avatar-md">
                                                                    <img class="avatar-img rounded-circle" src="/admin/theme1/images/avatar/01.jpg" alt="avatar">
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-1">New request to apply for Instructor</h6>
                                                                <u class="small">View detail</u>
                                                            </div>
                                                        </a>
                                                    </li>

                                                    <!-- Notif item -->
                                                    <li>
                                                        <a href="#" class="list-group-item-action border-0 border-bottom d-flex p-3">
                                                            <div class="me-3">
                                                                <div class="avatar avatar-md">
                                                                    <img class="avatar-img rounded-circle" src="/admin/theme1/images/avatar/01.jpg" alt="avatar">
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-1">Update v2.3 completed successfully</h6>
                                                                <p class="small text-body m-0">What's new! Find out about new features</p>
                                                                <small class="text-body">5 min ago</small>
                                                            </div>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <!-- Button -->
                                            <div class="card-footer bg-transparent border-0 py-3 text-center position-relative">
                                                <a href="#" class="stretched-link">See all incoming activity</a>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Notification dropdown menu END -->
                                </li>
                                <!-- Notification dropdown END -->

                                <!-- Profile dropdown START -->
                                <li class="nav-item ms-2 ms-md-3 dropdown">
                                    <!-- Avatar -->
                                    <a class="avatar avatar-sm p-0" href="#" id="profileDropdown" role="button" data-bs-auto-close="outside" data-bs-display="static" data-bs-toggle="dropdown" aria-expanded="false">
                                        <img class="avatar-img rounded-circle" src="/admin/theme1/images/avatar/01.jpg" alt="avatar">
                                    </a>

                                    <!-- Profile dropdown START -->
                                    <ul class="dropdown-menu dropdown-animation dropdown-menu-end shadow pt-3" aria-labelledby="profileDropdown">
                                        <!-- Profile info -->
                                        <li class="px-3">
                                            <div class="d-flex align-items-center">
                                                <!-- Avatar -->
                                                <div class="avatar me-3 mb-3">
                                                    <img class="avatar-img rounded-circle shadow" src="/admin/theme1/images/avatar/01.jpg" alt="avatar">
                                                </div>
                                                <div>
                                                    <a class="h6 mt-2 mt-sm-0" href="#">Lori Ferguson</a>
                                                    <p class="small m-0">example@gmail.com</p>
                                                </div>
                                            </div>
                                        </li>
                        <li> <hr class="dropdown-divider"></li>
                                        <!-- Links -->
                                        <li><a class="dropdown-item" href="#"><i class="bi bi-person fa-fw me-2"></i>{{ t('edit_profile') }}</a></li>
                                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear fa-fw me-2"></i>{{ t('account_settings') }}</a></li>
                                        <li><a class="dropdown-item" href="#"><i class="bi bi-info-circle fa-fw me-2"></i>{{ t('help') }}</a></li>
                                            <li><button @click="handleLogout" class="dropdown-item bg-danger-soft-hover"><i class="bi bi-power fa-fw me-2"></i>{{ t('sign_out') }}</button></li>
                                        <li> <hr class="dropdown-divider"></li>

                                        <!-- Dark mode options START -->
                                        <li>
                                            <div class="bg-light dark-mode-switch theme-icon-active d-flex align-items-center p-1 rounded mt-2">
                                                <!-- <span>Mode:</span> -->
                                                <button type="button" class="btn btn-sm mb-0" data-bs-theme-value="light">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sun fa-fw mode-switch" viewBox="0 0 16 16">
                                                        <path d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z"/>
                                                        <use href="#"></use>
                                                    </svg> Light
                                                </button>
                                                <button type="button" class="btn btn-sm mb-0" data-bs-theme-value="dark">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-moon-stars fa-fw mode-switch" viewBox="0 0 16 16">
                                                        <path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278zM4.858 1.311A7.269 7.269 0 0 0 1.025 7.71c0 4.02 3.279 7.276 7.319 7.276a7.316 7.316 0 0 0 5.205-2.162c-.337.042-.68.063-1.029.063-4.61 0-8.343-3.714-8.343-8.29 0-1.167.242-2.278.681-3.286z"/>
                                                        <path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z"/>
                                                        <use href="#"></use>
                                                    </svg> Dark
                                                </button>
                                                <button type="button" class="btn btn-sm mb-0 active" data-bs-theme-value="auto">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-circle-half fa-fw mode-switch" viewBox="0 0 16 16">
                                                        <path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z"/>
                                                        <use href="#"></use>
                                                    </svg> Auto
                                                </button>
                                            </div>
                                        </li>
                                        <!-- Dark mode options END-->
                                    </ul>
                                    <!-- Profile dropdown END -->
                                </li>
                                <!-- Profile dropdown END -->
                            </ul>
                        </div>
                        <!-- Top bar right END -->
                    </div>
                </div>
            </nav>
            <!-- Top bar END -->
            <slot/>
        </div>
        <!-- Page content END -->

    </main>
    <!-- **************** MAIN CONTENT END **************** -->
    <!-- Back to top -->
    <div class="back-top"><i class="bi bi-arrow-up-short position-absolute top-50 start-50 translate-middle"></i></div>
</template>

<style scoped>
.sidebar-scrollable {
    overflow: hidden !important;
    height: 100% !important;
    max-height: 100vh !important;
}

.sidebar-scrollable #navbar-sidebar {
    flex: 1 1 auto;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    min-height: 0 !important;
    -webkit-overflow-scrolling: touch;
}

/* Custom scrollbar styling */
.sidebar-scrollable #navbar-sidebar::-webkit-scrollbar {
    width: 6px;
}

.sidebar-scrollable #navbar-sidebar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 3px;
}

.sidebar-scrollable #navbar-sidebar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 3px;
}

.sidebar-scrollable #navbar-sidebar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}
</style>
