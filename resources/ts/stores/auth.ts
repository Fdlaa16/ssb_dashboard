import { defineStore } from 'pinia'
import { ref } from 'vue'

interface User {
  id: number
  name: string
  email: string
  role: string
  company_id?: number
}

interface UserData {
  user: User
  token: string
  role: string
  login_type: string
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(null)
  const role = ref<string | null>(null)
  const loginType = ref<string | null>(null)

  function storeUserData(userData: UserData) {
    console.log('Storing user data:', userData)
    user.value = userData.user
    token.value = userData.token
    role.value = userData.role
    loginType.value = userData.login_type

    console.log('stored user:', user.value)
    console.log('stored token:', token.value)
  }

  function deleteUserData() {
    user.value = null
    token.value = null
    role.value = null
    loginType.value = null
  }

  // Helper untuk check apakah user sudah login
  const isLoggedIn = computed(() => {
    return !!(user.value && token.value)
  })

  // Helper untuk check role
  const hasRole = (requiredRole: string) => {
    return role.value === requiredRole
  }

  // Helper untuk check apakah user adalah company user
  const isCompanyUser = computed(() => {
    return loginType.value === 'company' && (role.value === 'user')
  })

  return {
    user,
    token,
    role,
    loginType,
    isLoggedIn,
    isCompanyUser,
    storeUserData,
    deleteUserData,
    hasRole,
  }
}, {
  persist: true
})

if (import.meta.hot)
  import.meta.hot.accept(acceptHMRUpdate(useAuthStore, import.meta.hot))
