<template>
  <div class="company-messages-page">
    <section class="hero-card">
      <div>
        <p class="eyebrow">{{ t.eyebrow }}</p>
        <h1>{{ t.title }}</h1>
        <p class="hero-subtitle">{{ t.subtitle }}</p>
      </div>

      <div class="hero-stats">
        <article class="mini-stat">
          <span>{{ t.conversations }}</span>
          <strong>{{ filteredConversations.length }}</strong>
        </article>
        <article class="mini-stat">
          <span>{{ t.unread }}</span>
          <strong>{{ unreadCount }}</strong>
        </article>
      </div>
    </section>

    <section class="toolbar-card">
      <div class="search-box">
        <i class="bi bi-search"></i>
        <input v-model="searchTerm" type="text" :placeholder="t.searchPlaceholder" />
      </div>

      <div class="chips-row">
        <button
          v-for="option in channelOptions"
          :key="option.value"
          type="button"
          class="chip"
          :class="{ active: channelFilter === option.value }"
          @click="channelFilter = option.value"
        >
          {{ option.label }}
        </button>
      </div>
    </section>

    <section class="workspace-grid">
      <article class="conversations-card">
        <div class="section-head">
          <h2>{{ t.inbox }}</h2>
          <span>{{ filteredConversations.length }} {{ t.items }}</span>
        </div>

        <div class="conversation-list" v-if="filteredConversations.length">
          <button
            v-for="item in filteredConversations"
            :key="item.id"
            class="conversation-item"
            :class="{ active: selectedConversation?.id === item.id }"
            type="button"
            @click="selectConversation(item)"
          >
            <div class="avatar">{{ item.name.charAt(0) }}</div>
            <div class="conversation-main">
              <div class="top-line">
                <strong>{{ item.name }}</strong>
                <small>{{ item.time }}</small>
              </div>
              <p>{{ item.lastMessage }}</p>
              <div class="meta-row">
                <span
                  class="type-pill"
                  :class="
                    item.channel === 'university'
                      ? 'uni'
                      : item.channel === 'student'
                        ? 'student'
                        : 'other'
                  "
                >
                  {{ channelText(item.channel) }}
                </span>
                <span v-if="item.unread > 0" class="unread-badge">{{ item.unread }}</span>
              </div>
            </div>
          </button>
        </div>

        <div class="empty-state" v-else>{{ t.noConversation }}</div>
      </article>

      <article class="chat-card" v-if="selectedConversation">
        <div class="section-head">
          <div>
            <h2>{{ selectedConversation.name }}</h2>
            <p class="muted">
              {{ channelText(selectedConversation.channel) }} · {{ selectedConversation.contact }}
            </p>
          </div>
          <span
            class="type-pill"
            :class="
              selectedConversation.channel === 'university'
                ? 'uni'
                : selectedConversation.channel === 'student'
                  ? 'student'
                  : 'other'
            "
          >
            {{ channelText(selectedConversation.channel) }}
          </span>
        </div>

        <div class="messages-list">
          <div
            v-for="message in selectedConversation.messages"
            :key="message.id"
            class="message-item"
            :class="message.from === 'company' ? 'mine' : 'other'"
          >
            <p>{{ message.text }}</p>
            <small>{{ message.time }}</small>
          </div>
        </div>

        <form class="composer" @submit.prevent="sendMessage">
          <input v-model="messageText" type="text" :placeholder="t.messagePlaceholder" />
          <button class="primary-btn" type="submit" :disabled="!messageText.trim()">
            {{ t.send }}
          </button>
        </form>
      </article>

      <article class="chat-card empty-chat" v-else>
        <div class="empty-state">{{ t.selectConversation }}</div>
      </article>
    </section>
  </div>
</template>

<script setup>
import { computed, inject, onMounted, ref } from 'vue'
import { useAuthStore } from '@/stores/auth.store'
import { useCompanyOpportunities } from '@/compasables/useCompanyOpportunities'

const locale = inject('locale', ref('en'))
const authStore = useAuthStore()
const { companyOpportunities, currentCompanyId, fetchOpportunities, getOpportunityCandidates } =
  useCompanyOpportunities()

