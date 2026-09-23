<template>
  <div class="candidates-page">
    <section class="page-top">
      <div>
        <p class="eyebrow">Company / Candidates</p>
        <h1>Candidate Matching</h1>
        <p class="intro">
          Review students that match your open roles, based on skills, academics and profile fit.
        </p>
      </div>
    </section>

    <section class="toolbar">
      <div class="search-group">
        <input
          type="search"
          v-model="query"
          placeholder="Search by name, program, skill or language"
          aria-label="Search candidates"
        />
        <button class="secondary-btn" type="button" @click="sortBy = 'match'">Best match</button>
      </div>

      <div class="filters">
        <select v-model="programFilter">
          <option value="all">All programs</option>
          <option v-for="program in programOptions" :key="program" :value="program">
            {{ program }}
          </option>
        </select>
        <select v-model="statusFilter">
          <option value="all">All statuses</option>
          <option value="applied">Applied</option>
          <option value="interview">Interview Scheduled</option>
          <option value="offer">Offer Extended</option>
          <option value="accepted">Accepted</option>
          <option value="rejected">Rejected</option>
        </select>
      </div>
    </section>

    <!-- Loading State -->
    <section v-if="isLoading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading candidates...</p>
    </section>

    <!-- Error State -->
    <section v-else-if="error" class="error-state">
      <i class="bi bi-exclamation-circle"></i>
      <p>{{ error }}</p>
      <button class="secondary-btn" @click="loadCandidates">Try again</button>
    </section>

    <!-- Empty State -->
    <section v-else-if="filteredCandidates.length === 0" class="empty-state">
      <i class="bi bi-inbox"></i>
      <p>No candidates found</p>
      <small>Try adjusting your filters or search terms</small>
    </section>

    <!-- Candidates Grid -->
    <section v-else class="candidate-grid">
      <article
        v-for="candidate in filteredCandidates"
        :key="`${candidate.studentId}-${candidate.opportunityId}`"
        class="candidate-card"
      >
        <div class="candidate-header">
          <div>
            <strong>{{ candidate.fullName }}</strong>
            <small>{{ candidate.program }} · {{ candidate.level }}</small>
          </div>
          <span class="match-score">{{ candidate.matchScore }}%</span>
        </div>

        <p class="candidate-summary">
          {{ candidate.notes || candidate.summary || 'No additional info' }}
        </p>

        <div class="candidate-tags">
          <span v-for="tag in (candidate.matchTags || []).slice(0, 3)" :key="tag">{{ tag }}</span>
        </div>

        <div class="candidate-metrics">
          <div>
            <small>GPA</small>
            <strong>{{ candidate.gpa || '—' }}</strong>
          </div>
          <div>
            <small>Program</small>
            <strong>{{ candidate.program || '—' }}</strong>
          </div>
          <div>
            <small>Status</small>
            <strong class="status-badge">{{ formatApplicationStatus(candidate.status) }}</strong>
          </div>
        </div>

        <div class="candidate-actions">
          <button class="btn-primary" @click="viewProfile(candidate)">View profile</button>
          <button
            v-if="candidate.status === 'applied'"
            class="btn-outline-secondary"
            type="button"
            @click="scheduleInterview(candidate)"
          >
            Schedule Interview
          </button>
        </div>
      </article>
    </section>

    <div
      v-if="isInterviewModalOpen"
      class="interview-modal-backdrop"
      @click="closeInterviewModal"
    ></div>
    <section v-if="isInterviewModalOpen" class="interview-modal" role="dialog" aria-modal="true">
      <header class="interview-modal-header">
        <div>
          <h3>Schedule Interview</h3>
          <p>{{ selectedCandidate?.fullName || 'Candidate' }}</p>
        </div>
        <button class="icon-close" type="button" @click="closeInterviewModal">x</button>
      </header>

      <form class="interview-form" @submit.prevent="submitInterviewSchedule">
        <div class="interview-grid">
          <label>
            <span>Interview Date</span>
            <input v-model="interviewForm.date" type="date" required />
          </label>

          <label>
            <span>Interview Time</span>
            <input v-model="interviewForm.time" type="time" required />
          </label>
        </div>

        <label>
          <span>Optional Notes</span>
          <textarea
            v-model.trim="interviewForm.note"
            rows="4"
            placeholder="Interview link, meeting room, or instructions"
          ></textarea>
        </label>

        <div class="interview-actions">
          <button class="btn-outline-secondary" type="button" @click="closeInterviewModal">
            Cancel
          </button>
          <button class="btn-primary" type="submit" :disabled="isSchedulingInterview">
            {{ isSchedulingInterview ? 'Saving...' : 'Confirm Interview' }}
          </button>
        </div>
      </form>
    </section>
  </div>
