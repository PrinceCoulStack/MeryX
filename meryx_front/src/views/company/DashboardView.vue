<template>
  <div class="company-dashboard">
    <section class="hero-card">
      <div>
        <p class="eyebrow">{{ t.eyebrow }}</p>
        <h1>{{ companyName }}</h1>
        <p class="hero-text">{{ t.heroText }}</p>
      </div>
      <div class="hero-actions">
        <button class="primary-btn" type="button" @click="goToOpportunities">
          {{ t.postOpportunity }}
        </button>
        <button class="ghost-btn" type="button" @click="goToTrainings">
          {{ t.viewTrainings }}
        </button>
      </div>
    </section>

    <section class="kpi-grid">
      <article class="kpi-card">
        <span>{{ t.internshipsPosted }}</span>
        <strong>{{ dashboardStats.opportunities }}</strong>
        <small>{{ t.liveOpportunities }}</small>
      </article>
      <article class="kpi-card">
        <span>{{ t.candidates }}</span>
        <strong>{{ dashboardStats.candidates }}</strong>
        <small>{{ t.activeCandidates }}</small>
      </article>
      <article class="kpi-card">
        <span>{{ t.applications }}</span>
        <strong>{{ dashboardStats.applications }}</strong>
        <small>{{ t.receivedThisCycle }}</small>
      </article>
      <article class="kpi-card">
        <span>{{ t.interviewsPlanned }}</span>
        <strong>{{ dashboardStats.interviews }}</strong>
        <small>{{ t.upcomingMeetings }}</small>
      </article>
    </section>

    <section class="content-grid">
      <article class="panel-card">
        <div class="section-head">
          <h2>{{ t.topOpportunities }}</h2>
          <span>{{ t.byApplications }}</span>
        </div>

        <div class="list-grid">
          <div class="list-item" v-for="item in topOpportunities" :key="item.id">
            <div>
              <strong>{{ item.title }}</strong>
              <small>{{ item.department }}</small>
            </div>
            <div class="metric">
              <span>{{ item.applications }}</span>
              <small>apps</small>
            </div>
          </div>
        </div>
      </article>

      <article class="panel-card">
        <div class="section-head">
          <h2>{{ t.recruitmentPipeline }}</h2>
          <span>{{ t.currentSnapshot }}</span>
        </div>

        <div class="pipeline-grid">
          <div class="pipeline-item" v-for="step in pipeline" :key="step.label">
            <div class="bar-track">
              <span :style="{ width: `${step.value}%` }"></span>
            </div>
            <div class="line">
              <strong>{{ step.label }}</strong>
              <small>{{ step.count }}</small>
            </div>
          </div>
        </div>
      </article>
    </section>

    <section class="panel-card activities-card">
      <div class="section-head">
        <h2>{{ t.recentActivity }}</h2>
        <span>{{ t.latestUpdates }}</span>
      </div>

      <div class="timeline">
        <div class="timeline-item" v-for="activity in activities" :key="activity.id">
          <div class="dot"></div>
          <div>
            <strong>{{ activity.title }}</strong>
            <p>{{ activity.description }}</p>
            <small>{{ activity.time }}</small>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed, inject, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.store'
import { useCompanyOpportunities } from '@/compasables/useCompanyOpportunities'
import { useCompanyTrainings } from '@/compasables/useCompanyTrainings'
import { useCompanyActualities } from '@/compasables/useCompanyActualities'

const router = useRouter()
const locale = inject('locale', ref('en'))
const authStore = useAuthStore()
const { companyOpportunities, currentCompanyId, fetchOpportunities, getOpportunityCandidates } =
  useCompanyOpportunities()
const { companyTrainings, fetchTrainings } = useCompanyTrainings()
const { companyActualities, fetchActualities } = useCompanyActualities()

