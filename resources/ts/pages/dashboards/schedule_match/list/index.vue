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

const scheduleMatchs = ref<any[]>([])
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
  return Math.ceil(scheduleMatchs.value.length / itemsPerPage)
})

const widgetData = ref([
  { title: 'All', value: 24, icon: 'tabler-calendar-check' },
  { title: 'Active', value: 24, icon: 'tabler-calendar-check' },
  { title: 'Non Active', value: 24, icon: 'tabler-calendar-check' },
])

const headers = [
  { title: 'ID', key: 'id' },
  { title: 'Club 1', key: 'first_club.name' },
  { title: 'Club 2', key: 'secound_club.name' },
  { title: 'Stadium', key: 'stadium.name' },
  { title: 'Schedule Date', key: 'schedule_date' },
  { title: 'Schedule Start At', key: 'schedule_start_at' },
  { title: 'Action', key: 'action', sortable: false },
]

const statusColorMap = {
  1: { label: 'Active', color: 'success' },
  0: { label: 'Non Active', color: 'error' },
}

const paginatedScheduleMatchs = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return scheduleMatchs.value.slice(start, start + itemsPerPage)
})

async function fetchScheduleMatch() {
  loading.value = true
  error.value = null

  try {
    const response = await $api('schedule-match', {
      method: 'GET',
      params: {
        search: searchQuery.value,
        club_id: selectedClub.value,
        stadium_id: selectedStadium.value,
        status: selectedStatus.value,
        sort: selectedSort.value,
      },
    })

    scheduleMatchs.value = response.data 
    const totals = response.totals

    widgetData.value = [
      { title: 'All', value: totals.all, icon: 'tabler-calendar-check', iconColor: 'primary', change: 0, desc: 'Total semua scheduleMatch' },
      { title: 'Active', value: totals.active, icon: 'tabler-calendar-check', iconColor: 'success', change: 0, desc: 'Schedule Match aktif' },
      { title: 'Non Active', value: totals.in_active, icon: 'tabler-calendar-check', iconColor: 'error', change: 0, desc: 'Schedule Match tidak aktif' },
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

async function fetchStadiums() {
  try {
    const response = await $api('stadium', {
      method: 'GET',
    })

    const stadiumData = response.data.map((stadium: any) => ({
      title: stadium.name,
      value: stadium.id,
    }))

    stadiums.value = [{ title: 'Pilih Stadium', value: '' }, ...stadiumData]
  } catch (error) {
    console.error('Gagal memuat stadiums', error)
  }
}

function editScheduleMatch(scheduleMatch: any) {
  router.push({ name: 'dashboards-schedule-match-edit-id', params: { id: scheduleMatch.id } })
}

async function deleteScheduleMatch(scheduleMatch: any) {
  const confirm = await Swal.fire({
    title: 'Apakah kamu yakin?',
    text: `Data Schedule Match ${scheduleMatch.first_club.name} melawan ${scheduleMatch.secound_club.name} pada tanggal ${scheduleMatch.schedule_date} pukul ${scheduleMatch.schedule_start_at} akan dihapus.`,
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

      await $api(`schedule-match/${scheduleMatch.id}`, {
        method: 'DELETE',
      })

      await fetchScheduleMatch()

      snackbarMessage.value = 'Schedule Match berhasil dihapus'
      snackbarColor.value = 'success'
      isSnackbarVisible.value = true
    } catch (err: any) {
      snackbarMessage.value = err?.response?.data?.message || 'Gagal menghapus schedule match'
      snackbarColor.value = 'error'
      isSnackbarVisible.value = true
    } finally {
      loading.value = false
    }
  }
}

async function activateScheduleMatch(scheduleMatch: any) {
  const confirm = await Swal.fire({
    title: 'Aktifkan Schedule Match?',
    text: `Schedule Match ${scheduleMatch.first_club.name} melawan ${scheduleMatch.secound_club.name} pada tanggal ${scheduleMatch.schedule_date} pukul ${scheduleMatch.schedule_start_at} akan diaktifkan kembali.`,
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

      await $api(`schedule-match/${scheduleMatch.id}/active`, {
        method: 'PUT',
      })

      await fetchScheduleMatch()

      snackbarMessage.value = 'Schedule Match berhasil diaktifkan kembali'
      snackbarColor.value = 'success'
      isSnackbarVisible.value = true
    } catch (err: any) {
      snackbarMessage.value = err?.response?.data?.message || 'Gagal mengaktifkan Schedule Match'
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
  selectedStadium.value = getQueryParam(route.query.stadium_id)
  selectedStatus.value = getQueryParam(route.query.status)
  selectedSort.value = getQueryParam(route.query.sort)

  fetchScheduleMatch()
  fetchClubs()
  fetchStadiums()
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

  fetchScheduleMatch()
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
          <VCardTitle>Schedule Matchs</VCardTitle>
        </VCardItem>

        <VCardText>
          <VRow>
            <VCol
              cols="12"
              sm="3"
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
              sm="3"
            >
              <AppSelect
                v-model="selectedStadium"
                placeholder="Stadium"
                clearable
                clear-icon="tabler-x"
                single-line
                :items="stadiums"
              />
            </VCol>

            <VCol
              cols="12"
              sm="3"
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

            <VCol
              cols="12"
              sm="3"
            >
              <AppSelect
                v-model="selectedSort"
                placeholder="Z-A"
                clearable
                clear-icon="tabler-x"
                single-line
                :items="[
                  { title: 'Pilih Sort', value: '' },
                  { title: 'A-Z', value: 'asc' },
                  { title: 'Z-A', value: 'desc' },
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
                placeholder="Search Schedule Match"
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

            <VBtn
              prepend-icon="tabler-plus"
              :to="{ name: 'dashboards-schedule-match-add' }"
            >
              Add New Schedule Match
            </VBtn>
          </div>
        </VCardText>

        <VDivider />

        <VDataTable
          :headers="headers"
          :items="paginatedScheduleMatchs"
          :loading="loading"
          class="text-no-wrap"
          :items-per-page="itemsPerPage"
          hide-default-footer
        >
          <template #item.id="{ item }">
            <div class="text-body-1">{{ item.id }}</div>
          </template>

          <template #item.first_club.name="{ item }">
            <div class="text-body-1">{{ item.first_club.name }}</div>
          </template>

          <template #item.secound_club.name="{ item }">
            <div class="text-body-1">{{ item.secound_club.name }}</div>
          </template>

          <template #item.stadium.name="{ item }">
            <div class="text-body-1">{{ item.stadium.name }}</div>
          </template>

          <template #item.schedule_date="{ item }">
            <div class="text-body-1">{{ item.schedule_date }}</div>
          </template>

          <template #item.schedule_start_at="{ item }">
            <div class="text-body-1">{{ item.schedule_start_at }}</div>
          </template>

          <template #item.action="{ item }">
            <div class="d-flex gap-x-2">
              <VBtn
                v-if="!item.deleted_at"
                icon
                size="small"
                color="primary"
                @click="editScheduleMatch(item)"
                title="Edit"
              >
                <VIcon icon="tabler-pencil" />
              </VBtn>

              <VBtn  
                v-if="!item.deleted_at"
                icon
                size="small"
                color="error"
                @click="deleteScheduleMatch(item)"
                title="Delete"
              >
                <VIcon icon="tabler-trash" />
              </VBtn>
              
              <VBtn
                v-if="item.deleted_at"
                icon
                size="small"
                color="success"
                @click="activateScheduleMatch(item)"
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
