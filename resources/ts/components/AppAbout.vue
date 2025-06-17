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
      <div class="text-center">
        <h3 class="text-h3 pricing-title">
          Tentang 
        </h3>
        <p class="mb-0">
          Indonesia Junior League merupakan operator kompetisi sepak bola usia dini dengan level berjenjang mulai dari usia delapan tahun yang menancapkan eksistensinya di Tanah Air sejak 17 Agustus 2014. 
          Khusus untuk IJL edisi pertama dan kedua lalu, kompetisi dibagi khusus pada dua kelompok usia yakni U-9 dan U-11 dan menampung sekitar 600-700 pemain. 
          Di musim ketiganya dengan dukungan Mayapada Heath Care Group, IJL melakukan terobosan baru dengan menggelar tiga kompetisi sekaligus yakni U-9, U-11 hingga U-13. 
          Sekitar 24 SSB akan jadi kontestan di tiap kelompok umur yang diperkirakan akan menampung lebih dari 1000 pemain. Sistem tanam, rawat, panen jadi visi misi terbentuknya IJL. 
          Hingga pada akhirnya pemain jebolan Indonesia Junior League nantinya akan dipetik oleh PSSI dan Timnas Indonesia demi menghadirkan tim sepak bola Tanah Air yang solid.
        </p>
      </div>
      </VCard>
    </div>
  </div>
</template>

<style lang="scss" scoped>
@use "../../styles/@core/template/pages/page-auth";
</style>