const translations = {
  en: {
    eyebrow: 'Company / Dashboard',
    heroText:
      'Track opportunity performance, candidate flow, and recruiting progress in one place.',
    postOpportunity: 'Post Opportunity',
    viewTrainings: 'View Trainings',
    internshipsPosted: 'Internships Posted',
    liveOpportunities: 'Live opportunities',
    candidates: 'Candidates',
    activeCandidates: 'Total active candidates',
    applications: 'Applications',
    receivedThisCycle: 'Received this cycle',
    interviewsPlanned: 'Interviews Planned',
    upcomingMeetings: 'Upcoming meetings',
    topOpportunities: 'Top Opportunities',
    byApplications: 'By applications',
    recruitmentPipeline: 'Recruitment Pipeline',
    currentSnapshot: 'Current snapshot',
    recentActivity: 'Recent Recruiting Activity',
    latestUpdates: 'Latest updates',
    noCompany: 'Company Dashboard',
  },
  fr: {
    eyebrow: 'Entreprise / Tableau de bord',
    heroText:
      'Suivez les performances des opportunites, le flux des candidats et la progression du recrutement au meme endroit.',
    postOpportunity: 'Publier une opportunite',
    viewTrainings: 'Voir les formations',
    internshipsPosted: 'Stages publies',
    liveOpportunities: 'Opportunites en ligne',
    candidates: 'Candidats',
    activeCandidates: 'Total des candidats actifs',
    applications: 'Candidatures',
    receivedThisCycle: 'Recues ce cycle',
    interviewsPlanned: 'Entretiens planifies',
    upcomingMeetings: 'Rendez-vous a venir',
    topOpportunities: 'Meilleures opportunites',
    byApplications: 'Par candidatures',
    recruitmentPipeline: 'Pipeline de recrutement',
    currentSnapshot: 'Vue actuelle',
    recentActivity: 'Activite de recrutement recente',
    latestUpdates: 'Dernieres mises a jour',
    noCompany: 'Tableau de bord entreprise',
  },
}

const t = computed(() => translations[locale.value] || translations.en)

const companyName = computed(() => {
  const user = authStore.user || {}
  return (
    user.companyName ||
    user.company?.name ||
    user.profile?.companyName ||
    user.fullName ||
    t.value.noCompany
  )
})

const dashboardStats = computed(() => {
  const opportunities = companyOpportunities.value.length
  const trainings = companyTrainings.value.length
  const applications = topOpportunities.value.reduce((total, item) => total + item.applications, 0)
  const candidates = topOpportunities.value.reduce((total, item) => total + item.candidates, 0)
  const interviews = topOpportunities.value.reduce((total, item) => total + item.interviews, 0)

  return {
    opportunities: opportunities + trainings,
    candidates,
    applications,
    interviews,
  }
})

const topOpportunities = ref([])
const pipeline = ref([])
const activities = ref([])

const buildPipeline = () => {
  const counts = topOpportunities.value.reduce(
    (accumulator, item) => {
      accumulator.applications += item.applications
      accumulator.shortlisted += item.shortlisted
      accumulator.interviewed += item.interviewed
      accumulator.offers += item.offers
      return accumulator
    },
    { applications: 0, shortlisted: 0, interviewed: 0, offers: 0 },
  )

  const total = Math.max(counts.applications, 1)
  pipeline.value = [
    { label: 'New Applications', count: counts.applications, value: 100 },
    {
      label: 'Shortlisted',
      count: counts.shortlisted,
      value: Math.round((counts.shortlisted / total) * 100),
    },
    {
      label: 'Interviewed',
      count: counts.interviewed,
      value: Math.round((counts.interviewed / total) * 100),
    },
    {
      label: 'Offers Sent',
      count: counts.offers,
      value: Math.round((counts.offers / total) * 100),
    },
  ]
}

const loadDashboard = async () => {
  await Promise.all([fetchOpportunities(), fetchTrainings(), fetchActualities()])

  const companyOpportunityRows = companyOpportunities.value
  const candidateBreakdown = await Promise.all(
    companyOpportunityRows.map(async (opportunity) => {
      const candidates = await getOpportunityCandidates(opportunity.id)
      const interviewCount = candidates.filter(
        (candidate) =>
          String(candidate.status || '')
            .toLowerCase()
            .includes('interview') || Boolean(candidate.interviewDate),
      ).length
      const shortlistedCount = candidates.filter((candidate) =>
        String(candidate.status || '')
          .toLowerCase()
          .includes('short'),
      ).length
      const offersCount = candidates.filter((candidate) =>
        String(candidate.status || '')
          .toLowerCase()
          .includes('offer'),
      ).length

      return {
        id: opportunity.id,
        title: opportunity.title,
        department: opportunity.department || opportunity.type || 'General',
        applications: candidates.length,
        candidates: candidates.length,
        interviews: interviewCount,
        shortlisted: shortlistedCount,
        interviewed: interviewCount,
        offers: offersCount,
      }
    }),
  )

  topOpportunities.value = candidateBreakdown
    .sort((left, right) => right.applications - left.applications)
    .slice(0, 4)

  buildPipeline()

  const recentPosts = companyActualities.value.slice(0, 3)
  activities.value = recentPosts.length
    ? recentPosts.map((item, index) => ({
        id: item.id || index,
        title: item.title || 'Recruiting update',
        description: item.content || item.category || 'New company activity.',
        time: item.postedAt || item.raw?.createdAt || 'Recently',
      }))
    : [
        {
          id: 1,
          title: 'No recent activity',
          description: 'Publish a company post to see recruiting updates here.',
          time: 'Now',
        },
      ]
}

