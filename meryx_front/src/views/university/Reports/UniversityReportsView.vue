<template>
  <div class="reports-page">
    <header class="page-header">
      <div>
        <p class="eyebrow">{{ t.eyebrow }}</p>
        <h1>{{ t.title }}</h1>
        <p class="intro">
          {{ t.intro }}
        </p>
      </div>

      <div class="period-switch" role="tablist" :aria-label="t.reportPeriod">
        <button
          v-for="period in periods"
          :key="period.key"
          type="button"
          class="period-btn"
          :class="{ active: selectedPeriod === period.key }"
          @click="selectedPeriod = period.key"
        >
          {{ period.label }}
        </button>
      </div>
    </header>

    <section class="stats-grid">
      <article class="stat-card">
        <span>{{ t.totalStudents }}</span>
        <strong>{{ currentData.totalStudents }}</strong>
      </article>
      <article class="stat-card">
        <span>{{ t.internshipPlacement }}</span>
        <strong>{{ currentData.placementRate }}%</strong>
      </article>
      <article class="stat-card">
        <span>{{ t.activeCompanies }}</span>
        <strong>{{ currentData.activeCompanies }}</strong>
      </article>
      <article class="stat-card">
        <span>{{ t.averageGpa }}</span>
        <strong>{{ currentData.avgGpa }}</strong>
      </article>
    </section>

    <p v-if="isLoading" class="info-line">{{ t.loading }}</p>
    <p v-else-if="loadError" class="error-line">{{ loadError }}</p>

    <section class="charts-grid">
      <article class="chart-card">
        <div class="card-head">
          <h2>{{ t.studentsPerFaculty }}</h2>
          <span>{{ selectedPeriodLabel }}</span>
        </div>
        <div class="chart-wrap">
          <Bar :data="facultyBarData" :options="baseChartOptions" />
        </div>
      </article>

      <article class="chart-card">
        <div class="card-head">
          <h2>{{ t.placementsPerMonth }}</h2>
          <span>{{ selectedPeriodLabel }}</span>
        </div>
        <div class="chart-wrap">
          <Bar :data="placementBarData" :options="baseChartOptions" />
        </div>
      </article>
    </section>

    <section class="charts-grid single">
      <article class="chart-card">
        <div class="card-head">
          <h2>{{ t.partnerEngagement }}</h2>
          <span>{{ t.publishedAndOpen }}</span>
        </div>
        <div class="chart-wrap">
          <Bar :data="engagementBarData" :options="stackedChartOptions" />
        </div>
      </article>
    </section>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { Bar } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
} from 'chart.js'
import { useUiPreferences } from '@/compasables/useUiPreferences'
import { useAuthStore } from '@/stores/auth.store'
import { useStudentStore } from '@/stores/student.store'
import { useCompanyAdmin } from '@/compasables/useCompanyAdmin'
import { useCompanyOpportunities } from '@/compasables/useCompanyOpportunities'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

const { locale } = useUiPreferences()

const translations = {
  en: {
    eyebrow: 'University / Reports',
    title: 'Performance Reports',
    intro:
      'Track student outcomes, internship placement, and partner-company activity in one analytics workspace.',
    reportPeriod: 'Report period',
    periods: {
      thisSemester: 'This Semester',
      lastSemester: 'Last Semester',
      year: 'Year',
    },
    totalStudents: 'Total Students',
    internshipPlacement: 'Internship Placement',
    activeCompanies: 'Active Companies',
    averageGpa: 'Average GPA',
    loading: 'Loading report data...',
    studentsPerFaculty: 'Students Per Faculty',
    placementsPerMonth: 'Internship Placements Per Month',
    partnerEngagement: 'Partner Company Engagement',
    publishedAndOpen: 'Published + Open Opportunities',
    studentsSeries: 'Students',
    placedSeries: 'Placed Students',
    publishedSeries: 'Published Opportunities',
    openSeries: 'Open Opportunities',
  },
  fr: {
    eyebrow: 'Universite / Rapports',
    title: 'Rapports de performance',
    intro:
      "Suivez les resultats etudiants, le placement en stage et l'activite des entreprises partenaires dans un seul espace analytique.",
    reportPeriod: 'Periode du rapport',
    periods: {
      thisSemester: 'Ce semestre',
      lastSemester: 'Semestre precedent',
      year: 'Annee',
    },
    totalStudents: 'Total etudiants',
    internshipPlacement: 'Placement en stage',
    activeCompanies: 'Entreprises actives',
    averageGpa: 'Moyenne GPA',
    loading: 'Chargement des donnees du rapport...',
    studentsPerFaculty: 'Etudiants par faculte',
    placementsPerMonth: 'Placements en stage par mois',
    partnerEngagement: 'Engagement des entreprises partenaires',
    publishedAndOpen: 'Publiees + opportunites ouvertes',
    studentsSeries: 'Etudiants',
    placedSeries: 'Etudiants places',
    publishedSeries: 'Opportunites publiees',
    openSeries: 'Opportunites ouvertes',
  },
}

