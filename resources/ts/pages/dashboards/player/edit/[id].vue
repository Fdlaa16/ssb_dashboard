<script lang="ts" setup>
import PlayerEditable from '@/views/dashboards/player/PlayerEditable.vue';
import type { PlayerData, Sport } from '@/views/dashboards/player/types';
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();

const playerId = route.params.id as string;

const playerData = ref<PlayerData>({
  id: 0,
  name: '',
  email: '',
  password: '',
  nisn: '',
  height: '',
  weight: '',
  sport_players: [],
  avatar: null,
  family_card: null,
  report_grades: null,
  birth_certificate: null,
});

const sports = ref<Sport[]>([]);

const loading = ref(false);
const error = ref<string | null>(null);

// Ambil data player berdasarkan ID dari API
const fetchPlayer = async () => {
  loading.value = true;
  try {
    const res = await $api(`player/${playerId}/edit`); // contoh endpoint get player by id
    playerData.value = res.data;
  } catch (err: any) {
    error.value = err.message || 'Gagal mengambil data player';
  } finally {
    loading.value = false;
  }
};

const fetchSports = async () => {
  try {
    const res = await $api('sport');
    sports.value = res.data;
  } catch {
    // tangani error fetch sports
  }
};

onMounted(async () => {
  await fetchSports();
  await fetchPlayer();
});

// Submit handler (edit update)
const handleSubmit = async () => {
  try {
    const formData = new FormData();
    formData.append('email', playerData.value.email);
    // password kosong berarti tidak update password
    if (playerData.value.password) {
      formData.append('password', playerData.value.password);
    }
    formData.append('nisn', playerData.value.nisn);
    formData.append('name', playerData.value.name);
    formData.append('height', playerData.value.height);
    formData.append('weight', playerData.value.weight);
    formData.append('sport_players', JSON.stringify(playerData.value.sport_players));
    // file uploads
    formData.append('avatar', playerData.value.avatar || '');
    formData.append('family_card', playerData.value.family_card || '');
    formData.append('report_grades', playerData.value.report_grades || '');
    formData.append('birth_certificate', playerData.value.birth_certificate || '');

    const res = await $api(`player/${playerId}`, {
      method: 'POST', // atau PUT tergantung API
      body: formData,
    });

    alert('Data berhasil diperbarui!');
    router.push({ name: 'dashboards-player-list' });
  } catch (err: any) {
    alert('Gagal update data: ' + (err.message || 'Unknown error'));
  }
};
</script>

<template>
  <VRow>
    <VCol cols="12" md="12">
      <PlayerEditable
        :data="readonly(playerData)"  
        :sports="sports"
        @submit="handleSubmit"
        @update:selectedSports="(val) => {
          playerData.value.sport_players = val.map(id => {
            const s = sports.value.find(x => x.id === id);
            return s ? { id: 0, sport: s } : null;
          }).filter(x => x !== null);
        }"
      />

    </VCol>
  </VRow>
</template>
