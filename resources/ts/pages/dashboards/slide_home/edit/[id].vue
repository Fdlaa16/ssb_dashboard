<script lang="ts" setup>
import SlideHomeEditable from '@/views/dashboards/slide_home/SlideHomeEditable.vue';
import type { SlideHomeData } from '@/views/dashboards/slide_home/types';
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();

const slideHomeId = route.params.id as string;
const error = ref<string | null>(null)
const loading = ref(false)
const isFlatSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref<'success' | 'error'>('success')

const slideHome = ref<SlideHomeData>({
  id: 0,
  code: '',
  slide_home: null,
});

const fetchSlideHome = async () => {
  loading.value = true;
  try {
    const res = await $api(`slide_home/${slideHomeId}/edit`);

    slideHome.value = res.data
    
  } catch (err: any) {
    error.value = err.message || 'Gagal mengambil data slide home';
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  await fetchSlideHome();
});

const handleSubmit = async () => {
  try {        
    const formData = new FormData();
    formData.append('_method', 'PUT'); 

    if (slideHome.value.slide_home instanceof File)
      formData.append('slide_home', slideHome.value.slide_home);

    const res = await $api(`slide_home/${slideHomeId}`, {
      method: 'POST',
      body: formData,
    });

    snackbarMessage.value = 'Data berhasil diperbarui!';
    snackbarColor.value = 'success';
    isFlatSnackbarVisible.value = true;

    router.push({
      name: 'dashboards-slide-home-list',
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
      <SlideHomeEditable
        :data="slideHome"  
        @update:data="slideHome = $event"
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
