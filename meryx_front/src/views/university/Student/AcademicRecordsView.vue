<template>
  <div class="academic-records-page">
    <header class="page-header">
      <div>
        <p class="eyebrow">University / Students</p>
        <h1>Academic Records</h1>
        <p class="intro">
          Review student academic performance, GPA progress, and completed course status in one
          place.
        </p>
      </div>
      <!-- <button>Create Record</button> -->
    </header>

    <section class="summary-grid">
      <article class="summary-card">
        <span>Total Records</span>
        <strong>{{ totalRecords }}</strong>
      </article>
      <article class="summary-card">
        <span>Average GPA</span>
        <strong>{{ averageGpa }}</strong>
      </article>
      <article class="summary-card">
        <span>On Track</span>
        <strong>{{ statusCounts.onTrack }}</strong>
      </article>
      <article class="summary-card">
        <span>Needs Attention</span>
        <strong>{{ statusCounts.needsAttention }}</strong>
      </article>
    </section>

    <section class="records-card">
      <div class="records-header">
        <div>
          <h2>Student records</h2>
          <p>Search students and filter by program or academic status.</p>
        </div>

        <div class="filters-row">
          <input
            type="search"
            v-model="query"
            placeholder="Search by student name or program"
            aria-label="Search records"
          />
          <select v-model="programFilter">
            <option value="all">All programs</option>
            <option v-for="program in programOptions" :key="program" :value="program">
              {{ program }}
            </option>
          </select>
          <select v-model="statusFilter">
            <option value="all">All status</option>
            <option value="onTrack">On track</option>
            <option value="needsAttention">Needs attention</option>
            <option value="probation">Probation</option>
          </select>
        </div>
      </div>

      <p v-if="studentStore.loading" class="loading-line">Loading academic records...</p>

      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Student</th>
              <th>Program</th>
              <th>GPA</th>
              <th>Credits</th>
              <th>Status</th>
              <th>Last Review</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="record in filteredRecords" :key="record.id">
              <td>
                <strong>{{ record.name }}</strong>
                <small>{{ record.email }}</small>
              </td>
              <td>{{ record.program }}</td>
              <td>{{ record.gpa }}</td>
              <td>{{ record.credits }}</td>
              <td>
                <span :class="['status-pill', record.status]">{{ record.label }}</span>
              </td>
              <td>{{ record.lastReview }}</td>
            </tr>
            <tr v-if="filteredRecords.length === 0">
              <td colspan="6" class="empty-state">No records match the current filters.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useAuthStore } from '@/stores/auth.store'
import { useStudentStore } from '@/stores/student.store'

const query = ref('')
const programFilter = ref('all')
const statusFilter = ref('all')
const studentStore = useStudentStore()
const authStore = useAuthStore()

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

const parseNumber = (value) => {
  const parsed = Number.parseFloat(String(value || '').replace(',', '.'))
  return Number.isFinite(parsed) ? parsed : null
}

const computeCredits = (records) => {
  if (!Array.isArray(records)) return 0
  return records.reduce((sum, item) => sum + Number(item?.credits || 0), 0)
}

const relativeDate = (value) => {
  if (!value) return '-'
  const parsed = new Date(value)
  if (Number.isNaN(parsed.getTime())) return '-'

  const diffMs = Date.now() - parsed.getTime()
  const dayMs = 24 * 60 * 60 * 1000
  const days = Math.max(0, Math.floor(diffMs / dayMs))

  if (days === 0) return 'today'
  if (days === 1) return '1 day ago'
  if (days < 7) return `${days} days ago`

  const weeks = Math.floor(days / 7)
  if (weeks === 1) return '1 week ago'
  if (weeks < 5) return `${weeks} weeks ago`

  return parsed.toLocaleDateString()
}

const statusFromStudent = (student) => {
  const explicit = String(student.status || '').toLowerCase()
  if (explicit.includes('probation')) return 'probation'
  if (explicit.includes('pending') || explicit.includes('rejected')) return 'needsAttention'

  const gpaValue = parseNumber(student.gpa)
  if (gpaValue === null) return 'onTrack'
  if (gpaValue < 2.5) return 'probation'
  if (gpaValue < 3) return 'needsAttention'
  return 'onTrack'
}

const statusLabel = (status) => {
  if (status === 'needsAttention') return 'Needs attention'
  if (status === 'probation') return 'Probation'
  return 'On track'
}

const studentRecords = computed(() =>
  (studentStore.students || []).map((student) => {
    const academicRecords = Array.isArray(student.academicRecords) ? student.academicRecords : []
    const computedStatus = statusFromStudent(student)
    const gpaValue = parseNumber(student.gpa)

    return {
      id: student.id,
      name: student.fullName || 'Student',
      email: student.email || '-',
      program: student.program || student.faculty || '-',
      gpa: gpaValue === null ? '0.00' : gpaValue.toFixed(2),
      credits: computeCredits(academicRecords),
      status: computedStatus,
      label: statusLabel(computedStatus),
      lastReview: relativeDate(student.updatedAt || student.createdAt),
    }
  }),
)

