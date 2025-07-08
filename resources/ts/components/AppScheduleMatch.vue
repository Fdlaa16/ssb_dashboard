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
const scheduleMatchs = ref<any[]>([])

const selectedMatch = ref('upcoming')

const getScheduleMatchQuery = async () => {
  loading.value = true
  error.value = null

  try {
    const response = await $api('company/list-matches', {
      method: 'GET',
      params: {
        status: selectedMatch.value || 'upcoming',
      }
    })

    const matches = response.data

    logisticData.value = matches.map((item: any) => ({
      icon: 'tabler-calendar-event',
      color: 'primary',
      title: `${item.first_club.name} vs ${item.secound_club.name}`,
      value: formatMatchTime(item.schedule_date, item.schedule_start_at),
      change: 0,
      isHover: false,
      ...item
    }))

    scheduleMatchs.value = matches

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

watch(selectedMatch, () => {
  getScheduleMatchQuery()
})
</script>

<template>
  <VContainer id="team">
    <div class="our-team pa-">
      <VRow class="align-center my-6">
        <VCol>
          <VChip label color="primary" size="small">Pertandingan Terdekat</VChip>
          <h4 v-if="selectedMatch == 'upcoming'" class="text-h4 mt-2 mb-1">Jadwal Sepak Bola Mendatang</h4>
          <h4 v-else class="text-h4 mt-2 mb-1">Jadwal Sepak Bola Sebelumnya</h4>
          <p class="text-body-1 mb-0">Simak pertandingan sepak bola terdekat dan hasilnya!</p>
        </VCol>

        <VCol class="text-end" cols="12" sm="4" md="3">
          <AppSelect
            v-model="selectedMatch"
            placeholder="Status"
            clearable
            clear-icon="tabler-x"
            single-line
            class="w-100"
            :items="[
              { title: 'Pertandingan Mendatang', value: 'upcoming' },
              { title: 'Pertandingan Sebelumnya', value: 'previous' },
            ]"
          />
        </VCol>
      </VRow>

      <VRow>
        <!-- Tampilkan jika ada data -->
        <template v-if="logisticData.length > 0">
          <VCol
            v-for="(data, index) in logisticData"
            :key="index"
            cols="12"
            md="12"
            sm="6"
          >
            <!-- CARD PERTANDINGAN -->
            <VCard
              class="logistics-card-statistics cursor-pointer"
              :style="data.isHover ? `border-block-end-color: rgb(var(--v-theme-${data.color}))` : `border-block-end-color: rgba(var(--v-theme-${data.color}),0.38)`"
              @mouseenter="data.isHover = true"
              @mouseleave="data.isHover = false"
            >
              <VCardText>
                <VRow class="align-center justify-space-between">
                  <VCol class="text-center" cols="4">
                    <VAvatar size="80" variant="flat" rounded="lg" class="mb-2">
                      <img
                        :src="data.first_club.profile_club.url"
                        alt="Club A"
                        style="width: 100%; height: 100%; object-fit: contain"
                      />
                    </VAvatar>
                    <h5 class="text-h6 font-weight-bold">
                      {{ data.first_club.name }}
                    </h5>
                  </VCol>

                  <VCol class="text-center" cols="4">
                    <div class="text-h4 font-weight-bold">
                      {{ data.first_club_score ?? '0' }} : {{ data.secound_club_score ?? '0' }}
                    </div>
                    <VChip
                      color="grey-lighten-2"
                      size="small"
                      class="mt-1"
                      v-if="data.first_club_score !== null && data.secound_club_score !== null"
                    >
                      FT
                    </VChip>
                  </VCol>

                  <VCol class="text-center" cols="4">
                    <VAvatar size="80" variant="flat" rounded="lg" class="mb-2">
                      <img
                        :src="data.secound_club.profile_club.url"
                        alt="Club B"
                        style="width: 100%; height: 100%; object-fit: contain"
                      />
                    </VAvatar>
                    <h5 class="text-h6 font-weight-bold">
                      {{ data.secound_club.name }}
                    </h5>
                  </VCol>
                </VRow>

                <div class="text-center text-caption mt-2 text-grey">
                  📍 {{ data.stadium.name }} • 🗓️ {{ data.schedule_date }} • ⏰ {{ data.schedule_start_at }}
                </div>
              </VCardText>
            </VCard>
          </VCol>
        </template>

        <!-- Tampilkan jika data kosong -->
        <template v-else>
          <VCol cols="12" class="text-center py-10">
            <VIcon size="64" color="grey-lighten-1">tabler-calendar-x</VIcon>
            <p class="text-body-1 mt-2 text-grey">
              Belum ada jadwal pertandingan ditemukan.
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
</style>

