import './bootstrap'
import '../css/app.css'
import '../css/custom-search.css'
// import 'simple-line-icons/css/simple-line-icons.css'
// import 'bootstrap/dist/css/bootstrap.min.css'

import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { ZiggyVue } from '../../vendor/tightenco/ziggy'
import VueApexCharts from 'vue3-apexcharts'
import VueSweetalert2 from 'vue-sweetalert2'
import 'sweetalert2/dist/sweetalert2.min.css'
import 'vue3-toastify/dist/index.css'
import { createPinia } from 'pinia'


const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

// Exclude broken or archived pages explicitly from glob mapping
const pages = import.meta.glob('./Pages/**/*.vue')
// delete pages['./Pages/Front/Theme1/Index.vue']

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) =>
    resolvePageComponent(`./Pages/${name}.vue`, pages),
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) })

    app.use(createPinia())
    app.use(plugin)
    app.use(ZiggyVue)
    app.use(VueApexCharts)
    // .use(VueSweetalert2)

    app.mount(el)
  },
  progress: {
    color: '#4B5563',
  },
})