</template>

<script setup>
import { computed, reactive, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCompanyOpportunities } from '@/compasables/useCompanyOpportunities'
import { useToast } from '@/compasables/useToast'

const router = useRouter()
const {
  companyOpportunities,
  fetchOpportunities,
  getOpportunityCandidates,
  updateCandidatureStatus,
} = useCompanyOpportunities()
const { showToast } = useToast()

const query = ref('')
const programFilter = ref('all')
const statusFilter = ref('all')
const sortBy = ref('match')

const isLoading = ref(false)
const error = ref(null)
const candidates = ref([])
const isInterviewModalOpen = ref(false)
const isSchedulingInterview = ref(false)
const selectedCandidate = ref(null)
const interviewForm = reactive({
  date: '',
  time: '',
  note: '',
})

const parseId = (value) => {
  if (!value && value !== 0) return null
  if (typeof value === 'number') return Number.isFinite(value) ? value : null

  if (typeof value === 'string') {
    const trimmed = value.trim()
    if (!trimmed) return null

    const match = trimmed.match(/(\d+)$/)
    if (match) {
      const parsed = Number(match[1])
      return Number.isFinite(parsed) ? parsed : null
    }

    const parsed = Number(trimmed)
    return Number.isFinite(parsed) ? parsed : null
  }

  if (typeof value === 'object') {
    return parseId(value.id || value['@id'] || value.studentId || value.userId)
  }

  return null
}

const programOptions = computed(() => {
  const programs = new Set()
  candidates.value.forEach((candidate) => {
    if (candidate.program) programs.add(candidate.program)
  })
  return [...programs].sort()
})

const filteredCandidates = computed(() => {
  const term = query.value.toLowerCase().trim()
  return candidates.value
    .filter((candidate) => {
      if (programFilter.value !== 'all' && candidate.program !== programFilter.value) {
        return false
      }
      if (statusFilter.value !== 'all' && candidate.status !== statusFilter.value) {
        return false
      }
      if (!term) return true

      return [
        candidate.fullName,
        candidate.program,
        (candidate.student?.skills || []).join(' '),
        candidate.notes,
        candidate.summary,
      ]
        .join(' ')
        .toLowerCase()
        .includes(term)
    })
    .sort((a, b) => {
      if (sortBy.value === 'match') {
        return (b.matchScore || 0) - (a.matchScore || 0)
      }
      return b.matchScore - a.matchScore
    })
})

const formatApplicationStatus = (status) => {
  const map = {
    applied: 'Applied',
    interview: 'Interview Scheduled',
    offer: 'Offer Extended',
    accepted: 'Accepted',
    rejected: 'Rejected',
  }
  return map[status] || 'Unknown'
}

