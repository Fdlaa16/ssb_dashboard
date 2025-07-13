<script setup lang="ts">
import { computed, ref } from 'vue'

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
const scheduleMatchs = ref<any[]>([])

const selectedMatch = ref('')

const getScheduleMatchQuery = async () => {
  loading.value = true
  error.value = null

  try {
    const response = await $api('company/player', {
      method: 'GET',
      params: {
        position: selectedPosition.value.value || '',
        category: category.value.value || '', 
      },
    })
    
    scheduleMatchs.value = response.data
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

const groupedByPosition = computed(() => {
  const groups: Record<string, any[]> = {}

  for (const player of scheduleMatchs.value) {
    const position = player.position ?? 'unknown'
    if (!groups[position]) {
      groups[position] = []
    }
    groups[position].push(player)
  }

  return groups
})

const formatMatchTime = (date: string, time: string) => {
  const d = new Date(`${date}T${time}`)
  return d.toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }) + ' • ' + time.slice(0, 5) + ' WIB'
}

const categories = [
  { title: 'Semua Kategori', value: '' },
  { title: 'Tim Utama', value: 'main' },
  { title: 'U-9', value: 'u9' },
  { title: 'U-10', value: 'u10' },
  { title: 'U-11', value: 'u11' },
  { title: 'U-12', value: 'u12' },
  { title: 'U-13', value: 'u13' },
  { title: 'U-14', value: 'u14' },
  { title: 'U-15', value: 'u15' },
]
const category = ref(categories[0])

const positions = [
  { title: 'Semua Posisi', value: '' },
  { title: 'Penjaga Gawang', value: 'goalkeeper' },
  { title: 'Bek', value: 'defender' },
  { title: 'Gelandang', value: 'midfielder' },
  { title: 'Penyerang', value: 'forward' },
];
const selectedPosition = ref(positions[0])

onMounted(() => {
  if (!selectedMatch.value) selectedMatch.value = ''
  
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

const getPositionTitle = (value: string) => {
  const match = positions.find(pos => pos.value === value)
  return match ? match.title : value
}

watch([category, selectedPosition], () => {
  getScheduleMatchQuery()
})
</script>

<template>
  <VContainer id="team">
    <div class="our-team pa-">
      <VRow class="align-center my-6">
        <VCol>
          <VChip label color="primary" size="small">Player</VChip>

          <h4 class="text-h4 mt-2 mb-1">
            {{
              category?.value
                ? `Pemain di Kategori ${category.title}`
                  : 'Semua Kategori Pemain'
            }}
          </h4>

          <p class="text-body-1 mb-0">
            {{
              category?.value
                  ? `Lihat daftar pemain dalam kategori ${category.title}.`
                  : 'Jelajahi pemain dari semua kategori umur.'
            }}
          </p>
        </VCol>

        <VCol class="text-end" cols="12" sm="4" md="3">
          <AppSelect
            v-model="category"
            :items="categories"
            item-title="title"
            item-value="value"
            return-object
            placeholder="Pilih Kategori"
            clearable
            clear-icon="tabler-x"
            single-line
            class="w-100"
            @update:modelValue="val => category = val ?? categories[0]"
          />
        </VCol>
      </VRow>

      <VRow>
        <template v-if="Object.keys(groupedByPosition).length > 0">
          <VCol v-for="(players, positionKey) in groupedByPosition" :key="positionKey">
            <VCol cols="12">
              <h5 class="text-h5 font-weight-bold mb-3 mt-6">
                {{ getPositionTitle(positionKey) }}
              </h5>
            </VCol>

            <VCol
              v-for="(data, index) in players"
              :key="`${positionKey}-${index}`"
              cols="12"
              sm="6"
              md="4"
            >
              <router-link :to="{ name: 'front-pages-player-id', params: { id: String(data.id) } }">
                <VCard>
                  <VImg
                    :src="data?.avatar?.url"
                    cover
                    class="media-img"
                  />

                  <VCardItem class="py-1"> 
                    <div class="d-flex align-center justify-space-between">
                      <VCardTitle class="text-subtitle-1 font-weight-bold pa-0">
                        {{ data.name }}
                      </VCardTitle>
                      <div class="d-flex align-center">
                        <VIcon icon="tabler-shirt" size="18" class="mr-1" />
                        <span class="text-subtitle-1">{{ data.back_number }}</span>
                      </div>
                    </div>
                  </VCardItem>

                  <VCardText class="pt-1 pb-2"> 
                    <div class="d-flex justify-space-between text-caption text-grey-darken-1">
                      <span>{{ getPositionTitle(data.position) }}</span>
                    </div>
                  </VCardText>
                </VCard>
              </router-link>  
            </VCol>
          </VCol>
        </template>

        <template v-else>
          <VCol cols="12" class="text-center py-10">
            <VIcon size="64" color="grey-lighten-1">tabler-calendar-x</VIcon>
            <p class="text-body-1 mt-2 text-grey">
              Belum ada pemain ditemukan.
            </p>
          </VCol>
        </template>
      </VRow>

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

