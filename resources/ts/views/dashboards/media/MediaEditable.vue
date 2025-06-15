<script setup lang="ts">
import type { MediaData } from './types'

const currentTab = ref('biodata')
const clubs = ref<Club[]>([])
const error = ref<string | null>(null)
const toggleSwitch = ref(true)
const toggleFalseSwitch = ref(false)

const rulesNisn = {
  required: (value: string) => !!value || 'Harus diisi.',
  exactLength: (value: string) => value.length === 10 || 'Harus tepat 10 karakter',
};

const rules = [
  (fileList: FileList) =>
    !fileList || !fileList.length || fileList[0].size < 1000000 || 'Ukuran gambar maksimal 1 MB!',
]

const props = defineProps<{ data: MediaData }>()
const emit = defineEmits(['submit', 'update:data'])

const localData = ref<MediaData>({
  ...props.data,
  status: props.data.status || 'active',
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

const capitalizedLabel = (label: boolean) => {
  const convertLabelText = label.toString()

  return convertLabelText.charAt(0).toUpperCase() + convertLabelText.slice(1)
}
</script>

<template>
  <form @submit.prevent="$emit('submit')">
    <div class="d-flex flex-column gap-6 mb-6">
      <VCard title="Create Media">
        <VCardText>
          <VWindow>
            <!-- Tab Biodata -->
            <VWindowItem >

              <VRow>
                <VCol cols="12" class="text-no-wrap">
                  <VRow class="justify-center align-center">
                    <VCol cols="10" class="text-no-wrap">  
                      <AppTextField
                        v-model="localData.name"
                        label="Name"
                        placeholder="Name"
                        class="mb-4"
                      />
                    </VCol>
                    
                    <VCol cols="2" class="text-no-wrap mt-5">
                      <VSwitch
                        v-model="localData.status"
                        :label="localData.status === 1 ? 'Active' : 'Non Active'"
                        :true-value= 1
                        :false-value= 0
                      />
                    </VCol>
                  </VRow>

                  <AppTextField
                    v-model="localData.title"
                    label="Title"
                    placeholder="Title"
                    class="mb-4"
                    maxlength="3"
                  />

                  <AppTextField
                    v-model="localData.hashtag"
                    label="Hashtag"
                    placeholder="Hashtag"
                    class="mb-4"
                    maxlength="3"
                  />

                  <DemoTextareaBasic 
                    v-model="localData.description"
                    label="Description"
                    placeholder="Masukkan deskripsi media"
                    class="mb-4"
                  />

                  <AppTextField
                    v-model="localData.link"
                    label="Link"
                    placeholder="Link"
                    class="mb-4"
                    maxlength="3"
                  />

                  <h6 class="text-h6 mb-2">Document Media</h6>
                  <img
                    v-if="localData.document_media"
                    :src="getImageUrl(localData.document_media.url)"
                    class="card-website-analytics-img"
                    style="width: 12%; filter: drop-shadow(0 4px 60px rgba(0, 0, 0, 50%));"
                  />
                  
                  <VFileInput
                    v-model="localData.document_media"
                    :rules="rules"
                    accept="image/png, image/jpeg, image/bmp"
                  />
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
