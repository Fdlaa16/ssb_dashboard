<script setup lang="ts">
import Footer from '@/views/front-pages/front-page-footer.vue'
import Navbar from '@/views/front-pages/front-page-navbar.vue'
import CustomersReview from '@/views/front-pages/landing-page/customers-review.vue'
import HeroSection from '@/views/front-pages/landing-page/hero-section.vue'
import OurTeam from '@/views/front-pages/landing-page/our-team.vue'
import { useConfigStore } from '@core/stores/config'

const store = useConfigStore()

store.skin = 'default'
definePage({
  meta: {
    layout: 'blank',
    public: true,
  },
})

const activeSectionId = ref()

const refHome = ref()
const refFeatures = ref()
const refTeam = ref()
const refContact = ref()
const refFaq = ref()

useIntersectionObserver(
  [refHome, refFeatures, refTeam, refContact, refFaq],
  ([{ isIntersecting, target }]) => {
    if (isIntersecting)
      activeSectionId.value = target.id
  },
  {
    threshold: 0.25,
  },
)
</script>

<template>
  <div class="landing-page-wrapper">
    <Navbar :active-id="activeSectionId" />

    <!-- 👉 Hero Section  -->
    <HeroSection ref="refHome" />

    <!-- 👉 Our Team -->
    <div :style="{ 'background-color': 'rgb(var(--v-theme-surface))' }">
      <OurTeam ref="refTeam" />
    </div>

    <!-- 👉 Useful features  -->
    <!-- <div :style="{ 'background-color': 'rgb(var(--v-theme-surface))' }">
      <Features ref="refFeatures" />
    </div> -->

    <!-- 👉 Customer Review -->
    <div :style="{ 'background-color': 'rgb(var(--v-theme-surface))' }">
      <CustomersReview />
    </div>

    <!-- 👉 Pricing Plans -->
    <!-- <div :style="{ 'background-color': 'rgb(var(--v-theme-surface))' }">
      <PricingPlans />
    </div> -->

    <!-- 👉 Product stats -->
    <!-- <ProductStats /> -->

    <!-- 👉 FAQ Section -->
    <!-- <div :style="{ 'background-color': 'rgb(var(--v-theme-surface))' }">
      <FaqSection ref="refFaq" />
    </div> -->

    <!-- 👉 Banner  -->
    <!-- <Banner /> -->

    <!-- 👉 Contact Us  -->
    <!-- <ContactUs ref="refContact" /> -->

    <!-- 👉 Footer -->
    <Footer />
  </div>
</template>

<style lang="scss">
@media (max-width: 960px) and (min-width: 600px) {
  .landing-page-wrapper {
    .v-container {
      padding-inline: 2rem !important;
    }
  }
}
</style>
