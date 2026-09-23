// src/stores/auth.store.js

import { defineStore } from 'pinia'
import api from '@/api/axios'

const AUTH_META_KEY = 'auth_meta'

const ALLOWED_REDIRECT_TARGETS = new Set([
  '/super-admin',
  '/admin',
  '/student',
  '/university',
  '/company',
  '/pending-approval',
])

const normalizeRoleName = (value) => {
  if (!value) return null
  return String(value)
    .trim()
    .toUpperCase()
    .replace(/^ROLE_/, '')
    .replace(/\s+/g, '_')
    .replace(/-/g, '_')
}

const normalizeInterfaceKey = (value) => {
  if (!value) return null
  return String(value).trim().toLowerCase().replace(/\s+/g, '-').replace(/_/g, '-')
}

const interfaceDefaultTarget = (value) => {
  const normalized = normalizeInterfaceKey(value)

  if (normalized === 'admin' || normalized === 'super-admin') return '/super-admin'
  if (normalized === 'university') return '/university'
  if (normalized === 'student') return '/student'
  if (normalized === 'company') return '/company'

  return null
}

const normalizeSymfonyRole = (value) => {
  const role = normalizeRoleName(value)
  return role ? `ROLE_${role}` : null
}

const roleDefaultTarget = (role, interfaceKey = null) => {
  const normalized = normalizeRoleName(role)
  if (normalized === 'ADMIN' || normalized === 'SUPER_ADMIN') return '/super-admin'
  if (normalized === 'UNIVERSITY') return '/university'
  if (normalized === 'STUDENT') return '/student'
  if (normalized === 'COMPANY') return '/company'

  return interfaceDefaultTarget(interfaceKey) || '/unauthorized'
}

const isSafeTarget = (target) => typeof target === 'string' && target.startsWith('/')

const safeRedirectTarget = (target, role, interfaceKey = null) => {
  if (isSafeTarget(target) && ALLOWED_REDIRECT_TARGETS.has(target)) return target
  return roleDefaultTarget(role, interfaceKey)
}

const parseJson = (value, fallback) => {
  try {
    return value ? JSON.parse(value) : fallback
  } catch {
    return fallback
  }
}

const parseHydraCollection = (data) => {
  if (Array.isArray(data?.['hydra:member'])) return data['hydra:member']
  if (Array.isArray(data)) return data
  return []
}

const toNumberId = (value) => {
  if (typeof value === 'number' && Number.isFinite(value)) return value
  if (typeof value === 'string') {
    const parts = value.split('/')
    const parsed = Number(parts[parts.length - 1])
    return Number.isFinite(parsed) ? parsed : null
  }
  if (value && typeof value === 'object') {
    if (typeof value.id === 'number' && Number.isFinite(value.id)) return value.id
    if (typeof value['@id'] === 'string') {
      const parts = value['@id'].split('/')
      const parsed = Number(parts[parts.length - 1])
      return Number.isFinite(parsed) ? parsed : null
    }
  }
  return null
}

const normalizeCompareText = (value) =>
  String(value || '')
    .trim()
    .toLowerCase()

const isApprovedUniversity = (item) => normalizeCompareText(item?.status) === 'approved'

const extractRoleFromPayload = (payload) => {
  if (!payload || typeof payload !== 'object') return null

  return (
    payload.role ||
    payload.user?.role ||
    payload.userTypeId?.role ||
    payload.user?.userTypeId?.role ||
    payload.userTypeId?.name ||
    payload.roles?.[0] ||
    payload.user?.roles?.[0] ||
    payload.user?.userTypeId?.name ||
    null
  )
}

const extractRolesArray = (payload, roleValue) => {
  const roles = payload?.roles || payload?.user?.roles
  if (Array.isArray(roles) && roles.length) {
    return roles.map((item) => normalizeSymfonyRole(item)).filter(Boolean)
  }

  const fallbackRole = normalizeSymfonyRole(roleValue)
  return fallbackRole ? [fallbackRole] : []
}

