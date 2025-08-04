<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import { useWindowScroll } from '@vueuse/core'
import type { RouteLocationRaw } from 'vue-router/auto'
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { useDisplay } from 'vuetify'

import NavbarThemeSwitcher from '@/layouts/components/NavbarThemeSwitcher.vue'

const props = defineProps({
  activeId: String,
})

const display = useDisplay()

interface navItem {
  name: string
  to: RouteLocationRaw
}

interface MenuItem {
  listTitle: string
  listIcon: string
  navItems: navItem[]
}
const { y } = useWindowScroll()

const authStore = useAuthStore()
const route = useRoute()
const router = useRouter()

const sidebar = ref(false)

watch(() => display, () => {
  return display.mdAndUp ? sidebar.value = false : sidebar.value
}, { deep: true })

const isMenuOpen = ref(false)
const isMegaMenuOpen = ref(false)

const isMenuOpen2 = ref(false)
const isMegaMenuOpen2 = ref(false)

const isMenuOpen3 = ref(false)
const isMegaMenuOpen3 = ref(false)

const isMenuOpenMedia = ref(false)
const isMegaMenuOpenMedia = ref(false)

const menuItems: MenuItem[] = [
  {
    listTitle: 'Sejarah',
    listIcon: 'tabler-layout-dashboard',
    navItems: [
      { name: 'Sejarah', to: { name: 'front-pages-history' }},
      { name: 'Pemain', to: { name: 'front-pages-player' } },
    ],
  },
]

const menuItems2: MenuItem[] = [
  {
    listTitle: 'Pertandingan',
    listIcon: 'tabler-ball-football',
    navItems: [
      { name: 'Jadwal Pertandingan', to: { name: 'front-pages-schedule-match' } },
      { name: 'Jadwal Latihan', to: { name: 'front-pages-schedule-training' } },
      { name: 'Klasemen', to: { name: 'front-pages-standing' } },
    ],
  },
]

const menuItems3: MenuItem[] = [
  {
    listTitle: 'Selengkapnya',
    listIcon: 'tabler-list',
    navItems: [
      { name: 'Klub', to: { name: 'front-pages-club' } },
      // { name: 'Berita', to: { name: 'front-pages-media' } },
      { name: 'Biaya Pendaftaran', to: { name: 'front-pages-pricing' } },
    ],
  },
]

const isCurrentRoute = (to: RouteLocationRaw) => {
  return route.matched.some(_route => _route.path.startsWith(router.resolve(to).path))

  // ℹ️ Below is much accurate approach if you don't have any nested routes
  // return route.matched.some(_route => _route.path === router.resolve(to).path)
}

const isPageActive = computed(() => menuItems.some(item => item.navItems.some(listItem => isCurrentRoute(listItem.to))))
const isPageActive2 = computed(() => menuItems2.some(item => item.navItems.some(listItem => isCurrentRoute(listItem.to))))
const isPageActive3 = computed(() => menuItems3.some(item => item.navItems.some(listItem => isCurrentRoute(listItem.to))))

const onLogout = async () => {
    try {
        const loginType = authStore.loginType;
        const endpoint = loginType === 'dashboard' ? '/api/dashboard/logout' : '/api/company/logout';
        
        await $api(endpoint, {
            method: 'POST'
        });
        
        authStore.deleteUserData();
        
        // snackbarMessage.value = 'Logout berhasil';
        // snackbarColor.value = 'success';
        // isFlatSnackbarVisible.value = true;
        
        await router.push({ name: 'pages-authentication-login-v1-company' });
        
    } catch (err) {
        // Clear local data even if API call fails
        authStore.deleteUserData();
        await router.push({ name: 'pages-authentication-login-v1-company' });
    }
}
</script>

