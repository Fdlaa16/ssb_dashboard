<script setup lang="ts">
import Swal from 'sweetalert2'
import { computed, onMounted, ref } from 'vue'
import type { LocationQueryValue } from 'vue-router'
import { useRouter } from 'vue-router'

const router = useRouter()
const route = useRoute()

const searchQuery = ref('')
const selectedClub = ref('')
const selectedStadium = ref('')
const selectedStatus = ref('')
const selectedSort = ref('')

const standings = ref<any[]>([])
const clubs = ref<{ title: string; value: string | number }[]>([])
const stadiums = ref<{ title: string; value: string | number }[]>([])

const loading = ref(false)
const error = ref<string | null>(null)
const currentPage = ref(1)

const itemsPerPage = 5 
const isSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref<'success' | 'error'>('success')

onMounted(() => {
  if (route.query.success) {
    snackbarMessage.value = String(route.query.success)
    snackbarColor.value = 'success'
    isSnackbarVisible.value = true

    router.replace({ query: {} })
  }
})

const totalPages = computed(() => {
  return Math.ceil(standings.value.length / itemsPerPage)
})

const widgetData = ref([
  { title: 'All', value: 0, icon: 'tabler-calendar-check' },
  { title: 'Active', value: 0, icon: 'tabler-calendar-check' },
  { title: 'Non Active', value: 0, icon: 'tabler-calendar-check' },
])

const headers = [
  { title: 'ID', key: 'id' },
  { title: 'Club', key: 'club.name' },
  { title: 'Total', key: 'total' },
  { title: 'Win', key: 'win' },
  { title: 'Draw', key: 'draw' },
  { title: 'Lose', key: 'lose' },
  { title: 'Goal In', key: 'goal_in' },
  { title: 'Goal Conceded', key: 'goal_conceded' },
  { title: 'Goal Difference', key: 'goal_difference' },
  { title: 'Point', key: 'points' },
  { title: 'Action', key: 'action', sortable: false },
]

const statusColorMap = {
  1: { label: 'Active', color: 'success' },
  0: { label: 'Non Active', color: 'error' },
}

const paginatedStandings = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return standings.value.slice(start, start + itemsPerPage)
})

async function fetchStanding() {
  loading.value = true
  error.value = null

  try {
    const response = await $api('standing', {
      method: 'GET',
      params: {
        search: searchQuery.value,
        club_id: selectedClub.value,
        status: selectedStatus.value,
        sort: selectedSort.value,
      },
    })

    standings.value = response.data 
    const totals = response.totals

    widgetData.value = [
      { title: 'All', value: totals.all, icon: 'tabler-calendar-check', iconColor: 'primary', change: 0, desc: 'Total semua klasemen' },
      { title: 'Active', value: totals.active, icon: 'tabler-calendar-check', iconColor: 'success', change: 0, desc: 'Klasemen aktif' },
      { title: 'Non Active', value: totals.in_active, icon: 'tabler-calendar-check', iconColor: 'error', change: 0, desc: 'Klasemen tidak aktif' },
    ]

  } catch (err: any) {
    error.value = err.message || 'Gagal memuat data'
  } finally {
    loading.value = false
  }
}

async function fetchClubs() {
  try {
    const response = await $api('club', {
      method: 'GET',
    })

    const clubData = response.data.map((club: any) => ({
      title: club.name,
      value: club.id,
    }))

    clubs.value = [{ title: 'Pilih Club', value: '' }, ...clubData]
  } catch (error) {
    console.error('Gagal memuat clubs', error)
  }
}

function editStanding(standing: any) {
  router.push({ name: 'dashboards-standing-edit-id', params: { id: standing.id } })
}

