<template>
  <div class="student-list-page">
    <section class="hero-card">
      <div class="hero-main">
        <p class="eyebrow">University / Students</p>
        <h1>Student Management Workspace</h1>
        <p class="hero-description">
          Manage student accounts, monitor status, and review academic profile details from one
          organized dashboard.
        </p>

        <div class="hero-actions">
          <button class="secondary-btn" @click="triggerImport">
            <i class="bi bi-upload"></i>
            Import Students
          </button>
          <button class="primary-btn" @click="createStudent">
            <i class="bi bi-plus-lg"></i>
            Create Student Account
          </button>
        </div>
      </div>

      <div class="hero-stats">
        <article class="quick-stat">
          <span>Total Students</span>
          <strong>{{ filteredStudents.length }}</strong>
          <small>Matching current filters</small>
        </article>
        <article class="quick-stat">
          <span>Active</span>
          <strong>{{ activeCount }}</strong>
          <small>Currently enrolled</small>
        </article>
        <article class="quick-stat">
          <span>Suspended</span>
          <strong>{{ suspendedCount }}</strong>
          <small>Require follow-up</small>
        </article>
      </div>
    </section>

    <section class="toolbar-card">
      <div class="toolbar-top">
        <div class="search-box">
          <i class="bi bi-search"></i>
          <input
            type="search"
            placeholder="Search by name, email, program, skill, or language"
            v-model="search"
          />
        </div>

        <div class="result-count">{{ filteredStudents.length }} students</div>
      </div>

      <div class="filters-row">
        <label>
          <span>Status</span>
          <select v-model="statusFilter">
            <option value="all">All statuses</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
            <option value="Graduated">Graduated</option>
            <option value="Suspended">Suspended</option>
          </select>
        </label>

        <label>
          <span>Program</span>
          <select v-model="programFilter">
            <option value="all">All programs</option>
            <option v-for="program in programOptions" :key="program" :value="program">
              {{ program }}
            </option>
          </select>
        </label>

        <button class="ghost-btn" @click="resetFilters" type="button">
          <i class="bi bi-arrow-counterclockwise"></i>
          Reset filters
        </button>
      </div>
    </section>

    <section class="desktop-table-card">
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Student</th>
              <th>Academic Info</th>
              <th>Status</th>
              <th>Applications</th>
              <th>Program</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="student in filteredStudents" :key="student.id">
              <td>
                <div class="student-summary">
                  <div class="avatar">{{ studentInitials(student) }}</div>
                  <div>
                    <strong>{{ studentName(student) }}</strong>
                    <small>{{ student.email || 'no-email@example.com' }}</small>
                  </div>
                </div>
              </td>
              <td>
                <div class="student-tags">
                  <span>{{ student.level || 'Undergraduate' }}</span>
                  <span>{{ student.gpa || 'N/A' }} GPA</span>
                  <span>{{ student.attendance || '--' }}% attendance</span>
                </div>
              </td>
              <td>
                <span class="status-chip" :class="statusClass(student.status)">
                  {{ student.status || 'Active' }}
                </span>
              </td>
              <td>{{ student.applicationCount || 0 }} apps</td>
              <td>{{ student.program || '--' }}</td>
              <td>
                <div class="actions-cell">
                  <button class="text-btn" @click="viewStudent(student.id)">View</button>
                  <button class="text-btn" @click="editStudent(student.id)">Edit</button>
                  <button class="text-btn" @click="toggleStatus(student)">
                    {{ student.status === 'Suspended' ? 'Activate' : 'Suspend' }}
                  </button>
                  <button class="secondary-btn compact" @click="resetPassword(student.id)">
                    Reset Password
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!filteredStudents.length">
              <td colspan="6" class="empty-state">No students match these filters.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <section class="mobile-list" v-if="filteredStudents.length">
      <article class="mobile-student-card" v-for="student in filteredStudents" :key="student.id">
        <div class="mobile-head">
          <div class="avatar">{{ studentInitials(student) }}</div>
          <div>
            <strong>{{ studentName(student) }}</strong>
            <small>{{ student.email || 'no-email@example.com' }}</small>
          </div>
          <span class="status-chip" :class="statusClass(student.status)">
            {{ student.status || 'Active' }}
          </span>
        </div>

        <div class="mobile-meta">
          <span>{{ student.level || 'Undergraduate' }}</span>
          <span>{{ student.gpa || 'N/A' }} GPA</span>
          <span>{{ student.applicationCount || 0 }} apps</span>
          <span>{{ student.program || '--' }}</span>
        </div>

        <div class="mobile-actions">
          <button class="text-btn" @click="viewStudent(student.id)">View</button>
          <button class="text-btn" @click="editStudent(student.id)">Edit</button>
          <button class="text-btn" @click="toggleStatus(student)">
            {{ student.status === 'Suspended' ? 'Activate' : 'Suspend' }}
          </button>
          <button class="secondary-btn compact" @click="resetPassword(student.id)">Reset</button>
        </div>
      </article>
    </section>

    <section class="mobile-list" v-else>
      <article class="mobile-student-card empty-card">No students match these filters.</article>
    </section>

    <input
      ref="importInput"
      type="file"
      accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
      @change="importFile"
      hidden
    />
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useStudentStore } from '@/stores/student.store'
import { useAuthStore } from '@/stores/auth.store'

