<script lang="ts" setup>
import ScheduleMatchEditable from '@/views/dashboards/schedule_match/ScheduleMatchEditable.vue';
import type { ScheduleMatchData } from '@/views/dashboards/schedule_match/types';
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter() 
const selectedSports = ref<Sport[]>([])
const isFlatSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref<'success' | 'error'>('success')

const scheduleMatchData = ref<ScheduleMatchData>({
  id: 0, 
  first_club_id: 0,
  secound_club_id: 0,
  stadium_id: 0,
  schedule_date: '',
  schedule_start_at: '',
  schedule_end_at: '',
  score: '',
  status: false,
})

const handleSubmit = async () => {
  const formData = new FormData();
  formData.append('first_club_id', String(scheduleMatchData.value.first_club_id));
  formData.append('secound_club_id', String(scheduleMatchData.value.secound_club_id));
  formData.append('stadium_id', String(scheduleMatchData.value.stadium_id));
  formData.append('schedule_date', scheduleMatchData.value.schedule_date); 
  formData.append('schedule_start_at', scheduleMatchData.value.schedule_start_at);
  formData.append('schedule_end_at', scheduleMatchData.value.schedule_end_at);
  formData.append('score', scheduleMatchData.value.score ?? '');
  formData.append('status', scheduleMatchData.value.status ? '1' : '0');

  try {
    const response = await $api('schedule-match/store', {
      method: 'POST',
      body: formData,
    });

    snackbarMessage.value = 'Data berhasil dibuat!';
    snackbarColor.value = 'success';
    isFlatSnackbarVisible.value = true;

    router.push({
      name: 'dashboards-schedule-match-list',
      query: {
        success: 'Data berhasil dibuat!',
      },
    });
  } catch (err: any) {
    const errors = err?.data?.errors

    if (err?.status === 422 && errors) {
      const messages = Object.values(errors).flat()
      snackbarMessage.value = 'Validasi gagal: ' + messages.join(', ')
    } else {
      snackbarMessage.value = 'Gagal mengirim data: ' + (err?.message || 'Unknown error')
    }

    snackbarColor.value = 'error'
    isFlatSnackbarVisible.value = true
  }
};

const onSubmit = () => {
  handleSubmit()
}
</script>

<template>
  <VRow>
    <VCol
      cols="12"
      md="12"
    >
    <ScheduleMatchEditable
      :data="scheduleMatchData"
      @update:data="scheduleMatchData = $event"
      @submit="onSubmit"
    />
    </VCol>
  </VRow>

  <VSnackbar
    v-model="isFlatSnackbarVisible"
    :color="snackbarColor"
    location="bottom start"
    variant="flat"
    timeout="3000"
  >
    {{ snackbarMessage }}
  </VSnackbar>

</template>
