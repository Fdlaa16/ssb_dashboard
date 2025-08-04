import { useAuthStore } from '@/stores/auth'
import { canNavigate } from '@layouts/plugins/casl'
import { storeToRefs } from 'pinia'
import type { RouteNamedMap, _RouterTyped } from 'unplugin-vue-router'

export const setupGuards = (router: _RouterTyped<RouteNamedMap & { [key: string]: any }>) => {
  // 👉 router.beforeEach
  router.beforeEach(to => {
    console.log('Route guard executing for:', to.name)
    const authStore = useAuthStore()
    
    const { isLoggedIn, isAdmin, isRegularUser, canAccessAdminDashboard, canAccessUserPages } = storeToRefs(authStore)

    /*
     * Jika route adalah public, lanjutkan navigasi
     * Route public bisa diakses tanpa login (404, maintenance, dll)
     */
    if (to.meta.public) {
      console.log('Public route, allowing access')
      return
    }

    /*
     * ========== HANDLE AUTHENTICATED USERS TRYING TO ACCESS LOGIN PAGES ==========
     * Jika user sudah login dan mencoba akses halaman login, redirect ke dashboard mereka
     */
    if (to.meta.unauthenticatedOnly || 
        to.name === 'pages-authentication-login-v1' || 
        to.name === 'pages-authentication-login-v1-admin') {
      
      if (isLoggedIn.value) {
        console.log('User already logged in, redirecting to appropriate dashboard')
        
        // Redirect berdasarkan role
        if (isAdmin.value) {
          return { name: 'dashboard-admin' } // Sesuaikan dengan nama route dashboard admin
        } else if (isRegularUser.value) {
          return { name: 'front-pages-landing-page' } // Route untuk user biasa
        } else {
          return { name: 'dashboard' } // Default dashboard
        }
      } else {
        console.log('User not logged in, allowing access to login page')
        return undefined
      }
    }

    /*
     * ========== HANDLE UNAUTHENTICATED USERS ==========
     * Jika user belum login dan mencoba akses protected route
     */
    if (!isLoggedIn.value) {
      console.log('User not logged in, redirecting to appropriate login page')

      // Redirect ke login page yang sesuai berdasarkan route yang diakses
      if (to.name?.toString().includes('dashboard') || 
          to.name?.toString().includes('admin') ||
          to.meta.requiresAdmin) {
        
        console.log('Trying to access admin area, redirecting to admin login')
        return { 
          name: 'pages-authentication-login-v1-admin',
          query: {
            ...to.query,
            to: to.fullPath !== '/' ? to.path : undefined,
          },
        }
      } else {
        console.log('Trying to access user area, redirecting to user login')
        return {
          name: 'pages-authentication-login-v1',
          query: {
            ...to.query,
            to: to.fullPath !== '/' ? to.path : undefined,
          },
        }
      }
    }

    /*
     * ========== ROLE-BASED ACCESS CONTROL ==========
     * User sudah login, sekarang check apakah mereka punya akses ke route ini
     */

    // Check admin access - hanya admin yang bisa akses area admin
    if (to.meta.requiresAdmin || 
        to.name?.toString().includes('dashboard') ||
        to.name?.toString().includes('admin')) {
      
      if (!canAccessAdminDashboard.value) {
        console.log('User does not have admin access, redirecting to user area')
        return { 
          name: 'front-pages-landing-page',
          query: { error: 'access_denied' }
        }
      }
    }

    // Check user access - hanya user biasa yang bisa akses area user tertentu
    if (to.meta.requiresUser) {
      if (!canAccessUserPages.value) {
        console.log('Admin trying to access user-only area, redirecting to admin dashboard')
        return { 
          name: 'dashboard-admin',
          query: { error: 'wrong_access_area' }
        }
      }
    }

    // Check role-specific routes
    if (to.meta.roles && Array.isArray(to.meta.roles)) {
      const userRole = authStore.role
      if (!to.meta.roles.includes(userRole)) {
        console.log(`User role '${userRole}' not allowed for this route`)
        
        // Redirect berdasarkan role user
        if (isAdmin.value) {
          return { name: 'dashboard-admin', query: { error: 'insufficient_permissions' } }
        } else {
          return { name: 'front-pages-landing-page', query: { error: 'insufficient_permissions' } }
        }
      }
    }

    /*
     * ========== CASL PERMISSIONS CHECK ==========
     * Check CASL permissions jika user sudah login dan punya role yang sesuai
     */
    if (!canNavigate(to) && to.matched.length) {
      console.log('User does not have CASL permission to access this route')
      return { name: 'not-authorized' }
    }

    console.log('All checks passed, allowing navigation to:', to.name)
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