const router = useRouter()
const studentStore = useStudentStore()
const authStore = useAuthStore()
const search = ref('')
const statusFilter = ref('all')
const programFilter = ref('all')
const importInput = ref(null)

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

onMounted(async () => {
  const universityId = connectedUniversityId.value
  if (universityId) {
    await studentStore.fetchStudentsByUniversity(universityId)
    return
  }
  await studentStore.fetchStudents()
})

const createStudent = () => {
  router.push({ name: 'addStudent' })
}

const viewStudent = (id) => {
  router.push({ name: 'viewStudentProfile', params: { id } })
}

const editStudent = (id) => {
  router.push({ name: 'editStudent', params: { id } })
}

const studentName = (student) => {
  return (
    student.fullName ||
    `${student.firstName || ''} ${student.lastName || ''}`.trim() ||
    student.name ||
    'Student'
  )
}

const studentInitials = (student) => {
  const name = studentName(student)
  const parts = name.split(' ').filter(Boolean)
  if (!parts.length) return 'ST'
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase()
  return `${parts[0][0]}${parts[1][0]}`.toUpperCase()
}

const statusClass = (status) => {
  if (status === 'Inactive' || status === 'Suspended') return 'inactive'
  if (status === 'Graduated') return 'graduated'
  return 'active'
}

const filteredStudents = computed(() => {
  return studentStore.students.filter((student) => {
    if (statusFilter.value !== 'all' && (student.status || 'Active') !== statusFilter.value) {
      return false
    }
    if (programFilter.value !== 'all' && student.program !== programFilter.value) {
      return false
    }

    const term = search.value.toLowerCase()
    if (!term) return true

    const name = (
      student.fullName ||
      `${student.firstName || ''} ${student.lastName || ''}`.trim() ||
      student.name ||
      ''
    ).toLowerCase()

    const email = (student.email || '').toLowerCase()
    const program = (student.program || '').toLowerCase()
    const skills = (student.skills || []).join(' ').toLowerCase()
    const languages = (student.languages || []).join(' ').toLowerCase()

    return (
      name.includes(term) ||
      email.includes(term) ||
      program.includes(term) ||
      skills.includes(term) ||
      languages.includes(term)
    )
  })
})

const activeCount = computed(
  () =>
    filteredStudents.value.filter((student) => (student.status || 'Active') === 'Active').length,
)

const suspendedCount = computed(
  () =>
    filteredStudents.value.filter((student) => (student.status || 'Active') === 'Suspended').length,
)

const programOptions = computed(() => {
  const programs = new Set()
  studentStore.students.forEach((student) => {
    if (student.program) programs.add(student.program)
  })
  return [...programs]
})

const toggleStatus = (student) => {
  student.status = student.status === 'Suspended' ? 'Active' : 'Suspended'
}

const resetPassword = (id) => {
  window.alert(`Password reset link sent for student ${id}`)
}

const triggerImport = () => {
  importInput.value?.click()
}

const importFile = (event) => {
  const file = event.target.files?.[0]
  if (!file) return
  window.alert(`Importing ${file.name}. This is a placeholder action.`)
  event.target.value = ''
}

const resetFilters = () => {
  search.value = ''
  statusFilter.value = 'all'
  programFilter.value = 'all'
}
</script>

<style scoped>
.student-list-page {
  display: grid;
  gap: 16px;
}

.hero-card,
.toolbar-card,
.desktop-table-card,
.mobile-student-card,
.quick-stat {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 20px;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
}

.hero-card {
  display: grid;
  grid-template-columns: 1fr;
  gap: 12px;
  padding: 16px;
  position: relative;
  overflow: hidden;
}

.hero-card::before {
  content: '';
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 82% 20%, rgba(212, 160, 23, 0.2), transparent 45%),
    linear-gradient(135deg, rgba(13, 43, 69, 0.32), rgba(30, 63, 102, 0.18));
  pointer-events: none;
}

.hero-main,
.hero-stats {
  position: relative;
  z-index: 1;
}

.eyebrow {
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  color: var(--primary);
  font-size: 0.76rem;
  font-weight: 700;
}

h1 {
  margin: 8px 0 0;
  color: var(--text);
  font-size: clamp(1.35rem, 2.2vw, 1.7rem);
}

.hero-description {
  margin: 8px 0 0;
  color: var(--muted);
  max-width: 640px;
  line-height: 1.55;
}

