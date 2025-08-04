<script setup lang="ts">
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'
import type { Club } from '../../views/dashboards/player/types'

import registerMultiStepIllustrationDark from '@images/illustrations/register-multi-step-illustration-dark.png'
import registerMultiStepIllustrationLight from '@images/illustrations/register-multi-step-illustration-light.png'

import registerMultiStepBgDark from '@images/pages/register-multi-step-bg-dark.png'
import registerMultiStepBgLight from '@images/pages/register-multi-step-bg-light.png'

const registerMultiStepBg = useGenerateImageVariant(registerMultiStepBgLight, registerMultiStepBgDark)

definePage({
  meta: {
    layout: 'blank',
    public: true,
  },
})

const router = useRouter()
const currentStep = ref(0)
const isPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)
const clubs = ref<Club[]>([])

const isFlatSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref<'success' | 'error'>('success')

const rules = [
  (file: File | null) => {
    if (!file) return true
    return file.size < 1000000 || 'Ukuran gambar maksimal 1 MB!'
  },
]

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
  sameAsPassword: (value: string) => value === localData.value.password || 'Konfirmasi password tidak cocok',
}

const registerMultiStepIllustration = useGenerateImageVariant(registerMultiStepIllustrationLight, registerMultiStepIllustrationDark)

const localData = ref<{
  email: string
  password: string
  confirmPassword: string
  name: string
  phone: string
  nisn: string
  height: string
  weight: string
  family_card: File | null
  report_grades: File | null
  birth_certificate: File | null
  club_player: {
    club_id: string
    category: string
    position: string
    back_number: string
  }
}>({
  email: '',
  password: '',
  confirmPassword: '',
  name: '',
  phone: '',
  nisn: '',
  height: '',
  weight: '',
  family_card: null,
  report_grades: null,
  birth_certificate: null,
  club_player: {
    club_id: '',
    category: '',
    position: '',
    back_number: '',
  },
})

const items = [
  {
    title: 'Akun',
    subtitle: 'Rincian Akun',
    icon: 'tabler-file-analytics',
  },
  {
    title: 'Pribadi',
    subtitle: 'Masukkan Informasi',
    icon: 'tabler-user',
  },
  {
    title: 'Dokumen',
    subtitle: 'Masukan Dokumen Pribadi',
    icon: 'tabler-credit-card',
  },
]

const form = ref({
  username: '',
  email: '',
  password: '',
  confirmPassword: '',
  link: '',
  name: '',
  phone: '',
  pincode: '',
  address: '',
  landmark: '',
  city: '',
  state: null,
  selectedPlan: '0',
  cardNumber: '',
  cardName: '',
  expiryDate: '',
  cvv: '',
})

