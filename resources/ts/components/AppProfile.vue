<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const user = computed(() => authStore.user)


console.log('user', authStore);

// Types
interface UserData {
  id: number
  fullName: string
  firstName: string
  lastName: string
  company: string
  username: string
  role: string
  country: string
  contact: string
  email: string
  currentPlan: string
  status: string
  avatar: string
  taskDone: number
  projectDone: number
  taxId: string
  language: string
}

interface PlayerData {
  id: number
  name: string
  user: {
    id: number
    email: string
    new_password: string
    confirm_password: string
  }
  nisn: string
  height: string
  weight: string
  back_number: string
  position: string
  category: string
  is_captain: boolean
  status: boolean
  sport_players: any[]
  club: {
    id: number
    code: string
    name: string
  }
  avatar: File | { url: string } | null
  family_card: File | { url: string } | null
  report_grades: File | { url: string } | null
  birth_certificate: File | { url: string } | null
}

interface Props {
  userData?: UserData
  playerId?: number
}

// Props & Emits
const props = defineProps<Props>()
const router = useRouter()

// State Management
const currentTab = ref('biodata')
const loading = ref(false)
const error = ref<string | null>(null)
const isFlatSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref<'success' | 'error'>('success')

const isPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)

// Player Data
const playerData = ref<PlayerData>({
  id: 0,
  name: '',
  user: {
    id: 0,
    email: '',
    new_password: '',
    confirm_password: ''
  },
  nisn: '',
  height: '',
  weight: '',
  back_number: '',
  position: '',
  category: '',
  is_captain: false,
  status: false,
  sport_players: [],
  club: {
    id: 0,
    code: '',
    name: '',
  },
  avatar: null,
  family_card: null,
  report_grades: null,
  birth_certificate: null,
})

// Image Previews
const avatarPreview = ref<string | null>(null)
const familyCardPreview = ref<string | null>(null)
const reportGradesPreview = ref<string | null>(null)
const birthCertificatePreview = ref<string | null>(null)

// Options
const positions = [
  { title: 'Pilih Posisi', value: '' },
  { title: 'Penjaga Gawang', value: 'goalkeeper' },
  { title: 'Bek', value: 'defender' },
  { title: 'Gelandang', value: 'midfielder' },
  { title: 'Penyerang', value: 'forward' },
]

const categories = [
  { title: 'Pilih Kategori', value: '' },
  { title: 'Tim Utama', value: 'main' },
  { title: 'U-9', value: 'u9' },
  { title: 'U-10', value: 'u10' },
  { title: 'U-11', value: 'u11' },
  { title: 'U-12', value: 'u12' },
  { title: 'U-13', value: 'u13' },
  { title: 'U-14', value: 'u14' },
  { title: 'U-15', value: 'u15' },
]

// Validation Rules
const rulesNisn = {
  required: (value: string) => !!value || 'Harus diisi.',
  exactLength: (value: string) => value.length === 10 || 'Harus tepat 10 karakter',
};

const rulesPassword = {
  required: (value: string) => !!value || 'Password harus diisi',
  minLength: (value: string) => value.length >= 8 || 'Minimal 8 karakter',
}

const rulesConfirmPassword = {
  required: (value: string) => !!value || 'Konfirmasi password harus diisi',
  sameAsPassword: (value: string) => value === playerData.value.user.new_password || 'Konfirmasi password tidak cocok',
}

// Utility Functions
const getImageUrl = (path: string) => {
  return import.meta.env.VITE_APP_URL + path
}

const resolveUserRoleVariant = (role: string) => {
  const roleMap = {
    subscriber: { color: 'warning', icon: 'tabler-user' },
    author: { color: 'success', icon: 'tabler-circle-check' },
    maintainer: { color: 'primary', icon: 'tabler-chart-pie-2' },
    editor: { color: 'info', icon: 'tabler-pencil' },
    admin: { color: 'secondary', icon: 'tabler-server-2' },
  }
  return roleMap[role as keyof typeof roleMap] || { color: 'primary', icon: 'tabler-user' }
}

const showNotification = (message: string, type: 'success' | 'error' = 'success') => {
  snackbarMessage.value = message
  snackbarColor.value = type
  isFlatSnackbarVisible.value = true
}

