<template>
    <div class="client-sidebar">
        <div class="sidebar-header">
            <h2 class="sidebar-title">حسابي</h2>
        </div>
        <nav class="sidebar-nav">
            <ul class="nav-list">
                <li class="nav-item">
                    <Link 
                        :href="route('client.dashboard')" 
                        class="nav-link"
                        :class="{ active: isActive('/client/dashboard') }"
                    >
                        <i class="icon-home"></i>
                        <span>لوحة التحكم</span>
                    </Link>
                </li>
                <li v-if="user && user.user_type === 'admin'" class="nav-item">
                    <a 
                        :href="route('admin.dashboard.index')" 
                        class="nav-link"
                        style="background: linear-gradient(90deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 152, 0, 0.05) 100%); color: #ff9800; border-right-color: #ff9800;"
                        @click.prevent="goToAdminDashboard"
                    >
                        <i class="icon-settings"></i>
                        <span>لوحة التحكم (أدمن)</span>
                    </a>
                </li>
                <li class="nav-item">
                    <Link 
                        :href="route('client.myorders')" 
                        class="nav-link"
                        :class="{ active: isActive('/myorders') || isActive('/tracking') || isActive('/order/') }"
                    >
                        <i class="sicon-social-dropbox"></i>
                        <span>الطلبات</span>
                    </Link>
                </li>
                <li class="nav-item">
                    <Link 
                        :href="route('client.account.settings')" 
                        class="nav-link"
                        :class="{ active: isActive('/account/settings') }"
                    >
                        <i class="icon-user-2"></i>
                        <span>تفاصيل الحساب</span>
                    </Link>
                </li>
                <li class="nav-item">
                    <Link 
                        :href="route('client.favorites')" 
                        class="nav-link"
                        :class="{ active: isActive('/favorites') }"
                    >
                        <i class="sicon-heart"></i>
                        <span>قائمة الرغبات</span>
                    </Link>
                </li>
                <li class="nav-item">
                    <a 
                        class="nav-link logout-link" 
                        href="#" 
                        @click.prevent="logout"
                    >
                        <i class="icon-sign-out"></i>
                        <span>تسجيل الخروج</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</template>

<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const page = usePage();
const user = page.props.auth?.user;

const isActive = (path) => {
    const currentUrl = page.url;
    
    if (path === '#edit') {
        return currentUrl.includes('#edit') || window.location.hash === '#edit';
    }
    
    return currentUrl.includes(path);
};

const logout = () => {
    router.post(route('logout'));
};

const goToAdminDashboard = () => {
    // استخدام window.location.href لإجبار إعادة تحميل الصفحة بالكامل
    // هذا يضمن تحميل ملفات CSS و JS الخاصة بلوحة الأدمن
    window.location.href = route('admin.dashboard.index');
};
</script>

<style scoped>
.client-sidebar {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    padding: 0;
    overflow: hidden;
    transition: all 0.3s ease;
    display: block;
    width: 100%;
}

.sidebar-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 24px 20px;
    text-align: center;
}

.sidebar-title {
    color: #ffffff;
    font-size: 20px;
    font-weight: 600;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.sidebar-nav {
    padding: 12px 0;
}

.nav-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.nav-item {
    margin: 4px 0;
    position: relative;
}

.nav-link {
    display: flex;
    align-items: center;
    padding: 14px 20px;
    color: #555;
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
    border-right: 3px solid transparent;
    font-size: 15px;
    font-weight: 500;
}

.nav-link i {
    margin-left: 12px;
    font-size: 18px;
    width: 24px;
    text-align: center;
    transition: all 0.3s ease;
}

.nav-link span {
    flex: 1;
}

.nav-link:hover {
    background-color: #f8f9fa;
    color: #667eea;
    border-right-color: #667eea;
    padding-right: 24px;
}

.nav-link:hover i {
    color: #667eea;
    transform: translateX(-3px);
}

.nav-link.active {
    background: linear-gradient(90deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.05) 100%);
    color: #667eea;
    border-right-color: #667eea;
    font-weight: 600;
    box-shadow: inset 0 0 20px rgba(102, 126, 234, 0.05);
}

.nav-link.active::before {
    content: '';
    position: absolute;
    right: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
    border-radius: 0 3px 3px 0;
}

.nav-link.active i {
    color: #667eea;
    transform: scale(1.1);
}

.logout-link {
    color: #dc3545;
    border-top: 1px solid #e9ecef;
    margin-top: 8px;
    padding-top: 16px;
}

.logout-link:hover {
    background-color: #fff5f5;
    color: #dc3545;
    border-right-color: #dc3545;
}

.logout-link.active {
    background: linear-gradient(90deg, rgba(220, 53, 69, 0.1) 0%, rgba(220, 53, 69, 0.05) 100%);
    color: #dc3545;
    border-right-color: #dc3545;
}

.logout-link.active::before {
    background: linear-gradient(180deg, #dc3545 0%, #c82333 100%);
}

/* Responsive */
@media (max-width: 991px) {
    .client-sidebar {
        margin-bottom: 20px;
    }
    
    .sidebar-header {
        padding: 20px 16px;
    }
    
    .sidebar-title {
        font-size: 18px;
    }
    
    .nav-link {
        padding: 12px 16px;
        font-size: 14px;
    }
}
</style>

