<script lang="ts" setup>
import ClubEditable from '@/views/dashboards/club/ClubEditable.vue';
import type { ClubData } from '@/views/dashboards/club/types';
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();

const clubId = route.params.id as string;
const error = ref<string | null>(null)
const loading = ref(false)
const isFlatSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref<'success' | 'error'>('success')

const clubData = ref<ClubData>({
  id: 0,
  code: '',
  name: '',
  profile_club: null,
});

const fetchClub = async () => {
  loading.value = true;
  try {
    const res = await $api(`club/${clubId}/edit`);

    clubData.value = res.data
    
  } catch (err: any) {
    error.value = err.message || 'Gagal mengambil data club';
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  await fetchClub();
});

const handleSubmit = async () => {
  try {        
    const formData = new FormData();
    formData.append('_method', 'PUT'); 

    formData.append('name', clubData.value.name);

    if (clubData.value.profile_club instanceof File)
      formData.append('profile_club', clubData.value.profile_club);

    const res = await $api(`club/${clubId}`, {
      method: 'POST',
      body: formData,
    });

    snackbarMessage.value = 'Data berhasil diperbarui!';
    snackbarColor.value = 'success';
    isFlatSnackbarVisible.value = true;

    router.push({
      name: 'dashboards-club-list',
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
      <ClubEditable
        :data="clubData"  
        @update:data="clubData = $event"
        @submit="onSubmit"
      />

    </VCol>
  </VRow>
</template>
