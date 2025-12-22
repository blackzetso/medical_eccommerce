<script setup>
import AppLayout from '@/Pages/Admin/theme1/Layout/App.vue'
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import Swal from 'sweetalert2'
import { useTranslations } from '@/composables/translations'
import { onMounted, computed, watch } from 'vue'

const props = defineProps({
  role: {
    type: Object,
    default: null,
  },
  groupedPermissions: {
    type: Array,
    default: () => [],
  },
})

const form = useForm({
  name: props.role?.name ?? '',
  permissions: props.role?.permissions?.map((p) => p.id) ?? [],
})

const isEdit = !!props.role

const { t } = useTranslations()

// Check if we're in development mode (for debugging)
const isDev = import.meta.env.DEV

const moduleLabel = (module) => t(module) || module.replaceAll('_', ' ')
const permissionLabel = (permissionName) => t(permissionName) || permissionName.replaceAll('_', ' ')

// Filter out duplicate permissions by ID across ALL groups
const uniqueGroupedPermissions = computed(() => {
  if (!props.groupedPermissions || props.groupedPermissions.length === 0) {
    return []
  }
  
  // Track all seen IDs globally across all groups
  const globalSeenIds = new Set()
  const result = []
  
  for (const group of props.groupedPermissions) {
    const uniquePermissions = []
    const groupSeenIds = new Set()
    
    for (const permission of group.permissions || []) {
      // Skip if this ID was seen in any previous group
      if (globalSeenIds.has(permission.id)) {
        if (import.meta.env.DEV) {
          console.warn(`Duplicate permission ID ${permission.id} (${permission.name}) found across groups`)
        }
        continue
      }
      
      // Skip if this ID was seen in current group
      if (groupSeenIds.has(permission.id)) {
        if (import.meta.env.DEV) {
          console.warn(`Duplicate permission ID ${permission.id} (${permission.name}) found in module ${group.module}`)
        }
        continue
      }
      
      groupSeenIds.add(permission.id)
      globalSeenIds.add(permission.id)
      uniquePermissions.push(permission)
    }
    
    if (uniquePermissions.length > 0) {
      result.push({
        ...group,
        permissions: uniquePermissions,
      })
    }
  }
  
  if (import.meta.env.DEV) {
    console.log('Unique Grouped Permissions (Filtered):', result)
    console.log('Total unique permission IDs:', globalSeenIds.size)
  }
  
  return result
})


const page = usePage()

// Watch for flash messages
watch(() => page.props.flash, (flash) => {
  if (flash?.success) {
    Swal.fire({
      icon: 'success',
      title: 'تم الحفظ',
      text: flash.success,
      timer: 2000,
    })
  }
  if (flash?.error) {
    Swal.fire({
      icon: 'error',
      title: 'خطأ',
      text: flash.error,
    })
  }
}, { immediate: true, deep: true })

// Also check on mount in case flash message is already present
onMounted(() => {
  if (import.meta.env.DEV) {
    console.log('Grouped Permissions (Raw):', props.groupedPermissions)
    console.log('Grouped Permissions (Unique):', uniqueGroupedPermissions.value)
    console.log('Grouped Permissions Length:', props.groupedPermissions?.length)
  }
  
  // Check for flash messages on mount
  const flash = page.props.flash
  if (flash?.success) {
    Swal.fire({
      icon: 'success',
      title: 'تم الحفظ',
      text: flash.success,
      timer: 2000,
    })
  }
  if (flash?.error) {
    Swal.fire({
      icon: 'error',
      title: 'خطأ',
      text: flash.error,
    })
  }
})

