<script lang="ts" setup>
import MediaEditable from '@/views/dashboards/media/MediaEditable.vue';
import type { MediaData, Sport } from '@/views/dashboards/media/types';
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter() 
const selectedSports = ref<Sport[]>([])
const isFlatSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref<'success' | 'error'>('success')

const mediaData = ref<MediaData>({
  id: 0,
  code: '',
  name: '',
  title: '',
  hashtag: '',
  description: '',
  link: '',
  start_date: '',
  end_date: '',
  document_media: [], 
})

const handleSubmit = async () => {
  const formData = new FormData();
  formData.append('title', mediaData.value.title);
  formData.append('name', mediaData.value.name);
  formData.append('hashtag', mediaData.value.hashtag);
  formData.append('description', mediaData.value.description);
  formData.append('link', mediaData.value.link);
  formData.append('start_date', mediaData.value.start_date);
  formData.append('end_date', mediaData.value.end_date);

  if (mediaData.value.document_media && Array.isArray(mediaData.value.document_media)) {
    mediaData.value.document_media.forEach((file, index) => {
      if (file instanceof File) {
        formData.append(`document_media[${index}]`, file);
      }
    });
  }

  try {
    const response = await $api('media/store', {
      method: 'POST',
      body: formData,
    });

    snackbarMessage.value = 'Data berhasil dibuat!';
    snackbarColor.value = 'success';
    isFlatSnackbarVisible.value = true;

    router.push({
      name: 'dashboards-media-list',
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
    <MediaEditable
      :data="mediaData"
      @update:data="mediaData = $event"
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
