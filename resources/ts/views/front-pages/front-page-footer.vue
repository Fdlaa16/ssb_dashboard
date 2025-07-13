<script setup lang="ts">

import footerDarkBg from '@images/front-pages/backgrounds/footer-bg-dark.png'
import footerLightBg from '@images/front-pages/backgrounds/footer-bg-light.png'


interface Menu {
  name: string
  to: any
  isNew?: boolean
}

const footerBg = useGenerateImageVariant(footerLightBg, footerDarkBg)

const pagesList: Menu[] = [
  { name: 'History', to: { name: 'front-pages-history' } },
  { name: 'About', to: { name: 'front-pages-about' } },
  { name: 'Player', to: { name: 'front-pages-player' } },
  { name: 'Club', to: { name: 'front-pages-club' } },
  { name: 'Schedule Match', to: { name: 'front-pages-schedule-match' } },
  { name: 'Schedule Training', to: { name: 'front-pages-schedule-training' } },
  { name: 'Standing', to: { name: 'front-pages-standing' } },
  { name: 'Media', to: { name: 'front-pages-media' } },
  { name: 'Pricing', to: { name: 'front-pages-pricing' } },
  { name: 'Payment', to: { name: 'front-pages-payment' }, isNew: true },
  { name: 'Checkout', to: { name: 'front-pages-checkout' } },
  { name: 'Help Center', to: { name: 'front-pages-help-center' } },
  { name: 'Login/Register', to: { name: 'pages-authentication-login-v2' } },
]

const demoList = [
  { title: 'Vertical Layout', to: 'https://demos.pixinvent.com/vuexy-vuejs-laravel-admin-template/demo-1/dashboards/analytics' },
  { title: 'Horizontal Layout', to: 'https://demos.pixinvent.com/vuexy-vuejs-admin-template/demo-5/dashboards/analytics' },
  { title: 'Bordered Layout', to: 'https://demos.pixinvent.com/vuexy-vuejs-admin-template/demo-2/dashboards/analytics' },
  { title: 'Semi Dark Layout', to: 'https://demos.pixinvent.com/vuexy-vuejs-admin-template/demo-3/dashboards/analytics' },
  { title: 'Dark Layout', to: 'https://demos.pixinvent.com/vuexy-vuejs-admin-template/demo-4/dashboards/analytics' },
]
</script>

<template>
  <div class="footer">
    <div
      class="footer-top pt-11"
      :style="{ 'background-image': `url(${footerBg})` }"
    >
      <VContainer>
        <VRow>
          <!-- 👉 Footer  -->
          <VCol 
            md="6"
            sm="3"
            xs="6"
          >
            <div
              class="mb-4"
              :class="$vuetify.display.smAndDown ? 'w-100' : 'w-75'"
            >
              <div class="app-logo mb-6 d-flex align-center gap-3">
                <img
                  src="/storage/logo/LOGOSSB.png"
                  alt="Logo SSB"
                  class="app-logo-img"
                  style="height: 40px;"
                />
                <h3 class="app-logo-title text-white mb-0">
                  PUTRA MUDA BALARAJA
                </h3>
              </div>

              <div
                class="mb-6"
                :class="$vuetify.theme.current.dark ? 'text-body-2' : 'text-white-variant'"
              >
                <p class="mb-3">
                  <strong>Address:</strong> Jl. Cipete No. 06, Cipete Selatan, Cilandak, Jakarta Selatan - DKI Jakarta - 12410
                </p>
                <p class="mb-3">
                  <strong>Phone Number:</strong> 081234567890
                </p>
                <p class="mb-0">
                  <strong>Email:</strong> putramuda.balaraja@gmail.com
                </p>
              </div>
            </div>
          </VCol>
          
          <VCol 
            md="6"
            sm="3"
            xs="6"
          >
            <div
              class="mb-6"
              :class="$vuetify.theme.current.dark ? 'text-body-2' : 'text-white-variant'"
            >
              <div class="app-logo mb-9 d-flex align-center gap-3">
                <h3 class="app-logo-title text-white mb-0">
                  ABOUT US
                </h3>
              </div>

              <p class="mb-0">
                Indonesia Junior League merupakan operator kompetisi sepak bola usia dini 
                dengan level berjenjang mulai dari usia delapan tahun yang menancapkan 
                eksistensinya di Tanah Air sejak 17 Agustus 2014.
              </p>
            </div>
          </VCol>

        </VRow>
      </VContainer>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.footer-title {
  color: rgba(255, 255, 255, 92%);
}

.footer-top {
  // border-radius: 60px 60px 0 0;
  background-size: cover;
  color: #fff;
}

.footer-links {
  .text-white-variant,
  .text-body-1 {
    &:hover {
      color: #fff;
    }
  }
}

.footer-line {
  background: #282c3e;
}
</style>

<style lang="scss">
.subscribe-form {
  .v-label {
    color: rgba(225, 222, 245, 90%) !important;
  }

  .v-field {
    border-end-end-radius: 0;
    border-end-start-radius: 10px;
    border-start-end-radius: 0;
    border-start-start-radius: 10px;

    input.v-field__input::placeholder {
      color: rgba(225, 222, 245, 40%) !important;
    }

    input.v-field__input {
      color: rgba(255, 255, 255, 78%);
    }
  }
}

.footer {
  border-radius: 50%;

  @media (min-width: 600px) and (max-width: 960px) {
    .v-container {
      padding-inline: 2rem !important;
    }

    .footer-logo-buttons {
      gap: 0.75rem;
    }
  }
}
</style>
