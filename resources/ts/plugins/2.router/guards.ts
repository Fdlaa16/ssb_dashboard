import { useAuthStore } from '@/stores/auth'
import { canNavigate } from '@layouts/plugins/casl'
import type { RouteNamedMap, _RouterTyped } from 'unplugin-vue-router'


export const setupGuards = (router: _RouterTyped<RouteNamedMap & { [key: string]: any }>) => {
  // 👉 router.beforeEach
  router.beforeEach(to => {
    console.log('guards.ts jalan')
    const authStore = useAuthStore()

    const {user} = storeToRefs(authStore)
    const {isLoggedIn} = useAuth()
    
    /*
     * If it's a public route, continue navigation. This kind of pages are allowed to visited by login & non-login users. Basically, without any restrictions.
     * Examples of public routes are, 404, under maintenance, etc.
     */
    if (to.meta.public) {
      console.log('Public route, allowing access')
      return
    }

    /**
     * Check if user is logged in by checking if token & user data exists in pinia store
     */
    // const isLoggedIn = authStore.isLoggedIn

    // console.log('Auth check:', {
    //   isLoggedIn,
    //   user: authStore.user,
    //   token: authStore.token ? 'exists' : 'missing',
    //   route: to.name
    // })

    /*
      If user is logged in and is trying to access login like page, redirect to home
      else allow visiting the page
     */
    // Untuk Halaman yang boleh diakses hanya jika user belum login sama sekali misal halaman login
    // if (to.meta.unauthenticatedOnly) {
    //   if (isLoggedIn) {
    //     console.log('User already logged in, redirecting to home')
    //     return '/'
    //   } else {
    //     console.log('User not logged in, allowing access to login page')
    //     return undefined
    //   }
    // }

    console.log('currentPage', to, to.meta.public, isLoggedIn)
    // Check if user is trying to access protected route without login
    if (!isLoggedIn) {
      console.log('User not logged in, redirecting to login')

      // Check if route is dashboard
      if (to.name.includes('dashboard')) {
        console.log('Mau Akses Dashboard, harusnya cuman admin')
        return { 
          name: 'pages-authentication-login-v1-admin',
          query: {
            ...to.query,
            to: to.fullPath !== '/' ? to.path : undefined,
          },
         } // Sesuaikan dengan nama route login Anda
      } else {
        console.log('Mau Akses halaman harus login tapi bukan dashboard')
        return {
          name: 'pages-authentication-login-v1', // Sesuaikan dengan nama route login Anda
          query: {
            ...to.query,
            to: to.fullPath !== '/' ? to.path : undefined,
          },
        }
      }
    }

    // Check CASL permissions if user is logged in
    if (!canNavigate(to) && to.matched.length) {
      console.log('User does not have permission to access this route')
      return { name: 'not-authorized' }
    }

    console.log('All checks passed, allowing navigation')
  })
}
