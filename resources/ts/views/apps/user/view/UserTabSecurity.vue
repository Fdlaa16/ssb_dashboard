<script setup lang="ts">
const isNewPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)
const smsVerificationNumber = ref('+1(968) 819-2547')
const isTwoFactorDialogOpen = ref(false)

const loading = ref(false)
const error = ref<string | null>(null)
const isFlatSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref<'success' | 'error'>('success')

interface UserData {
  id: number
  new_password: string
  confirm_password: string
}

const userData = ref<UserData>({
  id: 0,
  new_password: '',
  confirm_password: ''
})

// Recent devices Headers
const recentDeviceHeader = [
  { title: 'BROWSER', key: 'browser' },
  { title: 'DEVICE', key: 'device' },
  { title: 'LOCATION', key: 'location' },
  { title: 'RECENT ACTIVITY', key: 'activity' },
]

const recentDevices = [
  {
    browser: ' Chrome on Windows',
    icon: 'tabler-brand-windows',
    color: 'info',
    device: 'HP Spectre 360',
    location: 'Switzerland',
    activity: '10, July 2021 20:07',
  },
  {
    browser: 'Chrome on Android',
    icon: 'tabler-brand-android',
    color: 'success',
    device: 'Oneplus 9 Pro',
    location: 'Dubai',
    activity: '14, July 2021 15:15',
  },
  {
    browser: 'Chrome on macOS',
    icon: 'tabler-brand-apple',
    color: 'secondary',
    device: 'Apple iMac',
    location: 'India',
    activity: '16, July 2021 16:17',
  },
  {
    browser: 'Chrome on iPhone',
    icon: 'tabler-device-mobile',
    color: 'error',
    device: 'iPhone 12x',
    location: 'Australia',
    activity: '13, July 2021 10:10',
  },

]

const rulesPassword = {
  required: (value: string) => !!value || 'Password harus diisi',
  minLength: (value: string) => value.length >= 8 || 'Minimal 8 karakter',
}

const rulesConfirmPassword = {
  required: (value: string) => !!value || 'Konfirmasi password harus diisi',
  sameAsPassword: (value: string) => value === userData.value.new_password || 'Konfirmasi password tidak cocok',
}

const fetchPlayer = async () => {
  // if (!props.playerId) return

  loading.value = true
  error.value = null

  try {
    const res = await $api(`profile`)
    userData.value = res.data

  } catch (err: any) {
    error.value = err.message || 'Gagal memuat data'

    snackbarMessage.value = error.value
    snackbarColor.value = 'error'
    isFlatSnackbarVisible.value = true
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchPlayer()
})

const updatePassword = async () => {
  // if (!props.playerId) return

  loading.value = true

  try {
    const formData = new FormData()
    formData.append('_method', 'PUT')

    // Basic data
    formData.append('new_password', userData.value.new_password ?? '')
    formData.append('confirm_password', userData.value.confirm_password ?? '')

    const res = await $api(`password-update`, {
      method: 'POST',
      body: formData,
    })

    snackbarMessage.value = 'Password Berhasil Diperbarui!'
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
</script>

<template>
  <VRow>
    <VCol cols="12">
      <!-- 👉 Change password -->
      <VCard title="Change Password">
        <VCardText>
          <VForm @submit.prevent="() => { updatePassword() }">
            <VRow>
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="userData.new_password"
                  label="New Password"
                  placeholder="············"
                  :type="isNewPasswordVisible ? 'text' : 'password'"
                  autocomplete="password"
                  :append-inner-icon="isNewPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isNewPasswordVisible = !isNewPasswordVisible"
                  :rules="[rulesPassword.required, rulesPassword.minLength]"
                />
              </VCol>
              <VCol
                cols="12"
                md="6"
              >
                <AppTextField
                  v-model="userData.confirm_password"
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
                <VBtn type="submit">
                  Change Password
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>

  <!-- 👉 Enable One Time Password Dialog -->
  <TwoFactorAuthDialog
    v-model:is-dialog-visible="isTwoFactorDialogOpen"
    :sms-code="smsVerificationNumber"
  />

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
