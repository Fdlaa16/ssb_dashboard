<script setup lang="ts">
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import type { Club, PlayerData, Sport } from './types'

// Gambar dummy

const currentTab = ref('biodata')
const sports = ref<Sport[]>([])
const clubs = ref<Club[]>([])
const error = ref<string | null>(null)
const show1 = ref(false)
const show2 = ref(true)
const password = ref('Password')
const confirmPassword = ref('wqfasds')

const rulesPassword = {
  required: (value: string) => !!value || 'Required.',
  min: (v: string) => v.length >= 8 || 'Min 8 characters',
}

const rulesNisn = {
  required: (value: string) => !!value || 'Harus diisi.',
  exactLength: (value: string) => value.length === 10 || 'Harus tepat 10 karakter',
};

const rules = [
  (fileList: FileList) =>
    !fileList || !fileList.length || fileList[0].size < 1000000 || 'Ukuran gambar maksimal 1 MB!',
]

const props = defineProps<{ data: PlayerData, sports: Sport[] }>()
const emit = defineEmits(['submit', 'update:data', 'update:selectedSports'])


const selectedItem = ref<number[]>([])
const items = computed(() => props.sports.map(s => s.name))

const localData = reactive<PlayerData>({ ...toRaw(props.data) })

watch(() => props.data, (newVal) => {
  Object.assign(localData, toRaw(newVal))
}, { deep: true })

watch(() => localData.sport_players, (newVal) => {
  if (newVal && newVal.length > 0) {
    selectedItem.value = newVal
      .filter(sp => sp.sport !== undefined && sp.sport !== null)
      .map(sp => sp.sport.id)
  } else {
    selectedItem.value = []
  }
}, { immediate: true })


// const localData = ref<PlayerData>(JSON.parse(JSON.stringify(props.data))) 
// const localData = reactive({ ...props.data })

const getSports = async() => {
  try {
    const res = await $api('sport')
    sports.value = res.data

    console.log('sport', sports.value);
    
  } catch (error: any) {
    error.value = 'Gagal memuat data olahraga'
  }
}

const getClubs = async() => {
  try {
    const res = await $api('club')
    clubs.value = res.data

    console.log('clubs ', clubs.value);
    
  } catch (error: any) {
    error.value = 'Gagal memuat data olahraga'
  }
}

onMounted(async () => {
  getSports()
  getClubs()
})

// watch(() => props.data.sport_players, (newVal) => {
//   if (newVal && newVal.length > 0) {
//     selectedItem.value = newVal
//       .filter(sp => sp.sport !== undefined && sp.sport !== null)  // filter aman
//       .map(sp => sp.sport.id)
//   } else {
//     selectedItem.value = []
//   }
// }, { immediate: true })

// watch(() => props.data, (newVal) => {
//   Object.assign(localData, newVal)
// }, { deep: true, immediate: true })


watch(localData, () => {
  emit('update:data', localData)
}, { deep: true })


watch(selectedItem, (val) => {
  emit('update:selectedSports', val)

  localData.value.sport_players = val.map(sportId => {
    const sport = sports.value.find(s => s.id === sportId)
    return {
      id: 0,
      sport: {
        id: sport?.id || 0,
        code: sport?.code || '',
        name: sport?.name || '',
      },
    }
  })
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
              <div class="d-flex justify-end flex-column rounded bg-var-theme-background flex-sm-row gap-6 pa-6 mb-6">
                <div class="d-flex align-end app-logo">
                  <VNodeRenderer :nodes="themeConfig.app.logo" />
                  <h6 class="app-logo-title">SSB Balaraja</h6>
                </div>
              </div>

              <VRow>
                <VCol cols="4" class="d-flex justify-center align-center text-no-wrap">
                  <!-- <VNodeRenderer v-if="themeConfig.app.logo" :nodes="themeConfig.app.logo" /> -->
                  <VFileInput
                    v-model="localData.avatar"
                    :rules="rules"
                    label="Kartu Keluarga"
                    accept="image/png, image/jpeg, image/bmp"
                  />
                </VCol>

                <VCol cols="8" class="text-no-wrap">
                  <AppTextField
                    v-model="localData.email"
                    label="Email"
                    placeholder="Contoh: admin@gmail.com"
                    class="mb-4"
                  />

                  <AppTextField
                    v-model="localData.password"
                    :append-inner-icon="show1 ? 'tabler-eye-off' : 'tabler-eye' "
                    :rules="[rulesPassword.required, rulesPassword.min]"
                    :type="show1 ? 'text' : 'password'"
                    name="input-10-1"
                    label="Password"
                    hint="Minimal 8 karakter"
                    placeholder="············"
                    counter
                    @click:append-inner="show1 = !show1"
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

                  <AppCombobox
                    v-model="selectedItem"
                    :items="sports"
                    item-title="name"
                    item-value="id"
                    placeholder="Pilih olahraga favorit"
                    label="Olahraga Favorit"
                    multiple
                    chips
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
                    <!-- <img
                      v-if="sliderBar1"
                      :src="sliderBar1"
                      class="card-website-analytics-img"
                      style="width: 12%; filter: drop-shadow(0 4px 60px rgba(0, 0, 0, 50%));"
                    > -->
                    <VFileInput
                      v-model="localData.family_card"
                      :rules="rules"
                      accept="image/png, image/jpeg, image/bmp"
                    />
                  </div>

                  <div class="text-h6 mt-4">
                    <h6 class="text-h6 mb-2">Rapor Terakhir</h6>
                    <!-- <img
                      v-if="sliderBar2"
                      :src="sliderBar2"
                      class="card-website-analytics-img"
                      style="width: 12%; filter: drop-shadow(0 4px 60px rgba(0, 0, 0, 50%));"
                    > -->
                    <VFileInput
                      v-model="localData.report_grades"
                      :rules="rules"
                      accept="image/png, image/jpeg, image/bmp"
                    />
                  </div>

                  <div class="text-h6 mt-4">
                    <h6 class="text-h6 mb-2">Akte Kelahiran</h6>
                    <!-- <img
                      v-if="sliderBar3"
                      :src="sliderBar3"
                      class="card-website-analytics-img"
                      style="width: 12%; filter: drop-shadow(0 4px 60px rgba(0, 0, 0, 50%));"
                    > -->
                    <VFileInput
                      v-model="localData.birth_certificate"
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
          <VBtn type="submit">
            Submit
          </VBtn>
        </VCol>
      </VCard>
    </div>
  </form>
</template>
