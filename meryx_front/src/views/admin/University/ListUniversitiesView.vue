<template>
  <div class="universities-page">
    <header class="card page-header">
      <div>
        <p class="kicker">{{ t.title }}</p>
        <h1>{{ t.subtitle }}</h1>
        <p class="hint">{{ t.hint }}</p>
      </div>
    </header>

    <section class="summary-grid">
      <article class="card summary-card">
        <p>{{ t.totalUniversities }}</p>
        <strong>{{ universities.length }}</strong>
      </article>
      <article class="card summary-card">
        <p>{{ t.pendingRequests }}</p>
        <strong>{{ pendingUniversities.length }}</strong>
      </article>
      <article class="card summary-card">
        <p>{{ t.approvedUniversities }}</p>
        <strong>{{ approvedUniversities.length }}</strong>
      </article>
      <article class="card summary-card">
        <p>{{ t.rejectedUniversities }}</p>
        <strong>{{ rejectedUniversities.length }}</strong>
      </article>
    </section>

    <section class="card panel" v-if="pendingUniversities.length">
      <div class="panel-head">
        <h2>{{ t.pendingReview }}</h2>
      </div>
      <div class="request-list">
        <article
          v-for="university in pendingUniversities"
          :key="university.id"
          class="request-card"
        >
          <div class="request-main">
            <h3>{{ university.name }}</h3>
            <p>{{ university.email || '-' }} · {{ university.country || '-' }}</p>

            <div class="detail-grid">
              <div v-if="university.type"><strong>Type:</strong> {{ university.type }}</div>
              <div v-if="university.phone"><strong>Phone:</strong> {{ university.phone }}</div>
              <div v-if="university.websiteUrl">
                <strong>Website:</strong> {{ university.websiteUrl }}
              </div>
              <div v-if="university.registrationNumber">
                <strong>Registration:</strong> {{ university.registrationNumber }}
              </div>
              <div v-if="university.accreditationNumber">
                <strong>Accreditation:</strong> {{ university.accreditationNumber }}
              </div>
              <div v-if="university.description">
                <strong>Description:</strong> {{ university.description }}
              </div>
            </div>

            <div class="chips">
              <span
                class="status-chip"
                :class="checkClass(university.verification.accreditationId)"
              >
                {{
                  university.verification.accreditationId
                    ? t.accreditationOk
                    : t.accreditationMissing
                }}
              </span>
              <span class="status-chip" :class="checkClass(university.verification.domainMatches)">
                {{ university.verification.domainMatches ? t.domainOk : t.domainMismatch }}
              </span>
              <span
                class="status-chip"
                :class="checkClass(university.verification.documentsComplete)"
              >
                {{ university.verification.documentsComplete ? t.documentsOk : t.documentsMissing }}
              </span>
              <span class="status-chip" :class="checkClass(university.email)">
                {{ university.email ? 'Email present' : 'Email missing' }}
              </span>
            </div>
          </div>

          <div class="request-actions">
            <button
              type="button"
              class="primary-btn"
              :disabled="!canApproveUniversity(university)"
              @click="approve(university.id)"
            >
              {{ t.approve }}
            </button>

            <textarea
              v-model.trim="rejectReasons[university.id]"
              :placeholder="t.rejectReasonPlaceholder"
              rows="2"
            ></textarea>
            <button type="button" class="secondary-btn" @click="reject(university.id)">
              {{ t.reject }}
            </button>

            <p v-if="actionMessage[university.id]" class="action-message">
              {{ actionMessage[university.id] }}
            </p>
          </div>
        </article>
      </div>
    </section>

    <section class="card table-card">
      <div class="panel-head">
        <h2>{{ t.allUniversities }}</h2>
      </div>
      <table>
        <thead>
          <tr>
            <th>{{ t.university }}</th>
            <th>{{ t.email }}</th>
            <th>{{ t.country }}</th>
            <th>{{ t.status }}</th>
            <th>{{ t.actions }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="university in universities" :key="university.id">
            <td>
              <strong>{{ university.name }}</strong>
            </td>
            <td>{{ university.email || '-' }}</td>
            <td>{{ university.country || '-' }}</td>
            <td>
              <span class="status-chip" :class="statusClass(university.status)">
                {{ statusLabel(university.status) }}
              </span>
            </td>
            <td>
              <RouterLink
                class="text-btn"
                :to="{ name: 'adminUniversityStudents', params: { id: university.id } }"
              >
                {{ t.viewStudents }}
              </RouterLink>
            </td>
          </tr>
        </tbody>
      </table>
    </section>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive } from 'vue'
import { RouterLink } from 'vue-router'
import { useUiPreferences } from '@/compasables/useUiPreferences'
import { useUniversityAdmin } from '@/compasables/useUniversityAdmin'

const {
  universities,
  approvedUniversities,
  pendingUniversities,
  rejectedUniversities,
  canApproveUniversity,
  approveUniversity,
  rejectUniversity,
  fetchUniversities,
} = useUniversityAdmin()

