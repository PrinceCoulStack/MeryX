<template>
  <div class="message-management-page">
    <header class="page-header">
      <div>
        <p class="eyebrow">University / Company Messaging</p>
        <h1>Message Management Workspace</h1>
        <p class="intro">
          Centralize communication with partner companies, track unread conversations, and reply
          quickly from one workspace.
        </p>
      </div>

      <div class="header-actions">
        <button class="secondary-btn" type="button" @click="resetFilters">
          <i class="bi bi-arrow-counterclockwise"></i>
          Reset Filters
        </button>
        <button class="primary-btn" type="button" @click="composeMode = true">
          <i class="bi bi-pencil-square"></i>
          New Message
        </button>
      </div>
    </header>

    <section class="kpi-grid">
      <article class="kpi-card">
        <span>Conversations</span>
        <strong>{{ conversations.length }}</strong>
      </article>
      <article class="kpi-card">
        <span>Unread</span>
        <strong>{{ unreadCount }}</strong>
      </article>
      <article class="kpi-card">
        <span>Active Partners</span>
        <strong>{{ activeConversationCount }}</strong>
      </article>
      <article class="kpi-card">
        <span>Replied Today</span>
        <strong>{{ repliedTodayCount }}</strong>
      </article>
    </section>

    <section class="toolbar-card">
      <div class="search-box">
        <i class="bi bi-search"></i>
        <input v-model="filters.query" type="search" placeholder="Search company, message, topic" />
      </div>

      <div class="filters-grid">
        <select v-model="filters.status">
          <option value="all">All Status</option>
          <option value="Unread">Unread</option>
          <option value="Open">Open</option>
          <option value="Closed">Closed</option>
        </select>

        <select v-model="filters.partnerType">
          <option value="all">All Partner Types</option>
          <option value="Active partner">Active partner</option>
          <option value="Pending approval">Pending approval</option>
        </select>

        <button class="secondary-btn" type="button" @click="markAllRead">
          <i class="bi bi-check2-all"></i>
          Mark all read
        </button>
      </div>
    </section>

    <p v-if="isLoading" class="info-line">Loading conversations...</p>
    <p v-else-if="loadError" class="error-line">{{ loadError }}</p>

    <section class="workspace-grid">
      <article class="conversation-list-card">
        <div class="card-head">
          <h2>Conversations</h2>
          <span>{{ filteredConversations.length }} items</span>
        </div>

        <div class="conversation-list">
          <button
            v-for="conversation in filteredConversations"
            :key="conversation.id"
            class="conversation-item"
            :class="{ active: selectedConversation?.id === conversation.id }"
            type="button"
            @click="selectConversation(conversation)"
          >
            <div class="avatar">{{ conversation.company.charAt(0) }}</div>

            <div class="conversation-core">
              <div class="line-top">
                <strong>{{ conversation.company }}</strong>
                <small>{{ conversation.lastTime }}</small>
              </div>
              <p>{{ conversation.topic }}</p>
              <small>{{ conversation.preview }}</small>
              <div class="tag-row">
                <span class="pill">{{ conversation.partnerStatus }}</span>
                <span class="pill muted-pill">{{ conversation.status }}</span>
              </div>
            </div>

            <span v-if="conversation.unread > 0" class="unread-badge">{{
              conversation.unread
            }}</span>
          </button>

          <div v-if="!filteredConversations.length" class="empty-state">
            No conversations found.
          </div>
        </div>
      </article>

      <article class="chat-panel-card" v-if="selectedConversation">
        <div class="chat-header">
          <div class="chat-company">
            <div class="avatar large">{{ selectedConversation.company.charAt(0) }}</div>
            <div>
              <h2>{{ selectedConversation.company }}</h2>
              <p>{{ selectedConversation.industry }} · {{ selectedConversation.location }}</p>
            </div>
          </div>

          <div class="chat-actions">
            <span class="status-pill" :class="statusClass(selectedConversation.status)">
              {{ selectedConversation.status }}
            </span>
            <button class="secondary-btn" type="button" @click="openCompanyView">
              View Company
            </button>
          </div>
        </div>

        <div class="chat-meta-grid">
          <div>
            <span>Contact</span>
            <strong>{{ selectedConversation.contact }}</strong>
          </div>
          <div>
            <span>Email</span>
            <strong>{{ selectedConversation.email }}</strong>
          </div>
          <div>
            <span>Open Topics</span>
            <strong>{{ selectedConversation.openTopics }}</strong>
          </div>
          <div>
            <span>Last Reply</span>
            <strong>{{ selectedConversation.lastTime }}</strong>
          </div>
        </div>

        <div class="messages-thread">
          <div
            v-for="message in selectedConversation.messages"
            :key="message.id"
            class="message-row"
            :class="message.from === 'university' ? 'sent' : 'received'"
          >
            <div class="bubble">
              <p>{{ message.text }}</p>
              <small>{{ message.time }}</small>
            </div>
          </div>
        </div>

        <div class="quick-templates">
          <button
            v-for="template in quickTemplates"
            :key="template"
            type="button"
            class="template-chip"
            @click="applyTemplate(template)"
          >
            {{ template }}
          </button>
        </div>

        <form class="composer" @submit.prevent="sendMessage">
          <div class="composer-grid">
            <label>
              <span>Subject</span>
              <input v-model="composer.subject" type="text" placeholder="Internship slots update" />
            </label>
            <label class="full">
              <span>Message</span>
              <textarea
                v-model="composer.body"
                rows="3"
                placeholder="Write your message to the company..."
              ></textarea>
            </label>
          </div>

          <div class="composer-actions">
            <button class="secondary-btn" type="button" @click="clearComposer">Clear</button>
            <button class="primary-btn" type="submit" :disabled="!composer.body.trim()">
              <i class="bi bi-send-fill"></i>
              Send Message
            </button>
          </div>
        </form>
      </article>

      <article class="chat-panel-card" v-else>
        <div class="empty-state chat-empty">Select a conversation to start managing messages.</div>
      </article>
    </section>

    <section v-if="composeMode" class="overlay" @click="composeMode = false"></section>
    <section v-if="composeMode" class="compose-modal">
      <div class="card-head">
        <h2>New Message</h2>
        <button class="icon-btn" type="button" @click="composeMode = false">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      <div class="composer-grid">
        <label>
          <span>Select Company</span>
          <select v-model="newMessageCompanyId">
            <option v-for="item in conversations" :key="item.id" :value="item.id">
              {{ item.company }}
            </option>
          </select>
        </label>
        <label>
          <span>Subject</span>
          <input v-model="newMessageSubject" type="text" placeholder="Partnership follow-up" />
        </label>
        <label class="full">
          <span>Message</span>
          <textarea
            v-model="newMessageBody"
            rows="4"
            placeholder="Write your message..."
          ></textarea>
        </label>
      </div>

      <div class="composer-actions">
        <button class="secondary-btn" type="button" @click="composeMode = false">Cancel</button>
        <button
          class="primary-btn"
          type="button"
          @click="sendNewMessage"
          :disabled="!newMessageBody.trim()"
        >
          Send
        </button>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api/axios'
