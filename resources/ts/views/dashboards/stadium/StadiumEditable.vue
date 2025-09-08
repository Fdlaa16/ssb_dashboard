<script setup lang="ts">
import type { StadiumData } from './types';

const error = ref<string | null>(null)

const rules = [
  (file: File | null) => {
    if (!file) return true
    return file.size < 1000000 || 'Ukuran gambar maksimal 1 MB!'
  },
]

const props = defineProps<{ data: StadiumData }>()
const emit = defineEmits(['submit', 'update:data'])

const localData = ref<StadiumData>({
  ...props.data,
})

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
</script>

<template>
  <form @submit.prevent="$emit('submit')">
    <div class="d-flex flex-column gap-6 mb-6">
      <VCard :title="props.data.id ? 'Ubah Stadium' : 'Tambah Stadium'">
        <VCardText>
          <VWindow>
            <div>
              <VRow>
                <VCol cols="12" class="text-no-wrap">
                 <VRow class="mb-4">
                    <VCol cols="6">
                      <AppTextField
                        v-model="localData.name"
                        label="Nama"
                        placeholder="Contoh: Nama Stadium"
                      />
                    </VCol>

                    <VCol cols="6">
                      <AppTextField
                        v-model="localData.area"
                        label="Daerah"
                        placeholder="Contoh: Nama Area"
                      />
                    </VCol>
                  </VRow>
                    
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