const texts = {
  en: {
    eyebrow: 'Company / Messages',
    title: 'Messages Center',
    subtitle: 'Chat with students and university partners from one place.',
    conversations: 'Conversations',
    unread: 'Unread',
    searchPlaceholder: 'Search conversation, student, or university contact',
    inbox: 'Inbox',
    items: 'items',
    university: 'University',
    student: 'Student',
    partner: 'Partner',
    noConversation: 'No conversation found with current filters.',
    selectConversation: 'Select a conversation to start chatting.',
    messagePlaceholder: 'Write your message...',
    send: 'Send',
    all: 'All',
  },
  fr: {
    eyebrow: 'Entreprise / Messages',
    title: 'Centre de messages',
    subtitle: 'Discutez avec les etudiants et les partenaires universitaires au meme endroit.',
    conversations: 'Conversations',
    unread: 'Non lus',
    searchPlaceholder: 'Rechercher conversation, etudiant ou contact universite',
    inbox: 'Boite de reception',
    items: 'elements',
    university: 'Universite',
    student: 'Etudiant',
    partner: 'Partenaire',
    noConversation: 'Aucune conversation trouvee avec ces filtres.',
    selectConversation: 'Selectionnez une conversation pour discuter.',
    messagePlaceholder: 'Ecrivez votre message...',
    send: 'Envoyer',
    all: 'Tout',
  },
}

const t = computed(() => texts[locale.value] || texts.en)

const searchTerm = ref('')
const channelFilter = ref('all')
const messageText = ref('')

const conversations = ref([])
const selectedConversation = ref(null)

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

  const diffMinutes = Math.max(0, Math.floor((Date.now() - date.getTime()) / 60000))
  if (diffMinutes < 1) return 'Now'
  if (diffMinutes < 60) return `${diffMinutes}m ago`

  const diffHours = Math.floor(diffMinutes / 60)
  if (diffHours < 24) return `${diffHours}h ago`

  const diffDays = Math.floor(diffHours / 24)
  if (diffDays < 7) return `${diffDays}d ago`

  return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' })
}

const formatStatus = (status) => {
  const normalized = String(status || '')
    .trim()
    .toLowerCase()
  if (normalized === 'interview') return 'Interview Scheduled'
  if (normalized === 'accepted' || normalized === 'offer') return 'Accepted'
  if (normalized === 'rejected') return 'Rejected'
  return 'Applied'
}

const loggedCompanyId = computed(
  () => currentCompanyId.value || parseId(authStore.user?.companyId || authStore.user?.company?.id),
)

const buildStudentThread = (candidate) => {
  const student = candidate.student || {}
  const opportunity = candidate.opportunity || {}
  const studentName = student.fullName || student.user?.fullName || 'Student'
  const opportunityTitle = opportunity.title || 'Opportunity'
  const appliedAt = candidate.appliedDate || candidate.createdAt || candidate.lastUpdated
  const interviewAt = candidate.interviewDate
  const status = String(candidate.status || 'applied').toLowerCase()

  const messages = [
    {
      id: `student-${candidate.id}-1`,
      from: 'student',
      text: `${studentName} applied for ${opportunityTitle}.`,
      time: formatDateTime(appliedAt),
    },
  ]

  if (interviewAt) {
    messages.push({
      id: `student-${candidate.id}-2`,
      from: 'company',
      text: `Interview scheduled for ${formatDateTime(interviewAt)}.`,
      time: formatDateTime(interviewAt),
    })
  }

  if (status !== 'applied') {
    messages.push({
      id: `student-${candidate.id}-3`,
      from: 'company',
      text: `Application status updated to ${formatStatus(candidate.status)}.`,
      time: relativeTime(candidate.lastUpdated || candidate.updatedAt || appliedAt),
    })
  }

  return {
    id: `student-${candidate.id}`,
    name: studentName,
    channel: 'student',
    contact: student.email || student.user?.email || opportunityTitle,
    unread: status === 'applied' ? 1 : 0,
    time: relativeTime(candidate.lastUpdated || candidate.updatedAt || appliedAt),
    lastMessage:
      status === 'interview'
        ? `Interview scheduled for ${formatDateTime(interviewAt || candidate.lastUpdated || appliedAt)}`
        : status === 'accepted'
          ? `${studentName} was accepted for ${opportunityTitle}.`
          : status === 'rejected'
            ? `${studentName}'s application for ${opportunityTitle} was declined.`
            : `${studentName} applied for ${opportunityTitle}.`,
    messages,
    raw: candidate,
  }
}