const submit = () => {
  // Clean permissions array - remove any null, undefined, or empty values
  if (form.permissions && Array.isArray(form.permissions)) {
    form.permissions = form.permissions.filter(
      (id) => id !== null && id !== undefined && id !== ''
    )
  }
  
  if (isEdit) {
    form.put(route('admin.roles.update', props.role.id), {
      preserveScroll: true,
      onSuccess: () => {
        // Success is handled by flash message watcher
      },
      onError: (errors) => {
        console.error('Validation errors:', errors)
        
        // Build detailed error message
        let errorMessage = 'يرجى التحقق من البيانات المدخلة:\n\n'
        if (errors.name) {
          errorMessage += `• الاسم: ${errors.name}\n`
        }
        if (errors.permissions) {
          errorMessage += `• الصلاحيات: ${errors.permissions}\n`
        }
        if (errors['permissions.0']) {
          errorMessage += `• الصلاحيات: ${errors['permissions.0']}\n`
        }
        
        // If no specific errors, show generic message
        if (errorMessage === 'يرجى التحقق من البيانات المدخلة:\n\n') {
          errorMessage = 'حدث خطأ أثناء الحفظ. يرجى المحاولة مرة أخرى.'
        }
        
        Swal.fire({
          icon: 'error',
          title: 'خطأ في التحقق',
          text: errorMessage,
          html: errorMessage.replace(/\n/g, '<br>'),
        })
      },
    })
  } else {
    form.post(route('admin.roles.store'), {
      preserveScroll: true,
      onSuccess: () => {
        // Success is handled by flash message watcher
      },
      onError: (errors) => {
        console.error('Validation errors:', errors)
        
        // Build detailed error message
        let errorMessage = 'يرجى التحقق من البيانات المدخلة:\n\n'
        if (errors.name) {
          errorMessage += `• الاسم: ${errors.name}\n`
        }
        if (errors.permissions) {
          errorMessage += `• الصلاحيات: ${errors.permissions}\n`
        }
        if (errors['permissions.0']) {
          errorMessage += `• الصلاحيات: ${errors['permissions.0']}\n`
        }
        
        // If no specific errors, show generic message
        if (errorMessage === 'يرجى التحقق من البيانات المدخلة:\n\n') {
          errorMessage = 'حدث خطأ أثناء الحفظ. يرجى المحاولة مرة أخرى.'
        }
        
        Swal.fire({
          icon: 'error',
          title: 'خطأ في التحقق',
          text: errorMessage,
          html: errorMessage.replace(/\n/g, '<br>'),
        })
      },
    })
  }
}
</script>

<template>
  <Head :title="isEdit ? 'تعديل دور' : 'إضافة دور'" />
  <AppLayout>
    <div class="page-content-wrapper border">
      <div class="row mb-3 align-items-center">
        <div class="col-md-6">
          <h1 class="h3 mb-0">
            {{ isEdit ? 'تعديل دور' : 'إضافة دور جديد' }}
          </h1>
        </div>
        <div class="col-md-6 text-md-end mt-2 mt-md-0">
          <Link :href="route('admin.roles.index')" class="btn btn-light">
            رجوع
          </Link>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-12">
          <div class="card card-body bg-transparent border">
            <form @submit.prevent="submit" class="row g-3">
              <div class="col-12">
                <label class="form-label">اسم الدور <span class="text-danger">*</span></label>
                <input
                  v-model="form.name"
                  type="text"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors.name }"
                  placeholder="مثال: admin"
                  required
                />
                <div class="invalid-feedback" v-if="form.errors.name">
                  {{ form.errors.name }}
                </div>
              </div>

              <div class="col-12">
                <label class="form-label">الصلاحيات</label>
                <div v-if="!uniqueGroupedPermissions || uniqueGroupedPermissions.length === 0" class="alert alert-warning">
                  <i class="bi bi-exclamation-triangle me-2"></i>
                  لا توجد صلاحيات متاحة. يرجى التأكد من تشغيل PermissionSeeder لإضافة الصلاحيات.
                </div>
                <div v-else class="row">
                  <div
                    class="col-12 mb-3"
                    v-for="group in uniqueGroupedPermissions"
                    :key="`${group.module}-${group.permissions.length}`"
                  >
                    <div class="mb-2 fw-semibold text-primary">
                      {{ moduleLabel(group.module) }}
                      <span v-if="isDev" class="badge bg-info ms-2">
                        {{ group.permissions.length }} صلاحيات
                      </span>
                    </div>
                    <div class="row">
                      <div
                        class="col-md-6 col-lg-4 mb-2"
                        v-for="(permission, permIndex) in group.permissions"
                        :key="`${group.module}-${permission.id}-${permIndex}`"
                      >
                        <div class="form-check">
                          <input
                            class="form-check-input"
                            type="checkbox"
                            :id="`perm-${permission.id}`"
                            :value="permission.id"
                            v-model="form.permissions"
                          />
                          <label class="form-check-label" :for="`perm-${permission.id}`">
                            {{ permissionLabel(permission.name) }}
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="text-danger small" v-if="form.errors.permissions">
                  {{ form.errors.permissions }}
                </div>
              </div>

              <div class="col-12 d-flex gap-2">
                <button class="btn btn-success" type="submit" :disabled="form.processing">
                  {{ isEdit ? 'تحديث' : 'حفظ' }}
                </button>
                <Link :href="route('admin.roles.index')" class="btn btn-outline-secondary">
                  إلغاء
                </Link>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