// API Functions
const fetchPlayer = async () => {
  console.log('asd', authStore);
  
  loading.value = true
  error.value = null

  try {
    const res = await $api(`player/2/edit`)
    playerData.value = res.data
    
    // Set image previews
    if (playerData.value.avatar?.url) {
      avatarPreview.value = getImageUrl(playerData.value.avatar.url)
    }
    if (playerData.value.family_card?.url) {
      familyCardPreview.value = getImageUrl(playerData.value.family_card.url)
    }
    if (playerData.value.report_grades?.url) {
      reportGradesPreview.value = getImageUrl(playerData.value.report_grades.url)
    }
    if (playerData.value.birth_certificate?.url) {
      birthCertificatePreview.value = getImageUrl(playerData.value.birth_certificate.url)
    }
  } catch (err: any) {
    error.value = err.message || 'Gagal mengambil data player'
    showNotification(error.value, 'error')
  } finally {
    loading.value = false
  }
}

const updatePlayer = async () => {
  loading.value = true

  try {
    const formData = new FormData()
    formData.append('_method', 'PUT')

    // Basic data
    formData.append('email', playerData.value.user.email ?? '')
    formData.append('new_password', playerData.value.user.new_password ?? '')
    formData.append('nisn', playerData.value.nisn ?? '')
    formData.append('name', playerData.value.name ?? '')
    formData.append('height', playerData.value.height ?? '')
    formData.append('weight', playerData.value.weight ?? '')
    formData.append('back_number', playerData.value.back_number ?? '')
    formData.append('position', playerData.value.position ?? '')
    formData.append('category', playerData.value.category ?? '')

    // Files
    if (playerData.value.avatar instanceof File) {
      formData.append('avatar', playerData.value.avatar)
    }
    if (playerData.value.family_card instanceof File) {
      formData.append('family_card', playerData.value.family_card)
    }
    if (playerData.value.report_grades instanceof File) {
      formData.append('report_grades', playerData.value.report_grades)
    }
    if (playerData.value.birth_certificate instanceof File) {
      formData.append('birth_certificate', playerData.value.birth_certificate)
    }

    const res = await $api(`company/player/profile-update/2`, {
      method: 'POST',
      body: formData,
    })

    snackbarMessage.value = 'Profile Berhasil Diperbarui!'
    snackbarColor.value = 'success'
    isFlatSnackbarVisible.value = true
  } catch (err: any) {
    const errors = err?.data?.errors

    if (err?.status === 422 && errors) {
      const messages = Object.values(errors).flat()
      snackbarMessage.value = 'Validasi gagal: ' + messages.join(', ')
    } else {
      snackbarMessage.value = 'Gagal mengirim data: ' + (err?.message || 'Unknown error')
    }

    snackbarColor.value = 'error'
    isFlatSnackbarVisible.value = true
  }
}

// Event Handlers
// const handleSubmit = async () => {
//   await updatePlayer()
// }

const handleFileChange = (file: File | null, type: 'avatar' | 'family_card' | 'report_grades' | 'birth_certificate') => {
  if (file) {
    playerData.value[type] = file
  }
}

// Watchers
watch(() => playerData.value.avatar, (newAvatar: any) => {
  if (newAvatar instanceof File) {
    avatarPreview.value = URL.createObjectURL(newAvatar)
  } else if (newAvatar?.url) {
    avatarPreview.value = getImageUrl(newAvatar.url)
  } else {
    avatarPreview.value = null
  }
})

watch(() => playerData.value.family_card, (newFamilyCard: any) => {
  if (newFamilyCard instanceof File) {
    familyCardPreview.value = URL.createObjectURL(newFamilyCard)
  } else if (newFamilyCard?.url) {
    familyCardPreview.value = getImageUrl(newFamilyCard.url)
  } else {
    familyCardPreview.value = null
  }
})

watch(() => playerData.value.report_grades, (newReportGrades: any) => {
  if (newReportGrades instanceof File) {
    reportGradesPreview.value = URL.createObjectURL(newReportGrades)
  } else if (newReportGrades?.url) {
    reportGradesPreview.value = getImageUrl(newReportGrades.url)
  } else {
    reportGradesPreview.value = null
  }
})

watch(() => playerData.value.birth_certificate, (newBirthCertificate: any) => {
  if (newBirthCertificate instanceof File) {
    birthCertificatePreview.value = URL.createObjectURL(newBirthCertificate)
  } else if (newBirthCertificate?.url) {
    birthCertificatePreview.value = getImageUrl(newBirthCertificate.url)
  } else {
    birthCertificatePreview.value = null
  }
})

// Lifecycle
onMounted(() => {
  fetchPlayer()
})

onBeforeUnmount(() => {
  // Clean up blob URLs
  const previews = [avatarPreview, familyCardPreview, reportGradesPreview, birthCertificatePreview]
  previews.forEach(preview => {
    if (preview.value?.startsWith('blob:')) {
      URL.revokeObjectURL(preview.value)
    }
  })
})
</script>

