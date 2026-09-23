<template>
  <div class="opportunity-page">
    <header class="page-header">
      <div>
        <p class="eyebrow">University / Opportunities</p>
        <h1>University Opportunity Management</h1>
        <p class="intro">
          Monitor internships, jobs, and research opportunities shared with students and partner
          companies.
        </p>
      </div>

      <div class="header-actions">
        <button class="secondary-btn" type="button">
          <i class="bi bi-funnel"></i>
          Saved Filters
        </button>
        <button class="primary-btn" type="button">
          <i class="bi bi-plus-lg"></i>
          Add Opportunity
        </button>
      </div>
    </header>

    <section class="summary-grid">
      <article class="summary-card">
        <span>Total Opportunities</span>
        <strong>{{ totalCount }}</strong>
      </article>
      <article class="summary-card">
        <span>Active</span>
        <strong>{{ activeCount }}</strong>
      </article>
      <article class="summary-card">
        <span>Closing Soon</span>
        <strong>{{ closingSoonCount }}</strong>
      </article>
      <article class="summary-card">
        <span>Archived</span>
        <strong>{{ archivedCount }}</strong>
      </article>
    </section>

    <section class="toolbar-card">
      <div class="search-box">
        <i class="bi bi-search"></i>
        <input
          v-model="filters.query"
          type="search"
          placeholder="Search by title, company, skill, or location"
        />
      </div>

      <div class="filters-grid">
        <select v-model="filters.type">
          <option value="all">All Types</option>
          <option value="Internship">Internship</option>
          <option value="Full-time">Full-time</option>
          <option value="Part-time">Part-time</option>
          <option value="Research">Research</option>
          <option value="Scholarship">Scholarship</option>
        </select>

        <select v-model="filters.faculty">
          <option value="all">All Faculties</option>
          <option v-for="faculty in facultyOptions" :key="faculty" :value="faculty">
            {{ faculty }}
          </option>
        </select>

        <select v-model="filters.status">
          <option value="all">All Statuses</option>
          <option value="Active">Active</option>
          <option value="Closing Soon">Closing Soon</option>
          <option value="Archived">Archived</option>
        </select>

        <button class="secondary-btn" type="button" @click="resetFilters">Reset</button>
      </div>
    </section>

    <p v-if="isLoadingOpportunities" class="info-line">Loading opportunities...</p>
    <p v-else-if="opportunityError" class="error-line">{{ opportunityError }}</p>

    <section class="workspace-grid">
      <article class="list-card">
        <div class="card-header-row">
          <h2>Opportunities</h2>
          <span>{{ filteredOpportunities.length }} results</span>
        </div>

        <div class="opportunity-list">
          <button
            v-for="item in filteredOpportunities"
            :key="item.id"
            class="opportunity-item"
            :class="{ active: selectedOpportunity?.id === item.id }"
            type="button"
            @click="selectedOpportunityId = item.id"
          >
            <div class="item-main">
              <strong>{{ item.title }}</strong>
              <small>{{ item.company }} · {{ item.location }}</small>
              <div class="tag-row">
                <span class="pill">{{ item.type }}</span>
                <span class="pill">{{ item.faculty }}</span>
              </div>
            </div>
            <span class="status-pill" :class="statusClass(item.status)">{{ item.status }}</span>
          </button>

          <div v-if="!filteredOpportunities.length" class="empty-state">
            No opportunities found for these filters.
          </div>
        </div>
      </article>

      <article class="details-card" v-if="selectedOpportunity">
        <div class="card-header-row">
          <h2>Opportunity Details</h2>
          <span class="status-pill" :class="statusClass(selectedOpportunity.status)">
            {{ selectedOpportunity.status }}
          </span>
        </div>

        <div class="detail-block">
          <h3>{{ selectedOpportunity.title }}</h3>
          <p>{{ selectedOpportunity.description }}</p>
        </div>

        <div class="details-grid">
          <div>
            <span>Company</span>
            <strong>{{ selectedOpportunity.company }}</strong>
          </div>
          <div>
            <span>Location</span>
            <strong>{{ selectedOpportunity.location }}</strong>
          </div>
          <div>
            <span>Type</span>
            <strong>{{ selectedOpportunity.type }}</strong>
          </div>
          <div>
            <span>Faculty</span>
            <strong>{{ selectedOpportunity.faculty }}</strong>
          </div>
          <div>
            <span>Academic Year</span>
            <strong>{{ selectedOpportunity.academicYear }}</strong>
          </div>
          <div>
            <span>Deadline</span>
            <strong>{{ selectedOpportunity.deadline }}</strong>
          </div>
        </div>

        <div class="skills-block">
          <h4>Required Skills</h4>
          <div class="chip-row">
            <span v-for="skill in selectedOpportunity.skills" :key="skill">{{ skill }}</span>
          </div>
        </div>

        <div class="applications-block">
          <div class="applications-head">
            <h4>Student Applications</h4>
            <span>{{ selectedOpportunityApplications.length }} received</span>
          </div>

          <p v-if="isLoadingApplications" class="info-line">Loading applications...</p>
          <p v-else-if="applicationsError" class="error-line">{{ applicationsError }}</p>

          <div v-else-if="selectedOpportunityApplications.length" class="applications-list">
            <article
              v-for="application in selectedOpportunityApplications"
              :key="application.id"
              class="application-item"
            >
              <div class="application-top">
                <strong>{{ application.studentName }}</strong>
                <span
                  class="status-pill"
                  :class="statusClass(statusFromApplication(application.status))"
                >
                  {{ statusFromApplication(application.status) }}
                </span>
              </div>

              <p class="application-meta">
                <span>{{ application.email || '-' }}</span>
                <span>{{ application.phone || '-' }}</span>
                <span>{{ application.program || '-' }}</span>
              </p>

              <p class="application-meta">
                <span>Applied: {{ formatAppliedDate(application.appliedDate) }}</span>
                <span>
                  Interview:
                  {{
                    application.interviewDate
                      ? formatAppliedDate(application.interviewDate)
                      : 'Not scheduled'
                  }}
                </span>
              </p>

              <div class="application-motivation" v-if="application.motivationLetter">
                <strong>Motivation</strong>
                <p>{{ application.motivationLetter }}</p>
              </div>
            </article>
          </div>

          <div v-else class="empty-state">No applications submitted for this opportunity yet.</div>
        </div>

        <div class="actions-row">
          <button class="secondary-btn" type="button">Edit</button>
          <button class="secondary-btn" type="button">Archive</button>
          <button class="primary-btn" type="button" @click="openShareModal">
            Share with Students
          </button>
        </div>
      </article>

      <article class="details-card" v-else>
        <div class="empty-state details-empty">
          Select an opportunity from the list to see details.
        </div>
      </article>
    </section>

    <div v-if="isShareModalOpen" class="share-modal-backdrop" @click="closeShareModal"></div>
    <section v-if="isShareModalOpen" class="share-modal" role="dialog" aria-modal="true">
      <header class="share-modal-header">
        <div>
          <h3>Share Opportunity</h3>
          <p>
            {{ selectedOpportunity?.title || 'Opportunity' }}
          </p>
        </div>
        <button type="button" class="icon-close" @click="closeShareModal">x</button>
      </header>

      <div class="share-modal-toolbar">
        <input
          v-model="studentQuery"
          type="search"
          placeholder="Search student by name, email or program"
        />
        <button type="button" class="secondary-btn" @click="toggleSelectAllStudents">
          {{ allVisibleStudentsSelected ? 'Clear selection' : 'Select all visible' }}
        </button>
      </div>

      <div class="share-list" v-if="!studentStore.loading">
        <label v-for="student in filteredUniversityStudents" :key="student.id" class="share-row">
          <input
            type="checkbox"
            :checked="selectedStudentIds.includes(student.id)"
            @change="toggleStudentSelection(student.id)"
          />
          <div class="share-meta">
            <strong>{{ student.fullName || 'Student' }}</strong>
            <small>{{ student.email || '-' }} · {{ student.program || '-' }}</small>
          </div>
          <span
            class="status-pill"
            :class="
              statusClass(
                (student.status || '').toLowerCase() === 'approved' ? 'Active' : 'Closing Soon',
              )
            "
          >
            {{ student.status || 'pending' }}
          </span>
        </label>
        <p v-if="!filteredUniversityStudents.length" class="empty-state">
          No university students found.
        </p>
      </div>

      <p v-else class="info-line">Loading university students...</p>

      <footer class="share-modal-actions">
        <span>{{ selectedStudentIds.length }} selected</span>
        <div>
          <button type="button" class="secondary-btn" @click="closeShareModal">Cancel</button>
          <button
            type="button"
            class="primary-btn"
            :disabled="!selectedStudentIds.length"
            @click="shareOpportunityWithStudents"
          >
            Share now
          </button>
        </div>
      </footer>
    </section>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useCompanyOpportunities } from '@/compasables/useCompanyOpportunities'
