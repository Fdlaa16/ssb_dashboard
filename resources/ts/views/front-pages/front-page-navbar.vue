<script setup lang="ts">
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
</script>

<template>
  <!-- 👉 Navigation drawer for mobile devices  -->
  <VNavigationDrawer
    v-model="sidebar"
    width="275"
    data-allow-mismatch
    disable-resize-watcher
  >
    <PerfectScrollbar
      :options="{ wheelPropagation: false }"
      class="h-100"
    >
      <!-- Nav items -->
      <div>
        <div class="d-flex flex-column gap-y-4 pa-4">
          <RouterLink
            v-for="(item, index) in ['Home']"
            :key="index"
            :to="{ name: 'front-pages-landing-page', hash: `#${item.toLowerCase().replace(' ', '-')}` }"
            class="nav-link font-weight-medium"
            :class="[props.activeId?.toLocaleLowerCase().replace('-', ' ') === item.toLocaleLowerCase() ? 'active-link' : '']"
          >
            {{ item }}
          </RouterLink>

          <div class="font-weight-medium cursor-pointer">
            <div
              :class="[isMenuOpen ? 'mb-6 active-link' : '', isPageActive ? 'active-link' : '']"
              style="color: rgba(var(--v-theme-on-surface));"
              class="page-link"
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
              :class="[isMenuOpen2 ? 'mb-6 active-link' : '', isPageActive ? 'active-link' : '']"
              style="color: rgba(var(--v-theme-on-surface));"
              class="page-link"
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
              :class="[isMenuOpen3 ? 'mb-6 active-link' : '', isPageActive ? 'active-link' : '']"
              style="color: rgba(var(--v-theme-on-surface));"
              class="page-link"
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

          <!-- <RouterLink
            to="/"
            target="_blank"
            class="font-weight-medium nav-link"
          >
            Admin
          </RouterLink> -->
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
    <div class="front-page-navbar">
      <VAppBar
        :color="$vuetify.theme.current.dark ? 'rgba(var(--v-theme-surface),0.38)' : 'rgba(var(--v-theme-surface), 0.38)'"
        :class="y > 10 ? 'app-bar-scrolled' : [$vuetify.theme.current.dark ? 'app-bar-dark' : 'app-bar-light', 'elevation-0']"
        class="navbar-blur"
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
            color="rgba(var(--v-theme-on-surface))"
          />
        </IconBtn>
        <!-- Title and Landing page sections -->
        <div class="d-flex align-center">
          <VAppBarTitle class="me-6">
            <RouterLink
              :to="{ name: 'front-pages-landing-page', hash: '#home' }"
              class="d-flex gap-x-4"
              :class="$vuetify.display.mdAndUp ? 'd-none' : 'd-block'"
            >
              <div class="app-logo d-flex align-center d-md-none">
                <img
                  src="/storage/logo/LOGOSSB.png"
                  alt="Logo SSB"
                  style="height: 40px;"
                />
              </div>

              <div class="app-logo d-none d-md-flex align-center">
                <img
                  src="/storage/logo/LOGOSSB.png"
                  alt="Logo SSB"
                  style="height: 40px;"
                  class="me-2"
                />
                <div class="brand-text d-flex flex-column">
                  <h5 class="brand-line-1">PUTRA MUDA</h5>
                  <h5 class="brand-line-2">BALARAJA</h5>
                </div>
              </div>
            </RouterLink>
          </VAppBarTitle>
        </div>

        <VSpacer />

        <div class="d-flex gap-x-4">
          <!-- landing page sections -->
          <div class="text-base align-center d-none d-md-flex">
            <RouterLink
              v-for="(item, index) in ['Home']"
              :key="index"
              :to="{ name: 'front-pages-landing-page', hash: `#${item.toLowerCase().replace(' ', '-')}` }"
              class="nav-link font-weight-medium py-2 px-2 px-lg-4"
              :class="[props.activeId?.toLocaleLowerCase().replace('-', ' ') === item.toLocaleLowerCase() ? 'active-link' : '']"
            >
              {{ item }}
            </RouterLink>

            <!-- Pages Menu -->
            <span
              class="font-weight-medium cursor-pointer px-2 px-lg-4 py-2"
              :class="isPageActive || isMegaMenuOpen ? 'active-link' : ''"
              style="color: rgba(var(--v-theme-on-surface));"
            >
              Tentang
              <VIcon
                icon="tabler-chevron-down"
                size="16"
                class="ms-2"
              />
              <VMenu
                v-model="isMegaMenuOpen"
                open-on-hover
                activator="parent"
                transition="slide-y-transition"
                location="bottom center"
                offset="16"
                content-class="mega-menu"
                location-strategy="static"
                close-on-content-click
              >
                <VCard max-width="1000">
                  <VCardText class="pa-8">
                    <div class="nav-menu">
                      <div
                        v-for="(item, index) in menuItems"
                        :key="index"
                      >
                        <div class="d-flex align-center gap-x-3 mb-6">
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
                        <ul>
                          <li
                            v-for="listItem in item.navItems"
                            :key="listItem.name"
                            style="list-style: none;"
                            class="text-body-1 mb-4 text-no-wrap"
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
                                  :size="10"
                                  class="me-2"
                                />
                                <span>{{ listItem.name }}</span>
                              </div>
                            </RouterLink>
                          </li>
                        </ul>
                      </div>
                      <!-- <img
                        :src="navImg"
                        alt="Navigation Image"
                        class="d-inline-block rounded-lg"
                        style="border: 10px solid rgb(var(--v-theme-background));"
                        :width="$vuetify.display.lgAndUp ? '330' : '250'"
                        :height="$vuetify.display.lgAndUp ? '330' : '250'"
                      > -->
                    </div>
                  </VCardText>
                </VCard>
              </VMenu>
            </span>

            <RouterLink
              :to="{ name: 'front-pages-media'}"
              class="nav-link font-weight-medium py-2 px-2 px-lg-4"
              :class="isMenuOpenMedia || isMegaMenuOpenMedia ? 'active-link' : ''"
            >
              Media
            </RouterLink>

            <span
              class="font-weight-medium cursor-pointer px-2 px-lg-4 py-2"
              :class="isPageActive2 || isMegaMenuOpen2 ? 'active-link' : ''"
              style="color: rgba(var(--v-theme-on-surface));"
            >
              Pertandingan
              <VIcon
                icon="tabler-chevron-down"
                size="16"
                class="ms-2"
              />
              <VMenu
                v-model="isMegaMenuOpen2"
                open-on-hover
                activator="parent"
                transition="slide-y-transition"
                location="bottom center"
                offset="16"
                content-class="mega-menu"
                location-strategy="static"
                close-on-content-click
              >
                <VCard max-width="1000">
                  <VCardText class="pa-8">
                    <div class="nav-menu">
                      <div
                        v-for="(item, index) in menuItems2"
                        :key="index"
                      >
                        <div class="d-flex align-center gap-x-3 mb-6">
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
                        <ul>
                          <li
                            v-for="listItem in item.navItems"
                            :key="listItem.name"
                            style="list-style: none;"
                            class="text-body-1 mb-4 text-no-wrap"
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
                                  :size="10"
                                  class="me-2"
                                />
                                <span>{{ listItem.name }}</span>
                              </div>
                            </RouterLink>
                          </li>
                        </ul>
                      </div>
                      <!-- <img
                        :src="navImg"
                        alt="Navigation Image"
                        class="d-inline-block rounded-lg"
                        style="border: 10px solid rgb(var(--v-theme-background));"
                        :width="$vuetify.display.lgAndUp ? '330' : '250'"
                        :height="$vuetify.display.lgAndUp ? '330' : '250'"
                      > -->
                    </div>
                  </VCardText>
                </VCard>
              </VMenu>
            </span>

            <span
              class="font-weight-medium cursor-pointer px-2 px-lg-4 py-2"
              :class="isPageActive3 || isMegaMenuOpen3 ? 'active-link' : ''"
              style="color: rgba(var(--v-theme-on-surface));"
            >
              Selengkapnya
              <VIcon
                icon="tabler-chevron-down"
                size="16"
                class="ms-2"
              />
              <VMenu
                v-model="isMegaMenuOpen3"
                open-on-hover
                activator="parent"
                transition="slide-y-transition"
                location="bottom center"
                offset="16"
                content-class="mega-menu"
                location-strategy="static"
                close-on-content-click
              >
                <VCard max-width="1000">
                  <VCardText class="pa-8">
                    <div class="nav-menu">
                      <div
                        v-for="(item, index) in menuItems3"
                        :key="index"
                      >
                        <div class="d-flex align-center gap-x-3 mb-6">
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
                        <ul>
                          <li
                            v-for="listItem in item.navItems"
                            :key="listItem.name"
                            style="list-style: none;"
                            class="text-body-1 mb-4 text-no-wrap"
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
                                  :size="10"
                                  class="me-2"
                                />
                                <span>{{ listItem.name }}</span>
                              </div>
                            </RouterLink>
                          </li>
                        </ul>
                      </div>
                      <!-- <img
                        :src="navImg"
                        alt="Navigation Image"
                        class="d-inline-block rounded-lg"
                        style="border: 10px solid rgb(var(--v-theme-background));"
                        :width="$vuetify.display.lgAndUp ? '330' : '250'"
                        :height="$vuetify.display.lgAndUp ? '330' : '250'"
                      > -->
                    </div>
                  </VCardText>
                </VCard>
              </VMenu>
            </span>

            <!-- <RouterLink
              to="/"
              target="_blank"
              class="font-weight-medium nav-link"
            >
              Admin
            </RouterLink> -->
          </div>

          <NavbarThemeSwitcher />

           <VBtn
            v-if="$vuetify.display.lgAndUp"
            variant="elevated"
            color="primary"
            @click="$router.push({ name: 'pages-authentication-login-v1-company' })"
          >
            Login
          </VBtn>

          <VBtn
            v-else
            rounded
            icon
            variant="elevated"
            color="primary"
            @click="$router.push({ name: 'pages-authentication-login-v1-company' })"
          >
            <VIcon icon="tabler-login" />
          </VBtn>

          <VBtn
            v-if="$vuetify.display.lgAndUp"
            variant="elevated"
            color="primary"
            @click="$router.push({ name: 'pages-authentication-register-multi-steps' })"
          >
            Register Now
          </VBtn>

          <VBtn
            v-else
            rounded
            icon
            variant="elevated"
            color="primary"
            @click="$router.push({ name: 'pages-authentication-register-multi-steps' })"
          >
            <VIcon icon="tabler-registered" />
          </VBtn>

          <!-- <VBtn
            v-if="$vuetify.display.lgAndUp"
            variant="elevated"
            color="primary"
            @click="$router.push({ name: 'front-pages-profile' })"
          >
            Profile
          </VBtn>

          <VBtn
            v-else
            rounded
            icon
            variant="elevated"
            color="primary"
            @click="$router.push({ name: 'front-pages-profile' })"
          >
            <VIcon icon="tabler-user" />
          </VBtn> -->
        </div>
      </VAppBar>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.brand-line-1,
