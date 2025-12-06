<script setup>
import { ref } from 'vue'
import { useForm, Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Pages/Front/Theme1/Layout/App.vue'
import { route } from 'ziggy-js'
import { useTranslations } from '@/composables/translations'

const { t } = useTranslations()

const props = defineProps({
		sliders: Array,
		categories: Array
})

const form = useForm({
	email: '',
	password: '',
	remember: false
})

const submitting = ref(false)

function submit() {
	submitting.value = true
	form.post(route('login'), {
		onSuccess: (page) => {
			if (page.url && page.url.includes('/admin/dashboard')) {
				window.location.href = route('admin.dashboard.index');
			}
		},
		onFinish: () => {
			submitting.value = false
		}
	})
}
</script>
<template>
	<Head title="Login" />
	<AppLayout>
		<div class="container mt-5">
			<div class="page-header">
				<div class="container d-flex flex-column align-items-center">
					<nav aria-label="breadcrumb" class="breadcrumb-nav">
						<div class="container">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><Link :href="route('/')">{{ t('home') }}</Link></li>
								<li class="breadcrumb-item active" aria-current="page">
									{{ t('my_account') }}
								</li>
							</ol>
						</div>
					</nav>
					<h1>{{ t('my_account') }}</h1>
				</div>
			</div>
			<div class="container login-container">
				<div class="row">
					<div class="col-lg-10 mx-auto">
						<div class="row">
							<div class="col-md-6">
								<div class="heading mt-5 mb-1">
									<h2 class="title">{{ t('login') }}</h2>
								</div>
								<form @submit.prevent="submit">
									<label for="login-email">
										{{ t('username_or_email') }}
										<span class="required">*</span>
									</label>
									<input type="email" class="form-input form-wide" id="login-email" v-model="form.email" :placeholder="t('email')" required />
									<div v-if="form.errors.email" class="text-danger small mb-2">{{ form.errors.email }}</div>

									<label for="login-password">
										{{ t('password') }}
										<span class="required">*</span>
									</label>
									<input type="password" class="form-input form-wide" id="login-password" v-model="form.password" :placeholder="t('password')" required />
									<div v-if="form.errors.password" class="text-danger small mb-2">{{ form.errors.password }}</div>

									<div class="form-footer">
										<div class="custom-control custom-checkbox mb-0">
											<input type="checkbox" class="custom-control-input" id="lost-password" v-model="form.remember" />
											<label class="custom-control-label mb-0" for="lost-password">{{ t('remember_me') }}</label>
										</div>
										<Link :href="route('password.request')" class="forget-password text-dark form-footer-right">{{ t('forgot_password') }}</Link>
									</div>
									<button type="submit" class="btn btn-dark btn-md w-100" :disabled="form.processing || submitting">
										<span v-if="form.processing || submitting">{{ t('logging_in') }}</span>
										<span v-else>{{ t('login') }}</span>
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