onMounted(() => {
  fetchUniversities().catch(() => {})
})

const { locale } = useUiPreferences()

const rejectReasons = reactive({})
const actionMessage = reactive({})

const translations = {
  en: {
    title: 'University Governance',
    subtitle: 'University Accounts',
    hint: 'Review new requests, verify details, and approve or reject account creation.',
    totalUniversities: 'Total Universities',
    pendingRequests: 'Pending Requests',
    approvedUniversities: 'Approved',
    rejectedUniversities: 'Rejected',
    pendingReview: 'Pending Verification Review',
    accreditationOk: 'Accreditation ID valid',
    accreditationMissing: 'Accreditation missing',
    domainOk: 'Domain verified',
    domainMismatch: 'Domain mismatch',
    documentsOk: 'Documents complete',
    documentsMissing: 'Documents incomplete',
    approve: 'Approve Request',
    reject: 'Reject Request',
    rejectReasonPlaceholder: 'Reason if data looks fake or invalid...',
    cannotApprove: 'Cannot approve: verification checks failed.',
    approvedDone: 'University approved successfully.',
    rejectedDone: 'Request rejected.',
    allUniversities: 'All Universities',
    university: 'University',
    email: 'Email',
    country: 'Country',
    status: 'Status',
    actions: 'Actions',
    viewStudents: 'View Students',
    statusApproved: 'Approved',
    statusPending: 'Pending',
    statusRejected: 'Rejected',
  },
  fr: {
    title: 'Gouvernance Universitaire',
    subtitle: 'Comptes Universites',
    hint: 'Verifiez les nouvelles demandes, puis approuvez ou refusez la creation du compte.',
    totalUniversities: 'Total Universites',
    pendingRequests: 'Demandes en attente',
    approvedUniversities: 'Approuvees',
    rejectedUniversities: 'Refusees',
    pendingReview: 'Demandes en attente de verification',
    accreditationOk: "ID d'accreditation valide",
    accreditationMissing: "ID d'accreditation manquant",
    domainOk: 'Domaine verifie',
    domainMismatch: 'Domaine non conforme',
    documentsOk: 'Documents complets',
    documentsMissing: 'Documents incomplets',
    approve: 'Approuver la demande',
    reject: 'Refuser la demande',
    rejectReasonPlaceholder: 'Motif si les donnees semblent fausses...',
    cannotApprove: "Impossible d'approuver : verification invalide.",
    approvedDone: 'Universite approuvee avec succes.',
    rejectedDone: 'Demande refusee.',
    allUniversities: 'Toutes les universites',
    university: 'Universite',
    email: 'Email',
    country: 'Pays',
    status: 'Statut',
    actions: 'Actions',
    viewStudents: 'Voir les etudiants',
    statusApproved: 'Approuve',
    statusPending: 'En attente',
    statusRejected: 'Refuse',
  },
}

const t = computed(() => translations[locale.value] || translations.en)

const checkClass = (ok) => (ok ? 'active' : 'needsAttention')

const statusClass = (status) => {
  if (status === 'approved') return 'active'
  if (status === 'rejected') return 'needsAttention'
  return 'inactive'
}

const statusLabel = (status) => {
  if (status === 'approved') return t.value.statusApproved
  if (status === 'rejected') return t.value.statusRejected
  return t.value.statusPending
}

const approve = async (id) => {
  const university = universities.value.find((item) => item.id === id)
  if (university && !canApproveUniversity(university)) {
    actionMessage[id] = t.value.cannotApprove
    return
  }

  const result = await approveUniversity(id)
  actionMessage[id] = result.ok ? t.value.approvedDone : t.value.cannotApprove
}

const reject = async (id) => {
  const reason = rejectReasons[id] || ''
  const result = await rejectUniversity(id, reason)
  if (result.ok) {
    actionMessage[id] = t.value.rejectedDone
  }
}
</script>

<style scoped>
.universities-page {
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

.detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 8px 14px;
  margin: 12px 0;
  font-size: 0.88rem;
  color: rgba(255, 255, 255, 0.84);
}

.detail-grid strong {
  color: #fff;
}

.request-list {
  display: grid;
  gap: 10px;
}

.request-card {
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 12px;
  display: grid;
  grid-template-columns: 1.2fr 1fr;
  gap: 12px;
}

.request-main p {
  margin: 6px 0 0;
  color: var(--muted);
}

.chips {
  margin-top: 10px;
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.request-actions {
  display: grid;
  gap: 8px;
}

.request-actions textarea {
  width: 100%;
  resize: vertical;
}

.action-message {
  margin: 0;
  color: var(--muted);
  font-size: 0.84rem;
}

.table-card {
  overflow-x: auto;
}

@media (max-width: 900px) {
  .request-card {
    grid-template-columns: 1fr;
  }
}
</style>
