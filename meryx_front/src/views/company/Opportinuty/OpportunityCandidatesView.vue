<template>
  <div class="opportunity-candidates-page" :class="{ dark: isDarkMode }">
    <!-- Theme & Language Toggle -->
    <div class="theme-language-toggle">
      <button
        class="toggle-btn"
        @click="toggleDarkMode"
        :title="isDarkMode ? 'Light mode' : 'Dark mode'"
      >
        <i :class="isDarkMode ? 'bi bi-sun-fill' : 'bi bi-moon-fill'"></i>
      </button>
      <button
        class="toggle-btn"
        @click="toggleLanguage"
        :title="`Switch to ${language === 'en' ? 'Français' : 'English'}`"
      >
        {{ language.toUpperCase() }}
      </button>
    </div>

    <!-- Header Section -->
    <section class="page-header">
      <div>
        <p class="eyebrow">Company / Opportunities / Candidates</p>
        <h1>{{ opportunity?.title || 'Opportunity' }}</h1>
        <p class="intro">Review and manage all candidates who applied for this opportunity.</p>
      </div>
      <button class="ghost-btn" type="button" @click="goBack">
        <i class="bi bi-arrow-left"></i>
        Back to Opportunities
      </button>
    </section>

    <!-- Opportunity Quick View -->
    <section v-if="opportunity" class="quick-view-card">
      <div class="info-row">
        <div>
          <strong>{{ opportunity.title }}</strong>
          <span class="badge" :class="statusClass(opportunity.status)">{{
            opportunity.status
          }}</span>
        </div>
        <div class="meta">
          <span><i class="bi bi-geo-alt"></i> {{ opportunity.location }}</span>
          <span><i class="bi bi-briefcase"></i> {{ opportunity.type }}</span>
          <span><i class="bi bi-people"></i> {{ totalApplications }} Applications</span>
        </div>
      </div>
    </section>

    <!-- Filters & Search Section -->
    <section class="toolbar-card">
      <div class="search-box">
        <i class="bi bi-search"></i>
        <input
          v-model="searchQuery"
          type="search"
          placeholder="Search by name, program, skills..."
        />
      </div>

      <div class="toolbar-filters">
        <label>
          <span>Time Period</span>
          <select v-model="timePeriodFilter">
            <option value="all">All time</option>
            <option value="24h">Last 24 hours</option>
            <option value="7d">Last 7 days</option>
            <option value="30d">Last 30 days</option>
            <option value="3m">Last 3 months</option>
          </select>
        </label>

        <label>
          <span>Status</span>
          <select v-model="applicationStatusFilter">
            <option value="all">All statuses</option>
            <option value="applied">Applied</option>
            <option value="interview">Interview Scheduled</option>
            <option value="offer">Offer Extended</option>
            <option value="accepted">Accepted</option>
            <option value="rejected">Rejected</option>
          </select>
        </label>

        <label>
          <span>Profile Match</span>
          <select v-model="matchScoreFilter">
            <option value="all">All matches</option>
            <option value="high">High (80%+)</option>
            <option value="medium">Medium (50-79%)</option>
            <option value="low">Low (Below 50%)</option>
          </select>
        </label>

        <label>
          <span>Sort By</span>
          <select v-model="sortBy">
            <option value="recent">Most Recent</option>
            <option value="match">Best Match</option>
            <option value="oldest">Oldest</option>
          </select>
        </label>
      </div>
    </section>

    <!-- Candidates List Section -->
    <section v-if="isLoading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading candidates...</p>
    </section>

    <section v-else-if="filteredCandidates.length > 0" class="candidates-list-section">
      <div class="candidates-count">
        <h3>
          {{ filteredCandidates.length }} Candidate{{ filteredCandidates.length !== 1 ? 's' : '' }}
        </h3>
      </div>

      <article
        v-for="application in filteredCandidates"
        :key="application.id"
        class="candidate-application-card"
      >
        <div class="card-header">
          <div class="candidate-info">
            <div class="candidate-name">
              <strong>{{ application.student?.fullName || 'Unknown' }}</strong>
              <small>{{ application.student?.program || 'Program not specified' }}</small>
            </div>
            <div class="match-indicator">
              <span class="match-score" :class="matchScoreClass(application.matchScore)">
                {{ application.matchScore || 0 }}% Match
              </span>
            </div>
          </div>

          <div class="status-badge">
            <span class="badge" :class="statusClass(application.status)">
              {{ formatApplicationStatus(application.status) }}
            </span>
          </div>
        </div>

        <div class="card-body">
          <!-- Application Message Preview -->
          <div v-if="application.notes" class="application-message">
            <strong>Application Message:</strong>
            <p>{{ truncateText(application.notes, 150) }}</p>
          </div>

          <!-- Skills & Fit -->
          <div class="fit-details">
            <div class="fit-item">
              <span class="label">Applied:</span>
              <span class="value">{{ formatDate(application.createdAt) }}</span>
            </div>
            <div class="fit-item">
              <span class="label">GPA:</span>
              <span class="value">{{ application.student?.gpa || '—' }}</span>
            </div>
            <div class="fit-item">
              <span class="label">Skills:</span>
              <span class="value">
                <span
                  v-for="skill in (application.student?.skills || []).slice(0, 3)"
                  :key="skill"
                  class="skill-tag"
                >
                  {{ skill }}
                </span>
                <span v-if="(application.student?.skills || []).length > 3" class="more-tag">
                  +{{ (application.student?.skills || []).length - 3 }}
                </span>
              </span>
            </div>
          </div>
        </div>

        <div class="card-footer">
          <button class="secondary-btn" type="button" @click="viewProfile(application)">
            <i class="bi bi-eye"></i>
            View Full Profile
          </button>

          <div class="action-buttons">
            <button
              v-if="application.status === 'applied'"
              class="primary-btn"
              type="button"
              @click="updateApplicationStatus(application, 'interview')"
            >
              <i class="bi bi-calendar-check"></i>
              Schedule Interview
            </button>

            <button
              v-if="['applied', 'interview'].includes(application.status)"
              class="success-btn"
              type="button"
              @click="updateApplicationStatus(application, 'offer')"
            >
              <i class="bi bi-star"></i>
              Send Offer
            </button>

            <button
              v-if="application.status !== 'rejected'"
              class="danger-btn"
              type="button"
              @click="updateApplicationStatus(application, 'rejected')"
            >
              <i class="bi bi-x-lg"></i>
              Reject
            </button>
          </div>
        </div>
      </article>
    </section>

    <section v-else class="empty-state">
      <i class="bi bi-inbox"></i>
      <p>No candidates match your filters.</p>
      <small>Candidates will appear here as they apply to this opportunity.</small>
    </section>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useCompanyOpportunities } from '@/compasables/useCompanyOpportunities'
