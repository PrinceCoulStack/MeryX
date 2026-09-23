import { computed, ref } from 'vue'
import api from '@/api/axios'

const companies = ref([])
const trainingPosts = ref([])
const opportunityPosts = ref([])
const isLoadingCompanies = ref(false)
const companyAdminError = ref('')

let companiesLoaded = false

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

const parseId = (value) => {
  if (!value && value !== 0) return null
  if (typeof value === 'number') return value
  if (typeof value === 'string') {
    const match = value.trim().match(/(?:\/)?(\d+)$/)
    return match ? Number(match[1]) : null
  }
  if (typeof value === 'object') {
    return parseId(value.id || value['@id'])
  }
  return null
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

const mapCompany = (item) => {
  const registrationNumber = item.registrationNumber || ''
  const taxId = item.taxId || ''
  const websiteUrl = item.websiteUrl || ''
  const email = readEmail(item)
  const phone = item.phone || item.user?.phone || item.userId?.phone || ''
  const description = item.description || ''
  const sector = item.sector || ''
  const hasBusinessContactInfo = Boolean(websiteUrl || email)

  return {
    id: Number(item.id),
    name: item.name || '',
    email,
    phone,
    country: readCountry(item),
    description,
    websiteUrl,
    sector,
    taxId,
    registrationNumber,
    createdAt: item.createdAt || null,
    status: readStatus(item),
    verification: {
      registrationNumber,
      taxId,
      domainMatches: hasBusinessContactInfo,
      documentsComplete: Boolean(registrationNumber && taxId),
    },
    reviewNotes: item.reviewNotes || '',
    raw: item,
  }
}

const mapTraining = (item) => ({
  id: Number(item.id),
  companyId:
    parseId(item.companyId) ||
    parseId(item.company) ||
    parseId(item.companyProfileId) ||
    parseId(item.createdByCompany) ||
    null,
  title: item.title || item.name || 'Training',
  raw: item,
})

const mapOpportunity = (item) => ({
  id: Number(item.id),
  companyId:
    parseId(item.companyId) ||
    parseId(item.company) ||
    parseId(item.companyProfileId) ||
    parseId(item.createdByCompany) ||
    null,
  title: item.title || item.name || 'Opportunity',
  raw: item,
})

const getApiErrorMessage = (error, fallback) => {
  const status = error?.response?.status
  const payload = normalizeApiData(error?.response?.data)
  const detail = payload?.detail || payload?.message || error?.message

  if (status && detail) return `[${status}] ${detail}`
  if (status) return `[${status}] ${fallback}`
  if (detail) return detail
  return fallback
}

const fetchCompanyContent = async () => {
  const [trainingsResponse, opportunitiesResponse] = await Promise.all([
    api.listItems('trainings').catch(() => ({ data: [] })),
    api.listItems('opportunities').catch(() => ({ data: [] })),
  ])

  trainingPosts.value = extractCollection(trainingsResponse.data).map(mapTraining)
  opportunityPosts.value = extractCollection(opportunitiesResponse.data).map(mapOpportunity)
}

const getCompanyById = (id) => companies.value.find((item) => item.id === Number(id)) || null

const getCompanyTrainings = (companyId) =>
  trainingPosts.value.filter((item) => item.companyId === Number(companyId))

const getCompanyOpportunities = (companyId) =>
  opportunityPosts.value.filter((item) => item.companyId === Number(companyId))

const fetchCompanies = async (force = false) => {
  if (companiesLoaded && !force) return companies.value

  isLoadingCompanies.value = true
  companyAdminError.value = ''
  try {
    const response = await api.listItems('companies')
    const items = extractCollection(response.data)

    companies.value = await Promise.all(
      items.map(async (item) => {
        const mapped = mapCompany(item)

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

    await fetchCompanyContent()

    companiesLoaded = true
    return companies.value
  } catch (error) {
    companyAdminError.value = getApiErrorMessage(error, 'Unable to load companies.')
    throw new Error(companyAdminError.value)
  } finally {
    isLoadingCompanies.value = false
  }
}

const canApproveCompany = (company) => {
  if (!company) return false
  const check = company.verification

  // Approval is based on the uploaded registration documents. The admin
  // will manually review whether the domain or email is valid.
  return Boolean(check?.registrationNumber && check?.taxId && check?.documentsComplete)
}

const approveCompany = async (id) => {
  const company = getCompanyById(id)
  if (!company) return { ok: false, reason: 'not-found' }

  if (!canApproveCompany(company)) {
    return { ok: false, reason: 'verification-failed' }
  }

  try {
    await api.patchItem('companies', id, {
      isApproved: true,
      status: 'approved',
    })

    company.status = 'approved'
    company.reviewNotes = ''
    return { ok: true }
  } catch (error) {
    companyAdminError.value = getApiErrorMessage(error, 'Unable to approve company.')
    return { ok: false, reason: 'request-failed' }
  }
}

const rejectCompany = async (id, reason = '') => {
  const company = getCompanyById(id)
  if (!company) return { ok: false, reason: 'not-found' }

  try {
    await api.patchItem('companies', id, {
      isApproved: false,
      status: 'rejected',
    })

    company.status = 'rejected'
    company.reviewNotes = reason.trim() || 'Rejected during verification review.'
    return { ok: true }
  } catch (error) {
    companyAdminError.value = getApiErrorMessage(error, 'Unable to reject company.')
    return { ok: false, reason: 'request-failed' }
  }
}

const approvedCompanies = computed(() =>
  companies.value.filter((company) => company.status === 'approved'),
)

const pendingCompanies = computed(() =>
  companies.value.filter((company) => company.status === 'pending'),
)

const rejectedCompanies = computed(() =>
  companies.value.filter((company) => company.status === 'rejected'),
)

export const useCompanyAdmin = () => ({
  companies,
  trainingPosts,
  opportunityPosts,
  isLoadingCompanies,
  companyAdminError,
  approvedCompanies,
  pendingCompanies,
  rejectedCompanies,
  fetchCompanies,
  getCompanyById,
  getCompanyTrainings,
  getCompanyOpportunities,
  canApproveCompany,
  approveCompany,
  rejectCompany,
})