const loadCandidates = async () => {
  isLoading.value = true
  error.value = null
  try {
    // Fetch all opportunities first
    await fetchOpportunities()

    const companyOpportunityIds = new Set(
      companyOpportunities.value.map((item) => parseId(item.id)).filter(Boolean),
    )

    // Fetch candidatures once, then filter to company opportunities.
    const opportCandidates = await getOpportunityCandidates()

    const allCandidates = []

    // Map each candidature to include student data
    opportCandidates.forEach((candidature) => {
      const candidateOpportunityId = parseId(
        candidature.opportunityId || candidature.opportunity?.id || candidature.opportunity,
      )

      if (!candidateOpportunityId || !companyOpportunityIds.has(candidateOpportunityId)) {
        return
      }

      const normalizedStudentId = parseId(
        candidature.studentId || candidature.student?.id || candidature.student,
      )

      allCandidates.push({
        // Candidature data
        id: candidature.id,
        candidatureId: candidature.id,
        studentId: normalizedStudentId,
        opportunityId: candidateOpportunityId,
        status: candidature.status,
        matchScore: candidature.matchScore || 0,
        notes: candidature.notes,
        createdAt: candidature.createdAt,

        // Student data
        fullName: candidature.student?.fullName || 'Unknown',
        program: candidature.student?.program || 'Unknown',
        level: candidature.student?.level || 'Unknown',
        gpa: candidature.student?.gpa || 0,
        email: candidature.student?.email,
        phone: candidature.student?.phone,
        summary: candidature.student?.summary,
        skills: candidature.student?.skills || [],
        matchTags: [],
      })
    })

    candidates.value = allCandidates
    console.log(
      `✅ Loaded ${allCandidates.length} candidates from ${companyOpportunities.value.length} opportunities`,
    )
  } catch (err) {
    error.value = 'Failed to load candidates. Please try again.'
    console.error('Error loading candidates:', err)
  } finally {
    isLoading.value = false
  }
}

const viewProfile = (candidate) => {
  const profileRouteId = parseId(
    candidate?.studentId || candidate?.student?.id || candidate?.id || candidate?.candidatureId,
  )
  if (!profileRouteId) return

  router.push({
    name: 'companyCandidateProfile',
    params: { id: profileRouteId },
    state: { candidatePayload: candidate },
  })
}

const scheduleInterview = (candidate) => {
  selectedCandidate.value = candidate
  interviewForm.date = ''
  interviewForm.time = ''
  interviewForm.note = ''
  isInterviewModalOpen.value = true
}

const closeInterviewModal = () => {
  isInterviewModalOpen.value = false
  selectedCandidate.value = null
}

const submitInterviewSchedule = async () => {
  const candidate = selectedCandidate.value
  if (!candidate?.candidatureId) {
    showToast('Missing candidature reference for interview scheduling.', 'error')
    return
  }

  const interviewDateTime = new Date(`${interviewForm.date}T${interviewForm.time}`)
  if (Number.isNaN(interviewDateTime.getTime())) {
    showToast('Please select a valid date and time.', 'warning')
    return
  }

  isSchedulingInterview.value = true
  try {
    await updateCandidatureStatus(candidate.candidatureId, 'interview', {
      interviewDate: interviewDateTime.toISOString(),
      notes: interviewForm.note || undefined,
    })

    const target = candidates.value.find((item) => item.candidatureId === candidate.candidatureId)
    if (target) {
      target.status = 'interview'
      target.interviewDate = interviewDateTime.toISOString()
      if (interviewForm.note) target.notes = interviewForm.note
    }

    showToast('Interview scheduled successfully.', 'success')
    closeInterviewModal()
  } catch (err) {
    showToast(err?.message || 'Failed to schedule interview.', 'error')
  } finally {
    isSchedulingInterview.value = false
  }
}

onMounted(() => {
  loadCandidates()
})
</script>

<style scoped>
.candidates-page {
  display: grid;
  gap: 24px;
  width: 200%;
}

.page-top {
  display: grid;
  gap: 14px;
}

.eyebrow {
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 0.18em;
  color: var(--primary);
  margin: 0;
}

.page-top h1 {
  margin: 0;
  font-size: clamp(2rem, 2.5vw, 2.6rem);
}

.intro {
  margin: 0;
  color: var(--muted);
  max-width: 720px;
  line-height: 1.7;
}

.toolbar {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  justify-content: space-between;
  align-items: center;
}

.search-group {
  display: flex;
  gap: 12px;
  flex: 1 1 420px;
}

.search-group input {
  flex: 1;
  min-width: 0;
  padding: 14px 18px;
  border: 1px solid var(--border);
  border-radius: 18px;
  background: var(--surface);
  color: var(--text);
}

