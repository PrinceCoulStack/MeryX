<template>
  <div class="student-messages-page">
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
                <span class="type-pill" :class="item.channel === 'university' ? 'uni' : 'company'">
                  {{ item.channel === 'university' ? t.university : t.company }}
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
              {{ selectedConversation.channel === 'university' ? t.university : t.company }}
              · {{ selectedConversation.contact }}
            </p>
          </div>
          <span
            class="type-pill"
            :class="selectedConversation.channel === 'university' ? 'uni' : 'company'"
          >
            {{ selectedConversation.channel === 'university' ? t.university : t.company }}
          </span>
        </div>

        <div class="messages-list">
          <div
            v-for="message in selectedConversation.messages"
            :key="message.id"
            class="message-item"
            :class="message.from === 'student' ? 'mine' : 'other'"
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
import { useStudentStore } from '@/stores/student.store'
import { useCandidature } from '@/compasables/useCandidature'

const locale = inject('locale', ref('en'))
const authStore = useAuthStore()
const studentStore = useStudentStore()
const { candidatures, fetchCandidatures } = useCandidature()

const texts = {
  en: {
    eyebrow: 'Student / Messages',
    title: 'Messages Center',
    subtitle:
      'Chat directly with companies and your university team about applications and follow-up.',
    conversations: 'Conversations',
    unread: 'Unread',
    searchPlaceholder: 'Search conversation, company, or university contact',
    inbox: 'Inbox',
    items: 'items',
    university: 'University',
    company: 'Company',
    noConversation: 'No conversation found with current filters.',
    selectConversation: 'Select a conversation to start chatting.',
    messagePlaceholder: 'Write your message...',
    send: 'Send',
    all: 'All',
  },
  fr: {
    eyebrow: 'Etudiant / Messages',
    title: 'Centre de messages',
    subtitle:
      'Discutez directement avec les entreprises et votre universite pour le suivi des candidatures.',
    conversations: 'Conversations',
    unread: 'Non lus',
    searchPlaceholder: 'Rechercher conversation, entreprise ou contact universite',
    inbox: 'Boite de reception',
    items: 'elements',
    university: 'Universite',
    company: 'Entreprise',
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

const currentUserId = computed(() =>
  parseId(authStore.user?.id || authStore.user?.userId || authStore.user?.profile?.id),
)
const currentUserEmail = computed(() =>
  String(authStore.user?.email || '')
    .trim()
    .toLowerCase(),
)
const currentStudent = ref(null)

const buildCompanyConversation = (candidature) => {
  const companyName =
    candidature.opportunity?.company || candidature.opportunity?.title || 'Company'
  const opportunityTitle = candidature.opportunity?.title || 'Opportunity'
  const status = String(candidature.status || 'applied').toLowerCase()
  const appliedAt = candidature.appliedDate || candidature.createdAt || candidature.lastUpdated
  const interviewAt = candidature.interviewDate
  const lastMessage =
    status === 'interview'
      ? `Interview scheduled for ${formatDateTime(interviewAt || candidature.lastUpdated || appliedAt)}`
      : status === 'accepted'
        ? `You were accepted for ${opportunityTitle}.`
        : status === 'rejected'
          ? `Application for ${opportunityTitle} was declined.`
          : `Application sent for ${opportunityTitle}.`

  const messages = [
    {
      id: `student-${candidature.id}-1`,
      from: 'student',
      text: `I applied for ${opportunityTitle}.`,
      time: formatDateTime(appliedAt),
    },
  ]

  if (interviewAt) {
    messages.push({
      id: `student-${candidature.id}-2`,
      from: 'company',
      text: `Interview scheduled for ${formatDateTime(interviewAt)}.`,
      time: formatDateTime(interviewAt),
    })
  }

  if (status !== 'applied') {
    messages.push({
      id: `student-${candidature.id}-3`,
      from: 'company',
      text: `Application status updated to ${formatStatus(candidature.status)}.`,
      time: relativeTime(candidature.lastUpdated || candidature.updatedAt || appliedAt),
    })
  }

  return {
    id: `company-${candidature.id}`,
    name: companyName,
    channel: 'company',
    contact: opportunityTitle,
    unread: status === 'applied' ? 1 : 0,
    time: relativeTime(candidature.lastUpdated || candidature.updatedAt || appliedAt),
    lastMessage,
    messages,
    raw: candidature,
  }
}

const buildUniversityConversation = () => {
  const universityName =
    currentStudent.value?.university?.name ||
    currentStudent.value?.raw?.verificationData?.universityName ||
    ''
  if (!universityName) return null

  const appliedCount = candidatures.value.length
  const interviewCount = candidatures.value.filter((item) => item.interviewDate).length
  const profileScore = Number(currentStudent.value?.profileCompletion || 0)

  return {
    id: 'university-summary',
    name: `${universityName} Career Office`,
    channel: 'university',
    contact: currentStudent.value?.university?.email || 'Career Desk',
    unread: interviewCount > 0 ? 1 : 0,
    time: appliedCount ? 'Today' : 'Now',
    lastMessage: `You currently have ${appliedCount} applications and ${interviewCount} interviews scheduled.`,
    messages: [
      {
        id: 'university-1',
        from: 'university',
        text: `Your profile is ${profileScore}% complete and ${appliedCount} opportunities are in progress.`,
        time: 'Today',
      },
      {
        id: 'university-2',
        from: 'student',
        text: `I want to keep the university team updated on my applications and interviews.`,
        time: 'Now',
      },
    ],
    raw: { source: 'university-summary' },
  }
}

const rebuildConversations = () => {
  const list = []
  const universityThread = buildUniversityConversation()
  if (universityThread) list.push(universityThread)
  list.push(...candidatures.value.map(buildCompanyConversation))

  conversations.value = list.sort((left, right) => {
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

const findCurrentStudent = () => {
  return studentStore.students.find((student) => {
    const sameUser = currentUserId.value && Number(student.userId) === Number(currentUserId.value)
    const sameEmail =
      currentUserEmail.value &&
      String(student.email || '')
        .trim()
        .toLowerCase() === currentUserEmail.value
    return sameUser || sameEmail
  })
}

const loadInbox = async () => {
  await studentStore.fetchStudents()
  const found = findCurrentStudent()

  if (found?.id) {
    await studentStore.fetchStudent(found.id)
    currentStudent.value = studentStore.student || found
    await fetchCandidatures(found.id)
  } else {
    currentStudent.value = found || null
    if (currentUserId.value) {
      await fetchCandidatures(currentUserId.value)
    }
  }

  rebuildConversations()
}

const channelOptions = computed(() => [
  { value: 'all', label: t.value.all },
  { value: 'company', label: t.value.company },
  { value: 'university', label: t.value.university },
])

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
    from: 'student',
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
.student-messages-page {
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
  font-size: clamp(1.22rem, 2.2vw, 1.7rem);
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
  background: var(--surface-soft);
  padding: 10px 12px;
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
  gap: 8px;
  flex-wrap: wrap;
}

.chip,
.primary-btn {
  border: none;
  border-radius: 999px;
  padding: 8px 12px;
  cursor: pointer;
}

.chip {
  border: 1px solid var(--border);
  background: var(--surface-soft);
  color: var(--text);
}

.chip.active {
  border-color: var(--primary);
  background: var(--primary);
  color: #fff;
}

.workspace-grid {
  display: grid;
  grid-template-columns: 340px minmax(0, 1fr);
  gap: 10px;
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
  max-height: 620px;
  overflow: auto;
}

.conversation-item {
  border: 1px solid transparent;
  border-radius: 12px;
  background: var(--surface-soft);
  cursor: pointer;
  text-align: left;
  padding: 9px;
  display: grid;
  grid-template-columns: 38px minmax(0, 1fr);
  gap: 8px;
}

.conversation-item.active {
  border-color: rgba(6, 170, 197, 0.4);
  background: rgba(6, 170, 197, 0.09);
}

.avatar {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  background: rgba(6, 170, 197, 0.16);
  color: var(--primary);
  display: grid;
  place-items: center;
  font-weight: 700;
}

.conversation-main {
  min-width: 0;
  display: grid;
  gap: 4px;
}

.top-line {
  display: flex;
  justify-content: space-between;
  gap: 8px;
}

.conversation-main p {
  margin: 0;
}

.meta-row {
  display: flex;
  gap: 8px;
  align-items: center;
  justify-content: space-between;
}

.type-pill {
  border-radius: 999px;
  padding: 4px 9px;
  font-size: 0.72rem;
  font-weight: 700;
}

.type-pill.company {
  color: #0f766e;
  background: rgba(16, 185, 129, 0.16);
}

.type-pill.uni {
  color: #1d4ed8;
  background: rgba(59, 130, 246, 0.16);
}

.unread-badge {
  min-width: 20px;
  height: 20px;
  border-radius: 999px;
  background: var(--primary);
  color: #fff;
  display: grid;
  place-items: center;
  font-size: 0.72rem;
  font-weight: 700;
}

.messages-list {
  display: grid;
  gap: 8px;
  max-height: 420px;
  overflow: auto;
  padding-right: 2px;
}

.message-item {
  max-width: 78%;
  border-radius: 12px;
  padding: 8px 10px;
}

.message-item.other {
  background: rgba(15, 23, 42, 0.06);
}

.message-item.mine {
  justify-self: end;
  background: rgba(6, 170, 197, 0.16);
}

.message-item p {
  margin: 0 0 4px;
}

.composer {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 8px;
}

.composer input {
  border: 1px solid var(--border);
  border-radius: 999px;
  background: var(--surface-soft);
  color: var(--text);
  padding: 10px 12px;
  outline: none;
}

.primary-btn {
  background: var(--primary);
  color: #fff;
}

.empty-state {
  border-radius: 12px;
  background: var(--surface-soft);
  padding: 14px;
  text-align: center;
}

.empty-chat {
  min-height: 240px;
  align-content: center;
}

@media (max-width: 1080px) {
  .hero-card,
  .workspace-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 760px) {
  .hero-stats,
  .composer {
    grid-template-columns: 1fr;
  }

  .primary-btn {
    width: 100%;
  }
}
</style>
