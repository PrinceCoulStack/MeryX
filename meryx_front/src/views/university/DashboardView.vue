<template>
  <div class="dashboard-v2">
    <section class="hero-card">
      <div class="hero-main">
        <p class="eyebrow">{{ t.heroEyebrow }}</p>
        <h1>{{ t.dashboard }}</h1>
        <p class="hero-subtitle">{{ heroSubtitle }}</p>

        <div class="hero-actions">
          <RouterLink class="primary-btn" to="/university/listStudents">
            <i class="bi bi-people"></i>
            {{ t.viewStudents }}
          </RouterLink>
          <RouterLink class="ghost-btn" to="/university/reports">
            <i class="bi bi-bar-chart-line"></i>
            {{ t.openReports }}
          </RouterLink>
        </div>
      </div>

      <div class="hero-aside">
        <div class="aside-kpi">
          <span>{{ t.placementRate }}</span>
          <strong>{{ dashboardStats.placementRate }}%</strong>
          <small>{{ t.placementHint }}</small>
        </div>
        <div class="aside-kpi">
          <span>{{ t.employmentRate }}</span>
          <strong>{{ dashboardStats.employmentRate }}%</strong>
          <small>{{ t.employmentHint }}</small>
        </div>
      </div>
    </section>

    <section class="metric-grid">
      <article class="metric-card" v-for="item in metricCards" :key="item.key">
        <div class="metric-head">
          <span>{{ item.label }}</span>
          <i :class="item.icon"></i>
        </div>
        <h2>{{ item.value }}</h2>
        <p>
          <strong :class="{ positive: item.trend >= 0, negative: item.trend < 0 }">
            {{ item.trend >= 0 ? '+' : '' }}{{ item.trend }}%
          </strong>
          {{ t.vsLastMonth }}
        </p>
      </article>
    </section>

    <p v-if="isLoading" class="info-line">{{ t.loading }}</p>
    <p v-else-if="loadError" class="error-line">{{ loadError }}</p>

    <section class="analytics-grid">
      <article class="panel-card department-card">
        <div class="panel-head">
          <div>
            <h3>{{ t.studentsByDepartment }}</h3>
            <p>{{ t.departmentBreakdown }}</p>
          </div>
          <span class="panel-tag">{{ t.currentSemester }}</span>
        </div>

        <div class="department-list">
          <div
            class="department-item"
            v-for="dept in departments"
            :key="`${dept.name}-${dept.value}`"
          >
            <div class="item-line">
              <span>{{ dept.name }}</span>
              <small>{{ dept.value }}</small>
            </div>
            <div class="progress-track">
              <div class="progress-fill" :style="{ width: departmentWidth(dept.value) }"></div>
            </div>
          </div>
        </div>
      </article>

      <article class="panel-card chart-card">
        <div class="panel-head">
          <div>
            <h3>{{ t.monthlyPlacements }}</h3>
            <p>{{ t.monthlyPlacementsHint }}</p>
          </div>
        </div>

        <div class="bar-chart">
          <div class="bar-col" v-for="item in monthlyPlacements" :key="item.month">
            <div class="bar-value">{{ item.value }}</div>
            <div class="bar-track">
              <div class="bar-fill" :style="{ height: `${item.height}%` }"></div>
            </div>
            <small>{{ item.month }}</small>
          </div>
        </div>
      </article>
    </section>

    <section class="operations-grid">
      <article class="panel-card activity-card">
        <div class="panel-head">
          <div>
            <h3>{{ t.recentActivities }}</h3>
            <p>{{ t.activityFeed }}</p>
          </div>
        </div>

        <div class="timeline-list">
          <div class="timeline-item" v-for="activity in recentActivities" :key="activity.id">
            <div class="dot"></div>
            <div>
              <strong>{{ activity.title }}</strong>
              <small>{{ activity.time }}</small>
            </div>
          </div>
        </div>
      </article>

      <article class="panel-card plan-card">
        <div class="panel-head">
          <div>
            <h3>{{ t.upcomingAgenda }}</h3>
            <p>{{ t.agendaHint }}</p>
          </div>
        </div>

        <div class="agenda-list">
          <div class="agenda-item" v-for="event in agenda" :key="event.id">
            <div>
              <span>{{ event.title }}</span>
              <small>{{ event.date }}</small>
            </div>
            <span class="agenda-pill">{{ event.type }}</span>
          </div>
        </div>
      </article>
    </section>
  </div>