onMounted(async () => {
  if (!currentCompanyId.value) return
  await loadDashboard()
})

const goToOpportunities = () => router.push({ name: 'companyCreateOpportunity' })
const goToTrainings = () => router.push({ name: 'companyTrainingList' })
</script>

<style scoped>
.company-dashboard {
  display: grid;
  gap: 12px;
}

.hero-card,
.kpi-card,
.panel-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 18px;
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
}

.hero-card {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
  padding: 14px;
}

.eyebrow {
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: var(--primary);
  font-size: 0.74rem;
  font-weight: 700;
}

.hero-card h1,
.section-head h2,
.list-item strong,
.timeline-item strong,
.line strong {
  margin: 0;
  color: var(--text);
}

.hero-card h1 {
  margin-top: 6px;
  font-size: clamp(1.22rem, 2.2vw, 1.72rem);
}

.hero-text,
.kpi-card span,
.kpi-card small,
.section-head span,
.list-item small,
.metric small,
.line small,
.timeline-item p,
.timeline-item small {
  color: var(--muted);
}

.hero-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.primary-btn,
.ghost-btn {
  border: none;
  border-radius: 999px;
  padding: 9px 12px;
  cursor: pointer;
}

.primary-btn {
  background: var(--primary);
  color: #fff;
}

.ghost-btn {
  border: 1px solid var(--border);
  color: var(--text);
  background: var(--surface);
}

.kpi-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 10px;
}

.kpi-card {
  padding: 11px;
  display: grid;
  gap: 3px;
}

.kpi-card strong {
  color: var(--text);
  font-size: 1.25rem;
}

.content-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

.panel-card {
  padding: 12px;
}

.section-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.list-grid,
.pipeline-grid,
.timeline {
  display: grid;
  gap: 8px;
}

.list-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px;
  border-radius: 12px;
  border: 1px solid var(--border);
  background: var(--surface-soft);
}

.metric {
  text-align: right;
}

.metric span {
  display: block;
  color: var(--text);
  font-weight: 700;
}

.pipeline-item {
  display: grid;
  gap: 6px;
  padding: 10px;
  border-radius: 12px;
  border: 1px solid var(--border);
  background: var(--surface-soft);
}

.bar-track {
  height: 8px;
  border-radius: 999px;
  background: rgba(15, 23, 42, 0.08);
  overflow: hidden;
}

.bar-track span {
  display: block;
  height: 100%;
  border-radius: 999px;
  background: linear-gradient(90deg, #0f766e, #d4a017);
}

.line {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.activities-card {
  padding-top: 10px;
}

.timeline-item {
  display: grid;
  grid-template-columns: 16px 1fr;
  gap: 8px;
  align-items: flex-start;
  padding: 10px;
  border-radius: 12px;
  border: 1px solid var(--border);
  background: var(--surface-soft);
}

.dot {
  width: 9px;
  height: 9px;
  border-radius: 999px;
  background: var(--primary);
  margin-top: 6px;
  box-shadow: 0 0 0 4px rgba(6, 170, 197, 0.16);
}

.timeline-item p {
  margin: 4px 0;
}

@media (max-width: 1140px) {
  .kpi-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .content-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 760px) {
  .hero-card {
    flex-direction: column;
  }

  .hero-actions {
    width: 100%;
  }

  .hero-actions button {
    width: 100%;
  }

  .kpi-grid {
    grid-template-columns: 1fr;
  }
}
</style>