import { useCompanyAdmin } from '@/compasables/useCompanyAdmin'
import { useAuthStore } from '@/stores/auth.store'
import { useStudentStore } from '@/stores/student.store'
import { useCandidature } from '@/compasables/useCandidature'
import { useToast } from '@/compasables/useToast'

const { opportunities, isLoadingOpportunities, opportunityError, fetchOpportunities } =
  useCompanyOpportunities()
const { getCompanyById, fetchCompanies } = useCompanyAdmin()
const authStore = useAuthStore()
const studentStore = useStudentStore()
const {
  candidatures,
  fetchCandidatures,
  isLoading: isLoadingApplications,
  error: applicationsError,
} = useCandidature()
const toast = useToast()

const selectedOpportunityId = ref(null)
const isShareModalOpen = ref(false)
const studentQuery = ref('')
const selectedStudentIds = ref([])
const APPLICATION_FORM_PREFIX = 'MERYX_APPLICATION_FORM::'

const parseCompanyId = (value) => {
  if (!value && value !== 0) return 0
  if (typeof value === 'number') return value
  if (typeof value === 'string') {
    const match = value.trim().match(/(?:\/)?(\d+)$/)
    return match ? Number(match[1]) : Number(value) || 0
  }
  if (typeof value === 'object') {
    return parseCompanyId(value.id || value.companyId || value.company?.id)
  }
  return 0
}