.filters {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.filters select,
.secondary-btn,
.btn-outline-secondary {
  padding: 12px 16px;
  border-radius: 14px;
  border: 1px solid var(--border);
  background: var(--surface);
  color: var(--text);
}

.filters select {
  min-width: 150px;
}

.secondary-btn {
  cursor: pointer;
}

/* Loading State */
.loading-state,
.error-state,
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 16px;
  padding: 60px 24px;
  text-align: center;
  background: var(--surface);
  border-radius: 24px;
  border: 1px solid var(--border);
  min-height: 300px;
}

.loading-state i,
.error-state i,
.empty-state i {
  font-size: 48px;
  color: var(--primary);
  opacity: 0.6;
}

.loading-state p,
.error-state p,
.empty-state p {
  margin: 0;
  font-size: 18px;
  font-weight: 500;
  color: var(--text);
}

.empty-state small {
  color: var(--muted);
  font-size: 14px;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid var(--border);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.candidate-grid {
  display: grid;
  gap: 18px;
}

.candidate-card {
  padding: 24px;
  border-radius: 24px;
  background: var(--surface);
  border: 1px solid var(--border);
  box-shadow: 0 16px 40px rgba(15, 23, 42, 0.06);
  display: grid;
  gap: 18px;
}

.candidate-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
}

.candidate-header strong {
  font-size: 1.1rem;
}

.match-score {
  font-weight: 700;
  color: var(--primary);
  background: rgba(6, 170, 197, 0.12);
  padding: 10px 14px;
  border-radius: 999px;
}

.candidate-summary {
  margin: 0;
  color: var(--muted);
  line-height: 1.75;
}

.candidate-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.candidate-tags span {
  background: rgba(15, 23, 42, 0.05);
  color: var(--text);
  padding: 8px 12px;
  border-radius: 999px;
  font-size: 0.9rem;
}

.candidate-metrics {
  display: grid;
  grid-template-columns: repeat(3, minmax(120px, 1fr));
  gap: 16px;
}

.candidate-metrics div {
  display: grid;
  gap: 6px;
}

.candidate-metrics small {
  color: var(--muted);
}

.status-badge {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 6px;
  background: rgba(6, 170, 197, 0.12);
  color: var(--primary);
  font-size: 0.85rem;
  font-weight: 600;
}

.candidate-actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.btn-primary,
.secondary-btn {
  border: none;
  border-radius: 14px;
  padding: 12px 18px;
  cursor: pointer;
}

.btn-primary {
  background: var(--primary);
  color: white;
}

.btn-outline-secondary {
  background: transparent;
  border-color: var(--border);
}

.interview-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(2, 6, 23, 0.48);
  z-index: 50;
}

.interview-modal {
  position: fixed;
  z-index: 51;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: min(520px, calc(100vw - 24px));
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 18px;
  box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
  padding: 16px;
  display: grid;
  gap: 14px;
}

.interview-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  gap: 12px;
}

.interview-modal-header h3 {
  margin: 0;
  color: var(--text);
}

.interview-modal-header p {
  margin: 6px 0 0;
  color: var(--muted);
}

.icon-close {
  border: 1px solid var(--border);
  background: var(--surface);
  color: var(--text);
  width: 30px;
  height: 30px;
  border-radius: 8px;
  cursor: pointer;
}

.interview-form {
  display: grid;
  gap: 12px;
}

.interview-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

.interview-form label {
  display: grid;
  gap: 6px;
}

.interview-form label span {
  color: var(--muted);
  font-size: 0.85rem;
}

.interview-form input,
.interview-form textarea {
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 10px 12px;
  background: var(--surface);
  color: var(--text);
}

.interview-form textarea {
  resize: vertical;
}

.interview-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

@media (max-width: 980px) {
  .toolbar,
  .candidate-metrics {
    grid-template-columns: 1fr;
  }

  .interview-grid {
    grid-template-columns: 1fr;
  }
}
</style>
