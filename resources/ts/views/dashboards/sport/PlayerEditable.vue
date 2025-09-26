<script setup lang="ts">
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import type { Club, PlayerData } from './types'

const currentTab = ref('biodata')
const clubs = ref<Club[]>([])
const error = ref<string | null>(null)

const rulesNisn = {
  required: (value: string) => !!value || 'Harus diisi.',
  maxLength: (value: string) =>
    (value?.length <= 13) || 'Maksimal 13 karakter',
};

const rules = [
  (fileList: FileList) =>
    !fileList || !fileList.length || fileList[0].size < 1000000 || 'Ukuran gambar maksimal 1 MB!',
]

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

watch(() => props.data, (newVal) => {
  localData.value = props.data
}, { deep: true })

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

watch(localData, (newVal, oldVal) => {
  if (JSON.stringify(newVal) !== JSON.stringify(oldVal)) {
    emit('update:data', newVal)
  }
}, { deep: true })

const submitForm = () => {
  emit('update:data', localData) 
  emit('submit')
}

const getImageUrl = (path: string) => {  
  return import.meta.env.VITE_APP_URL + path
}
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
              <div class="d-flex justify-end flex-column rounded bg-var-theme-background flex-sm-row gap-6 pa-6 mb-6">
                <div class="d-flex align-end app-logo">
                  <VNodeRenderer :nodes="themeConfig.app.logo" />
                  <h6 class="app-logo-title">SSB Balaraja</h6>
                </div>
              </div>

              <VRow>
                <VCol cols="4" class="d-flex justify-center align-center text-no-wrap">
                  <img
                      v-if="localData.avatar"
                      :src="getImageUrl(localData.avatar.url)"
                      class="card-website-analytics-img"
                      style="width: 12%; filter: drop-shadow(0 4px 60px rgba(0, 0, 0, 50%));"
                    />
                    
                  <VFileInput
                    v-model="localData.avatar"
                    :rules="rules"
                    label="Kartu Keluarga"
                    accept="image/png, image/jpeg, image/bmp"
                  />
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
                    maxlength="13"
                    :rules="[rulesNisn.required, rulesNisn.maxLength]"
                    hint="Maksimal 13 karakter"
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
                      v-if="localData.family_card"
                      :src="getImageUrl(localData.family_card.url)"
                      class="card-website-analytics-img"
                      style="width: 12%; filter: drop-shadow(0 4px 60px rgba(0, 0, 0, 50%));"
                    />
                    
                    <VFileInput
                      v-model="localData.family_card"
                      :rules="rules"
                      accept="image/png, image/jpeg, image/bmp"
                    />
                  </div>

                  <div class="text-h6 mt-4">
                    <h6 class="text-h6 mb-2">Rapor Terakhir</h6>
                    <img
                      v-if="localData.report_grades"
                      :src="getImageUrl(localData.report_grades.url)"
                      class="card-website-analytics-img"
                      style="width: 12%; filter: drop-shadow(0 4px 60px rgba(0, 0, 0, 50%));"
                    />
                    <VFileInput
                      v-model="localData.report_grades"
                      :rules="rules"
                      accept="image/png, image/jpeg, image/bmp"
                    />
                  </div>

                  <div class="text-h6 mt-4">
                    <h6 class="text-h6 mb-2">Akte Kelahiran</h6>
                    <img
                      v-if="localData.birth_certificate"
                      :src="getImageUrl(localData.birth_certificate.url)"
                      class="card-website-analytics-img"
                      style="width: 12%; filter: drop-shadow(0 4px 60px rgba(0, 0, 0, 50%));"
                    />
                    <VFileInput
                      v-model="localData.birth_certificate"
                      :rules="rules"
                      accept="image/png, image/jpeg, image/bmp"
                    />
                  </div>

                  <div class="text-h6 mt-4">
                    <h6 class="text-h6 mb-2">Bukti Pendaftaran</h6>
                    <img
                      v-if="localData.proof_payment"
                      :src="getImageUrl(localData.proof_payment.url)"
                      class="card-website-analytics-img"
                      style="width: 12%; filter: drop-shadow(0 4px 60px rgba(0, 0, 0, 50%));"
                    />
                    <VFileInput
                      v-model="localData.proof_payment"
                      :rules="rules"
                      accept="image/png, image/jpeg, image/bmp"
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
