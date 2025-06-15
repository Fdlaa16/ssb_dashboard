<script lang="ts" setup>
import MediaEditable from '@/views/dashboards/media/MediaEditable.vue';
import type { MediaData } from '@/views/dashboards/media/types';
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();

const mediaId = route.params.id as string;
const error = ref<string | null>(null)
const loading = ref(false)
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
  thumbnail: '',
  digital_platform: '',
  status: false,
});


const fetchMedia = async () => {
  loading.value = true;
  try {
    const res = await $api(`media/${mediaId}/edit`);
   
    mediaData.value = res.data 

    console.log('Fetched media data:', mediaData.value);
    
  } catch (err: any) {
    error.value = err.message || 'Gagal mengambil data media';
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  await fetchMedia();
});

const handleSubmit = async () => {
  try {    

    console.log('Submitting media data:', mediaData.value);
    
    const formData = new FormData();
    formData.append('_method', 'PUT'); 

    formData.append('name', mediaData.value.name);
    formData.append('title', mediaData.value.title);
    formData.append('hashtag', mediaData.value.hashtag);
    formData.append('description', mediaData.value.description);
    formData.append('link', mediaData.value.link);
    formData.append('status', mediaData.value.status);

    if (mediaData.value.document_media instanceof File)
      formData.append('document_media', mediaData.value.document_media);

    const res = await $api(`media/${mediaId}`, {
      method: 'POST',
      body: formData,
    });

    snackbarMessage.value = 'Data berhasil dibuat!';
    snackbarColor.value = 'success';
    isFlatSnackbarVisible.value = true;

    router.push({
      name: 'dashboards-media-list',
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

</script>

<template>
  <VRow>
    <VCol cols="12" md="12">
      <MediaEditable
        :data="mediaData"  
        @submit="handleSubmit"
      />

    </VCol>
  </VRow>
</template>