async function deleteStanding(standing: any) {
  const confirm = await Swal.fire({
    title: 'Apakah kamu yakin?',
    text: `Data klasemen club ${standing.club.name} akan dihapus.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya, hapus!',
    cancelButtonText: 'Batal',
    customClass: {
      confirmButton: 'swal2-confirm-btn',
      cancelButton: 'swal2-cancel-btn',
    },
  })

  if (confirm.isConfirmed) {
    try {
      loading.value = true

      await $api(`standing/${standing.id}`, {
        method: 'DELETE',
      })

      await fetchStanding()

      snackbarMessage.value = 'Klasemen berhasil dihapus'
      snackbarColor.value = 'success'
      isSnackbarVisible.value = true
    } catch (err: any) {
      snackbarMessage.value = err?.response?.data?.message || 'Gagal menghapus klasemen'
      snackbarColor.value = 'error'
      isSnackbarVisible.value = true
    } finally {
      loading.value = false
    }
  }
}

async function activateStanding(standing: any) {
  const confirm = await Swal.fire({
    title: 'Aktifkan Klasemen?',
    text: `Data klasemen club ${standing.club.name} akan diaktifkan kembali.`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Ya, aktifkan!',
    cancelButtonText: 'Batal',
    customClass: {
      confirmButton: 'swal2-confirm-btn',
      cancelButton: 'swal2-cancel-btn',
    },
  })

  if (confirm.isConfirmed) {
    try {
      loading.value = true

      await $api(`standing/${standing.id}/active`, {
        method: 'PUT',
      })

      await fetchStanding()

      snackbarMessage.value = 'Klasemen berhasil diaktifkan kembali'
      snackbarColor.value = 'success'
      isSnackbarVisible.value = true
    } catch (err: any) {
      snackbarMessage.value = err?.response?.data?.message || 'Gagal mengaktifkan klasemen'
      snackbarColor.value = 'error'
      isSnackbarVisible.value = true
    } finally {
      loading.value = false
    }
  }
}

function getQueryParam(param: LocationQueryValue | LocationQueryValue[] | undefined): string {
  return Array.isArray(param) ? param[0] || '' : param || ''
}

onMounted(() => {
  searchQuery.value = getQueryParam(route.query.search)
  selectedClub.value = getQueryParam(route.query.club_id)
  selectedStatus.value = getQueryParam(route.query.status)
  selectedSort.value = getQueryParam(route.query.sort)

  fetchStanding()
  fetchClubs()
})

watch([searchQuery, selectedClub, selectedStadium, selectedStatus, selectedSort], () => {
  router.replace({
    query: {
      ...route.query,
      search: searchQuery.value || undefined,
      club_id: selectedClub.value || undefined,
      stadium_id: selectedStadium.value || undefined,
      status: selectedStatus.value || undefined,
      sort: selectedSort.value || undefined,
    },
  })

  fetchStanding()
})
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex mb-6">
        <VRow>
          <template
            v-for="(data, id) in widgetData"
            :key="id"
          >
            <VCol
              cols="12"
              md="4"
              sm="6"
            >
              <VCard>
                <VCardText>
                  <div class="d-flex justify-space-between">
                    <div class="d-flex flex-column gap-y-1">
                      <div class="text-body-1 text-high-emphasis">
                        {{ data.title }}
                      </div>
                      <div class="d-flex gap-x-2 align-center">
                        <h4 class="text-h4">
                          {{ data.value }}
                        </h4>
                      </div>
                      <div class="text-sm">
                        {{ data.desc }}
                      </div>
                    </div>
                    <VAvatar
                      :color="data.iconColor"
                      variant="tonal"
                      rounded
                      size="42"
                    >
                      <VIcon
                        :icon="data.icon"
                        size="26"
                      />
                    </VAvatar>
                  </div>
                </VCardText>
              </VCard>
            </VCol>
          </template>
        </VRow>
      </div>

      <VCard class="mb-6">
        <VCardItem class="pb-4">
          <VCardTitle>Standings</VCardTitle>
        </VCardItem>

        <VCardText>
          <VRow>
            <VCol
              cols="12"
              sm="6"
            >
              <AppSelect
                v-model="selectedClub"
                :items="clubs"
                placeholder="Club"
                clearable
                clear-icon="tabler-x"
                single-line
              />
            </VCol>

            <VCol
              cols="12"
              sm="6"
            >
              <AppSelect
                v-model="selectedStatus"
                placeholder="Status"
                clearable
                clear-icon="tabler-x"
                single-line
                :items="[
                  { title: 'Pilih Status', value: '' },
                  { title: 'Semua', value: 'all' },
                  { title: 'Aktif', value: 'active' },
                  { title: 'Tidak Aktif', value: 'in_active' }
                ]"
              />
            </VCol>
          </VRow>
        </VCardText>

        <VDivider />

        <VCardText class="d-flex flex-wrap gap-4">
          <div style="inline-size: 15.625rem;">
            <AppTextField
                v-model="searchQuery"
                placeholder="Search Standing"
            />
          </div>
          <VSpacer />

          <div class="app-user-search-filter d-flex align-center flex-wrap gap-4">
            <VBtn
              variant="tonal"
              color="secondary"
              prepend-icon="tabler-upload"
            >
              Export
            </VBtn>
          </div>
        </VCardText>

        <VDivider />

        <VDataTable
          :headers="headers"
          :items="paginatedStandings"
          :loading="loading"
          class="text-no-wrap"
          :items-per-page="itemsPerPage"
          hide-default-footer
        >
          <template #item.id="{ item }">
            <div class="text-body-1">{{ item.id }}</div>
          </template>

          <template #item.first_club.name="{ item }">
            <div class="text-body-1">{{ item.club.name }}</div>
          </template>

          <template #item.total="{ item }">
            <div class="text-body-1">{{ item.total }}</div>
          </template>

          <template #item.win="{ item }">
            <div class="text-body-1">{{ item.win }}</div>
          </template>

          <template #item.draw="{ item }">
            <div class="text-body-1">{{ item.draw }}</div>
          </template>

          <template #item.lose="{ item }">
            <div class="text-body-1">{{ item.lose }}</div>
          </template>

          <template #item.points="{ item }">
            <div class="text-body-1">{{ item.points }}</div>
          </template>

          <template #item.action="{ item }">
            <div class="d-flex gap-x-2">
              <VBtn  
                v-if="!item.deleted_at"
                icon
                size="small"
                color="error"
                @click="deleteStanding(item)"
                title="Delete"
              >
                <VIcon icon="tabler-trash" />
              </VBtn>
              
              <VBtn
                v-if="item.deleted_at"
                icon
                size="small"
                color="success"
                @click="activateStanding(item)"
                title="Activate"
              >
                <VIcon icon="tabler-check" />
              </VBtn>
            </div>
          </template>
        </VDataTable>

        <!-- Pagination -->
        <div class="d-flex justify-end mt-4 mb-3 mr-2">
          <VPagination
            v-model="currentPage"
            :length="totalPages"
            total-visible="5"
            color="primary"
          />
        </div>
      </VCard>
    </VCol>
  </VRow>

  <VSnackbar
    v-model="isSnackbarVisible"
    :color="snackbarColor"
    location="bottom start"
    variant="flat"
    timeout="3000"
  >
    {{ snackbarMessage }}
  </VSnackbar>

</template>

<style lang="scss">
.swal2-confirm-btn {
  background-color: #7B68EE !important;
  color: #ffffff !important;
  border: none;
  padding: 0.625rem 1.25rem;
  border-radius: 0.375rem;
}

.swal2-cancel-btn {
  background-color: #6c757d !important;
  color: #ffffff !important;
  border: none;
  padding: 0.625rem 1.25rem;
  border-radius: 0.375rem;
}
</style>
