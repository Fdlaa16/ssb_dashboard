<script setup lang="ts">
import type { Club, ScheduleTrainingData, Stadium } from './types';

const clubs = ref<Club[]>([])
const stadiums = ref<Stadium[]>([])

const props = defineProps<{ data: ScheduleTrainingData }>()
const emit = defineEmits(['submit', 'update:data'])

const localData = ref<ScheduleTrainingData>({
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

const capitalizedLabel = (label: boolean) => {
  return label ? 'Active' : 'Non Active'
}
</script>

<template>
  <form @submit.prevent="$emit('submit')">
    <div class="d-flex flex-column gap-6 mb-6">
      <VCard title="Create Schedule Training" >
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
                      <h6 class="text-h6 mb-1">Club Pertama</h6>
                      <AppSelect
                        v-model="localData.first_club_id"
                        :items="clubs"
                        placeholder="Club"
                        clearable
                        clear-icon="tabler-x"
                        single-line
                      />
                    </VCol>

                    <VCol cols="6">
                      <h6 class="text-h6 mb-1">Club Kedua</h6>
                      <AppSelect
                        v-model="localData.secound_club_id"
                        :items="clubs"
                        placeholder="Club"
                        clearable
                        clear-icon="tabler-x"
                        single-line
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
                      
                  <AppTextField
                    v-model="localData.score"
                    label="Skor"
                    placeholder="Contoh: 2 - 1"
                    :rules="[value => /^\d+\s*-\s*\d+$/.test(value) || 'Format harus 2 - 1']"
                    class="mt-5"
                  />

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
