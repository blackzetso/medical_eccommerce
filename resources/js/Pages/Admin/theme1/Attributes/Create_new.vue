<script setup>
import { ref, computed, onMounted } from 'vue'
import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import Swal from 'sweetalert2'

const form = useForm({
  name: '',
  slug: '',
  type: 'select',
  description: '',
  is_required: false,
  status: true,
  sort_order: 0,
  values: [
    { value: '', label: '', color_code: '', sort_order: 0 }
  ]
})

// إضافة قيمة جديدة
const addValue = () => {
  form.values.push({
    value: '',
    label: '',
    color_code: '',
    sort_order: form.values.length
  })
}

// حذف قيمة
const removeValue = (index) => {
  if (form.values.length > 1) {
    form.values.splice(index, 1)
  }
}

// توليد Slug تلقائياً
const generateSlug = () => {
  if (form.name) {
    form.slug = form.name
      .toLowerCase()
      .replace(/[\u0600-\u06FF]/g, '') // إزالة الأحرف العربية
      .replace(/[^\w\s-]/g, '') // إزالة الرموز الخاصة
      .replace(/\s+/g, '-') // استبدال المسافات بـ -
      .trim()
  }
}

// حفظ النموذج
function saveForm() {
  form.post(route('admin.attributes.store'), {
    onSuccess: () => {
      Swal.fire({
        title: 'تم بنجاح!',
        text: 'تم إنشاء الخاصية بنجاح',
        icon: 'success',
        confirmButtonText: 'حسناً'
      })
    },
    onError: () => {
      Swal.fire({
        title: 'خطأ!',
        text: 'حدث خطأ أثناء الحفظ',
        icon: 'error',
        confirmButtonText: 'حسناً'
      })
    }
  })
}
</script>

<template>
  <Head title="Add Attribute" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <div class="card-body px-1 px-sm-4">
        <h4>إضافة خاصية جديدة</h4>
        <Link :href="route('admin.attributes.index')">
          <i class="fas fa-arrow-left"></i> رجوع
        </Link>
        <hr />

        <div class="row g-4">
          <!-- اسم الخاصية -->
          <div class="col-md-6">
            <label class="form-label">اسم الخاصية *</label>
            <input
              class="form-control"
              v-model="form.name"
              @input="generateSlug"
              type="text"
              placeholder="مثال: الحجم، الذاكرة، اللون"
            />
            <div v-if="form.errors.name" class="text-danger">{{ form.errors.name }}</div>
          </div>

          <!-- الرابط الثابت -->
          <div class="col-md-6">
            <label class="form-label">الرابط الثابت (Slug)</label>
            <input
              class="form-control"
              v-model="form.slug"
              type="text"
              placeholder="size, memory, color"
            />
            <div v-if="form.errors.slug" class="text-danger">{{ form.errors.slug }}</div>
          </div>

          <!-- نوع الحقل -->
          <div class="col-md-4">
            <label class="form-label">نوع الحقل *</label>
            <select class="form-select" v-model="form.type">
              <option value="select">قائمة منسدلة</option>
              <option value="radio">اختيار واحد</option>
              <option value="checkbox">اختيار متعدد</option>
            </select>
            <div v-if="form.errors.type" class="text-danger">{{ form.errors.type }}</div>
          </div>

          <!-- الترتيب -->
          <div class="col-md-4">
            <label class="form-label">ترتيب العرض</label>
            <input
              class="form-control"
              v-model="form.sort_order"
              type="number"
              placeholder="0"
            />
            <div v-if="form.errors.sort_order" class="text-danger">{{ form.errors.sort_order }}</div>
          </div>

          <!-- مطلوبة -->
          <div class="col-md-4">
            <label class="form-label">حالة الخاصية</label>
            <div>
              <div class="form-check form-check-inline">
                <input
                  class="form-check-input"
                  type="checkbox"
                  v-model="form.is_required"
                  id="is_required"
                />
                <label class="form-check-label" for="is_required">
                  مطلوبة للمنتج
                </label>
              </div>
              <div class="form-check form-check-inline">
                <input
                  class="form-check-input"
                  type="checkbox"
                  v-model="form.status"
                  id="status"
                />
                <label class="form-check-label" for="status">
                  مفعلة
                </label>
              </div>
            </div>
          </div>

          <!-- الوصف -->
          <div class="col-12">
            <label class="form-label">الوصف</label>
            <textarea
              class="form-control"
              v-model="form.description"
              rows="3"
              placeholder="وصف اختياري للخاصية"
            ></textarea>
            <div v-if="form.errors.description" class="text-danger">{{ form.errors.description }}</div>
          </div>

          <!-- القيم -->
          <div class="col-12">
            <h5>قيم الخاصية</h5>
            <p class="text-muted">أضف القيم الممكنة لهذه الخاصية (مثل: X, 2X, 3X)</p>
          </div>

          <div class="col-12">
            <div v-for="(value, index) in form.values" :key="index" class="row g-2 mb-3 border p-3 rounded">

              <!-- القيمة -->
              <div class="col-md-3">
                <label class="form-label">القيمة *</label>
                <input
                  class="form-control"
                  v-model="value.value"
                  type="text"
                  placeholder="X, 2GB, أحمر"
                />
                <div v-if="form.errors[`values.${index}.value`]" class="text-danger">
                  {{ form.errors[`values.${index}.value`] }}
                </div>
              </div>

              <!-- التسمية -->
              <div class="col-md-3">
                <label class="form-label">التسمية (اختياري)</label>
                <input
                  class="form-control"
                  v-model="value.label"
                  type="text"
                  placeholder="حجم X, ذاكرة 2 جيجا"
                />
              </div>

              <!-- اللون (إذا كان نوع اللون) -->
              <div class="col-md-2" v-if="form.name.includes('لون') || form.name.includes('color')">
                <label class="form-label">كود اللون</label>
                <input
                  class="form-control"
                  v-model="value.color_code"
                  type="color"
                />
              </div>

              <!-- الترتيب -->
              <div class="col-md-2">
                <label class="form-label">الترتيب</label>
                <input
                  class="form-control"
                  v-model="value.sort_order"
                  type="number"
                  :placeholder="index"
                />
              </div>

              <!-- إجراءات -->
              <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div>
                  <button
                    type="button"
                    class="btn btn-danger btn-sm"
                    @click="removeValue(index)"
                    :disabled="form.values.length === 1"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </div>
            </div>

            <!-- إضافة قيمة جديدة -->
            <button
              type="button"
              class="btn btn-outline-primary"
              @click="addValue"
            >
              <i class="fas fa-plus me-2"></i>
              إضافة قيمة جديدة
            </button>
          </div>

          <!-- زر الحفظ -->
          <div class="d-flex justify-content-end mt-3">
            <button
              type="button"
              class="btn btn-primary mb-0"
              :disabled="form.processing"
              @click="saveForm"
            >
              حفظ الخاصية
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
