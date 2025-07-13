<script lang="ts" setup>
import SlideHomeEditable from '@/views/dashboards/slide_home/SlideHomeEditable.vue';
import type { SlideHomeData } from '@/views/dashboards/slide_home/types';
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter() 
const isFlatSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref<'success' | 'error'>('success')

const slideHomeData = ref<SlideHomeData>({
  id: 0,
  code: '',
  slide_home: null,
})

const handleSubmit = async () => {  
  const formData = new FormData();
  
  if (slideHomeData.value.slide_home instanceof File)
    formData.append('slide_home', slideHomeData.value.slide_home);

  try {
    const response = await $api('slide_home/store', {
      method: 'POST',
      body: formData,
    });

    snackbarMessage.value = 'Data berhasil dibuat!';
    snackbarColor.value = 'success';
    isFlatSnackbarVisible.value = true;

    router.push({
      name: 'dashboards-slide-home-list',
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
    <SlideHomeEditable
      :data="slideHomeData"
      @update:data="slideHomeData = $event"
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