const buildUniversityThread = (candidates) => {
  const grouped = new Map()

  for (const candidate of candidates) {
    const universityName =
      candidate.student?.university?.name ||
      candidate.student?.raw?.verificationData?.universityName
    if (!universityName) continue

    const key = universityName.toLowerCase()
    if (!grouped.has(key)) {
      grouped.set(key, {
        universityName,
        count: 0,
        latest: candidate,
        candidates: [],
      })
    }

    const bucket = grouped.get(key)
    bucket.count += 1
    bucket.candidates.push(candidate)
    const latestTime =
      safeDate(candidate.lastUpdated || candidate.updatedAt || candidate.appliedDate)?.getTime() ||
      0
    const currentLatestTime =
      safeDate(
        bucket.latest.lastUpdated || bucket.latest.updatedAt || bucket.latest.appliedDate,
      )?.getTime() || 0
    if (latestTime >= currentLatestTime) bucket.latest = candidate
  }

  return [...grouped.values()].map((bucket) => {
    const latest = bucket.latest
    const appliedAt = latest.appliedDate || latest.createdAt || latest.lastUpdated
    const messages = [
      {
        id: `uni-${bucket.universityName}-1`,
        from: 'company',
        text: `${bucket.count} student application${bucket.count > 1 ? 's' : ''} are linked to ${bucket.universityName}.`,
        time: relativeTime(appliedAt),
      },
    ]

    if (latest.interviewDate) {
      messages.push({
        id: `uni-${bucket.universityName}-2`,
        from: 'university',
        text: `Interview window updated for ${bucket.universityName}.`,
        time: formatDateTime(latest.interviewDate),
      })
    }

    return {
      id: `university-${bucket.universityName}`,
      name: `${bucket.universityName} Career Office`,
      channel: 'university',
      contact: latest.student?.university?.email || 'Placement Desk',
      unread: bucket.candidates.filter(
        (candidate) => String(candidate.status || '').toLowerCase() === 'applied',
      ).length,
      time: relativeTime(latest.lastUpdated || latest.updatedAt || appliedAt),
      lastMessage: `${bucket.count} candidates linked to ${bucket.universityName}.`,
      messages,
      raw: { universityName: bucket.universityName },
    }
  })
}

const rebuildConversations = async () => {
  if (!loggedCompanyId.value) {
    conversations.value = []
    selectedConversation.value = null
    return
  }

  const rows = []
  for (const opportunity of companyOpportunities.value) {
    const candidates = await getOpportunityCandidates(opportunity.id)
    rows.push(
      ...candidates.map(buildStudentThread).map((conversation) => ({
        ...conversation,
        opportunityId: opportunity.id,
        opportunityTitle: opportunity.title,
      })),
    )
    rows.push(...buildUniversityThread(candidates))
  }

  conversations.value = rows.sort((left, right) => {
    const leftTime =
      safeDate(left.raw?.lastUpdated || left.raw?.updatedAt || left.raw?.appliedDate)?.getTime() ||
      0
    const rightTime =
      safeDate(
        right.raw?.lastUpdated || right.raw?.updatedAt || right.raw?.appliedDate,
      )?.getTime() || 0
    return rightTime - leftTime
  })

  if (!selectedConversation.value && conversations.value.length) {
    selectedConversation.value = conversations.value[0]
  }
}

const loadInbox = async () => {
  await fetchOpportunities()
  await rebuildConversations()
}

const channelOptions = computed(() => [
  { value: 'all', label: t.value.all },
  { value: 'student', label: t.value.student },
  { value: 'university', label: t.value.university },
])

const channelText = (channel) => {
  if (channel === 'student') return t.value.student
  if (channel === 'university') return t.value.university
  return t.value.partner
}

const filteredConversations = computed(() => {
  const q = searchTerm.value.trim().toLowerCase()
  return conversations.value.filter((item) => {
    const channelOk = channelFilter.value === 'all' || item.channel === channelFilter.value
    const queryOk =
      !q ||
      item.name.toLowerCase().includes(q) ||
      item.contact.toLowerCase().includes(q) ||
      item.lastMessage.toLowerCase().includes(q)
    return channelOk && queryOk
  })
})

const unreadCount = computed(() => conversations.value.reduce((acc, item) => acc + item.unread, 0))

const selectConversation = (item) => {
  selectedConversation.value = item
  if (item.unread > 0) item.unread = 0
}

const sendMessage = () => {
  if (!selectedConversation.value || !messageText.value.trim()) return

  selectedConversation.value.messages.push({
    id: Date.now(),
    from: 'company',
    text: messageText.value.trim(),
    time: 'Now',
  })

  selectedConversation.value.lastMessage = messageText.value.trim()
  selectedConversation.value.time = 'Now'
  messageText.value = ''
}

onMounted(async () => {
  await loadInbox()
})
</script>

<style scoped>
.company-messages-page {
  display: grid;
  gap: 14px;
  padding: 14px;
  background: var(--bg);
  min-height: 100%;
}

.hero-card,
.toolbar-card,
.conversations-card,
.chat-card,
.mini-stat {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 18px;
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
}

.hero-card {
  display: grid;
  grid-template-columns: 1.5fr 1fr;
  gap: 10px;
  padding: 14px;
}

.eyebrow {
  margin: 0;
  color: var(--primary);
  font-size: 0.75rem;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  font-weight: 700;
}

