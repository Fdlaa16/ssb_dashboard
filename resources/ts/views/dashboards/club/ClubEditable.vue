<script setup lang="ts">
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import type { ClubData } from './types'

const clubs = ref<Club[]>([])
const error = ref<string | null>(null)

const rulesNisn = {
  required: (value: string) => !!value || 'Harus diisi.',
  exactLength: (value: string) => value.length === 10 || 'Harus tepat 10 karakter',
};

const rules = [
  (fileList: FileList) =>
    !fileList || !fileList.length || fileList[0].size < 1000000 || 'Ukuran gambar maksimal 1 MB!',
]

const props = defineProps<{ data: ClubData }>()
const emit = defineEmits(['submit', 'update:data'])

const localData = ref<ClubData>({
  ...props.data,
})

watch(() => props.data, (newVal) => {
  localData.value = props.data
}, { deep: true })

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
      <VCard title="Create Club">
        <VCardText>
          <VWindow>
            <div>
              <VRow>
                <VCol cols="12" class="text-no-wrap">
                  <img
                      v-if="localData.profile_club"
                      :src="getImageUrl(localData.profile_club.url)"
                      class="card-website-analytics-img"
                      style="width: 12%; filter: drop-shadow(0 4px 60px rgba(0, 0, 0, 50%));"
                    />
                    
                  <VFileInput
                    v-model="localData.profile_club"
                    :rules="rules"
                    label="Profile Club"
                    accept="image/png, image/jpeg, image/bmp"
                    class="mb-4"
                  />

                  <AppTextField
                    v-model="localData.name"
                    label="name"
                    placeholder="Contoh: Nama Club"
                  />
                </VCol>
              </VRow>
            </div>
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