const parseId = (value) => {
  if (!value) return null
  if (typeof value === 'number') return value
  if (typeof value === 'string') {
    const parts = value.split('/')
    const parsed = Number(parts[parts.length - 1])
    return Number.isFinite(parsed) ? parsed : null
  }
  if (typeof value === 'object') {
    if (typeof value.id === 'number') return value.id
    if (typeof value['@id'] === 'string') {
      const parts = value['@id'].split('/')
      const parsed = Number(parts[parts.length - 1])
      return Number.isFinite(parsed) ? parsed : null
    }
  }
  return null
}

const connectedUniversityId = computed(() => {
  const user = authStore.user || {}
  return (
    parseId(user.universityId) ||
    parseId(user.university) ||
    parseId(user.universityProfile) ||
    (authStore.isUniversity ? parseId(user.id) : null)
  )
})

const toDate = (value) => {
  if (!value) return null
  const parsed = new Date(value)
  return Number.isNaN(parsed.getTime()) ? null : parsed
}

const statusFromOpportunity = (item) => {
  const raw = String(item.status || '').toLowerCase()
  if (raw.includes('archiv') || raw.includes('close')) return 'Archived'

  const deadlineDate = toDate(item.deadLine)
  if (deadlineDate) {
    const diffMs = deadlineDate.getTime() - Date.now()
    const diffDays = Math.ceil(diffMs / (1000 * 60 * 60 * 24))
    if (diffDays < 0) return 'Archived'
    if (diffDays <= 7) return 'Closing Soon'
  }

  return 'Active'
}

const displayDeadline = (value) => {
  const parsed = toDate(value)
  if (!parsed) return '-'
  return parsed.toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })
}

const normalizeOpportunity = (item) => {
  const companyId = parseCompanyId(item.companyId)
  const company = getCompanyById(companyId)
  const status = statusFromOpportunity(item)

  return {
    id: item.id,
    title: item.title || 'Untitled opportunity',
    company: company?.name || item.raw?.companyId?.name || 'Company',
    location: item.location || 'Remote',
    type: item.type || item.raw?.category || 'Opportunity',
    faculty: item.department || 'General',
    academicYear: item.raw?.academicYear || item.raw?.year || '-',
    status,
    deadline: displayDeadline(item.deadLine),
    skills: Array.isArray(item.requirements) ? item.requirements : [],
    description: item.description || '-',
  }
}

const normalizedOpportunities = computed(() => opportunities.value.map(normalizeOpportunity))

const filters = ref({
  query: '',
  type: 'all',
  faculty: 'all',
  status: 'all',
})

