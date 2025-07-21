<script setup lang="ts">
import authV1BottomShape from '@images/svg/auth-v1-bottom-shape.svg?raw'
import authV1TopShape from '@images/svg/auth-v1-top-shape.svg?raw'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'

interface Pricing {
 title?: string
 xs?: number | string
 sm?: number | string
 md?: string | number
 lg?: string | number
 xl?: string | number
}
</script>

<template>
  <div class="d-flex align-center justify-center">
    <div class="position-relative my-sm-16">
      <VNodeRenderer
        :nodes="h('div', { innerHTML: authV1TopShape })"
        class="text-primary auth-v1-top-shape d-none d-sm-block"
      />
      <VNodeRenderer
      :nodes="h('div', { innerHTML: authV1BottomShape })"
      class="text-primary auth-v1-bottom-shape d-none d-sm-block"
    />
      <VCard
        class="auth-card"
        :class="$vuetify.display.smAndUp ? 'pa-6' : 'pa-0'"
      >
        <div class="text-center mb-6">
          <h3 class="text-h3 pricing-title">
          Sejarah
          </h3>
        </div>

        <!-- Grid System untuk layout 2 kolom -->
        <VContainer fluid>
          <VRow>
            <!-- Kolom untuk teks sejarah (col-8) -->
            <VCol cols="12" md="8">
              <div class="text-left">
                SSB Putra Muda Balaraja didirikan pada 4 Oktober 2020 sebagai wadah pembinaan sepak bola usia dini hingga remaja di wilayah Balaraja, Kabupaten Tangerang. Berbasis di Stadion Mini Balaraja, sekolah sepak bola ini rutin mengadakan latihan setiap hari Rabu dan Minggu. Dengan dukungan tim pelatih yang berpengalaman, SSB Putra Muda aktif mengikuti berbagai turnamen lokal dan nasional, serta berkomitmen membentuk pemain yang tidak hanya unggul secara teknik, tetapi juga berkarakter dan bermental juara.
              </div>
            </VCol>

            <!-- Kolom untuk gambar (col-4) -->
            <VCol cols="12" md="4">
              <div class="d-flex flex-column align-center">
                <!-- Gambar utama -->
                <img
                  src="/storage/logo/LOGOSSB.png"
                  alt="Logo SSB"
                  class="app-logo-img"
                  style="height: 300px;"
                />
              </div>
            </VCol>
          </VRow>
        </VContainer>

      </VCard>
    </div>
  </div>
</template>

<style lang="scss" scoped>
@use "../../styles/@core/template/pages/page-auth";

// Responsive adjustments
@media (max-width: 960px) {
  .auth-card {
    .v-col {
      text-align: center !important;
    }
  }
}
</style>
