<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const mediaId = computed(() => route.params.id)

const detail = ref<any>(null)
const loading = ref(true)
const error = ref<string | null>(null)

const fetchMediaDetail = async () => {
  try {
    const response = await $api(`company/media/${mediaId.value}`, {
      method: 'GET'
    })
    detail.value = response.data
    console.log('Media Detail:', response)
  } catch (err: any) {
    error.value = err.message || 'Gagal memuat detail media'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  console.log('Mounted. mediaId:', mediaId.value)
  if (mediaId.value) {
    fetchMediaDetail()
  } else {
    error.value = 'ID tidak ditemukan'
    loading.value = false
  }
})

function formatTanggalIndonesia(dateString: string): string {
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  }).format(date)
}
</script>

<template>
  <VContainer>
    <VRow justify="center">
      <VCol cols="12" md="8">
        <template v-if="loading">
          <VAlert type="info" border="start" color="primary" variant="tonal">
            Memuat detail media...
          </VAlert>
        </template>

        <template v-else-if="error">
          <VAlert type="error" border="start" color="error" variant="tonal">
            {{ error }}
          </VAlert>
        </template>

        <template v-else>
          <h1 class="text-h5 font-weight-bold mb-4">{{ detail?.title }}</h1>
          <div class="text-caption text-grey mb-2">
            {{ formatTanggalIndonesia(detail?.start_date) }}
          </div>

          <VImg
            :src="detail?.document_media?.url"
            height="300"
            cover
            class="mb-3"
          />

          <div class="text-caption text-grey mb-4">
            {{ detail?.caption || 'Tidak ada caption tersedia.' }}
          </div>

          <div class="body-1" v-html="detail?.description"></div>
        </template>
      </VCol>
    </VRow>
  </VContainer>
</template>