const filteredOpportunities = computed(() => {
  const query = filters.value.query.trim().toLowerCase()

  return normalizedOpportunities.value.filter((item) => {
    const matchesQuery =
      !query ||
      item.title.toLowerCase().includes(query) ||
      item.company.toLowerCase().includes(query) ||
      item.location.toLowerCase().includes(query) ||
      item.skills.join(' ').toLowerCase().includes(query)

    const matchesType = filters.value.type === 'all' || item.type === filters.value.type
    const matchesFaculty = filters.value.faculty === 'all' || item.faculty === filters.value.faculty
    const matchesStatus = filters.value.status === 'all' || item.status === filters.value.status

    return matchesQuery && matchesType && matchesFaculty && matchesStatus
  })
})

const selectedOpportunity = computed(() => {
  if (!filteredOpportunities.value.length) return null
  if (selectedOpportunityId.value === null) return filteredOpportunities.value[0]

  return (
    filteredOpportunities.value.find((item) => item.id === selectedOpportunityId.value) ||
    filteredOpportunities.value[0]
  )
})

const safeParseNotes = (notes) => {
  const value = String(notes || '')
  if (!value) return null

  const payload = value.startsWith(APPLICATION_FORM_PREFIX)
    ? value.slice(APPLICATION_FORM_PREFIX.length)
    : value

  try {
    const parsed = JSON.parse(payload)
    return parsed && typeof parsed === 'object' ? parsed : null
  } catch {
    return null
  }
}

const formatAppliedDate = (value) => {
  const parsed = toDate(value)
  if (!parsed) return '-'
  return parsed.toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })
}

const statusFromApplication = (status) => {
  const value = String(status || '').toLowerCase()
  if (value === 'interview') return 'Closing Soon'
  if (value === 'offer' || value === 'accepted') return 'Active'
  if (value === 'rejected') return 'Archived'
  return 'Active'
}

const selectedOpportunityApplications = computed(() => {
  const opportunityId = selectedOpportunity.value?.id
  if (!opportunityId) return []

  return candidatures.value
    .filter((item) => Number(item.opportunityId) === Number(opportunityId))
    .map((item) => {
      const parsed = safeParseNotes(item.notes)
      return {
        id: item.id,
        status: item.status,
        appliedDate: item.appliedDate,
        interviewDate: item.interviewDate,
        studentName: parsed?.fullName || item.student?.fullName || 'Student',
        email: parsed?.email || item.student?.email || '',
        phone: parsed?.phone || '',
        program: parsed?.program || item.student?.program || '',
        motivationLetter: parsed?.motivationLetter || (parsed ? '' : item.notes || ''),
      }
    })
    .sort((left, right) => {
      const leftDate = toDate(left.appliedDate)?.getTime() || 0
      const rightDate = toDate(right.appliedDate)?.getTime() || 0
      return rightDate - leftDate
    })
})

const facultyOptions = computed(() => [
  ...new Set(normalizedOpportunities.value.map((item) => item.faculty).filter(Boolean)),
])

const totalCount = computed(() => normalizedOpportunities.value.length)
const activeCount = computed(
  () => normalizedOpportunities.value.filter((item) => item.status === 'Active').length,
)
const closingSoonCount = computed(
  () => normalizedOpportunities.value.filter((item) => item.status === 'Closing Soon').length,
)
const archivedCount = computed(
  () => normalizedOpportunities.value.filter((item) => item.status === 'Archived').length,
)

const statusClass = (status) => {
  if (status === 'Archived') return 'archived'
  if (status === 'Closing Soon') return 'closing'
  return 'active'
}

const resetFilters = () => {
  filters.value = {
    query: '',
    type: 'all',
    faculty: 'all',
    status: 'all',
  }
}

const filteredUniversityStudents = computed(() => {
  const query = studentQuery.value.trim().toLowerCase()

  return (studentStore.students || []).filter((student) => {
    const matchesQuery =
      !query ||
      String(student.fullName || '')
        .toLowerCase()
        .includes(query) ||
      String(student.email || '')
        .toLowerCase()
        .includes(query) ||
      String(student.program || '')
        .toLowerCase()
        .includes(query)

    return matchesQuery
  })
})

const allVisibleStudentsSelected = computed(
  () =>
    filteredUniversityStudents.value.length > 0 &&
    filteredUniversityStudents.value.every((student) =>
      selectedStudentIds.value.includes(student.id),
    ),
)

const openShareModal = async () => {
  const universityId = connectedUniversityId.value
  if (universityId) {
    await studentStore.fetchStudentsByUniversity(universityId)
  } else {
    await studentStore.fetchStudents()
  }

  isShareModalOpen.value = true
}

