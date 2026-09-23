// src/api/axios.js

import axios from 'axios'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || '/api'

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    Accept: 'application/ld+json',
    'Content-Type': 'application/ld+json',
  },
})

// Public endpoints that must never send an Authorization header
const PUBLIC_ENDPOINTS = [
  '/login',
  '/login_check',
  '/users',
  '/companies',
  '/addresses',
  '/university',
  '/user_types',
]

api.interceptors.request.use((config) => {
  const rawUrl = String(config.url || '')
  const normalizedUrl = rawUrl.startsWith('/') ? rawUrl : `/${rawUrl}`
  const isAuthRoute = /^\/login(?:_check)?(?:$|[/?])/.test(normalizedUrl)
  const isPublic = isAuthRoute || PUBLIC_ENDPOINTS.some((path) => normalizedUrl.startsWith(path))

  if (!isPublic) {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
  }

  // Let the browser set the multipart boundary itself; our default JSON content-type breaks file uploads.
  if (typeof FormData !== 'undefined' && config.data instanceof FormData) {
    delete config.headers['Content-Type']
  }

  return config
})

// Clear expired / invalid token automatically
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      localStorage.removeItem('auth_meta')
    }
    return Promise.reject(error)
  },
)

//export default api
export default {
  listItems(ressponse, params = {}) {
    return api.get(`/${ressponse}`, { params })
  },
  getItem(ressponse, id) {
    return api.get(`/${ressponse}/${id}`)
  },
  createItem(ressponse, data, config = {}) {
    return api.post(`/${ressponse}`, data, config)
  },
  updateItem(ressponse, id, data, config = {}) {
    return api.put(`/${ressponse}/${id}`, data, config)
  },
  patchItem(ressponse, id, data, config = {}) {
    return api.patch(`/${ressponse}/${id}`, data, config)
  },
  deleteItem(ressponse, id) {
    return api.delete(`/${ressponse}/${id}`)
  },
}

// export default api
