<script setup lang="ts">
import type { ClubData } from './types';

const error = ref<string | null>(null)

const rules = [
  (file: File | null) => {
    if (!file) return true
    return file.size < 1000000 || 'Ukuran gambar maksimal 1 MB!'
  },
]

const props = defineProps<{ data: ClubData }>()
const emit = defineEmits(['submit', 'update:data'])

const localData = ref<ClubData>({
  ...props.data,
})

const profileClubPreview = ref<string | null>(
  props.data.profile_club?.url ? getImageUrl(props.data.profile_club.url) : null
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

watch(() => localData.value.profile_club, (newProfileClub: any) => {
  if (newProfileClub instanceof File) {
    profileClubPreview.value = URL.createObjectURL(newProfileClub)
  } else if (newProfileClub?.url) {
    profileClubPreview.value = getImageUrl(newProfileClub.url)
  } else {
    profileClubPreview.value = null
  }
})

onBeforeUnmount(() => {
  if (profileClubPreview.value?.startsWith('blob:')) {
    URL.revokeObjectURL(profileClubPreview.value)
  }
})
</script>

<template>
  <form @submit.prevent="$emit('submit')">
    <div class="d-flex flex-column gap-6 mb-6">
      <VCard :title="props.data.id ? 'Edit Club' : 'Create Club'">
        <VCardText>
          <VWindow>
            <div>
              <VRow>
                <VCol cols="12" class="text-no-wrap">
                  <h6 class="text-h6 mb-2">Profile Club</h6>
                  <img
                    v-if="profileClubPreview"
                    :src="profileClubPreview"
                    alt="Preview Profile Club"
                    style="width: 30%; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.2); margin-bottom: 1rem;"
                  />

                  <VFileInput
                    v-model="localData.profile_club"
                    :rules="rules"
                    label="Ganti Foto"
                    accept="image/png, image/jpeg, image/bmp"
                    density="comfortable"
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
