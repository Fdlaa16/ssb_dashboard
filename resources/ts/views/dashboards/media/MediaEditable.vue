<script setup lang="ts">
import { onBeforeUnmount, ref, watch } from 'vue';
import type { MediaData } from './types';

const props = defineProps<{ data: MediaData }>()
const emit = defineEmits(['submit', 'update:data'])

const localData = ref<MediaData>({ ...props.data })
const currentTab = ref('biodata')
const documentMediaPreview = ref<string | null>(null)

watch(() => props.data.id, (newId, oldId) => {
  if (newId !== oldId) {
    localData.value = JSON.parse(JSON.stringify(props.data))
  }
}, { immediate: true })

watch(localData, (newVal, oldVal) => {
  if (JSON.stringify(newVal) !== JSON.stringify(oldVal)) {
    emit('update:data', newVal)
  }
}, { deep: true })

const submitForm = () => {
  emit('update:data', localData.value)
  emit('submit')
}

const getImageUrl = (path: string) => import.meta.env.VITE_APP_URL + path

watch(() => localData.value.document_media, (newDocumentMedia: any) => {
  if (newDocumentMedia instanceof File) {
    documentMediaPreview.value = URL.createObjectURL(newDocumentMedia)
  } else if (newDocumentMedia?.url) {
    documentMediaPreview.value = getImageUrl(newDocumentMedia.url)
  } else {
    documentMediaPreview.value = null
  }
})

onBeforeUnmount(() => {
  if (documentMediaPreview.value?.startsWith('blob:')) {
    URL.revokeObjectURL(documentMediaPreview.value)
  }
})

</script>

<template>
  <form @submit.prevent="$emit('submit')">
    <div class="d-flex flex-column gap-6 mb-6">
      <VCard :title="props.data.id ? 'Edit Media' : 'Create Media'">
        <VCardText>
          <VWindow>
            <!-- Tab Biodata -->
            <VWindowItem >

              <VRow>
                <VCol cols="12" class="text-no-wrap">
                  <VRow>
                    <VCol cols="6">
                      <AppDateTimePicker
                        v-model="localData.start_date"
                        label="Start Date"
                        placeholder="Start date"
                        class="mb-4"
                      />
                    </VCol>

                    <VCol cols="6">
                      <AppDateTimePicker
                        v-model="localData.end_date"
                        label="End Date"
                        placeholder="End date"
                        class="mb-4"
                      />
                    </VCol>
                  </VRow>
                  <AppTextField
                    v-model="localData.name"
                    label="Name"
                    placeholder="Name"
                    class="mb-4"
                  />

                  <AppTextField
                    v-model="localData.title"
                    label="Title"
                    placeholder="Title"
                    class="mb-4"
                  />

                  <AppTextField
                    v-model="localData.hashtag"
                    label="Hashtag"
                    placeholder="Hashtag"
                    class="mb-4"
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
                  />

                  <h6 class="text-h6 mb-2">Document Media</h6>
                  <img
                    v-if="documentMediaPreview"
                    :src="documentMediaPreview"
                    alt="Preview Profile Club"
                    style="width: 30%; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.2); margin-bottom: 1rem;"
                  />

                  <VFileInput
                    v-model="localData.document_media"
                    :rules="rules"
                    label="Ganti Foto"
                    accept="image/png, image/jpeg, image/bmp"
                    density="comfortable"
                    class="mb-4"
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
