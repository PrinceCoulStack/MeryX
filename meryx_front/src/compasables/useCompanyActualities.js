import { computed, ref } from 'vue'
import api from '@/api/axios'

const actualities = ref([])
const isLoadingActualities = ref(false)
const actualityError = ref('')
const DEFAULT_ACTUALITY_IMAGE = 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d'

const normalizeEntityId = (value) => {
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
    return normalizeEntityId(
      value.id ||
        value['@id'] ||
        value.companyId ||
        value.company ||
        value.company?.id ||
        value.company?.['@id'] ||
        value.userId ||
        value.user?.id ||
        value.user?.['@id'] ||
        value.authorId ||
        value.author?.id ||
        value.author?.['@id'],
    )
  }

  return 0
}

const resolveEntityName = (value) => {
  if (!value) return ''

  if (typeof value === 'string') {
    const trimmed = value.trim()
    if (!trimmed) return ''
    const match = trimmed.match(/(?:\/)?(\d+)$/)
    if (match) return `Entity #${match[1]}`
    return trimmed
  }

  if (typeof value === 'object') {
    return (
      value.name ||
      value.companyName ||
      value.fullName ||
      value.username ||
      value.email ||
      value.title ||
      [value.firstName, value.lastName].filter(Boolean).join(' ') ||
      ''
    )
  }

  return ''
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

const sanitizeImageSource = (value) => {
  if (typeof value !== 'string') return DEFAULT_ACTUALITY_IMAGE

  const trimmed = value.trim().replace(/^['"]+|['"]+$/g, '')
  if (!trimmed) return DEFAULT_ACTUALITY_IMAGE

  if (/^data:image\//i.test(trimmed)) {
    const compact = trimmed.replace(/\s+/g, '')
    const normalized = compact.replace(/;base64(?!,)/i, ';base64,')
    return normalized.includes(',') ? normalized : DEFAULT_ACTUALITY_IMAGE
  }

  if (/^[A-Za-z0-9+/=_-]{120,}$/.test(trimmed)) {
    return `data:image/png;base64,${trimmed.replace(/\s+/g, '')}`
  }

  try {
    const normalized = trimmed.startsWith('//') ? `https:${trimmed}` : trimmed
    const parsed = new URL(normalized)
    if (parsed.protocol === 'http:' || parsed.protocol === 'https:') {
      return parsed.href
    }
  } catch {
    return DEFAULT_ACTUALITY_IMAGE
  }

  return DEFAULT_ACTUALITY_IMAGE
}

const getCurrentCompanyId = () => {
  try {
    const storedUser = localStorage.getItem('user')
    if (!storedUser) return 0

    const parsed = JSON.parse(storedUser)
    const candidates = [
      parsed?.companyId,
      parsed?.company_id,
      parsed?.company,
      parsed?.companyProfileId,
      parsed?.company_profile_id,
      parsed?.companyProfile,
      parsed?.company_profile,
      parsed?.companyProfile?.companyId,
      parsed?.companyProfile?.company,
      parsed?.company_profile?.companyId,
      parsed?.company_profile?.company,
      parsed?.profile?.companyId,
      parsed?.profile?.company,
      parsed?.profile?.id, // Company profile is stored as profile.id
      parsed?.profile, // Profile object itself
    ]

    for (const value of candidates) {
      const resolved = normalizeEntityId(value)
      if (resolved) return resolved
    }

    return 0
  } catch {
    return 0
  }
}

const getCurrentUserId = () => {
  try {
    const storedUser = localStorage.getItem('user')
    if (!storedUser) return 1

    const parsed = JSON.parse(storedUser)
    const directId = normalizeEntityId(parsed?.id)
    if (directId) return directId

    return 1
  } catch {
    return 1
  }
}

const readLocalInteractions = () => {
  try {
    const raw = localStorage.getItem('actualityInteractions')
    return raw ? JSON.parse(raw) : { reactions: {}, comments: {} }
  } catch {
    return { reactions: {}, comments: {} }
  }
}

const writeLocalInteractions = (data) => {
  localStorage.setItem('actualityInteractions', JSON.stringify(data))
}

const mapActuality = (item) => {
  const companyId = normalizeEntityId(item?.companyId)
  const authorId = normalizeEntityId(item?.authorId)
  const interactions = readLocalInteractions()
  const postId = Number(item.id)
  const localReactions = Number(interactions.reactions?.[postId] || 0)
  const localComments = Array.isArray(interactions.comments?.[postId])
    ? interactions.comments[postId]
    : []

  const publicComments = Array.isArray(item?.comments) ? item.comments : []
  const mergedComments = [...localComments, ...publicComments].filter(
    (comment, index, list) =>
      index ===
      list.findIndex((candidate) => JSON.stringify(candidate) === JSON.stringify(comment)),
  )

  return {
    id: Number(item.id),
    companyId,
    authorId,
    companyName: resolveEntityName(item?.companyId) || 'Company',
    author: resolveEntityName(item?.authorId) || 'Company member',
    title: item.title || 'Untitled post',
    content: item.content || '',
    category: item.category || 'Announcement',
    visibility: item.visibility || 'Public',
    image: sanitizeImageSource(item.imageUrl || item.image || ''),
    postedAt: item.publiedAt || item.createdAt || item.updatedAt || new Date().toISOString(),
    likes: Number(item.likes ?? item.reactions ?? localReactions ?? 0),
    comments: Number(item.comments ?? mergedComments.length ?? 0),
    commentList: mergedComments,
    raw: item,
  }
}

const fetchActualities = async (force = false) => {
  if (!force && actualities.value.length) return actualities.value

  isLoadingActualities.value = true
  actualityError.value = ''

  try {
    const response = await api.listItems('companyPosts')
    actualities.value = extractCollection(response.data).map(mapActuality)
    return actualities.value
  } catch (error) {
    const message =
      error?.response?.data?.detail || error?.message || 'Unable to load company posts.'
    actualityError.value = message
    return []
  } finally {
    isLoadingActualities.value = false
  }
}

const createActuality = async (payload) => {
  actualityError.value = ''
  const now = new Date().toISOString()
  const companyId = getCurrentCompanyId()
  if (!companyId) {
    actualityError.value = 'Company profile is required to create a post.'
    return null
  }

  const authorId = getCurrentUserId()

  const body = {
    ...payload,
    companyId: payload.companyId || `/api/companies/${companyId}`,
    authorId: payload.authorId || `/api/users/${authorId}`,
    title: payload.title,
    content: payload.content,
    category: payload.category || 'Announcement',
    visibility: payload.visibility || 'Public',
    imageUrl: payload.imageUrl || payload.image || '',
    publiedAt: payload.publiedAt || now,
    createdAt: payload.createdAt || now,
    updatedAt: payload.updatedAt || now,
  }

  try {
    const response = await api.createItem('companyPosts', body)
    const created = mapActuality(response.data || body)
    actualities.value = [created, ...actualities.value]
    return created
  } catch (error) {
    const message =
      error?.response?.data?.detail ||
      error?.response?.data?.message ||
      error?.message ||
      'Unable to create company post.'
    actualityError.value = message
    return null
  }
}

const updateActuality = async (id, payload) => {
  actualityError.value = ''
  const now = new Date().toISOString()
  const companyId = getCurrentCompanyId()
  if (!companyId) {
    actualityError.value = 'Company profile is required to update this post.'
    return null
  }

  const authorId = getCurrentUserId()

  const body = {
    ...payload,
    companyId: payload.companyId || `/api/companies/${companyId}`,
    authorId: payload.authorId || `/api/users/${authorId}`,
    title: payload.title,
    content: payload.content,
    category: payload.category || 'Announcement',
    visibility: payload.visibility || 'Public',
    imageUrl: payload.imageUrl || payload.image || '',
    publiedAt: payload.publiedAt || now,
    updatedAt: payload.updatedAt || now,
  }

  try {
    const response = await api.updateItem('companyPosts', id, body)
    const updated = mapActuality(response.data || { id, ...body })
    actualities.value = actualities.value.map((item) =>
      Number(item.id) === Number(id) ? updated : item,
    )
    return updated
  } catch (error) {
    const message =
      error?.response?.data?.detail ||
      error?.response?.data?.message ||
      error?.message ||
      'Unable to update company post.'
    actualityError.value = message
    return null
  }
}

const companyActualities = computed(() => {
  const companyId = getCurrentCompanyId()
  return actualities.value.filter((item) => Number(item.companyId) === Number(companyId))
})

const getActualityById = (id) =>
  actualities.value.find((item) => Number(item.id) === Number(id)) || null

const addReaction = (postId, reactionType = 'like') => {
  const interactions = readLocalInteractions()
  const safeId = Number(postId)
  const current = Number(interactions.reactions?.[safeId] || 0)
  const next = current + 1

  interactions.reactions = { ...interactions.reactions, [safeId]: next }
  writeLocalInteractions(interactions)

  const target = actualities.value.find((item) => Number(item.id) === safeId)
  if (target) {
    target.likes = Number(target.likes || 0) + 1
  }

  try {
    const userId = getCurrentUserId()
    api.createItem('postRection', {
      companyPostId: `/api/companyPosts/${safeId}`,
      userId: `/api/users/${userId}`,
      type: reactionType,
      createdAt: new Date().toISOString(),
    })
  } catch {
    // The backend reaction endpoint may not be available yet.
  }

  return next
}

const addComment = (postId, message) => {
  const trimmed = String(message || '').trim()
  if (!trimmed) return null

  const safeId = Number(postId)
  const interactions = readLocalInteractions()
  const currentComments = Array.isArray(interactions.comments?.[safeId])
    ? interactions.comments[safeId]
    : []
  const newComment = {
    id: Date.now(),
    author: 'You',
    message: trimmed,
    createdAt: new Date().toISOString(),
  }

  interactions.comments = {
    ...interactions.comments,
    [safeId]: [...currentComments, newComment],
  }
  writeLocalInteractions(interactions)

  const target = actualities.value.find((item) => Number(item.id) === safeId)
  if (target) {
    target.commentList = [...(target.commentList || []), newComment]
    target.comments = target.commentList.length
  }

  try {
    const userId = getCurrentUserId()
    api.createItem('postComments', {
      companyPostId: `/api/companyPosts/${safeId}`,
      userId: `/api/users/${userId}`,
      content: trimmed,
      createdAt: new Date().toISOString(),
      updatedAt: new Date().toISOString(),
    })
  } catch {
    // The backend comments endpoint may not be available yet.
  }

  return newComment
}

export const useCompanyActualities = () => ({
  actualities,
  companyActualities,
  isLoadingActualities,
  actualityError,
  fetchActualities,
  createActuality,
  updateActuality,
  getActualityById,
  getCurrentCompanyId,
  addReaction,
  addComment,
})
