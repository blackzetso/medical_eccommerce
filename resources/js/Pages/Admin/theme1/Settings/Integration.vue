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
    orgasoft_enabled:    props.settings?.orgasoft_enabled === '1' || props.settings?.orgasoft_enabled === true,
    orgasoft_url:        props.settings?.orgasoft_url        || 'http://127.0.0.1:8080',
    orgasoft_api_key:    props.settings?.orgasoft_api_key    || 'AHMED_ADEL',
    orgasoft_account_id: props.settings?.orgasoft_account_id || '',
  }
})

function submit() {
  // تحويل الـ boolean لـ string للتخزين في DB
  const payload = {
    ...form.settings,
    orgasoft_enabled: form.settings.orgasoft_enabled ? '1' : '0',
  }

  useForm({ settings: payload }).put(route('admin.settings.integration.update'), {
    onSuccess: () => {
      toast.success('تم تحديث إعدادات التكامل بنجاح', {
        position: 'top-right',
        autoClose: 3000,
      })
    },
    onError: () => {
      toast.error('حدث خطأ أثناء الحفظ', { position: 'top-right' })
    },
  })
}
</script>

<template>
  <Head title="إعدادات التكامل مع OrgaSoft" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <div class="row">
        <div class="col-12 mb-3">
          <h1 class="h3 mb-2 mb-sm-0">إعدادات التكامل مع OrgaSoft</h1>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-xl-3">
          <Sidebar />
        </div>

        <div class="col-xl-9">
          <!-- بطاقة الإعدادات -->
          <div class="card shadow">
            <div class="card-header border-bottom p-4">
              <h5 class="card-header-title mb-0">
                <i class="fas fa-plug me-2"></i>
                ربط الموقع بسيستم الديسكتوب (OrgaSoft)
              </h5>
            </div>
            <div class="card-body p-4">
              <form @submit.prevent="submit">
                <!-- تفعيل/تعطيل المزامنة -->
                <div class="row g-4 mb-4">
                  <div class="col-12">
                    <div class="form-check form-switch">
                      <input
                        class="form-check-input"
                        type="checkbox"
                        id="orgasoft_enabled"
                        v-model="form.settings.orgasoft_enabled"
                      />
                      <label class="form-check-label fw-semibold" for="orgasoft_enabled">
                        تفعيل المزامنة مع OrgaSoft
                      </label>
                    </div>
                    <small class="text-muted d-block mt-1">
                      عند التفعيل، أي أوردر جديد من الموقع سيُرسَل تلقائياً كفاتورة لسيستم الديسكتوب.
                    </small>
                  </div>
                </div>

                <hr class="mb-4" />

                <div class="row g-4">
                  <!-- رابط الديسكتوب -->
                  <div class="col-md-8">
                    <label class="form-label">
                      رابط سيستم الديسكتوب (URL)
                      <span class="text-danger">*</span>
                    </label>
                    <input
                      type="url"
                      class="form-control"
                      v-model="form.settings.orgasoft_url"
                      placeholder="http://127.0.0.1:8080"
                      required
                    />
                    <small class="text-muted">
                      الـ URL اللي السيستم شغال عليه على جهاز السيرفر.
                    </small>
                    <div v-if="form.errors['settings.orgasoft_url']" class="text-danger small mt-1">
                      {{ form.errors['settings.orgasoft_url'] }}
                    </div>
                  </div>

                  <!-- رقم الحساب -->
                  <div class="col-md-4">
                    <label class="form-label">رقم الحساب (ACCOUNT_ID)</label>
                    <input
                      type="text"
                      class="form-control"
                      v-model="form.settings.orgasoft_account_id"
                      placeholder="139"
                    />
                    <small class="text-muted">يُستخدم في header الطلبات.</small>
                  </div>

                  <!-- مفتاح الـ API -->
                  <div class="col-md-6">
                    <label class="form-label">
                      مفتاح الـ API (API-KEY)
                      <span class="text-danger">*</span>
                    </label>
                    <input
                      type="text"
                      class="form-control"
                      v-model="form.settings.orgasoft_api_key"
                      placeholder="AHMED_ADEL"
                      required
                    />
                    <small class="text-muted">
                      يُستخدم في header كل الطلبات بين الموقع والديسكتوب.
                    </small>
                    <div v-if="form.errors['settings.orgasoft_api_key']" class="text-danger small mt-1">
                      {{ form.errors['settings.orgasoft_api_key'] }}
                    </div>
                  </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                  <button type="submit" class="btn btn-primary" :disabled="form.processing">
                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
                    <i v-else class="fas fa-save me-2"></i>
                    حفظ التغييرات
                  </button>
                </div>
              </form>
            </div>
          </div>

          <!-- بطاقة توثيق الـ API -->
          <div class="card shadow mt-4">
            <div class="card-header border-bottom p-4">
              <h5 class="card-header-title mb-0">
                <i class="fas fa-book me-2"></i>
                توثيق الـ API — للديسكتوب
              </h5>
            </div>
            <div class="card-body p-4">
              <p class="text-muted mb-3">
                الديسكتوب يقدر يستخدم الـ endpoints دي لمزامنة الأوردرات مع الموقع.
                كل الطلبات تحتاج header: <code>API-KEY: {{ form.settings.orgasoft_api_key || 'AHMED_ADEL' }}</code>
              </p>

              <div class="table-responsive">
                <table class="table table-bordered table-sm">
                  <thead class="table-light">
                    <tr>
                      <th>Endpoint</th>
                      <th>Method</th>
                      <th>الغرض</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td><code>/api/desktop/order</code></td>
                      <td><span class="badge bg-success">POST</span></td>
                      <td>إرسال فاتورة/أوردر جديد من الديسكتوب للموقع</td>
                    </tr>
                    <tr>
                      <td><code>/api/desktop/orders?since=2025-01-01</code></td>
                      <td><span class="badge bg-primary">GET</span></td>
                      <td>جلب الأوردرات المنشأة من الموقع (للمزامنة)</td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <h6 class="mt-4 mb-2">مثال — Body الخاص بـ POST /api/desktop/order:</h6>
              <pre class="bg-light p-3 rounded border small"><code>[
  {
    "INVOICES_H_ID": 4,
    "ACCOUNT_ID": 1243,
    "ACCOUNT_NAME": "Ahmed Ali",
    "PROD_ID": 107189,
    "DISCOUNT1": 2,
    "TOTAL_QTY": 10
  }
]</code></pre>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
