<script setup>
import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'
import Sidebar from '@/Pages/Admin/theme1/Settings/Partials/Sidebar.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { toast } from 'vue3-toastify'

const props = defineProps({
  settings: Object
})

const form = useForm({
  settings: {
    mail_mailer: props.settings?.mail_mailer || 'smtp',
    mail_host: props.settings?.mail_host || '',
    mail_port: props.settings?.mail_port || '587',
    mail_username: props.settings?.mail_username || '',
    mail_password: props.settings?.mail_password || '',
    mail_encryption: props.settings?.mail_encryption || 'tls',
    mail_from_address: props.settings?.mail_from_address || '',
    mail_from_name: props.settings?.mail_from_name || '',
  }
})

function submit() {
  form.put(route('admin.settings.update'), {
    onSuccess: () => {
      toast.success("تم تحديث الإعدادات بنجاح")
    }
  })
}
</script>

<template>
  <Head title="إعدادات البريد الإلكتروني" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <div class="row">
        <div class="col-12 mb-3">
          <h1 class="h3 mb-2 mb-sm-0">إعدادات البريد الإلكتروني</h1>
        </div>
      </div>
      <div class="row g-4">
        <div class="col-xl-3">
          <Sidebar/>
        </div>
        <div class="col-xl-9">
          <div class="card shadow">
            <div class="card-header border-bottom p-4">
              <h5 class="card-header-title mb-0">إعدادات SMTP</h5>
            </div>
            <div class="card-body p-4">
              <form @submit.prevent="submit">
                <div class="row g-4">
                  <div class="col-md-6">
                    <label class="form-label">SMTP Host</label>
                    <input type="text" class="form-control" v-model="form.settings.mail_host" />
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">SMTP Port</label>
                    <input type="text" class="form-control" v-model="form.settings.mail_port" />
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">SMTP Username</label>
                    <input type="text" class="form-control" v-model="form.settings.mail_username" />
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">SMTP Password</label>
                    <input type="password" class="form-control" v-model="form.settings.mail_password" />
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Encryption</label>
                    <select class="form-select" v-model="form.settings.mail_encryption">
                      <option value="tls">TLS</option>
                      <option value="ssl">SSL</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">From Address</label>
                    <input type="email" class="form-control" v-model="form.settings.mail_from_address" />
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">From Name</label>
                    <input type="text" class="form-control" v-model="form.settings.mail_from_name" />
                  </div>
                </div>
                <div class="d-flex justify-content-end mt-4">
                  <button type="submit" class="btn btn-primary" :disabled="form.processing">حفظ</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

