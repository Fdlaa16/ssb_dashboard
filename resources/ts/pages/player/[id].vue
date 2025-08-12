<script setup lang="ts">
import { useConfigStore } from '@/@core/stores/config'
import Footer from '@/views/front-pages/front-page-footer.vue'
import Navbar from '@/views/front-pages/front-page-navbar.vue'
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const playerId = computed(() => route.params.id)

const detail = ref<any>(null)
const loading = ref(true)
const error = ref<string | null>(null)

const store = useConfigStore()
store.skin = 'default'
definePage({
  meta: {
    layout: 'blank',
    public: true,
  },
})

const currentTab = ref('biodata')

const fetchPlayerDetail = async () => {
  try {
    const response = await $api(`company/player/${playerId.value}`, {
      method: 'GET'
    })
    detail.value = response.data
  } catch (err: any) {
    error.value = err.message || 'Gagal memuat detail player'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  if (playerId.value) {
    fetchPlayerDetail()
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

const getImageUrl = (path: string) => {  
  return import.meta.env.VITE_APP_URL + path
}

const getPositionLabel = (position: string) => {
  const positions = {
    'goalkeeper': 'Penjaga Gawang',
    'defender': 'Bek',
    'midfielder': 'Gelandang',
    'forward': 'Penyerang',
  }
  return positions[position] || position
}

const categories = [
  { title: 'Pilih Kategori', value: '' },
  { title: 'Tim Utama', value: 'main' },
  { title: 'U-9', value: 'u9' },
  { title: 'U-10', value: 'u10' },
  { title: 'U-11', value: 'u11' },
  { title: 'U-12', value: 'u12' },
  { title: 'U-13', value: 'u13' },
  { title: 'U-14', value: 'u14' },
  { title: 'U-15', value: 'u15' },
]

const getCategoryLabel = (category: string) => {
  const found = categories.find(cat => cat.value === category)
  return found ? found.title : '-'
}
</script>

<template>
  <Navbar />
  <VContainer style="margin-top: 100px;">
    <VRow justify="center">
      <VCol cols="12" md="10">
        <template v-if="loading">
          <VAlert type="info" border="start" color="primary" variant="tonal">
            Memuat detail player...
          </VAlert>
        </template>

        <template v-else-if="error">
          <VAlert type="error" border="start" color="error" variant="tonal">
            {{ error }}
          </VAlert>
        </template>

        <template v-else>
          <VCard>
            <!-- Tab Navigasi -->

            <VCardText>
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
                      v-if="detail?.avatar?.url"
                      :src="getImageUrl(detail.avatar.url)"
                      alt="Avatar Player"
                      style="width: 100%; margin-bottom: 1rem;"
                    />
                    <div v-else class="text-center text-grey">
                      <VIcon icon="tabler-user" size="100" />
                      <p>Tidak ada foto</p>
                    </div>
                  </div>
                </VCol>

                <VCol cols="8" class="text-no-wrap">
                  <VRow>
                    <VCol cols="4" class="text-subtitle-2 font-weight-bold">Email:</VCol>
                    <VCol cols="8">{{ detail?.user?.email || '-' }}</VCol>
                  </VRow>
                  
                  <VRow>
                    <VCol cols="4" class="text-subtitle-2 font-weight-bold">NISN:</VCol>
                    <VCol cols="8">{{ detail?.nisn || '-' }}</VCol>
                  </VRow>

                  <VRow>
                    <VCol cols="4" class="text-subtitle-2 font-weight-bold">Nama Lengkap:</VCol>
                    <VCol cols="8">{{ detail?.name || '-' }}</VCol>
                  </VRow>

                  <VRow>
                    <VCol cols="4" class="text-subtitle-2 font-weight-bold">Tinggi Badan:</VCol>
                    <VCol cols="8">{{ detail?.height ? detail.height + ' cm' : '-' }}</VCol>
                  </VRow>

                  <VRow>
                    <VCol cols="4" class="text-subtitle-2 font-weight-bold">Berat Badan:</VCol>
                    <VCol cols="8">{{ detail?.weight ? detail.weight + ' kg' : '-' }}</VCol>
                  </VRow>

                  <!-- <VRow>
                    <VCol cols="4" class="text-subtitle-2 font-weight-bold">Club:</VCol>
                    <VCol cols="8">{{ detail.club?.name || '-' }}</VCol>
                  </VRow> -->
                  
                  <VRow>
                    <VCol cols="4" class="text-subtitle-2 font-weight-bold">Nomor Punggung:</VCol>
                    <VCol cols="8">{{ detail.back_number || '-' }}</VCol>
                  </VRow>

                  <VRow>
                    <VCol cols="4" class="text-subtitle-2 font-weight-bold">Kategori:</VCol>
                    <VCol cols="8">{{ getCategoryLabel(detail.category) }}</VCol>
                  </VRow>
                  
                  <VRow>
                    <VCol cols="4" class="text-subtitle-2 font-weight-bold">Posisi:</VCol>
                    <VCol cols="8">{{ getPositionLabel(detail.position) }}</VCol>
                  </VRow>

                  <VRow>
                    <VCol cols="4" class="text-subtitle-2 font-weight-bold">Status:</VCol>
                    <VCol cols="8">
                      <VChip 
                        :color="detail?.status === 1 ? 'success' : 'error'"
                        size="small"
                      >
                        {{ detail?.status === 1 ? 'Aktif' : 'Tidak Aktif' }}
                      </VChip>
                    </VCol>
                  </VRow>

                  <VRow>
                    <VCol cols="4" class="text-subtitle-2 font-weight-bold">Tanggal Dibuat:</VCol>
                    <VCol cols="8">{{ formatTanggalIndonesia(detail?.created_at) }}</VCol>
                  </VRow>
                </VCol>
              </VRow>
            </VCardText>
          </VCard>
        </template>
      </VCol>
    </VRow>
  </VContainer>
  <Footer />
</template>
