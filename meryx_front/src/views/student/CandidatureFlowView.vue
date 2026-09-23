<template>
  <div class="candidature-page">
    <section class="hero-card">
      <div>
        <p class="eyebrow">{{ t.eyebrow }}</p>
        <h1>{{ t.title }}</h1>
        <p class="hero-text">{{ t.subtitle }}</p>
      </div>

      <div class="hero-stats">
        <article class="mini-stat">
          <span>{{ t.totalApplications }}</span>
          <strong>{{ candidatures.length }}</strong>
        </article>
        <article class="mini-stat">
          <span>{{ t.appliedStatus }}</span>
          <strong>{{ appliedCount }}</strong>
        </article>
        <article class="mini-stat">
          <span>{{ t.interviewStatus }}</span>
          <strong>{{ interviewCount }}</strong>
        </article>
        <article class="mini-stat">
          <span>{{ t.offerStatus }}</span>
          <strong>{{ offerCount }}</strong>
        </article>
      </div>
    </section>

    <section class="toolbar-card">
      <div class="filters-row">
        <select v-model="statusFilter">
          <option value="all">{{ t.allStatuses }}</option>
          <option value="applied">{{ t.appliedStatus }}</option>
          <option value="interview">{{ t.interviewStatus }}</option>
          <option value="offer">{{ t.offerStatus }}</option>
          <option value="accepted">{{ t.acceptedStatus }}</option>
          <option value="rejected">{{ t.rejectedStatus }}</option>
        </select>

        <div class="search-input">
          <i class="bi bi-search"></i>
          <input v-model="searchTerm" type="text" :placeholder="t.searchPlaceholder" />
        </div>

        <button type="button" class="ghost-btn" @click="resetFilters">
          <i class="bi bi-arrow-counterclockwise"></i>
          {{ t.reset }}
        </button>
      </div>
    </section>

    <p v-if="isLoadingCandidatures" class="info-state">{{ t.loading }}</p>
    <p v-else-if="candidatureError" class="error-state">{{ candidatureError }}</p>

    <section class="content-grid" v-else>
      <article class="list-card">
        <div class="section-head">
          <h2>{{ t.yourApplications }}</h2>
          <span>{{ filteredCandidatures.length }} {{ t.items }}</span>
        </div>

        <div class="timeline-container" v-if="filteredCandidatures.length">
          <article
            class="candidature-card"
            v-for="candidature in filteredCandidatures"
            :key="candidature.id"
          >
            <div class="card-header">
              <div class="opportunity-info">
                <h3>{{ candidature.opportunity.title }}</h3>
                <p class="company-line">
                  <i class="bi bi-building"></i>
                  {{ candidature.opportunity.company }}
                </p>
              </div>
              <div class="status-badge" :class="`status-${candidature.status}`">
                {{ getStatusLabel(candidature.status) }}
              </div>
            </div>

            <div class="card-timeline">
              <div class="timeline-item" :class="{ completed: candidature.status !== 'applied' }">
                <div class="timeline-marker applied"></div>
                <div class="timeline-content">
                  <strong>{{ t.applied }}</strong>
                  <p>{{ formatDate(candidature.appliedDate) }}</p>
                </div>
              </div>

              <div
                class="timeline-item"
                :class="{
                  completed:
                    candidature.status === 'interview' ||
                    candidature.status === 'offer' ||
                    candidature.status === 'accepted',
                }"
              >
                <div class="timeline-marker interview"></div>
                <div class="timeline-content">
                  <strong>{{ t.interviewStatus }}</strong>
                  <p v-if="candidature.interviewDate">
                    {{ formatDate(candidature.interviewDate) }}
                  </p>
                  <p v-else class="muted">{{ t.pending }}</p>
                </div>
              </div>

              <div
                class="timeline-item"
                :class="{
                  completed: candidature.status === 'offer' || candidature.status === 'accepted',
                }"
              >
                <div class="timeline-marker offer"></div>
                <div class="timeline-content">
                  <strong>{{ t.offerStatus }}</strong>
                  <p v-if="candidature.status === 'offer' || candidature.status === 'accepted'">
                    {{ formatDate(candidature.lastUpdated) }}
                  </p>
                  <p v-else class="muted">{{ t.pending }}</p>
                </div>
              </div>

              <div class="timeline-item" :class="{ completed: candidature.status === 'accepted' }">
                <div class="timeline-marker accepted"></div>
                <div class="timeline-content">
                  <strong>{{ t.acceptedStatus }}</strong>
                  <p v-if="candidature.status === 'accepted'">
                    {{ formatDate(candidature.lastUpdated) }}
                  </p>
                  <p v-else class="muted">{{ t.pending }}</p>
                </div>
              </div>
            </div>

            <div v-if="candidature.feedback || candidature.notes" class="card-notes">
              <div v-if="candidature.feedback" class="note-section">
                <strong>{{ t.feedback }}</strong>
                <p>{{ candidature.feedback }}</p>
              </div>
              <div v-if="candidature.notes" class="note-section">
                <strong>{{ t.notes }}</strong>
                <p>{{ candidature.notes }}</p>
              </div>
            </div>

            <div class="card-actions">
              <button
                v-if="candidature.status === 'offer'"
                class="primary-btn"
                type="button"
                @click="acceptOffer(candidature)"
              >
                {{ t.acceptOffer }}
              </button>
              <button
                class="secondary-btn"
                type="button"
                @click="viewOpportunity(candidature.opportunity)"
              >
                {{ t.viewOpportunity }}
              </button>
            </div>
          </article>
        </div>

        <div class="empty-state" v-else>{{ t.noApplications }}</div>
      </article>

      <article class="list-card">
        <div class="section-head">
          <h2>{{ t.savedOpportunities }}</h2>
          <span>{{ savedOpportunities.length }} {{ t.items }}</span>
        </div>

        <div class="cards-grid" v-if="savedOpportunities.length">
          <article class="saved-card" v-for="saved in savedOpportunities" :key="saved.id">
            <div class="card-top">
              <h4>{{ saved.opportunity.title }}</h4>
              <small>{{ formatDate(saved.savedDate) }}</small>
            </div>

            <p class="company-line">
              <i class="bi bi-building"></i>
              {{ saved.opportunity.company }}
            </p>

            <p v-if="saved.notes" class="saved-notes">{{ saved.notes }}</p>

            <div class="card-actions">
              <button class="primary-btn" type="button" @click="applyFromSaved(saved)">
                {{ t.apply }}
              </button>
              <button class="ghost-btn" type="button" @click="removeSaved(saved)">
                <i class="bi bi-trash"></i>
                {{ t.remove }}
              </button>
            </div>
          </article>
        </div>

        <div class="empty-state" v-else>{{ t.noSaved }}</div>
      </article>
    </section>
  </div>
