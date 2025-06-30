<script lang="ts" setup>
import ClubEditable from '@/views/dashboards/club/ClubEditable.vue';
import type { ClubData } from '@/views/dashboards/club/types';
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter() 
const isFlatSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref<'success' | 'error'>('success')

const clubData = ref<ClubData>({
  id: 0,
  code: '',
  name: '',
  profile_club: null,
})

const handleSubmit = async () => {  
  const formData = new FormData();
  formData.append('name', clubData.value.name);
  
  if (clubData.value.profile_club instanceof File)
    formData.append('profile_club', clubData.value.profile_club);

  try {
    const response = await $api('club/store', {
      method: 'POST',
      body: formData,
    });

    snackbarMessage.value = 'Data berhasil dibuat!';
    snackbarColor.value = 'success';
    isFlatSnackbarVisible.value = true;

    router.push({
      name: 'dashboards-club-list',
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
    <ClubEditable
      :data="clubData"
      @update:data="clubData = $event"
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