.brand-line-2 {
  margin: 0;
  line-height: 1.2;
  font-size: 1rem; 
}

.nav-menu {
  display: flex;
  gap: 2rem;
}

.nav-link {
  &:not(:hover) {
    color: rgb(var(--v-theme-on-surface));
  }
}

.page-link {
  &:hover {
    color: rgb(var(--v-theme-primary)) !important;
  }
}

@media (max-width: 1280px) {
  .nav-menu {
    gap: 2.25rem;
  }
}

@media (min-width: 1920px) {
  .front-page-navbar {
    .v-toolbar {
      max-inline-size: calc(1440px - 32px);
    }
  }
}

@media (min-width: 1280px) and (max-width: 1919px) {
  .front-page-navbar {
    .v-toolbar {
      max-inline-size: calc(1200px - 32px);
    }
  }
}

@media (min-width: 960px) and (max-width: 1279px) {
  .front-page-navbar {
    .v-toolbar {
      max-inline-size: calc(900px - 32px);
    }
  }
}

@media (min-width: 600px) and (max-width: 959px) {
  .front-page-navbar {
    .v-toolbar {
      max-inline-size: calc(100% - 64px);
    }
  }
}

@media (max-width: 600px) {
  .front-page-navbar {
    .v-toolbar {
      max-inline-size: calc(100% - 32px);
    }
  }
}