const t = computed(() => translations[locale.value] || translations.en)

const periods = computed(() => [
  { key: 'thisSemester', label: t.value.periods.thisSemester },
  { key: 'lastSemester', label: t.value.periods.lastSemester },
  { key: 'year', label: t.value.periods.year },
])
const selectedPeriod = ref('thisSemester')
const selectedPeriodLabel = computed(() => {
  const period = periods.value.find((item) => item.key === selectedPeriod.value)
  return period?.label || t.value.periods.thisSemester
})

const authStore = useAuthStore()
const studentStore = useStudentStore()
const { companies, fetchCompanies, isLoadingCompanies, companyAdminError } = useCompanyAdmin()
const { opportunities, fetchOpportunities, isLoadingOpportunities, opportunityError } =
  useCompanyOpportunities()

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

const safeDate = (value) => {
  if (!value) return null
  const parsed = new Date(value)
  return Number.isNaN(parsed.getTime()) ? null : parsed
}

const periodRange = computed(() => {
  const now = new Date()
  const month = now.getMonth()
  const semesterStartMonth = month < 6 ? 0 : 6
  const thisSemesterStart = new Date(now.getFullYear(), semesterStartMonth, 1)

  if (selectedPeriod.value === 'thisSemester') {
    return { start: thisSemesterStart, end: now }
  }

  if (selectedPeriod.value === 'lastSemester') {
    const lastSemesterEnd = new Date(thisSemesterStart)
    lastSemesterEnd.setDate(lastSemesterEnd.getDate() - 1)
    const lastSemesterStart = new Date(thisSemesterStart)
    lastSemesterStart.setMonth(lastSemesterStart.getMonth() - 6)
    return { start: lastSemesterStart, end: lastSemesterEnd }
  }

  const yearStart = new Date(now)
  yearStart.setFullYear(now.getFullYear() - 1)
  return { start: yearStart, end: now }
})

const isInRange = (dateValue) => {
  const date = safeDate(dateValue)
  if (!date) return true

  const { start, end } = periodRange.value
  return date >= start && date <= end
}

const periodStudents = computed(() =>
  (studentStore.students || []).filter((student) =>
    isInRange(student.updatedAt || student.createdAt),
  ),
)

const periodCompanies = computed(() =>
  (companies.value || []).filter((company) =>
    isInRange(company.createdAt || company.raw?.createdAt),
  ),
)

const periodOpportunities = computed(() =>
  (opportunities.value || []).filter((item) =>
    isInRange(item.raw?.publishedAt || item.raw?.createdAt || item.deadLine),
  ),
)

const parseGpa = (value) => {
  const text = String(value || '').trim()
  if (!text) return null

  const slashMatch = text.match(/^([0-9]+(?:[.,][0-9]+)?)\s*\/\s*([0-9]+(?:[.,][0-9]+)?)$/)
  if (slashMatch) {
    const score = Number.parseFloat(slashMatch[1].replace(',', '.'))
    const base = Number.parseFloat(slashMatch[2].replace(',', '.'))
    if (Number.isFinite(score) && Number.isFinite(base) && base > 0) {
      return (score / base) * 4
    }
  }

  const raw = Number.parseFloat(text.replace(',', '.'))
  if (!Number.isFinite(raw)) return null
  if (raw > 5) return raw / 4
  return raw
}

const isPlacedStudent = (student) => {
  if (Number(student.applicationCount || 0) > 0) return true
  if (Array.isArray(student.projects) && student.projects.length > 0) return true
  return false
}

const monthLabels = computed(() => {
  const { start, end } = periodRange.value
  const labels = []
  const cursor = new Date(start.getFullYear(), start.getMonth(), 1)
  const endCursor = new Date(end.getFullYear(), end.getMonth(), 1)

  while (cursor <= endCursor) {
    labels.push(
      cursor.toLocaleDateString(locale.value === 'fr' ? 'fr-FR' : 'en-GB', {
        month: 'short',
      }),
    )
    cursor.setMonth(cursor.getMonth() + 1)
  }

  return labels
})

