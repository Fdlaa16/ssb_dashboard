<script lang="ts" setup>
import StructureEditable from '@/views/dashboards/structure/StructureEditable.vue';
import type { Sport, StructureData } from '@/views/dashboards/structure/types';
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter() 
const selectedSports = ref<Sport[]>([])
const isFlatSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref<'success' | 'error'>('success')

const structureData = ref<StructureData>({
  id: 0,
  name: '',
  user: {
    id: 0,
    email: '',
    password: '',
  },
  date_of_birth: '',
  department: '',
  avatar: null,
})

const handleSubmit = async () => {

  console.log('structureData', structureData.value);
  
  const formData = new FormData();
  formData.append('email', structureData.value.user.email ?? '');
  formData.append('name', structureData.value.name ?? '');
  formData.append('date_of_birth', structureData.value.date_of_birth ?? '');
  formData.append('department', structureData.value.department ?? '');

  if (structureData.value.avatar instanceof File)
    formData.append('avatar', structureData.value.avatar);

  try {
    const response = await $api('structure/store', {
      method: 'POST',
      body: formData,
    });

    snackbarMessage.value = 'Data berhasil dibuat!';
    snackbarColor.value = 'success';
    isFlatSnackbarVisible.value = true;

    router.push({
      name: 'dashboards-structure-list',
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
    <StructureEditable
      :data="structureData"
      @update:data="structureData = $event"
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
