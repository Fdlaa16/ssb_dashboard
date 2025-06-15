<script lang="ts" setup>
import PlayerEditable from '@/views/dashboards/player/PlayerEditable.vue';
import type { PlayerData } from '@/views/dashboards/player/types';
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();

const playerId = route.params.id as string;
const error = ref<string | null>(null)
const loading = ref(false)
const isFlatSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref<'success' | 'error'>('success')

const playerData = ref<PlayerData>({
  id: 0,
  name: '',
  user: {
    id: 0,
    email: '',
    password: '',
  },
  nisn: '',
  height: '',
  weight: '',
  sport_players: [],
  club: {
    id: 0,
    code: '',
    name: '',
  },
  club_player: {
    club_id: 0,
    player_id: 0,
    back_number: '',   
    position: '',
    is_captain: false,   
    status: false,
  },
  avatar: null,
  family_card: null,
  report_grades: null,
  birth_certificate: null,
});


const fetchPlayer = async () => {
  loading.value = true;
  try {
    const res = await $api(`player/${playerId}/edit`);
    const data = res.data

    const clubPlayer = data.club_players?.[0]
    
    data.club_player = clubPlayer || {
      club_id: 0,
      player_id: 0,
      back_number: '',   
      position: '',
      is_captain: false,   
      status: false,
    }

    playerData.value = data 

    console.log('Fetched player data:', playerData.value);
    
  } catch (err: any) {
    error.value = err.message || 'Gagal mengambil data player';
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  await fetchPlayer();
});

const handleSubmit = async () => {
  try {    

    console.log('Submitting player data:', playerData.value);
    
    const formData = new FormData();
    formData.append('_method', 'PUT'); 

    formData.append('email', playerData.value.user.email);
    formData.append('nisn', playerData.value.nisn);
    formData.append('name', playerData.value.name);
    formData.append('height', playerData.value.height);
    formData.append('weight', playerData.value.weight);
    formData.append('club_id', playerData.value.club_player.club_id.toString())

    if (typeof playerData.value.user.id === 'number') {
      formData.append('user_id', playerData.value.user.id.toString());
    }

    formData.append('sport_players', JSON.stringify(playerData.value.sport_players));

    if (playerData.value.avatar instanceof File)
      formData.append('avatar', playerData.value.avatar);

    if (playerData.value.family_card instanceof File)
      formData.append('family_card', playerData.value.family_card);

    if (playerData.value.report_grades instanceof File)
      formData.append('report_grades', playerData.value.report_grades);

    if (playerData.value.birth_certificate instanceof File)
      formData.append('birth_certificate', playerData.value.birth_certificate);

    const res = await $api(`player/${playerId}`, {
      method: 'POST',
      body: formData,
    });

    snackbarMessage.value = 'Data berhasil dibuat!';
    snackbarColor.value = 'success';
    isFlatSnackbarVisible.value = true;

    router.push({
      name: 'dashboards-player-list',
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
      <PlayerEditable
        :data="playerData"  
        @submit="handleSubmit"
      />

    </VCol>
  </VRow>
</template>