const onSubmit = async () => {
  const formData = new FormData()

  formData.append('email', localData.value.email)
  formData.append('password', localData.value.password)
  formData.append('confirmPassword', localData.value.confirmPassword)
  formData.append('name', localData.value.name)
  formData.append('phone', localData.value.phone)
  formData.append('nisn', localData.value.nisn)
  formData.append('height', localData.value.height)
  formData.append('weight', localData.value.weight)
  formData.append('club_id', localData.value.club_player.club_id)
  formData.append('category', localData.value.club_player.category)
  formData.append('position', localData.value.club_player.position)
  formData.append('back_number', localData.value.club_player.back_number)

  // file upload
  if (localData.value.family_card instanceof File) {
    formData.append('family_card', localData.value.family_card)
  }
  if (localData.value.report_grades instanceof File) {
    formData.append('report_grades', localData.value.report_grades)
  }
  if (localData.value.birth_certificate instanceof File) {
    formData.append('birth_certificate', localData.value.birth_certificate)
  }

  try {
    await $api('company/register', {
      method: 'POST',
      body: formData,
    });

    snackbarMessage.value = 'Registrasi Berhasil!!';
    snackbarColor.value = 'success';
    isFlatSnackbarVisible.value = true;

    router.push({
      name: 'authentication-login',
      query: {
        success: 'Registrasi Berhasil!!',
      },
    });
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

const categories = [
  { title: 'Pilih Kategori', value: '' },
  { title: 'U-9', value: 'u9' },
  { title: 'U-10', value: 'u10' },
  { title: 'U-11', value: 'u11' },
  { title: 'U-12', value: 'u12' },
  { title: 'U-13', value: 'u13' },
  { title: 'U-14', value: 'u14' },
  { title: 'U-15', value: 'u15' },
]

const positions = [
  { title: 'Pilih Posisi', value: '' },
  { title: 'Penjaga Gawang', value: 'goalkeeper' },
  { title: 'Bek', value: 'defender' },
  { title: 'Gelandang', value: 'midfielder' },
  { title: 'Penyerang', value: 'forward' },
];

const familyCardPreview = ref<string | null>(null)
const reportGradesPreview = ref<string | null>(null)
const birthCertificatePreview = ref<string | null>(null)

const props = defineProps<{
  data?: {
    family_card?: { url: string },
    report_grades?: { url: string },
    birth_certificate?: { url: string },
  }
}>()

onMounted(() => {
  if (props.data?.family_card?.url) {
    familyCardPreview.value = getImageUrl(props.data.family_card.url)
  }
  if (props.data?.report_grades?.url) {
    reportGradesPreview.value = getImageUrl(props.data.report_grades.url)
  }
  if (props.data?.birth_certificate?.url) {
    birthCertificatePreview.value = getImageUrl(props.data.birth_certificate.url)
  }
})

const getImageUrl = (path: string) => {  
  return import.meta.env.VITE_APP_URL + path
}

watch(() => localData.value.family_card, (newFamilyCard: any) => {
  if (newFamilyCard instanceof File) {
    familyCardPreview.value = URL.createObjectURL(newFamilyCard)
  } else if (newFamilyCard?.url) {
    familyCardPreview.value = getImageUrl(newFamilyCard.url)
  } else {
    familyCardPreview.value = null
  }
})

watch(() => localData.value.report_grades, (newReportGrades: any) => {
  if (newReportGrades instanceof File) {
    reportGradesPreview.value = URL.createObjectURL(newReportGrades)
  } else if (newReportGrades?.url) {
    reportGradesPreview.value = getImageUrl(newReportGrades.url)
  } else {
    reportGradesPreview.value = null
  }
})

watch(() => localData.value.birth_certificate, (newBirthCertificatePreview: any) => {
  if (newBirthCertificatePreview instanceof File) {
    birthCertificatePreview.value = URL.createObjectURL(newBirthCertificatePreview)
  } else if (newBirthCertificatePreview?.url) {
    birthCertificatePreview.value = getImageUrl(newBirthCertificatePreview.url)
  } else {
    birthCertificatePreview.value = null
  }
})

onBeforeUnmount(() => {
  if (familyCardPreview.value?.startsWith('blob:')) {
    URL.revokeObjectURL(familyCardPreview.value)
  }
})

onBeforeUnmount(() => {
  if (reportGradesPreview.value?.startsWith('blob:')) {
    URL.revokeObjectURL(reportGradesPreview.value)
  }
})

onBeforeUnmount(() => {
  if (birthCertificatePreview.value?.startsWith('blob:')) {
    URL.revokeObjectURL(birthCertificatePreview.value)
  }
})
</script>

<template>
  <RouterLink to="/">
    <div class="auth-logo d-flex align-center gap-x-3">
      <img
        src="/storage/logo/LOGOSSB.png"
        alt="Logo SSB"
        class="app-logo-img"
        style="height: 40px;"
      />
      <h3 class="app-logo-title mb-0">
        PUTRA MUDA BALARAJA
      </h3>
    </div>
  </RouterLink>

  <VRow
    no-gutters
    class="auth-wrapper"
  >
    <VCol
      md="4"
      class="d-none d-md-flex"
    >
      <!-- here your illustration -->
      <div class="d-flex justify-center align-center w-100 position-relative">
        <VImg
          :src="registerMultiStepIllustration"
          class="illustration-image flip-in-rtl"
        />

        <img
          class="bg-image position-absolute w-100 flip-in-rtl"
          :src="registerMultiStepBg"
          alt="register-multi-step-bg"
          height="340"
        >
      </div>
    </VCol>

    <VCol
      cols="12"
      md="8"
      class="auth-card-v2 d-flex align-center justify-center pa-10"
      style="background-color: rgb(var(--v-theme-surface));"
    >
      <VCard
        flat
        class="mt-12 mt-sm-10"
      >
        <AppStepper
          v-model:current-step="currentStep"
          :items="items"
          :direction="$vuetify.display.smAndUp ? 'horizontal' : 'vertical'"
          icon-size="22"
          class="stepper-icon-step-bg mb-12"
        />

        <VWindow
          v-model="currentStep"
          class="disable-tab-transition"
          style="max-inline-size: 681px;"
        >
          <VForm>
            <VWindowItem>
              <h4 class="text-h4">
                Informasi Akun
              </h4>
              <p class="text-body-1 mb-6">
                Masukan Detail Akun Anda
              </p>

              <VRow>
                <VCol
                  cols="12"
                  md="12"
                >
                  <AppTextField
                    v-model="localData.email"
                    label="Email"
                    placeholder="budi@gmail.com"
                  />
                </VCol>

                <VCol
                  cols="12"
                  md="6"
                >
                  <AppTextField
                    v-model="localData.password"
                    label="Password"
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
                    v-model="localData.confirmPassword"
                    label="Konfirmasi Password"
                    placeholder="············"
                    :type="isConfirmPasswordVisible ? 'text' : 'password'"
                    autocomplete="confirm-password"
                    :append-inner-icon="isConfirmPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                    @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
                    :rules="[rulesConfirmPassword.required, rulesConfirmPassword.sameAsPassword]"
                  />
                </VCol>
              </VRow>
            </VWindowItem>

            <VWindowItem>
              <h4 class="text-h4">
                Informasi Pribadi
              </h4>
              <p>
                Masukan Informasi Pribadi Anda
              </p>

              <VRow>
                <VCol
                  cols="12"
                  md="12"
                >
                  <AppTextField
                    v-model="localData.name"
                    label="Nama Lengkap"
                    placeholder="Contoh: Budi"
                  />
                </VCol>

                <VCol
                  cols="12"
                  md="6"
                >
                  <AppTextField
                    v-model="localData.phone"
                    type="number"
                    label="Nomor Telepon"
                    placeholder="081234567890"
                  />
                </VCol>

                <VCol
                  cols="12"
                  md="6"
                >
                  <AppTextField
                    v-model="localData.nisn"
                    label="NISN"
                    placeholder="Contoh: 1234567890"
                    maxlength="10"
                    hint="Harus tepat 10 karakter"
                    countertype="number"
                    :rules="[rulesNisn.required, rulesNisn.exactLength]"
                  />
                </VCol>

                <VCol
                  cols="12"
                  md="6"
                >
                  <AppTextField
                    v-model="localData.height"
                    label="Tinggi Badan (cm)"
                    placeholder="Contoh: 160"
                    maxlength="3"
                  />
                </VCol>

                <VCol
                  cols="12"
                  md="6"
                >
                  <AppTextField
                    v-model="localData.weight"
                    label="Berat Badan (kg)"
                    placeholder="Contoh: 45"
                    maxlength="3"
                  />
                </VCol>

                <!-- <VCol
                  cols="12"
                  md="12"
                >
                  <AppSelect
                    v-model="localData.club_player.club_id"
                    label="Club"
                    :items="clubs"
                    placeholder="Pilih Club"
                    clearable
                    clear-icon="tabler-x"
                    single-line
                  />
                </VCol> -->

                <!-- <VCol
                  cols="12"
                  md="6"
                >
                  <AppTextField
                    v-model="localData.club_player.back_number"
                    label="Nomor Punggung"
                    placeholder="Contoh: 07"
                    maxlength="3"
                  />
                </VCol> -->

                <VCol
                  cols="12"
                  md="6"
                >
                  <AppSelect
                    v-model="localData.club_player.category"
                    label="Kategori"
                    :items="categories"
                    clearable
                    clear-icon="tabler-x"
                    single-line
                    placeholder="Pilih Kategori"
                  />
                </VCol>
                
                <VCol
                  cols="12"
                  md="6"
                >
                  <AppSelect
                    v-model="localData.club_player.position"
                    label="Posisi"
                    :items="positions"
                    clearable
                    clear-icon="tabler-x"
                    single-line
                    placeholder="Pilih Posisi"
                  />
                </VCol>
              </VRow>
            </VWindowItem>

            <VWindowItem>
              <h4 class="text-h4">
                Dokumen Pribadi
              </h4>
              <p class="text-body-1 mb-5">
                Silakan unggah dokumen yang diperlukan untuk pendaftaran. Pastikan foto yang diunggah jelas dan sesuai dengan format yang ditentukan.
              </p>

              <div class="text-h6 mt-4">
                <h6 class="text-h6 mb-2">Kartu Keluarga</h6>
                <img
                  v-if="familyCardPreview"
                  :src="familyCardPreview"
                  alt="Preview Family Card"
                  style="width: 30%; border-radius: 8px; margin-bottom: 1rem;"
                />

                <VFileInput
                  v-model="localData.family_card"
                  label="Ganti Foto"
                  accept="image/png, image/jpeg, image/bmp"
                  density="comfortable"
                /> 
              </div>  

              <div class="text-h6 mt-4">
                <h6 class="text-h6 mb-2">Rapor Terakhir</h6>
                <img
                  v-if="reportGradesPreview"
                  :src="reportGradesPreview"
                  alt="Preview Report Grades"
                  style="width: 30%; border-radius: 8px; margin-bottom: 1rem;"
                />

                <VFileInput
                  v-model="localData.report_grades"
                  label="Ganti Foto"
                  accept="image/png, image/jpeg, image/bmp"
                  density="comfortable"
                />
              </div>  

              <div class="text-h6 mt-4">
                <h6 class="text-h6 mb-2">Akte Kelahiran</h6>
                <img
                  v-if="birthCertificatePreview"
                  :src="birthCertificatePreview"
                  alt="Preview Birth Certificate"
                  style="width: 30%; border-radius: 8px; margin-bottom: 1rem;"
                />

                <VFileInput
                  v-model="localData.birth_certificate"
                  label="Ganti Foto"
                  accept="image/png, image/jpeg, image/bmp"
                  density="comfortable"
                />
              </div>  
            </VWindowItem>
          </VForm>
        </VWindow>

        <div class="d-flex flex-wrap justify-space-between gap-x-4 mt-6">
          <VBtn
            color="secondary"
            :disabled="currentStep === 0"
            variant="tonal"
            @click="currentStep--"
          >
            <VIcon
              icon="tabler-arrow-left"
              start
              class="flip-in-rtl"
            />
            Previous
          </VBtn>

          <VBtn
            v-if="items.length - 1 === currentStep"
            color="success"
            @click="onSubmit"
          >
            submit
          </VBtn>

          <VBtn
            v-else
            @click="currentStep++"
          >
            Next

            <VIcon
              icon="tabler-arrow-right"
              end
              class="flip-in-rtl"
            />
          </VBtn>
        </div>
      </VCard>
    </VCol>
  </VRow>

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

<style lang="scss">
@use "@core-scss/template/pages/page-auth.scss";

.illustration-image {
  block-size: 550px;
  inline-size: 248px;
}

.bg-image {
  inset-block-end: 0;
}
</style>
