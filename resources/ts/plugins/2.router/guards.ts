import { useAuthStore } from '@/stores/auth'
import { storeToRefs } from 'pinia'
import type { RouteNamedMap, _RouterTyped } from 'unplugin-vue-router'

export const setupGuards = (router: _RouterTyped<RouteNamedMap & { [key: string]: any }>) => {
  router.beforeEach(async (to) => {
    const authStore = useAuthStore()
    const { isLoggedIn } = storeToRefs(authStore)
    const role = authStore.role

    await authStore.checkSessionTimeout()
    const authPages = ['authentication-login', 'authentication-register']
    
    if (authPages.includes(to.name as string)) {      
      if (isLoggedIn.value) {
        if (role === 'admin') {
          return { name: 'dashboards-player-list' }
        } else if (role === 'user') {
          return { name: 'landing-page' }
        } else {
          return { name: 'landing-page' }
        }
      }
      return undefined
    }

    if (to.meta.public) return undefined

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

    const userRoutes = [
      'history', 'landing-page', 'media', 'media-id', 'club', 'player', 'player-id',
      'pricing', 'schedule-match', 'schedule-training', 'standing'
    ]

    const authRequiredRoutes = ['profile', 'dashboards-profile']

    const needsAuth = adminRoutes.includes(to.name as string) || 
                     userRoutes.includes(to.name as string) || 
                     authRequiredRoutes.includes(to.name as string) ||
                     to.name?.toString().includes('dashboard') || 
                     to.meta.requiresAuth

    if (needsAuth && !isLoggedIn.value) {
      return { name: 'authentication-login' }
    }

    if (isLoggedIn.value) {
      if (role === 'admin' && userRoutes.includes(to.name as string)) {
        return { name: 'dashboards-player-list' }
      }
      
      if (role === 'user' && adminRoutes.includes(to.name as string)) {
        return { name: 'landing-page' }
      }
      
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
