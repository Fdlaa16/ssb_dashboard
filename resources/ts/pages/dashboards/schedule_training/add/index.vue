<script lang="ts" setup>
import ScheduleTrainingEditable from '@/views/dashboards/schedule_training/ScheduleTrainingEditable.vue';
import type { ScheduleTrainingData } from '@/views/dashboards/schedule_training/types';
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter() 
const isFlatSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref<'success' | 'error'>('success')

const scheduleTrainingData = ref<ScheduleTrainingData>({
  id: 0, 
  first_club_id: '',
  secound_club_id: '',
  stadium_id: '',
  schedule_date: '',
  schedule_start_at: '',
  schedule_end_at: '',
  score: '',
  status: false,
})

const handleSubmit = async () => {
  const formData = new FormData();
  formData.append('first_club_id', String(scheduleTrainingData.value.first_club_id));
  formData.append('secound_club_id', String(scheduleTrainingData.value.secound_club_id));
  formData.append('stadium_id', String(scheduleTrainingData.value.stadium_id));
  formData.append('schedule_date', scheduleTrainingData.value.schedule_date); 
  formData.append('schedule_start_at', scheduleTrainingData.value.schedule_start_at);
  formData.append('schedule_end_at', scheduleTrainingData.value.schedule_end_at);
  formData.append('score', scheduleTrainingData.value.score ?? '');
  formData.append('status', scheduleTrainingData.value.status ? '1' : '0');

  try {
    const response = await $api('schedule-training/store', {
      method: 'POST',
      body: formData,
    });

    snackbarMessage.value = 'Data berhasil dibuat!';
    snackbarColor.value = 'success';
    isFlatSnackbarVisible.value = true;

    router.push({
      name: 'dashboards-schedule-training-list',
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
    <ScheduleTrainingEditable
      :data="scheduleTrainingData"
      @update:data="scheduleTrainingData = $event"
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