import { useDarkMode } from '@/compasables/useDarkMode'
import { useI18n } from '@/utils/translations'

const router = useRouter()
const route = useRoute()
const { isDarkMode, toggleDarkMode } = useDarkMode()
const { language, toggleLanguage } = useI18n()

const {
  companyOpportunities,
  fetchOpportunities,
  getOpportunityCandidates,
  updateCandidatureStatus,
} = useCompanyOpportunities()

const isLoading = ref(false)
const searchQuery = ref('')
const timePeriodFilter = ref('all')
const applicationStatusFilter = ref('all')
const matchScoreFilter = ref('all')
const sortBy = ref('recent')
const candidates = ref([])

const opportunityId = computed(() => Number(route.params.id))

const opportunity = computed(() =>
  companyOpportunities.value.find((o) => o.id === opportunityId.value),
)

const totalApplications = computed(() => candidates.value.length)

const filteredCandidates = computed(() => {
  let filtered = candidates.value

  // Search filter
  if (searchQuery.value.trim()) {
    const term = searchQuery.value.toLowerCase()
    filtered = filtered.filter((app) => {
      const text = [
        app.student?.fullName,
        app.student?.program,
        (app.student?.skills || []).join(' '),
        app.notes,
      ]
        .join(' ')
        .toLowerCase()
      return text.includes(term)
    })
  }

  // Time period filter
  if (timePeriodFilter.value !== 'all') {
    const now = new Date()
    const cutoffDate = new Date()

    if (timePeriodFilter.value === '24h') {
      cutoffDate.setHours(now.getHours() - 24)
    } else if (timePeriodFilter.value === '7d') {
      cutoffDate.setDate(now.getDate() - 7)
    } else if (timePeriodFilter.value === '30d') {
      cutoffDate.setDate(now.getDate() - 30)
    } else if (timePeriodFilter.value === '3m') {
      cutoffDate.setMonth(now.getMonth() - 3)
    }

    filtered = filtered.filter((app) => new Date(app.appliedDate || app.createdAt) >= cutoffDate)
  }

  // Application status filter
  if (applicationStatusFilter.value !== 'all') {
    filtered = filtered.filter((app) => app.status === applicationStatusFilter.value)
  }

  // Match score filter
  if (matchScoreFilter.value !== 'all') {
    filtered = filtered.filter((app) => {
      const score = app.matchScore || 0
      if (matchScoreFilter.value === 'high') return score >= 80
      if (matchScoreFilter.value === 'medium') return score >= 50 && score < 80
      if (matchScoreFilter.value === 'low') return score < 50
      return true
    })
  }

  // Sort
  const sorted = [...filtered]
  if (sortBy.value === 'recent') {
    sorted.sort(
      (a, b) => new Date(b.appliedDate || b.createdAt) - new Date(a.appliedDate || a.createdAt),
    )
  } else if (sortBy.value === 'match') {
    sorted.sort((a, b) => (b.matchScore || 0) - (a.matchScore || 0))
  } else if (sortBy.value === 'oldest') {
    sorted.sort(
      (a, b) => new Date(a.appliedDate || a.createdAt) - new Date(b.appliedDate || b.createdAt),
    )
  }

  return sorted
})