<template>
  <!-- 👉 Navigation drawer for mobile devices  -->
  <VNavigationDrawer
    v-model="sidebar"
    width="275"
    data-allow-mismatch
    disable-resize-watcher
    class="mobile-nav-drawer"
  >
    <PerfectScrollbar
      :options="{ wheelPropagation: false }"
      class="h-100"
    >
      <!-- Nav items -->
      <div>
        <div class="d-flex flex-column gap-y-4 pa-4">
          <RouterLink
            v-for="(item, index) in ['Beranda']"
            :key="index"
            :to="{ name: 'front-pages-landing-page', hash: `#${item.toLowerCase().replace(' ', '-')}` }"
            class="nav-link font-weight-medium mobile-nav-link"
            :class="[props.activeId?.toLocaleLowerCase().replace('-', ' ') === item.toLocaleLowerCase() ? 'active-link' : '']"
          >
            {{ item }}
          </RouterLink>

          <div class="font-weight-medium cursor-pointer">
            <div
              :class="[isMenuOpen ? 'mb-6 active-link' : '', isPageActive ? 'active-link' : '']"
              class="page-link mobile-nav-link"
              @click="isMenuOpen = !isMenuOpen"
            >
              Tentang <VIcon :icon="isMenuOpen ? 'tabler-chevron-up' : 'tabler-chevron-down'" />
            </div>

            <div
              class="px-4"
              :class="isMenuOpen ? 'd-block' : 'd-none'"
            >
              <div
                v-for="(item, index) in menuItems"
                :key="index"
              >
                <div class="d-flex align-center gap-x-3 mb-4">
                  <VAvatar
                    variant="tonal"
                    color="primary"
                    rounded
                    :icon="item.listIcon"
                  />
                  <div class="text-body-1 text-high-emphasis font-weight-medium">
                    {{ item.listTitle }}
                  </div>
                </div>
                <ul class="mb-6">
                  <li
                    v-for="listItem in item.navItems"
                    :key="listItem.name"
                    style="list-style: none;"
                    class="text-body-1 mb-4 text-no-wrap"
                  >
                    <RouterLink
                      :to="listItem.to"
                      :target="'_self'"
                      class="mega-menu-item"
                      :class="isCurrentRoute(listItem.to) ? 'active-link' : 'text-high-emphasis'"
                    >
                      <VIcon
                        icon="tabler-circle"
                        :size="10"
                        class="me-2"
                      />
                      <span>  {{ listItem.name }}</span>
                    </RouterLink>
                  </li>
                </ul>
              </div>
            </div>

            <div
              :class="[isMenuOpen2 ? 'mb-6 active-link' : '', isPageActive2 ? 'active-link' : '']"
              class="page-link mobile-nav-link"
              @click="isMenuOpen2 = !isMenuOpen2"
            >
              Pertandingan <VIcon :icon="isMenuOpen2 ? 'tabler-chevron-up' : 'tabler-chevron-down'" />
            </div>

            <div
              class="px-4"
              :class="isMenuOpen2 ? 'd-block' : 'd-none'"
            >
              <div
                v-for="(item, index) in menuItems2"
                :key="index"
              >
                <div class="d-flex align-center gap-x-3 mb-4">
                  <VAvatar
                    variant="tonal"
                    color="primary"
                    rounded
                    :icon="item.listIcon"
                  />
                  <div class="text-body-1 text-high-emphasis font-weight-medium">
                    {{ item.listTitle }}
                  </div>
                </div>
                <ul class="mb-6">
                  <li
                    v-for="listItem in item.navItems"
                    :key="listItem.name"
                    style="list-style: none;"
                    class="text-body-1 mb-4 text-no-wrap"
                  >
                    <RouterLink
                      :to="listItem.to"
                      :target="'_self'"
                      class="mega-menu-item"
                      :class="isCurrentRoute(listItem.to) ? 'active-link' : 'text-high-emphasis'"
                    >
                      <VIcon
                        icon="tabler-circle"
                        :size="10"
                        class="me-2"
                      />
                      <span>  {{ listItem.name }}</span>
                    </RouterLink>
                  </li>
                </ul>
              </div>
            </div>

            <div
              :class="[isMenuOpen3 ? 'mb-6 active-link' : '', isPageActive3 ? 'active-link' : '']"
              class="page-link mobile-nav-link"
              @click="isMenuOpen3 = !isMenuOpen3"
            >
              Selengkapnya <VIcon :icon="isMenuOpen3 ? 'tabler-chevron-up' : 'tabler-chevron-down'" />
            </div>

            <div
              class="px-4"
              :class="isMenuOpen3 ? 'd-block' : 'd-none'"
            >
              <div
                v-for="(item, index) in menuItems3"
                :key="index"
              >
                <div class="d-flex align-center gap-x-3 mb-4">
                  <VAvatar
                    variant="tonal"
                    color="primary"
                    rounded
                    :icon="item.listIcon"
                  />
                  <div class="text-body-1 text-high-emphasis font-weight-medium">
                    {{ item.listTitle }}
                  </div>
                </div>
                <ul class="mb-6">
                  <li
                    v-for="listItem in item.navItems"
                    :key="listItem.name"
                    style="list-style: none;"
                    class="text-body-1 mb-4 text-no-wrap"
                  >
                    <RouterLink
                      :to="listItem.to"
                      :target="'_self'"
                      class="mega-menu-item"
                      :class="isCurrentRoute(listItem.to) ? 'active-link' : 'text-high-emphasis'"
                    >
                      <VIcon
                        icon="tabler-circle"
                        :size="10"
                        class="me-2"
                      />
                      <span>  {{ listItem.name }}</span>
                    </RouterLink>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Navigation drawer close icon -->
      <VIcon
        id="navigation-drawer-close-btn"
        icon="tabler-x"
        size="20"
        @click="sidebar = !sidebar"
      />
    </PerfectScrollbar>
  </VNavigationDrawer>

  <!-- 👉 Navbar for desktop devices  -->
  <div class="front-page-navbar">
    <VAppBar
      color="#1793FF"
      elevation="0"
      class="new-navbar px-5"
      height="95"
    >
      <!-- toggle icon for mobile device -->
      <IconBtn
        id="vertical-nav-toggle-btn"
        class="ms-n3 me-2 d-inline-block d-md-none"
        @click="sidebar = !sidebar"
      >
        <VIcon
          size="26"
          icon="tabler-menu-2"
          color="white"
        />
      </IconBtn>

      <!-- Logo Section -->
      <div class="d-flex align-center">
        <VAppBarTitle class="me-6">
          <RouterLink
            :to="{ name: 'front-pages-landing-page', hash: '#beranda' }"
            class="d-flex align-center gap-x-3"
          >
            <div class="app-logo d-flex align-center">
              <img
                src="/storage/logo/LOGOSSB.png"
                alt="Logo SSB"
                style="height: 50px;"
                class="me-3"
              />
              <div class="brand-text d-none d-md-flex flex-column">
                <h5 class="brand-line-1">PUTRA MUDA</h5>
                <h5 class="brand-line-2">BALARAJA</h5>
              </div>
            </div>
          </RouterLink>
        </VAppBarTitle>
      </div>

      <VSpacer />

      <!-- Navigation Menu -->
      <div class="d-flex align-center gap-x-2">
        <!-- Menu Items -->
        <div class="d-none d-md-flex align-center">
          <RouterLink
            v-for="(item, index) in ['Beranda']"
            :key="index"
            :to="{ name: 'front-pages-landing-page', hash: `#${item.toLowerCase().replace(' ', '-')}` }"
            class="nav-link-new font-weight-medium"
            :class="[props.activeId?.toLocaleLowerCase().replace('-', ' ') === item.toLocaleLowerCase() ? 'active-link-new' : '']"
          >
            {{ item }}
          </RouterLink>

          <!-- Tentang Menu -->
          <span
            class="nav-link-new font-weight-medium cursor-pointer"
            :class="isPageActive || isMegaMenuOpen ? 'active-link-new' : ''"
          >
            Tentang
            <VIcon
              icon="tabler-chevron-down"
              size="18"
              class="ms-1"
            />
            <VMenu
              v-model="isMegaMenuOpen"
              open-on-hover
              activator="parent"
              transition="slide-y-transition"
              location="bottom center"
              offset="20"
              content-class="mega-menu"
              location-strategy="connected"
              close-on-content-click
            >
              <VCard max-width="1200">
                <VCardText class="pa-10">
                  <div class="nav-menu">
                    <div
                      v-for="(item, index) in menuItems"
                      :key="index"
                    >
                      <ul>
                        <li
                          v-for="listItem in item.navItems"
                          :key="listItem.name"
                          style="list-style: none;"
                          class="text-h6 mb-5 text-no-wrap"
                        >
                          <RouterLink
                            class="mega-menu-item"
                            :to="listItem.to"
                            :target="'_self'"
                            :class="isCurrentRoute(listItem.to) ? 'active-link' : 'text-high-emphasis'"
                          >
                            <div class="d-flex align-center">
                              <VIcon
                                icon="tabler-circle"
                                color="primary"
                                :size="12"
                                class="me-3"
                              />
                              <span>{{ listItem.name }}</span>
                            </div>
                          </RouterLink>
                        </li>
                      </ul>
                    </div>
                  </div>
                </VCardText>
              </VCard>
            </VMenu>
          </span>

          <!-- Media Menu -->
          <RouterLink
            :to="{ name: 'front-pages-media'}"
            class="nav-link-new font-weight-medium"
            :class="isMenuOpenMedia || isMegaMenuOpenMedia ? 'active-link-new' : ''"
          >
            Media
          </RouterLink>

          <!-- Pertandingan Menu -->
          <span
            class="nav-link-new font-weight-medium cursor-pointer"
            :class="isPageActive2 || isMegaMenuOpen2 ? 'active-link-new' : ''"
          >
            Pertandingan
            <VIcon
              icon="tabler-chevron-down"
              size="18"
              class="ms-1"
            />
            <VMenu
              v-model="isMegaMenuOpen2"
              open-on-hover
              activator="parent"
              transition="slide-y-transition"
              location="bottom center"
              offset="20"
              content-class="mega-menu"
              location-strategy="connected"
              close-on-content-click
            >
              <VCard max-width="1200">
                <VCardText class="pa-10">
                  <div class="nav-menu">
                    <div
                      v-for="(item, index) in menuItems2"
                      :key="index"
                    >
                      <ul>
                        <li
                          v-for="listItem in item.navItems"
                          :key="listItem.name"
                          style="list-style: none;"
                          class="text-h6 mb-5 text-no-wrap"
                        >
                          <RouterLink
                            class="mega-menu-item"
                            :to="listItem.to"
                            :target="'_self'"
                            :class="isCurrentRoute(listItem.to) ? 'active-link' : 'text-high-emphasis'"
                          >
                            <div class="d-flex align-center">
                              <VIcon
                                icon="tabler-circle"
                                color="primary"
                                :size="12"
                                class="me-3"
                              />
                              <span>{{ listItem.name }}</span>
                            </div>
                          </RouterLink>
                        </li>
                      </ul>
                    </div>
                  </div>
                </VCardText>
              </VCard>
            </VMenu>
          </span>

          <!-- Selengkapnya Menu -->
          <span
            class="nav-link-new font-weight-medium cursor-pointer"
            :class="isPageActive3 || isMegaMenuOpen3 ? 'active-link-new' : ''"
          >
            Selengkapnya
            <VIcon
              icon="tabler-chevron-down"
              size="18"
              class="ms-1"
            />
            <VMenu
              v-model="isMegaMenuOpen3"
              open-on-hover
              activator="parent"
              transition="slide-y-transition"
              location="bottom center"
              offset="20"
              content-class="mega-menu"
              location-strategy="connected"
              close-on-content-click
            >
              <VCard max-width="1200">
                <VCardText class="pa-10">
                  <div class="nav-menu">
                    <div
                      v-for="(item, index) in menuItems3"
                      :key="index"
                    >
                      <ul>
                        <li
                          v-for="listItem in item.navItems"
                          :key="listItem.name"
                          style="list-style: none;"
                          class="text-h6 mb-5 text-no-wrap"
                        >
                          <RouterLink
                            class="mega-menu-item"
                            :to="listItem.to"
                            :target="'_self'"
                            :class="isCurrentRoute(listItem.to) ? 'active-link' : 'text-high-emphasis'"
                          >
                            <div class="d-flex align-center">
                              <VIcon
                                icon="tabler-circle"
                                color="primary"
                                :size="12"
                                class="me-3"
                              />
                              <span>{{ listItem.name }}</span>
                            </div>
                          </RouterLink>
                        </li>
                      </ul>
                    </div>
                  </div>
                </VCardText>
              </VCard>
            </VMenu>
          </span>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex align-center gap-x-3 ms-4">
          <NavbarThemeSwitcher />

          <!-- Login Button -->
          <VBtn
            v-if="$vuetify.display.lgAndUp"
            variant="outlined"
            color="white"
            class="text-white btn-navbar"
            size="default"
            @click="$router.push({ name: 'pages-authentication-login-v1-company' })"
          >
            Login
          </VBtn>

          <VBtn
            v-else
            rounded
            icon
            variant="outlined"
            color="white"
            size="default"
            @click="$router.push({ name: 'pages-authentication-login-v1-company' })"
          >
            <VIcon icon="tabler-login" color="white" />
          </VBtn>

          <!-- Register Button -->
          <VBtn
            v-if="$vuetify.display.lgAndUp"
            variant="flat"
            color="white"
            class="text-primary btn-navbar"
            size="default"
            @click="$router.push({ name: 'pages-authentication-register-multi-steps' })"
          >
            Register Now
          </VBtn>

          <VBtn
            v-else
            rounded
            icon
            variant="flat"
            color="white"
            size="default"
            @click="$router.push({ name: 'pages-authentication-register-multi-steps' })"
          >
            <VIcon icon="tabler-registered" color="#1793FF" />
          </VBtn>
        </div>
      </div>
    </VAppBar>
  </div>
