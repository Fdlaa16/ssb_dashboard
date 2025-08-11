<script setup lang="ts">
import type { StructureData } from './types';

const currentTab = ref('biodata')

const props = defineProps<{ data: StructureData }>()
const emit = defineEmits(['submit', 'update:data'])

const localData = ref<StructureData>({
  ...props.data,
})

const avatarPreview = ref<string | null>(
  props.data.avatar?.url ? getImageUrl(props.data.avatar.url) : null
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

const departments = [
  { title: 'Pilih Posisi', value: '' },
  { title: 'Ketua Umum', value: 'chief' },
  { title: 'Official', value: 'official' },
];

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

onBeforeUnmount(() => {
  if (avatarPreview.value?.startsWith('blob:')) {
    URL.revokeObjectURL(avatarPreview.value)
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

                  <AppTextField
                    v-model="localData.name"
                    label="Nama Lengkap"
                    placeholder="Contoh: Budi Setiawan"
                    class="mb-4"
                  />

                  <VRow class="mb-4">
                    <VCol cols="6">
                      <AppDateTimePicker
                        v-model="localData.date_of_birth"
                        label="Tanggal Lahir"
                        placeholder="Tanggal Lahir"
                      />
                    </VCol>

                    <VCol cols="6">
                      <AppSelect
                        label="Department"
                        v-model="localData.department"
                        :items="departments"
                        clearable
                        clear-icon="tabler-x"
                        single-line
                      />
                    </VCol>
                  </VRow>
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