onMounted(async () => {
  isLoading.value = true
  try {
    // First fetch the list of opportunities to populate the opportunity computed property
    await fetchOpportunities()

    console.log('Opportunities loaded:', companyOpportunities.value)
    console.log('Current opportunity ID from route:', opportunityId.value)
    console.log('Current opportunity:', opportunity.value)

    if (!opportunity.value) {
      console.warn('Opportunity not found for ID:', opportunityId.value)
    }

    // Fetch all candidatures
    const allCandidates = await getOpportunityCandidates()
    console.log('All candidates fetched:', allCandidates)
    console.log('Total candidates:', allCandidates.length)

    if (allCandidates.length === 0) {
      console.warn('No candidates returned from API')
    }

    // Filter by current opportunity ID from the route
    candidates.value = allCandidates.filter((app) => {
      // Extract opportunity ID - handle both "/api/opportunities/3" and direct number
      let appOppId = null

      if (app.opportunityId) {
        const oppIdStr = String(app.opportunityId)
        if (oppIdStr.includes('/')) {
          appOppId = Number(oppIdStr.split('/').pop())
        } else {
          appOppId = Number(oppIdStr)
        }
      } else if (app.opportunity?.id) {
        appOppId = Number(app.opportunity.id)
      }

      const matches = appOppId === opportunityId.value

      if (appOppId !== null && appOppId !== undefined) {
        console.log(
          `Candidate ${app.id}: oppId=${appOppId}, looking for=${opportunityId.value}, matches=${matches}`,
        )
      }

      return matches
    })

    console.log('Filtered candidates for opportunity:', candidates.value)
  } catch (error) {
    console.error('Failed to load candidates:', error)
    console.error('Error stack:', error.stack)
  } finally {
    isLoading.value = false
  }
})

const formatDate = (date) => {
  if (!date) return '—'
  return new Date(date).toLocaleDateString(language.value === 'en' ? 'en-GB' : 'fr-FR', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })
}

