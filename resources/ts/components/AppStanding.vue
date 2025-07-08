<script setup lang="ts">

const isFlatSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref<'success' | 'error'>('success')

interface Pricing {
  title?: string
  xs?: number | string
  sm?: number | string
  md?: string | number
  lg?: string | number
  xl?: string | number
}

const router = useRouter()

const logisticData = ref<any[]>([])

const loading = ref(true)
const error = ref<string | null>(null)
const clubs = ref<any[]>([])

const selectedMatch = ref('')

const headers = [
  { title: 'Nama Klub', key: 'club.name' },
  { title: 'Main', key: 'total' },
  { title: 'Menang', key: 'win' },
  { title: 'Seri', key: 'draw' },
  { title: 'Kalah', key: 'lose' },
  { title: 'GM', key: 'goal_in' },
  { title: 'GK', key: 'goal_conceded' },
  { title: 'Selisih', key: 'goal_difference' },
  { title: 'Poin', key: 'points' },
]


const getScheduleMatchQuery = async () => {
  loading.value = true
  error.value = null

  try {
    const response = await $api('company/standing', {
      method: 'GET',
      params: {
        status: selectedMatch.value || 'upcoming',
      }
    })
    
    clubs.value = response.data

    snackbarMessage.value = 'Data berhasil dimuat!'
    snackbarColor.value = 'success'
    isFlatSnackbarVisible.value = true

  } catch (err: any) {
    error.value = err.message || 'Gagal memuat data'

    snackbarMessage.value = error.value
    snackbarColor.value = 'error'
    isFlatSnackbarVisible.value = true
  } finally {
    loading.value = false
  }
}

const formatMatchTime = (date: string, time: string) => {
  const d = new Date(`${date}T${time}`)
  return d.toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }) + ' • ' + time.slice(0, 5) + ' WIB'
}

onMounted(() => {
  if (!selectedMatch.value) selectedMatch.value = 'upcoming'
  
  getScheduleMatchQuery()
})

function formatTanggalIndonesia(dateString: string): string {
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  }).format(date)
}

watch(selectedMatch, () => {
  getScheduleMatchQuery()
})

const currentPage = ref(1)
const itemsPerPage = 10

const paginatedClubs = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return clubs.value.slice(start, start + itemsPerPage)
})

const totalPages = computed(() =>
  Math.ceil(clubs.value.length / itemsPerPage)
)
</script>

<template>
  <VContainer id="club">
    <div class="our-team pa-">
      <VRow class="align-center my-6">
        <VCol>
          <VChip label color="primary" size="small">Klasemen Terbaru</VChip>
          <h4 class="text-h4 mt-2 mb-1">Klasemen dan Peringkat Liga Sepak Bola Saat Ini</h4>
          <p class="text-body-1 mb-0">
            Jelajahi klasemen sepak bola terkini dengan peringkat tim terkini, hasil pertandingan, dan poin. Lacak performa klub favorit Anda dan tetaplah unggul dengan pembaruan liga terkini, catatan menang/kalah, selisih gol, dan banyak lagi.
          </p>
        </VCol>
      </VRow>

      <VCard>
        <VDataTable
          :headers="headers"
          :items="paginatedClubs"
          :loading="loading"
          class="text-no-wrap mb-2"
          :items-per-page="itemsPerPage"
          hide-default-footer
        >
          <!-- Templating data -->
          <template #item["club.name"]="{ item }">
            <div>{{ item.club?.name || '-' }}</div>
          </template>

          <template #item.total="{ item }">
            <div>{{ item.total }}</div>
          </template>

          <template #item.win="{ item }">
            <div>{{ item.win }}</div>
          </template>

          <template #item.draw="{ item }">
            <div>{{ item.draw }}</div>
          </template>

          <template #item.lose="{ item }">
            <div>{{ item.lose }}</div>
          </template>

          <template #item.goal_in="{ item }">
            <div>{{ item.goal_in }}</div>
          </template>

          <template #item.goal_conceded="{ item }">
            <div>{{ item.goal_conceded }}</div>
          </template>

          <template #item.goal_difference="{ item }">
            <div>{{ item.goal_difference }}</div>
          </template>

          <template #item.points="{ item }">
            <strong>{{ item.points }}</strong>
          </template>

          <!-- Tampilkan pesan saat data kosong -->
          <template #no-data>
            <div class="text-center pa-4 text-medium-emphasis">
              {{ loading ? 'Memuat data...' : 'Tidak ada data yang tersedia' }}
            </div>
          </template>
        </VDataTable>
      </VCard>
    </div>
  </VContainer>

  <VSnackbar
    v-model="isFlatSnackbarVisible"
    :color="snackbarColor"
    location="bottom start"
    variant="flat"
    timeout="3000"
  >
    {{ snackbarMessage }}
  </VSnackbar>
</template>



<style lang="scss" scoped>
@use "@core-scss/base/mixins" as mixins;

.team-image {
  position: absolute;
  inset-block-start: -3.4rem;
  inset-inline: 0;
}

.headers {
  margin-block-end: 7.4375rem;
}

.our-team {
  margin-block: 5.25rem;
}

@media (max-width: 1264px) {
  .our-team {
    margin-block-end: 1rem;
  }
}

.team-card {
  border-radius: 90px 20px 6px 6px;
}

.section-title {
  font-size: 24px;
  font-weight: 800;
  line-height: 36px;
}

.section-title::after {
  position: absolute;
  background: url("../../../assets/images/front-pages/icons/section-title-icon.png") no-repeat left bottom;
  background-size: contain;
  block-size: 100%;
  content: "";
  font-weight: 800;
  inline-size: 120%;
  inset-block-end: 12%;
  inset-inline-start: -12%;
}

.logistics-card-statistics {
  border-block-end-style: solid;
  border-block-end-width: 2px;

  &:hover {
    border-block-end-width: 3px;
    margin-block-end: -1px;

    @include mixins.elevation(8);

    transition: all 0.1s ease-out;
  }
}

.skin--bordered {
  .logistics-card-statistics {
    border-block-end-width: 2px;

    &:hover {
      border-block-end-width: 3px;
      margin-block-end: -2px;
      transition: all 0.1s ease-out;
    }
  }
}

.match-card {
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.08);
}

.match-card ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.match-card li {
  margin-bottom: 4px;
}

.media-img {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.line-clamp {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2; // Tampilkan 2 baris
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 1.4rem; // Sesuaikan line-height
  max-height: 2.8rem;  // line-height * line-clamp
  margin: 0;
}
</style>

