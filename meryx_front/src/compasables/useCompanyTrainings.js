import { computed, ref } from 'vue'
import api from '@/api/axios'

const trainings = ref([])
const isLoadingTrainings = ref(false)
const trainingError = ref('')

const parseEntityId = (value) => {
  if (!value && value !== 0) return 0

  if (typeof value === 'number') return value

  if (typeof value === 'string') {
    const trimmed = value.trim()
    if (!trimmed) return 0
    const match = trimmed.match(/(?:\/)?(\d+)$/)
    if (match) return Number(match[1])
    const number = Number(trimmed)
    return Number.isFinite(number) ? number : 0
  }

  if (typeof value === 'object') {
    return parseEntityId(
      value.id ||
        value['@id'] ||
        value.companyId ||
        value.company ||
        value.company?.id ||
        value.company?.['@id'] ||
        value.companyId?.id ||
        value.companyId?.['@id'],
    )
  }

  return 0
}

const normalizeApiData = (data) => {
  if (typeof data !== 'string') return data

  try {
    return JSON.parse(data)
  } catch {
    return data
  }
}

const extractCollection = (data) => {
  const normalized = normalizeApiData(data)

  if (Array.isArray(normalized)) return normalized
  if (Array.isArray(normalized?.['hydra:member'])) return normalized['hydra:member']
  if (Array.isArray(normalized?.items)) return normalized.items
  return []
}

const getCurrentCompanyId = () => {
  try {
    const storedUser = localStorage.getItem('user')
    if (!storedUser) return 0

    const parsedUser = JSON.parse(storedUser)
    const candidates = [
      parsedUser?.companyId,
      parsedUser?.company_id,
      parsedUser?.company,
      parsedUser?.companyProfileId,
      parsedUser?.company_profile_id,
      parsedUser?.companyProfile,
      parsedUser?.company_profile,
      parsedUser?.companyProfile?.companyId,
      parsedUser?.companyProfile?.company,
      parsedUser?.company_profile?.companyId,
      parsedUser?.company_profile?.company,
      parsedUser?.profile?.companyId,
      parsedUser?.profile?.company,
      parsedUser?.profile?.id, // Company profile is stored as profile.id
      parsedUser?.profile, // Profile object itself
    ]

    for (const value of candidates) {
      const resolved = parseEntityId(value)
      if (resolved) return resolved
    }

    return 0
  } catch {
    return 0
  }
}

