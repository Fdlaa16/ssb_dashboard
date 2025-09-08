<script setup lang="ts">
import { useConfigStore } from '@/@core/stores/config';
import { useAuthStore } from '@/stores/auth';
import Footer from '@/views/front-pages/front-page-footer.vue';
import Navbar from '@/views/front-pages/front-page-navbar.vue';
import { computed, nextTick, onBeforeUnmount, onMounted, readonly, ref, watch } from 'vue';

const route = useRoute()
const router = useRouter()
const structureId = computed(() => route.params.id)

const authStore = useAuthStore()
const user = computed(() => authStore.user)

const store = useConfigStore()
store.skin = 'default'
definePage({
  meta: {
    layout: 'blank',
    public: true,
  },
})

const props = defineProps<{
  data?: any
}>()

const currentTab = ref('biodata')
const loading = ref(false)
const error = ref<string | null>(null)
const isFlatSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref<'success' | 'error'>('success')
const detail = ref<any>(null)

const isPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)
const emit = defineEmits(['submit', 'update:data'])

const isFormValid = ref(false)
const isSubmitting = ref(false)

const formRef = ref()

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

interface DetailStructure {
  id: number | null
  code: string
  name: string
  date_of_birth: string
  department: string
  avatar: File | { url: string } | null
  user: {
    email: string
    new_password: string
    confirm_password: string
  }
}

const getDefaultData = (): DetailStructure => ({
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

const localData = ref<DetailStructure>(getDefaultData())
const originalData = ref<DetailStructure>(getDefaultData())

const avatarPreview = ref<string | null>(null)

const departments = [
  { title: 'Ketua Umum', value: 'chief' },
  { title: 'Official', value: 'official' }, 
  { title: 'Admin', value: 'admin' }, 
  { title: 'Pelatih', value: 'coach' }
]

const rulesConfirmPassword = computed(() => ({
  sameAsPassword: (value: string) => !localData.value.user.new_password || value === localData.value.user.new_password || 'Konfirmasi password tidak cocok',
}))

const getImageUrl = (path: string) => {  
  return import.meta.env.VITE_APP_URL + path
}

const showNotification = (message: string, type: 'success' | 'error' = 'success') => {
  snackbarMessage.value = message
  snackbarColor.value = type
  isFlatSnackbarVisible.value = true
}

const fetchProfile = async () => {
  loading.value = true
  error.value = null

  try {
    const response = await $api(`company/structure/${structureId.value}`, {
      method: 'GET'
    })

    detail.value = response.data
    localData.value = {
      id: response.data.id,
      code: response.data.code || '',
      name: response.data.name || '',
      date_of_birth: response.data.date_of_birth || '',
      department: response.data.department || '',
      avatar: response.data.avatar || null,
      user: {
        email: response.data.user?.email || '',
        new_password: '',
        confirm_password: ''
      }
    }
    
    if (response.data.avatar?.url) {
      avatarPreview.value = getImageUrl(response.data.avatar.url)
    }
    
  } catch (err: any) {
    console.error('Fetch Structure Error:', err)
    error.value = err.message || 'Gagal memuat data pengurus'
    showNotification(error.value, 'error')
    
    if (err.status === 404) {
      setTimeout(() => {
        router.push({ name: 'structure' })
      }, 2000)
    }
  } finally {
    loading.value = false
  }
}

const validateForm = async () => {
  if (!formRef.value) return false
  
  const { valid } = await formRef.value.validate()
  return valid
}

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
    if (avatarPreview.value?.startsWith('blob:')) {
      URL.revokeObjectURL(avatarPreview.value)
    }
    avatarPreview.value = URL.createObjectURL(newAvatar)
  } else if (newAvatar?.url) {
    avatarPreview.value = getImageUrl(newAvatar.url)
  } else {
    if (avatarPreview.value?.startsWith('blob:')) {
      URL.revokeObjectURL(avatarPreview.value)
    }
    avatarPreview.value = null
  }
})

watch(localData, (newVal) => {
  emit('update:data', newVal)
}, { deep: true })

onMounted(() => {
  if (!props.data && structureId.value) {
    fetchProfile()
  }
})

onBeforeUnmount(() => {
  if (avatarPreview.value?.startsWith('blob:')) {
    URL.revokeObjectURL(avatarPreview.value)
  }
})

const getDepartmentTitle = (value) => {
  const found = departments.find(d => d.value === value)
  return found ? found.title : '-'
}

defineExpose({
  validateForm,
  fetchProfile,
  localData: readonly(localData)
})
</script>

<template>
  <div class="page-wrapper">
    <Navbar />
    
    <div class="main-content">
      <VContainer style="margin-top: 100px; padding-bottom: 2rem;">
        <VRow justify="center">
          <VCol cols="12" md="10">
            <template v-if="loading">
              <VAlert type="info" border="start" color="primary" variant="tonal">
                Memuat detail player...
              </VAlert>
            </template>
            
            <template v-else-if="error">
              <VAlert type="error" border="start" color="error" variant="tonal">
                {{ error }}
              </VAlert>
            </template>
            
            <template v-else>
                <VCard>
                  <VCardText>
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
                      <!-- Avatar Section - Kiri -->
                      <VCol cols="12" md="4" class="d-flex flex-column align-center justify-center">
                        <div style="width: 100%; max-width: 300px;" class="text-center justify-center">
                          <img
                            v-if="avatarPreview"
                            :src="avatarPreview"
                            alt="Avatar Player"
                            style="width: 100%; border-radius: 8px; margin-bottom: 1rem;"
                          />
                          <div v-else class="text-center text-grey">
                            <VIcon icon="tabler-user" size="100" />
                            <p>Tidak ada foto</p>
                          </div>
                        </div>
                      </VCol>

                      <!-- Data Section - Kanan -->
                      <VCol cols="12" md="8">
                        <VRow class="mb-3">
                          <VCol cols="12" sm="4" class="text-subtitle-2 font-weight-bold">Nama Lengkap:</VCol>
                          <VCol cols="12" sm="8" class="text-body-1">{{ localData.name || '-' }}</VCol>
                        </VRow>
                        
                        <VRow class="mb-3">
                          <VCol cols="12" sm="4" class="text-subtitle-2 font-weight-bold">Tanggal Lahir:</VCol>
                          <VCol cols="12" sm="8" class="text-body-1">{{ localData.date_of_birth || '-' }}</VCol>
                        </VRow>
                        
                        <VRow class="mb-3">
                          <VCol cols="12" sm="4" class="text-subtitle-2 font-weight-bold">Department:</VCol>
                          <VCol cols="12" sm="8" class="text-body-1">{{ getDepartmentTitle(localData.department) }}</VCol>
                        </VRow>
                      </VCol>
                    </VRow>
                  </VCardText>
                </VCard>

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
            </template>
          </VCol>
        </VRow>
      </VContainer>
    </div>
    
    <!-- Footer yang akan selalu di bawah -->
    <Footer />
  </div>
</template>

<style scoped>
.page-wrapper {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.main-content .v-container {
  flex: 1;
  display: flex;
  flex-direction: column;
}

html {
  scroll-behavior: smooth;
}

body {
  margin: 0;
  padding: 0;
}
</style>
