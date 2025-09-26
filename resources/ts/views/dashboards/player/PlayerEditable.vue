<script setup lang="ts">
import type { Club, PlayerData } from './types';

const currentTab = ref('biodata')
const clubs = ref<Club[]>([])
const error = ref<string | null>(null)

const downloading = ref(false)
const loading = ref(false)
const isFlatSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref<'success' | 'error'>('success')

const rulesNisn = {
  required: (value: string) => !!value || 'Harus diisi.',
  maxLength: (value: string) =>
    (value?.length <= 13) || 'Maksimal 13 karakter',
};

const props = defineProps<{ data: PlayerData }>()
const emit = defineEmits(['submit', 'update:data'])

const localData = ref<PlayerData>({
  ...props.data,
})

const avatarPreview = ref<string | null>(
  props.data.avatar?.url ? getImageUrl(props.data.avatar.url) : null
)

const familyCardPreview = ref<string | null>(
  props.data.family_card?.url ? getImageUrl(props.data.family_card.url) : null
)

const reportGradesPreview = ref<string | null>(
  props.data.report_grades?.url ? getImageUrl(props.data.report_grades.url) : null
)

const birthCertificatePreview = ref<string | null>(
  props.data.birth_certificate?.url ? getImageUrl(props.data.birth_certificate.url) : null
)

const proofPaymentPreview = ref<string | null>(
  props.data.proof_payment?.url ? getImageUrl(props.data.proof_payment.url) : null
)

watch(
  () => props.data.id,
  (newId, oldId) => {
    // Hanya reset localData saat data yang dimuat berbeda (halaman edit baru)
    if (newId !== oldId) {
      localData.value = JSON.parse(JSON.stringify(props.data))
    }
  },
  { immediate: true }
)

const positions = [
  { title: 'Pilih Posisi', value: '' },
  { title: 'Depan', value: 'front' },
  { title: 'Tengah', value: 'center' },
  { title: 'Belakang', value: 'back' },
  { title: 'GK', value: 'gk' },
];

async function getClubs() {
  try {
    const response = await $api('club', {
      method: 'GET',
    })

    const clubData = response.data.map((club: any) => ({
      title: club.name,
      value: club.id,
    }))

    clubs.value = [{ title: 'Pilih Club', value: '' }, ...clubData]
  } catch (error) {
    console.error('Gagal memuat clubs', error)
  }
}

onMounted(async () => {
  getClubs()
})

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

watch(localData, (newVal, oldVal) => {
  if (JSON.stringify(newVal) !== JSON.stringify(oldVal)) {
    emit('update:data', newVal)
  }
}, { deep: true })

const submitForm = () => {
  emit('update:data', localData.value) 
  emit('submit')
}

const getImageUrl = (path: string) => {  
  return import.meta.env.VITE_APP_URL + path
}

watch(() => localData.value.avatar, (newAvatar: any) => {
  if (newAvatar instanceof File) {
    avatarPreview.value = URL.createObjectURL(newAvatar)
  } else if (newAvatar?.url) {
    avatarPreview.value = getImageUrl(newAvatar.url)
  } else {
    avatarPreview.value = null
  }
})

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

watch(() => localData.value.proof_payment, (newProofPaymentPreview: any) => {
  if (newProofPaymentPreview instanceof File) {
    proofPaymentPreview.value = URL.createObjectURL(newProofPaymentPreview)
  } else if (newProofPaymentPreview?.url) {
    proofPaymentPreview.value = getImageUrl(newProofPaymentPreview.url)
  } else {
    proofPaymentPreview.value = null
  }
})

