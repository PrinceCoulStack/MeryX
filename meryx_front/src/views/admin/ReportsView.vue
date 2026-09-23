<template>
  <div class="reports-page">
    <header class="card page-header">
      <div>
        <p class="kicker">{{ t.analytics }}</p>
        <h1>{{ t.title }}</h1>
        <p class="hint">{{ t.hint }}</p>
      </div>
      <span class="status-chip active">{{ t.liveSnapshot }}</span>
    </header>

    <section class="kpi-grid" v-if="!isLoading">
      <article class="card kpi-card">
        <p>{{ t.totalStudents }}</p>
        <strong>{{ students.length }}</strong>
      </article>
      <article class="card kpi-card">
        <p>{{ t.activeStudents }}</p>
        <strong>{{ activeStudents }}</strong>
      </article>
      <article class="card kpi-card">
        <p>{{ t.pendingApprovals }}</p>
        <strong>{{ pendingUniversities.length + pendingCompanies.length }}</strong>
      </article>
      <article class="card kpi-card">
        <p>{{ t.totalPosts }}</p>
        <strong>{{ trainingPosts.length + opportunityPosts.length }}</strong>
      </article>
    </section>
    <section v-else class="card panel status-panel">{{ t.loading }}</section>
    <section v-if="loadError" class="card panel status-panel error">{{ loadError }}</section>

    <section class="card panel">
      <div class="panel-head">
        <h2>{{ t.engagementHighlights }}</h2>
      </div>
      <div class="highlights-grid">
        <article class="highlight-card">
          <p class="small-label">{{ t.mostActiveStudent }}</p>
          <h3>{{ mostActiveStudent?.fullName || '-' }}</h3>
          <p class="small-meta">
            {{ mostActiveStudent?.university || '-' }} ·
            {{ mostActiveStudent?.usage.sessionsLast30Days || 0 }}
            {{ t.sessions30d }}
          </p>
        </article>
        <article class="highlight-card">
          <p class="small-label">{{ t.avgStudentActions }}</p>
          <h3>{{ averageStudentActions }}</h3>
          <p class="small-meta">{{ t.actionsPerStudent30d }}</p>
        </article>
      </div>
    </section>

    <section class="card panel">
      <div class="panel-head">
        <h2>{{ t.pendingVerificationTitle }}</h2>
      </div>
      <table>
        <thead>
          <tr>
            <th>{{ t.entity }}</th>
            <th>{{ t.type }}</th>
            <th>{{ t.email }}</th>
            <th>{{ t.risk }}</th>
            <th>{{ t.createdAt }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!pendingVerificationRows.length">
            <td colspan="5">{{ t.noPendingItems }}</td>
          </tr>
          <template v-else>
            <tr v-for="item in pendingVerificationRows" :key="item.id">
              <td>
                <strong>{{ item.name }}</strong>
              </td>
              <td>{{ item.type }}</td>
              <td>{{ item.email }}</td>
              <td>
                <span
                  class="status-chip"
                  :class="item.risk === t.lowRisk ? 'active' : 'needsAttention'"
                >
                  {{ item.risk }}
                </span>
              </td>
              <td>{{ item.createdAt }}</td>
            </tr>
          </template>
        </tbody>
      </table>
    </section>

    <section class="card panel">
      <div class="panel-head">
        <h2>{{ t.contentPerformance }}</h2>
      </div>
      <div class="content-grid">
        <article class="content-item">
          <p class="small-label">{{ t.trainingPosts }}</p>
          <h3>{{ trainingPosts.length }}</h3>
        </article>
        <article class="content-item">
          <p class="small-label">{{ t.jobInternshipPosts }}</p>
          <h3>{{ opportunityPosts.length }}</h3>
        </article>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useUiPreferences } from '@/compasables/useUiPreferences'
import { useStudentAdmin } from '@/compasables/useStudentAdmin'
import { useUniversityAdmin } from '@/compasables/useUniversityAdmin'
import { useCompanyAdmin } from '@/compasables/useCompanyAdmin'

const { locale } = useUiPreferences()
const {
  students,
  mostActiveStudent,
  loadStudents,
  loading: loadingStudents,
  storeLoading,
} = useStudentAdmin()
const { pendingUniversities, fetchUniversities, isLoadingUniversities, universityAdminError } =
  useUniversityAdmin()
const {
  pendingCompanies,
  trainingPosts,
  opportunityPosts,
  canApproveCompany,
  fetchCompanies,
  isLoadingCompanies,
  companyAdminError,
} = useCompanyAdmin()
const localLoading = ref(false)

