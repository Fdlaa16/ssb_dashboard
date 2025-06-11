<script lang="ts" setup>
import PlayerEditable from '@/views/dashboards/player/PlayerEditable.vue';
import type { PlayerData, Sport } from '@/views/dashboards/player/types';
import { ref } from 'vue';
import { useRouter } from 'vue-router'; // ✅ import router

const router = useRouter() // ✅ inisialisasi router

const sports = ref<Sport[]>([])
const error = ref<string | null>(null)
const loading = ref(false)
const selectedSports = ref<Sport[]>([])
const isFlatSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref<'success' | 'error'>('success')

const playerData = ref<PlayerData>({
  id: 0,
  name: '',
  email: '',
  password: '',
  nisn: '',
  height: '',
  weight: '',
  sport_players: [
    {
      id: 0,
      sport: {
        id: 0,
        code: '',
        name: '',
      },
    },
  ],
})

const handleSubmit = async () => {
  console.log('selectedSports:', selectedSports.value);
  
  playerData.value.sport_players = selectedSports.value.map(sport => ({
    id: 0,
    sport: {
      id: sport.id,
      code: sport.code,
      name: sport.name,
    },
  }))

  const formData = new FormData()
  formData.append('email', playerData.value.email)
  formData.append('password', playerData.value.password || '')
  formData.append('nisn', playerData.value.nisn)
  formData.append('name', playerData.value.name)
  formData.append('height', playerData.value.height)
  formData.append('weight', playerData.value.weight)

  if (playerData.value.avatar instanceof File)
  formData.append('avatar', playerData.value.avatar)

  if (playerData.value.family_card instanceof File)
  formData.append('family_card', playerData.value.family_card)

  if (playerData.value.report_grades instanceof File)
  formData.append('report_grades', playerData.value.report_grades)

  if (playerData.value.birth_certificate instanceof File)
  formData.append('birth_certificate', playerData.value.birth_certificate)

  
  formData.append('sport_players', JSON.stringify(playerData.value.sport_players))

  try {
    const response = await $api('player/store', {
      method: 'POST',
      body: formData,
    })

    snackbarMessage.value = 'Data berhasil dibuat!'
    snackbarColor.value = 'success'
    isFlatSnackbarVisible.value = true

    router.push({
      name: 'dashboards-player-list',
      query: {
        success: 'Data berhasil dibuat!',
      },
    })

    console.log(response)
  } catch (err: any) {
    snackbarMessage.value = 'Gagal mengirim data: ' + (err?.message || 'Unknown error')
    snackbarColor.value = 'error'
    isFlatSnackbarVisible.value = true
  }
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
        :sports="sports"
        @submit="handleSubmit"
        @update:selectedSports="selectedSports = $event"
        @update:data="playerData = $event"
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