</template>

<script setup>
import { computed, inject, onMounted, ref } from 'vue'
import { useAuthStore } from '@/stores/auth.store'
import { useStudentStore } from '@/stores/student.store'
import { useCandidature } from '@/compasables/useCandidature'
import { useToast } from '@/compasables/useToast'

const locale = inject('locale', ref('en'))
const authStore = useAuthStore()
const studentStore = useStudentStore()
const {
  candidatures,
  savedOpportunities,
  isLoadingCandidatures,
  candidatureError,
  fetchCandidatures,
  fetchSavedOpportunities,
  updateCandidatureStatus,
  unsaveOpportunity,
  applyCandidature,
} = useCandidature()
const { showToast } = useToast()

const translations = {
  en: {
    eyebrow: 'Student / Candidature',
    title: 'Your Application Journey',
    subtitle: 'Track your applications and monitor their progress through each stage.',
    totalApplications: 'Applications',
    appliedStatus: 'Applied',
    interviewStatus: 'Interview',
    offerStatus: 'Offer',
    acceptedStatus: 'Accepted',
    rejectedStatus: 'Rejected',
    allStatuses: 'All statuses',
    searchPlaceholder: 'Search by opportunity or company',
    reset: 'Reset',
    yourApplications: 'Your Applications',
    savedOpportunities: 'Saved Opportunities',
    items: 'items',
    loading: 'Loading your candidature...',
    applied: 'Applied',
    feedback: 'Feedback',
    notes: 'Notes',
    pending: 'Pending',
    acceptOffer: 'Accept Offer',
    viewOpportunity: 'View Opportunity',
    apply: 'Apply Now',
    remove: 'Remove',
    noApplications: 'No applications yet. Start exploring opportunities!',
    noSaved: 'No saved opportunities yet.',
  },
  fr: {
    eyebrow: 'Etudiant / Candidature',
    title: 'Votre Parcours de Candidature',
    subtitle: 'Suivez vos candidatures et surveillez leur progression à chaque étape.',
    totalApplications: 'Candidatures',
    appliedStatus: 'Postulé',
    interviewStatus: 'Entretien',
    offerStatus: 'Offre',
    acceptedStatus: 'Accepté',
    rejectedStatus: 'Rejeté',
    allStatuses: 'Tous les statuts',
    searchPlaceholder: 'Chercher par opportunité ou entreprise',
    reset: 'Réinitialiser',
    yourApplications: 'Vos Candidatures',
    savedOpportunities: 'Opportunités Sauvegardées',
    items: 'éléments',
    loading: 'Chargement de vos candidatures...',
    applied: 'Postulé',
    feedback: 'Retour',
    notes: 'Notes',
    pending: 'En attente',
    acceptOffer: "Accepter l'Offre",
    viewOpportunity: "Voir l'Opportunité",
    apply: 'Postuler Maintenant',
    remove: 'Supprimer',
    noApplications: 'Aucune candidature pour le moment. Commencez à explorer les opportunités!',
    noSaved: 'Aucune opportunité sauvegardée pour le moment.',
  },
}

