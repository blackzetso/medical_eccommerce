<script setup>
import { ref, watch } from 'vue'
import { useForm, Head, Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Front/Theme1/Layout/App.vue'
import { route } from 'ziggy-js'

const page = usePage()
const form = useForm({
	name: '',
	email: '',
	phone: '',
	pharmacy_name: '',
	address: '',
	notes: ''
})

const submitting = ref(false)
const success = ref(false)

// Watch for flash messages
watch(() => page.props.flash, (flash) => {
	if (flash?.success) {
		success.value = true
		form.reset()
	}
}, { immediate: true })

function submit() {
	submitting.value = true
	form.post(route('client.register'), {
		onSuccess: () => {
			success.value = true
			form.reset()
		},
		onFinish: () => {
			submitting.value = false
		}
	})
}
</script>
<template>
	<Head title="Signup" />
	<AppLayout>
		<div class="container mt-5">
			<div class="page-header">
				<div class="container d-flex flex-column align-items-center">
					<nav aria-label="breadcrumb" class="breadcrumb-nav">
						<div class="container">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><Link :href="route('/')">Home</Link></li>
								<li class="breadcrumb-item active" aria-current="page">
									Signup
								</li>
							</ol>
						</div>
					</nav>
					<h1>إنشاء حساب</h1>
				</div>
			</div>
			<div class="container login-container">
				<div class="row">
					<div class="col-lg-10 mx-auto">
						<div class="row">
							<div class="col-md-8 mx-auto">
								<div v-if="success" class="alert alert-success alert-dismissible fade show" role="alert">
									<strong>شكراً لك!</strong> تم إرسال طلبك بنجاح. سيقوم فريقنا بالتواصل معك قريباً لإنشاء حسابك.
									<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
								</div>
								<div class="heading mt-5 mb-1">
									<h2 class="title">إنشاء حساب جديد</h2>
									<p class="text-muted">يرجى ملء النموذج أدناه. سيقوم فريقنا بالتواصل معك لإنشاء حسابك.</p>
								</div>
								<form @submit.prevent="submit">
									<label for="signup-name">
										الاسم الكامل
										<span class="required">*</span>
									</label>
									<input type="text" class="form-input form-wide" id="signup-name" v-model="form.name" required />
									<div v-if="form.errors.name" class="text-danger small mb-2">{{ form.errors.name }}</div>

									<label for="signup-email">
										البريد الإلكتروني
										<span class="required">*</span>
									</label>
									<input type="email" class="form-input form-wide" id="signup-email" v-model="form.email" required />
									<div v-if="form.errors.email" class="text-danger small mb-2">{{ form.errors.email }}</div>

									<label for="signup-phone">
										رقم الهاتف
										<span class="required">*</span>
									</label>
									<input type="tel" class="form-input form-wide" id="signup-phone" v-model="form.phone" required />
									<div v-if="form.errors.phone" class="text-danger small mb-2">{{ form.errors.phone }}</div>

									<label for="signup-pharmacy">
										اسم الصيدلية
									</label>
									<input type="text" class="form-input form-wide" id="signup-pharmacy" v-model="form.pharmacy_name" />
									<div v-if="form.errors.pharmacy_name" class="text-danger small mb-2">{{ form.errors.pharmacy_name }}</div>

									<label for="signup-address">
										العنوان
									</label>
									<textarea class="form-input form-wide" id="signup-address" v-model="form.address" rows="3"></textarea>
									<div v-if="form.errors.address" class="text-danger small mb-2">{{ form.errors.address }}</div>

									<label for="signup-notes">
										ملاحظات إضافية
									</label>
									<textarea class="form-input form-wide" id="signup-notes" v-model="form.notes" rows="3"></textarea>
									<div v-if="form.errors.notes" class="text-danger small mb-2">{{ form.errors.notes }}</div>

									<div class="form-footer mt-3">
										<Link :href="route('client.login')" class="text-dark">لديك حساب بالفعل؟ تسجيل الدخول</Link>
									</div>
									<button type="submit" class="btn btn-dark btn-md w-100 mt-3" :disabled="form.processing || submitting">
										<span v-if="form.processing || submitting">جاري الإرسال...</span>
										<span v-else>إرسال الطلب</span>
									</button>
									<div v-if="form.errors.general" class="text-danger small mt-2">{{ form.errors.general }}</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</AppLayout>
</template>

