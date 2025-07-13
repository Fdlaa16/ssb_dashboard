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
const scheduleTranings = ref<any[]>([])

const selectedTraining = ref('upcoming')

const getScheduleTrainingQuery = async () => {
  loading.value = true
  error.value = null

  try {
    const response = await $api('company/list-trainings', {
      method: 'GET',
      params: {
        status: selectedTraining.value || 'upcoming',
      }
    })

    const trainings = response.data

    logisticData.value = trainings.map((item: any) => ({
      icon: 'tabler-calendar-event',
      color: 'primary',
      value: formatTrainingTime(item.schedule_date, item.schedule_start_at),
      change: 0,
      isHover: false,
      ...item
    }))

    scheduleTranings.value = trainings

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

const formatTrainingTime = (date: string, time: string) => {
  const d = new Date(`${date}T${time}`)
  return d.toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }) + ' • ' + time.slice(0, 5) + ' WIB'
}

onMounted(() => {
  if (!selectedTraining.value) selectedTraining.value = 'upcoming'
  
  getScheduleTrainingQuery()
})

watch(selectedTraining, () => {
  getScheduleTrainingQuery()
})

function formatIndoDate(dateStr: string): string {
  const date = new Date(dateStr)
  return new Intl.DateTimeFormat('id-ID', {
    weekday: 'long',
    day: '2-digit',
    month: 'long',
    year: 'numeric',
  }).format(date)
}
</script>

<template>
  <VContainer id="team">
    <div class="our-team pa-">
      <VRow class="align-center my-6">
        <VCol>
          <VChip label color="primary" size="small">Pelatihan terdekat</VChip>
          <h4 v-if="selectedTraining == 'upcoming'" class="text-h4 mt-2 mb-1">Jadwal Latihan Sepak Bola Mendatang</h4>
          <h4 v-else class="text-h4 mt-2 mb-1">Jadwal Latihan Sepak Bola Sebelumnya</h4>
          <p class="text-body-1 mb-0">Simak latihan sepak bola terdekat dan hasilnya!</p>
        </VCol>

        <VCol class="text-end" cols="12" sm="4" md="3">
          <AppSelect
            v-model="selectedTraining"
            placeholder="Status"
            clearable
            clear-icon="tabler-x"
            single-line
            class="w-100"
            :items="[
              { title: 'Pelatihan Mendatang', value: 'upcoming' },
              { title: 'Pelatihan Sebelumnya', value: 'previous' },
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
  </template>

  <!-- Tampilkan jika data kosong -->
  <template v-else>
    <VCol cols="12" class="text-center py-10">
      <VIcon size="64" color="grey-lighten-1">tabler-calendar-x</VIcon>
      <p class="text-body-1 mt-2 text-grey">
        Belum ada jadwal latihan ditemukan.
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

.taining-card {
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.08);
}

.taining-card ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.taining-card li {
  margin-bottom: 4px;
}
</style>

