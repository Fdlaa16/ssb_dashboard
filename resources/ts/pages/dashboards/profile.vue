<script setup lang="ts">
import { useAuthStore } from '@/stores/auth';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import type { StructureData } from '../../../views/dashboards/structure/types';

const authStore = useAuthStore()
const user = computed(() => authStore.user)
const currentTab = ref('biodata')
const loading = ref(false)
const error = ref<string | null>(null)
const isFlatSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref<'success' | 'error'>('success')

const isPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)
const props = defineProps<{ data?: StructureData }>()
const emit = defineEmits(['submit', 'update:data'])

// Form validation state
const isFormValid = ref(false)
const isSubmitting = ref(false)

// Form reference
const formRef = ref()

// Validation rules
const emailRules = [
  (v: string) => !!v || 'Email wajib diisi',
  (v: string) => /.+@.+\..+/.test(v) || 'Format email tidak valid'
]

const nameRules = [
  (v: string) => !!v || 'Nama lengkap wajib diisi',
  (v: string) => v.length >= 2 || 'Nama minimal 2 karakter'
]

const dateRules = [
  (v: string) => !!v || 'Tanggal lahir wajib diisi'
]

const departmentRules = [
  (v: string) => !!v || 'Kategori wajib dipilih'
]

const rulesPassword = {
  minLength: (value: string) => !value || value.length >= 8 || 'Minimal 8 karakter',
}

const rulesConfirmPassword = {
  sameAsPassword: (value: string) => !localData.value.user.new_password || value === localData.value.user.new_password || 'Konfirmasi password tidak cocok',
}

// Initialize default structure
const getDefaultData = (): StructureData => ({
  id: null,
  code: '',
  name: '',
  date_of_birth: '',
  department: '',
  avatar: null,
  user: {
    email: '',
    new_password: '',
    confirm_password: ''
  }
})

const localData = ref<StructureData>(
  props.data ? { ...props.data } : getDefaultData()
)

const originalData = ref<StructureData>(getDefaultData())

const avatarPreview = ref<string | null>(
  props.data?.avatar?.url ? getImageUrl(props.data.avatar.url) : null
)

// Departments options
const departments = [
  { title: 'Pilih Posisi', value: '' },
  { title: 'Ketua Umum', value: 'chief' },
  { title: 'Official', value: 'official' }, 
  { title: 'Admin', value: 'admin' }, 
  { title: 'Pelatih', value: 'coach' }
]

// Utility functions
const getImageUrl = (path: string) => {  
  return import.meta.env.VITE_APP_URL + path
}

const showNotification = (message: string, type: 'success' | 'error' = 'success') => {
  snackbarMessage.value = message
  snackbarColor.value = type
  isFlatSnackbarVisible.value = true
}

// Data fetching
const fetchProfile = async () => {
  loading.value = true
  error.value = null

  try {
    const res = await $api('profile')
    const profileData = res.data
    
    localData.value = {
      ...profileData,
      user: {
        email: profileData.user?.email || '',
        new_password: '',
        confirm_password: ''
      }
    }
    
    // Store original data for comparison
    originalData.value = JSON.parse(JSON.stringify(localData.value))
    
    // Set avatar preview
    if (profileData.avatar?.url) {
      avatarPreview.value = getImageUrl(profileData.avatar.url)
    }    
  } catch (err: any) {
    error.value = err.message || 'Gagal mengambil data profil'
    showNotification(error.value, 'error')
    console.error('Fetch Profile Error:', err)
  } finally {
    loading.value = false
  }
}

