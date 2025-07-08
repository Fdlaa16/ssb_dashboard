<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const playerId = computed(() => route.params.id)

const detail = ref<any>(null)
const loading = ref(true)
const error = ref<string | null>(null)
const currentTab = ref('biodata')

const fetchPlayerDetail = async () => {
  try {
    const response = await $api(`company/player/${playerId.value}`, {
      method: 'GET'
    })
    detail.value = response.data
    console.log('player Detail:', response)
  } catch (err: any) {
    error.value = err.message || 'Gagal memuat detail player'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  console.log('Mounted. playerId:', playerId.value)
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
  { title: 'Putri', value: 'female' },
  { title: 'U-6', value: 'u6' },
  { title: 'U-7', value: 'u7' },
  { title: 'U-8', value: 'u8' },
  { title: 'U-9', value: 'u9' },
  { title: 'U-10', value: 'u10' },
  { title: 'U-11', value: 'u11' },
  { title: 'U-12', value: 'u12' },
  { title: 'U-13', value: 'u13' },
  { title: 'U-14', value: 'u14' },
  { title: 'U-15', value: 'u15' },
  { title: 'U-16', value: 'u16' },
  { title: 'U-17', value: 'u17' },
  { title: 'U-18', value: 'u18' },
  { title: 'U-19', value: 'u19' },
  { title: 'U-20', value: 'u20' },
]

const getCategoryLabel = (category: string) => {
  const found = categories.find(cat => cat.value === category)
  return found ? found.title : '-'
}
</script>

<template>
  <VContainer>
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
            <VTabs v-model="currentTab" grow stacked>
              <VTab value="biodata">
                <VIcon icon="tabler-user" class="mb-2" />
                <span>Biodata</span>
              </VTab>

              <VTab value="files">
                <VIcon icon="tabler-file" class="mb-2" />
                <span>Files</span>
              </VTab>
            </VTabs>

            <VCardText>
              <VWindow v-model="currentTab">
                <!-- Tab Biodata -->
                <VWindowItem value="biodata">
                 <div
                    v-if="detail?.club_players?.[0]?.club?.profile_club"
                    class="d-flex justify-end flex-column rounded bg-var-theme-background flex-sm-row gap-6 pa-6 mb-6"
                    >
                    <div class="d-flex align-center app-logo gap-4">
                        <img
                        v-if="detail?.club_players?.[0]?.club?.profile_club?.url"
                        :src="getImageUrl(detail.club_players[0].club.profile_club.url)"
                        alt="Logo Club"
                        style="width: 40px; height: 40px; border-radius: 8px; object-fit: cover;"
                        />
                        <h6 class="app-logo-title mb-0">
                        {{ detail.club_players[0].club.name }}
                        </h6>
                    </div>
                </div>

                  <VRow>
                    <VCol cols="4" class="d-flex flex-column align-center justify-center">
                      <div style="width: 100%; max-width: 300px;" class="text-center justify-center">
                        <img
                          v-if="detail?.avatar?.url"
                          :src="getImageUrl(detail.avatar.url)"
                          alt="Avatar Player"
                          style="width: 100%; border-radius: 8px; margin-bottom: 1rem;"
                        />
                        <div v-else class="text-center text-grey">
                          <VIcon icon="tabler-user" size="100" />
                          <p>Tidak ada foto</p>
                        </div>
                      </div>
                    </VCol>

                    <VCol cols="8" class="text-no-wrap">
                      <VRow class="mb-4">
                        <VCol cols="4" class="text-subtitle-2 font-weight-bold">Email:</VCol>
                        <VCol cols="8">{{ detail?.user?.email || '-' }}</VCol>
                      </VRow>
                      
                      <VRow class="mb-4">
                        <VCol cols="4" class="text-subtitle-2 font-weight-bold">NISN:</VCol>
                        <VCol cols="8">{{ detail?.nisn || '-' }}</VCol>
                      </VRow>

                      <VRow class="mb-4">
                        <VCol cols="4" class="text-subtitle-2 font-weight-bold">Nama Lengkap:</VCol>
                        <VCol cols="8">{{ detail?.name || '-' }}</VCol>
                      </VRow>

                      <VRow class="mb-4">
                        <VCol cols="4" class="text-subtitle-2 font-weight-bold">Tinggi Badan:</VCol>
                        <VCol cols="8">{{ detail?.height ? detail.height + ' cm' : '-' }}</VCol>
                      </VRow>

                      <VRow class="mb-4">
                        <VCol cols="4" class="text-subtitle-2 font-weight-bold">Berat Badan:</VCol>
                        <VCol cols="8">{{ detail?.weight ? detail.weight + ' kg' : '-' }}</VCol>
                      </VRow>

                      <VRow class="mb-4">
                        <VCol cols="4" class="text-subtitle-2 font-weight-bold">Club:</VCol>
                        <VCol cols="8">{{ detail?.club_players?.[0]?.club?.name || '-' }}</VCol>
                      </VRow>
                      
                      <VRow class="mb-4">
                        <VCol cols="4" class="text-subtitle-2 font-weight-bold">Nomor Punggung:</VCol>
                        <VCol cols="8">{{ detail?.club_players?.[0]?.back_number || '-' }}</VCol>
                      </VRow>

                      <VRow class="mb-4">
                        <VCol cols="4" class="text-subtitle-2 font-weight-bold">Kategori:</VCol>
                        <VCol cols="8">{{ getCategoryLabel(detail?.club_players?.[0]?.category) }}</VCol>
                      </VRow>
                      
                      <VRow class="mb-4">
                        <VCol cols="4" class="text-subtitle-2 font-weight-bold">Posisi:</VCol>
                        <VCol cols="8">{{ getPositionLabel(detail?.club_players?.[0]?.position) }}</VCol>
                      </VRow>

                      <VRow class="mb-4">
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

                      <VRow class="mb-4">
                        <VCol cols="4" class="text-subtitle-2 font-weight-bold">Tanggal Dibuat:</VCol>
                        <VCol cols="8">{{ formatTanggalIndonesia(detail?.created_at) }}</VCol>
                      </VRow>
                    </VCol>
                  </VRow>
                </VWindowItem>

                <!-- Tab Files -->
                <VWindowItem value="files">
                  <VRow class="px-3">
                    <VCol class="text-no-wrap">
                      <div class="text-h6 mt-2">
                        <h6 class="text-h6 mb-4">Kartu Keluarga</h6>
                        <img
                          v-if="detail?.family_card?.url"
                          :src="getImageUrl(detail.family_card.url)"
                          alt="Family Card"
                          style="width: 30%; border-radius: 8px; margin-bottom: 1rem;"
                        />
                        <div v-else class="text-center text-grey mb-4">
                          <VIcon icon="tabler-file-x" size="50" />
                          <p>Tidak ada file</p>
                        </div>
                      </div>

                      <div class="text-h6 mt-4">
                        <h6 class="text-h6 mb-4">Rapor Terakhir</h6>
                        <img
                          v-if="detail?.report_grades?.url"
                          :src="getImageUrl(detail.report_grades.url)"
                          alt="Report Grades"
                          style="width: 30%; border-radius: 8px; margin-bottom: 1rem;"
                        />
                        <div v-else class="text-center text-grey mb-4">
                          <VIcon icon="tabler-file-x" size="50" />
                          <p>Tidak ada file</p>
                        </div>
                      </div>

                      <div class="text-h6 mt-4">
                        <h6 class="text-h6 mb-4">Akte Kelahiran</h6>
                        <img
                          v-if="detail?.birth_certificate?.url"
                          :src="getImageUrl(detail.birth_certificate.url)"
                          alt="Birth Certificate"
                          style="width: 30%; border-radius: 8px; margin-bottom: 1rem;"
                        />
                        <div v-else class="text-center text-grey mb-4">
                          <VIcon icon="tabler-file-x" size="50" />
                          <p>Tidak ada file</p>
                        </div>
                      </div>
                    </VCol>
                  </VRow>
                </VWindowItem>
              </VWindow>
            </VCardText>
          </VCard>
        </template>
      </VCol>
    </VRow>
  </VContainer>
</template>
