import { useAuthStore } from '@/stores/auth'
import { storeToRefs } from 'pinia'
import type { RouteNamedMap, _RouterTyped } from 'unplugin-vue-router'

export const setupGuards = (router: _RouterTyped<RouteNamedMap & { [key: string]: any }>) => {
  router.beforeEach(async (to) => {
    const authStore = useAuthStore()
    const { isLoggedIn } = storeToRefs(authStore)
    const role = authStore.role

    // Check session timeout sebelum proses lainnya
    await authStore.checkSessionTimeout()

    // Handle authentication pages (login & register) - PRIORITAS PERTAMA
    const authPages = ['authentication-login', 'authentication-login-v1-admin', 'authentication-register']
    
    if (authPages.includes(to.name as string)) {
      console.log('Accessing auth page:', to.name, 'isLoggedIn:', isLoggedIn.value, 'role:', role) // Debug log
      
      if (isLoggedIn.value) {
        console.log('User is logged in, redirecting...') // Debug log
        // Redirect berdasarkan role user
        if (role === 'admin') {
          return { name: 'dashboards-player-list' }
        } else if (role === 'user') {
          return { name: 'landing-page' }
        } else {
          // Fallback untuk role lain yang mungkin ada
          return { name: 'landing-page' }
        }
      }
      // Jika belum login, biarkan akses ke halaman auth
      return undefined
    }

    // Skip public routes (setelah pengecekan auth pages)
    if (to.meta.public) return undefined

    // Define admin-only routes
    const adminRoutes = [
      'dashboards-club-add', 'dashboards-club-edit-id', 'dashboards-club-list',
      'dashboards-media-add', 'dashboards-media-edit-id', 'dashboards-media-list',
      'dashboards-player-add', 'dashboards-player-edit-id', 'dashboards-player-list',
      'dashboards-schedule-match-add', 'dashboards-schedule-match-edit-id', 'dashboards-schedule-match-list',
      'dashboards-schedule-training-add', 'dashboards-schedule-training-edit-id', 'dashboards-schedule-training-list',
      'dashboards-slide-home-add', 'dashboards-slide-home-edit-id', 'dashboards-slide-home-list',
      'dashboards-stadium-add', 'dashboards-stadium-edit-id', 'dashboards-stadium-list',
      'dashboards-standing-add', 'dashboards-standing-edit-id', 'dashboards-standing-list',
      'dashboards-structure-add', 'dashboards-structure-edit-id', 'dashboards-structure-list'
    ]

    // Define user-only routes
    const userRoutes = [
      'history', 'landing-page', 'media', 'media-id', 'club', 'player', 'player-id',
      'pricing', 'schedule-match', 'schedule-training', 'standing'
    ]

    // Define routes that require authentication (both admin and user)
    const authRequiredRoutes = ['profile', 'dashboards-profile']

    // Check if route requires authentication
    const needsAuth = adminRoutes.includes(to.name as string) || 
                     userRoutes.includes(to.name as string) || 
                     authRequiredRoutes.includes(to.name as string) ||
                     to.name?.toString().includes('dashboard') || 
                     to.meta.requiresAuth

    // Redirect to login if not authenticated and route needs auth
    if (needsAuth && !isLoggedIn.value) {
      return { name: 'authentication-login' }
    }

    // Role-based access control
    if (isLoggedIn.value) {
      // Admin trying to access user-only routes
      if (role === 'admin' && userRoutes.includes(to.name as string)) {
        return { name: 'dashboards-player-list' }
      }
      
      // User trying to access admin-only routes
      if (role === 'user' && adminRoutes.includes(to.name as string)) {
        return { name: 'landing-page' }
      }
      
      // Additional checks for routes containing specific keywords
      if (role === 'admin' && (to.name?.toString().includes('user') || to.meta.requiresUser)) {
        return { name: 'dashboards-player-list' }
      }
      
      if (role === 'user' && (to.name?.toString().includes('dashboard') || to.name?.toString().includes('admin'))) {
        return { name: 'landing-page' }
      }
    }

    return undefined
  })
}