const t = computed(() => translations[locale.value] || translations.en)
const statusFilter = ref('all')
const searchTerm = ref('')
const studentProfile = ref(null)

const formatDate = (date) => {
  if (!date) return '—'
  const d = new Date(date)
  if (Number.isNaN(d.getTime())) return String(date)
  return d.toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })
}

const getStatusLabel = (status) => {
  const statusMap = {
    en: {
      applied: 'Applied',
      interview: 'Interview Scheduled',
      offer: 'Offer Received',
      accepted: 'Accepted',
      rejected: 'Rejected',
    },
    fr: {
      applied: 'Postulé',
      interview: 'Entretien Prévu',
      offer: 'Offre Reçue',
      accepted: 'Accepté',
      rejected: 'Rejeté',
    },
  }

  const lang = locale.value === 'fr' ? 'fr' : 'en'
  return statusMap[lang][status] || status
}

const filteredCandidatures = computed(() => {
  const query = searchTerm.value.trim().toLowerCase()

  return candidatures.value.filter((item) => {
    const statusMatch = statusFilter.value === 'all' || item.status === statusFilter.value
    const queryMatch =
      !query ||
      item.opportunity.title.toLowerCase().includes(query) ||
      item.opportunity.company.toLowerCase().includes(query)

    return statusMatch && queryMatch
  })
})

const appliedCount = computed(
  () => candidatures.value.filter((item) => item.status === 'applied').length,
)
const interviewCount = computed(
  () => candidatures.value.filter((item) => item.status === 'interview').length,
)
const offerCount = computed(
  () => candidatures.value.filter((item) => item.status === 'offer').length,
)

const resetFilters = () => {
  statusFilter.value = 'all'
  searchTerm.value = ''
}

const acceptOffer = async (candidature) => {
  try {
    await updateCandidatureStatus(candidature.id, 'accepted')
    showToast('Offer accepted successfully', 'success')
  } catch (error) {
    showToast(error?.message || 'Unable to accept offer', 'error')
  }
}

const viewOpportunity = (opportunity) => {
  // Navigate to opportunity details or open in a modal
  window.location.href = `/opportunity/${opportunity.id}`
}