<template>
<VContainer id="team">
  <div class="our-team pa-">
    <VRow class="align-center my-6">
      <VCol>
        <VChip label color="primary" size="small">Profil Pengguna</VChip>
        <h4 class="text-h4 mt-2 mb-1">Informasi Akun dan Aktivitas Anda</h4>
        <p class="text-body-1 mb-0">
          Kelola informasi pribadi Anda, perbarui data akun, dan pantau aktivitas serta riwayat interaksi Anda di platform kami dengan mudah dan aman.
        </p>
      </VCol>
    </VRow>

    <VRow>
      <VCol cols="12">
        <!-- Main Form -->
        <form @submit.prevent="updatePlayer">
          <div class="d-flex flex-column gap-6">
            <VCard>
              <!-- Tab Navigation -->
              <VTabs v-model="currentTab" grow stacked>
                <VTab value="biodata">
                  <VIcon icon="tabler-user" class="mb-2" />
                  <span>Biodata</span>
                </VTab>
                <VTab value="files">
                  <VIcon icon="tabler-file" class="mb-2" />
                  <span>Files</span>
                </VTab>
              </VTabs>

              <VCardText>
                <VWindow v-model="currentTab">
                  <!-- Biodata Tab -->
                  <VWindowItem value="biodata">
                    <!-- Header -->
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
                      <VCol cols="12" md="4" class="d-flex flex-column align-center justify-center">
                        <div style="width: 100%; max-width: 300px;" class="text-center justify-center">
                          <img
                            v-if="avatarPreview"
                            :src="avatarPreview"
                            alt="Preview Avatar"
                            style="width: 100%; border-radius: 8px; margin-bottom: 1rem;"
                          />
                          <VFileInput
                            v-model="playerData.avatar"
                            label="Ganti Foto"
                            accept="image/png, image/jpeg, image/bmp"
                            density="comfortable"
                          />
                        </div>
                      </VCol>

                      <!-- Form Fields -->
                      <VCol cols="12" md="8">
                        <VRow>
                          <VCol cols="12">
                            <AppTextField
                              v-model="playerData.user.email"
                              label="Email"
                              placeholder="Contoh: admin@gmail.com"
                              type="email"
                              required
                            />
                          </VCol>

                          <VCol
                            cols="12"
                            md="6"
                          >
                            <AppTextField
                              v-model="playerData.user.new_password"
                              label="New Password"
                              placeholder="············"
                              :type="isPasswordVisible ? 'text' : 'password'"
                              autocomplete="password"
                              :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                              @click:append-inner="isPasswordVisible = !isPasswordVisible"
                              :rules="[rulesPassword.required, rulesPassword.minLength]"
                            />
                          </VCol>

                          <VCol 
                            cols="12"
                            md="6"
                          >
                            <AppTextField
                              v-model="playerData.user.confirm_password"
                              label="Konfirmasi Password"
                              placeholder="············"
                              :type="isConfirmPasswordVisible ? 'text' : 'password'"
                              autocomplete="confirm-password"
                              :append-inner-icon="isConfirmPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                              @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
                              :rules="[rulesConfirmPassword.required, rulesConfirmPassword.sameAsPassword]"
                            />
                          </VCol>
                          
                          <VCol cols="12">
                            <AppTextField
                              v-model="playerData.nisn"
                              label="NISN"
                              placeholder="Contoh: 1234567890"
                              maxlength="10"
                              :rules="[rulesNisn.required, rulesNisn.exactLength]"
                              hint="Harus tepat 10 karakter"
                              counter
                              required
                            />
                          </VCol>

                          <VCol cols="12">
                            <AppTextField
                              v-model="playerData.name"
                              label="Nama Lengkap"
                              placeholder="Contoh: Budi Setiawan"
                              required
                            />
                          </VCol>

                          <VCol cols="12" sm="6">
                            <AppTextField
                              v-model="playerData.height"
                              label="Tinggi Badan (cm)"
                              placeholder="Contoh: 160"
                              class="mb-4"
                              maxlength="3"
                            />
                          </VCol>

                          <VCol cols="12" sm="6">
                            <AppTextField
                              v-model="playerData.weight"
                              label="Berat Badan (kg)"
                              placeholder="Contoh: 45"
                              class="mb-4"
                              maxlength="3"
                            />
                          </VCol>

                          <VCol cols="12" sm="6">
                            <AppTextField
                              v-model="playerData.back_number"
                              label="Nomor Punggung"
                              placeholder="Contoh: 07"
                              type="number"
                              maxlength="3"
                            />
                          </VCol>

                          <VCol cols="12" sm="6">
                            <AppSelect
                              v-model="playerData.category"
                              label="Kategori"
                              :items="categories"
                              clearable
                              clear-icon="tabler-x"
                              single-line
                            />
                          </VCol>

                          <VCol cols="12">
                            <AppSelect
                              v-model="playerData.position"
                              label="Posisi"
                              :items="positions"
                              clearable
                              clear-icon="tabler-x"
                              single-line
                            />
                          </VCol>
                        </VRow>
                      </VCol>
                    </VRow>
                  </VWindowItem>

                  <!-- Files Tab -->
                  <VWindowItem value="files">
                    <VRow>
                      <VCol cols="12" md="6">
                        <div class="mb-6">
                          <h6 class="text-h6 mb-2">Kartu Keluarga</h6>
                          <img
                            v-if="familyCardPreview"
                            :src="familyCardPreview"
                            alt="Preview Family Card"
                            style="width: 100%; max-width: 300px; border-radius: 8px; margin-bottom: 1rem;"
                          />
                          <VFileInput
                            v-model="playerData.family_card"
                            label="Upload Kartu Keluarga"
                            accept="image/png, image/jpeg, image/bmp"
                            density="comfortable"
                          />
                        </div>

                        <div class="mb-6">
                          <h6 class="text-h6 mb-2">Rapor Terakhir</h6>
                          <img
                            v-if="reportGradesPreview"
                            :src="reportGradesPreview"
                            alt="Preview Report Grades"
                            style="width: 100%; max-width: 300px; border-radius: 8px; margin-bottom: 1rem;"
                          />
                          <VFileInput
                            v-model="playerData.report_grades"
                            label="Upload Rapor Terakhir"
                            accept="image/png, image/jpeg, image/bmp"
                            density="comfortable"
                          />
                        </div>
                      </VCol>

                      <VCol cols="12" md="6">
                        <div class="mb-6">
                          <h6 class="text-h6 mb-2">Akte Kelahiran</h6>
                          <img
                            v-if="birthCertificatePreview"
                            :src="birthCertificatePreview"
                            alt="Preview Birth Certificate"
                            style="width: 100%; max-width: 300px; border-radius: 8px; margin-bottom: 1rem;"
                          />
                          <VFileInput
                            v-model="playerData.birth_certificate"
                            label="Upload Akte Kelahiran"
                            accept="image/png, image/jpeg, image/bmp"
                            density="comfortable"
                          />
                        </div>
                      </VCol>
                    </VRow>
                  </VWindowItem>
                </VWindow>
              </VCardText>

              <!-- Submit Button -->
              <VCol cols="12" class="d-flex justify-end">
                <VBtn
                  type="submit"
                  color="primary"
                >
                  Simpan
                </VBtn>
              </VCol>
            </VCard>
          </div>
        </form>
      </VCol>
    </VRow>
  </div>