.hero-card h1,
.section-head h2,
.conversation-main strong,
.message-item p {
  margin: 0;
  color: var(--text);
}

.hero-card h1 {
  margin-top: 6px;
  font-size: clamp(1.2rem, 2.1vw, 1.65rem);
}

.hero-subtitle,
.mini-stat span,
.muted,
.conversation-main p,
.conversation-main small,
.section-head span,
.message-item small,
.empty-state {
  color: var(--muted);
}

.hero-stats {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 8px;
  align-content: start;
}

.mini-stat {
  padding: 10px;
  display: grid;
  gap: 2px;
}

.mini-stat strong {
  color: var(--text);
  font-size: 1.2rem;
}

.toolbar-card {
  padding: 12px;
  display: grid;
  gap: 10px;
}

.search-box {
  display: flex;
  align-items: center;
  gap: 8px;
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 10px 12px;
  background: var(--surface-soft);
}

.search-box i {
  color: var(--muted);
}

.search-box input {
  width: 100%;
  border: none;
  outline: none;
  background: transparent;
  color: var(--text);
}

.chips-row {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.chip {
  border: 1px solid var(--border);
  background: var(--surface);
  color: var(--muted);
  border-radius: 999px;
  padding: 8px 12px;
  cursor: pointer;
}

.chip.active {
  background: rgba(6, 170, 197, 0.14);
  color: var(--primary);
  border-color: rgba(6, 170, 197, 0.3);
}

.workspace-grid {
  display: grid;
  grid-template-columns: minmax(250px, 330px) minmax(0, 1fr);
  gap: 12px;
}

.conversations-card,
.chat-card {
  padding: 12px;
  display: grid;
  gap: 10px;
}

.section-head {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 8px;
}

.conversation-list {
  display: grid;
  gap: 8px;
  max-height: 560px;
  overflow: auto;
  padding-right: 4px;
}

.conversation-item {
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 8px;
  border: 1px solid var(--border);
  border-radius: 14px;
  background: var(--surface);
  padding: 10px;
  text-align: left;
  cursor: pointer;
}

.conversation-item.active {
  border-color: rgba(6, 170, 197, 0.45);
  background: rgba(6, 170, 197, 0.07);
}

.avatar {
  width: 38px;
  height: 38px;
  border-radius: 12px;
  display: grid;
  place-items: center;
  background: rgba(6, 170, 197, 0.14);
  color: #0c6a7a;
  font-weight: 700;
}

.conversation-main {
  display: grid;
  gap: 3px;
}

.top-line {
  display: flex;
  justify-content: space-between;
  gap: 6px;
}

.conversation-main p {
  margin: 0;
  font-size: 0.9rem;
}

.meta-row {
  display: flex;
  gap: 6px;
  align-items: center;
  flex-wrap: wrap;
}

.type-pill {
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  padding: 3px 8px;
  font-size: 0.72rem;
  font-weight: 700;
}

.type-pill.student {
  color: #7c3aed;
  background: rgba(124, 58, 237, 0.16);
}

.type-pill.uni {
  color: #0f766e;
  background: rgba(20, 184, 166, 0.14);
}

.type-pill.other {
  color: #1d4ed8;
  background: rgba(37, 99, 235, 0.14);
}

.unread-badge {
  min-width: 24px;
  height: 24px;
  border-radius: 999px;
  background: #ef4444;
  color: #fff;
  font-size: 0.78rem;
  display: grid;
  place-items: center;
  font-weight: 700;
}

.messages-list {
  display: grid;
  gap: 8px;
  max-height: 430px;
  overflow: auto;
  padding-right: 4px;
}

.message-item {
  max-width: 80%;
  border-radius: 14px;
  padding: 8px 10px;
  display: grid;
  gap: 4px;
}

.message-item.mine {
  justify-self: end;
  background: rgba(6, 170, 197, 0.16);
}

.message-item.other {
  justify-self: start;
  background: var(--surface-soft);
}

.message-item p,
.message-item small {
  margin: 0;
}

.composer {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 8px;
}

.composer input {
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 10px 12px;
  background: var(--surface-soft);
  color: var(--text);
}

.primary-btn {
  border: none;
  border-radius: 12px;
  padding: 0 14px;
  background: var(--primary);
  color: #fff;
  cursor: pointer;
}

.primary-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.empty-chat {
  align-content: center;
}

.empty-state {
  text-align: center;
  padding: 18px;
}

@media (max-width: 1080px) {
  .hero-card,
  .workspace-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 720px) {
  .company-messages-page {
    padding: 10px;
  }

  .hero-stats {
    grid-template-columns: 1fr;
  }

  .composer {
    grid-template-columns: 1fr;
  }

  .primary-btn {
    min-height: 42px;
  }
}
</style>
