<script setup lang="ts">
import { nextTick, onBeforeUnmount, ref, watch } from 'vue';
import type { MediaData } from './types';

const props = defineProps<{ data: MediaData }>()
const emit = defineEmits(['submit', 'update:data'])

const localData = ref<MediaData>({ ...props.data })
const currentTab = ref('biodata')
const documentMediaPreviews = ref<string[]>([])
const MAX_IMAGES = 5

const getImageUrl = (path: string) => import.meta.env.VITE_APP_URL + path

const initializePreviews = () => {
  if (localData.value.document_media && Array.isArray(localData.value.document_media)) {
    documentMediaPreviews.value = localData.value.document_media.map((item: any) => {
      if (item instanceof File) {
        return URL.createObjectURL(item)
      } else if (typeof item === 'object' && item.path) {
        return getImageUrl(item.path)
      } else if (typeof item === 'string') {
        return getImageUrl(item)
      }
      return null
    }).filter(Boolean)
  }
}

watch(() => props.data, (newData) => {
  localData.value = JSON.parse(JSON.stringify(newData))
  nextTick(() => {
    initializePreviews()
  })
}, { deep: true, immediate: true })

watch(localData, (newVal, oldVal) => {
  if (JSON.stringify(newVal) !== JSON.stringify(oldVal)) {
    emit('update:data', newVal)
  }
}, { deep: true })

watch(() => localData.value.document_media, (newDocumentMedia: any) => {
  if (newDocumentMedia && Array.isArray(newDocumentMedia)) {
    documentMediaPreviews.value.forEach(url => {
      if (url?.startsWith('blob:')) {
        URL.revokeObjectURL(url)
      }
    })
    
    documentMediaPreviews.value = newDocumentMedia.map((item: any) => {
      if (item instanceof File) {
        return URL.createObjectURL(item)
      } else if (typeof item === 'object' && item.path) {
        return getImageUrl(item.path)
      } else if (typeof item === 'string') {
        return getImageUrl(item)
      }
      return null
    }).filter(Boolean)
  } else {
    documentMediaPreviews.value.forEach(url => {
      if (url?.startsWith('blob:')) {
        URL.revokeObjectURL(url)
      }
    })
    documentMediaPreviews.value = []
  }
}, { immediate: true })

const submitForm = () => {
  emit('update:data', localData.value)
  emit('submit')
}

const onFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  const files = target.files
  
  if (files && files.length > 0) {
    const currentCount = documentMediaPreviews.value.length
    const availableSlots = MAX_IMAGES - currentCount
    const selectedFiles = Array.from(files).slice(0, availableSlots)
    
    const newPreviews = selectedFiles.map(file => URL.createObjectURL(file))
    
    documentMediaPreviews.value.push(...newPreviews)
    
    if (!localData.value.document_media) {
      localData.value.document_media = []
    }
    
    if (Array.isArray(localData.value.document_media)) {
      localData.value.document_media.push(...selectedFiles)
    } else {
      localData.value.document_media = selectedFiles
    }
    
    target.value = ''
  }
}

const getFileName = (index: number) => {
  if (localData.value.document_media && Array.isArray(localData.value.document_media)) {
    const file = localData.value.document_media[index]
    if (file instanceof File) {
      return file.name
    } else if (typeof file === 'object' && file.original_name) {
      return file.original_name
    } else if (typeof file === 'string') {
      return file.split('/').pop() || `Image ${index + 1}`
    }
  }
  return `Image ${index + 1}`
}

const removeImage = (index: number) => {
  if (documentMediaPreviews.value[index]?.startsWith('blob:')) {
    URL.revokeObjectURL(documentMediaPreviews.value[index])
  }
  
  documentMediaPreviews.value.splice(index, 1)
  
  if (localData.value.document_media && Array.isArray(localData.value.document_media)) {
    localData.value.document_media.splice(index, 1)
  }
}

onBeforeUnmount(() => {
  documentMediaPreviews.value.forEach(url => {
    if (url?.startsWith('blob:')) {
      URL.revokeObjectURL(url)
    }
  })
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

                  <h6 class="text-h6 mb-2">Document Media (Maksimal 5 gambar)</h6>                  
                  <div v-if="documentMediaPreviews.length > 0" class="mb-4">
                    <VRow>
                      <VCol 
                        v-for="(preview, index) in documentMediaPreviews" 
                        :key="`preview-${index}`"
                        cols="12" 
                        sm="6" 
                        md="4"
                        class="d-flex flex-column align-center"
                      >
                        <div class="position-relative">
                          <img
                            :src="preview"
                            :alt="`Preview ${index + 1}`"
                            style="width: 100%; max-width: 200px; height: 150px; object-fit: cover; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.2);"
                            @error="(e) => { e.target.style.display = 'none' }"
                          />
                          <VBtn
                            icon="tabler-x"
                            size="small"
                            color="error"
                            variant="elevated"
                            class="position-absolute"
                            style="top: -8px; right: -8px; z-index: 1;"
                            @click="removeImage(index)"
                          />

                          <div class="text-caption text-center mt-2" style="max-width: 200px; word-break: break-word;">
                            {{ getFileName(index) }}
                          </div>
                        </div>
                      </VCol>
                    </VRow>
                  </div>

                  <VFileInput
                    label="Pilih Gambar (Maksimal 5)"
                    accept="image/png, image/jpeg, image/bmp"
                    multiple
                    :disabled="documentMediaPreviews.length >= MAX_IMAGES"
                    density="comfortable"
                    class="mb-4"
                    @change="onFileSelect"
                  >
                    <template #selection="{ fileNames }">
                      <span class="text-caption text-grey">
                        {{ documentMediaPreviews.length }} dari {{ MAX_IMAGES }} gambar dipilih
                      </span>
                    </template>
                  </VFileInput>

                  <VAlert
                    v-if="documentMediaPreviews.length >= MAX_IMAGES"
                    type="info"
                    variant="tonal"
                    class="mb-4"
                  >
                    <VAlertTitle>Batas Maksimal Tercapai</VAlertTitle>
                    Anda sudah mengupload {{ MAX_IMAGES }} gambar (maksimal). Hapus gambar yang ada jika ingin mengganti dengan gambar baru.
                  </VAlert>

                  <VAlert
                    v-else-if="documentMediaPreviews.length > 0"
                    type="success"
                    variant="tonal"
                    class="mb-4"
                  >
                    <VAlertTitle>{{ documentMediaPreviews.length }} Gambar Terpilih</VAlertTitle>
                    Anda dapat menambah {{ MAX_IMAGES - documentMediaPreviews.length }} gambar lagi.
                  </VAlert>

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