const applyFromSaved = async (saved) => {
  try {
    await applyCandidature(saved.opportunityId, studentProfile.value.id)
    showToast('Application submitted successfully', 'success')
  } catch (error) {
    showToast(error?.message || 'Unable to submit application', 'error')
  }
}

const removeSaved = async (saved) => {
  try {
    await unsaveOpportunity(saved.opportunityId, studentProfile.value.id)
    showToast('Opportunity removed from saved', 'success')
  } catch (error) {
    showToast(error?.message || 'Unable to remove opportunity', 'error')
  }
}

const loadStudentProfile = async () => {
  try {
    await studentStore.fetchStudents()

    const currentUserId = authStore.user?.id || authStore.user?.userId
    const currentUserEmail = String(authStore.user?.email || '')
      .trim()
      .toLowerCase()

    studentProfile.value =
      studentStore.students.find((student) => {
        const sameUser = currentUserId && Number(student.userId) === Number(currentUserId)
        const sameEmail =
          currentUserEmail &&
          String(student.email || student.user?.email || '')
            .trim()
            .toLowerCase() === currentUserEmail

        return sameUser || sameEmail
      }) || null
  } catch (error) {
    console.warn('Unable to load student profile.', error)
  }
}

onMounted(async () => {
  if (!authStore.bootstrapped && authStore.token) {
    await authStore.bootstrapAuth().catch(() => {})
  }

  await loadStudentProfile()

  if (studentProfile.value?.id) {
    await Promise.allSettled([
      fetchCandidatures(studentProfile.value.id),
      fetchSavedOpportunities(studentProfile.value.id),
    ])
  }
})
</script>

<style scoped>
.candidature-page {
  display: grid;
  gap: 14px;
  padding: 14px;
  background: var(--bg);
  min-height: 100%;
}

.hero-card,
.toolbar-card,
.list-card,
.mini-stat,
.candidature-card,
.saved-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 18px;
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
}

.hero-card {
  display: grid;
  grid-template-columns: 1.6fr 1fr;
  gap: 10px;
  padding: 14px;
  position: relative;
  overflow: hidden;
}

.hero-card::before {
  content: '';
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 88% 12%, rgba(6, 170, 197, 0.16), transparent 44%),
    linear-gradient(130deg, rgba(15, 118, 110, 0.1), rgba(14, 165, 233, 0.05));
  pointer-events: none;
}

.hero-card > * {
  position: relative;
  z-index: 1;
}

.eyebrow {
  margin: 0;
  color: var(--primary);
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

.hero-card h1 {
  margin: 6px 0 10px;
  color: var(--text);
  font-size: clamp(1.26rem, 2.2vw, 1.75rem);
}

.hero-text {
  margin: 0;
  color: var(--muted);
}

.hero-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
  gap: 10px;
  align-self: start;
}

.mini-stat {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding: 12px;
  text-align: center;
}

.mini-stat strong {
  color: var(--heading);
  font-size: 1.5rem;
}

.toolbar-card {
  display: grid;
  gap: 10px;
  padding: 14px;
}

.filters-row {
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 10px;
  align-items: center;
}

.search-input {
  position: relative;
  display: flex;
  align-items: center;
}

.search-input i {
  position: absolute;
  left: 10px;
  color: var(--muted);
}

.search-input input {
  padding: 10px 10px 10px 32px;
  border: 1px solid var(--border);
  border-radius: 8px;
  background: var(--surface);
  color: var(--text);
  font-size: 0.875rem;
  width: 100%;
}

.search-input input::placeholder {
  color: var(--muted);
}

select {
  padding: 10px;
  border: 1px solid var(--border);
  border-radius: 8px;
  background: var(--surface);
  color: var(--text);
  font-size: 0.875rem;
}

.ghost-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 10px 14px;
  border: 1px solid var(--border);
  border-radius: 8px;
  background: transparent;
  color: var(--primary);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.ghost-btn:hover {
  background: var(--primary);
  color: var(--surface);
}

.content-grid {
  display: grid;
  gap: 14px;
}

.list-card {
  padding: 14px;
}

.section-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 14px;
}

.section-head h2 {
  margin: 0;
  color: var(--heading);
}