</VContainer>

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

<style lang="scss" scoped>
@use "@core-scss/base/mixins" as mixins;

.team-image {
  position: absolute;
  inset-block-start: -3.4rem;
  inset-inline: 0;
}

.headers {
  margin-block-end: 7.4375rem;
}

.our-team {
  margin-block: 5.25rem;
}

@media (max-width: 1264px) {
  .our-team {
    margin-block-end: 1rem;
  }
}

.team-card {
  border-radius: 90px 20px 6px 6px;
}

.section-title {
  font-size: 24px;
  font-weight: 800;
  line-height: 36px;
}

.section-title::after {
  position: absolute;
  background: url("../../../assets/images/front-pages/icons/section-title-icon.png") no-repeat left bottom;
  background-size: contain;
  block-size: 100%;
  content: "";
  font-weight: 800;
  inline-size: 120%;
  inset-block-end: 12%;
  inset-inline-start: -12%;
}

.logistics-card-statistics {
  border-block-end-style: solid;
  border-block-end-width: 2px;

  &:hover {
    border-block-end-width: 3px;
    margin-block-end: -1px;

    @include mixins.elevation(8);

    transition: all 0.1s ease-out;
  }
}

.skin--bordered {
  .logistics-card-statistics {
    border-block-end-width: 2px;

    &:hover {
      border-block-end-width: 3px;
      margin-block-end: -2px;
      transition: all 0.1s ease-out;
    }
  }
}

.match-card {
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.08);
}

.match-card ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.match-card li {
  margin-bottom: 4px;
}

.media-img {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.line-clamp {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2; // Tampilkan 2 baris
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 1.4rem; // Sesuaikan line-height
  max-height: 2.8rem;  // line-height * line-clamp
  margin: 0;
}
</style>
