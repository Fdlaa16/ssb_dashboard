import { useAuthStore } from '@/stores/auth'
import { canNavigate } from '@layouts/plugins/casl'
import { storeToRefs } from 'pinia'
import type { RouteNamedMap, _RouterTyped } from 'unplugin-vue-router'

export const setupGuards = (router: _RouterTyped<RouteNamedMap & { [key: string]: any }>) => {
  // 👉 router.beforeEach
  router.beforeEach(to => {
    const authStore = useAuthStore()
    const { isLoggedIn, isAdmin, isRegularUser, canAccessAdminDashboard, canAccessUserPages } = storeToRefs(authStore)

    // 🔓 Jika route public, izinkan langsung
    if (to.meta.public) {
      console.log('Public route, allowing access')
      return undefined
    }

    // ✅ Jika belum login DAN route tidak membutuhkan login (misal: tidak pakai meta.requiresAuth), biarkan saja
    if (!isLoggedIn.value && !to.meta.requiresAuth && !to.meta.requiresAdmin && !to.meta.requiresUser) {
      console.log('Route does not require auth and user not logged in — allowing')
      return undefined
    }

    // 🛑 Jika user belum login dan route butuh auth
    if (!isLoggedIn.value) {
      console.log('User not logged in, redirecting to appropriate login page')

      if (to.name?.toString().includes('dashboard') || to.name?.toString().includes('admin') || to.meta.requiresAdmin) {
        return { 
          name: 'authentication-login-v1-admin',
          query: { ...to.query, to: to.fullPath !== '/' ? to.path : undefined },
        }
      } else {
        return {
          name: 'authentication-login',
          query: { ...to.query, to: to.fullPath !== '/' ? to.path : undefined },
        }
      }
    }

    // ✋ Cegah akses login page jika sudah login
    if (to.meta.unauthenticatedOnly || to.name === 'authentication-login' || to.name === 'authentication-login-v1-admin') {
      if (isLoggedIn.value) {
        console.log('User already logged in, redirecting to appropriate dashboard')
        if (isAdmin.value) return { name: 'dashboard-admin' }
        if (isRegularUser.value) return { name: 'front-pages-landing-page' }
        return { name: 'dashboard' }
      }
      return undefined
    }

    // 👮 Role-based access
    if (to.meta.requiresAdmin || to.name?.toString().includes('dashboard') || to.name?.toString().includes('admin')) {
      if (!canAccessAdminDashboard.value) {
        return { name: 'front-pages-landing-page' }
      }
    }

    if (to.meta.requiresUser && !canAccessUserPages.value) {
      return { name: 'dashboard-admin', query: { error: 'wrong_access_area' } }
    }

    if (to.meta.roles && Array.isArray(to.meta.roles)) {
      const userRole = authStore.role
      if (!to.meta.roles.includes(userRole)) {
        return isAdmin.value
          ? { name: 'dashboard-admin', query: { error: 'insufficient_permissions' } }
          : { name: 'front-pages-landing-page', query: { error: 'insufficient_permissions' } }
      }
    }

    if (!canNavigate(to) && to.matched.length) {
      return { name: 'not-authorized' }
    }

    return undefined
  })


  /*
   * ========== AFTER EACH HOOK ==========
   * Optional: untuk logging atau tracking
   */
  router.afterEach((to, from) => {
    console.log(`Navigation completed: ${from.name} -> ${to.name}`)
    
    // Optional: Track route changes for analytics
    // trackPageView(to.name, to.path)
  })
}
