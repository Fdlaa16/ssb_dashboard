<script setup lang="ts">

// const isFlatSnackbarVisible = ref(false)
// const snackbarMessage = ref('')
// const snackbarColor = ref<'success' | 'error'>('success')

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
    const response = await $api('company/media', {
      method: 'GET',
      params: {
        status: selectedMatch.value || 'upcoming',
      }
    })

    scheduleMatchs.value = response.data

    // snackbarMessage.value = 'Data berhasil dimuat!'
    // snackbarColor.value = 'success'
    // isFlatSnackbarVisible.value = true

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
</script>

<template>
  <VContainer id="team">
    <div class="our-team pa-">
      <VRow class="align-center my-6">
        <VCol>
          <VChip label color="primary" size="small">Latest Football Media</VChip>
          <h4  class="text-h4 mt-2 mb-1">Latest Football Highlights & Reports</h4>
          <p class="text-body-1 mb-0">Stay informed with the most recent football news, match highlights, exclusive interviews, and expert analysis from around the field. </p>
        </VCol>
      </VRow>

      <VRow>
        <template v-if="scheduleMatchs.length > 0">
          <VCol
            v-for="(data, index) in scheduleMatchs"
            :key="index"
            cols="12"
            sm="6"
            md="4"
          >
            <router-link :to="{ name: 'front-pages-media-id', params: { id: String(data.id) } }">
              <VCard class="cursor-pointer" hover>
                <VImg
                  :src="data.document_media.url"
                  cover
                  class="media-img"
                />

                <VCardItem>
                  <VCardTitle>{{ data.title }}</VCardTitle>
                </VCardItem>

                <VCardText>
                  <p class="line-clamp">
                    {{ data.description }}
                  </p>
                  <span class="text-caption text-disabled">
                    {{ formatTanggalIndonesia(data.start_date) }}
                  </span>
                </VCardText>
              </VCard>
            </router-link>
          </VCol>
        </template>

        <!-- Tampilkan jika data kosong -->
        <template v-else>
          <VCol cols="12" class="text-center py-10">
            <VIcon size="64" color="grey-lighten-1">tabler-calendar-x</VIcon>
            <p class="text-body-1 mt-2 text-grey">
              Belum ada media berita ditemukan.
            </p>
          </VCol>
        </template>
      </VRow>
    </div>    
  </VContainer>
  
  <!-- <VSnackbar
    v-model="isFlatSnackbarVisible"
    :color="snackbarColor"
    location="bottom start"
    variant="flat"
    timeout="3000"
  >
    {{ snackbarMessage }}
  </VSnackbar> -->
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

