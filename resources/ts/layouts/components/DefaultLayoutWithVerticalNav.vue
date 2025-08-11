<script lang="ts" setup>
import navItems from '@/navigation/vertical'
// Components
import Footer from '@/layouts/components/Footer.vue'
import UserProfile from '@/layouts/components/UserProfile.vue'
// @layouts plugin
import { VerticalNavLayout } from '@layouts'
</script>

<template>
  <VerticalNavLayout :nav-items="navItems">
    <!-- 👉 navbar -->
    <template #navbar="{ toggleVerticalOverlayNavActive }">
      <div class="d-flex h-100 align-center w-100">
        <div class="ms-auto">
          <UserProfile />
        </div>
      </div>
    </template>
    
    <!-- 👉 Pages -->
    <slot />
    
    <!-- 👉 Footer -->
    <template #footer>
      <Footer />
    </template>
    
    <!-- 👉 Customizer -->
    <TheCustomizer />
  </VerticalNavLayout>
</template>
