<template>
  <div class="requests-page">
    <header class="card page-header">
      <div>
        <p class="kicker">{{ t.title }}</p>
        <h1>{{ t.subtitle }}</h1>
        <p class="hint">{{ t.hint }}</p>
      </div>
    </header>

    <section class="summary-grid">
      <article class="card summary-card">
        <p>{{ t.totalRequests }}</p>
        <strong>{{ requests.length }}</strong>
      </article>
      <article class="card summary-card">
        <p>{{ t.pendingRequests }}</p>
        <strong>{{ pendingRequests.length }}</strong>
      </article>
      <article class="card summary-card">
        <p>{{ t.approvedRequests }}</p>
        <strong>{{ approvedRequests.length }}</strong>
      </article>
      <article class="card summary-card">
        <p>{{ t.rejectedRequests }}</p>
        <strong>{{ rejectedRequests.length }}</strong>
      </article>
    </section>

    <section class="card panel" v-if="pendingRequests.length">
      <div class="panel-head">
        <h2>{{ t.pendingReview }}</h2>
      </div>

      <div class="request-list">
        <article v-for="student in pendingRequests" :key="student.id" class="request-card">
          <div class="request-main">
            <h3>{{ student.fullName || t.unknownStudent }}</h3>
            <p>{{ student.email || '-' }} · {{ student.program || '-' }}</p>

            <div class="detail-grid">
              <div>
                <strong>{{ t.studentId }}:</strong> {{ student.studentId || '-' }}
              </div>
              <div>
                <strong>{{ t.level }}:</strong> {{ student.level || '-' }}
              </div>
              <div>
                <strong>{{ t.gender }}:</strong> {{ student.gender || '-' }}
              </div>
              <div>
                <strong>{{ t.phone }}:</strong> {{ student.phone || '-' }}
              </div>
              <div>
                <strong>{{ t.universityName }}:</strong> {{ readMeta(student, 'universityName') }}
              </div>
              <div>
                <strong>{{ t.universityEmail }}:</strong> {{ readMeta(student, 'universityEmail') }}
              </div>
            </div>

            <details class="verification-details">
              <summary>{{ t.allSubmittedData }}</summary>
              <div class="detail-grid">
                <div v-for="entry in verificationEntries(student)" :key="entry.key">
                  <strong>{{ entry.label }}:</strong> {{ entry.value }}
                </div>
              </div>
            </details>
          </div>

          <div class="request-actions">
            <button type="button" class="primary-btn" @click="approve(student.id)">
              {{ t.approve }}
            </button>

            <button type="button" class="ghost-btn" @click="setPending(student.id)">
              {{ t.markPending }}
            </button>

            <textarea
              v-model.trim="rejectReasons[student.id]"
              :placeholder="t.rejectReasonPlaceholder"
              rows="2"
            ></textarea>
            <button type="button" class="secondary-btn" @click="reject(student.id)">
              {{ t.reject }}
            </button>

            <p v-if="actionMessage[student.id]" class="action-message">
              {{ actionMessage[student.id] }}
            </p>
          </div>
        </article>
      </div>
    </section>

    <section class="card table-card">
      <div class="panel-head">
        <h2>{{ t.allRequests }}</h2>
      </div>

      <table>
        <thead>
          <tr>
            <th>{{ t.student }}</th>
            <th>{{ t.email }}</th>
            <th>{{ t.program }}</th>
            <th>{{ t.status }}</th>
            <th>{{ t.actions }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!requests.length">
            <td colspan="5" class="empty-row">{{ t.noRequests }}</td>
          </tr>
          <tr v-for="student in requests" :key="student.id">
            <td>
              <strong>{{ student.fullName || t.unknownStudent }}</strong>
            </td>
            <td>{{ student.email || '-' }}</td>
            <td>{{ student.program || '-' }}</td>
            <td>
              <span class="status-chip" :class="statusClass(student.status)">
                {{ statusLabel(student.status) }}
              </span>
            </td>
            <td>
              <div class="actions-row">
                <button type="button" class="text-btn" @click="viewStudent(student.id)">
                  {{ t.view }}
                </button>
                <button
                  v-if="student.status !== 'approved'"
                  type="button"
                  class="text-btn"
                  @click="approve(student.id)"
                >
                  {{ t.approve }}
                </button>
                <button
                  v-if="student.status !== 'pending'"
                  type="button"
                  class="text-btn"
                  @click="setPending(student.id)"
                >
                  {{ t.markPending }}
                </button>
                <button
                  v-if="student.status !== 'rejected'"
                  type="button"
                  class="text-btn"
                  @click="reject(student.id)"
                >
                  {{ t.reject }}
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </section>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useUiPreferences } from '@/compasables/useUiPreferences'
import { useAuthStore } from '@/stores/auth.store'
import { useStudentStore } from '@/stores/student.store'

