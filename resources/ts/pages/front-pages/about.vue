<script setup lang="ts">
import Navbar from '@/views/front-pages/front-page-navbar.vue'
import { useConfigStore } from '@core/stores/config'

const store = useConfigStore()

store.skin = 'default'

definePage({
  meta: {
    layout: 'blank',
    public: true,
  },
})

</script>

<template>
  <div class="about-page">
    <Navbar />

    <VCard class="about-card full-height-card">
      <VContainer class="d-flex justify-center align-center fill-height">
        <AppAbout md="4" />
      </VContainer>
    </VCard>
  </div>
</template>

<style lang="scss" scoped>
.about-card {
  padding-block-start: 0 !important; 
  height: 100vh !important;
}

.full-height-card {
  min-height: 100vh;
}

.fill-height {
  height: 100%;
}
</style>

<style lang="scss">
.about-page {
  @media (min-width: 600px) and (max-width: 960px) {
    .v-container {
      padding-inline: 2rem !important;
    }
  }
}
</style>
