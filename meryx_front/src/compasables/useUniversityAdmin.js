import { computed, ref } from 'vue'
import api from '@/api/axios'

const universities = ref([])
const studentsByUniversity = ref({})
const isLoadingUniversities = ref(false)
const universityAdminError = ref('')

let universitiesLoaded = false

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

const readEmail = (item) =>
  item?.email ||
  item?.user?.email ||
  item?.userId?.email ||
  item?.userId?.user?.email ||
  item?.owner?.email ||
  ''

const readCountry = (item) =>
  item?.country ||
  item?.address?.country ||
  item?.addressId?.country ||
  item?.AddressId?.country ||
  item?.user?.address?.country ||
  item?.userId?.address?.country ||
  item?.location ||
  '-'

const resolveRelation = async (iri, fallbackEndpoint) => {
  if (!iri || typeof iri !== 'string') return null

  const match = iri.match(/(?:\/api\/)?([^/]+)\/(\d+)$/)
  if (!match) return null

  const endpoint = fallbackEndpoint || match[1]
  const id = match[2]

  try {
    const response = await api.getItem(endpoint, id)
    return response?.data || null
  } catch {
    return null
  }
}

const readStatus = (item) => {
  if (typeof item.status === 'string') {
    const value = item.status.toLowerCase()
    if (value.includes('approve')) return 'approved'
    if (value.includes('reject')) return 'rejected'
    if (value.includes('pend')) return 'pending'
  }

  if (typeof item.isApproved === 'boolean') {
    return item.isApproved ? 'approved' : 'pending'
  }

  return 'pending'
}

const mapUniversity = (item) => {
  const accreditationId = item.accreditationNumber || item.accreditationId || ''
  const registrationNumber = item.registrationNumber || ''
  const websiteUrl = item.websiteUrl || ''
  const email = readEmail(item)
  const phone = item.phone || item.user?.phone || item.userId?.phone || ''
  const description = item.description || ''
  const type = item.type || ''

  return {
    id: Number(item.id),
    name: item.name || '',
    email,
    phone,
    country: readCountry(item),
    description,
    websiteUrl,
    type,
    accreditationNumber: accreditationId,
    registrationNumber,
    createdAt: item.createdAt || null,
    status: readStatus(item),
    verification: {
      accreditationId,
      registrationNumber,
      domainMatches: Boolean(websiteUrl || email),
      documentsComplete: Boolean(accreditationId && registrationNumber),
    },
    reviewNotes: item.reviewNotes || '',
    raw: item,
  }
}

const getApiErrorMessage = (error, fallback) => {
  const status = error?.response?.status
  const payload = normalizeApiData(error?.response?.data)
  const detail = payload?.detail || payload?.message || error?.message

  if (status && detail) return `[${status}] ${detail}`
  if (status) return `[${status}] ${fallback}`
  if (detail) return detail
  return fallback
}

const fetchUniversities = async (force = false) => {
  if (universitiesLoaded && !force) return universities.value

  isLoadingUniversities.value = true
  universityAdminError.value = ''

  try {
    const response = await api.listItems('university')
    const items = extractCollection(response.data)

    universities.value = await Promise.all(
      items.map(async (item) => {
        const mapped = mapUniversity(item)

        if (!mapped.email && typeof item.userId === 'string') {
          const user = await resolveRelation(item.userId, 'users')
          mapped.email = user?.email || user?.userId?.email || ''
        }

        if (mapped.country === '-' && typeof item.addressId === 'string') {
          const address = await resolveRelation(item.addressId, 'addresses')
          mapped.country = address?.country || '-'
        }

        return mapped
      }),
    )

    universitiesLoaded = true
    return universities.value
  } catch (error) {
    universityAdminError.value = getApiErrorMessage(error, 'Unable to load universities.')
    throw new Error(universityAdminError.value)
  } finally {
    isLoadingUniversities.value = false
  }
}

const canApproveUniversity = (university) => {
  if (!university) return false
  const check = university.verification

  // Approval is based on uploaded registration documents. Domain/email validity
  // is left for manual admin review.
  return Boolean(check?.accreditationId && check?.registrationNumber && check?.documentsComplete)
}

const getUniversityById = (id) =>
  universities.value.find((university) => university.id === Number(id)) || null

const getStudentsByUniversityId = (id) => studentsByUniversity.value[Number(id)] || []

const approveUniversity = async (id) => {
  const university = getUniversityById(id)
  if (!university) return { ok: false, reason: 'not-found' }

  if (!canApproveUniversity(university)) {
    return { ok: false, reason: 'verification-failed' }
  }

  try {
    await api.patchItem('university', id, {
      isApproved: true,
      status: 'approved',
    })

    university.status = 'approved'
    university.reviewNotes = ''
    return { ok: true }
  } catch (error) {
    universityAdminError.value = getApiErrorMessage(error, 'Unable to approve university.')
    return { ok: false, reason: 'request-failed' }
  }
}

const rejectUniversity = async (id, reason = '') => {
  const university = getUniversityById(id)
  if (!university) return { ok: false, reason: 'not-found' }

  try {
    await api.patchItem('university', id, {
      isApproved: false,
      status: 'rejected',
    })

    university.status = 'rejected'
    university.reviewNotes = reason.trim() || 'Rejected during verification review.'
    return { ok: true }
  } catch (error) {
    universityAdminError.value = getApiErrorMessage(error, 'Unable to reject university.')
    return { ok: false, reason: 'request-failed' }
  }
}

const approvedUniversities = computed(() =>
  universities.value.filter((university) => university.status === 'approved'),
)

const pendingUniversities = computed(() =>
  universities.value.filter((university) => university.status === 'pending'),
)

const rejectedUniversities = computed(() =>
  universities.value.filter((university) => university.status === 'rejected'),
)

export const useUniversityAdmin = () => ({
  universities,
  isLoadingUniversities,
  universityAdminError,
  approvedUniversities,
  pendingUniversities,
  rejectedUniversities,
  fetchUniversities,
  getUniversityById,
  getStudentsByUniversityId,
  canApproveUniversity,
  approveUniversity,
  rejectUniversity,
})
