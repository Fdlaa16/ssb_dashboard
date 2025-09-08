<script setup lang="ts">
import { nextTick, onBeforeUnmount, ref, watch } from 'vue';
import type { MediaData } from './types';

const props = defineProps<{ data: MediaData }>()
const emit = defineEmits(['submit', 'update:data'])

const localData = ref<MediaData>({ ...props.data })
const currentTab = ref('biodata')
const documentMediaPreviews = ref<string[]>([])
const MAX_IMAGES = 5
const removedMediaIds = ref<number[]>([]) // Track removed media IDs

const getImageUrl = (path: string) => {
  // Handle both old format (path) and new format (url)
  if (path.startsWith('http')) {
    return path
  }
  return import.meta.env.VITE_APP_URL + '/storage/' + path
}

const initializePreviews = () => {
  if (localData.value.document_media && Array.isArray(localData.value.document_media)) {
    documentMediaPreviews.value = localData.value.document_media.map((item: any) => {
      if (item instanceof File) {
        return URL.createObjectURL(item)
      } else if (typeof item === 'object' && item.url) {
        // Use .url property like in avatar implementation
        return getImageUrl(item.url)
      } else if (typeof item === 'object' && item.path) {
        // Fallback for old path property
        return getImageUrl(item.path)
      } else if (typeof item === 'string') {
        return getImageUrl(item)
      }
      return null
    }).filter(Boolean)
  }
  // Reset removed media IDs when initializing
  removedMediaIds.value = []
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
    // Revoke old blob URLs
    documentMediaPreviews.value.forEach(url => {
      if (url?.startsWith('blob:')) {
        URL.revokeObjectURL(url)
      }
    })
    
    documentMediaPreviews.value = newDocumentMedia.map((item: any) => {
      if (item instanceof File) {
        return URL.createObjectURL(item)
      } else if (typeof item === 'object' && item.url) {
        // Use .url property like in avatar implementation
        return getImageUrl(item.url)
      } else if (typeof item === 'object' && item.path) {
        // Fallback for old path property
        return getImageUrl(item.path)
      } else if (typeof item === 'string') {
        return getImageUrl(item)
      }
      return null
    }).filter(Boolean)
  } else {
    // Clean up blob URLs
    documentMediaPreviews.value.forEach(url => {
      if (url?.startsWith('blob:')) {
        URL.revokeObjectURL(url)
      }
    })
    documentMediaPreviews.value = []
  }
}, { immediate: true })

const submitForm = () => {
  // Add removed media IDs to localData before emitting
  const dataWithRemovedIds = {
    ...localData.value,
    removed_media_ids: removedMediaIds.value
  }
  emit('update:data', dataWithRemovedIds)
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
    } else if (typeof file === 'object' && file.url) {
      // Extract filename from URL
      return file.url.split('/').pop() || `Image ${index + 1}`
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
  
  // Check if it's an existing file (not a new File object)
  if (localData.value.document_media && Array.isArray(localData.value.document_media)) {
    const file = localData.value.document_media[index]
    if (typeof file === 'object' && file.id && !(file instanceof File)) {
      // It's an existing file, add to removed list
      removedMediaIds.value.push(file.id)
    }
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

const typeMedia = ref([
  { title: 'Dokumentasi', value: 'documentation' },
  { title: 'Prestasi', value: 'performance' },
])
</script>

<template>
  <form @submit.prevent="$emit('submit')">
    <div class="d-flex flex-column gap-6 mb-6">
      <VCard :title="props.data.id ? 'Ubah Media' : 'Tambah Media'">
        <VCardText>
          <VWindow>
            <!-- Tab Biodata -->
            <VWindowItem >
              <VRow>
                <VCol cols="12" class="text-no-wrap">
                  <h6 class="text-h6 mb-1 mt-5">Tipe Media</h6>
                  <AppSelect
                    v-model="localData.type_media"
                    :items="typeMedia"
                    placeholder="Status"
                    clearable
                    clear-icon="tabler-x"
                    single-line
                    class="mb-4"
                  />

                  <!-- <template v-if="localData.type_media === 'documentation'">
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
                  </template> -->

                  <h6 class="text-h6 mb-1 mt-5">Judul</h6>
                  <AppTextField
                    v-model="localData.title"
                    placeholder="Title"
                    class="mb-4"
                  />

                  <h6 class="text-h6 mb-1 mt-5">Deskripsi</h6>
                  <TiptapEditor
                    v-model="localData.description"
                    class="border rounded basic-editor"
                    placeholder="Masukkan deskripsi media"
                  />

                  <h6 class="text-h6 mb-1 mt-5">Link</h6>
                  <AppTextField
                    v-model="localData.link"
                    placeholder="Link"
                    class="mb-4"
                  />

                  <h6 class="text-h6 mb-2">Documen Media (Maksimal 5 gambar)</h6>                  
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

<style lang="scss">
.basic-editor {
  .ProseMirror {
    block-size: 200px;
    outline: none;
    overflow-y: auto;
    padding-inline: 0.5rem;
  }
}
</style>