</template>

<script setup>
import { computed, inject, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth.store'
import { useStudentStore } from '@/stores/student.store'
import { useCompanyAdmin } from '@/compasables/useCompanyAdmin'
import { useCompanyOpportunities } from '@/compasables/useCompanyOpportunities'

const locale = inject('locale', ref('en'))

const translations = {
  en: {
    heroEyebrow: 'University Command Center',
    dashboard: 'University Dashboard',
    dashboardSubtitle:
      'Monitor academic momentum, company partnerships, and internship outcomes from one high-level control panel.',
    viewStudents: 'View Students',
    openReports: 'Open Reports',
    placementRate: 'Placement Rate',
    placementHint: 'Students placed in companies this term',
    employmentRate: 'Employment Rate',
    employmentHint: 'Graduates employed after completion',
    totalStudents: 'Total Students',
    activeStudents: 'Active Students',
    companies: 'Partner Companies',
    internships: 'Open Internships',
    vsLastMonth: 'vs last month',
    loading: 'Loading dashboard data...',
    studentsByDepartment: 'Students by Department',
    departmentBreakdown: 'Enrollment distribution across faculties',
    currentSemester: 'Current Semester',
    monthlyPlacements: 'Monthly Placements',
    monthlyPlacementsHint: 'Internship placements in the last 6 months',
    recentActivities: 'Recent Activities',
    activityFeed: 'Latest updates from university operations',
    upcomingAgenda: 'Upcoming Agenda',
    agendaHint: 'Priority milestones and events',
    noActivity: 'No recent activity yet.',
    noAgenda: 'No upcoming deadlines.',
    unknownCompany: 'Unknown company',
    internship: 'Internship',
    approvedProfiles: 'approved profiles',
    opportunitiesPublished: 'opportunities published',
    newPartnerAdded: 'new partner added',
    ago: 'ago',
    justNow: 'just now',
    hour: 'hour',
    hours: 'hours',
    day: 'day',
    days: 'days',
    minute: 'minute',
    minutes: 'minutes',
    event: 'Event',
    deadline: 'Deadline',
    report: 'Report',
  },
  fr: {
    heroEyebrow: 'Centre de pilotage universitaire',
    dashboard: 'Tableau de bord universitaire',
    dashboardSubtitle:
      'Suivez la dynamique academique, les partenariats entreprises et les resultats de stage depuis un panneau central.',
    viewStudents: 'Voir les etudiants',
    openReports: 'Ouvrir les rapports',
    placementRate: 'Taux de placement',
    placementHint: 'Etudiants places en entreprise ce semestre',
    employmentRate: "Taux d'emploi",
    employmentHint: 'Diplomes employes apres la formation',
    totalStudents: 'Etudiants totaux',
    activeStudents: 'Etudiants actifs',
    companies: 'Entreprises partenaires',
    internships: 'Stages ouverts',
    vsLastMonth: 'par rapport au mois dernier',
    loading: 'Chargement des donnees du tableau de bord...',
    studentsByDepartment: 'Etudiants par departement',
    departmentBreakdown: 'Repartition des inscriptions par faculte',
    currentSemester: 'Semestre en cours',
    monthlyPlacements: 'Placements mensuels',
    monthlyPlacementsHint: 'Placements en stage sur les 6 derniers mois',
    recentActivities: 'Activites recentes',
    activityFeed: 'Dernieres mises a jour des operations',
    upcomingAgenda: 'Agenda a venir',
    agendaHint: 'Etapes prioritaires et evenements',
    noActivity: 'Aucune activite recente pour le moment.',
    noAgenda: 'Aucune echeance a venir.',
    unknownCompany: 'Entreprise inconnue',
    internship: 'Stage',
    approvedProfiles: 'profils approuves',
    opportunitiesPublished: 'opportunites publiees',
    newPartnerAdded: 'nouveau partenaire ajoute',
    ago: 'il y a',
    justNow: "a l'instant",
    hour: 'heure',
    hours: 'heures',
    day: 'jour',
    days: 'jours',
    minute: 'minute',
    minutes: 'minutes',
    event: 'Evenement',
    deadline: 'Echeance',
    report: 'Rapport',
  },
}

const t = computed(() => translations[locale.value] || translations.en)

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

const safeDate = (value) => {
  if (!value) return null
  const parsed = new Date(value)
  return Number.isNaN(parsed.getTime()) ? null : parsed
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

const connectedUniversityName = computed(() => {
  const user = authStore.user || {}
  const fromUniversityObj =
    user.university?.name || user.universityProfile?.name || user.universityId?.name || ''
  return fromUniversityObj || user.universityName || user.fullName || t.value.dashboard
})

const heroSubtitle = computed(
  () => `${t.value.dashboardSubtitle} ${connectedUniversityName.value}.`,
)

const normalizeStatus = (status) => {
  const value = String(status || '')
    .trim()
    .toLowerCase()
  if (value.includes('approve') || value.includes('active')) return 'active'
  if (value.includes('reject') || value.includes('inactive') || value.includes('archive')) {
    return 'inactive'
  }
  if (value.includes('pend') || value.includes('review')) return 'pending'
  if (value.includes('open')) return 'active'
  return value || 'pending'
}

const isPlacedStudent = (student) => {
  if (Number(student.applicationCount || 0) > 0) return true
  if (Array.isArray(student.projects) && student.projects.length > 0) return true
  return false
}

const isEmploymentStudent = (student) => {
  const status = normalizeStatus(student.status)
  return status === 'active' && isPlacedStudent(student)
}

const students = computed(() => studentStore.students || [])

const dashboardStats = computed(() => {
  const totalStudents = students.value.length
  const activeStudents = students.value.filter(
    (student) => normalizeStatus(student.status) === 'active',
  ).length
  const activeCompanies = (companies.value || []).filter(
    (item) => normalizeStatus(item.status) === 'active',
  ).length
  const openInternships = (opportunities.value || []).filter((item) => {
    const status = normalizeStatus(item.status)
    return status === 'active' || status === 'pending'
  }).length
  const placementCount = students.value.filter(isPlacedStudent).length
  const employedCount = students.value.filter(isEmploymentStudent).length

  const placementRate = totalStudents ? Math.round((placementCount / totalStudents) * 100) : 0
  const employmentRate = totalStudents ? Math.round((employedCount / totalStudents) * 100) : 0

  return {
    students: totalStudents,
    activeStudents,
    companies: activeCompanies,
    internships: openInternships,
    placementRate,
    employmentRate,
  }
})

const getMonthKey = (date) => `${date.getFullYear()}-${date.getMonth() + 1}`

const thisMonthRange = computed(() => {
  const now = new Date()
  return {
    start: new Date(now.getFullYear(), now.getMonth(), 1),
    end: now,
  }
})

const lastMonthRange = computed(() => {
  const now = new Date()
  const start = new Date(now.getFullYear(), now.getMonth() - 1, 1)
  const end = new Date(now.getFullYear(), now.getMonth(), 0)
  return { start, end }
})

const countByRange = (items, dateGetter, range) =>
  items.filter((item) => {
    const date = safeDate(dateGetter(item))
    if (!date) return false
    return date >= range.start && date <= range.end
  }).length

const computeTrend = (current, previous) => {
  if (!previous && !current) return 0
  if (!previous) return 100
  return Number((((current - previous) / previous) * 100).toFixed(1))
}

const formatNumber = (value) =>
  new Intl.NumberFormat(locale.value === 'fr' ? 'fr-FR' : 'en-GB').format(Number(value || 0))

const metricCards = computed(() => [
  {
    key: 'students',
    label: t.value.totalStudents,
    value: formatNumber(dashboardStats.value.students),
    trend: computeTrend(
      countByRange(
        students.value,
        (student) => student.updatedAt || student.createdAt,
        thisMonthRange.value,
      ),
      countByRange(
        students.value,
        (student) => student.updatedAt || student.createdAt,
        lastMonthRange.value,
      ),
    ),
    icon: 'bi bi-people-fill',
  },
  {
    key: 'active',
    label: t.value.activeStudents,
    value: formatNumber(dashboardStats.value.activeStudents),
    trend: computeTrend(
      countByRange(
        students.value.filter((student) => normalizeStatus(student.status) === 'active'),
        (student) => student.updatedAt || student.createdAt,
        thisMonthRange.value,
      ),
      countByRange(
        students.value.filter((student) => normalizeStatus(student.status) === 'active'),
        (student) => student.updatedAt || student.createdAt,
        lastMonthRange.value,
      ),
    ),
    icon: 'bi bi-person-check-fill',
  },
  {
    key: 'companies',
    label: t.value.companies,
    value: formatNumber(dashboardStats.value.companies),
    trend: computeTrend(
      countByRange(
        (companies.value || []).filter((item) => normalizeStatus(item.status) === 'active'),
        (item) => item.createdAt || item.raw?.createdAt,
        thisMonthRange.value,
      ),
      countByRange(
        (companies.value || []).filter((item) => normalizeStatus(item.status) === 'active'),
        (item) => item.createdAt || item.raw?.createdAt,
        lastMonthRange.value,
      ),
    ),
    icon: 'bi bi-building-fill-check',
  },
  {
    key: 'internships',
    label: t.value.internships,
    value: formatNumber(dashboardStats.value.internships),
    trend: computeTrend(
      countByRange(
        (opportunities.value || []).filter((item) => {
          const status = normalizeStatus(item.status)
          return status === 'active' || status === 'pending'
        }),
        (item) => item.raw?.publishedAt || item.raw?.createdAt || item.deadLine,
        thisMonthRange.value,
      ),
      countByRange(
        (opportunities.value || []).filter((item) => {
          const status = normalizeStatus(item.status)
          return status === 'active' || status === 'pending'
        }),
        (item) => item.raw?.publishedAt || item.raw?.createdAt || item.deadLine,
        lastMonthRange.value,
      ),
    ),
    icon: 'bi bi-briefcase-fill',
  },
])

const departments = computed(() => {
  const buckets = new Map()

  for (const student of students.value) {
    const key = student.faculty || student.program || student.department || 'Unassigned'
    buckets.set(key, (buckets.get(key) || 0) + 1)
  }

  const rows = [...buckets.entries()].sort((a, b) => b[1] - a[1]).slice(0, 6)
  const fallbackLabel = locale.value === 'fr' ? 'Non assigne' : 'Unassigned'

  return rows.map(([name, value]) => ({
    name: name === 'Unassigned' ? fallbackLabel : name,
    value,
  }))
})

const monthlyPlacements = computed(() => {
  const now = new Date()
  const monthStart = new Date(now.getFullYear(), now.getMonth() - 5, 1)
  const labels = []
  const counts = new Map()

  for (let i = 0; i < 6; i += 1) {
    const date = new Date(monthStart.getFullYear(), monthStart.getMonth() + i, 1)
    const monthKey = getMonthKey(date)
    const label = date.toLocaleDateString(locale.value === 'fr' ? 'fr-FR' : 'en-GB', {
      month: 'short',
    })
    labels.push({ label, monthKey })
    counts.set(monthKey, 0)
  }

  for (const student of students.value) {
    if (!isPlacedStudent(student)) continue
    const date = safeDate(student.updatedAt || student.createdAt)
    if (!date) continue
    const monthKey = getMonthKey(new Date(date.getFullYear(), date.getMonth(), 1))
    if (counts.has(monthKey)) {
      counts.set(monthKey, (counts.get(monthKey) || 0) + 1)
    }
  }

  const values = labels.map((item) => counts.get(item.monthKey) || 0)
  const maxValue = Math.max(...values, 1)

  return labels.map((item, index) => ({
    month: item.label,
    value: values[index],
    height: Math.max(8, Math.round((values[index] / maxValue) * 100)),
  }))
})

const diffTimeLabel = (dateValue) => {
  const date = safeDate(dateValue)
  if (!date) return t.value.justNow

  const diffMs = Date.now() - date.getTime()
  const minutes = Math.max(0, Math.floor(diffMs / 60000))
  const hours = Math.floor(minutes / 60)
  const days = Math.floor(hours / 24)

  if (days > 0) {
    const unit = days > 1 ? t.value.days : t.value.day
    return locale.value === 'fr'
      ? `${t.value.ago} ${days} ${unit}`
      : `${days} ${unit} ${t.value.ago}`
  }

  if (hours > 0) {
    const unit = hours > 1 ? t.value.hours : t.value.hour
    return locale.value === 'fr'
      ? `${t.value.ago} ${hours} ${unit}`
      : `${hours} ${unit} ${t.value.ago}`
  }

  if (minutes > 0) {
    const unit = minutes > 1 ? t.value.minutes : t.value.minute
    return locale.value === 'fr'
      ? `${t.value.ago} ${minutes} ${unit}`
      : `${minutes} ${unit} ${t.value.ago}`
  }

  return t.value.justNow
}

const recentActivities = computed(() => {
  const rows = []

  for (const item of (opportunities.value || []).slice(0, 8)) {
    const date = item.raw?.publishedAt || item.raw?.createdAt || item.deadLine
    rows.push({
      id: `opp-${item.id}`,
      timestamp: safeDate(date)?.getTime() || 0,
      title:
        locale.value === 'fr'
          ? `${t.value.internship}: ${item.title}`
          : `${t.value.internship}: ${item.title}`,
      time: diffTimeLabel(date),
    })
  }

  for (const student of students.value.slice(0, 8)) {
    const status = normalizeStatus(student.status)
    if (status !== 'active') continue
    const date = student.updatedAt || student.createdAt
    rows.push({
      id: `student-${student.id}`,
      timestamp: safeDate(date)?.getTime() || 0,
      title:
        locale.value === 'fr'
          ? `${student.fullName || 'Etudiant'} - ${t.value.approvedProfiles}`
          : `${student.fullName || 'Student'} - ${t.value.approvedProfiles}`,
      time: diffTimeLabel(date),
    })
  }

  for (const company of (companies.value || []).slice(0, 8)) {
    const status = normalizeStatus(company.status)
    if (status !== 'active') continue
    const date = company.createdAt || company.raw?.createdAt
    rows.push({
      id: `company-${company.id}`,
      timestamp: safeDate(date)?.getTime() || 0,
      title:
        locale.value === 'fr'
          ? `${company.name || 'Entreprise'} - ${t.value.newPartnerAdded}`
          : `${company.name || 'Company'} - ${t.value.newPartnerAdded}`,
      time: diffTimeLabel(date),
    })
  }

  const topRows = rows
    .sort((a, b) => b.timestamp - a.timestamp)
    .slice(0, 6)
    .map((item) => ({ id: item.id, title: item.title, time: item.time }))

  if (topRows.length) return topRows
  return [{ id: 'empty-activity', title: t.value.noActivity, time: t.value.justNow }]
})

const agenda = computed(() => {
  const items = (opportunities.value || [])
    .map((item) => {
      const companyName = item.raw?.companyId?.name || t.value.unknownCompany
      const deadlineDate = safeDate(item.deadLine)
      if (!deadlineDate) return null

      return {
        id: `deadline-${item.id}`,
        deadlineDate,
        title: `${item.title} · ${companyName}`,
        date: deadlineDate.toLocaleDateString(locale.value === 'fr' ? 'fr-FR' : 'en-GB', {
          day: '2-digit',
          month: 'short',
          year: 'numeric',
        }),
        type: t.value.deadline,
      }
    })
    .filter(Boolean)
    .sort((a, b) => a.deadlineDate.getTime() - b.deadlineDate.getTime())
    .slice(0, 4)

  if (items.length) {
    return items.map((item) => ({
      id: item.id,
      title: item.title,
      date: item.date,
      type: item.type,
    }))
  }

  return [{ id: 'empty-agenda', title: t.value.noAgenda, date: '-', type: t.value.report }]
})

const departmentMax = computed(() => Math.max(...departments.value.map((item) => item.value), 1))

const departmentWidth = (value) =>
  `${Math.min(100, Math.round((value / departmentMax.value) * 100))}%`

const isLoading = computed(
  () => isLoadingCompanies.value || isLoadingOpportunities.value || studentStore.loading,
)

const loadError = computed(() => {
  const studentError = studentStore.error?.message || ''
  return companyAdminError.value || opportunityError.value || studentError || ''
})

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
.dashboard-v2 {
  display: grid;
  gap: 20px;
}

.hero-card,
.metric-card,
.panel-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 22px;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
}

.hero-card {
  position: relative;
  overflow: hidden;
  display: grid;
  grid-template-columns: 1.7fr 1fr;
  gap: 18px;
  padding: 24px;
  isolation: isolate;
}

.hero-card::before {
  content: '';
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 85% 15%, rgba(212, 160, 23, 0.22), transparent 45%),
    linear-gradient(130deg, rgba(13, 43, 69, 0.36), rgba(30, 63, 102, 0.2));
  z-index: -1;
}

