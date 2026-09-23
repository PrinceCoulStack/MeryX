// src/composables/useAuth.js

import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.store'

export function useAuth() {
  const authStore = useAuthStore()
  const router = useRouter()

  const user = computed(() => authStore.user)
  const token = computed(() => authStore.token)

  const isAuthenticated = computed(() => !!authStore.token)

  const hasRole = (role) => {
    return authStore.user?.role === role
  }

  const isAdmin = computed(() => hasRole('ROLE_ADMIN'))
  const isStudent = computed(() => hasRole('ROLE_STUDENT'))
  const isUniversity = computed(() => hasRole('ROLE_UNIVERSITY'))
  const isCompany = computed(() => hasRole('ROLE_COMPANY'))

  const logout = () => {
    authStore.logout()
    router.push('/login')
  }

  return {
    user,
    token,
    isAuthenticated,

    isAdmin,
    isStudent,
    isUniversity,
    isCompany,

    hasRole,
    logout,
  }
}
