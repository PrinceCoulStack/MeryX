<template>
  <div class="dashboard-v2">
    <section class="hero-card">
      <div class="hero-main">
        <p class="eyebrow">{{ t.heroEyebrow }}</p>
        <h1>{{ t.dashboard }}</h1>
        <p class="hero-subtitle">{{ t.dashboardSubtitle }}</p>

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
          <strong>{{ stats.placementRate }}%</strong>
          <small>{{ t.placementHint }}</small>
        </div>
        <div class="aside-kpi">
          <span>{{ t.employmentRate }}</span>
          <strong>{{ stats.employmentRate }}%</strong>
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
          <div class="department-item" v-for="dept in departments" :key="dept.name">
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
          <div class="timeline-item" v-for="activity in recentActivities" :key="activity.title">
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
          <div class="agenda-item" v-for="event in agenda" :key="event.title">
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
import { computed, inject, ref } from 'vue'
import { RouterLink } from 'vue-router'

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
    studentsByDepartment: 'Students by Department',
    departmentBreakdown: 'Enrollment distribution across faculties',
    currentSemester: 'Current Semester',
    monthlyPlacements: 'Monthly Placements',
    monthlyPlacementsHint: 'Internship placements in the last 6 months',
    recentActivities: 'Recent Activities',
    activityFeed: 'Latest updates from university operations',
    upcomingAgenda: 'Upcoming Agenda',
    agendaHint: 'Priority milestones and events',
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
    studentsByDepartment: 'Etudiants par departement',
    departmentBreakdown: 'Repartition des inscriptions par faculte',
    currentSemester: 'Semestre en cours',
    monthlyPlacements: 'Placements mensuels',
    monthlyPlacementsHint: 'Placements en stage sur les 6 derniers mois',
    recentActivities: 'Activites recentes',
    activityFeed: 'Dernieres mises a jour des operations',
    upcomingAgenda: 'Agenda a venir',
    agendaHint: 'Etapes prioritaires et evenements',
  },
}

const t = computed(() => translations[locale.value] || translations.en)

const stats = ref({
  students: 4182,
  activeStudents: 3614,
  companies: 186,
  internships: 430,
  placementRate: 75,
  employmentRate: 88,
})

const metricCards = computed(() => [
  {
    key: 'students',
    label: t.value.totalStudents,
    value: stats.value.students,
    trend: 4.2,
    icon: 'bi bi-people-fill',
  },
  {
    key: 'active',
    label: t.value.activeStudents,
    value: stats.value.activeStudents,
    trend: 2.8,
    icon: 'bi bi-person-check-fill',
  },
  {
    key: 'companies',
    label: t.value.companies,
    value: stats.value.companies,
    trend: 6.4,
    icon: 'bi bi-building-fill-check',
  },
  {
    key: 'internships',
    label: t.value.internships,
    value: stats.value.internships,
    trend: -1.3,
    icon: 'bi bi-briefcase-fill',
  },
])

const departments = computed(() => {
  if (locale.value === 'fr') {
    return [
      { name: 'Informatique', value: 982 },
      { name: 'Ingenierie', value: 1240 },
      { name: 'Gestion', value: 860 },
      { name: 'Medecine', value: 720 },
      { name: 'Arts et design', value: 540 },
    ]
  }
  return [
    { name: 'Computer Science', value: 982 },
    { name: 'Engineering', value: 1240 },
    { name: 'Business', value: 860 },
    { name: 'Medicine', value: 720 },
    { name: 'Arts & Design', value: 540 },
  ]
})

const monthlyPlacements = computed(() => {
  const labels =
    locale.value === 'fr'
      ? ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Jun']
      : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']
  const values = [42, 51, 58, 67, 73, 81]
  return labels.map((month, index) => ({
    month,
    value: values[index],
    height: Math.round((values[index] / 81) * 100),
  }))
})

const recentActivities = computed(() => {
  if (locale.value === 'fr') {
    return [
      { title: 'Nouvelle opportunite de stage publiee', time: 'il y a 2 heures' },
      { title: 'Validation de 24 nouveaux profils etudiants', time: 'il y a 5 heures' },
      { title: 'Partenariat confirme avec NovaTech', time: 'hier' },
      { title: 'Atelier CV programme pour mardi', time: 'il y a 2 jours' },
    ]
  }
  return [
    { title: 'New internship opportunity published', time: '2 hours ago' },
    { title: '24 student profiles validated', time: '5 hours ago' },
    { title: 'Partnership confirmed with NovaTech', time: 'Yesterday' },
    { title: 'CV workshop scheduled for Tuesday', time: '2 days ago' },
  ]
})

const agenda = computed(() => {
  if (locale.value === 'fr') {
    return [
      { title: 'Forum entreprises', date: '12 Aout 2026', type: 'Evenement' },
      { title: 'Cloture candidatures stage', date: '18 Aout 2026', type: 'Deadline' },
      { title: 'Comite pedagogique', date: '22 Aout 2026', type: 'Reunion' },
      { title: 'Publication rapport mensuel', date: '30 Aout 2026', type: 'Rapport' },
    ]
  }
  return [
    { title: 'Company forum', date: '12 Aug 2026', type: 'Event' },
    { title: 'Internship application deadline', date: '18 Aug 2026', type: 'Deadline' },
    { title: 'Academic committee review', date: '22 Aug 2026', type: 'Meeting' },
    { title: 'Monthly report publication', date: '30 Aug 2026', type: 'Report' },
  ]
})

const departmentWidth = (value) => `${Math.min(100, Math.round((value / 1400) * 100))}%`
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