const normalizeRequirements = (value) => {
  if (Array.isArray(value)) {
    return value
      .map((item) => {
        if (typeof item === 'string') return item.trim()
        if (item && typeof item === 'object') {
          return item.label || item.name || item.title || ''
        }
        return ''
      })
      .filter(Boolean)
  }

  if (typeof value === 'string') {
    const trimmed = value.trim()
    if (!trimmed) return []

    try {
      const parsed = JSON.parse(trimmed)
      if (Array.isArray(parsed)) return normalizeRequirements(parsed)
    } catch {
      // ignore invalid JSON and continue to split as CSV
    }

    return trimmed
      .replace(/\[|\]|{|}/g, '')
      .split(',')
      .map((item) => item.trim().replace(/^['"]|['"]$/g, ''))
      .filter(Boolean)
  }

  return []
}

const formatDateValue = (value) => {
  if (!value) return '—'

  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value

  return date.toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })
}

const mapTraining = (item) => {
  const companyId = parseEntityId(item?.companyId)
  const requirements = normalizeRequirements(item?.trainingRequirements || item?.requirements || [])

  return {
    id: Number(item.id),
    companyId,
    title: item.title || 'Untitled training',
    type: item.type || 'Bootcamp',
    mode: item.mode || 'Remote',
    location: item.location || 'Remote',
    durationLabel: item.durationLabel || item.duration || 'N/A',
    seatCount: Number(item.seatCount ?? item.seats ?? 0),
    description: item.description || '',
    status: item.status || 'Open',
    startAt: item.startAt || item.startDate || null,
    endAt: item.endAt || item.endDate || null,
    publishedAt: item.publishedAt || item.createdAt || null,
    createdAt: item.createdAt || null,
    updatedAt: item.updatedAt || null,
    requirements,
    raw: item,
  }
}

const fetchTrainings = async (force = false) => {
  if (!force && trainings.value.length) return trainings.value

  isLoadingTrainings.value = true
  trainingError.value = ''

  try {
    const response = await api.listItems('trainings')
    trainings.value = extractCollection(response.data).map(mapTraining)
    return trainings.value
  } catch (error) {
    const message =
      error?.response?.data?.detail ||
      error?.response?.data?.message ||
      error?.message ||
      'Unable to load trainings.'
    trainingError.value = message
    return []
  } finally {
    isLoadingTrainings.value = false
  }
}

const createTraining = async (payload) => {
  trainingError.value = ''
  const now = new Date().toISOString()
  const companyId = getCurrentCompanyId()
  if (!companyId) {
    trainingError.value = 'Company profile is required to publish a training.'
    return null
  }

  const requirements = normalizeRequirements(payload.requirements || [])

  const body = {
    ...payload,
    companyId: payload.companyId || `/api/companies/${companyId}`,
    title: payload.title,
    type: payload.type || 'Bootcamp',
    mode: payload.mode || 'Remote',
    location: payload.location || 'Remote',
    durationLabel: payload.durationLabel || payload.duration || 'N/A',
    seatCount: payload.seatCount ?? payload.seats ?? 1,
    description: payload.description || '',
    status: payload.status || 'Open',
    startAt: payload.startAt || payload.startDate || now,
    endAt: payload.endAt || payload.endDate || now,
    publishedAt: payload.publishedAt || now,
    createdAt: payload.createdAt || now,
    updatedAt: payload.updatedAt || now,
    trainingRequirements: requirements.map((label) => ({
      label,
      isRequired: true,
      isDeleted: false,
      isEnabled: true,
      createdAt: now,
      updatedAt: now,
    })),
  }

  try {
    const response = await api.createItem('trainings', body)
    const created = mapTraining(response.data || body)
    trainings.value = [created, ...trainings.value]
    return created
  } catch (error) {
    const message =
      error?.response?.data?.detail ||
      error?.response?.data?.message ||
      error?.message ||
      'Unable to create training.'
    trainingError.value = message
    return null
  }
}

const updateTraining = async (id, payload) => {
  trainingError.value = ''
  const now = new Date().toISOString()
  const companyId = getCurrentCompanyId()
  if (!companyId) {
    trainingError.value = 'Company profile is required to update this training.'
    return null
  }

  const requirements = normalizeRequirements(payload.requirements || [])

  const body = {
    ...payload,
    companyId: payload.companyId || `/api/companies/${companyId}`,
    title: payload.title,
    type: payload.type || 'Bootcamp',
    mode: payload.mode || 'Remote',
    location: payload.location || 'Remote',
    durationLabel: payload.durationLabel || payload.duration || 'N/A',
    seatCount: payload.seatCount ?? payload.seats ?? 1,
    description: payload.description || '',
    status: payload.status || 'Open',
    startAt: payload.startAt || payload.startDate || now,
    endAt: payload.endAt || payload.endDate || now,
    publishedAt: payload.publishedAt || now,
    updatedAt: payload.updatedAt || now,
    trainingRequirements: requirements.map((label) => ({
      label,
      isRequired: true,
      isDeleted: false,
      isEnabled: true,
      createdAt: now,
      updatedAt: now,
    })),
  }

  try {
    const response = await api.updateItem('trainings', id, body)
    const updated = mapTraining(response.data || { id, ...body })
    trainings.value = trainings.value.map((item) =>
      Number(item.id) === Number(id) ? updated : item,
    )
    return updated
  } catch (error) {
    const message =
      error?.response?.data?.detail ||
      error?.response?.data?.message ||
      error?.message ||
      'Unable to update training.'
    trainingError.value = message
    return null
  }
}

const companyTrainings = computed(() =>
  trainings.value.filter((item) => Number(item.companyId) === Number(getCurrentCompanyId())),
)

const getTrainingById = (id) =>
  trainings.value.find((item) => Number(item.id) === Number(id)) || null

export const useCompanyTrainings = () => ({
  trainings,
  companyTrainings,
  isLoadingTrainings,
  trainingError,
  fetchTrainings,
  createTraining,
  updateTraining,
  getCurrentCompanyId,
  getTrainingById,
  formatDateValue,
})
