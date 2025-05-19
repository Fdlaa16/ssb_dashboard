<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'

const searchQuery = ref('')
const selectedStatus = ref<invoiceStatus>(null)
const selectedRows = ref([])

const players = ref<any[]>([])
const loading = ref(false)
const error = ref<string | null>(null)
const currentPage = ref(1)

const itemsPerPage = 5 
const page = ref(1)
const sortBy = ref()
const orderBy = ref()

const totalPages = computed(() => {
  return Math.ceil(players.value.length / itemsPerPage)
})

const widgetData = ref([
  { title: 'Active Player', value: 24, icon: 'tabler-user' },
  { title: 'Inactive Player', value: 24, icon: 'tabler-user' },
])

const headers = [
  { title: 'ID', key: 'id' },
  { title: 'Name', key: 'name' },
  { title: 'NISN', key: 'nisn' },
  { title: 'Height (cm)', key: 'height' },
  { title: 'Weight (kg)', key: 'weight' },
  { title: 'Sport', key: 'sports' },
  { title: 'Club', key: 'clubs' },
  { title: 'Action', key: 'action', sortable: false },
]

const paginatedPlayers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return players.value.slice(start, start + itemsPerPage)
})

const { data: invoiceData, execute: fetchInvoices } = await useApi<any>(createUrl('/apps/invoice', {
  query: {
    q: searchQuery,
    status: selectedStatus,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const invoices = computed((): Invoice[] => invoiceData.value.invoices)
const totalInvoices = computed(() => invoiceData.value.totalInvoices)

async function fetchPlayer() {
  loading.value = true
  error.value = null
  try {
    const response = await $api('/api/player')
    players.value = response.data

    console.log('players', response.data)
  } catch (err: any) {
    error.value = err.message || 'Gagal memuat data'
  } finally {
    loading.value = false
  }
}

function editPlayer(player: any) {
  console.log('Edit', player)
}

function deletePlayer(player: any) {
  console.log('Delete', player)
}

function activatePlayer(player: any) {
  console.log('Activate', player)
}

onMounted(() => {
  fetchPlayer()
})
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VCard class="mb-6">
        <VCardText class="px-3">
          <VRow>
            <template
              v-for="(data, id) in widgetData"
              :key="id"
            >
              <VCol
                cols="12"
                sm="6"
                md="6"
                class="px-6"
              >
                <div
                  class="d-flex justify-space-between align-center"
                  :class="$vuetify.display.xs
                    ? id !== widgetData.length - 1 ? 'border-b pb-4' : ''
                    : $vuetify.display.sm
                      ? id < (widgetData.length / 2) ? 'border-b pb-4' : ''
                      : ''"
                >
                  <div class="d-flex flex-column">
                    <h4 class="text-h4">
                      {{ data.value }}
                    </h4>
                    <span class="text-body-1 text-capitalize">{{ data.title }}</span>
                  </div>

                  <VAvatar
                    variant="tonal"
                    rounded
                    size="42"
                  >
                    <VIcon
                      :icon="data.icon"
                      size="26"
                      color="high-emphasis"
                    />
                  </VAvatar>
                </div>
              </VCol>
              <VDivider
                v-if="$vuetify.display.mdAndUp ? id !== widgetData.length - 1
                  : $vuetify.display.smAndUp ? id % 2 === 0
                    : false"
                vertical
                inset
                length="60"
              />
            </template>
          </VRow>
        </VCardText>
      </VCard>
      
      <VCard>
        <VCardItem title="Player Master"/>
        <VCardText class="d-flex justify-space-between align-center flex-wrap gap-4">
          <div class="d-flex gap-4 align-center flex-wrap">
            <div class="d-flex align-center gap-2">
              <span>Show</span>
              <AppSelect
                :model-value="itemsPerPage"
                :items="[
                  { value: 10, title: '10' },
                  { value: 25, title: '25' },
                  { value: 50, title: '50' },
                  { value: 100, title: '100' },
                  { value: -1, title: 'All' },
                ]"
                style="inline-size: 5.5rem;"
                @update:model-value="itemsPerPage = parseInt($event, 10)"
              />
            </div>
            <!-- 👉 Create invoice -->
            <VBtn
              prepend-icon="tabler-plus"
              :to="{ name: 'apps-invoice-add' }"
            >
              Create invoice
            </VBtn>
          </div>

          <div class="d-flex align-center flex-wrap gap-4">
            <div class="invoice-list-filter">
              <AppTextField
                v-model="searchQuery"
                placeholder="Search Player"
                style="inline-size: 10rem;"
              />
            </div>

            <div class="invoice-list-filter">
              <AppSelect
                v-model="selectedStatus"
                placeholder="Club Player"
                clearable
                clear-icon="tabler-x"
                single-line
                style="inline-size: 10rem;"
                :items="['Downloaded', 'Draft', 'Sent', 'Paid', 'Partial Payment', 'Past Due']"
              />
            </div>

            <div class="invoice-list-filter">
              <AppSelect
                v-model="selectedStatus"
                placeholder="Sport Player"
                clearable
                clear-icon="tabler-x"
                single-line
                style="inline-size: 10rem;"
                :items="['Downloaded', 'Draft', 'Sent', 'Paid', 'Partial Payment', 'Past Due']"
              />
            </div>
          </div>
        </VCardText>
        <VDivider />
          
        <VCardText>
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

            <template #item.clubs="{ item }">
              <div class="text-body-1">
                <span
                  v-for="(club, index) in item.clubs"
                  :key="club.id"
                >
                  {{ club.name }}<span v-if="index < item.clubs.length - 1">, </span>
                </span>
              </div>
            </template>

            <template #item.action="{ item }">
              <div class="d-flex gap-x-2">
                <VBtn
                  v-if="!item.deleted_at"
                  icon
                  size="small"
                  color="primary"
                  @click="editPlayer(item)"
                  title="Edit"
                >
                  <VIcon icon="tabler-pencil" />
                </VBtn>

                <VBtn  
                  v-if="!item.deleted_at"
                  icon
                  size="small"
                  color="error"
                  @click="deletePlayer(item)"
                  title="Delete"
                >
                  <VIcon icon="tabler-trash" />
                </VBtn>

                <VBtn
                  v-if="item.deleted_at"
                  icon
                  size="small"
                  color="success"
                  @click="activatePlayer(item)"
                  title="Activate"
                >
                  <VIcon icon="tabler-check" />
                </VBtn>
              </div>
            </template>
          </VDataTable>

          <!-- Pagination -->
          <div class="d-flex justify-end mt-4">
            <VPagination
              v-model="currentPage"
              :length="totalPages"
              total-visible="5"
              color="primary"
            />
          </div>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