.eyebrow {
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 0.13em;
  color: var(--primary);
  font-size: 0.75rem;
  font-weight: 700;
}

.hero-main h1 {
  margin: 8px 0 0;
  color: var(--text);
  font-size: 2rem;
}

.hero-subtitle {
  margin: 12px 0 0;
  color: var(--muted);
  max-width: 760px;
  line-height: 1.65;
}

.hero-actions {
  margin-top: 18px;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.primary-btn,
.ghost-btn {
  text-decoration: none;
  border-radius: 12px;
  padding: 11px 14px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
}

.primary-btn {
  background: #d4a017;
  color: #0d2b45;
}

.ghost-btn {
  color: #f2f4f7;
  background: rgba(30, 63, 102, 0.86);
  border: 1px solid rgba(212, 160, 23, 0.4);
}

.hero-aside {
  display: grid;
  gap: 10px;
}

.aside-kpi {
  border-radius: 16px;
  background: rgba(30, 63, 102, 0.6);
  border: 1px solid rgba(242, 244, 247, 0.14);
  padding: 14px;
  display: grid;
  gap: 6px;
}

.aside-kpi span,
.aside-kpi small,
.panel-head p,
.metric-card p,
.agenda-item small,
.timeline-item small {
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

.aside-kpi strong,
.panel-head h3,
.metric-card h2,
.item-line span,
.timeline-item strong,
.agenda-item span {
  color: var(--text);
}

.aside-kpi strong {
  font-size: 1.65rem;
}

.metric-grid {
  display: grid;
  gap: 12px;
  grid-template-columns: repeat(4, minmax(0, 1fr));
}

.metric-card {
  padding: 16px;
  display: grid;
  gap: 8px;
}

.metric-head {
  display: flex;
  justify-content: space-between;
  gap: 10px;
  align-items: center;
}

.metric-head i {
  width: 34px;
  height: 34px;
  border-radius: 11px;
  display: grid;
  place-items: center;
  background: rgba(212, 160, 23, 0.18);
  color: #f2d277;
}

.metric-card h2 {
  margin: 0;
  font-size: 1.75rem;
}

.metric-card p {
  margin: 0;
  font-size: 0.88rem;
}

.positive {
  color: #8af0b0;
}

.negative {
  color: #f2d277;
}

.analytics-grid {
  display: grid;
  gap: 14px;
  grid-template-columns: 1.5fr 1fr;
}

.operations-grid {
  display: grid;
  gap: 14px;
  grid-template-columns: 1.35fr 1fr;
}

.panel-card {
  padding: 16px;
}

.panel-head {
  display: flex;
  justify-content: space-between;
  gap: 10px;
  align-items: flex-start;
  margin-bottom: 14px;
}

.panel-head h3 {
  margin: 0;
}

.panel-head p {
  margin: 6px 0 0;
}

.panel-tag {
  border-radius: 999px;
  padding: 7px 10px;
  font-size: 0.78rem;
  background: rgba(212, 160, 23, 0.18);
  color: #f2d277;
}

.department-list {
  display: grid;
  gap: 10px;
}

.department-item {
  border-radius: 14px;
  padding: 10px;
  background: var(--surface-soft);
  border: 1px solid var(--border);
}

.item-line {
  display: flex;
  justify-content: space-between;
  gap: 8px;
  margin-bottom: 8px;
}

.progress-track {
  width: 100%;
  height: 10px;
  border-radius: 999px;
  overflow: hidden;
  background: rgba(13, 43, 69, 0.66);
}

.progress-fill {
  height: 100%;
  border-radius: 999px;
  background: linear-gradient(90deg, #d4a017, #9ec0de);
}

.bar-chart {
  min-height: 250px;
  display: grid;
  grid-template-columns: repeat(6, minmax(0, 1fr));
  gap: 12px;
  align-items: end;
}

.bar-col {
  display: grid;
  gap: 8px;
  justify-items: center;
}

.bar-value {
  color: var(--text);
  font-weight: 700;
  font-size: 0.84rem;
}

.bar-track {
  width: 100%;
  height: 170px;
  border-radius: 14px;
  background: rgba(13, 43, 69, 0.68);
  position: relative;
  overflow: hidden;
  border: 1px solid rgba(148, 163, 184, 0.2);
}

.bar-fill {
  position: absolute;
  left: 0;
  bottom: 0;
  width: 100%;
  border-radius: 12px 12px 0 0;
  background: linear-gradient(180deg, #9ec0de, #d4a017);
}

.bar-col small {
  color: var(--muted);
}

.timeline-list,
.agenda-list {
  display: grid;
  gap: 10px;
}

.timeline-item,
.agenda-item {
  border-radius: 14px;
  border: 1px solid var(--border);
  background: var(--surface-soft);
  padding: 11px 12px;
}

.timeline-item {
  display: grid;
  grid-template-columns: 18px 1fr;
  gap: 8px;
  align-items: flex-start;
}

.dot {
  width: 10px;
  height: 10px;
  border-radius: 999px;
  margin-top: 7px;
  background: #d4a017;
  box-shadow: 0 0 0 4px rgba(212, 160, 23, 0.22);
}

.timeline-item strong,
.timeline-item small {
  display: block;
}

.agenda-item {
  display: flex;
  justify-content: space-between;
  gap: 10px;
  align-items: center;
}

.agenda-pill {
  border-radius: 999px;
  padding: 6px 10px;
  font-size: 0.74rem;
  color: #f2d277;
  background: rgba(212, 160, 23, 0.2);
  white-space: nowrap;
}

@media (max-width: 1180px) {
  .hero-card,
  .analytics-grid,
  .operations-grid {
    grid-template-columns: 1fr;
  }

  .hero-aside {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .metric-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 760px) {
  .hero-main h1 {
    font-size: 1.55rem;
  }

  .hero-actions,
  .hero-aside {
    grid-template-columns: 1fr;
  }

  .primary-btn,
  .ghost-btn {
    width: 100%;
    justify-content: center;
  }

  .metric-grid {
    grid-template-columns: 1fr;
  }

  .bar-chart {
    gap: 8px;
  }

  .bar-track {
    height: 140px;
  }
}
</style>