.nav-item-img {
  border: 10px solid rgb(var(--v-theme-background));
  border-radius: 10px;
}

.active-link {
  color: rgb(var(--v-theme-primary)) !important;
}

.app-bar-light {
  border: 2px solid rgba(var(--v-theme-surface), 68%);
  border-radius: 0.5rem;
  background-color: rgba(var(--v-theme-surface), 38%);
  transition: all 0.1s ease-in-out;
}

.app-bar-dark {
  border: 2px solid rgba(var(--v-theme-surface), 68%);
  border-radius: 0.5rem;
  background-color: rgba(255, 255, 255, 4%);
  transition: all 0.1s ease-in-out;
}

.app-bar-scrolled {
  border: 2px solid rgb(var(--v-theme-surface));
  border-radius: 0.5rem;
  background-color: rgb(var(--v-theme-surface)) !important;
  transition: all 0.1s ease-in-out;
}

.front-page-navbar::after {
  position: fixed;
  z-index: 2;
  backdrop-filter: saturate(100%) blur(6px);
  block-size: 5rem;
  content: "";
  inline-size: 100%;
}
</style>

<style lang="scss">
@use "@layouts/styles/mixins" as layoutMixins;

.mega-menu {
  position: fixed !important;
  inset-block-start: 5.4rem;
  inset-inline-start: 50%;
  transform: translateX(-50%);

  @include layoutMixins.rtl {
    transform: translateX(50%);
  }
}

.front-page-navbar {
  .v-toolbar__content {
    padding-inline: 30px !important;
  }

  .v-toolbar {
    inset-inline: 0 !important;
    margin-block-start: 1rem !important;
    margin-inline: auto !important;
  }
}

.mega-menu-item {
  &:hover {
    color: rgb(var(--v-theme-primary)) !important;
  }
}

#navigation-drawer-close-btn {
  position: absolute;
  cursor: pointer;
  inset-block-start: 0.5rem;
  inset-inline-end: 1rem;
}
</style>