const truncateText = (text, length) => {
  if (!text) return '—'
  return text.length > length ? text.substring(0, length) + '...' : text
}

const statusClass = (status) => {
  if (status === 'rejected') return 'rejected'
  if (status === 'accepted') return 'accepted'
  if (status === 'offer') return 'offer'
  if (status === 'interview') return 'interview'
  return 'applied'
}

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

const matchScoreClass = (score) => {
  if (score >= 80) return 'high-match'
  if (score >= 50) return 'medium-match'
  return 'low-match'
}

const viewProfile = (application) => {
  if (application.student?.id) {
    router.push({
      name: 'companyCandidateProfile',
      params: { id: application.student.id },
      state: {
        candidatePayload: {
          studentId: application.student.id,
          fullName: application.student.fullName,
          program: application.student.program,
          level: application.student.level,
          gpa: application.student.gpa,
          email: application.student.email,
          phone: application.student.phone,
          summary: application.student.summary,
          skills: application.student.skills,
          matchScore: application.matchScore || 0,
          matchTags: [],
        },
      },
    })
  }
}

const updateApplicationStatus = async (application, newStatus) => {
  try {
    await updateCandidatureStatus(application.id, newStatus)
    // Update local state
    application.status = newStatus
  } catch (error) {
    console.error('Failed to update application status:', error)
  }
}

const goBack = () => {
  router.push({ name: 'companyOpportunities' })
}
</script>

<style scoped>
.opportunity-candidates-page {
  display: grid;
  gap: 24px;
  padding: 32px;
  background: #f8f9fa;
  min-height: 100vh;
  color: #333;
  transition:
    background-color 0.3s,
    color 0.3s;
}

.opportunity-candidates-page.dark {
  background: #1a1a1a;
  color: #e0e0e0;
}

.theme-language-toggle {
  position: fixed;
  top: 20px;
  right: 20px;
  display: flex;
  gap: 8px;
  z-index: 999;
}

.toggle-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: none;
  background: white;
  color: #333;
  cursor: pointer;
  font-weight: 600;
  font-size: 12px;
  transition: all 0.2s;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.opportunity-candidates-page.dark .toggle-btn {
  background: #333;
  color: #e0e0e0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.toggle-btn:hover {
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 24px;
}

.page-header > div {
  flex: 1;
}

.page-header p.eyebrow {
  color: #666;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin: 0 0 8px;
}

.opportunity-candidates-page.dark .page-header p.eyebrow {
  color: #aaa;
}

.page-header h1 {
  margin: 0 0 8px;
  font-size: 28px;
  font-weight: 600;
}

.page-header .intro {
  margin: 0;
  color: #666;
  font-size: 14px;
}

.opportunity-candidates-page.dark .page-header .intro {
  color: #bbb;
}

.ghost-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: transparent;
  border: 1px solid #ddd;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  color: #333;
  transition: all 0.2s;
}

.opportunity-candidates-page.dark .ghost-btn {
  border-color: #555;
  color: #e0e0e0;
}

.ghost-btn:hover {
  border-color: #999;
  background: #f5f5f5;
}

.opportunity-candidates-page.dark .ghost-btn:hover {
  border-color: #777;
  background: #333;
}

