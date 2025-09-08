<script setup lang="ts">
import type { Club, ScheduleMatchData, Stadium } from './types';

const clubs = ref<Club[]>([])
const stadiums = ref<Stadium[]>([])

const props = defineProps<{ data: ScheduleMatchData }>()
const emit = defineEmits(['submit', 'update:data'])

const localData = ref<ScheduleMatchData>({
  ...props.data,
})

watch(() => props.data, (newVal) => {
  localData.value = props.data
}, { deep: true })

async function getClubs() {
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

async function getStadiums() {
  try {
    const response = await $api('stadium', {
      method: 'GET',
    })

    const stadiumData = response.data.map((stadium: any) => ({
      title: stadium.name,
      value: stadium.id,
    }))

    stadiums.value = [{ title: 'Pilih stadium', value: '' }, ...stadiumData]
  } catch (error) {
    console.error('Gagal memuat stadiums', error)
  }
}

const statuses = ref([
  { title: 'Belum Dimulai', value: 'not_started' },
  { title: 'Sedang Berlangsung', value: 'in_progress' },
  { title: 'Selesai', value: 'finished' },
])

onMounted(async () => {
  getClubs()
  getStadiums()
})

watch(localData, (newVal, oldVal) => {
  if (JSON.stringify(newVal) !== JSON.stringify(oldVal)) {
    emit('update:data', newVal)
  }
}, { deep: true })

const submitForm = () => {
  emit('update:data', localData.value)
  emit('submit')
}

const filteredClubsForFirst = computed(() => {
  return clubs.value.filter(club => club.value !== localData.value.secound_club_id)
})

const filteredClubsForSecond = computed(() => {
  return clubs.value.filter(club => club.value !== localData.value.first_club_id)
})
</script>

<template>
  <form @submit.prevent="$emit('submit')">
    <div class="d-flex flex-column gap-6 mb-6">
      <VCard :title="props.data.id ? 'Ubah Jadwal Pertandingan' : 'Tambah Jadwal Pertandingan'">
        <VCardText>
          <VWindow>
            <VWindowItem>

              <VRow>
                <VCol cols="12" class="text-no-wrap">
                  <VRow class="justify-center align-center">
                    <VCol cols="4">
                      <AppDateTimePicker
                        v-model="localData.schedule_date"
                        label="Schedule Date"
                        placeholder="Schedule date"
                      />
                    </VCol>

                    <VCol cols="4">
                      <AppDateTimePicker
                        v-model="localData.schedule_start_at"
                        label="Start At"
                        placeholder="Select time"
                        :config="{ enableTime: true, noCalendar: true, dateFormat: 'H:i' }"
                      />
                    </VCol>

                    <VCol cols="4">
                      <AppDateTimePicker
                        v-model="localData.schedule_end_at"
                        label="End At"
                        placeholder="Select time"
                        :config="{ enableTime: true, noCalendar: true, dateFormat: 'H:i' }"
                      />
                    </VCol>
                  </VRow>

                  <VRow class="justify-center align-center">
                    <VCol cols="6">
                      <AppSelect
                        v-model="localData.first_club_id"
                        :items="filteredClubsForFirst"
                        item-title="title"
                        item-value="value"
                        placeholder="Pilih Club"
                        clearable
                        clear-icon="tabler-x"
                        single-line
                        label="Club Pertama"
                      />
                    </VCol>

                    <VCol cols="6">
                      <AppSelect
                        v-model="localData.secound_club_id"
                        :items="filteredClubsForSecond"
                        item-title="title"
                        item-value="value"
                        placeholder="Pilih Club"
                        clearable
                        clear-icon="tabler-x"
                        single-line
                        label="Club Kedua"
                      />
                    </VCol>
                  </VRow>

                  <h6 class="text-h6 mb-1 mt-5">Stadium</h6>
                  <AppSelect
                    v-model="localData.stadium_id"
                    placeholder="Stadium"
                    clearable
                    clear-icon="tabler-x"
                    single-line
                    :items="stadiums"
                  />

                  <template v-if="localData.id">
                    <h6 class="text-h6 mb-1 mt-5">Status</h6>
                    <AppSelect
                      v-model="localData.status"
                      :items="statuses"
                      placeholder="Status"
                      clearable
                      clear-icon="tabler-x"
                      single-line
                    />

                    <template v-if="localData.id && localData.status === 'finished'">
                      <VRow>
                        <VCol cols="6">
                          <AppTextField
                            v-model="localData.first_club_score"
                            label="Skor Club Pertama"
                            placeholder="Contoh: 3"
                            class="mt-5"
                          />
                        </VCol>  

                        <VCol cols="6">
                          <AppTextField
                            v-model="localData.secound_club_score"
                            label="Skor Club Kedua"
                            placeholder="Contoh: 3"
                            class="mt-5"
                          />
                        </VCol>
                      </VRow>
                    </template>
                  </template> 
                </VCol>
              </VRow>
            </VWindowItem>
          </VWindow>
        </VCardText>

        <!-- Tombol Submit -->
        <VCol cols="12" class="d-flex justify-end">
          <VBtn
            color="primary"
            @click="submitForm"
          >
            Simpan
          </VBtn>
        </VCol>
      </VCard>
    </div>
  </form>
</template>
