<script setup lang="ts">
import Swal from 'sweetalert2'
import { computed, onMounted, ref } from 'vue'
import type { LocationQueryValue } from 'vue-router'
import { useRouter } from 'vue-router'

const router = useRouter()
const route = useRoute()

const searchQuery = ref('')
// const selectedClub = ref('')
const selectedStatus = ref('')
const selectedSort = ref('')

const players = ref<any[]>([])
const clubs = ref<{ title: string; value: string | number }[]>([])
const sports = ref<{ title: string; value: string | number }[]>([])

const loading = ref(false)
const error = ref<string | null>(null)
const currentPage = ref(1)

const itemsPerPage = 5 
const isSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref<'success' | 'error'>('success')
const exportLoading = ref(false)

onMounted(() => {
  if (route.query.success) {
    snackbarMessage.value = String(route.query.success)
    snackbarColor.value = 'success'
    isSnackbarVisible.value = true

    router.replace({ query: {} })
  }
})

const totalPages = computed(() => {
  return Math.ceil(players.value.length / itemsPerPage)
})

const widgetData = ref([
  { title: 'All', value: 0, icon: 'tabler-user' },
  { title: 'Active', value: 0, icon: 'tabler-user' },
  { title: 'In Confirm', value: 0, icon: 'tabler-user' },
  { title: 'Non Active', value: 0, icon: 'tabler-user' },
])

const headers = [
  { title: 'ID', key: 'id' },
  { title: 'Nama', key: 'name' },
  { title: 'NISN', key: 'nisn' },
  { title: 'Tinggi Badan (cm)', key: 'height' },
  { title: 'Berat Badan (kg)', key: 'weight' },
  { title: 'Nomor Punggung', key: 'back_number' },
  { title: 'Status', key: 'status' },
  { title: 'Aksi', key: 'action', sortable: false },
]

const statusColor = (item) => {
  switch (item.status) {
    case 0:
      return { label: 'Menunggu Konfirmasi', color: 'warning', textColor: 'black' }
    case 1:
      return { label: 'Permain Aktif', color: 'success' }
    case 2:
      return { label: 'Perlu Perbaikan', color: 'error' }
    default:
      return { label: 'Unknown', color: 'grey' }
  }
}


const paginatedPlayers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return players.value.slice(start, start + itemsPerPage)
})

