<script lang="ts" setup>
import PlayerEditable from '@/views/dashboards/player/PlayerEditable.vue';
import type { PlayerData, Sport } from '@/views/dashboards/player/types';
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter() 
const selectedSports = ref<Sport[]>([])
const isFlatSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref<'success' | 'error'>('success')

const playerData = ref<PlayerData>({
  id: 0,
  name: '',
  user: {
    email: '',
    password: '',
  },
  nisn: '',
  height: '',
  weight: '',
  club: {
    id: 0,
    code: '',
    name: '',
  },
  sport_players: [],
  club_player: {
    club_id: '',
    player_id: 0,
    back_number: '',   
    position: '',
    is_captain: false,   
    status: false,
  },
})

const handleSubmit = async () => {
  playerData.value.sport_players = selectedSports.value.map(sport => ({
    id: 0,
    player_id: '',
    sport_id: sport.id,
  }));

  const formData = new FormData();
  formData.append('email', playerData.value.user.email);
  formData.append('nisn', playerData.value.nisn);
  formData.append('name', playerData.value.name);
  formData.append('height', playerData.value.height);
  formData.append('weight', playerData.value.weight);
  formData.append('club_id', playerData.value.club_player.club_id.toString())

  if (playerData.value.avatar instanceof File)
    formData.append('avatar', playerData.value.avatar);

  if (playerData.value.family_card instanceof File)
    formData.append('family_card', playerData.value.family_card);

  if (playerData.value.report_grades instanceof File)
    formData.append('report_grades', playerData.value.report_grades);

  if (playerData.value.birth_certificate instanceof File)
    formData.append('birth_certificate', playerData.value.birth_certificate);

  try {
    const response = await $api('player/store', {
      method: 'POST',
      body: formData,
    });

    snackbarMessage.value = 'Data berhasil dibuat!';
    snackbarColor.value = 'success';
    isFlatSnackbarVisible.value = true;

    router.push({
      name: 'dashboards-sport-list',
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
    <PlayerEditable
      :data="playerData"
      @update:data="playerData = $event"
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
