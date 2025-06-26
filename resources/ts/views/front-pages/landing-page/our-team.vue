<script setup lang="ts">
import teamPerson1 from '@images/front-pages/landing-page/team-member-1.png'
import teamPerson2 from '@images/front-pages/landing-page/team-member-2.png'
import teamPerson3 from '@images/front-pages/landing-page/team-member-3.png'
import teamPerson4 from '@images/front-pages/landing-page/team-member-4.png'

const logisticData = ref([
  { icon: 'tabler-truck', color: 'primary', title: 'On route vehicles', value: 42, change: 18.2, isHover: false },
])

const loading = ref(true)
const error = ref<string | null>(null)
const medias = ref<any[]>([])

const searchQuery = ref('')
const selectedClub = ref('')
const selectedStadium = ref('')
const selectedStatus = ref('')
const selectedSort = ref('')

const getScheduleMatchQuery = async () => {
  loading.value = true
  error.value = null

  try {
    const response = await $api('company/schedule-match', {
      method: 'GET',
      params: {
        search: searchQuery.value,
        club_id: selectedClub.value,
        stadium_id: selectedStadium.value,
        status: selectedStatus.value,
        sort: selectedSort.value,
      },
    })

    console.log('Media response:', response);
    
    medias.value = response.data 
    const totals = response.totals

  } catch (err: any) {
    error.value = err.message || 'Gagal memuat data'
  } finally {
    loading.value = false
  }
}


onMounted(() => {
  getScheduleMatchQuery()
})
</script>

<template>
  <VContainer id="team">
    <div class="our-team pa-">
      <div class="headers d-flex justify-center flex-column align-center">
        <VChip
          label
          color="primary"
          class="mb-4"
          size="small"
        >
          Our Great Team
        </VChip>

        <h4 class="d-flex align-center text-h4 mb-1 flex-wrap justify-center">
          <div class="position-relative me-2">
            <div class="section-title">
              Supported
            </div>
          </div>
          by Real People
        </h4>

        <p class="text-center text-body-1 mb-0">
          Who is behind these great-looking interfaces?
        </p>
      </div>

      <VRow>
        <VCol
          v-for="(data, index) in logisticData"
          :key="index"
          cols="12"
          md="12"
          sm="6"
        >
          <div>
            <VCard
              class="logistics-card-statistics cursor-pointer"
              :style="data.isHover ? `border-block-end-color: rgb(var(--v-theme-${data.color}))` : `border-block-end-color: rgba(var(--v-theme-${data.color}),0.38)`"
              @mouseenter="data.isHover = true"
              @mouseleave="data.isHover = false"
            >
              <VCardText>
                <div class="d-flex align-center gap-x-4 mb-1">
                  <VAvatar
                    variant="tonal"
                    :color="data.color"
                    rounded
                  >
                    <VIcon
                      :icon="data.icon"
                      size="28"
                    />
                  </VAvatar>
                  <h4 class="text-h4">
                    {{ data.value }}
                  </h4>
                </div>
                <div class="text-body-1 mb-1">
                  {{ data.title }}
                </div>
                <div class="d-flex gap-x-2 align-center">
                  <h6 class="text-h6">
                    {{ (data.change > 0) ? '+' : '' }} {{ data.change }}%
                  </h6>
                  <div class="text-disabled">
                    than last week
                  </div>
                </div>
              </VCardText>
            </VCard>
          </div>
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
</style>
