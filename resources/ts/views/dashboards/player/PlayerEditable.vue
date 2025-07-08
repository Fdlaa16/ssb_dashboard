<script setup lang="ts">
import type { Club, PlayerData } from './types'

const currentTab = ref('biodata')
const clubs = ref<Club[]>([])
const error = ref<string | null>(null)

const rulesNisn = {
  required: (value: string) => !!value || 'Harus diisi.',
  exactLength: (value: string) => value.length === 10 || 'Harus tepat 10 karakter',
};

const props = defineProps<{ data: PlayerData }>()
const emit = defineEmits(['submit', 'update:data'])

const localData = ref<PlayerData>({
  ...props.data,
  club_player: props.data.club_player || { 
    club_id: '',
    player_id: 0,
    back_number: '',   
    position: '',
    is_captain: false,   
    status: false, 
  },
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
  { title: 'Penjaga Gawang', value: 'goalkeeper' },
  { title: 'Bek', value: 'defender' },
  { title: 'Gelandang', value: 'midfielder' },
  { title: 'Penyerang', value: 'forward' },
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
  { title: 'Putri', value: 'female' },
  { title: 'U-6', value: 'u6' },
  { title: 'U-7', value: 'u7' },
  { title: 'U-8', value: 'u8' },
  { title: 'U-9', value: 'u9' },
  { title: 'U-10', value: 'u10' },
  { title: 'U-11', value: 'u11' },
  { title: 'U-12', value: 'u12' },
  { title: 'U-13', value: 'u13' },
  { title: 'U-14', value: 'u14' },
  { title: 'U-15', value: 'u15' },
  { title: 'U-16', value: 'u16' },
  { title: 'U-17', value: 'u17' },
  { title: 'U-18', value: 'u18' },
  { title: 'U-19', value: 'u19' },
  { title: 'U-20', value: 'u20' },
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
            <span>Files</span>
          </VTab>
        </VTabs>

        <VCardText>
          <VWindow v-model="currentTab">
            <!-- Tab Biodata -->
            <VWindowItem value="biodata">
              <div
                  v-if="localData.club_player?.club?.profile_club"
                  class="d-flex justify-end flex-column rounded bg-var-theme-background flex-sm-row gap-6 pa-6 mb-6"
                  >
                  <div class="d-flex align-center app-logo gap-4">
                      <img
                        v-if="localData.club_player?.club?.profile_club?.url"
                        :src="getImageUrl(localData.club_player.club.profile_club.url)"
                        alt="Logo Club"
                        style="width: 40px; height: 40px; border-radius: 8px; object-fit: cover;"
                      />
                      <h6 class="app-logo-title mb-0">
                      {{ localData.club_player?.club?.name }}
                      </h6>
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
                  
                  <AppTextField
                    v-model="localData.nisn"
                    label="NISN"
                    placeholder="Contoh: 1234567890"
                    class="mb-4"
                    maxlength="10"
                    :rules="[rulesNisn.required, rulesNisn.exactLength]"
                    hint="Harus tepat 10 karakter"
                    counter
                  />

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

                 <AppSelect
                    label="Club"
                    v-model="localData.club_player.club_id"
                    :items="clubs"
                    placeholder="Pilih Club"
                    clearable
                    clear-icon="tabler-x"
                    single-line
                    class="mb-4"
                  />
                  
                  <VRow class="mb-4">
                    <VCol cols="6">
                      <AppTextField
                        v-model="localData.club_player.back_number"
                        label="Nomor Punggung"
                        placeholder="Contoh: 07"
                        maxlength="3"
                      />
                    </VCol>

                    <VCol cols="6">
                      <AppSelect
                        label="Kategori"
                        v-model="localData.club_player.category"
                        :items="categories"
                        clearable
                        clear-icon="tabler-x"
                        single-line
                      />
                    </VCol>
                  </VRow>
                  
                  <AppSelect
                    label="Position"
                    v-model="localData.club_player.position"
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
                </VCol>
              </VRow>
            </VWindowItem>
          </VWindow>
        </VCardText>

        <!-- Tombol Submit -->
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
  </form>
</template>