const translations = {
  en: {
    analytics: 'Analytics',
    title: 'Admin Reports',
    hint: 'Cross-platform reporting for users, approval flow, and content activity.',
    liveSnapshot: 'Live Snapshot',
    totalStudents: 'Total Students',
    activeStudents: 'Active Students',
    pendingApprovals: 'Pending Approvals',
    totalPosts: 'Total Posts',
    engagementHighlights: 'Engagement Highlights',
    mostActiveStudent: 'Most Active Student',
    sessions30d: 'sessions / 30d',
    avgStudentActions: 'Average Student Actions',
    actionsPerStudent30d: 'actions per student in last 30 days',
    pendingVerificationTitle: 'Pending Verification Queue',
    entity: 'Entity',
    type: 'Type',
    email: 'Email',
    risk: 'Risk',
    createdAt: 'Created At',
    lowRisk: 'Low risk',
    reviewRequired: 'Review required',
    university: 'University',
    company: 'Company',
    contentPerformance: 'Content Performance',
    trainingPosts: 'Training Posts',
    jobInternshipPosts: 'Job/Internship Posts',
    loading: 'Loading report data...',
    noPendingItems: 'No pending verification items.',
  },
  fr: {
    analytics: 'Analytique',
    title: 'Rapports Admin',
    hint: 'Reporting transversal sur les utilisateurs, le flux de validation et les contenus publies.',
    liveSnapshot: 'Instantane en direct',
    totalStudents: 'Total etudiants',
    activeStudents: 'Etudiants actifs',
    pendingApprovals: 'Validations en attente',
    totalPosts: 'Total publications',
    engagementHighlights: "Faits marquants d'engagement",
    mostActiveStudent: 'Etudiant le plus actif',
    sessions30d: 'sessions / 30j',
    avgStudentActions: 'Moyenne actions etudiants',
    actionsPerStudent30d: 'actions par etudiant sur 30 jours',
    pendingVerificationTitle: 'File de verification en attente',
    entity: 'Entite',
    type: 'Type',
    email: 'Email',
    risk: 'Risque',
    createdAt: 'Date de creation',
    lowRisk: 'Risque faible',
    reviewRequired: 'Controle requis',
    university: 'Universite',
    company: 'Entreprise',
    contentPerformance: 'Performance des contenus',
    trainingPosts: 'Publications de formation',
    jobInternshipPosts: 'Publications emploi/stage',
    loading: 'Chargement des donnees du rapport...',
    noPendingItems: 'Aucun element en attente de verification.',
  },
}

const t = computed(() => translations[locale.value] || translations.en)
const isLoading = computed(
  () =>
    localLoading.value ||
    loadingStudents.value ||
    storeLoading.value ||
    isLoadingUniversities.value ||
    isLoadingCompanies.value,
)
const loadError = computed(() => universityAdminError.value || companyAdminError.value || '')

const activeStudents = computed(
  () => students.value.filter((student) => student.status === 'active').length,
)

const averageStudentActions = computed(() => {
  if (!students.value.length) return 0
  const total = students.value.reduce((sum, student) => sum + student.usage.actionsLast30Days, 0)
  return Math.round(total / students.value.length)
})

const pendingVerificationRows = computed(() => {
  const universityRows = pendingUniversities.value.map((university) => ({
    id: `u-${university.id}`,
    name: university.name,
    type: t.value.university,
    email: university.email,
    risk:
      university.verification.domainMatches && university.verification.documentsComplete
        ? t.value.lowRisk
        : t.value.reviewRequired,
    createdAt: university.createdAt,
  }))

  const companyRows = pendingCompanies.value.map((company) => ({
    id: `c-${company.id}`,
    name: company.name,
    type: t.value.company,
    email: company.email,
    risk: canApproveCompany(company) ? t.value.lowRisk : t.value.reviewRequired,
    createdAt: company.createdAt,
  }))

  return [...universityRows, ...companyRows]
})

onMounted(async () => {
  localLoading.value = true
  try {
    await Promise.all([loadStudents(), fetchUniversities(), fetchCompanies()])
  } finally {
    localLoading.value = false
  }
})
</script>

<style scoped>
.reports-page {
  display: grid;
  gap: 16px;
}

.page-header,
.panel {
  padding: 16px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
}

.kicker,
.hint,
.kpi-card p,
.small-label,
.small-meta {
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

.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 12px;
}

.kpi-card {
  padding: 14px;
}

.kpi-card p {
  color: var(--muted);
  font-size: 0.82rem;
}

.kpi-card strong {
  display: block;
  margin-top: 6px;
  font-size: 1.5rem;
}

.panel-head {
  margin-bottom: 10px;
}

.highlights-grid,
.content-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 10px;
}

.highlight-card,
.content-item {
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 12px;
}

.small-label {
  color: var(--muted);
  font-size: 0.8rem;
}

.small-meta {
  margin-top: 6px;
  color: var(--muted);
}

.panel {
  overflow-x: auto;
}

.status-panel {
  text-align: center;
  color: var(--muted);
}

.status-panel.error {
  color: #b42318;
}

@media (max-width: 760px) {
  .page-header {
    flex-direction: column;
  }
}
</style>