.hero-actions {
  margin-top: 12px;
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.quick-stat {
  padding: 10px 12px;
  display: grid;
  gap: 2px;
  background: rgba(30, 63, 102, 0.56);
  border-radius: 14px;
}

.quick-stat span,
.quick-stat small,
.hero-description,
.result-count,
label span,
.student-summary small,
.empty-state,
.empty-card {
  color: var(--muted);
}

.quick-stat strong,
h1,
.student-summary strong,
.mobile-head strong,
.result-count {
  color: var(--text);
}

.quick-stat strong {
  font-size: 1.2rem;
}

.hero-stats {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 10px;
}

.toolbar-card {
  padding: 14px;
  display: grid;
  gap: 12px;
}

.toolbar-top {
  display: grid;
  grid-template-columns: 1fr auto;
  align-items: center;
  gap: 10px;
}

.search-box {
  display: flex;
  align-items: center;
  gap: 9px;
  border-radius: 14px;
  border: 1px solid var(--border);
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

.filters-row {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr)) auto;
  gap: 10px;
  align-items: end;
}

label {
  display: grid;
  gap: 6px;
}

label select {
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 10px 12px;
  background: var(--surface);
  color: var(--text);
}

.table-wrapper {
  overflow: auto;
  border-radius: 20px;
}

table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  min-width: 980px;
}

thead th {
  text-align: left;
  padding: 16px;
  color: var(--muted);
  font-size: 0.82rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  border-bottom: 1px solid var(--border);
  background: var(--surface-soft);
}

tbody td {
  padding: 16px;
  border-bottom: 1px solid var(--border);
  vertical-align: top;
  color: var(--text);
}

.student-summary {
  display: flex;
  gap: 10px;
  align-items: center;
}

.avatar {
  width: 38px;
  height: 38px;
  border-radius: 12px;
  background: rgba(212, 160, 23, 0.16);
  color: #f2d277;
  display: grid;
  place-items: center;
  font-weight: 700;
  flex: 0 0 auto;
}

.student-summary strong,
.student-summary small {
  display: block;
}

.student-tags,
.mobile-meta,
.actions-cell,
.mobile-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.student-tags span,
.mobile-meta span {
  background: rgba(30, 63, 102, 0.88);
  color: #d7e6f5;
  padding: 6px 10px;
  border-radius: 999px;
  font-size: 0.8rem;
  border: 1px solid rgba(242, 244, 247, 0.16);
}

.status-chip {
  display: inline-flex;
  align-items: center;
  padding: 7px 11px;
  border-radius: 999px;
  font-size: 0.8rem;
  font-weight: 700;
}

.status-chip.active {
  background: rgba(16, 185, 129, 0.15);
  color: #8af0b0;
}

.status-chip.inactive {
  background: rgba(148, 163, 184, 0.2);
  color: #c9d6e4;
}

.status-chip.graduated {
  background: rgba(212, 160, 23, 0.22);
  color: #f2d277;
}

.text-btn,
.secondary-btn,
.primary-btn,
.ghost-btn {
  border: none;
  border-radius: 999px;
  padding: 9px 13px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 7px;
  font-size: 0.9rem;
}

.text-btn {
  background: transparent;
  color: #d7e6f5;
}

.secondary-btn {
  background: rgba(30, 63, 102, 0.9);
  color: #f2f4f7;
  border: 1px solid rgba(212, 160, 23, 0.45);
}

.primary-btn {
  background: #d4a017;
  color: #0d2b45;
  border: 1px solid rgba(212, 160, 23, 0.9);
}

.ghost-btn {
  border: 1px solid var(--border);
  background: rgba(30, 63, 102, 0.86);
  color: #f2f4f7;
}

.secondary-btn.compact {
  padding: 7px 10px;
  font-size: 0.82rem;
}

.empty-state {
  text-align: center;
  padding: 26px;
}

.mobile-list {
  display: none;
}

.mobile-student-card {
  padding: 12px;
  display: grid;
  gap: 10px;
}

.mobile-head {
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 8px;
  align-items: center;
}

.mobile-head strong,
.mobile-head small {
  display: block;
}

.empty-card {
  text-align: center;
}

@media (max-width: 1080px) {
  .hero-stats {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .filters-row {
    grid-template-columns: 1fr 1fr;
  }

  .ghost-btn {
    grid-column: 1 / -1;
    justify-content: center;
  }
}

@media (max-width: 860px) {
  .desktop-table-card {
    display: none;
  }

  .mobile-list {
    display: grid;
    gap: 10px;
  }

  .hero-stats {
    grid-template-columns: 1fr;
  }

  .toolbar-top,
  .filters-row {
    grid-template-columns: 1fr;
  }

  .hero-actions {
    display: grid;
    grid-template-columns: 1fr;
  }

  .hero-actions button {
    width: 100%;
    justify-content: center;
  }
}
</style>
