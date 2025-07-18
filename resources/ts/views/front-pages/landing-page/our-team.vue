<script setup lang="ts">


const router = useRouter()

const logisticDataMatch = ref<any[]>([])
const logisticDataTraining = ref<any[]>([])

const loading = ref(true)
const error = ref<string | null>(null)
const scheduleMatchs = ref<any[]>([])
const scheduleTrainings = ref<any[]>([])

const searchQuery = ref('')
const selectedClub = ref('')
const selectedStadium = ref('')
const selectedStatus = ref('')
const selectedSort = ref('')

const getScheduleMatchQuery = async () => {
  loading.value = true
  error.value = null

  try {
    const response = await $api('company/nearest-matches', {
      method: 'GET',
      params: {
        search: searchQuery.value,
        club_id: selectedClub.value,
        stadium_id: selectedStadium.value,
        status: selectedStatus.value,
        sort: selectedSort.value,
      },
    })

    const matches = response.data

    logisticDataMatch.value = matches.map((item: any) => ({
      icon: 'tabler-calendar-event',
      color: 'primary',
      title: `${item.first_club.name} vs ${item.secound_club.name}`,
      value: formatMatchTime(item.schedule_date, item.schedule_start_at),
      change: 0,
      isHover: false,

      ...item
    }))
    
    scheduleMatchs.value = matches

  } catch (err: any) {
    error.value = err.message || 'Gagal memuat data'
  } finally {
    loading.value = false
  }
}

const getScheduleTrainingQuery = async () => {
  loading.value = true
  error.value = null

  try {
    const response = await $api('company/nearest-trainings', {
      method: 'GET',
      params: {
        search: searchQuery.value,
        club_id: selectedClub.value,
        stadium_id: selectedStadium.value,
        status: selectedStatus.value,
        sort: selectedSort.value,
      },
    })

    const trainings = response.data

    logisticDataTraining.value = trainings.map((item: any) => ({
      icon: 'tabler-calendar-event',
      color: 'primary',
      // title: `${item.first_club.name} vs ${item.secound_club.name}`,
      value: formatMatchTime(item.schedule_date, item.schedule_start_at),
      change: 0,
      isHover: false,

      ...item
    }))
    
    scheduleTrainings.value = trainings

  } catch (err: any) {
    error.value = err.message || 'Gagal memuat data'
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

function formatIndoDate(dateStr: string): string {
  const date = new Date(dateStr)
  return new Intl.DateTimeFormat('id-ID', {
    weekday: 'long',
    day: '2-digit',
    month: 'long',
    year: 'numeric',
  }).format(date)
}

const goToAllMatches = () => {
  router.push({ name: 'front-pages-schedule-match' }) 
}

const goToAllTrainings = () => {
  router.push({ name: 'front-pages-schedule-training' }) 
}

onMounted(() => {
  getScheduleMatchQuery()
  getScheduleTrainingQuery()
})
</script>

<template>
  <VContainer id="schedule">
    <div class="our-team pa-">
      <div class="d-flex justify-space-between align-center my-6">
        <div>
          <VChip label color="primary" size="small">
            Pertandingan Terdekat
          </VChip>
          <h4 class="d-flex align-center text-h4 mt-2 mb-1 flex-wrap">
            Jadwal Sepak Bola Mendatang
          </h4>
          <p class="text-body-1 mb-0">
            Simak pertandingan sepak bola terdekat dan hasilnya!
          </p>
        </div>

        <VBtn color="primary" @click="goToAllMatches">
          Tampilkan Lebih Banyak
        </VBtn>
      </div>

      <VRow>
        <VCol
          v-for="(data, index) in logisticDataMatch"
          :key="index"
          cols="12"
          md="12"
          sm="12"
        >
          <div>
            <VCard
              class="logistics-card-statistics cursor-pointer"
              :style="data.isHover ? `border-block-end-color: rgb(var(--v-theme-${data.color}))` : `border-block-end-color: rgba(var(--v-theme-${data.color}),0.38)`"
              @mouseenter="data.isHover = true"
              @mouseleave="data.isHover = false"
            >
              <VCardText>
                <VRow class="align-center justify-space-between">
                  <!-- Team 1 -->
                  <VCol class="text-center" cols="4">
                    <VAvatar size="80" variant="flat" rounded="lg" class="mb-2">
                      <img
                        :src="data.first_club.profile_club?.url"
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
                        :src="data.secound_club.profile_club?.url"
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
                  📍 {{ data.stadium.name }} • 🗓️ {{ formatIndoDate(data.schedule_date) }} • ⏰ {{ data.schedule_start_at }}
                </div>
              </VCardText>
            </VCard>
          </div>
        </VCol>
      </VRow>
    </div>

    <div class="our-team pa-">
      <div class="d-flex justify-space-between align-center my-6">
        <div>
          <VChip label color="primary" size="small">
            Pelatihan Terdekat
          </VChip>
          <h4 class="d-flex align-center text-h4 mt-2 mb-1 flex-wrap">
            Jadwal Sepak Bola Mendatang
          </h4>
          <p class="text-body-1 mb-0">
            Simak latihan sepak bola terdekat dan hasilnya!
          </p>
        </div>

        <VBtn color="primary" @click="goToAllTrainings">
          Tampilkan Lebih Banyak
        </VBtn>
      </div>

      <VRow>
        <VCol
          v-for="(data, index) in logisticDataTraining"
          :key="index"
          cols="12"
          md="12"
          sm="12"
        >
          <!-- CARD latihan -->
          <VCard
            class="logistics-card-statistics cursor-pointer"
            :style="data.isHover ? `border-block-end-color: rgb(var(--v-theme-${data.color}))` : `border-block-end-color: rgba(var(--v-theme-${data.color}),0.38)`"
            @mouseenter="data.isHover = true"
            @mouseleave="data.isHover = false"
          >
            <VCardText>
              <div class="text-center text-caption mb-8 text-grey"> 
                
              </div>
              <VRow class="align-center justify-space-between">

                <VCol class="text-center" cols="4">
                  🗓️ {{ formatIndoDate(data.schedule_date) }}
                </VCol>

                <VCol class="text-center" cols="4">
                  <img
                    src="/storage/logo/LOGOSSB.png"
                    alt="Logo SSB"
                    style="height: 90px;"
                    class="me-2"
                  />
                
                </VCol>

                <VCol class="text-center" cols="4">
                  ⏰ {{ data.schedule_start_at }}
                </VCol>
              </VRow>
              <div class="text-center mt-4">
                  📍 {{ data.stadium.name }} - {{ data.stadium.area }}
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
    </div>
  </VContainer>
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