import { useToast } from '@/compasables/useToast'
import { useAuthStore } from '@/stores/auth.store'
import { useCompanyAdmin } from '@/compasables/useCompanyAdmin'

const router = useRouter()
const toast = useToast()
const authStore = useAuthStore()
const { companies, fetchCompanies, isLoadingCompanies, companyAdminError } = useCompanyAdmin()

const conversations = ref([])
const messagesLoading = ref(false)
const isLoadingConversations = ref(false)
const messageError = ref('')

const filters = ref({
  query: '',
  status: 'all',
  partnerType: 'all',
})

const selectedConversation = ref(conversations.value[0] || null)
const composeMode = ref(false)
const newMessageCompanyId = ref(conversations.value[0]?.id || null)
const newMessageSubject = ref('')
const newMessageBody = ref('')

const composer = ref({
  subject: '',
  body: '',
})

const pendingRequest = ref(false)

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

const normalizeApiData = (data) => {
  if (typeof data !== 'string') return data
  try {
    return JSON.parse(data)
  } catch {
    return data
  }
}

const extractCollection = (payload) => {
  const data = normalizeApiData(payload)
  if (Array.isArray(data)) return data
  if (Array.isArray(data?.['hydra:member'])) return data['hydra:member']
  if (Array.isArray(data?.items)) return data.items
  return []
}

