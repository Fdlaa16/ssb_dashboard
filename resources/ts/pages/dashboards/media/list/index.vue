<script setup lang="ts">
import Swal from 'sweetalert2'
import { computed, onMounted, ref } from 'vue'
import type { LocationQueryValue } from 'vue-router'
import { useRouter } from 'vue-router'

const router = useRouter()
const route = useRoute()

const searchQuery = ref('')
const selectedTypeMedia = ref('')
const selectedSport = ref('')
const selectedStatus = ref('')
const selectedSort = ref('')

const medias = ref<any[]>([])
const clubs = ref<{ title: string; value: string | number }[]>([])
const sports = ref<{ title: string; value: string | number }[]>([])

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
  return Math.ceil(medias.value.length / itemsPerPage)
})

const widgetData = ref([
  { title: 'All', value: 0, icon: 'tabler-user' },
  { title: 'Active', value: 0, icon: 'tabler-user' },
  { title: 'Non Active', value: 0, icon: 'tabler-user' },
])

const headers = [
  { title: 'ID', key: 'id' },
  { title: 'Judul', key: 'title' },
  { title: 'Tipe Media', key: 'type_media' },
  { title: 'Aksi', key: 'action', sortable: false },
]

const paginatedMedias = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return medias.value.slice(start, start + itemsPerPage)
})

async function fetchMedia() {
  loading.value = true
  error.value = null

  try {
    const response = await $api('media', {
      method: 'GET',
      params: {
        search: searchQuery.value,
        type_media: selectedTypeMedia.value,
        status: selectedStatus.value,
        sort: selectedSort.value,
      },
    })

    medias.value = response.data 
    const totals = response.totals

    widgetData.value = [
      { title: 'All', value: totals.all, icon: 'tabler-news', iconColor: 'primary', change: 0, desc: 'Total semua club' },
      { title: 'Active', value: totals.active, icon: 'tabler-news', iconColor: 'success', change: 0, desc: 'Club aktif' },
      { title: 'Non Active', value: totals.in_active, icon: 'tabler-news', iconColor: 'error', change: 0, desc: 'Club tidak aktif' },
    ]

  } catch (err: any) {
    error.value = err.message || 'Gagal memuat data'
  } finally {
    loading.value = false
  }
}

function editMedia(media: any) {
  router.push({ name: 'dashboards-media-edit-id', params: { id: media.id } })
}

async function deleteMedia(media: any) {
  const confirm = await Swal.fire({
    title: 'Apakah kamu yakin?',
    text: `Data media dengan nama ${media.name} akan dihapus.`,
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

      await $api(`media/${media.id}`, {
        method: 'DELETE',
      })

      await fetchMedia()

      snackbarMessage.value = 'Media berhasil dihapus'
      snackbarColor.value = 'success'
      isSnackbarVisible.value = true
    } catch (err: any) {
      snackbarMessage.value = err?.response?.data?.message || 'Gagal menghapus media'
      snackbarColor.value = 'error'
      isSnackbarVisible.value = true
    } finally {
      loading.value = false
    }
  }
}

async function activateMedia(media: any) {
  const confirm = await Swal.fire({
    title: 'Aktifkan Media?',
    text: `Media ${media.name} akan diaktifkan kembali.`,
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

      await $api(`media/${media.id}/active`, {
        method: 'PUT',
      })

      await fetchMedia()

      snackbarMessage.value = 'Media berhasil diaktifkan kembali'
      snackbarColor.value = 'success'
      isSnackbarVisible.value = true
    } catch (err: any) {
      snackbarMessage.value = err?.response?.data?.message || 'Gagal mengaktifkan media'
      snackbarColor.value = 'error'
      isSnackbarVisible.value = true
    } finally {
      loading.value = false
    }
  }
}