const router = useRouter()
const { locale } = useUiPreferences()
const authStore = useAuthStore()
const studentStore = useStudentStore()

const rejectReasons = reactive({})
const actionMessage = reactive({})

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

const readMeta = (student, key) => {
  const raw = student?.raw || {}
  const verificationData = raw.verificationData || {}
  const bio = raw.bio

  if (verificationData[key]) return verificationData[key]

  if (!bio) return '-'

  try {
    const parsed = JSON.parse(bio)
    const value =
      parsed?.[key] ||
      parsed?.personal?.[key] ||
      parsed?.academic?.[key] ||
      parsed?.verificationData?.[key]
    return value || '-'
  } catch {
    return '-'
  }
}

const requests = computed(() => studentStore.students)

const pendingRequests = computed(() =>
  requests.value.filter((student) => (student.status || '').toLowerCase() === 'pending'),
)

const approvedRequests = computed(() =>
  requests.value.filter((student) => (student.status || '').toLowerCase() === 'approved'),
)

const rejectedRequests = computed(() =>
  requests.value.filter((student) => (student.status || '').toLowerCase() === 'rejected'),
)

const translations = {
  en: {
    title: 'Student Governance',
    subtitle: 'Student Registration Requests',
    hint: 'Review incoming student registration requests, then approve or reject.',
    totalRequests: 'Total Requests',
    pendingRequests: 'Pending',
    approvedRequests: 'Approved',
    rejectedRequests: 'Rejected',
    pendingReview: 'Pending Verification Review',
    student: 'Student',
    unknownStudent: 'Unknown Student',
    email: 'Email',
    phone: 'Phone',
    gender: 'Gender',
    program: 'Program',
    level: 'Level',
    studentId: 'Student ID',
    universityName: 'University Name',
    universityEmail: 'University Email',
    status: 'Status',
    actions: 'Actions',
    view: 'View',
    approve: 'Approve',
    markPending: 'Set Pending',
    reject: 'Reject',
    rejectReasonPlaceholder: 'Reason if you reject this request...',
    noRequests: 'No registration requests found.',
    allRequests: 'All Registration Requests',
    allSubmittedData: 'All submitted verification data',
    approvedDone: 'Student request approved.',
    rejectedDone: 'Student request rejected.',
    pendingDone: 'Student request set to pending.',
    requestFailed: 'Action failed. Please try again.',
    statusApproved: 'Approved',
    statusPending: 'Pending',
    statusRejected: 'Rejected',
    statusOther: 'Active',
  },
  fr: {
    title: 'Gouvernance Etudiants',
    subtitle: "Demandes d'inscription etudiante",
    hint: "Verifiez les nouvelles demandes et approuvez ou refusez l'activation.",
    totalRequests: 'Total demandes',
    pendingRequests: 'En attente',
    approvedRequests: 'Approuvees',
    rejectedRequests: 'Refusees',
    pendingReview: 'Demandes en attente de verification',
    student: 'Etudiant',
    unknownStudent: 'Etudiant inconnu',
    email: 'Email',
    phone: 'Telephone',
    gender: 'Genre',
    program: 'Programme',
    level: 'Niveau',
    studentId: 'Matricule',
    universityName: "Nom de l'universite",
    universityEmail: "Email de l'universite",
    status: 'Statut',
    actions: 'Actions',
    view: 'Voir',
    approve: 'Approuver',
    markPending: 'Remettre en attente',
    reject: 'Refuser',
    rejectReasonPlaceholder: 'Motif en cas de rejet...',
    noRequests: "Aucune demande d'inscription trouvee.",
    allRequests: "Toutes les demandes d'inscription",
    allSubmittedData: 'Toutes les donnees soumises pour verification',
    approvedDone: 'Demande etudiante approuvee.',
    rejectedDone: 'Demande etudiante refusee.',
    pendingDone: 'Demande etudiante remise en attente.',
    requestFailed: 'Action echouee. Veuillez reessayer.',
    statusApproved: 'Approuvee',
    statusPending: 'En attente',
    statusRejected: 'Refusee',
    statusOther: 'Active',
  },
}

const t = computed(() => translations[locale.value] || translations.en)

const statusClass = (status) => {
  const value = (status || '').toLowerCase()
  if (value === 'approved') return 'active'
  if (value === 'rejected') return 'needsAttention'
  if (value === 'pending') return 'inactive'
  return 'active'
}

const statusLabel = (status) => {
  const value = (status || '').toLowerCase()
  if (value === 'approved') return t.value.statusApproved
  if (value === 'rejected') return t.value.statusRejected
  if (value === 'pending') return t.value.statusPending
  return t.value.statusOther
}

const loadRequests = async () => {
  const universityId = connectedUniversityId.value
  if (universityId) {
    await studentStore.fetchStudentsByUniversity(universityId)
    return
  }
  await studentStore.fetchStudents()
}