const closeShareModal = () => {
  isShareModalOpen.value = false
  studentQuery.value = ''
  selectedStudentIds.value = []
}

const toggleStudentSelection = (studentId) => {
  const exists = selectedStudentIds.value.includes(studentId)
  selectedStudentIds.value = exists
    ? selectedStudentIds.value.filter((id) => id !== studentId)
    : [...selectedStudentIds.value, studentId]
}

const toggleSelectAllStudents = () => {
  if (allVisibleStudentsSelected.value) {
    const visibleIds = filteredUniversityStudents.value.map((student) => student.id)
    selectedStudentIds.value = selectedStudentIds.value.filter((id) => !visibleIds.includes(id))
    return
  }

  const visibleIds = filteredUniversityStudents.value.map((student) => student.id)
  selectedStudentIds.value = [...new Set([...selectedStudentIds.value, ...visibleIds])]
}

const shareOpportunityWithStudents = () => {
  const targetStudents = filteredUniversityStudents.value.filter((student) =>
    selectedStudentIds.value.includes(student.id),
  )

  if (!targetStudents.length || !selectedOpportunity.value) {
    toast.warning('Select at least one student.')
    return
  }

  toast.success(
    `Opportunity "${selectedOpportunity.value.title}" shared with ${targetStudents.length} students.`,
  )
  closeShareModal()
}

watch(
  filteredOpportunities,
  (rows) => {
    if (!rows.length) {
      selectedOpportunityId.value = null
      return
    }

    const exists = rows.some((item) => item.id === selectedOpportunityId.value)
    if (!exists) {
      selectedOpportunityId.value = rows[0].id
    }
  },
  { immediate: true },
)

onMounted(async () => {
  await Promise.all([
    fetchCompanies().catch(() => {}),
    fetchOpportunities().catch(() => {}),
    fetchCandidatures().catch(() => {}),
  ])
})
</script>

<style scoped>
.opportunity-page {
  display: grid;
  gap: 20px;
  width: 100%;
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
.card-header-row h2 {
  margin: 0;
  color: var(--text);
}

.intro {
  margin: 10px 0 0;
  color: var(--muted);
  max-width: 760px;
}

.info-line,
.error-line {
  margin: 0;
  color: var(--muted);
}

.error-line {
  color: #b91c1c;
}

.share-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(2, 6, 23, 0.45);
  z-index: 40;
}

.share-modal {
  position: fixed;
  z-index: 41;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: min(760px, calc(100vw - 24px));
  max-height: calc(100vh - 48px);
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 16px;
  box-shadow: 0 24px 60px rgba(2, 6, 23, 0.28);
  display: grid;
  gap: 12px;
  padding: 16px;
}

.share-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  gap: 12px;
}

.share-modal-header h3 {
  margin: 0;
}

.share-modal-header p {
  margin: 6px 0 0;
  color: var(--muted);
}

.icon-close {
  border: 1px solid var(--border);
  background: var(--surface-soft);
  color: var(--text);
  width: 30px;
  height: 30px;
  border-radius: 8px;
}

.share-modal-toolbar {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 10px;
}

.share-modal-toolbar input {
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 10px 12px;
  background: var(--surface-soft);
  color: var(--text);
}

.share-list {
  display: grid;
  gap: 8px;
  overflow: auto;
  max-height: 50vh;
  padding-right: 2px;
}

.share-row {
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 10px;
  align-items: center;
  border: 1px solid var(--border);
  background: var(--surface-soft);
  border-radius: 12px;
  padding: 10px;
}

.share-meta {
  display: grid;
  gap: 3px;
}

.share-meta small {
  color: var(--muted);
}

.share-modal-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
  border-top: 1px solid var(--border);
  padding-top: 12px;
}

.share-modal-actions > div {
  display: flex;
  gap: 8px;
}

.header-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.primary-btn,
.secondary-btn,
.filter-btn,
.opportunity-item {
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

.secondary-btn,
.filter-btn {
  background: var(--surface);
  color: var(--text);
  border: 1px solid var(--border);
  padding: 12px 16px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 14px;
}

.summary-card,
.toolbar-card,
.list-card,
.details-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 22px;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
}

.summary-card {
  padding: 20px;
}

.summary-card span {
  color: var(--muted);
  font-size: 0.9rem;
}

