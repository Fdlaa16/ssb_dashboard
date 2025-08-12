<script lang="ts" setup>
import StadiumEditable from '@/views/dashboards/stadium/StadiumEditable.vue';
import type { stadiumData } from '@/views/dashboards/stadium/types';
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();

const stadiumId = route.params.id as string;
const error = ref<string | null>(null)
const loading = ref(false)
const isFlatSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref<'success' | 'error'>('success')

const stadiumData = ref<StadiumData>({
  id: 0,
  code: '',
  name: '',
  area: '',
});


const fetchstadium = async () => {
  loading.value = true;
  try {
    const res = await $api(`stadium/${stadiumId}/edit`);
   
    stadiumData.value = res.data 
  } catch (err: any) {
    error.value = err.message || 'Gagal mengambil data stadium';
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  await fetchstadium();
});

const handleSubmit = async () => {
  try {        
    const formData = new FormData();
    formData.append('_method', 'PUT'); 

    formData.append('name', stadiumData.value.name);
    formData.append('area', stadiumData.value.area);

    const res = await $api(`stadium/${stadiumId}`, {
      method: 'POST',
      body: formData,
    });

    snackbarMessage.value = 'Data berhasil dibuat!';
    snackbarColor.value = 'success';
    isFlatSnackbarVisible.value = true;

    router.push({
      name: 'dashboards-stadium-list',
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
      <StadiumEditable
        :data="stadiumData"  
        @update:data="stadiumData = $event"
        @submit="onSubmit"
      />

    </VCol>
  </VRow>
</template>
