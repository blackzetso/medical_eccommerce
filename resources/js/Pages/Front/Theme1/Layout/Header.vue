<template>
<header class="header font-sans">
            <div class="header-middle sticky-header">
                <div class="container header-container">
                    <div class="header-left">
                        <!-- <button class="mobile-menu-toggler" type="button">
                            <i class="fas fa-bars"></i>
                        </button> -->

                        <Link :href="route('/')" class="logo">
                            <img :src="asset('front/theme1/images/logo-black.png')" class="d-none d-sm-block" alt="Porto Logo">
                        </Link>

                        <nav class="main-nav font2 d-none d-md-block">
                            <ul class="menu">
                                <li class="active">
                                    <Link :href="route('/')">{{ t('home') }}</Link>
                                </li>
                                <li>
                                    <Link :href="route('web.categories')">{{ t('categories') }}</Link>
                                </li>
                                <li>
                                    <Link :href="route('web.products')">{{ t('products') }}</Link>
                                </li>
                                <li>
                                    <Link :href="route('web.about')">{{ t('about_us') }}</Link>
                                </li>
                                <li>
                                    <Link :href="route('web.contact')">{{ t('contact_us') }}</Link>
                                </li>
                            </ul>
                        </nav>
                    </div><!-- End .header-left -->

                    <div class="header-center">
                        <form @submit.prevent="handleSearch" class="header-search-form">
                            <div class="header-search">
                                <input 
                                    type="search" 
                                    v-model="searchQuery"
                                    class="form-control" 
                                    name="q" 
                                    id="q" 
                                    :placeholder="t('search') + '...'" 
                                    required
                                />
                                <button class="btn icon-magnifier" type="submit" :title="t('search')"></button>
                            </div>
                        </form>
                    </div><!-- End .header-center -->

                    <div class="header-right">
                        <!-- Language Switcher -->
                        <div v-if="languages && languages.length > 0" class="language-switcher dropdown" style="margin-left: 10px; display: block !important;">
                            <!-- <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-globe me-1"></i>
                                <span>{{ currentLanguageName }}</span>
                            </button> -->
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                                <li v-for="(language, index) in languages" :key="language?.id || index">
                                    <a 
                                        v-if="language"
                                        class="dropdown-item d-flex align-items-center justify-content-between" 
                                        href="#" 
                                        @click.prevent="changeLang(language.code)"
                                    >
                                        <span>{{ language.name }}</span>
                                        <span v-if="language.code === currentLocale" class="badge bg-primary">✓</span>
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <template v-if="user">
                                <Link :href="route('client.dashboard')" class="header-icon  " :title="t('my_account')"><i class="icon-user-2"></i></Link>
                                <Link :href="route('client.favorites')" class="header-icon header-icon-wishlist" :title="t('wishlist')"><i class="icon-wishlist-2"></i></Link>
                                <CartDropdown />
                            </template>
                            <template v-else>
                                <Link :href="route('client.login')" class="btn  btn-primary mr-3" :title="t('login')">
                                    {{ t('login') }}
                                </Link>
                                <Link :href="route('client.register')" class="btn btn-outline-primary ml-3 mr-5 ms-2" :title="t('register')">
                                    {{ t('register') }}
                                </Link>
                            </template>


                    </div><!-- End .header-right -->
                </div><!-- End .container -->
            </div><!-- End .header-middle -->
        </header>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { useTranslations } from '@/composables/translations'
import CartDropdown from './CartDropdown.vue'
const showSearch = ref(false)
const searchQuery = ref('')
const asset = (path) => '/' + path

const page = usePage()
const { t } = useTranslations()
const user = page.props.auth?.user
const languages = Array.isArray(page.props.languages) 
    ? page.props.languages.filter(lang => lang !== null && lang !== undefined)
    : []
const currentLocale = page.props.locale || 'ar'

const currentLanguageName = computed(() => {
    if (!languages || languages.length === 0) return currentLocale.toUpperCase()
    const current = languages.find(lang => lang && lang.code === currentLocale)
    return current ? current.name : currentLocale.toUpperCase()
})