const facultyAggregation = computed(() => {
  const bucket = new Map()
  for (const student of periodStudents.value) {
    const faculty = student.faculty || student.program || 'Unassigned'
    bucket.set(faculty, (bucket.get(faculty) || 0) + 1)
  }

  const rows = [...bucket.entries()].sort((a, b) => b[1] - a[1]).slice(0, 8)
  return {
    labels: rows.map((entry) => entry[0]),
    values: rows.map((entry) => entry[1]),
  }
})

const placementAggregation = computed(() => {
  const counts = new Map(monthLabels.value.map((label) => [label, 0]))

  for (const student of periodStudents.value) {
    if (!isPlacedStudent(student)) continue
    const date = safeDate(student.updatedAt || student.createdAt)
    if (!date) continue
    const label = date.toLocaleDateString(locale.value === 'fr' ? 'fr-FR' : 'en-GB', {
      month: 'short',
    })
    if (counts.has(label)) {
      counts.set(label, counts.get(label) + 1)
    }
  }

  return {
    labels: monthLabels.value,
    values: monthLabels.value.map((label) => counts.get(label) || 0),
  }
})

const companyNameById = computed(() => {
  const map = new Map()
  for (const company of companies.value || []) {
    map.set(Number(company.id), company.name || `Company ${company.id}`)
  }
  return map
})

const parseCompanyId = (value) => {
  if (!value && value !== 0) return 0
  if (typeof value === 'number') return value
  if (typeof value === 'string') {
    const match = value.trim().match(/(?:\/)?(\d+)$/)
    return match ? Number(match[1]) : 0
  }
  if (typeof value === 'object') {
    return parseCompanyId(value.id || value.companyId || value.company?.id)
  }
  return 0
}

const engagementAggregation = computed(() => {
  const buckets = new Map()

  for (const item of periodOpportunities.value) {
    const companyId = parseCompanyId(item.companyId)
    const companyName =
      companyNameById.value.get(companyId) || item.raw?.companyId?.name || 'Unknown'

    if (!buckets.has(companyName)) {
      buckets.set(companyName, { published: 0, open: 0 })
    }

    const row = buckets.get(companyName)
    row.published += 1

    const status = String(item.status || '').toLowerCase()
    if (status.includes('open') || status.includes('active') || !status) {
      row.open += 1
    }
  }

  const top = [...buckets.entries()].sort((a, b) => b[1].published - a[1].published).slice(0, 8)

  return {
    labels: top.map((entry) => entry[0]),
    published: top.map((entry) => entry[1].published),
    open: top.map((entry) => entry[1].open),
  }
})

const averageGpa = computed(() => {
  const gpas = periodStudents.value
    .map((student) => parseGpa(student.gpa))
    .filter((value) => value !== null)
  if (!gpas.length) return '0.00'
  return (gpas.reduce((sum, value) => sum + value, 0) / gpas.length).toFixed(2)
})

const placementRate = computed(() => {
  if (!periodStudents.value.length) return 0
  const placed = periodStudents.value.filter(isPlacedStudent).length
  return Math.round((placed / periodStudents.value.length) * 100)
})

const activeCompanies = computed(
  () =>
    periodCompanies.value.filter(
      (company) => String(company.status || '').toLowerCase() === 'approved',
    ).length,
)

const currentData = computed(() => ({
  totalStudents: periodStudents.value.length,
  placementRate: placementRate.value,
  activeCompanies: activeCompanies.value,
  avgGpa: averageGpa.value,
}))

const isLoading = computed(
  () => isLoadingCompanies.value || isLoadingOpportunities.value || studentStore.loading,
)

const loadError = computed(() => {
  const studentError = studentStore.error?.message || ''
  return companyAdminError.value || opportunityError.value || studentError || ''
})

const facultyBarData = computed(() => ({
  labels: facultyAggregation.value.labels,
  datasets: [
    {
      label: t.value.studentsSeries,
      data: facultyAggregation.value.values,
      backgroundColor: '#d4a017',
      borderRadius: 8,
      maxBarThickness: 44,
    },
  ],
}))

