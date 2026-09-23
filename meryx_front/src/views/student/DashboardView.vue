<template>
  <div class="student-dashboard">
    <section class="hero-card">
      <div class="hero-left">
        <div class="avatar-wrap">
          <img :src="avatarUrl" alt="Student avatar" class="avatar" />
        </div>
        <div>
          <p class="eyebrow">{{ t.eyebrow }}</p>
          <h1>{{ dashboardTitle }}</h1>
          <p class="subtitle">{{ dashboardSubtitle }}</p>
        </div>
      </div>

      <div class="hero-actions">
        <button class="primary-btn" type="button" @click="goToProfile">{{ t.viewProfile }}</button>
        <button
          class="ghost-btn"
          type="button"
          @click="goToFormation"
          style="color: rgba(13, 43, 69, 0.92) !important"
        >
          {{ t.goTraining }}
        </button>
      </div>
    </section>

    <section class="kpi-grid">
      <article class="kpi-card">
        <span>{{ t.applications }}</span>
        <strong>{{ dashboardStats.applications }}</strong>
        <small>{{ t.sentThisTerm }}</small>
      </article>
      <article class="kpi-card">
        <span>{{ t.interviews }}</span>
        <strong>{{ dashboardStats.interviews }}</strong>
        <small>{{ t.scheduled }}</small>
      </article>
      <article class="kpi-card">
        <span>{{ t.savedOpps }}</span>
        <strong>{{ dashboardStats.saved }}</strong>
        <small>{{ t.readyToApply }}</small>
      </article>
      <article class="kpi-card">
        <span>{{ t.profileScore }}</span>
        <strong>{{ profileScore }}%</strong>
        <small>{{ t.profileScoreHint }}</small>
      </article>
    </section>

    <section class="main-grid">
      <article class="panel-card progress-card">
        <div class="panel-head">
          <h2>{{ t.progressTitle }}</h2>
        </div>
        <div class="progress-list">
          <div class="progress-item" v-for="goal in goals" :key="goal.id">
            <div class="progress-line">
              <strong>{{ goal.label }}</strong>
              <small>{{ goal.value }}%</small>
            </div>
            <div class="progress-track">
              <span :style="{ width: `${goal.value}%` }"></span>
            </div>
          </div>
        </div>
      </article>

      <article class="panel-card shortcuts-card">
        <div class="panel-head">
          <h2>{{ t.quickAccess }}</h2>
        </div>
        <div class="shortcut-list">
          <button type="button" class="shortcut-item" @click="goToOpportunities">
            <i class="bi bi-briefcase"></i>
            <span>{{ t.exploreOpportunities }}</span>
          </button>
          <button type="button" class="shortcut-item" @click="goToFormation">
            <i class="bi bi-mortarboard"></i>
            <span>{{ t.trainingPrograms }}</span>
          </button>
          <button type="button" class="shortcut-item" @click="goToProfile">
            <i class="bi bi-person"></i>
            <span>{{ t.updateProfile }}</span>
          </button>
        </div>
      </article>
    </section>

    <section class="feed-card">
      <div class="panel-head">
        <h2>{{ t.newsFeed }}</h2>
      </div>

      <div class="create-post">
        <img src="https://i.pravatar.cc/100?img=11" alt="Student avatar small" />
        <input :placeholder="t.postPlaceholder" />
      </div>

      <div class="post-list">
        <article v-for="post in posts" :key="post.id" class="post-item">
          <h3>{{ post.author }}</h3>
          <p class="post-subtitle">{{ post.title }}</p>
          <p class="post-body">{{ post.content }}</p>
          <img :src="resolveImage(post.image)" class="post-image" alt="Post visual" />

          <div class="post-actions-row">
            <button class="tiny-btn" type="button" @click="handleReaction(post.id)">
              <i class="bi bi-heart"></i> {{ post.likes }} {{ t.reactions }}
            </button>
            <span class="post-stats">{{ post.comments }} {{ t.comments }}</span>
          </div>

          <div class="comment-list" v-if="getComments(post.id).length">
            <div
              v-for="comment in getComments(post.id)"
              :key="comment.id || comment.createdAt"
              class="comment-item"
            >
              <strong>{{ comment.author || 'Member' }}</strong>
              <span>{{ comment.message || comment.content }}</span>
            </div>
          </div>

          <form class="comment-form" @submit.prevent="submitComment(post.id)">
            <input v-model="commentDrafts[post.id]" type="text" :placeholder="t.postPlaceholder" />
            <button class="secondary-btn" type="submit">{{ t.comment }}</button>
          </form>
        </article>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed, inject, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useCompanyActualities } from '@/compasables/useCompanyActualities'