export const useAuthStore = defineStore('auth', {
  state: () => {
    const storedMeta = parseJson(localStorage.getItem(AUTH_META_KEY), {})

    return {
      token: localStorage.getItem('token') || null,
      user: parseJson(localStorage.getItem('user'), null),
      role: storedMeta.role || null,
      roles: Array.isArray(storedMeta.roles) ? storedMeta.roles : [],
      interface: storedMeta.interface || null,
      interfaceKey: storedMeta.interfaceKey || null,
      redirectTarget: storedMeta.redirectTarget || null,
      permissions: Array.isArray(storedMeta.permissions) ? storedMeta.permissions : [],
      loading: false,
      isBootstrapping: false,
      bootstrapped: false,
      authError: null,
      bootstrapPromise: null,
    }
  },

  getters: {
    isAuthenticated: (state) => !!state.token,

    normalizedRole: (state) => normalizeSymfonyRole(state.role),

    resolvedRedirectTarget(state) {
      return safeRedirectTarget(state.redirectTarget, state.role, state.interfaceKey)
    },

    isAdmin: (state) => {
      const role = normalizeRoleName(state.role)
      return role === 'ADMIN' || role === 'SUPER_ADMIN'
    },

    isStudent: (state) => normalizeRoleName(state.role) === 'STUDENT',

    isUniversity: (state) => normalizeRoleName(state.role) === 'UNIVERSITY',

    isCompany: (state) => normalizeRoleName(state.role) === 'COMPANY',
  },

  actions: {
    persistAuthMeta() {
      localStorage.setItem(
        AUTH_META_KEY,
        JSON.stringify({
          role: this.role,
          roles: this.roles,
          interface: this.interface,
          interfaceKey: this.interfaceKey,
          redirectTarget: this.redirectTarget,
          permissions: this.permissions,
        }),
      )
    },

    applyAuthPayload(payload) {
      const roleValue = extractRoleFromPayload(payload)

      this.user = payload?.user || payload || null
      this.role = normalizeRoleName(roleValue)
      this.roles = extractRolesArray(payload, roleValue)
      this.interface =
        payload?.interface || payload?.user?.userTypeId?.name || payload?.userTypeId?.name || null
      this.interfaceKey = payload?.interfaceKey || this.interface?.toLowerCase() || null
      this.redirectTarget = safeRedirectTarget(
        payload?.redirectTarget,
        this.role,
        this.interfaceKey,
      )
      this.permissions = Array.isArray(payload?.permissions) ? payload.permissions : []

      localStorage.setItem('user', JSON.stringify(this.user))
      this.persistAuthMeta()
    },

    resolveRouteByRole() {
      return this.resolvedRedirectTarget
    },

    clearAuthState({ keepBootstrapState = true, keepAuthError = false } = {}) {
      this.token = null
      this.user = null
      this.role = null
      this.roles = []
      this.interface = null
      this.interfaceKey = null
      this.redirectTarget = null
      this.permissions = []

      if (!keepAuthError) {
        this.authError = null
      }

      if (keepBootstrapState) {
        this.bootstrapped = true
        this.isBootstrapping = false
      } else {
        this.bootstrapped = false
        this.isBootstrapping = false
      }

      localStorage.removeItem('token')
      localStorage.removeItem('user')
      localStorage.removeItem(AUTH_META_KEY)
    },

    async fetchMe() {
      if (!this.token) {
        throw new Error('Missing token')
      }

      try {
        console.info('[auth] Calling /api/me...')
        const response = await api.listItems('me')
        this.applyAuthPayload(response.data)
        console.info('[auth] /api/me success', {
          role: this.role,
          interfaceKey: this.interfaceKey,
          target: this.redirectTarget,
        })
        return this.user
      } catch (error) {
        const status = error?.response?.status
        console.warn('[auth] /api/me failed', {
          status,
          data: error?.response?.data,
        })

        // Only clear auth on 401 if we don't have user data from login
        if (status === 401 && !this.user) {
          this.clearAuthState({ keepBootstrapState: true })
          this.authError = 'Your session has expired. Please sign in again.'
          throw error
        }

        // If we already have user data from login, don't fail on /me 401
        if (status === 401 && this.user) {
          console.info('[auth] /me returned 401 but user already authenticated from login response')
          return this.user
        }

        throw error
      }
    },

    async bootstrapAuth() {
      if (this.bootstrapped) return
      if (this.bootstrapPromise) {
        await this.bootstrapPromise
        return
      }

      this.bootstrapPromise = (async () => {
        this.isBootstrapping = true
        this.authError = null
        console.info('[auth] Bootstrapping auth...')

        try {
          if (!this.token) {
            this.user = null
            localStorage.removeItem('user')
            localStorage.removeItem(AUTH_META_KEY)
            console.info('[auth] No token found during bootstrap.')
            return
          }

          // If we already have user data from login, skip /me call
          if (this.user && this.role && this.interfaceKey) {
            console.info('[auth] User already authenticated from login, skipping /me call')
            return
          }

          await this.fetchMe()
        } catch (error) {
          if (error?.response?.status !== 401) {
            console.warn('[auth] Bootstrap failed unexpectedly.')
          }
          // Don't throw error during bootstrap - allow app to continue even if /me fails
        } finally {
          this.bootstrapped = true
          this.isBootstrapping = false
          this.bootstrapPromise = null
        }
      })()

      await this.bootstrapPromise
    },

    async login(credentials) {
      this.authError = null
      this.loading = true

      try {
        console.info('[auth] Login request started', { email: credentials?.email })

        let response = null
        try {
          response = await api.createItem(
            'login',
            {
              email: credentials?.email,
              password: credentials?.password,
            },
            {
              headers: {
                Accept: 'application/json, application/ld+json',
                'Content-Type': 'application/json',
              },
            },
          )
        } catch (error) {
          if (error?.response?.status === 404) {
            response = await api.createItem(
              'login_check',
              {
                _username: credentials?.email,
                _password: credentials?.password,
              },
              {
                headers: {
                  Accept: 'application/json, application/ld+json',
                  'Content-Type': 'application/json',
                },
              },
            )
          } else {
            throw error
          }
        }

        const token =
          response?.data?.token ||
          response?.data?.access_token ||
          response?.data?.jwt ||
          response?.data?.id_token ||
          response?.data?.data?.token

        if (!token) {
          this.authError = 'Unable to sign in right now. Please try again.'
          this.clearAuthState({ keepBootstrapState: false, keepAuthError: true })
          return { ok: false, status: 500, message: this.authError }
        }

        this.token = token
        localStorage.setItem('token', token)
        console.info('[auth] Token stored in localStorage.')

        if (response?.data?.user || response?.data?.role || response?.data?.roles) {
          this.applyAuthPayload(response.data)
        } else {
          await this.fetchMe()
        }
        this.bootstrapped = true
        this.isBootstrapping = false

        console.info('[auth] Login success', {
          role: this.role,
          roles: this.roles,
          interface: this.interface,
          interfaceKey: this.interfaceKey,
          target: this.redirectTarget,
        })

        return {
          ok: true,
          status: 200,
          redirectTarget: this.resolveRouteByRole(),
        }
      } catch (error) {
        const status = error?.response?.status
        const payload = error?.response?.data
        const backendMessage =
          payload?.message || payload?.detail || payload?.['hydra:description'] || error?.message

        if (status === 401) {
          this.authError = backendMessage || 'Invalid credentials.'
        } else if (status === 403) {
          this.authError = backendMessage || 'Your account is not allowed to sign in.'
        } else if (status === 400) {
          this.authError =
            backendMessage || 'Invalid login payload. Please check email and password fields.'
        } else if (status === 404) {
          this.authError =
            'Login endpoint not found. Please check backend auth route configuration.'
        } else if (status === 415) {
          this.authError =
            'Unsupported login content type on backend. Accept application/json or application/ld+json.'
        } else if (!error?.response) {
          this.authError =
            'Unable to reach authentication server. Verify backend is running and CORS allows this frontend origin.'
        } else {
          this.authError = 'Unable to sign in right now. Please try again.'
        }

        this.clearAuthState({ keepBootstrapState: false, keepAuthError: true })

        console.warn('[auth] Login failed', {
          status,
          message: this.authError,
          raw: error?.response?.data,
        })

        return { ok: false, status, message: this.authError }
      } finally {
        this.loading = false
      }
    },

    async registerCompany(userData, companyData, addressData = null) {
      this.authError = null

      try {
        this.loading = true

        let addressId = null
        if (addressData) {
          const addressResponse = await api.createItem('addresses', addressData)
          addressId = Number(addressResponse.data?.id ?? addressResponse.data?.AddressId ?? 0)
        }

        const userPayload = {
          ...userData,
          ...(addressId
            ? {
                AddressId: addressId,
                addressId,
                address: `/api/addresses/${addressId}`,
              }
            : {}),
        }

        const userResponse = await api.createItem('users', userPayload)
        const userId = Number(userResponse.data?.id)
        const userEmail = userResponse.data?.email || userData.email || ''

        const companyPayload = {
          ...companyData,
          userId,
          addressId,
          AddressId: addressId,
          email: userEmail,
        }

        await api.createItem('companies', companyPayload)
        return { ok: true }
      } catch (error) {
        const payload = error?.response?.data
        const message =
          payload?.detail || payload?.message || payload?.['hydra:description'] || error.message
        return { ok: false, message }
      } finally {
        this.loading = false
      }
    },

    async registerUniversity(userData, universityData, addressData = null) {
      this.authError = null

      try {
        this.loading = true

        let addressId = null
        if (addressData) {
          const addressResponse = await api.createItem('addresses', addressData)
          addressId = Number(addressResponse.data?.id ?? addressResponse.data?.AddressId ?? 0)
        }

        const userPayload = {
          ...userData,
          ...(addressId
            ? {
                AddressId: addressId,
                addressId,
                address: `/api/addresses/${addressId}`,
              }
            : {}),
        }

        const userResponse = await api.createItem('users', userPayload)
        const userId = Number(userResponse.data?.id)
        const userEmail = userResponse.data?.email || userData.email || ''

        const universityPayload = {
          ...universityData,
          userId,
          addressId,
          AddressId: addressId,
          email: userEmail,
        }

        await api.createItem('university', universityPayload)
        return { ok: true }
      } catch (error) {
        const payload = error?.response?.data
        const message =
          payload?.detail || payload?.message || payload?.['hydra:description'] || error.message
        return { ok: false, message }
      } finally {
        this.loading = false
      }
    },

    async registerStudent(userData, studentProfileData) {
      this.authError = null

      try {
        this.loading = true

        const universityResponse = await api.listItems('university')
        const universities = parseHydraCollection(universityResponse.data)

        const requestedUniversityName = normalizeCompareText(studentProfileData.universityName)
        const requestedUniversityEmail = normalizeCompareText(studentProfileData.universityEmail)

        const approvedUniversities = universities.filter(isApprovedUniversity)
        const searchableUniversities = approvedUniversities.length
          ? approvedUniversities
          : universities

        // Preferred path: exact name + email when both are available.
        let matchedUniversity = searchableUniversities.find((item) => {
          const itemName = normalizeCompareText(item?.name)
          const itemEmail = normalizeCompareText(
            item?.email || item?.userId?.email || item?.user?.email,
          )
          return itemName === requestedUniversityName && itemEmail === requestedUniversityEmail
        })

        // Fallback path: some backends return university list items without email.
        if (!matchedUniversity) {
          const sameNameCandidates = searchableUniversities.filter((item) => {
            const itemName = normalizeCompareText(item?.name)
            return itemName === requestedUniversityName
          })

          if (sameNameCandidates.length === 1) {
            matchedUniversity = sameNameCandidates[0]
          }
        }

        if (!matchedUniversity) {
          return {
            ok: false,
            message:
              'Universite introuvable avec ce nom et cet email. Verifiez les informations ou contactez votre universite.',
          }
        }

        const universityId = toNumberId(matchedUniversity)
        if (!universityId) {
          return {
            ok: false,
            message: "Impossible d'identifier l'universite selectionnee.",
          }
        }

        const resolvedUniversityIri = matchedUniversity['@id'] || `/api/university/${universityId}`
        const resolvedUniversityName = matchedUniversity.name || studentProfileData.universityName
        const resolvedUniversityEmail =
          matchedUniversity.email ||
          matchedUniversity.userId?.email ||
          matchedUniversity.user?.email ||
          studentProfileData.universityEmail

        const userPayload = {
          ...userData,
          status: 'pending',
          isActived: true,
          userTypeId: userData.userTypeId || '/api/user_types/4',
          universityId: resolvedUniversityIri,
          universityName: resolvedUniversityName,
          universityEmail: resolvedUniversityEmail,
        }

        const userResponse = await api.createItem('users', userPayload)
        const createdUserId = Number(userResponse.data?.id)

        if (!createdUserId) {
          return {
            ok: false,
            message: "Le compte etudiant n'a pas pu etre cree.",
          }
        }

        const studentPayload = {
          fullName: studentProfileData.fullName,
          gender: studentProfileData.gender || 'Male',
          gpa: studentProfileData.gpa || '0.00',
          profileCompletion: studentProfileData.profileCompletion ?? 15,
          profileUrl: studentProfileData.profileUrl || '',
          isApproved: false,
          status: 'pending',
          userId: `/api/users/${createdUserId}`,
          universityId: resolvedUniversityIri,
          bio: JSON.stringify({
            email: userData.email,
            phone: userData.phone || '',
            program: studentProfileData.program || '',
            level: studentProfileData.level || '',
            universityName: resolvedUniversityName,
            universityEmail: resolvedUniversityEmail,
            status: 'pending',
          }),
        }

        await api.createItem('studentProfiles', studentPayload)

        return { ok: true }
      } catch (error) {
        const payload = error?.response?.data
        const message =
          payload?.detail || payload?.message || payload?.['hydra:description'] || error.message
        return { ok: false, message }
      } finally {
        this.loading = false
      }
    },

    logout() {
      this.clearAuthState({ keepBootstrapState: true })
    },

    setUser(user) {
      this.user = user
      localStorage.setItem('user', JSON.stringify(user))
    },
  },
})