const placementBarData = computed(() => ({
  labels: placementAggregation.value.labels,
  datasets: [
    {
      label: t.value.placedSeries,
      data: placementAggregation.value.values,
      backgroundColor: '#0f766e',
      borderRadius: 8,
      maxBarThickness: 44,
    },
  ],
}))

const engagementBarData = computed(() => ({
  labels: engagementAggregation.value.labels,
  datasets: [
    {
      label: t.value.publishedSeries,
      data: engagementAggregation.value.published,
      backgroundColor: '#0891b2',
      borderRadius: 7,
      maxBarThickness: 42,
    },
    {
      label: t.value.openSeries,
      data: engagementAggregation.value.open,
      backgroundColor: '#14b8a6',
      borderRadius: 7,
      maxBarThickness: 42,
    },
  ],
}))

const baseChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false,
    },
    tooltip: {
      backgroundColor: 'rgba(15, 23, 42, 0.92)',
      titleColor: '#f8fafc',
      bodyColor: '#e2e8f0',
      cornerRadius: 10,
      padding: 10,
    },
  },
  scales: {
    x: {
      grid: {
        display: false,
      },
      ticks: {
        color: '#64748b',
      },
    },
    y: {
      beginAtZero: true,
      ticks: {
        color: '#64748b',
      },
      grid: {
        color: 'rgba(148, 163, 184, 0.18)',
      },
    },
  },
}

const stackedChartOptions = {
  ...baseChartOptions,
  plugins: {
    ...baseChartOptions.plugins,
    legend: {
      display: true,
      position: 'top',
      labels: {
        color: '#334155',
        boxWidth: 14,
        boxHeight: 14,
        useBorderRadius: true,
        borderRadius: 4,
      },
    },
  },
  scales: {
    x: {
      ...baseChartOptions.scales.x,
      stacked: true,
    },
    y: {
      ...baseChartOptions.scales.y,
      stacked: true,
    },
  },
}

onMounted(async () => {
  const universityId = connectedUniversityId.value
  const studentPromise = universityId
    ? studentStore.fetchStudentsByUniversity(universityId)
    : studentStore.fetchStudents()

  await Promise.all([
    studentPromise.catch(() => {}),
    fetchCompanies().catch(() => {}),
    fetchOpportunities().catch(() => {}),
  ])
})
</script>

<style scoped>
.reports-page {
  display: grid;
  gap: 18px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: flex-start;
}

.eyebrow {
  margin: 0 0 6px;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  color: var(--primary);
  font-size: 0.78rem;
  font-weight: 700;
}

h1,
h2 {
  margin: 0;
  color: var(--text);
}

.intro {
  margin: 10px 0 0;
  max-width: 760px;
  color: var(--muted);
}

.info-line,
.error-line {
  margin: 0;
  color: var(--muted);
}

.error-line {
  color: #b91c1c;
}

.period-switch {
  display: inline-flex;
  flex-wrap: wrap;
  gap: 8px;
}

.period-btn {
  border: 1px solid var(--border);
  background: var(--surface);
  color: var(--text);
  border-radius: 12px;
  padding: 10px 14px;
  cursor: pointer;
  font-weight: 600;
}

.period-btn.active {
  background: var(--primary);
  border-color: var(--primary);
  color: #fff;
}

.stats-grid {
  display: grid;
  gap: 12px;
  grid-template-columns: repeat(4, minmax(0, 1fr));
}

.stat-card,
.chart-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 20px;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
}

.stat-card {
  padding: 18px;
}

.stat-card span {
  color: var(--muted);
  font-size: 0.84rem;
}

.stat-card strong {
  display: block;
  margin-top: 8px;
  color: var(--text);
  font-size: 1.8rem;
}

.charts-grid {
  display: grid;
  gap: 14px;
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.charts-grid.single {
  grid-template-columns: 1fr;
}

.chart-card {
  padding: 16px;
  min-width: 0;
}

.card-head {
  display: flex;
  justify-content: space-between;
  gap: 10px;
  align-items: center;
  margin-bottom: 12px;
}

.card-head span {
  color: var(--muted);
  font-size: 0.84rem;
}

.chart-wrap {
  height: 330px;
}

@media (max-width: 1080px) {
  .stats-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .charts-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 720px) {
  .page-header {
    flex-direction: column;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .period-switch,
  .period-btn {
    width: 100%;
  }

  .period-switch {
    display: grid;
    grid-template-columns: 1fr;
  }

  .chart-wrap {
    height: 290px;
  }
}
</style>
