<script setup lang="ts">
import Swal from 'sweetalert2'
import { computed, onMounted, ref } from 'vue'
import type { LocationQueryValue } from 'vue-router'
import { useRouter } from 'vue-router'

const router = useRouter()
const route = useRoute()

const searchQuery = ref('')
const selectedStatus = ref('')
const selectedSort = ref('')

const structures = ref<any[]>([])
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
  return Math.ceil(structures.value.length / itemsPerPage)
})

const widgetData = ref([
  { title: 'All', value: 0, icon: 'tabler-user' },
  { title: 'Active', value: 0, icon: 'tabler-user' },
  { title: 'In Confirm', value: 0, icon: 'tabler-user' },
  { title: 'Non Active', value: 0, icon: 'tabler-user' },
])

const headers = [
  { title: 'ID', key: 'id' },
  { title: 'Kode', key: 'code' },
  { title: 'Nama', key: 'name' },
  { title: 'Departemen', key: 'department' },
  { title: 'Aksi', key: 'action', sortable: false },
]

const statusColorMap = {
  1: { label: 'Active', color: 'success' },
  0: { label: 'In Confirm', color: 'warning', textColor: 'black' },
  2: { label: 'Non Active', color: 'error' },
}

const paginatedStructures = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return structures.value.slice(start, start + itemsPerPage)
})