import { useCandidature } from '@/compasables/useCandidature'
import { useAuthStore } from '@/stores/auth.store'
import { useStudentStore } from '@/stores/student.store'

const router = useRouter()
const locale = inject('locale', ref('en'))
const { fetchActualities, addReaction, addComment } = useCompanyActualities()
const authStore = useAuthStore()
const studentStore = useStudentStore()
const { candidatures, savedOpportunities, fetchCandidatures, fetchSavedOpportunities } =
  useCandidature()

const translations = {
  en: {
    eyebrow: 'Student / Dashboard',
    viewProfile: 'View Profile',
    goTraining: 'Training Hub',
    applications: 'Applications',
    interviews: 'Interviews',
    savedOpps: 'Saved Opportunities',
    profileScore: 'Profile Completion',
    sentThisTerm: 'sent this term',
    scheduled: 'scheduled sessions',
    readyToApply: 'ready to apply',
    profileScoreHint: 'complete profile for better matching',
    progressTitle: 'Career Progress',
    quickAccess: 'Quick Access',
    exploreOpportunities: 'Explore Opportunities',
    trainingPrograms: 'Company Training Programs',
    updateProfile: 'Update My Profile',
    newsFeed: 'Latest News Feed',
    postPlaceholder: 'Share an update, ask a question, or post your learning progress...',
    reactions: 'reactions',
    comments: 'comments',
    comment: 'Comment',
  },
  fr: {
    eyebrow: 'Etudiant / Tableau de bord',
    viewProfile: 'Voir profil',
    goTraining: 'Hub Formation',
    applications: 'Candidatures',
    interviews: 'Entretiens',
    savedOpps: 'Opportunites sauvegardees',
    profileScore: 'Profil complete',
    sentThisTerm: 'envoyees ce semestre',
    scheduled: 'sessions programmees',
    readyToApply: 'pret a postuler',
    profileScoreHint: 'completez votre profil pour un meilleur matching',
    progressTitle: 'Progression Carriere',
    quickAccess: 'Acces Rapide',
    exploreOpportunities: 'Explorer les opportunites',
    trainingPrograms: 'Programmes de formation entreprises',
    updateProfile: 'Mettre a jour mon profil',
    newsFeed: 'Fil d actualites',
    postPlaceholder: 'Partagez une actualite, posez une question, ou publiez votre progression...',
    reactions: 'reactions',
    comments: 'commentaires',
    comment: 'Commenter',
  },
}

const t = computed(() => translations[locale.value] || translations.en)
const currentUserId = computed(() => {
  const candidate = authStore.user?.id || authStore.user?.userId || authStore.user?.profile?.id
  if (!candidate && candidate !== 0) return null
  const parsed = Number(String(candidate).split('/').pop())
  return Number.isFinite(parsed) ? parsed : null
})
const currentUserEmail = computed(() =>
  String(authStore.user?.email || '')
    .trim()
    .toLowerCase(),
)
const currentStudent = ref(null)

const posts = ref([])
const commentDrafts = ref({})

const avatarUrl = computed(() => {
  const profileUrl = currentStudent.value?.profileUrl || currentStudent.value?.raw?.profileUrl || ''
  if (/^https?:\/\//i.test(profileUrl) || /^data:image\/[a-z0-9.+-]+;base64,/i.test(profileUrl)) {
    return profileUrl
  }

  const name = dashboardName.value || 'Student'
  return `https://ui-avatars.com/api/?background=0D8ABC&color=fff&name=${encodeURIComponent(name)}`
})

const dashboardName = computed(() => {
  return (
    currentStudent.value?.fullName ||
    currentStudent.value?.user?.fullName ||
    authStore.user?.fullName ||
    [authStore.user?.firstName, authStore.user?.lastName].filter(Boolean).join(' ') ||
    'Student'
  )
})

