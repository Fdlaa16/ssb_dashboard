<script lang="ts" setup>
import ScheduleTrainingEditable from '@/views/dashboards/schedule_training/ScheduleTrainingEditable.vue';
import type { ScheduleTrainingData } from '@/views/dashboards/schedule_training/types';
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();

const scheduleTrainingId = route.params.id as string;
const error = ref<string | null>(null)
const loading = ref(false)
const isFlatSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref<'success' | 'error'>('success')

const scheduleTrainingData = ref<ScheduleTrainingData>({
  id: 0, 
  // first_club_id: '',
  // secound_club_id: '',
  stadium_id: '',
  schedule_date: '',
  schedule_start_at: '',
  // schedule_end_at: '',
  // first_club_score: '',
  // secound_club_score: '',
  // status: '',
});


const fetchScheduleTraining = async () => {
  loading.value = true;
  try {
    const res = await $api(`schedule-training/${scheduleTrainingId}/edit`);
  
    scheduleTrainingData.value = {
      ...res.data,
      first_club_id: res.data.first_club?.id ?? 0,
      secound_club_id: res.data.secound_club?.id ?? 0,
      stadium_id: res.data.stadium?.id ?? 0,
    }

    console.log('Fetched schedule training data:', scheduleTrainingData.value);
    
  } catch (err: any) {
    error.value = err.message || 'Gagal mengambil data schedule training';
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  await fetchScheduleTraining();
});

const handleSubmit = async () => {
  try {    

    console.log('Submitting schedule training data:', scheduleTrainingData.value);
    
    const formData = new FormData();
    formData.append('_method', 'PUT'); 

    formData.append('first_club_id', String(scheduleTrainingData.value.first_club_id));
    formData.append('secound_club_id', String(scheduleTrainingData.value.secound_club_id));
    formData.append('stadium_id', String(scheduleTrainingData.value.stadium_id));
    formData.append('schedule_date', scheduleTrainingData.value.schedule_date); 
    formData.append('schedule_start_at', scheduleTrainingData.value.schedule_start_at);
    formData.append('schedule_end_at', scheduleTrainingData.value.schedule_end_at);
    formData.append('first_club_score', scheduleTrainingData.value.first_club_score ?? '');
    formData.append('secound_club_score', scheduleTrainingData.value.secound_club_score ?? '');
    formData.append('status', scheduleTrainingData.value.status ?? '');
    
    const res = await $api(`schedule-training/${scheduleTrainingId}`, {
      method: 'POST',
      body: formData,
    });

    snackbarMessage.value = 'Data berhasil dibuat!';
    snackbarColor.value = 'success';
    isFlatSnackbarVisible.value = true;

    router.push({
      name: 'dashboards-schedule-training-list',
      query: {
        success: 'Data berhasil diperbarui!',
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
    <VCol cols="12" md="12">
      <ScheduleTrainingEditable
        :data="scheduleTrainingData"  
        @update:data="scheduleTrainingData = $event"
        @submit="onSubmit"
      />

    </VCol>
  </VRow>
</template>