// Debug: Check if languages and translations are available
if (import.meta.env.DEV) {
    console.log('=== FRONT TRANSLATION DEBUG ===')
    console.log('Languages from props:', page.props.languages)
    console.log('Filtered languages:', languages)
    console.log('Languages count:', languages.length)
    console.log('Current locale:', currentLocale)
    console.log('Translations from props:', page.props.translations)
    console.log('Translations count:', Object.keys(page.props.translations || {}).length)
    console.log('Sample translation keys:', Object.keys(page.props.translations || {}).slice(0, 5))
}

function changeLang(lang) {
    router.post(route('change.language'), { lang }, {
        preserveState: true,
        preserveScroll: true,
    })
}

// Sync search query with URL parameter
const syncSearchFromURL = () => {
    const urlParams = new URLSearchParams(window.location.search)
    const searchParam = urlParams.get('search')
    if (searchParam && searchQuery.value !== searchParam) {
        searchQuery.value = searchParam
    } else if (!searchParam && searchQuery.value) {
        // Clear if no search param in URL
        searchQuery.value = ''
    }
}

// Initial sync
syncSearchFromURL()

// Watch for route changes
watch(() => page.url, () => {
    syncSearchFromURL()
}, { immediate: true })

const handleSearch = () => {
    if (searchQuery.value.trim()) {
        router.get(route('web.products'), { search: searchQuery.value.trim() }, {
            preserveState: true,
            preserveScroll: true,
        })
    } else {
        // If search is empty, go to products without search
        router.get(route('web.products'), {}, {
            preserveState: true,
            preserveScroll: true,
        })
    }
}
</script>

<style scoped>
.header-container {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

.header-center {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    min-width: 200px;
    max-width: 500px;
    margin: 0 auto;
    padding: 0;
    height: 100%;
}

.header-search-form {
    width: 100%;
    margin: 0;
    display: flex;
    align-items: center;
}

.header-search {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
    margin: 0;
}

.header-search .form-control {
    flex: 1;
    height: 40px;
    padding: 8px 45px 8px 15px;
    border: 1px solid #e7e7e7;
    border-radius: 25px;
    background: #f4f4f4;
    font-size: 1.3rem;
    color: #333;
    transition: all 0.3s ease;
    width: 100%;
    line-height: 24px;
    vertical-align: middle;
}

.header-search .form-control:focus {
    outline: none;
    border-color: #007bff;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.header-search .form-control::placeholder {
    color: #a8a8a8;
}

.header-search .btn {
    position: absolute;
    right: 5px;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    color: #666;
    padding: 8px 12px;
    cursor: pointer;
    transition: color 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.header-search .btn:hover {
    color: #007bff;
}

 

.header-search .btn.icon-magnifier:before {
    font-size: 1.6rem;
    /* المحتوى سيأتي من icon-magnifier تلقائياً */
}

.header-search .btn.icon-magnifier:after {
    display: none !important;
    content: none !important;
}

/* منع أي محتوى إضافي من CSS العام */
.header-search .btn:not(.icon-magnifier):before,
.header-search .btn:not(.icon-magnifier):after {
    display: none !important;
    content: none !important;
}

/* Responsive for tablet and mobile */
@media (max-width: 991px) {
    .header-container {
        flex-wrap: wrap;
    }
    
    .header-center {
        order: 3;
        width: 100%;
        max-width: 100%;
        margin: 10px 0 0 0;
        padding: 0;
    }
    
    .header-left {
        flex: 1;
    }
    
    .header-right {
        margin-left: auto;
    }
}

@media (max-width: 767px) {
    .header-search .form-control {
        font-size: 1.2rem;
        height: 38px;
        padding: 6px 40px 6px 12px;
        line-height: 26px;
    }
    
    .header-search .btn {
        padding: 6px 10px;
        right: 3px;
    }
    
    .header-search .btn:before {
        font-size: 1.4rem;
    }
    
    .header-center {
        margin-top: 8px;
    }
}
</style>
