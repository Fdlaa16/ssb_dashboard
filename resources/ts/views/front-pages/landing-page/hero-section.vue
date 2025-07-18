<script setup lang="ts">
import { useGenerateImageVariant } from '@/@core/composable/useGenerateImageVariant'
import heroDashboardImgDark from '@images/front-pages/landing-page/hero-dashboard-dark.png'
import heroDashboardImgLight from '@images/front-pages/landing-page/hero-dashboard-light.png'
import { useMouse } from '@vueuse/core'
import { useTheme } from 'vuetify'

import heroElementsImgDark from '@images/front-pages/landing-page/hero-elements-dark.png'
import heroElementsImgLight from '@images/front-pages/landing-page/hero-elements-light.png'

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

  // Provide a default return value when `window` is undefined
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

const colors = [
  'indigo',
  'warning',
  'pink darken-2',
  'red lighten-1',
  'deep-purple accent-4',
]
const slides = [
  'First',
  'Second',
  'Third',
  'Fourth',
  'Fifth',
]

const slideImages = computed(() => {
  return detail.value.flatMap(item => item.slide_home || [])
})
</script>

<template>
  <div
    id="home"
    :style="{ background: 'rgb(var(--v-theme-surface))' }"
  >
    <v-carousel
      height="800"
      show-arrows="hover"
      cycle
      hide-delimiter-background
    >
      <v-carousel-item
        v-for="(img, i) in slideImages"
        :key="i"
      >
        <v-sheet
          class="d-flex justify-center align-center"
          color="transparent"
          height="100%"
        >
          <v-img
            :src="img.url"
            height="100%"
            width="100%"
            max-height="100%"
            max-width="1000px"
            style="object-fit: contain"
            class="rounded-lg"
            contain
          />
        </v-sheet>
      </v-carousel-item>
    </v-carousel>
  </div>
</template>

<style lang="scss" scoped>
.landing-hero {
  border-radius: 0 0 50px 50px;
  padding-block: 9.75rem 22rem;
}

.hero-animation-img {
  inline-size: 90%;
  inset-block-start: -25rem;
  inset-inline-start: 4.425rem;
  margin-inline: auto;
}

section {
  display: block;
}

.blank-section {
  background-color: rgba(var(--v-theme-surface));
  min-block-size: 25rem;
}

@media (min-width: 1280px) and (max-width: 1440px) {
  .blank-section {
    min-block-size: 18rem;
  }

  .landing-hero {
    padding-block-end: 22rem;
  }

  .hero-animation-img {
    inset-block-start: -25rem;
  }
}

@media (min-width: 900px) and (max-width: 1279px) {
  .blank-section {
    min-block-size: 13rem;
  }

  .landing-hero {
    padding-block-end: 14rem;
  }

  .hero-animation-img {
    inset-block-start: -17rem;
    inset-inline-start: 2.75rem;
  }
}

@media (min-width: 768px) and (max-width: 899px) {
  .blank-section {
    min-block-size: 12rem;
  }

  .landing-hero {
    padding-block-end: 12rem;
  }

  .hero-animation-img {
    inset-block-start: -15rem;
    inset-inline-start: 2.5rem;
  }
}

@media (min-width: 600px) and (max-width: 767px) {
  .blank-section {
    min-block-size: 12rem;
  }

  .landing-hero {
    padding-block-end: 8rem;
  }

  .hero-animation-img {
    inset-block-start: -11rem;
    inset-inline-start: 2rem;
  }
}

@media (min-width: 425px) and (max-width: 600px) {
  .blank-section {
    min-block-size: 8rem;
  }

  .landing-hero {
    padding-block-end: 8rem;
  }

  .hero-animation-img {
    inset-block-start: -9rem;
    inset-inline-start: 1.7rem;
  }
}

@media (min-width: 300px) and (max-width: 424px) {
  .blank-section {
    min-block-size: 4rem;
  }

  .landing-hero {
    padding-block-end: 6rem;
  }

  .hero-animation-img {
    inset-block-start: -7rem;
    inset-inline-start: 1.25rem;
  }
}

.landing-hero::before {
  position: absolute;
  background-repeat: no-repeat;
  inset-block: 0;
  opacity: 0.5;
}

.landing-hero-dark-bg {
  background-color: #25293c;
  background-image: url("@images/front-pages/backgrounds/hero-bg.png");
  background-position: center;
  background-repeat: no-repeat;
  background-size: cover;
}

.landing-hero-light-bg {
  background: url("@images/front-pages/backgrounds/hero-bg.png") center no-repeat, linear-gradient(138.18deg, #eae8fd 0%, #fce5e6 94.44%);
  background-size: cover;
}

@media (min-width: 650px) {
  .hero-text-box {
    inline-size: 38rem;
    margin-block-end: 1rem;
    margin-inline: auto;
  }
}

@media (max-width: 599px) {
  .hero-title {
    font-size: 1.5rem !important;
    line-height: 2.375rem !important;
  }
}

.hero-title {
  animation: shine 2s ease-in-out infinite alternate;
  background: linear-gradient(135deg, #28c76f 0%, #5a4aff 47.92%, #ff3739 100%);
  //  stylelint-disable-next-line property-no-vendor-prefix
  -webkit-background-clip: text;
  background-clip: text;
  background-size: 200% auto;
  font-size: 42px;
  font-weight: 800;
  line-height: 48px;
  -webkit-text-fill-color: rgba(0, 0, 0, 0%);
}

@keyframes shine {
  0% {
    background-position: 0% 50%;
  }

  80% {
    background-position: 50% 90%;
  }

  100% {
    background-position: 91% 100%;
  }
}

.hero-dashboard-img {
  margin-block: 0;
  margin-inline: auto;
  transform-style: preserve-3d;
  transition: all 0.35s;

  img {
    inline-size: 100%;
  }
}

.hero-elements-img {
  position: absolute;
  inset-block: 0;
  inset-inline-start: 0;
}

.feature-cards {
  margin-block-start: 6.25rem;
}

.hero-btn-item {
  inset-block-start: 80%;
  inset-inline-start: 5%;
}
</style>