const dashboardTitle = computed(() => `${t.value.eyebrow} · ${dashboardName.value}`)

const dashboardSubtitle = computed(() => {
  const applications = dashboardStats.value.applications
  const interviews = dashboardStats.value.interviews
  const profileScoreValue = profileScore.value
  return `You have ${applications} applications, ${interviews} interviews, and a ${profileScoreValue}% profile score.`
})

const dashboardStats = computed(() => {
  const applications = candidatures.value.length
  const interviews = candidatures.value.filter((item) =>
    /interview/i.test(String(item.status || '')),
  ).length
  const saved = savedOpportunities.value.length
  return {
    applications,
    interviews,
    saved,
  }
})

const profileScore = computed(() => Number(currentStudent.value?.profileCompletion || 0))

const goals = computed(() => {
  const profileGoal = Math.max(profileScore.value, 20)
  const applicationsGoal = Math.min(100, dashboardStats.value.applications * 18)
  const opportunityGoal = Math.min(100, dashboardStats.value.saved * 20 + 20)

  return [
    { id: 1, label: t.value.applications, value: applicationsGoal },
    { id: 2, label: t.value.profileScore, value: profileGoal },
    { id: 3, label: t.value.trainingPrograms, value: opportunityGoal },
  ]
})

const resolveImage = (value) => {
  if (!value || typeof value !== 'string' || value.trim() === '') {
    return 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d'
  }

  const trimmed = value.trim().replace(/^['"]+|['"]+$/g, '')

  if (/^data:image\/[a-z0-9.+-]+;base64,/i.test(trimmed)) {
    return trimmed
  }

  try {
    const normalized = trimmed.startsWith('//') ? `https:${trimmed}` : trimmed
    const parsed = new URL(normalized)
    if (parsed.protocol === 'http:' || parsed.protocol === 'https:') {
      return parsed.href
    }
  } catch {
    return 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d'
  }

  return 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d'
}

const getComments = (id) => {
  const target = posts.value.find((post) => Number(post.id) === Number(id))
  return Array.isArray(target?.commentList) ? target.commentList : []
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

const loadDashboardData = async () => {
  try {
    await studentStore.fetchStudents()
    const found = findCurrentStudent()
    if (found?.id) {
      await studentStore.fetchStudent(found.id)
      currentStudent.value = studentStore.student || found
      await Promise.all([fetchCandidatures(found.id), fetchSavedOpportunities(found.id)])
      return
    }

    currentStudent.value = null
    if (currentUserId.value) {
      await Promise.all([
        fetchCandidatures(currentUserId.value),
        fetchSavedOpportunities(currentUserId.value),
      ])
    }
  } catch {
    currentStudent.value = null
  }
}

const handleReaction = (id) => {
  addReaction(id)
}

const submitComment = (id) => {
  const value = (commentDrafts.value[id] || '').trim()
  if (!value) return
  addComment(id, value)
  commentDrafts.value[id] = ''
}

onMounted(async () => {
  await loadDashboardData()
  const actualities = await fetchActualities().catch(() => [])
  posts.value = actualities.map((item) => ({
    ...item,
    title: item.companyName || item.title,
    author: item.author || item.companyName || 'Company',
    content: item.content || item.summary || '',
    image: resolveImage(item.image),
    comments: Number(item.comments || item.commentList?.length || 0),
    likes: Number(item.likes || 0),
  }))
})

const goToProfile = () => router.push({ name: 'profile' })
const goToFormation = () => router.push({ name: 'formation' })
const goToOpportunities = () => router.push({ name: 'studentOpportunities' })
</script>

<style scoped>
.student-dashboard {
  display: grid;
  gap: 16px;
  padding: 14px;
  background: var(--bg);
  min-height: 100%;
}

.hero-card,
.kpi-card,
.panel-card,
.feed-card,
.post-item,
.create-post {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 18px;
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
}

.hero-card {
  display: grid;
  grid-template-columns: 1.6fr auto;
  gap: 12px;
  align-items: center;
  padding: 14px;
  position: relative;
  overflow: hidden;
}

.hero-card::before {
  content: '';
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 86% 12%, rgba(6, 170, 197, 0.16), transparent 45%),
    linear-gradient(130deg, rgba(15, 118, 110, 0.09), rgba(14, 165, 233, 0.05));
  pointer-events: none;
}

.hero-card > * {
  position: relative;
  z-index: 1;
}

.hero-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.avatar-wrap {
  flex: 0 0 auto;
}

.avatar {
  width: 62px;
  height: 62px;
  border-radius: 14px;
  object-fit: cover;
  border: 2px solid rgba(255, 255, 255, 0.8);
}

.eyebrow {
  margin: 0;
  color: var(--primary);
  font-size: 0.74rem;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-weight: 700;
}

.hero-card h1,
.panel-head h2,
.post-item h3,
.progress-line strong {
  margin: 0;
  color: var(--text);
}

.hero-card h1 {
  font-size: clamp(1.22rem, 2.2vw, 1.6rem);
  line-height: 1.22;
  margin-top: 6px;
}

.subtitle,
.kpi-card span,
.kpi-card small,
.progress-line small,
.post-subtitle,
.post-body,
.post-stats,
.panel-head p {
  color: var(--muted);
}

.subtitle {
  margin: 7px 0 0;
  max-width: 620px;
}

.hero-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.primary-btn,
.ghost-btn,
.shortcut-item {
  border: none;
  border-radius: 999px;
  padding: 9px 13px;
  cursor: pointer;
}

.primary-btn {
  background: var(--primary);
  color: #fff;
}

.ghost-btn {
  border: 1px solid var(--border);
  color: var(--text);
  background: var(--surface);
}

.kpi-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 10px;
}

.kpi-card {
  padding: 12px;
  display: grid;
  gap: 3px;
}

.kpi-card strong {
  color: var(--text);
  font-size: 1.3rem;
}

.main-grid {
  display: grid;
  grid-template-columns: 1.4fr 1fr;
  gap: 12px;
}

.panel-card,
.feed-card {
  padding: 14px;
}

.panel-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.progress-list,
.shortcut-list,
.post-list {
  display: grid;
  gap: 10px;
}

.post-list {
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px;
}

.progress-item {
  background: var(--surface-soft);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 10px;
}

.progress-line {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
}

.progress-track {
  height: 9px;
  border-radius: 999px;
  background: rgba(15, 23, 42, 0.08);
  overflow: hidden;
}

.progress-track span {
  display: block;
  height: 100%;
  border-radius: 999px;
  background: linear-gradient(90deg, #0f766e, #d4a017);
}

.shortcut-item {
  border-radius: 12px;
  border: 1px solid var(--border);
  background: var(--surface-soft);
  color: var(--text);
  display: flex;
  align-items: center;
  gap: 8px;
  justify-content: flex-start;
}

.create-post {
  padding: 10px;
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 10px;
  align-items: center;
  margin-bottom: 12px;
}

.create-post img {
  width: 42px;
  height: 42px;
  border-radius: 50%;
}

.create-post input {
  border: 1px solid var(--border);
  border-radius: 999px;
  background: var(--surface-soft);
  padding: 11px 13px;
  color: var(--text);
  outline: none;
}

.post-item {
  padding: 12px;
  height: 100%;
}

.post-subtitle {
  margin: 8px 0 0;
}

.post-body {
  margin: 8px 0 0;
  line-height: 1.6;
}

.post-image {
  width: 100%;
  border-radius: 12px;
  margin-top: 10px;
}

.post-stats {
  margin-top: 8px;
  font-size: 0.88rem;
}

@media (min-width: 1500px) {
  .post-list {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }
}

@media (max-width: 1160px) {
  .main-grid {
    grid-template-columns: 1fr;
  }

  .kpi-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .post-list {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 760px) {
  .hero-card {
    grid-template-columns: 1fr;
  }

  .hero-left {
    align-items: flex-start;
  }

  .hero-actions {
    width: 100%;
  }

  .hero-actions button {
    width: 100%;
  }

  .kpi-grid {
    grid-template-columns: 1fr;
  }

  .post-list {
    grid-template-columns: 1fr;
  }
}
</style>