// Form submission
const submitForm = async () => {
  const isValid = await validateForm()
  if (!isValid) {
    showNotification('Harap perbaiki kesalahan pada form', 'error')
    return
  }

  isSubmitting.value = true
  loading.value = true

  try {
    const formData = new FormData()
    formData.append('_method', 'PUT')

    formData.append('email', localData.value.user.email || '')
    if (localData.value.user.new_password) {
      formData.append('new_password', localData.value.user.new_password)
    }
    formData.append('name', localData.value.name || '')
    formData.append('date_of_birth', localData.value.date_of_birth || '')
    formData.append('department', localData.value.department || '')

    if (localData.value.avatar instanceof File) {
      formData.append('avatar', localData.value.avatar)
    }

    const res = await $api('profile-update', {
      method: 'POST',
      body: formData,
    })

    originalData.value = JSON.parse(JSON.stringify(localData.value))
    
    showNotification('Profil berhasil diperbarui!', 'success')
    
    emit('submit', res.data)

    setTimeout(() => {
      window.location.reload()
    }, 1000)
  } catch (err: any) {
    console.error('Submit Error:', err)
    
    const errors = err?.data?.errors
    if (err?.status === 422 && errors) {
      const messages = Object.values(errors).flat()
      showNotification('Validasi gagal: ' + messages.join(', '), 'error')
    } else {
      showNotification('Gagal menyimpan data: ' + (err?.message || 'Unknown error'), 'error')
    }
  } finally {
    isSubmitting.value = false
    loading.value = false
  }
}

// Form validation
const validateForm = async () => {
  if (!formRef.value) return false
  
  const { valid } = await formRef.value.validate()
  return valid
}

// Watchers
watch(
  () => props.data,
  (newData) => {
    if (newData) {
      localData.value = { ...newData }
      originalData.value = JSON.parse(JSON.stringify(newData))
      
      if (newData.avatar?.url) {
        avatarPreview.value = getImageUrl(newData.avatar.url)
      }
      
      nextTick(() => {
        formRef.value?.resetValidation()
      })
    }
  },
  { deep: true, immediate: true }
)

watch(() => localData.value.avatar, (newAvatar: any) => {
  if (newAvatar instanceof File) {
    // Revoke previous blob URL to prevent memory leaks
    if (avatarPreview.value?.startsWith('blob:')) {
      URL.revokeObjectURL(avatarPreview.value)
    }
    avatarPreview.value = URL.createObjectURL(newAvatar)
  } else if (newAvatar?.url) {
    avatarPreview.value = getImageUrl(newAvatar.url)
  } else {
    // Revoke blob URL when avatar is cleared
    if (avatarPreview.value?.startsWith('blob:')) {
      URL.revokeObjectURL(avatarPreview.value)
    }
    avatarPreview.value = null
  }
})

watch(localData, (newVal) => {
  emit('update:data', newVal)
}, { deep: true })

// Lifecycle
onMounted(() => {
  // Auto-fetch profile data if no props data provided
  if (!props.data) {
    fetchProfile()
  }
})

onBeforeUnmount(() => {
  // Clean up blob URLs
  if (avatarPreview.value?.startsWith('blob:')) {
    URL.revokeObjectURL(avatarPreview.value)
  }
})

// Expose functions for parent component
defineExpose({
  submitForm,
  validateForm,
  fetchProfile,
  localData: readonly(localData)
})
</script>