async function fetchStructure() {
  loading.value = true
  error.value = null

  try {
    const response = await $api('structure', {
      method: 'GET',
      params: {
        search: searchQuery.value,
        status: selectedStatus.value,
        sort: selectedSort.value,
      },
    })

    structures.value = response.data         
    const totals = response.totals

    widgetData.value = [
      { title: 'All', value: totals.all, icon: 'tabler-user', iconColor: 'primary', change: 0, desc: 'Total semua structure' },
      { title: 'Active', value: totals.active, icon: 'tabler-user-check', iconColor: 'success', change: 0, desc: 'Structure aktif' },
      { title: 'Non Active', value: totals.in_active, icon: 'tabler-user-x', iconColor: 'error', change: 0, desc: 'Structure tidak aktif' },
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

function editStructure(structure: any) {
  router.push({ name: 'dashboards-structure-edit-id', params: { id: structure.id } })
}

async function deleteStructure(structure: any) {
  const confirm = await Swal.fire({
    title: 'Apakah kamu yakin?',
    text: `Data structure dengan nama ${structure.name} akan dihapus.`,
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

      await $api(`structure/${structure.id}`, {
        method: 'DELETE',
      })

      await fetchStructure()

      snackbarMessage.value = 'Structure berhasil dihapus'
      snackbarColor.value = 'success'
      isSnackbarVisible.value = true
    } catch (err: any) {
      snackbarMessage.value = err?.response?.data?.message || 'Gagal menghapus structure'
      snackbarColor.value = 'error'
      isSnackbarVisible.value = true
    } finally {
      loading.value = false
    }
  }
}

async function activateStructure(structure: any) {
  const confirm = await Swal.fire({
    title: 'Aktifkan Structure?',
    text: `Structure ${structure.name} akan diaktifkan kembali.`,
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

      await $api(`structure/${structure.id}/active`, {
        method: 'PUT',
      })

      await fetchStructure()

      snackbarMessage.value = 'Structure berhasil diaktifkan kembali'
      snackbarColor.value = 'success'
      isSnackbarVisible.value = true
    } catch (err: any) {
      snackbarMessage.value = err?.response?.data?.message || 'Gagal mengaktifkan structure'
      snackbarColor.value = 'error'
      isSnackbarVisible.value = true
    } finally {
      loading.value = false
    }
  }
}

async function approveStructure(structure: any) {
  const result = await Swal.fire({
    title: 'Terima Structure?',
    text: `structure ${structure.name} akan diterima?.`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Ya, terima!',
    cancelButtonText: 'Batal',
    customClass: {
      confirmButton: 'swal2-confirm-btn',
      cancelButton: 'swal2-cancel-btn',
    },
  })

  if (result.isConfirmed) {
    $api(`structure/${structure.id}/approve`, {
      method: 'PUT',
    })
      .then(() => {
        fetchStructure()
        snackbarMessage.value = 'Structure diterima'
        snackbarColor.value = 'success'
        isSnackbarVisible.value = true
      })
      .catch((err: any) => {
        snackbarMessage.value = err?.response?.data?.message || 'Gagal menerima structure'
        snackbarColor.value = 'error'
        isSnackbarVisible.value = true
      })
  }
}

async function rejectStructure(structure: any) {
  const result = await Swal.fire({
    title: 'Tolak Structure?',
    text: `structure ${structure.name} akan ditolak?.`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Ya, tolak!',
    cancelButtonText: 'Batal',
    customClass: {
      confirmButton: 'swal2-confirm-btn',
      cancelButton: 'swal2-cancel-btn',
    },
  })

  if (result.isConfirmed) {
    $api(`structure/${structure.id}/reject`, {
      method: 'PUT',
    })
      .then(() => {
        fetchStructure()
        snackbarMessage.value = 'Structure ditolak'
        snackbarColor.value = 'success'
        isSnackbarVisible.value = true
      })
      .catch((err: any) => {
        snackbarMessage.value = err?.response?.data?.message || 'Gagal menolak structure'
        snackbarColor.value = 'error'
        isSnackbarVisible.value = true
      })
  }
}

async function exportStructures() {
  exportLoading.value = true
  
  try {
    const response = await $api(`structure/export`, {
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
    link.download = `Structure_Export_${timestamp}.xlsx`
    
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
  selectedStatus.value = getQueryParam(route.query.status)
  selectedSort.value = getQueryParam(route.query.sort)

  fetchStructure()
  fetchClubs()
  fetchSports()
})

watch([searchQuery, selectedStatus, selectedSort], () => {
  router.replace({
    query: {
      ...route.query,
      search: searchQuery.value || undefined,
      status: selectedStatus.value || undefined,
      sort: selectedSort.value || undefined,
    },
  })

  fetchStructure()
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
          <VCardTitle>Master Pengurus</VCardTitle>
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
                placeholder="Cari Pengurus"
            />
          </div>

          <VSpacer />
          <div class="app-user-search-filter d-flex align-center flex-wrap gap-4">
            <VBtn
              color="warning"
              prepend-icon="tabler-upload"
              @click="exportStructures()"
            >
              Ekspor
            </VBtn>

            <VBtn
              prepend-icon="tabler-plus"
              :to="{ name: 'dashboards-structure-add' }"
            >
              Tambah Pengurus Baru
            </VBtn>
          </div>
        </VCardText>

        <VDivider />

        <VDataTable
          :headers="headers"
          :items="paginatedStructures"
          :loading="loading"
          class="text-no-wrap"
          :items-per-page="itemsPerPage"
          hide-default-footer
        >
          <template #item.code="{ item }">
            <div class="text-body-1">{{ item.code }}</div>
          </template>

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

          <template #item.department="{ item }">
            <div class="text-body-1">{{ item.department }}</div>
          </template>

          <template #item.action="{ item }">
            <div class="d-flex gap-x-2">
              <VBtn
                v-if="!item.deleted_at"
                icon
                size="small"
                color="primary"
                @click="editStructure(item)"
                title="Ubah"
              >
                <VIcon icon="tabler-pencil" />
              </VBtn>

              <VBtn  
                v-if="!item.deleted_at && item.status !== 0"
                icon
                size="small"
                color="error"
                @click="deleteStructure(item)"
                title="Hapus"
              >
                <VIcon icon="tabler-trash" />
              </VBtn>
              
              <VBtn
                v-if="item.deleted_at"
                icon
                size="small"
                color="success"
                @click="activateStructure(item)"
                title="Aktifkan"
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
