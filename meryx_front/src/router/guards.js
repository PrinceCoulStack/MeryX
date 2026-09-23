import { useAuthStore } from '@/stores/auth.store'

const normalizeRoleName = (value) => {
  if (!value) return null
  return String(value)
    .trim()
    .toUpperCase()
    .replace(/^ROLE_/, '')
    .replace(/\s+/g, '_')
    .replace(/-/g, '_')
}

const isPendingApproval = (auth) => {
  const statusCandidates = [
    auth.user?.status,
    auth.user?.studentProfile?.status,
    auth.user?.studentProfileId?.status,
  ]

  const hasPendingStatus = statusCandidates.some((value) =>
    String(value || '')
      .trim()
      .toLowerCase()
      .includes('pend'),
  )

  if (hasPendingStatus) return true

  const approvalFlags = [
    auth.user?.isApproved,
    auth.user?.studentProfile?.isApproved,
    auth.user?.studentProfileId?.isApproved,
  ].filter((value) => typeof value === 'boolean')

  if (!approvalFlags.length) return false
  return approvalFlags.some((value) => value === false)
}

export async function authGuard(to) {
  const auth = useAuthStore()

  if (!auth.bootstrapped && !auth.isBootstrapping) {
    await auth.bootstrapAuth()
  } else if (auth.isBootstrapping) {
    await auth.bootstrapAuth()
  }

  const isLoggedIn = auth.isAuthenticated

  // If route requires login and user is not logged in
  if (to.meta.requiresAuth && !isLoggedIn) {
    return '/login'
  }

  // If user is already authenticated, avoid reopening login page.
  // If we cannot resolve a valid dashboard, drop stale auth state and show login.
  if (to.path === '/login' && isLoggedIn) {
    const target = auth.resolveRouteByRole()

    if (!target || target === '/unauthorized') {
      auth.clearAuthState({ keepBootstrapState: true })
      return true
    }

    console.info('[auth] Already authenticated. Redirecting from login page.', { target })
    return target
  }

  // Role-based access control
  // if (requiredRole && normalizedRequiredRole && !userHasRole(normalizedRequiredRole)) {
  //   return '/unauthorized'
  // }

  // Company users that are not yet approved are redirected to waiting screen
  if (
    isLoggedIn &&
    normalizeRoleName(auth.role) === 'COMPANY' &&
    isPendingApproval(auth) &&
    to.path.startsWith('/company')
  ) {
    return '/pending-approval'
  }

  // Student users that are not yet approved are redirected to waiting screen.
  if (
    isLoggedIn &&
    normalizeRoleName(auth.role) === 'STUDENT' &&
    isPendingApproval(auth) &&
    to.path.startsWith('/student')
  ) {
    return '/pending-approval'
  }

  return true
}