.summary-card strong {
  display: block;
  margin-top: 10px;
  color: var(--text);
  font-size: 1.8rem;
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

.search-box input,
.filters-grid select {
  width: 100%;
  border: none;
  outline: none;
  background: transparent;
  color: var(--text);
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 10px;
}

.filters-grid select {
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 11px 12px;
  background: var(--surface);
}

.workspace-grid {
  display: grid;
  grid-template-columns: 1fr 1.2fr;
  gap: 16px;
}

.list-card,
.details-card {
  padding: 16px;
  display: grid;
  gap: 14px;
}

.card-header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
}

.card-header-row span {
  color: var(--muted);
  font-size: 0.9rem;
}

.opportunity-list {
  display: grid;
  gap: 10px;
  max-height: 560px;
  overflow: auto;
}

.opportunity-item {
  width: 100%;
  text-align: left;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
  padding: 14px;
  background: var(--surface-soft);
  border: 1px solid transparent;
}

.opportunity-item.active {
  border-color: rgba(6, 170, 197, 0.4);
  background: rgba(6, 170, 197, 0.08);
}

.item-main {
  display: grid;
  gap: 5px;
}

.item-main strong,
.detail-block h3,
.details-grid strong,
.chip-row span {
  color: var(--text);
}

.item-main small,
.details-grid span,
.detail-block p {
  color: var(--muted);
}

.tag-row,
.chip-row,
.actions-row {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.pill,
.chip-row span {
  padding: 6px 10px;
  border-radius: 999px;
  background: rgba(6, 170, 197, 0.12);
  color: var(--primary);
  font-size: 0.82rem;
}

.status-pill {
  white-space: nowrap;
  border-radius: 999px;
  padding: 8px 11px;
  font-size: 0.78rem;
  font-weight: 700;
}

.status-pill.active {
  background: rgba(20, 184, 166, 0.14);
  color: #0f766e;
}

.status-pill.closing {
  background: rgba(234, 179, 8, 0.16);
  color: #92400e;
}

.status-pill.archived {
  background: rgba(15, 23, 42, 0.08);
  color: #475569;
}

.detail-block p {
  margin: 8px 0 0;
  line-height: 1.7;
}

.details-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
}

.details-grid div {
  display: grid;
  gap: 4px;
  border-bottom: 1px solid var(--border);
  padding-bottom: 10px;
}

.skills-block {
  display: grid;
  gap: 8px;
}

.applications-block {
  display: grid;
  gap: 10px;
  border-top: 1px solid var(--border);
  padding-top: 10px;
}

.applications-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
}

.applications-head h4 {
  margin: 0;
  color: var(--text);
}

.applications-head span {
  color: var(--muted);
  font-size: 0.9rem;
}

.applications-list {
  display: grid;
  gap: 10px;
}

.application-item {
  border: 1px solid var(--border);
  background: var(--surface-soft);
  border-radius: 12px;
  padding: 10px;
  display: grid;
  gap: 8px;
}

.application-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
}

.application-top strong {
  color: var(--text);
}

.application-meta {
  margin: 0;
  color: var(--muted);
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.application-motivation {
  display: grid;
  gap: 4px;
}

.application-motivation strong {
  color: var(--text);
}

.application-motivation p {
  margin: 0;
  color: var(--muted);
  line-height: 1.5;
}

.skills-block h4 {
  margin: 0;
  color: var(--text);
}

.empty-state {
  text-align: center;
  padding: 20px;
  color: var(--muted);
  background: var(--surface-soft);
  border-radius: 14px;
}

.details-empty {
  min-height: 220px;
  display: grid;
  place-items: center;
}

@media (max-width: 1100px) {
  .workspace-grid {
    grid-template-columns: 1fr;
  }

  .summary-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 760px) {
  .page-header {
    flex-direction: column;
  }

  .filters-grid {
    grid-template-columns: 1fr;
  }

  .summary-grid {
    grid-template-columns: 1fr;
  }

  .details-grid {
    grid-template-columns: 1fr;
  }

  .share-modal-toolbar {
    grid-template-columns: 1fr;
  }

  .share-row {
    grid-template-columns: auto 1fr;
  }

  .share-modal-actions {
    flex-direction: column;
    align-items: stretch;
  }

  .share-modal-actions > div {
    width: 100%;
  }

  .header-actions {
    width: 100%;
  }

  .header-actions button,
  .actions-row button {
    width: 100%;
    justify-content: center;
  }
}
</style>
