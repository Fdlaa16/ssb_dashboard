<script setup lang="ts">
import NavbarThemeSwitcher from '@/layouts/components/NavbarThemeSwitcher.vue'
import { useAuthStore } from '@/stores/auth'
import { useWindowScroll } from '@vueuse/core'
import type { RouteLocationRaw } from 'vue-router/auto'
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { useDisplay } from 'vuetify'


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

// Profile menu state
const isProfileMenuOpen = ref(false)

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
      { name: 'Sejarah', to: { name: 'history' }},
      { name: 'Pengurus', to: { name: 'structure' }},
      { name: 'Pemain', to: { name: 'player' } },
    ],
  },
]

const menuItems2: MenuItem[] = [
  {
    listTitle: 'Pertandingan',
    listIcon: 'tabler-ball-football',
    navItems: [
      { name: 'Jadwal Pertandingan', to: { name: 'schedule-match' } },
      { name: 'Jadwal Latihan', to: { name: 'schedule-training' } },
      { name: 'Klasemen', to: { name: 'standing' } },
    ],
  },
]

const menuItems3: MenuItem[] = [
  {
    listTitle: 'Selengkapnya',
    listIcon: 'tabler-list',
    navItems: [
      { name: 'Klub', to: { name: 'club' } },
      // { name: 'Berita', to: { name: 'media' } },
      { name: 'Biaya Pendaftaran', to: { name: 'pricing' } },
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

// Check if user is authenticated
const isAuthenticated = computed(() => authStore.isLoggedIn)
const userProfile = computed(() => authStore.userData)

interface UserData {
  id: number
  fullName: string
  firstName: string
  lastName: string
  company: string
  username: string
  role: string
  country: string
  contact: string
  email: string
  currentPlan: string
  status: string
  avatar: string
  taskDone: number
  projectDone: number
  taxId: string
  language: string
}

interface PlayerData {
  id: number
  name: string
  user: {
    id: number
    email: string
    new_password: string
    confirm_password: string
  }
  nisn: string
  height: string
  weight: string
  back_number: string
  position: string
  category: string
  is_captain: boolean
  status: boolean
  sport_players: any[]
  club: {
    id: number
    code: string
    name: string
  }
  avatar: File | { url: string } | null
  family_card: File | { url: string } | null
  report_grades: File | { url: string } | null
  birth_certificate: File | { url: string } | null
}

interface Props {
  userData?: UserData
  playerId?: number
}


const currentTab = ref('biodata')
const loading = ref(false)
const error = ref<string | null>(null)
const isFlatSnackbarVisible = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref<'success' | 'error'>('success')

const isPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)
const avatarPreview = ref<string | null>(null)

// Player Data
const playerData = ref<PlayerData>({
  id: 0,
  name: '',
  user: {
    id: 0,
    email: '',
    new_password: '',
    confirm_password: ''
  },
  nisn: '',
  height: '',
  weight: '',
  back_number: '',
  position: '',
  category: '',
  is_captain: false,
  status: false,
  sport_players: [],
  club: {
    id: 0,
    code: '',
    name: '',
  },
  avatar: null,
  family_card: null,
  report_grades: null,
  birth_certificate: null,
})

const fetchPlayer = async () => {  
    const res = await $api(`company/profile`)
    playerData.value = res.data    

    if (playerData.value.avatar?.url) {
      avatarPreview.value = getImageUrl(playerData.value.avatar?.url)
    }
}

const getImageUrl = (path: string) => {
  return import.meta.env.VITE_APP_URL + path
}

const onLogout = async () => {
    try {
        const loginType = authStore.loginType;
        const endpoint = loginType === 'dashboard' ? '/logout' : '/company/logout';
        
        await $api(endpoint, {
            method: 'POST'
        });
        
        authStore.deleteUserData();
        
        await router.push({ name: 'authentication-login' });
        
    } catch (err) {
        authStore.deleteUserData();
        await router.push({ name: 'authentication-login' });
    }
}

const goToProfile = () => {
  router.push({ name: 'profile' }); 
}

onMounted(() => {
  if (isAuthenticated.value) {
    fetchPlayer();
  }
});
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
            :to="{ name: 'landing-page', hash: `#${item.toLowerCase().replace(' ', '-')}` }"
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

            <!-- Media Menu -->
            <RouterLink
              :to="{ name: 'media'}"
              class="page-link mobile-nav-link"
              :class="isMenuOpenMedia || isMegaMenuOpenMedia ? 'active-link-new' : ''"
            >
              Media
            </RouterLink>

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

          <!-- Mobile Profile and Logout Section (when authenticated) -->
          <div v-if="isAuthenticated" class="mobile-auth-section mt-auto">
            <VDivider class="mb-4" color="rgba(255,255,255,0.2)" />
            
            <!-- Logout Button for Mobile -->
            <div 
              class="mobile-nav-link d-flex align-center justify-center cursor-pointer"
              style="border: 1px solid white; border-radius: 6px; padding: 0.5rem 1rem;"
              @click="onLogout"
            >
              <VIcon icon="tabler-logout" class="me-3" />
              Logout
            </div>
          </div>

          <div v-if="!isAuthenticated" class="mobile-auth-section mt-auto">
            <VDivider class="mb-4" color="rgba(255,255,255,0.2)" />
            
            <!-- Logout Button for Mobile -->
            <div 
              class="mobile-nav-link d-flex align-center justify-center cursor-pointer"
              style="border: 1px solid white; border-radius: 6px; padding: 0.5rem 1rem;"
              @click="$router.push({ name: 'authentication-register' })"
            >
              Daftar Sekarang
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
            :to="{ name: 'landing-page', hash: '#beranda' }"
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
            :to="{ name: 'landing-page', hash: `#${item.toLowerCase().replace(' ', '-')}` }"
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
            :to="{ name: 'media'}"
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
          <!-- <NavbarThemeSwitcher /> -->

          <!-- Authentication Buttons - Desktop -->
          <template v-if="!isAuthenticated">
            <!-- Login Button -->
            <template v-if="$vuetify.display.lgAndUp">
              <NavbarThemeSwitcher />
              <VBtn
                variant="outlined"
                color="white"
                class="text-white btn-navbar"
                size="default"
                @click="$router.push({ name: 'authentication-login' })"
              >
                Masuk
              </VBtn>
            </template>

            <template v-else>
              <NavbarThemeSwitcher />
              <VBtn
                variant="outlined"
                color="white"
                class="text-white btn-navbar"
                size="default"
                @click="$router.push({ name: 'authentication-login' })"
              >
                Masuk
              </VBtn>
            </template>

            <!-- Register Button -->
            <VBtn
              v-if="$vuetify.display.lgAndUp"
              variant="outlined"
              color="white"
              class="text-white btn-navbar"
              size="default"
              @click="$router.push({ name: 'authentication-register' })"
            >
              Daftar Sekarang
            </VBtn>
          </template>

          <!-- Profile Menu - Desktop (when authenticated) -->
          <template v-else>
            <!-- Profile Dropdown Menu - Desktop -->
            <VMenu
              v-model="isProfileMenuOpen"
              offset="10"
              location="bottom end"
              transition="slide-y-transition"
            >
              <template #activator="{ props }">
                <VBtn
                  v-bind="props"
                  icon
                  variant="text"
                  size="default"
                  class="profile-menu-btn"
                >
                  <VAvatar
                    size="32"
                    color="white"
                    class="text-primary"
                  >
                    <VIcon icon="tabler-user" color="white" />
                  </VAvatar>
                </VBtn>
            </template>

              <VCard min-width="200">
                <VList>
                  <!-- User Info -->
                  <VListItem class="pa-4">
                    <template #prepend>
                      <VAvatar size="40" color="primary">
                        <template v-if="avatarPreview">
                          <img
                            :src="avatarPreview"
                            alt="Avatar"
                            style="width: 100%; height: 100%; object-fit: cover;"
                          />
                        </template>
                        <VIcon v-else icon="tabler-user" />
                      </VAvatar>
                    </template>

                    <VListItemTitle class="font-weight-medium">
                      {{ playerData?.name || 'User' }}
                    </VListItemTitle>
                  </VListItem>

                  <VDivider />

                  <!-- Profile Link -->
                  <VListItem
                    @click="goToProfile"
                    class="cursor-pointer"
                  >
                    <template #prepend>
                      <VIcon icon="tabler-user" />
                    </template>
                    <VListItemTitle>Profile</VListItemTitle>
                  </VListItem>

                  <VDivider />

                  <!-- Logout Button - appears at bottom of popup -->
                  <VListItem
                    @click="onLogout"
                    class="cursor-pointer logout-item"
                  >
                    <template #prepend>
                      <VIcon icon="tabler-logout" color="error" />
                    </template>
                    <VListItemTitle class="text-error">Logout</VListItemTitle>
                  </VListItem>
                </VList>
              </VCard>
            </VMenu>
          </template>
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

// Profile Menu Button
.profile-menu-btn {
  &:hover {
    background: rgba(255, 255, 255, 0.1) !important;
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

// Mobile Auth Section
.mobile-auth-section {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 16px;
  
  .mobile-nav-link {
    padding: 12px 0;
    border-radius: 8px;
    
    &:hover {
      background: rgba(255, 255, 255, 0.1);
      padding-left: 8px;
      transition: all 0.2s ease;
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

// Logout Item Styling
.logout-item {
  &:hover {
    background: rgba(255, 0, 0, 0.1) !important;
  }
}
</style>
