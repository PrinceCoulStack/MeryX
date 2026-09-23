import { computed, ref } from 'vue'
import api from '@/api/axios'
import * as candidatureApi from '@/api/candidatureApiClient'

const opportunities = ref([])
const isLoadingOpportunities = ref(false)
const opportunityError = ref('')

const parseCompanyId = (value) => {
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
    return parseCompanyId(
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
      const resolved = parseCompanyId(value)
      if (resolved) return resolved
    }

    return 0
  } catch {
    return 0
  }
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

const normalizeRequirements = (value) => {
  if (Array.isArray(value)) {
    return value.map((req) => String(req).trim()).filter(Boolean)
  }

  if (typeof value === 'string') {
    const trimmed = value.trim()
    if (!trimmed) return []

    try {
      const parsed = JSON.parse(trimmed)
      if (Array.isArray(parsed)) {
        return normalizeRequirements(parsed)
      }
    } catch {
      // Ignore JSON parse failures and fall back to CSV parsing below.
    }

    return trimmed
      .replace(/\[|\]|{|\}/g, '')
      .split(',')
      .map((req) => req.trim().replace(/^['"]|['"]$/g, ''))
      .filter(Boolean)
  }

  return []
}

const mapOpportunity = (item) => {
  const companyId = parseCompanyId(item?.companyId)
  const type = item?.type || item?.category || 'Internship'
  const remoteType = item?.remoteType || 'Hybrid'
  const status = item?.status || 'Open'
  const salary = item?.salaryLabel || item?.salary || '—'

  return {
    id: Number(item.id),
    companyId,
    title: item.title || 'Untitled opportunity',
    type,
    location: item.location || 'Remote',
    department: item.department || 'General',
    remoteType,
    salary,
    status,
    postedDate: formatDateValue(item.publishedAt || item.createdAt),
    description: item.description || '',
    requirements: normalizeRequirements(item.requirements),
    deadLine: item.applicationDeadLine || item.deadline || null,
    raw: item,
  }
}

const fetchOpportunities = async (force = false) => {
  if (!force && opportunities.value.length) return opportunities.value

  isLoadingOpportunities.value = true
  opportunityError.value = ''

  try {
    const response = await api.listItems('opportunities')
    opportunities.value = extractCollection(response.data).map(mapOpportunity)
    return opportunities.value
  } catch (error) {
    const message =
      error?.response?.data?.detail || error?.message || 'Unable to load opportunities.'
    opportunityError.value = message
    return []
  } finally {
    isLoadingOpportunities.value = false
  }
}

const createOpportunity = async (payload) => {
  opportunityError.value = ''
  const now = new Date().toISOString()
  const companyId = getCurrentCompanyId()
  if (!companyId) {
    opportunityError.value = 'Company profile is required to publish an opportunity.'
    return null
  }

  const body = {
    ...payload,
    companyId: `/api/companies/${companyId}`,
    status: payload.status || 'Open',
    isEnabled: payload.isEnabled ?? true,
    isDeleted: payload.isDeleted ?? false,
    publishedAt: payload.publishedAt || now,
    createdAt: payload.createdAt || now,
    updatedAt: payload.updatedAt || now,
    applicationDeadLine: payload.applicationDeadLine || payload.deadline || now,
    category: payload.category || payload.type || 'Internship',
    experienceLevel: payload.experienceLevel || 'Not specified',
    numberOfPositions: payload.numberOfPositions || '1',
    remoteType: payload.remoteType || 'Hybrid',
    department: payload.department || 'General',
    location: payload.location || 'Remote',
    salaryLabel: payload.salaryLabel || payload.salary || '—',
  }

  try {
    const response = await api.createItem('opportunities', body)
    const created = mapOpportunity(response.data || body)
    opportunities.value = [created, ...opportunities.value]
    return created
  } catch (error) {
    const message =
      error?.response?.data?.detail || error?.message || 'Unable to create opportunity.'
    opportunityError.value = message
    return null
  }
}

const updateOpportunity = async (id, payload) => {
  opportunityError.value = ''
  const companyId = getCurrentCompanyId()
  if (!companyId) {
    opportunityError.value = 'Company profile is required to update this opportunity.'
    return null
  }

  const now = new Date().toISOString()

  const body = {
    ...payload,
    companyId: payload.companyId || `/api/companies/${companyId}`,
    status: payload.status || 'Open',
    isEnabled: payload.isEnabled ?? true,
    isDeleted: payload.isDeleted ?? false,
    updatedAt: payload.updatedAt || now,
    category: payload.category || payload.type || 'Internship',
    experienceLevel: payload.experienceLevel || 'Not specified',
    numberOfPositions: payload.numberOfPositions || '1',
    remoteType: payload.remoteType || 'Hybrid',
    department: payload.department || 'General',
    location: payload.location || 'Remote',
    salaryLabel: payload.salaryLabel || payload.salary || '—',
    applicationDeadLine:
      payload.applicationDeadLine || payload.deadline || payload.applicationDeadline || null,
  }

  try {
    const response = await api.updateItem('opportunities', id, body)
    const updated = mapOpportunity(response.data || { id, ...body })
    opportunities.value = opportunities.value.map((item) =>
      Number(item.id) === Number(id) ? updated : item,
    )
    return updated
  } catch (error) {
    const message =
      error?.response?.data?.detail ||
      error?.response?.data?.message ||
      error?.message ||
      'Unable to update opportunity.'
    opportunityError.value = message
    return null
  }
}

const currentCompanyId = computed(() => getCurrentCompanyId())

const companyOpportunities = computed(() =>
  opportunities.value.filter((item) => Number(item.companyId) === Number(currentCompanyId.value)),
)

const getOpportunityById = (id) =>
  opportunities.value.find((item) => Number(item.id) === Number(id)) || null

/**
 * Fetch all candidates/candidatures
 */
const getOpportunityCandidates = async (opportunityId = null) => {
  try {
    console.log('Fetching candidatures from API...', { opportunityId })
    const filters = {}
    if (opportunityId) {
      filters.opportunityId = opportunityId
    }
    const result = await candidatureApi.getCandidatures(filters)

    console.log('API response:', result)
    console.log('Raw response data:', result.raw)

    const candidates = result.data || []
    console.log('Parsed candidates:', candidates)

    // Enrich with match scoring
    return candidates.map((candidature) => ({
      ...candidature,
      matchScore: calculateMatchScore(candidature),
    }))
  } catch (error) {
    console.error('Failed to fetch candidates:', error)
    console.error('Error response:', error.response?.data)
    console.error('Error message:', error.message)
    return []
  }
}

/**
 * Simple match score calculation based on available data
 * In a real scenario, this would come from the backend
 */
const calculateMatchScore = (candidature) => {
  if (!candidature.student) return 0

  let score = 50 // Base score
  const student = candidature.student

  // Increase score based on available profile completeness
  if (student.gpa) score += 15
  if (student.skills && student.skills.length > 0) score += 20
  if (student.summary) score += 10
  if (student.program) score += 5

  return Math.min(score, 100)
}

/**
 * Update candidature status
 */
const updateCandidatureStatus = async (candidatureId, newStatus, options = {}) => {
  try {
    const payload = {
      status: newStatus,
      ...(options.feedback ? { feedback: options.feedback } : {}),
      ...(options.notes ? { notes: options.notes } : {}),
      ...(options.interviewDate ? { interviewDate: options.interviewDate } : {}),
    }

    const result = await candidatureApi.updateCandidature(candidatureId, payload)
    return result.data
  } catch (error) {
    console.error('Failed to update candidature status:', error)
    throw error
  }
}

export const useCompanyOpportunities = () => ({
  opportunities,
  companyOpportunities,
  currentCompanyId,
  isLoadingOpportunities,
  opportunityError,
  fetchOpportunities,
  createOpportunity,
  updateOpportunity,
  getCurrentCompanyId,
  getOpportunityById,
  getOpportunityCandidates,
  updateCandidatureStatus,
})