async function fetchPlayer() {
  loading.value = true
  error.value = null

  try {
    const response = await $api('player', {
      method: 'GET',
      params: {
        search: searchQuery.value,
        // club_id: selectedClub.value,
        status: selectedStatus.value,
        sort: selectedSort.value,
      },
    })

    players.value = response.data         
    const totals = response.totals

    widgetData.value = [
      { title: 'All', value: totals.all, icon: 'tabler-user', iconColor: 'primary', change: 0, desc: 'Total semua pemain' },
      { title: 'Active', value: totals.active, icon: 'tabler-user-check', iconColor: 'success', change: 0, desc: 'Pemain aktif' },
      { title: 'In Confirm', value: totals.in_confirm, icon: 'tabler-user-question', iconColor: 'warning', change: 0, desc: 'Menunggu konfirmasi' },
      { title: 'Non Active', value: totals.in_active, icon: 'tabler-user-x', iconColor: 'error', change: 0, desc: 'Player tidak aktif' },
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

async function fetchSports() {
  try {
    const response = await $api('sport', {
      method: 'GET',
    })

    const sportData = response.data.map((sport: any) => ({
      title: sport.name,
      value: sport.id,
    }))

    sports.value = [{ title: 'Pilih Sport', value: '' }, ...sportData]
  } catch (error) {
    console.error('Gagal memuat sports', error)
  }
}

function editPlayer(player: any) {
  router.push({ name: 'dashboards-player-edit-id', params: { id: player.id } })
}

async function deletePlayer(player: any) {
  const confirm = await Swal.fire({
    title: 'Apakah kamu yakin?',
    text: `Pemain dengan nama ${player.name} apakah akan dinonaktifkan?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya, Non Aktifkan!',
    cancelButtonText: 'Batal',
    customClass: {
      confirmButton: 'swal2-confirm-btn',
      cancelButton: 'swal2-cancel-btn',
    },
  })

  if (confirm.isConfirmed) {
    try {
      loading.value = true

      await $api(`player/${player.id}`, {
        method: 'DELETE',
      })

      await fetchPlayer()

      snackbarMessage.value = 'Pemain berhasil dihapus'
      snackbarColor.value = 'success'
      isSnackbarVisible.value = true
    } catch (err: any) {
      snackbarMessage.value = err?.response?.data?.message || 'Gagal menghapus pemain'
      snackbarColor.value = 'error'
      isSnackbarVisible.value = true
    } finally {
      loading.value = false
    }
  }
}

async function activatePlayer(player: any) {
  const confirm = await Swal.fire({
    title: 'Aktifkan Player?',
    text: `Player ${player.name} akan diaktifkan kembali.`,
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

      await $api(`player/${player.id}/activate`, {
        method: 'PUT',
      })

      await fetchPlayer()

      snackbarMessage.value = 'Player berhasil diaktifkan kembali'
      snackbarColor.value = 'success'
      isSnackbarVisible.value = true
    } catch (err: any) {
      snackbarMessage.value = err?.response?.data?.message || 'Gagal mengaktifkan player'
      snackbarColor.value = 'error'
      isSnackbarVisible.value = true
    } finally {
      loading.value = false
    }
  }
}

async function approvePlayer(player: any) {
  const result = await Swal.fire({
    title: 'Terima Pemain?',
    text: `Pemain ${player.name} apakah akan diterima?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Ya, Terima!',
    cancelButtonText: 'Batal',
    customClass: {
      confirmButton: 'swal2-confirm-btn',
      cancelButton: 'swal2-cancel-btn',
    },
  })

  if (result.isConfirmed) {
    $api(`player/${player.id}/approve`, {
      method: 'PUT',
    })
      .then(() => {
        fetchPlayer()
        snackbarMessage.value = 'Pemain diterima'
        snackbarColor.value = 'success'
        isSnackbarVisible.value = true
      })
      .catch((err: any) => {
        snackbarMessage.value = err?.response?.data?.message || 'Gagal menerima Pemain'
        snackbarColor.value = 'error'
        isSnackbarVisible.value = true
      })
  }
}

async function rejectPlayer(player: any) {
  const result = await Swal.fire({
    title: 'Perlu Perbaikan Data?',
    text: `Pemain ${player.name} apakah perlu melakukan perbaikan?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Ya, Perbaiki!',
    cancelButtonText: 'Batal',
    customClass: {
      confirmButton: 'swal2-confirm-btn',
      cancelButton: 'swal2-cancel-btn',
    },
  })

  if (result.isConfirmed) {
    $api(`player/${player.id}/reject`, {
      method: 'PUT',
    })
      .then(() => {
        fetchPlayer()
        snackbarMessage.value = 'Pemain ditolak'
        snackbarColor.value = 'success'
        isSnackbarVisible.value = true
      })
      .catch((err: any) => {
        snackbarMessage.value = err?.response?.data?.message || 'Gagal menolak pemain'
        snackbarColor.value = 'error'
        isSnackbarVisible.value = true
      })
  }
}

async function exportPlayers() {
  exportLoading.value = true
  
  try {
    const response = await $api(`player/export`, {
      method: 'POST',
      params: {
        format: 'xlsx',
        search: searchQuery.value,
        status: selectedStatus.value,
        sort: selectedSort.value,
      },
      responseType: 'blob',
    })

    const blob = new Blob([response], { 
      type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' 
    })
    
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    
    const timestamp = new Date().toISOString().slice(0, 19).replace(/[:.]/g, '-')
    link.download = `Players_Export_${timestamp}.xlsx`
    
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    
    window.URL.revokeObjectURL(url)

    snackbarMessage.value = 'Data berhasil diekspor'
    snackbarColor.value = 'success'
    isSnackbarVisible.value = true

  } catch (err: any) {
    console.error('Export error:', err)
    snackbarMessage.value = err?.response?.data?.message || 'Gagal mengekspor data'
    snackbarColor.value = 'error'
    isSnackbarVisible.value = true
  } finally {
    exportLoading.value = false
  }
}

function getQueryParam(param: LocationQueryValue | LocationQueryValue[] | undefined): string {
  return Array.isArray(param) ? param[0] || '' : param || ''
}

onMounted(() => {
  searchQuery.value = getQueryParam(route.query.search)
  // selectedClub.value = getQueryParam(route.query.club_id)
  selectedStatus.value = getQueryParam(route.query.status)
  selectedSort.value = getQueryParam(route.query.sort)

  fetchPlayer()
  fetchClubs()
  fetchSports()
})

watch([searchQuery, selectedStatus, selectedSort], () => {
  router.replace({
    query: {
      ...route.query,
      search: searchQuery.value || undefined,
      // club_id: selectedClub.value || undefined,
      status: selectedStatus.value || undefined,
      sort: selectedSort.value || undefined,
    },
  })

  fetchPlayer()
})
</script>

<template>
  <VRow>
    <VCol cols="12">
      <div class="d-flex mb-6">
        <VRow class="w-100">
          <template v-for="(data, id) in widgetData" :key="id">
            <VCol
              cols="12"
              md="3"
              sm="6"
            >
              <VCard class="h-100">
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
                      <VIcon :icon="data.icon" size="26" />
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
          <VCardTitle>Master Pemain</VCardTitle>
        </VCardItem>

        <VCardText>
          <VRow>
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
                  { title: 'Menunggu Konfirmasi', value: 'in_confirm' },
                  { title: 'Tidak Aktif', value: 'in_active' }
                ]"
              />
            </VCol>

            <VCol
              cols="12"
              sm="6"
            >
              <AppSelect
                v-model="selectedSort"
                placeholder="Z-A"
                clearable
                clear-icon="tabler-x"
                single-line
                :items="[
                  { title: 'Pilih Sortir', value: '' },
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
                placeholder="Cari Pemain"
            />
          </div>
          <VSpacer />

          <div class="app-user-search-filter d-flex align-center flex-wrap gap-4">
            <VBtn
              color="warning"
              prepend-icon="tabler-upload"
              @click="exportPlayers()"
            >
              Ekspor
            </VBtn>

            <VBtn
              prepend-icon="tabler-plus"
              :to="{ name: 'dashboards-player-add' }"
            >
              Tambah Pemain Baru
            </VBtn>
          </div>
        </VCardText>

        <VDivider />

        <VDataTable
          :headers="headers"
          :items="paginatedPlayers"
          :loading="loading"
          class="text-no-wrap"
          :items-per-page="itemsPerPage"
          hide-default-footer
        >
          <template #item.name="{ item }">
            <div class="d-flex gap-x-3 align-center">
              <!-- <VAvatar
                size="34"
                :image="item.productImage"
                :rounded="0"
              /> -->

              <div class="text-body-1">
                {{ item.name }}
              </div>
            </div>
          </template>

          <template #item.nisn="{ item }">
            <div class="text-body-1">{{ item.nisn }}</div>
          </template>

          <template #item.height="{ item }">
            <div class="text-body-1">{{ item.height }}</div>
          </template>

          <template #item.weight="{ item }">
            <div class="text-body-1">{{ item.weight }}</div>
          </template>

          <template #item.back_number="{ item }">
            <div class="text-body-1">{{ item.back_number || '-' }}</div>
          </template>

          <template #item.status="{ item }">
            <VChip
              label
              class="text-body-1 font-weight-medium"
              :color="statusColor(item).color"
              :text-color="statusColor(item).textColor || 'white'"
              variant="tonal"
              size="small"
            >
              {{ statusColor(item).label }}
            </VChip>
          </template>

          <template #item.action="{ item }">
            <div class="d-flex gap-x-2">
              <VBtn
                v-if="!item.deleted_at"
                icon
                size="small"
                color="primary"
                @click="editPlayer(item)"
                title="Ubah"
              >
                <VIcon icon="tabler-pencil" />
              </VBtn>
              
              <VBtn
                v-if="item.deleted_at"
                icon
                size="small"
                color="success"
                @click="activatePlayer(item)"
                title="Aktikan"
              >
                <VIcon icon="tabler-check" />
              </VBtn>

              <VBtn
                v-if="!item.deleted_at &&  item.status !== 1"
                icon
                size="small"
                color="warning"
                @click="approvePlayer(item)"
                title="Terima"
              >
                <VIcon icon="tabler-file-check" />
              </VBtn>

              <VBtn
                v-if="!item.deleted_at && item.status !== 1"
                icon
                size="small"
                color="error"
                @click="rejectPlayer(item)"
                title="Tolak"
              >
                <VIcon icon="tabler-x" />
              </VBtn>

              <VBtn  
                v-if="!item.deleted_at && item.status !== 0"
                icon
                size="small"
                color="error"
                @click="deletePlayer(item)"
                title="Hapus"
              >
                <VIcon icon="tabler-trash" />
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

.widget-col {
  flex: 1 1 20%;   /* setiap col ambil 20% */
  max-width: 20%;
}
</style>
