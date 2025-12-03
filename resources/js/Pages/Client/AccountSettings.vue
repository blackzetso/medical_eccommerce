<template>
    <Head title="تفاصيل الحساب" />
    <FrontLayout>
        <nav aria-label="breadcrumb" class="breadcrumb-nav">
            <div class="container">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <Link :href="route('/')">الرئيسية</Link>
                    </li>
                    <li class="breadcrumb-item">
                        <Link :href="route('client.dashboard')">لوحة التحكم</Link>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        تفاصيل الحساب
                    </li>
                </ol>
            </div>
        </nav>

        <div class="container account-container custom-account-container">
            <div class="row">
                <div class="col-lg-3 order-0 mb-lg-0 mb-3">
                    <ClientSidebar />
                </div>
                <div class="col-lg-9 order-lg-last order-1">
                    <div class="account-content">
                        <h3 class="account-sub-title d-none d-md-block mb-4">
                            <i class="icon-user-2 align-middle mr-3"></i>تفاصيل الحساب
                        </h3>
                        <form @submit.prevent="updateAccount">
                            <div class="form-group mb-4">
                                <label for="acc-name">الاسم <span class="required">*</span></label>
                                <input type="text" class="form-control" 
                                    id="acc-name" 
                                    v-model="form.name"
                                    required />
                                <div v-if="form.errors.name" class="text-danger">{{ form.errors.name }}</div>
                            </div>

                            <div class="form-group mb-4">
                                <label for="acc-email">البريد الإلكتروني <span class="required">*</span></label>
                                <input type="email" class="form-control" 
                                    id="acc-email" 
                                    v-model="form.email"
                                    placeholder="example@email.com" 
                                    required />
                                <div v-if="form.errors.email" class="text-danger">{{ form.errors.email }}</div>
                            </div>

                            <div class="change-password">
                                <h3 class="text-uppercase mb-2">تغيير كلمة المرور</h3>
                                <p class="mb-3">اترك الحقول فارغة إذا كنت لا تريد تغيير كلمة المرور</p>

                                <div class="form-group">
                                    <label for="acc-password">كلمة المرور الحالية</label>
                                    <input type="password" 
                                        class="form-control" 
                                        id="acc-password"
                                        v-model="form.current_password" />
                                    <div v-if="form.errors.current_password" class="text-danger">{{ form.errors.current_password }}</div>
                                </div>

                                <div class="form-group">
                                    <label for="acc-new-password">كلمة المرور الجديدة</label>
                                    <input type="password" 
                                        class="form-control" 
                                        id="acc-new-password"
                                        v-model="form.password" />
                                    <div v-if="form.errors.password" class="text-danger">{{ form.errors.password }}</div>
                                </div>

                                <div class="form-group">
                                    <label for="acc-confirm-password">تأكيد كلمة المرور الجديدة</label>
                                    <input type="password" 
                                        class="form-control" 
                                        id="acc-confirm-password"
                                        v-model="form.password_confirmation" />
                                    <div v-if="form.errors.password_confirmation" class="text-danger">{{ form.errors.password_confirmation }}</div>
                                </div>
                            </div>

                            <div class="form-footer mt-3 mb-0">
                                <button type="submit" class="btn btn-dark mr-0" :disabled="form.processing">
                                    <span v-if="form.processing">جاري الحفظ...</span>
                                    <span v-else>حفظ التغييرات</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </FrontLayout>
</template>

<script setup>
import { ref, onMounted, nextTick, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { useForm } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import FrontLayout from '@/Pages/Front/Theme1/Layout/App.vue';
import ClientSidebar from '@/Components/ClientSidebar.vue';

const page = usePage();

// Props from the controller
const props = defineProps({
    user: {
        type: Object,
        default: () => null
    }
});

// Form for account details
const form = useForm({
    name: props.user?.name || '',
    email: props.user?.email || '',
    current_password: '',
    password: '',
    password_confirmation: '',
});

// Watch for user prop changes and update form
watch(() => props.user, (newUser) => {
    if (newUser) {
        form.name = newUser.name || '';
        form.email = newUser.email || '';
    }
}, { deep: true, immediate: true });

// Method to update account details
const updateAccount = () => {
    form.put(route('client.account.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Reset password fields
            form.reset('current_password', 'password', 'password_confirmation');
            
            // Wait a bit for Inertia to update page props, then show toast
            setTimeout(() => {
                // Get success message from backend or use default
                const successMessage = page.props.flash?.success || 'تم تحديث بيانات الحساب بنجاح ✅';
                
                // Show success toast
                toast.success(successMessage, {
                    autoClose: 3000,
                    position: 'top-right',
                    rtl: true
                });
            }, 100);
        },
        onError: (errors) => {
            // Check if there are validation errors
            if (errors && Object.keys(errors).length > 0) {
                // Show first error message
                const firstError = Object.values(errors)[0];
                if (Array.isArray(firstError)) {
                    toast.error(firstError[0], {
                        autoClose: 4000,
                        position: 'top-right',
                        rtl: true
                    });
                } else {
                    toast.error(firstError, {
                        autoClose: 4000,
                        position: 'top-right',
                        rtl: true
                    });
                }
            } else {
                // Show generic error toast
                toast.error('حدثت مشكلة أثناء تحديث البيانات ❌', {
                    autoClose: 3000,
                    position: 'top-right',
                    rtl: true
                });
            }
        }
    });
};

onMounted(() => {
    // Show flash messages if any
    nextTick(() => {
        // Check for success message
        if (page.props.flash?.success) {
            toast.success(page.props.flash.success, {
                autoClose: 3000,
                position: 'top-right',
                rtl: true
            });
        }
        
        // Check for error message
        if (page.props.flash?.error) {
            toast.error(page.props.flash.error, {
                autoClose: 3000,
                position: 'top-right',
                rtl: true
            });
        }
    });
});
</script>