const safeDate = (value) => {
  if (!value) return null
  const parsed = new Date(value)
  return Number.isNaN(parsed.getTime()) ? null : parsed
}

const formatDateTime = (value) => {
  const date = safeDate(value)
  if (!date) return 'Now'
  return date.toLocaleString('en-GB', {
    day: '2-digit',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const relativeTime = (value) => {
  const date = safeDate(value)
  if (!date) return 'Now'

  const diffMs = Date.now() - date.getTime()
  const diffMin = Math.max(0, Math.floor(diffMs / 60000))
  if (diffMin < 1) return 'Now'
  if (diffMin < 60) return `${diffMin}m ago`

  const diffHours = Math.floor(diffMin / 60)
  if (diffHours < 24) return `${diffHours}h ago`

  const diffDays = Math.floor(diffHours / 24)
  if (diffDays < 7) return `${diffDays}d ago`

  return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' })
}

const normalizeStatus = (value) => {
  const raw = String(value || '')
    .trim()
    .toLowerCase()
  if (raw.includes('close') || raw.includes('resolved')) return 'Closed'
  if (raw.includes('unread') || raw.includes('new') || raw.includes('pending')) return 'Unread'
  return 'Open'
}

const normalizePartnerStatus = (value) => {
  const raw = String(value || '')
    .trim()
    .toLowerCase()
  if (raw.includes('approve') || raw.includes('active')) return 'Active partner'
  if (raw.includes('reject') || raw.includes('inactive') || raw.includes('suspend')) {
    return 'Pending approval'
  }
  return 'Pending approval'
}

const loggedUserId = computed(() => parseId(authStore.user?.id))

const companyById = computed(() => {
  const map = new Map()
  for (const company of companies.value || []) {
    map.set(Number(company.id), company)
  }
  return map
})

const tryListPaths = async (paths) => {
  let lastError = null
  for (const path of paths) {
    try {
      const response = await api.listItems(path)
      return extractCollection(response.data)
    } catch (error) {
      lastError = error
    }
  }
  throw lastError || new Error('Unable to fetch collection')
}

const tryCreatePaths = async (paths, payload) => {
  let lastError = null
  for (const path of paths) {
    try {
      return await api.createItem(path, payload)
    } catch (error) {
      lastError = error
    }
  }
  throw lastError || new Error('Unable to create item')
}

const mapMessage = (message, fallbackConversationId) => {
  const senderId = parseId(message.senderId || message.sender || message.userId)
  const from =
    senderId && loggedUserId.value && senderId === loggedUserId.value ? 'university' : 'company'

  return {
    id: Number(message.id || Date.now()),
    from,
    text: message.content || message.message || message.body || message.text || '',
    time: formatDateTime(message.createdAt || message.sentAt || message.updatedAt),
    createdAt: message.createdAt || message.sentAt || message.updatedAt || null,
    conversationId:
      parseId(message.conversationId || message.conversation || message.threadId) ||
      fallbackConversationId ||
      null,
    raw: message,
  }
}

const mapConversation = (item) => {
  const companyId = parseId(item.companyId || item.company || item.partnerCompany)
  const company = companyById.value.get(Number(companyId))

  const embeddedMessages = Array.isArray(item.messages)
    ? item.messages.map((message) => mapMessage(message, Number(item.id)))
    : []
  const lastMessage = embeddedMessages[embeddedMessages.length - 1] || null

  return {
    id: Number(item.id),
    companyId: Number(companyId || 0),
    company: company?.name || item.companyName || item.title || `Company ${companyId || ''}`.trim(),
    industry: company?.sector || company?.raw?.industry || '-',
    location: company?.country || company?.raw?.city || company?.raw?.location || '-',
    contact:
      company?.raw?.contactName ||
      company?.raw?.contactPerson ||
      item.contactName ||
      item.contact ||
      '-',
    email: company?.email || item.email || '-',
    partnerStatus: normalizePartnerStatus(company?.status || item.partnerStatus),
    status: normalizeStatus(item.status || (Number(item.unreadCount || 0) > 0 ? 'Unread' : 'Open')),
    topic: item.subject || item.topic || lastMessage?.text || 'Conversation',
    preview: lastMessage?.text || item.lastMessage || item.preview || '-',
    unread: Number(item.unreadCount || item.unread || 0),
    openTopics: Number(item.openTopics || item.pendingTopics || 0),
    lastTime: relativeTime(item.updatedAt || item.lastMessageAt || lastMessage?.createdAt),
    lastAt: item.updatedAt || item.lastMessageAt || lastMessage?.createdAt || null,
    messages: embeddedMessages,
    raw: item,
  }
}

const companyFallbackConversations = computed(() =>
  (companies.value || []).map((company) => ({
    id: Number(company.id),
    companyId: Number(company.id),
    company: company.name || 'Company',
    industry: company.sector || company.raw?.industry || '-',
    location: company.country || company.raw?.location || '-',
    contact: company.raw?.contactName || company.raw?.contactPerson || '-',
    email: company.email || '-',
    partnerStatus: normalizePartnerStatus(company.status),
    status: 'Open',
    topic: 'New discussion',
    preview: 'Start a conversation with this partner company.',
    unread: 0,
    openTopics: 0,
    lastTime: '-',
    lastAt: null,
    messages: [],
    raw: { fallback: true },
  })),
)

const fetchConversations = async () => {
  isLoadingConversations.value = true
  messageError.value = ''

  try {
    const rawConversations = await tryListPaths([
      'conversations',
      'conversation',
      'messageThreads',
      'message_threads',
    ])

    const mapped = rawConversations.map(mapConversation).sort((a, b) => {
      const aDate = safeDate(a.lastAt)?.getTime() || 0
      const bDate = safeDate(b.lastAt)?.getTime() || 0
      return bDate - aDate
    })

    conversations.value = mapped
  } catch (error) {
    const detail =
      error?.response?.data?.detail ||
      error?.response?.data?.message ||
      error?.response?.data?.['hydra:description'] ||
      error?.message ||
      'Unable to load conversations.'
    messageError.value = detail
    conversations.value = companyFallbackConversations.value
  } finally {
    isLoadingConversations.value = false
  }
}

const loadMessagesForConversation = async (conversation) => {
  if (!conversation || conversation.messages.length > 0) return

  messagesLoading.value = true
  try {
    const id = Number(conversation.id)
    const rows = await tryListPaths([
      `messages?conversationId=/api/conversations/${id}`,
      `messages?conversation=/api/conversations/${id}`,
      `messages?conversationId=${id}`,
      `message?conversationId=${id}`,
    ])

    conversation.messages = rows
      .map((item) => mapMessage(item, id))
      .sort((a, b) => {
        const aDate = safeDate(a.createdAt)?.getTime() || 0
        const bDate = safeDate(b.createdAt)?.getTime() || 0
        return aDate - bDate
      })

    if (conversation.messages.length > 0) {
      const last = conversation.messages[conversation.messages.length - 1]
      conversation.preview = last.text || conversation.preview
      conversation.lastTime = relativeTime(last.createdAt)
      conversation.lastAt = last.createdAt || conversation.lastAt
    }
  } catch {
    // Keep thread usable even when message endpoint is not yet exposed.
  } finally {
    messagesLoading.value = false
  }
}

const quickTemplates = [
  'Please share available internship slots for next month.',
  'Could you confirm interview dates for shortlisted students?',
  'Kindly send feedback on the latest student cohort.',
]

const filteredConversations = computed(() => {
  const query = filters.value.query.trim().toLowerCase()
  return conversations.value.filter((item) => {
    const matchesQuery =
      !query ||
      item.company.toLowerCase().includes(query) ||
      item.topic.toLowerCase().includes(query) ||
      item.preview.toLowerCase().includes(query)

    const matchesStatus = filters.value.status === 'all' || item.status === filters.value.status
    const matchesPartner =
      filters.value.partnerType === 'all' || item.partnerStatus === filters.value.partnerType

    return matchesQuery && matchesStatus && matchesPartner
  })
})

const unreadCount = computed(() => conversations.value.reduce((sum, item) => sum + item.unread, 0))
const activeConversationCount = computed(
  () => conversations.value.filter((item) => item.partnerStatus === 'Active partner').length,
)
const repliedTodayCount = computed(
  () =>
    conversations.value.filter(
      (item) =>
        item.lastTime.includes('AM') || item.lastTime.includes('PM') || item.lastTime === 'Now',
    ).length,
)

const isLoading = computed(
  () => isLoadingConversations.value || isLoadingCompanies.value || messagesLoading.value,
)

const loadError = computed(() => messageError.value || companyAdminError.value || '')

const statusClass = (status) => {
  if (status === 'Unread') return 'pending'
  if (status === 'Closed') return 'inactive'
  return 'active'
}

const selectConversation = (conversation) => {
  selectedConversation.value = conversation
  if (conversation.unread > 0) {
    conversation.unread = 0
    if (conversation.status === 'Unread') {
      conversation.status = 'Open'
    }
  }
  loadMessagesForConversation(conversation).catch(() => {})
}

const sendConversationMessage = async (conversation, subject, body) => {
  const trimmedBody = String(body || '').trim()
  if (!conversation || !trimmedBody) return false

  pendingRequest.value = true
  try {
    const now = new Date().toISOString()
    const basePayload = {
      conversationId: `/api/conversations/${conversation.id}`,
      conversation: `/api/conversations/${conversation.id}`,
      senderId: loggedUserId.value ? `/api/users/${loggedUserId.value}` : undefined,
      content: trimmedBody,
      body: trimmedBody,
      message: trimmedBody,
      subject: String(subject || '').trim() || null,
      createdAt: now,
      isEncrypted: true,
    }

    Object.keys(basePayload).forEach((key) => {
      if (basePayload[key] === undefined || basePayload[key] === null) delete basePayload[key]
    })

    let createdData = null
    try {
      const response = await tryCreatePaths(['messages', 'message'], basePayload)
      createdData = response?.data || null
    } catch {
      if (conversation.raw?.fallback) {
        createdData = { id: Date.now(), ...basePayload }
      } else {
        throw new Error('Unable to send message through API.')
      }
    }

    const mappedMessage = mapMessage(createdData, conversation.id)
    mappedMessage.from = 'university'
    mappedMessage.time = formatDateTime(createdData?.createdAt || now)

    conversation.messages.push(mappedMessage)
    conversation.preview = mappedMessage.text
    conversation.lastTime = 'Now'
    conversation.lastAt = createdData?.createdAt || now
    conversation.status = 'Open'
    return true
  } catch (error) {
    const detail =
      error?.response?.data?.detail ||
      error?.response?.data?.message ||
      error?.response?.data?.['hydra:description'] ||
      error?.message ||
      'Unable to send message.'
    toast.error(detail)
    return false
  } finally {
    pendingRequest.value = false
  }
}

const sendMessage = async () => {
  if (!selectedConversation.value || !composer.value.body.trim()) return
  const sent = await sendConversationMessage(
    selectedConversation.value,
    composer.value.subject,
    composer.value.body,
  )
  if (sent) clearComposer()
}

const clearComposer = () => {
  composer.value = {
    subject: '',
    body: '',
  }
}

const applyTemplate = (template) => {
  composer.value.body = template
}

const markAllRead = () => {
  conversations.value.forEach((item) => {
    item.unread = 0
    if (item.status === 'Unread') {
      item.status = 'Open'
    }
  })
}

const resetFilters = () => {
  filters.value = {
    query: '',
    status: 'all',
    partnerType: 'all',
  }
}

const sendNewMessage = () => {
  const target = conversations.value.find((item) => item.id === newMessageCompanyId.value)
  if (!target || !newMessageBody.value.trim()) return

  sendConversationMessage(target, newMessageSubject.value, newMessageBody.value).then((sent) => {
    if (!sent) return

    selectedConversation.value = target
    composeMode.value = false
    newMessageSubject.value = ''
    newMessageBody.value = ''
  })
}

const openCompanyView = () => {
  if (!selectedConversation.value?.companyId) {
    router.push({ name: 'partnerCompanies' })
    return
  }

  router.push({
    name: 'partnerCompanies',
    query: { companyId: selectedConversation.value.companyId },
  })
}

onMounted(async () => {
  await fetchCompanies().catch(() => {})
  await fetchConversations()

  if (!selectedConversation.value && conversations.value.length) {
    selectedConversation.value = conversations.value[0]
    newMessageCompanyId.value = conversations.value[0].id
    await loadMessagesForConversation(conversations.value[0]).catch(() => {})
  }
})
</script>

<style scoped>
.message-management-page {
  display: grid;
  gap: 20px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
}

.eyebrow {
  margin: 0 0 6px;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  color: var(--primary);
  font-size: 0.78rem;
  font-weight: 700;
}

.page-header h1,
.card-head h2,
.chat-company h2 {
  margin: 0;
  color: var(--text);
}

.intro {
  margin: 10px 0 0;
  color: var(--muted);
  max-width: 780px;
}

.header-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.primary-btn,
.secondary-btn,
.template-chip,
.icon-btn,
.conversation-item {
  border: none;
  border-radius: 14px;
  cursor: pointer;
}

.primary-btn {
  background: var(--primary);
  color: white;
  padding: 12px 16px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.secondary-btn {
  background: var(--surface);
  color: var(--text);
  border: 1px solid var(--border);
  padding: 12px 16px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.icon-btn {
  width: 40px;
  height: 40px;
  display: grid;
  place-items: center;
  background: transparent;
  color: var(--text);
}

.kpi-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 14px;
}

.kpi-card,
.toolbar-card,
.conversation-list-card,
.chat-panel-card,
.compose-modal {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 22px;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
}

.kpi-card {
  padding: 20px;
}

.kpi-card span,
.card-head span,
.conversation-core small,
.conversation-core p,
.chat-company p,
.chat-meta-grid span,
.bubble small,
.composer-grid span,
.line-top small {
  color: var(--muted);
}

.kpi-card strong {
  margin-top: 10px;
  display: block;
  color: var(--text);
  font-size: 1.8rem;
}

.info-line,
.error-line {
  margin: 0;
  color: var(--muted);
}

.error-line {
  color: #b91c1c;
}

.toolbar-card {
  padding: 16px;
  display: grid;
  gap: 14px;
}

.search-box {
  display: flex;
  align-items: center;
  gap: 10px;
  border-radius: 14px;
  border: 1px solid var(--border);
  background: var(--surface-soft);
  padding: 12px 14px;
}

.search-box input {
  width: 100%;
  border: none;
  outline: none;
  background: transparent;
  color: var(--text);
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 10px;
}

.filters-grid select {
  width: 100%;
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 11px 12px;
  background: var(--surface);
  color: var(--text);
}

.workspace-grid {
  display: grid;
  grid-template-columns: 380px minmax(0, 1fr);
  gap: 16px;
}

.conversation-list-card,
.chat-panel-card {
  padding: 16px;
  display: grid;
  gap: 14px;
  min-width: 0;
}

.card-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
}

.conversation-list {
  display: grid;
  gap: 10px;
  max-height: 620px;
  overflow: auto;
}

.conversation-item {
  width: 100%;
  text-align: left;
  display: grid;
  grid-template-columns: 44px minmax(0, 1fr) auto;
  gap: 12px;
  align-items: start;
  padding: 13px;
  background: var(--surface-soft);
  border: 1px solid transparent;
}

.conversation-item.active {
  border-color: rgba(6, 170, 197, 0.4);
  background: rgba(6, 170, 197, 0.1);
}

.avatar {
  width: 44px;
  height: 44px;
  border-radius: 14px;
  background: rgba(6, 170, 197, 0.16);
  color: var(--primary);
  font-weight: 700;
  display: grid;
  place-items: center;
}

.avatar.large {
  width: 62px;
  height: 62px;
  border-radius: 18px;
  font-size: 1.2rem;
}

.conversation-core {
  min-width: 0;
  display: grid;
  gap: 5px;
}

.line-top {
  display: flex;
  justify-content: space-between;
  gap: 8px;
}

.line-top strong,
.chat-meta-grid strong,
.bubble p,
.conversation-core strong {
  color: var(--text);
}

.conversation-core p,
.conversation-core small {
  margin: 0;
}

.tag-row,
.quick-templates,
.composer-actions,
.chat-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.pill {
  background: rgba(6, 170, 197, 0.12);
  color: var(--primary);
  border-radius: 999px;
  padding: 5px 10px;
  font-size: 0.78rem;
}

.muted-pill {
  background: rgba(15, 23, 42, 0.06);
  color: #475569;
}

.unread-badge {
  min-width: 22px;
  height: 22px;
  padding: 0 6px;
  border-radius: 999px;
  display: grid;
  place-items: center;
  font-size: 0.74rem;
  font-weight: 700;
  background: var(--primary);
  color: white;
}

.chat-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
}

.chat-company {
  display: flex;
  gap: 12px;
  align-items: center;
}

.chat-company p {
  margin: 6px 0 0;
}

.status-pill {
  border-radius: 999px;
  padding: 8px 12px;
  font-size: 0.8rem;
  font-weight: 700;
  white-space: nowrap;
}

.status-pill.active {
  background: rgba(20, 184, 166, 0.14);
  color: #0f766e;
}

.status-pill.pending {
  background: rgba(234, 179, 8, 0.16);
  color: #854d0e;
}

.status-pill.inactive {
  background: rgba(239, 68, 68, 0.12);
  color: #b91c1c;
}

.chat-meta-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 10px;
}

.chat-meta-grid div {
  background: var(--surface-soft);
  border-radius: 14px;
  padding: 10px;
  display: grid;
  gap: 4px;
}

.messages-thread {
  display: grid;
  gap: 10px;
  max-height: 420px;
  overflow-y: auto;
  padding-right: 2px;
}

.message-row {
  display: flex;
}

.message-row.sent {
  justify-content: flex-end;
}

.bubble {
  max-width: min(80%, 560px);
  border-radius: 16px;
  padding: 12px 14px;
  background: rgba(15, 23, 42, 0.03);
}

.message-row.sent .bubble {
  background: rgba(6, 170, 197, 0.16);
}

.bubble p {
  margin: 0 0 6px;
}

.bubble small {
  font-size: 0.78rem;
}

.template-chip {
  background: var(--surface-soft);
  border: 1px solid var(--border);
  color: var(--text);
  padding: 8px 10px;
  font-size: 0.82rem;
}

.composer {
  display: grid;
  gap: 12px;
}

.composer-grid {
  display: grid;
  gap: 10px;
  grid-template-columns: 1fr;
}

.composer-grid label {
  display: grid;
  gap: 7px;
}

.composer-grid input,
.composer-grid textarea,
.composer-grid select {
  width: 100%;
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 11px 12px;
  background: var(--surface);
  color: var(--text);
}

.composer-grid .full {
  grid-column: 1 / -1;
}

.empty-state {
  text-align: center;
  padding: 20px;
  color: var(--muted);
  border-radius: 14px;
  background: var(--surface-soft);
}

.chat-empty {
  min-height: 260px;
  display: grid;
  place-items: center;
}

.overlay {
  position: fixed;
  inset: 0;
  background: rgba(4, 33, 44, 0.45);
  z-index: 40;
}

.compose-modal {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: min(680px, 92vw);
  z-index: 50;
  padding: 16px;
  display: grid;
  gap: 12px;
}

@media (max-width: 1180px) {
  .workspace-grid {
    grid-template-columns: 1fr;
  }

  .kpi-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 760px) {
  .page-header {
    flex-direction: column;
  }

  .filters-grid,
  .chat-meta-grid {
    grid-template-columns: 1fr;
  }

  .kpi-grid {
    grid-template-columns: 1fr;
  }

  .conversation-item {
    grid-template-columns: 44px minmax(0, 1fr);
  }

  .conversation-item .unread-badge {
    grid-column: 1 / -1;
    justify-self: start;
  }

  .chat-header {
    flex-direction: column;
  }

  .header-actions,
  .chat-actions,
  .composer-actions {
    width: 100%;
  }

  .header-actions button,
  .chat-actions button,
  .composer-actions button,
  .filters-grid .secondary-btn {
    width: 100%;
    justify-content: center;
  }

  .compose-modal {
    width: calc(100vw - 20px);
  }
}
</style>