<template>
  <VForm ref="formRef" v-model="isFormValid" @submit.prevent="submitForm">
    <div class="d-flex flex-column gap-6 mb-6">
      <!-- Loading State -->
      <VProgressLinear 
        v-if="loading && !isSubmitting"
        indeterminate 
        color="primary"
        class="mb-4"
      />

      <!-- Error State -->
      <VAlert
        v-if="error"
        type="error"
        variant="tonal"
        class="mb-4"
        closable
        @click:close="error = null"
      >
        <VIcon icon="tabler-alert-circle" class="me-2" />
        {{ error }}
      </VAlert>

      <VCard>
        <!-- Tab Navigation -->
        <VTabs v-model="currentTab" grow stacked>
          <VTab value="biodata">
            <VIcon icon="tabler-user" class="mb-2" />
            <span>Biodata</span>
          </VTab>
        </VTabs>

        <VCardText>
          <VWindow v-model="currentTab">
            <!-- Biodata Tab -->
            <VWindowItem value="biodata">
              <div class="d-flex justify-end flex-column rounded bg-var-theme-background flex-sm-row gap-6 pa-6 mb-6">
                <div class="d-flex align-center align-end app-logo">
                  <img
                    src="/storage/logo/LOGOSSB.png"
                    alt="Logo SSB"
                    style="height: 40px;"
                    class="me-2"
                  />
                  <h6 class="app-logo-title">PUTRA MUDA BALARAJA</h6>
                </div>
              </div>

              <VRow>
                <!-- Avatar Section -->
                <VCol cols="4" class="d-flex flex-column align-center justify-center">
                  <div style="width: 100%; max-width: 300px;" class="text-center justify-center">
                    <img
                      v-if="avatarPreview"
                      :src="avatarPreview"
                      alt="Preview Avatar"
                      style="width: 100%; border-radius: 8px; margin-bottom: 1rem;"
                    />

                    <VFileInput
                      v-model="localData.avatar"
                      label="Ganti Foto"
                      accept="image/png, image/jpeg, image/bmp"
                      density="comfortable"
                    />
                  </div>
                </VCol>

                <!-- Form Fields -->
                <VCol cols="12" md="8">
                  <VRow>
                    <!-- Email -->
                    <VCol cols="12">
                      <AppTextField
                        v-model="localData.user.email"
                        label="Email"
                        placeholder="Contoh: admin@gmail.com"
                        :rules="emailRules"
                        type="email"
                        required
                      />
                    </VCol>

                    <!-- New Password -->
                    <VCol cols="12" md="6">
                      <AppTextField
                        v-model="localData.user.new_password"
                        label="Password Baru (Opsional)"
                        placeholder="············"
                        :type="isPasswordVisible ? 'text' : 'password'"
                        autocomplete="new-password"
                        :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                        @click:append-inner="isPasswordVisible = !isPasswordVisible"
                        :rules="[rulesPassword.minLength]"
                      />
                    </VCol>

                    <!-- Confirm Password -->
                    <VCol cols="12" md="6">
                      <AppTextField
                        v-model="localData.user.confirm_password"
                        label="Konfirmasi Password"
                        placeholder="············"
                        :type="isConfirmPasswordVisible ? 'text' : 'password'"
                        autocomplete="new-password"
                        :append-inner-icon="isConfirmPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                        @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
                        :rules="[rulesConfirmPassword.sameAsPassword]"
                        :disabled="!localData.user.new_password"
                      />
                    </VCol>

                    <!-- Full Name -->
                    <VCol cols="12">
                      <AppTextField
                        v-model="localData.name"
                        label="Nama Lengkap"
                        placeholder="Contoh: Budi Setiawan"
                        :rules="nameRules"
                        required
                      />
                    </VCol>

                    <!-- Date of Birth -->
                    <VCol cols="12" md="6">
                      <AppDateTimePicker
                        v-model="localData.date_of_birth"
                        label="Tanggal Lahir"
                        placeholder="Pilih Tanggal Lahir"
                        :rules="dateRules"
                        required
                      />
                    </VCol>

                    <!-- Department -->
                    <VCol cols="12" md="6">
                      <AppSelect
                        label="Department"
                        v-model="localData.department"
                        :items="departments"
                        :rules="departmentRules"
                        clearable
                        clear-icon="tabler-x"
                        single-line
                        required
                      />
                    </VCol>
                  </VRow>
                </VCol>
              </VRow>
            </VWindowItem>
          </VWindow>
        </VCardText>

        <VCol cols="12" class="d-flex justify-end">
          <VBtn
            color="primary"
            @click="submitForm"
          >
            Simpan
          </VBtn>
        </VCol>
      </VCard>
    </div>

    <!-- Snackbar -->
    <VSnackbar
      v-model="isFlatSnackbarVisible"
      :color="snackbarColor"
      location="bottom start"
      variant="flat"
      timeout="3000"
    >
      {{ snackbarMessage }}
    </VSnackbar>
  </VForm>
</template>

<style scoped>
.app-logo-title {
  color: var(--v-theme-primary);
  font-weight: 600;
}
</style>
