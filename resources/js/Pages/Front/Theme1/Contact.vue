<script setup>
import AppLayout from '@/Pages/Front/Theme1/Layout/App.vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { computed } from 'vue'
import { toast } from 'vue3-toastify'

const page = usePage()

// Get footer settings for contact info
const footerSettings = computed(() => {
    return page.props.footerSettings || {
        address: '',
        phone: '',
        email: '',
        working_hours: ''
    }
})

const form = useForm({
    name: '',
    email: '',
    subject: '',
    message: ''
})

function submit() {
    form.post(route('web.contact.submit'), {
        onSuccess: () => {
            toast.success('تم إرسال رسالتك بنجاح. سنتواصل معك قريباً.', {
                position: 'top-right',
                autoClose: 3000,
            })
            form.reset()
        },
        onError: (errors) => {
            toast.error('حدث خطأ أثناء إرسال الرسالة. يرجى المحاولة مرة أخرى.', {
                position: 'top-right',
                autoClose: 3000,
            })
        }
    })
}
</script>

<template>
    <Head title="اتصل بنا" />
    <AppLayout>
        <nav aria-label="breadcrumb" class="breadcrumb-nav">
            <div class="container">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a :href="route('/')">الرئيسية</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">اتصل بنا</li>
                </ol>
            </div>
        </nav>

        <div class="page-content">
            <div class="container">
                <h2 class="page-title mb-4">اتصل بنا</h2>
                <p class="mb-5">نحن هنا لمساعدتك. لا تتردد في التواصل معنا عبر النموذج أدناه أو من خلال معلومات التواصل.</p>

                <div class="row">
                    <!-- Contact Information -->
                    <div class="col-lg-4 mb-4 mb-lg-0">
                        <div class="contact-info">
                            <h3 class="contact-info-title mb-4">معلومات التواصل</h3>
                            
                            <div class="contact-info-item mb-4">
                                <div class="contact-info-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="contact-info-content">
                                    <h4 class="contact-info-label">العنوان</h4>
                                    <p v-if="footerSettings.address" class="contact-info-text">{{ footerSettings.address }}</p>
                                    <p v-else class="contact-info-text text-muted">-</p>
                                </div>
                            </div>

                            <div class="contact-info-item mb-4">
                                <div class="contact-info-icon">
                                    <i class="icon-phone"></i>
                                </div>
                                <div class="contact-info-content">
                                    <h4 class="contact-info-label">الهاتف</h4>
                                    <p v-if="footerSettings.phone" class="contact-info-text">
                                        <a :href="`tel:${footerSettings.phone}`">{{ footerSettings.phone }}</a>
                                    </p>
                                    <p v-else class="contact-info-text text-muted">-</p>
                                </div>
                            </div>

                            <div class="contact-info-item mb-4">
                                <div class="contact-info-icon">
                                    <i class="icon-mail-alt"></i>
                                </div>
                                <div class="contact-info-content">
                                    <h4 class="contact-info-label">البريد الإلكتروني</h4>
                                    <p v-if="footerSettings.email" class="contact-info-text">
                                        <a :href="`mailto:${footerSettings.email}`">{{ footerSettings.email }}</a>
                                    </p>
                                    <p v-else class="contact-info-text text-muted">-</p>
                                </div>
                            </div>

                            <div class="contact-info-item" v-if="footerSettings.working_hours">
                                <div class="contact-info-icon">
                                    <i class="icon-clock"></i>
                                </div>
                                <div class="contact-info-content">
                                    <h4 class="contact-info-label">أيام وساعات العمل</h4>
                                    <p class="contact-info-text">{{ footerSettings.working_hours }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Form -->
                    <div class="col-lg-8">
                        <div class="contact-form-wrapper">
                            <h3 class="contact-form-title mb-4">أرسل لنا رسالة</h3>
                            <form @submit.prevent="submit" class="contact-form">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">الاسم <span class="text-danger">*</span></label>
                                        <input 
                                            type="text" 
                                            id="name"
                                            class="form-control" 
                                            v-model="form.name" 
                                            :class="{ 'is-invalid': form.errors.name }"
                                            required 
                                        />
                                        <div v-if="form.errors.name" class="invalid-feedback">
                                            {{ form.errors.name }}
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">البريد الإلكتروني <span class="text-danger">*</span></label>
                                        <input 
                                            type="email" 
                                            id="email"
                                            class="form-control" 
                                            v-model="form.email" 
                                            :class="{ 'is-invalid': form.errors.email }"
                                            required 
                                        />
                                        <div v-if="form.errors.email" class="invalid-feedback">
                                            {{ form.errors.email }}
                                        </div>
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label for="subject" class="form-label">الموضوع</label>
                                        <input 
                                            type="text" 
                                            id="subject"
                                            class="form-control" 
                                            v-model="form.subject" 
                                            :class="{ 'is-invalid': form.errors.subject }"
                                        />
                                        <div v-if="form.errors.subject" class="invalid-feedback">
                                            {{ form.errors.subject }}
                                        </div>
                                    </div>

                                    <div class="col-12 mb-4">
                                        <label for="message" class="form-label">الرسالة <span class="text-danger">*</span></label>
                                        <textarea 
                                            id="message"
                                            class="form-control" 
                                            rows="6" 
                                            v-model="form.message" 
                                            :class="{ 'is-invalid': form.errors.message }"
                                            required
                                        ></textarea>
                                        <div v-if="form.errors.message" class="invalid-feedback">
                                            {{ form.errors.message }}
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <button 
                                            type="submit" 
                                            class="btn btn-primary btn-lg" 
                                            :disabled="form.processing"
                                        >
                                            <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
                                            إرسال الرسالة
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.page-content {
    padding: 3rem 0;
}

.page-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #333;
}

.contact-info {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 8px;
    height: 100%;
}

.contact-info-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #333;
    border-bottom: 2px solid #007bff;
    padding-bottom: 1rem;
}

.contact-info-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.contact-info-icon {
    width: 40px;
    height: 40px;
    background: #007bff;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1.2rem;
}

.contact-info-content {
    flex: 1;
}

.contact-info-label {
    font-size: 1rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.5rem;
}

.contact-info-text {
    font-size: 0.95rem;
    color: #666;
    margin: 0;
}

.contact-info-text a {
    color: #007bff;
    text-decoration: none;
}

.contact-info-text a:hover {
    text-decoration: underline;
}

.contact-form-wrapper {
    background: #fff;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.contact-form-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #333;
    border-bottom: 2px solid #007bff;
    padding-bottom: 1rem;
}

.contact-form .form-label {
    font-weight: 600;
    color: #333;
    margin-bottom: 0.5rem;
}

.contact-form .form-control {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 0.75rem;
}

.contact-form .form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.contact-form .btn-primary {
    background-color: #007bff;
    border-color: #007bff;
    padding: 0.75rem 2rem;
    font-weight: 600;
}

.contact-form .btn-primary:hover {
    background-color: #0056b3;
    border-color: #0056b3;
}

@media (max-width: 991px) {
    .contact-info {
        margin-bottom: 2rem;
    }
}

@media (max-width: 576px) {
    .page-title {
        font-size: 2rem;
    }
    
    .contact-info,
    .contact-form-wrapper {
        padding: 1.5rem;
    }
}
</style>