async function approveMedia(media: any) {
  const result = await Swal.fire({
    title: 'Terima Media?',
    text: `Media ${media.name} akan diterima?.`,
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
    $api(`media/${media.id}/approve`, {
      method: 'PUT',
    })
      .then(() => {
        fetchMedia()
        snackbarMessage.value = 'Media diterima'
        snackbarColor.value = 'success'
        isSnackbarVisible.value = true
      })
      .catch((err: any) => {
        snackbarMessage.value = err?.response?.data?.message || 'Gagal menerima media'
        snackbarColor.value = 'error'
        isSnackbarVisible.value = true
      })
  }
}

async function rejectMedia(media: any) {
  const result = await Swal.fire({
    title: 'Tolak Media?',
    text: `Media ${media.name} akan ditolak?.`,
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
    $api(`media/${media.id}/reject`, {
      method: 'PUT',
    })
      .then(() => {
        fetchMedia()
        snackbarMessage.value = 'Media ditolak'
        snackbarColor.value = 'success'
        isSnackbarVisible.value = true
      })
      .catch((err: any) => {
        snackbarMessage.value = err?.response?.data?.message || 'Gagal menolak media'
        snackbarColor.value = 'error'
        isSnackbarVisible.value = true
      })
  }
}


function getQueryParam(param: LocationQueryValue | LocationQueryValue[] | undefined): string {
  return Array.isArray(param) ? param[0] || '' : param || ''
}

onMounted(() => {
  searchQuery.value = getQueryParam(route.query.search)
  selectedTypeMedia.value = getQueryParam(route.query.type_media)
  selectedStatus.value = getQueryParam(route.query.status)
  selectedSort.value = getQueryParam(route.query.sort)

  fetchMedia()
})

watch([searchQuery, selectedTypeMedia, selectedStatus, selectedSort], () => {
  router.replace({
    query: {
      ...route.query,
      search: searchQuery.value || undefined,
      type_media: selectedTypeMedia.value || undefined,
      status: selectedStatus.value || undefined,
      sort: selectedSort.value || undefined,
    },
  })

  fetchMedia()
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
          <VCardTitle>Master Media</VCardTitle>
        </VCardItem>

         <VCardText>
          <VRow>
            <VCol
              cols="12"
              sm="4"
            >
              <AppSelect
                v-model="selectedTypeMedia"
                placeholder="Type Media"
                clearable
                clear-icon="tabler-x"
                single-line
                :items="[
                  { title: 'Pilih Type Media', value: '' },
                  { title: 'Documentation', value: 'documentation' },
                  { title: 'Performance', value: 'performance' }
                ]"
              />
            </VCol>

            <VCol
              cols="12"
              sm="4"
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
              sm="4"
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
                placeholder="Cari Media"
            />
          </div>
          <VSpacer />

          <div class="app-user-search-filter d-flex align-center flex-wrap gap-4">
            <VBtn
              prepend-icon="tabler-plus"
              :to="{ name: 'dashboards-media-add' }"
            >
              Tambah Media Baru
            </VBtn>
          </div>
        </VCardText>

        <VDivider />

        <VDataTable
          :headers="headers"
          :items="paginatedMedias"
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

          <template #item.sports="{ item }">
            <div class="text-body-1">
              <span
                v-for="(sport, index) in item.sports"
                :key="sport.id"
              >
                {{ sport.name }}<span v-if="index < item.sports.length - 1">, </span>
              </span>
            </div>
          </template>

          <!-- <template #item.clubs="{ item }">
            <div class="text-body-1">
              <span
                v-for="(club, index) in item.clubs"
                :key="club.id"
              >
                {{ club.name }}<span v-if="index < item.clubs.length - 1">, </span>
              </span>
            </div>
          </template> -->

          <template #item.action="{ item }">
            <div class="d-flex gap-x-2">
              <VBtn
                v-if="!item.deleted_at"
                icon
                size="small"
                color="primary"
                @click="editMedia(item)"
                title="Ubah"
              >
                <VIcon icon="tabler-pencil" />
              </VBtn>

              <VBtn  
                v-if="!item.deleted_at"
                icon
                size="small"
                color="error"
                @click="deleteMedia(item)"
                title="Hapus"
              >
                <VIcon icon="tabler-trash" />
              </VBtn>
              
              <VBtn
                v-if="item.deleted_at"
                icon
                size="small"
                color="success"
                @click="activateMedia(item)"
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
