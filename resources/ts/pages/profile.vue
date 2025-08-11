<script setup lang="ts">
import Footer from '@/views/front-pages/front-page-footer.vue'
import Navbar from '@/views/front-pages/front-page-navbar.vue'
import { useConfigStore } from '@core/stores/config'
import type { CustomInputContent } from '@core/types'
import paypalDark from '@images/icons/payments/img/paypal-dark.png'
import paypalLight from '@images/icons/payments/img/paypal-light.png'
import visaDark from '@images/icons/payments/img/visa-dark.png'
import visaLight from '@images/icons/payments/img/visa-light.png'

const visa = useGenerateImageVariant(visaLight, visaDark)
const paypal = useGenerateImageVariant(paypalLight, paypalDark)
const store = useConfigStore()
store.skin = 'default'
definePage({
  meta: {
    layout: 'blank',
    public: false,
  },
})

const radioContent: CustomInputContent[] = [
  {
    title: 'Credit Card',
    value: 'credit card',
    images: visa.value,
  },
  {
    title: 'PayPal',
    value: 'paypal',
    images: paypal.value,
  },
]

const selectedRadio = ref('credit card')
const selectedCountry = ref('USA')
const isPricingPlanDialogVisible = ref(false)
</script>

<template>
  <!-- eslint-disable vue/attribute-hyphenation -->
  <div class="payment-page">
    <!-- 👉 Navbar -->
    <Navbar />
    
    <!-- 👉 Main content wrapper -->
    <div class="main-content">
      <!-- 👉 Payment card -->
      <VContainer>
        <div class="d-flex justify-center align-center">
          <!-- <VCard width="100%"> -->
          <AppProfile />
          <!-- </VCard> -->
        </div>
      </VContainer>
    </div>
    
    <!-- 👉 Footer -->
    <Footer />
    
    <PricingPlanDialog v-model:is-dialog-visible="isPricingPlanDialogVisible" />
  </div>
</template>

<style lang="scss" scoped>
.payment-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  
  .main-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-height: calc(100vh - 64px - 100px); 
  }
  
  :deep(.footer) {
    margin-top: auto;
  }
}

.payment-card {
  margin-block: 10.5rem 5.25rem;
}

@media (min-width: 600px) and (max-width: 960px) {
  .payment-page {
    .v-container {
      padding-inline: 2rem !important;
    }
  }
}
</style>

<style lang="scss">
.payment-card {
  .custom-radio {
    .v-radio {
      margin-block-start: 0 !important;
    }
  }
}
</style>