const verificationEntries = (student) => {
  const parsedMeta = parseMeta(student)
  const verificationData = student?.raw?.verificationData || {}
  const personal = parsedMeta.personal || {}
  const academic = parsedMeta.academic || {}

  return [
    { key: 'fullName', label: t.value.student, value: student.fullName || '-' },
    {
      key: 'email',
      label: t.value.email,
      value: student.email || verificationData.email || parsedMeta.email || personal.email || '-',
    },
    {
      key: 'phone',
      label: t.value.phone,
      value: student.phone || verificationData.phone || parsedMeta.phone || personal.phone || '-',
    },
    {
      key: 'gender',
      label: t.value.gender,
      value:
        student.gender || verificationData.gender || personal.gender || parsedMeta.gender || '-',
    },
    {
      key: 'program',
      label: t.value.program,
      value:
        student.program ||
        verificationData.program ||
        parsedMeta.program ||
        academic.program ||
        '-',
    },
    {
      key: 'level',
      label: t.value.level,
      value:
        student.level ||
        verificationData.level ||
        parsedMeta.level ||
        academic.currentClass ||
        academic.level ||
        '-',
    },
    {
      key: 'studentId',
      label: t.value.studentId,
      value:
        student.studentId ||
        verificationData.studentId ||
        parsedMeta.studentId ||
        personal.studentId ||
        '-',
    },
    {
      key: 'universityName',
      label: t.value.universityName,
      value: verificationData.universityName || parsedMeta.universityName || '-',
    },
    {
      key: 'universityEmail',
      label: t.value.universityEmail,
      value: verificationData.universityEmail || parsedMeta.universityEmail || '-',
    },
    { key: 'submittedAt', label: 'Submitted At', value: student.createdAt || '-' },
    { key: 'currentStatus', label: t.value.status, value: statusLabel(student.status) },
    { key: 'reviewReason', label: 'Review Note', value: parsedMeta.reviewReason || '-' },
  ]
}

const approve = async (id) => {
  try {
    await studentStore.reviewStudentRegistrationRequest(id, 'approved', '')
    actionMessage[id] = t.value.approvedDone
  } catch {
    actionMessage[id] = t.value.requestFailed
  }
}

const setPending = async (id) => {
  try {
    await studentStore.reviewStudentRegistrationRequest(id, 'pending', '')
    actionMessage[id] = t.value.pendingDone
  } catch {
    actionMessage[id] = t.value.requestFailed
  }
}

const reject = async (id) => {
  const reason = rejectReasons[id] || ''
  try {
    await studentStore.reviewStudentRegistrationRequest(id, 'rejected', reason)
    actionMessage[id] = t.value.rejectedDone
  } catch {
    actionMessage[id] = t.value.requestFailed
  }
}

const viewStudent = (id) => {
  router.push({ name: 'viewStudentProfile', params: { id } })
}

const parseMeta = (student) => {
  const raw = student?.raw?.bio
  if (!raw) return {}

  try {
    const parsed = JSON.parse(raw)
    return parsed && typeof parsed === 'object' ? parsed : {}
  } catch {
    return {}
  }
}

onMounted(() => {
  loadRequests().catch(() => {})
})
</script>

<style scoped>
.requests-page {
  display: grid;
  gap: 16px;
}

.page-header,
.panel,
.table-card {
  padding: 16px;
}

.kicker,
.hint,
.summary-card p {
  margin: 0;
}

.kicker {
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-size: 0.76rem;
  font-weight: 700;
}

h1,
h2,
h3 {
  margin: 0;
}

h1 {
  margin-top: 6px;
}

.hint {
  margin-top: 8px;
  color: var(--muted);
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
  gap: 12px;
}

.summary-card {
  padding: 14px;
}

.summary-card p {
  color: var(--muted);
  font-size: 0.82rem;
}

.summary-card strong {
  margin-top: 6px;
  display: block;
  font-size: 1.55rem;
}

.panel-head {
  margin-bottom: 10px;
}

.request-list {
  display: grid;
  gap: 12px;
}

.request-card {
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 14px;
  display: grid;
  gap: 12px;
  background: var(--surface-soft, rgba(255, 255, 255, 0.04));
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 8px 14px;
  margin: 10px 0;
  font-size: 0.9rem;
}

.request-actions {
  display: grid;
  gap: 8px;
}

.verification-details {
  border-top: 1px solid var(--border);
  margin-top: 10px;
  padding-top: 10px;
}

.verification-details summary {
  cursor: pointer;
  color: var(--muted);
  font-weight: 700;
}

.request-actions textarea {
  width: 100%;
  border-radius: 10px;
  border: 1px solid var(--border);
  padding: 8px 10px;
  background: var(--surface);
  color: var(--text);
}

.action-message {
  margin: 0;
  color: var(--muted);
  font-size: 0.88rem;
}

.table-card {
  overflow-x: auto;
}

.actions-row {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.empty-row {
  text-align: center;
  color: var(--muted);
}
</style>
