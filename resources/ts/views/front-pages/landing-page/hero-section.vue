<script setup lang="ts">
import { useGenerateImageVariant } from '@/@core/composable/useGenerateImageVariant'
import heroDashboardImgDark from '@images/front-pages/landing-page/hero-dashboard-dark.png'
import heroDashboardImgLight from '@images/front-pages/landing-page/hero-dashboard-light.png'
import heroElementsImgDark from '@images/front-pages/landing-page/hero-elements-dark.png'
import heroElementsImgLight from '@images/front-pages/landing-page/hero-elements-light.png'
import { useMouse } from '@vueuse/core'
import { useTheme } from 'vuetify'

const detail = ref<any>([])
const loading = ref(true)
const error = ref<string | null>(null)
const theme = useTheme()
const heroElementsImg = useGenerateImageVariant(heroElementsImgLight, heroElementsImgDark)
const heroDashboardImg = useGenerateImageVariant(heroDashboardImgLight, heroDashboardImgDark)
const { x, y } = useMouse({ touch: false })

const translateMouse = computed(() => {
  if (typeof window !== 'undefined') {
    const rotateX = ref((window.innerHeight - (1 * y.value)) / 100)
    return { transform: `perspective(1200px) rotateX(${rotateX.value < -40 ? -20 : rotateX.value}deg) rotateY(${(window.innerWidth - (2 * x.value)) / 100}deg) scale3d(1,1,1)` }
  }
  return { transform: 'perspective(1200px) rotateX(0deg) rotateY(0deg) scale3d(1,1,1)' }
})

const fetchSlideHome = async () => {
  try {
    const response = await $api(`company/slide_home`, {
      method: 'GET'
    })
    detail.value = response.data
  } catch (err: any) {
    error.value = err.message || 'Gagal memuat slide home'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchSlideHome()
})

const slideImages = computed(() => {
  return detail.value.flatMap(item => item.slide_home || [])
})
</script>

<template>
  <div id="home">
    <v-carousel
      height="calc(100vh - 85px)"
      show-arrows="hover"
      cycle
      hide-delimiter-background
      class="carousel-container"
    >
      <v-carousel-item
        v-for="(img, i) in slideImages"
        :key="i"
      >
        <v-sheet class="carousel-image-sheet">
          <v-img
            :src="img.url"
            class="carousel-full-image"
            contain
          />
        </v-sheet>
      </v-carousel-item>
    </v-carousel>
  </div>
</template>

<style scoped lang="scss">
// Menghilangkan margin dari body, sebaiknya taruh di global css kalau bisa
:global(body) {
  margin: 0;
  padding: 0;
}

#home {
  margin: 0;
  padding: 0;
  overflow-x: hidden;
  margin-top: 85px; // Pindahkan margin-top ke container utama
}

.carousel-container {
  width: 100vw;
  position: relative;
}

.carousel-image-sheet {
  padding: 0;
margin: 0;
width: 100vw; // Memenuhi seluruh lebar layar
height: 100%;
position: relative;
}

.carousel-full-image {
  width: 100%;
  height: 100%;
  margin-top: 10px;
  border-radius: 0 !important;
  
  // Untuk memastikan gambar tidak terpotong
  :deep(.v-img__img) {
    object-fit: contain !important;
    object-position: center !important;
  }
}

// Media queries untuk responsivitas
@media (max-width: 960px) {
  #home {
    margin-top: 85px; // Sesuaikan dengan tinggi navbar mobile jika berbeda
  }
}

@media (max-width: 600px) {
  .carousel-container {
    height: calc(100vh - 85px);
  }
}
</style>