.quick-view-card {
  background: white;
  padding: 16px 20px;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

.opportunity-candidates-page.dark .quick-view-card {
  background: #2a2a2a;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

.toolbar-card {
  background: white;
  padding: 16px 20px;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

.opportunity-candidates-page.dark .toolbar-card {
  background: #2a2a2a;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

.search-box input {
  width: 100%;
  padding: 10px 12px 10px 36px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 14px;
  background: white;
  color: #333;
}

.opportunity-candidates-page.dark .search-box input {
  border-color: #555;
  background: #1a1a1a;
  color: #e0e0e0;
}

.toolbar-filters label {
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 12px;
  font-weight: 600;
  color: #666;
}

.opportunity-candidates-page.dark .toolbar-filters label {
  color: #aaa;
}

.toolbar-filters select {
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 13px;
  background: white;
  color: #333;
}

.opportunity-candidates-page.dark .toolbar-filters select {
  border-color: #555;
  background: #1a1a1a;
  color: #e0e0e0;
}

.loading-state {
  background: white;
}

.opportunity-candidates-page.dark .loading-state {
  background: #2a2a2a;
}

.spinner {
  border-color: #e0e0e0;
  border-top-color: #333;
}

.opportunity-candidates-page.dark .spinner {
  border-color: #444;
  border-top-color: #e0e0e0;
}

.candidate-application-card {
  background: white;
  border: 1px solid #e0e0e0;
}

.opportunity-candidates-page.dark .candidate-application-card {
  background: #2a2a2a;
  border-color: #444;
}

.opportunity-candidates-page.dark .candidate-application-card:hover {
  border-color: #666;
}

.card-header {
  background: #fafafa;
  border-bottom-color: #f0f0f0;
}

.opportunity-candidates-page.dark .card-header {
  background: #333;
  border-bottom-color: #444;
}

.candidate-name small {
  color: #999;
}

.opportunity-candidates-page.dark .candidate-name small {
  color: #aaa;
}

.application-message {
  background: #f5f5f5;
  border-left: 3px solid #2196f3;
}

.opportunity-candidates-page.dark .application-message {
  background: #1a1a1a;
  border-left-color: #64b5f6;
}

.application-message strong {
  color: #666;
}

.opportunity-candidates-page.dark .application-message strong {
  color: #aaa;
}

.application-message p {
  color: #333;
}

.opportunity-candidates-page.dark .application-message p {
  color: #ddd;
}

.fit-item .label {
  color: #666;
}

.opportunity-candidates-page.dark .fit-item .label {
  color: #aaa;
}

.fit-item .value {
  color: #333;
}

.opportunity-candidates-page.dark .fit-item .value {
  color: #ddd;
}

.skill-tag {
  background: #e0e0e0;
}

.opportunity-candidates-page.dark .skill-tag {
  background: #444;
  color: #ddd;
}

.more-tag {
  color: #666;
}

.opportunity-candidates-page.dark .more-tag {
  color: #aaa;
}

.card-footer {
  background: #fafafa;
  border-top: 1px solid #f0f0f0;
}

.opportunity-candidates-page.dark .card-footer {
  background: #333;
  border-top-color: #444;
}

.secondary-btn {
  background: #f0f0f0;
  color: #333;
}

.opportunity-candidates-page.dark .secondary-btn {
  background: #444;
  color: #e0e0e0;
}

.secondary-btn:hover {
  background: #e0e0e0;
}

.opportunity-candidates-page.dark .secondary-btn:hover {
  background: #555;
}

.empty-state {
  background: white;
  border: 2px dashed #ddd;
  color: #999;
}

.opportunity-candidates-page.dark .empty-state {
  background: #2a2a2a;
  border-color: #555;
  color: #aaa;
}

.empty-state small {
  color: #bbb;
}

.opportunity-candidates-page.dark .empty-state small {
  color: #888;
}

.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
}

.info-row > div:first-child {
  display: flex;
  align-items: center;
  gap: 12px;
}

.info-row strong {
  font-size: 16px;
}

.badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
}

.badge.open {
  background: #e8f5e9;
  color: #2e7d32;
}

.badge.closed {
  background: #ffebee;
  color: #c62828;
}

.badge.draft {
  background: #f5f5f5;
  color: #666;
}

.badge.applied {
  background: #e3f2fd;
  color: #1565c0;
}

.badge.interview {
  background: #fff3e0;
  color: #e65100;
}

.badge.offer {
  background: #f3e5f5;
  color: #6a1b9a;
}

.badge.accepted {
  background: #e8f5e9;
  color: #2e7d32;
}

.badge.rejected {
  background: #ffebee;
  color: #c62828;
}

.meta {
  display: flex;
  gap: 16px;
  font-size: 14px;
  color: #666;
}

.meta span {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.toolbar-card {
  background: white;
  padding: 16px 20px;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

.search-box {
  position: relative;
  margin-bottom: 16px;
}

.search-box i {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #999;
}

.search-box input {
  width: 100%;
  padding: 10px 12px 10px 36px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 14px;
}

.search-box input:focus {
  outline: none;
  border-color: #333;
  box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.05);
}

.toolbar-filters {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 12px;
}

.toolbar-filters label {
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 12px;
  font-weight: 600;
  color: #666;
}

.toolbar-filters select {
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 13px;
  background: white;
  cursor: pointer;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 16px;
  padding: 60px 20px;
  background: white;
  border-radius: 8px;
}

.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #e0e0e0;
  border-top-color: #333;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.candidates-list-section {
  display: grid;
  gap: 12px;
}

.candidates-count {
  padding: 0 4px;
}

.candidates-count h3 {
  margin: 0;
  font-size: 14px;
  font-weight: 600;
  color: #666;
}

.candidate-application-card {
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  overflow: hidden;
  transition: all 0.2s;
}

.candidate-application-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  border-color: #bbb;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 16px;
  border-bottom: 1px solid #f0f0f0;
  background: #fafafa;
}

.candidate-info {
  flex: 1;
}

.candidate-name {
  display: flex;
  flex-direction: column;
  gap: 4px;
  margin-bottom: 8px;
}

.candidate-name strong {
  font-size: 15px;
}

.candidate-name small {
  color: #999;
  font-size: 12px;
}

.match-indicator {
  display: flex;
  gap: 8px;
  align-items: center;
}

.match-score {
  display: inline-block;
  padding: 6px 10px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

.match-score.high-match {
  background: #c8e6c9;
  color: #1b5e20;
}

.match-score.medium-match {
  background: #ffe0b2;
  color: #e65100;
}

.match-score.low-match {
  background: #ffccbc;
  color: #bf360c;
}

.status-badge {
  text-align: right;
}

.card-body {
  padding: 16px;
  display: grid;
  gap: 12px;
}

.application-message {
  background: #f5f5f5;
  padding: 12px;
  border-radius: 4px;
  border-left: 3px solid #2196f3;
}

.application-message strong {
  display: block;
  margin-bottom: 6px;
  font-size: 12px;
  color: #666;
}

.application-message p {
  margin: 0;
  font-size: 13px;
  color: #333;
  line-height: 1.4;
}

.fit-details {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 12px;
}

.fit-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
}

.fit-item .label {
  font-weight: 600;
  color: #666;
  min-width: 50px;
}

.fit-item .value {
  color: #333;
  display: flex;
  gap: 6px;
  align-items: center;
  flex-wrap: wrap;
}

.skill-tag {
  display: inline-block;
  padding: 2px 8px;
  background: #e0e0e0;
  border-radius: 3px;
  font-size: 12px;
}

.more-tag {
  padding: 2px 8px;
  color: #666;
  font-size: 12px;
  font-weight: 600;
}

.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  background: #fafafa;
  border-top: 1px solid #f0f0f0;
  flex-wrap: wrap;
}

.secondary-btn,
.primary-btn,
.success-btn,
.danger-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border: none;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.secondary-btn {
  background: #f0f0f0;
  color: #333;
}

.secondary-btn:hover {
  background: #e0e0e0;
}

.primary-btn {
  background: #2196f3;
  color: white;
}

.primary-btn:hover {
  background: #1976d2;
}

.success-btn {
  background: #4caf50;
  color: white;
}

.success-btn:hover {
  background: #388e3c;
}

.danger-btn {
  background: #f44336;
  color: white;
}

.danger-btn:hover {
  background: #d32f2f;
}

.action-buttons {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 16px;
  padding: 60px 20px;
  background: white;
  border-radius: 8px;
  border: 2px dashed #ddd;
  color: #999;
}

.empty-state i {
  font-size: 48px;
  opacity: 0.5;
}

.empty-state p {
  margin: 0;
  font-size: 16px;
  font-weight: 500;
}

.empty-state small {
  color: #bbb;
  font-size: 13px;
}
</style>
