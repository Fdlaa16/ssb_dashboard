<script lang="ts" setup>
import ScheduleMatchEditable from '@/views/dashboards/schedule_match/ScheduleMatchEditable.vue';
import type { ScheduleMatchData } from '@/views/dashboards/schedule_match/types';
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();

const scheduleMatchId = route.params.id as string;
const error = ref<string | null>(null)
const loading = ref(false)
const isFlatSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref<'success' | 'error'>('success')

const scheduleMatchData = ref<ScheduleMatchData>({
  id: 0, 
  first_club_id: '',
  secound_club_id: '',
  stadium_id: '',
  schedule_date: '',
  schedule_start_at: '',
  schedule_end_at: '',
  score: '',
});


const fetchScheduleMatch = async () => {
  loading.value = true;
  try {
    const res = await $api(`schedule-match/${scheduleMatchId}/edit`);
  
    scheduleMatchData.value = {
      ...res.data,
      first_club_id: res.data.first_club?.id ?? 0,
      secound_club_id: res.data.secound_club?.id ?? 0,
      stadium_id: res.data.stadium?.id ?? 0,
    }

    console.log('Fetched schedule match data:', scheduleMatchData.value);
    
  } catch (err: any) {
    error.value = err.message || 'Gagal mengambil data schedule match';
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  await fetchScheduleMatch();
});

const handleSubmit = async () => {
  try {    

    console.log('Submitting schedule match data:', scheduleMatchData.value);
    
    const formData = new FormData();
    formData.append('_method', 'PUT'); 

    formData.append('first_club_id', String(scheduleMatchData.value.first_club_id));
    formData.append('secound_club_id', String(scheduleMatchData.value.secound_club_id));
    formData.append('stadium_id', String(scheduleMatchData.value.stadium_id));
    formData.append('schedule_date', scheduleMatchData.value.schedule_date); 
    formData.append('schedule_start_at', scheduleMatchData.value.schedule_start_at);
    formData.append('schedule_end_at', scheduleMatchData.value.schedule_end_at);
    formData.append('score', scheduleMatchData.value.score ?? '');
    
    const res = await $api(`schedule-match/${scheduleMatchId}`, {
      method: 'POST',
      body: formData,
    });

    snackbarMessage.value = 'Data berhasil dibuat!';
    snackbarColor.value = 'success';
    isFlatSnackbarVisible.value = true;

    router.push({
      name: 'dashboards-schedule-match-list',
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
      <ScheduleMatchEditable
        :data="scheduleMatchData"  
        @update:data="scheduleMatchData = $event"
        @submit="onSubmit"
      />

    </VCol>
  </VRow>
</template>
