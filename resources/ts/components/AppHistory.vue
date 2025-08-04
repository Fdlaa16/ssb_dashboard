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
      
      <!-- Shape Atas -->
      <VNodeRenderer
        :nodes="h('div', { innerHTML: authV1TopShape })"
        class="text-primary auth-v1-top-shape d-none d-sm-block"
      />

      <!-- Shape Bawah -->
      <VNodeRenderer
        :nodes="h('div', { innerHTML: authV1BottomShape })"
        class="text-primary auth-v1-bottom-shape d-none d-sm-block"
      />

      <!-- Kartu Utama -->
      <VCard
        class="auth-card"
        :class="$vuetify.display.smAndUp ? 'pa-6' : 'pa-0'"
      >
        <!-- Judul -->
        <div class="text-center mb-6">
          <h3 class="text-h3 pricing-title">Sejarah</h3>
        </div>

        <!-- Layout Grid -->
        <VContainer fluid>
          <VRow>

            <!-- Kolom Teks (8) -->
            <VCol cols="12" md="8">
              <div class="text-left mb-4">
                <p>
                  Sekolah Sepak Bola (SSB) Putra Muda Balaraja resmi didirikan pada tanggal 10 Oktober 2020 di kawasan Balaraja, Kabupaten Tangerang, sebagai wujud nyata kepedulian terhadap pembinaan generasi muda melalui jalur olahraga.
                  SSB ini hadir dengan tujuan utama untuk mengarahkan anak-anak pada kegiatan positif, membangun karakter, kedisiplinan, dan semangat kompetitif sejak dini melalui pelatihan sepak bola yang terstruktur.
                  Berangkat dari visi untuk mencetak anak-anak yang berprestasi di bidang sepak bola, SSB Putra Muda Balaraja berkomitmen menjadi tempat pembinaan yang konsisten dalam melatih pemain usia dini agar tumbuh menjadi pesepakbola handal dan profesional di masa depan.
                </p>
              </div>

              <!-- VISI -->
              <h4 class="text-h6 font-weight-medium mb-2">VISI</h4>
              <div class="mb-4 ml-3">
                <ul>
                  <li>Mengajak anak-anak untuk diarahkan berkegiatan positif tentunya untuk dididik menjadi anak yang berprestasi di bidang sepak bola.</li>
                  <li>Membina dan mencetak pemain sepak bola usia dini yang handal agar kelak menjadi pemain profesional.</li>
                </ul>
              </div>

              <!-- MISI -->
              <div>
                <h4 class="text-h6 font-weight-medium mb-2">MISI</h4>
                <p>
                  Berpartisipasi di bidang olahraga yang kami mudahkan sarana sepak bola bagi anak-anak yang cinta dengan sepak bola,
                  sehingga terciptanya pemain sepak bola yang profesional dan handal serta menjadi pemain terbaik di berbagai kompetisi
                  atau ajang pertandingan sepak bola terutama di daerah Kabupaten Tangerang.
                </p>
              </div>
            </VCol>

            <!-- Kolom Gambar (4) -->
            <VCol cols="12" md="4">
              <div class="d-flex flex-column align-center">
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