</template>

<style lang="scss" scoped>
// Brand Text Styling
.brand-line-1,
.brand-line-2 {
  margin: 0;
  line-height: 1.1;
  font-size: 1.1rem;
  font-weight: 600;
  color: white;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

// New Navbar Styling
.new-navbar {
  background: #1793FF !important;
  box-shadow: 0 2px 10px rgba(23, 147, 255, 0.2);
  position: fixed !important;
  width: 100% !important;
  z-index: 1000;
  
  .v-toolbar__content {
    padding: 0 24px !important;
    max-width: 1200px;
    margin: 0 auto;
  }
}

// Navigation Links
.nav-link-new {
  color: white !important;
  text-decoration: none !important;
  padding: 12px 20px;
  border-radius: 8px;
  font-size: 1.1rem;
  font-weight: 500;
  transition: all 0.3s ease;
  position: relative;
  
  &:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-1px);
  }
  
  &.active-link-new {
    background: rgba(255, 255, 255, 0.15);
    font-weight: 600;
    
    &::after {
      content: '';
      position: absolute;
      bottom: -2px;
      left: 50%;
      transform: translateX(-50%);
      width: 30px;
      height: 2px;
      background: white;
      border-radius: 2px;
    }
  }
}

// Button Styling
.btn-navbar {
  text-transform: none !important;
  font-weight: 500 !important;
  border-radius: 8px !important;
  font-size: 1rem !important;
  padding: 10px 20px !important;
  
  &.v-btn--variant-outlined {
    border: 1.5px solid white !important;
    
    &:hover {
      background: white !important;
      color: #1793FF !important;
    }
  }
  
  &.v-btn--variant-flat {
    &:hover {
      background: rgba(255, 255, 255, 0.9) !important;
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(255, 255, 255, 0.2);
    }
  }
}