const programOptions = computed(() => {
  const options = new Set()
  for (const record of studentRecords.value) {
    if (record.program && record.program !== '-') {
      options.add(record.program)
    }
  }
  return [...options].sort((a, b) => a.localeCompare(b))
})

const filteredRecords = computed(() => {
  const queryLower = query.value.trim().toLowerCase()

  return studentRecords.value.filter((record) => {
    const matchesQuery =
      !queryLower ||
      record.name.toLowerCase().includes(queryLower) ||
      record.program.toLowerCase().includes(queryLower) ||
      record.email.toLowerCase().includes(queryLower)

    const matchesProgram = programFilter.value === 'all' || record.program === programFilter.value

    const matchesStatus = statusFilter.value === 'all' || record.status === statusFilter.value

    return matchesQuery && matchesProgram && matchesStatus
  })
})

const totalRecords = computed(() => studentRecords.value.length)
const averageGpa = computed(() => {
  if (!studentRecords.value.length) return '0.0'
  const total = studentRecords.value.reduce((sum, record) => sum + Number(record.gpa || 0), 0)
  return (total / studentRecords.value.length).toFixed(2)
})

const statusCounts = computed(() => ({
  onTrack: studentRecords.value.filter((record) => record.status === 'onTrack').length,
  needsAttention: studentRecords.value.filter((record) => record.status === 'needsAttention')
    .length,
  probation: studentRecords.value.filter((record) => record.status === 'probation').length,
}))

onMounted(async () => {
  const universityId = connectedUniversityId.value
  if (universityId) {
    await studentStore.fetchStudentsByUniversity(universityId)
    return
  }
  await studentStore.fetchStudents()
})
</script>

<style scoped>
.academic-records-page {
  display: grid;
  gap: 24px;
}

.page-header {
  display: grid;
  gap: 12px;
}

.eyebrow {
  text-transform: uppercase;
  letter-spacing: 0.16em;
  font-size: 0.8rem;
  color: var(--primary);
  margin: 0;
}

.page-header h1 {
  margin: 0;
  font-size: 2rem;
}

.intro {
  margin: 0;
  color: var(--muted);
  max-width: 720px;
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 18px;
}

.summary-card {
  padding: 24px;
  border-radius: 24px;
  background: var(--surface);
  border: 1px solid var(--border);
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
}

.summary-card span {
  display: block;
  color: var(--muted);
  margin-bottom: 12px;
}

.summary-card strong {
  font-size: 2rem;
  color: var(--text);
}

.records-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 24px;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
  padding: 24px;
}

.records-header {
  display: flex;
  justify-content: space-between;
  gap: 24px;
  flex-wrap: wrap;
  margin-bottom: 20px;
}

.records-header h2 {
  margin: 0;
}

.records-header p {
  margin: 8px 0 0;
  color: var(--muted);
}

.filters-row {
  display: grid;
  grid-template-columns: repeat(3, minmax(180px, 1fr));
  gap: 14px;
  width: 100%;
}

.filters-row input,
.filters-row select {
  width: 100%;
  padding: 14px 16px;
  border-radius: 16px;
  border: 1px solid var(--border);
  background: var(--bg);
  color: var(--text);
}

.loading-line {
  margin: 0 0 14px;
  color: var(--muted);
  font-size: 0.92rem;
}

.table-wrapper {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th,
td {
  padding: 18px 16px;
  text-align: left;
  border-bottom: 1px solid var(--border);
}

th {
  color: var(--muted);
  font-size: 0.95rem;
}

tbody tr:hover {
  background: rgba(30, 63, 102, 0.7);
}

td strong {
  display: block;
  color: var(--text);
  margin-bottom: 6px;
}

td small {
  color: var(--muted);
}

.status-pill {
  display: inline-flex;
  align-items: center;
  padding: 8px 12px;
  border-radius: 999px;
  font-size: 0.85rem;
  font-weight: 700;
}

.status-pill.onTrack {
  background: rgba(16, 185, 129, 0.16);
  color: #8af0b0;
}

.status-pill.needsAttention {
  background: rgba(212, 160, 23, 0.22);
  color: #f2d277;
}

.status-pill.probation {
  background: rgba(148, 163, 184, 0.2);
  color: #d3dfeb;
}

.empty-state {
  padding: 32px;
  text-align: center;
  color: var(--muted);
}

@media (max-width: 980px) {
  .summary-grid {
    grid-template-columns: 1fr 1fr;
  }

  .filters-row {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 700px) {
  .summary-grid {
    grid-template-columns: 1fr;
  }
}
</style>
