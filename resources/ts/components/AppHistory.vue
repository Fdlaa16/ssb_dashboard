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
                <p class="mb-4">
                  PERSIB adalah klub sepak bola Indonesia yang berbasis di Kota Bandung, Jawa Barat, dan saat ini merupakan peserta dari kompetisi tertinggi Indonesia, Liga 1.
                </p>
                <p class="mb-4">
                  Cikal bakal PERSIB adalah Bandoeng Inlandsche Voetbal Bond (BIVB) yang didirikan pada 05 Januari 1919. BIVB kala itu dibentuk sebagai perserikatan klub-klub sepakbola bumi putera pertama di Bandung.
                </p>
                <p class="mb-4">
                  Pada 19 April 1930, BIVB tercatat sebagai salah satu dari tujuh klub yang membidani lahirnya PSSI (Persatuan Sepakbola Seluruh Indonesia). Sebagai bagian dari alat perjuangan kemerdekaan Republik Indonesia, BIVB pun mengalami beberapa transformasi dan perubahan nama. Huruf "I" yang semula Indlansche menjadi Indonesische, lalu menjadi Perserikatan Sepakraga Indonesia Bandung (PSIB), hingga melakukan fusi bersama NVB (National Voetball Bond) dan berubah nama menjadi PERSIB pada 18 Maret 1934.
                </p>
                <p class="mb-4">
                  Sejak 9 September 2009, PERSIB kembali bertransformasi dan menjadi klub profesional di bawah pengelolaan PT PERSIB Bandung Bermartabat.
                </p>
                <p class="mb-0">
                  Unduh dan baca selengkapnya terkait Hari Jadi PERSIB 05 Januari 1919 dalam Naskah Akademik berikut.
                </p>
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