.section-head span {
  color: var(--muted);
  font-size: 0.875rem;
}

.timeline-container {
  display: grid;
  gap: 14px;
}

.candidature-card {
  padding: 14px;
  border: 1px solid var(--border);
  border-radius: 12px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 14px;
  gap: 10px;
}

.opportunity-info h3 {
  margin: 0 0 6px;
  color: var(--text);
}

.company-line {
  margin: 0;
  color: var(--muted);
  font-size: 0.875rem;
}

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  white-space: nowrap;
}

.status-applied {
  background: rgba(59, 130, 246, 0.1);
  color: #3b82f6;
}

.status-interview {
  background: rgba(168, 85, 247, 0.1);
  color: #a855f7;
}

.status-offer {
  background: rgba(34, 197, 94, 0.1);
  color: #22c55e;
}

.status-accepted {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
}

.status-rejected {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}

.card-timeline {
  display: grid;
  gap: 12px;
  padding: 14px 0;
  border-top: 1px solid var(--border);
  border-bottom: 1px solid var(--border);
  margin-bottom: 14px;
}

.timeline-item {
  display: grid;
  grid-template-columns: 24px 1fr;
  gap: 12px;
  align-items: flex-start;
  opacity: 0.5;
  transition: opacity 0.2s ease;
}

.timeline-item.completed {
  opacity: 1;
}

.timeline-marker {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  border: 2px solid var(--border);
  background: var(--surface);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  margin-top: 2px;
}

.timeline-marker.applied {
  background: #3b82f6;
  border-color: #3b82f6;
}

.timeline-marker.interview {
  background: #a855f7;
  border-color: #a855f7;
}

.timeline-marker.offer {
  background: #22c55e;
  border-color: #22c55e;
}

.timeline-marker.accepted {
  background: #10b981;
  border-color: #10b981;
}

.timeline-content strong {
  display: block;
  color: var(--text);
  font-size: 0.875rem;
}

.timeline-content p {
  margin: 4px 0 0;
  color: var(--muted);
  font-size: 0.8rem;
}

.card-notes {
  display: grid;
  gap: 8px;
  padding: 12px;
  background: rgba(15, 23, 42, 0.02);
  border-radius: 8px;
  margin-bottom: 14px;
}

.note-section strong {
  display: block;
  color: var(--text);
  font-size: 0.875rem;
  margin-bottom: 4px;
}

.note-section p {
  margin: 0;
  color: var(--muted);
  font-size: 0.8rem;
  line-height: 1.4;
}

.card-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.primary-btn,
.secondary-btn {
  flex: 1;
  min-width: 120px;
  padding: 10px 14px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 0.875rem;
}

.primary-btn {
  background: var(--primary);
  color: white;
}

.primary-btn:hover {
  background: var(--primary-dark, #06a6c5);
}

.secondary-btn {
  background: var(--border);
  color: var(--text);
}

.secondary-btn:hover {
  background: var(--primary);
  color: white;
}

.cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 14px;
}

.saved-card {
  padding: 14px;
  border: 1px solid var(--border);
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.card-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 10px;
}

.card-top h4 {
  margin: 0;
  color: var(--text);
  font-size: 1rem;
}

.card-top small {
  color: var(--muted);
  font-size: 0.75rem;
  white-space: nowrap;
}

.saved-notes {
  margin: 0;
  color: var(--muted);
  font-size: 0.8rem;
  line-height: 1.4;
}

.empty-state {
  padding: 20px;
  text-align: center;
  color: var(--muted);
  font-size: 0.875rem;
}

.info-state {
  padding: 14px;
  text-align: center;
  color: #3b82f6;
  font-weight: 600;
}

.error-state {
  padding: 14px;
  text-align: center;
  color: #ef4444;
  font-weight: 600;
}

.muted {
  color: var(--muted);
}

@media (max-width: 768px) {
  .hero-card {
    grid-template-columns: 1fr;
  }

  .filters-row {
    grid-template-columns: 1fr;
  }

  .cards-grid {
    grid-template-columns: 1fr;
  }

  .hero-stats {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>