// Mobile Navigation
.mobile-nav-drawer {
  background: #1793FF !important;
  
  .mobile-nav-link {
    color: white !important;
    font-size: 1.1rem !important;
    
    &:hover {
      color: rgba(255, 255, 255, 0.8) !important;
    }
  }
}

// Mega Menu Styling
.nav-menu {
  display: flex;
  gap: 3rem;
}

.mega-menu-item {
  font-size: 1.1rem !important;
  font-weight: 500 !important;
  
  &:hover {
    color: #1793FF !important;
  }
}

// Responsive Design
@media (max-width: 960px) {
  .new-navbar {
    .v-toolbar__content {
      padding: 0 20px !important;
    }
  }
  
  .brand-line-1,
  .brand-line-2 {
    font-size: 1rem;
  }
  
  .nav-link-new {
    font-size: 1rem;
    padding: 10px 16px;
  }
}

@media (max-width: 600px) {
  .new-navbar {
    .v-toolbar__content {
      padding: 0 16px !important;
    }
  }
  
  .brand-line-1,
  .brand-line-2 {
    font-size: 0.9rem;
  }
}

// Navigation Drawer Close Button
#navigation-drawer-close-btn {
  position: absolute;
  cursor: pointer;
  top: 0.5rem;
  right: 1rem;
  color: white !important;
}

// Page Link Styling
.page-link {
  color: white !important;
  font-size: 1.1rem !important;
  
  &:hover {
    color: rgba(255, 255, 255, 0.8) !important;
  }
}

// Active Link Styling
.active-link {
  font-weight: 600 !important;
}
</style>

<style lang="scss">
@use "@layouts/styles/mixins" as layoutMixins;

.mega-menu {
  border-radius: 12px !important;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15) !important;
  
  .v-card-text {
    padding: 40px !important;
  }
  
  .text-h6 {
    font-size: 1.2rem !important;
    font-weight: 500 !important;
  }
}

.front-page-navbar {
  .v-toolbar {
    margin: 0 !important;
    left: 0 !important;
    right: 0 !important;
  }
}

// Override theme switcher for navbar
.front-page-navbar .v-btn--icon {
  color: white !important;
  
  &:hover {
    background: rgba(255, 255, 255, 0.1) !important;
  }
}
</style>