onBeforeUnmount(() => {
  if (avatarPreview.value?.startsWith('blob:')) {
    URL.revokeObjectURL(avatarPreview.value)
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

onBeforeUnmount(() => {
  if (proofPaymentPreview.value?.startsWith('blob:')) {
    URL.revokeObjectURL(proofPaymentPreview.value)
  }
})

const downloadBiodata = async () => {
  try {
    downloading.value = true;
    const playerId = localData.value.id;

    const response = await $api(`player/${playerId}/download-biodata`, {
      method: 'GET',
      responseType: 'blob',
      headers: {
        Accept: 'application/pdf',
      },
    });

    // Pastikan yang dipakai langsung response (kalau wrapper sudah return blob)
    const blob = response instanceof Blob ? response : new Blob([response.data], { type: 'application/pdf' });

    const downloadUrl = window.URL.createObjectURL(blob);
    const link = document.createElement('a');

    link.href = downloadUrl;
    link.download = `${localData.value.code}_${localData.value.name}.pdf`;

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(downloadUrl);

    snackbarMessage.value = 'Biodata berhasil diunduh!';
    snackbarColor.value = 'success';
    isFlatSnackbarVisible.value = true;
  } catch (err: any) {
    const errors = err?.data?.errors;

    if (err?.status === 422 && errors) {
      const messages = Object.values(errors).flat();
      snackbarMessage.value = 'Validasi gagal: ' + messages.join(', ');
    } else {
      snackbarMessage.value = 'Gagal mengunduh biodata: ' + (err?.message || 'Unknown error');
    }

    snackbarColor.value = 'error';
    isFlatSnackbarVisible.value = true;
  } finally {
    downloading.value = false;
  }
};

</script>

<template>
  <form @submit.prevent="$emit('submit')">
    <div class="d-flex flex-column gap-6 mb-6">
      <VCard>
        <!-- Tab Navigasi -->
        <VTabs v-model="currentTab" grow stacked>
          <VTab value="biodata">
            <VIcon icon="tabler-user" class="mb-2" />
            <span>Biodata</span>
          </VTab>

          <VTab value="files">
            <VIcon icon="tabler-file" class="mb-2" />
            <span>Dokumen</span>
          </VTab>
        </VTabs>

        <VCardText>
          <VWindow v-model="currentTab">
            <!-- Tab Biodata -->
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

                <VCol cols="8" class="text-no-wrap">
                  <AppTextField
                    v-model="localData.user.email"
                    label="Email"
                    placeholder="Contoh: admin@gmail.com"
                    class="mb-4"
                  />

                  <!-- <VSwitch
                    v-model="toggleSwitch"
                    :label="capitalizedLabel(toggleSwitch)"
                  /> -->
                  
                  <VRow>
                    <VCol cols="6">
                      <AppTextField
                        v-model="localData.nisn"
                        label="NISN"
                        placeholder="Contoh: 1234567890"
                        class="mb-4"
                        maxlength="13"
                        :rules="[rulesNisn.required, rulesNisn.maxLength]"
                        hint="Maksimal 13 karakter"
                        counter
                      />
                    </VCol>

                    <VCol cols="6">
                      <AppTextField
                        v-model="localData.phone"
                        type="number"
                        label="Nomor Telepon"
                        placeholder="081234567890"
                      />
                    </VCol>
                  </VRow>

                  <AppTextField
                    v-model="localData.name"
                    label="Nama Lengkap"
                    placeholder="Contoh: Budi Setiawan"
                    class="mb-4"
                  />

                  <AppTextField
                    v-model="localData.height"
                    label="Tinggi Badan (cm)"
                    placeholder="Contoh: 160"
                    class="mb-4"
                    maxlength="3"
                  />

                  <AppTextField
                    v-model="localData.weight"
                    label="Berat Badan (kg)"
                    placeholder="Contoh: 45"
                    class="mb-4"
                    maxlength="3"
                  />

                 <!-- <AppSelect
                    label="Club"
                    v-model="localData.club_id"
                    :items="clubs"
                    placeholder="Pilih Club"
                    clearable
                    clear-icon="tabler-x"
                    single-line
                    class="mb-4"
                  /> -->
                  
                  <VRow class="mb-4">
                    <VCol cols="6">
                      <AppTextField
                        v-model="localData.back_number"
                        label="Nomor Punggung"
                        placeholder="Contoh: 07"
                        maxlength="3"
                      />
                    </VCol>

                    <VCol cols="6">
                      <AppSelect
                        label="Kategori"
                        v-model="localData.category"
                        :items="categories"
                        clearable
                        clear-icon="tabler-x"
                        single-line
                      />
                    </VCol>
                  </VRow>
                  
                  <AppSelect
                    label="Posisi"
                    v-model="localData.position"
                    :items="positions"
                    clearable
                    clear-icon="tabler-x"
                    single-line
                  />
                </VCol>
              </VRow>
            </VWindowItem>

            <!-- Tab File Upload -->
            <VWindowItem value="files">
              <VRow class="px-3">
                <VCol class="text-no-wrap">
                  <div class="text-h6 mt-2">
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

                  <div class="text-h6 mt-4">
                    <h6 class="text-h6 mb-2">Bukti Pendaftaran</h6>
                    <img
                      v-if="proofPaymentPreview"
                      :src="proofPaymentPreview"
                      alt="Preview Bukti Pendaftaran"
                      style="width: 30%; border-radius: 8px; margin-bottom: 1rem;"
                    />

                    <VFileInput
                      v-model="localData.proof_payment"
                      label="Ganti Bukti Pendaftaran"
                      accept="image/png, image/jpeg, image/bmp"
                      density="comfortable"
                    />
                  </div>
                </VCol>
              </VRow>
            </VWindowItem>
          </VWindow>
        </VCardText>

        <!-- Tombol Submit -->
        <VCol cols="12" class="d-flex justify-end">
          <VBtn
            v-if="localData.id && localData.status == 1"
            color="warning"
            class="mr-2"
            @click="downloadBiodata"
          >
            Download Biodata
          </VBtn>
          <VBtn
            color="primary"
            @click="submitForm"
          >
            Simpan
          </VBtn>
        </VCol>
      </VCard>
    </div>
  </form>
</template>
