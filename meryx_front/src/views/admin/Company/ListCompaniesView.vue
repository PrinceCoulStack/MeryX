<template>
  <div class="companies-page">
    <header class="card page-header">
      <div>
        <p class="kicker">{{ t.companyGovernance }}</p>
        <h1>{{ t.companyAccounts }}</h1>
        <p class="hint">{{ t.hint }}</p>
      </div>
      <div class="header-actions">
        <RouterLink class="topbar-pill" :to="{ name: 'adminAllTrainings' }">{{
          t.allTrainings
        }}</RouterLink>
        <RouterLink class="topbar-pill" :to="{ name: 'adminAllOpportunities' }">{{
          t.allOpportunities
        }}</RouterLink>
      </div>
    </header>

    <section class="summary-grid">
      <article class="card summary-card">
        <p>{{ t.totalCompanies }}</p>
        <strong>{{ companies.length }}</strong>
      </article>
      <article class="card summary-card">
        <p>{{ t.pendingRequests }}</p>
        <strong>{{ pendingCompanies.length }}</strong>
      </article>
      <article class="card summary-card">
        <p>{{ t.approvedCompanies }}</p>
        <strong>{{ approvedCompanies.length }}</strong>
      </article>
      <article class="card summary-card">
        <p>{{ t.rejectedCompanies }}</p>
        <strong>{{ rejectedCompanies.length }}</strong>
      </article>
    </section>

    <section class="card panel" v-if="pendingCompanies.length">
      <div class="panel-head">
        <h2>{{ t.pendingReview }}</h2>
      </div>

      <div class="request-list">
        <article v-for="company in pendingCompanies" :key="company.id" class="request-card">
          <div class="request-main">
            <h3>{{ company.name }}</h3>
            <p>{{ company.email || '-' }} · {{ company.country || '-' }}</p>

            <div class="detail-grid">
              <div v-if="company.sector"><strong>Sector:</strong> {{ company.sector }}</div>
              <div v-if="company.phone"><strong>Phone:</strong> {{ company.phone }}</div>
              <div v-if="company.websiteUrl">
                <strong>Website:</strong> {{ company.websiteUrl }}
              </div>
              <div v-if="company.registrationNumber">
                <strong>Registration:</strong> {{ company.registrationNumber }}
              </div>
              <div v-if="company.taxId"><strong>Tax ID:</strong> {{ company.taxId }}</div>
              <div v-if="company.description">
                <strong>Description:</strong> {{ company.description }}
              </div>
            </div>

            <div class="chips">
              <span
                class="status-chip"
                :class="checkClass(company.verification.registrationNumber)"
              >
                {{
                  company.verification.registrationNumber ? t.registrationOk : t.registrationMissing
                }}
              </span>
              <span class="status-chip" :class="checkClass(company.verification.taxId)">
                {{ company.verification.taxId ? t.taxOk : t.taxMissing }}
              </span>
              <span class="status-chip" :class="checkClass(company.verification.domainMatches)">
                {{ company.verification.domainMatches ? t.domainOk : t.domainMismatch }}
              </span>
              <span class="status-chip" :class="checkClass(company.verification.documentsComplete)">
                {{ company.verification.documentsComplete ? t.documentsOk : t.documentsMissing }}
              </span>
              <span class="status-chip" :class="checkClass(company.email)">
                {{ company.email ? 'Email present' : 'Email missing' }}
              </span>
            </div>
          </div>

          <div class="request-actions">
            <button
              type="button"
              class="primary-btn"
              :disabled="!canApproveCompany(company)"
              @click="approve(company.id)"
            >
              {{ t.approve }}
            </button>

            <textarea
              v-model.trim="rejectReasons[company.id]"
              :placeholder="t.rejectReasonPlaceholder"
              rows="2"
            ></textarea>
            <button type="button" class="secondary-btn" @click="reject(company.id)">
              {{ t.reject }}
            </button>

            <p v-if="actionMessage[company.id]" class="action-message">
              {{ actionMessage[company.id] }}
            </p>
          </div>
        </article>
      </div>
    </section>

    <section class="card table-card">
      <div class="panel-head">
        <h2>{{ t.allCompanies }}</h2>
      </div>

      <table>
        <thead>
          <tr>
            <th>{{ t.company }}</th>
            <th>{{ t.email }}</th>
            <th>{{ t.country }}</th>
            <th>{{ t.status }}</th>
            <th>{{ t.actions }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!companies.length">
            <td colspan="5" class="empty-row">{{ t.noCompanies }}</td>
          </tr>
          <tr v-else v-for="company in companies" :key="company.id">
            <td>
              <strong>{{ company.name }}</strong>
            </td>
            <td>{{ company.email }}</td>
            <td>{{ company.country }}</td>
            <td>
              <span class="status-chip" :class="statusClass(company.status)">
                {{ statusLabel(company.status) }}
              </span>
            </td>
            <td>
              <RouterLink
                class="text-btn"
                :to="{ name: 'adminCompanyPosts', params: { id: company.id } }"
              >
                {{ t.viewPosts }}
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
import { useCompanyAdmin } from '@/compasables/useCompanyAdmin'

const {
  companies,
  approvedCompanies,
  pendingCompanies,
  rejectedCompanies,
  canApproveCompany,
  approveCompany,
  rejectCompany,
  fetchCompanies,
} = useCompanyAdmin()

onMounted(() => {
  fetchCompanies().catch(() => {})
})

const { locale } = useUiPreferences()

const rejectReasons = reactive({})
const actionMessage = reactive({})

const translations = {
  en: {
    companyGovernance: 'Company Governance',
    companyAccounts: 'Company Accounts',
    hint: 'Review and validate company registration requests before activation.',
    allTrainings: 'All Trainings',
    allOpportunities: 'All Jobs/Internships',
    totalCompanies: 'Total Companies',
    pendingRequests: 'Pending Requests',
    approvedCompanies: 'Approved',
    rejectedCompanies: 'Rejected',
    noCompanies: 'No companies registered yet.',
    pendingReview: 'Pending Verification Review',
    registrationOk: 'Registration valid',
    registrationMissing: 'Registration missing',
    taxOk: 'Tax ID valid',
    taxMissing: 'Tax ID missing',
    domainOk: 'Domain verified',
    domainMismatch: 'Domain mismatch',
    documentsOk: 'Documents complete',
    documentsMissing: 'Documents incomplete',
    approve: 'Approve Request',
    reject: 'Reject Request',
    rejectReasonPlaceholder: 'Reason if business details look fake or invalid...',
    cannotApprove: 'Cannot approve: verification checks failed.',
    approvedDone: 'Company approved successfully.',
    rejectedDone: 'Request rejected.',
    allCompanies: 'All Companies',
    company: 'Company',
    email: 'Email',
    country: 'Country',
    status: 'Status',
    actions: 'Actions',
    viewPosts: 'View Posts',
    statusApproved: 'Approved',
    statusPending: 'Pending',
    statusRejected: 'Rejected',
  },
  fr: {
    companyGovernance: 'Gouvernance Entreprises',
    companyAccounts: 'Comptes Entreprises',
    hint: "Verifiez et validez les demandes d'inscription des entreprises avant activation.",
    allTrainings: 'Toutes les formations',
    allOpportunities: 'Tous les emplois/stages',
    totalCompanies: 'Total Entreprises',
    pendingRequests: 'Demandes en attente',
    approvedCompanies: 'Approuvees',
    rejectedCompanies: 'Refusees',
    noCompanies: 'Aucune entreprise enregistree pour le moment.',
    pendingReview: 'Demandes en attente de verification',
    registrationOk: 'Immatriculation valide',
    registrationMissing: 'Immatriculation manquante',
    taxOk: 'Identifiant fiscal valide',
    taxMissing: 'Identifiant fiscal manquant',
    domainOk: 'Domaine verifie',
    domainMismatch: 'Domaine non conforme',
    documentsOk: 'Documents complets',
    documentsMissing: 'Documents incomplets',
    approve: 'Approuver la demande',
    reject: 'Refuser la demande',
    rejectReasonPlaceholder: 'Motif si les details semblent faux ou invalides...',
    cannotApprove: "Impossible d'approuver : verification invalide.",
    approvedDone: 'Entreprise approuvee avec succes.',
    rejectedDone: 'Demande refusee.',
    allCompanies: 'Toutes les entreprises',
    company: 'Entreprise',
    email: 'Email',
    country: 'Pays',
    status: 'Statut',
    actions: 'Actions',
    viewPosts: 'Voir les publications',
    statusApproved: 'Approuvee',
    statusPending: 'En attente',
    statusRejected: 'Refusee',
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

const approve = (id) => {
  const company = companies.value.find((item) => item.id === id)
  if (company && !canApproveCompany(company)) {
    actionMessage[id] = t.value.cannotApprove
    return
  }

  const result = approveCompany(id)
  actionMessage[id] = result.ok ? t.value.approvedDone : t.value.cannotApprove
}

const reject = (id) => {
  const reason = rejectReasons[id] || ''
  const result = rejectCompany(id, reason)
  if (result.ok) {
    actionMessage[id] = t.value.rejectedDone
  }
}
</script>

<style scoped>
.companies-page {
  display: grid;
  gap: 16px;
}

.page-header,
.panel,
.table-card {
  padding: 16px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
}

.header-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
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

  .page-header {
    flex-direction: column;
  }
}
</style>
