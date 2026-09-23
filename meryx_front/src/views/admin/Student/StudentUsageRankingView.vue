<template>
  <div class="usage-page">
    <header class="card page-header">
      <div>
        <p class="kicker">{{ t.analytics }}</p>
        <h1>{{ t.usageTitle }}</h1>
        <p class="hint">{{ t.usageHint }}</p>
      </div>
      <RouterLink class="text-btn" :to="{ name: 'adminStudents' }">{{
        t.backToStudents
      }}</RouterLink>
    </header>

    <section class="card top-card" v-if="mostActiveStudent">
      <p class="top-label">{{ t.mostActiveNow }}</p>
      <h2>{{ mostActiveStudent.fullName }}</h2>
      <p class="top-meta">
        {{ mostActiveStudent.university }} · {{ mostActiveStudent.usage.sessionsLast30Days }}
        {{ t.sessions }}
      </p>
    </section>

    <section class="card table-card">
      <table>
        <thead>
          <tr>
            <th>{{ t.rank }}</th>
            <th>{{ t.student }}</th>
            <th>{{ t.university }}</th>
            <th>{{ t.sessions30 }}</th>
            <th>{{ t.avgMinutes }}</th>
            <th>{{ t.actions30 }}</th>
            <th>{{ t.score }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading">
            <td colspan="7">{{ t.loading }}</td>
          </tr>
          <tr v-else-if="hasError">
            <td colspan="7">{{ t.loadingError }}</td>
          </tr>
          <tr v-else-if="!studentsByUsage.length">
            <td colspan="7">{{ t.noData }}</td>
          </tr>
          <template v-else>
            <tr v-for="(student, index) in studentsByUsage" :key="student.id">
              <td>#{{ index + 1 }}</td>
              <td>
                <strong>{{ student.fullName }}</strong>
              </td>
              <td>{{ student.university }}</td>
              <td>{{ student.usage.sessionsLast30Days }}</td>
              <td>{{ student.usage.avgSessionMinutes }}</td>
              <td>{{ student.usage.actionsLast30Days }}</td>
              <td>{{ usageScore(student).toFixed(1) }}</td>
            </tr>
          </template>
        </tbody>
      </table>
    </section>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useUiPreferences } from '@/compasables/useUiPreferences'
import { useStudentAdmin } from '@/compasables/useStudentAdmin'

const { locale } = useUiPreferences()
const {
  studentsByUsage,
  mostActiveStudent,
  usageScore,
  loadStudents,
  loading,
  storeLoading,
  error,
  storeError,
} = useStudentAdmin()

const isLoading = computed(() => loading.value || storeLoading.value)
const hasError = computed(() => Boolean(error.value || storeError.value))

const translations = {
  en: {
    analytics: 'Student Analytics',
    usageTitle: 'Most Active Students',
    usageHint:
      'Ranking based on sessions, average usage time, and actions during the last 30 days.',
    backToStudents: 'Back to Students',
    mostActiveNow: 'Most active right now',
    sessions: 'sessions in 30 days',
    rank: 'Rank',
    student: 'Student',
    university: 'University',
    sessions30: 'Sessions (30d)',
    avgMinutes: 'Avg Minutes/Session',
    actions30: 'Actions (30d)',
    score: 'Usage Score',
    loading: 'Loading student ranking...',
    loadingError: 'Unable to load student ranking at the moment.',
    noData: 'No student activity data available yet.',
  },
  fr: {
    analytics: 'Analytique Etudiants',
    usageTitle: 'Etudiants les plus actifs',
    usageHint:
      "Classement base sur les sessions, le temps moyen d'utilisation et les actions sur 30 jours.",
    backToStudents: 'Retour aux etudiants',
    mostActiveNow: 'Le plus actif actuellement',
    sessions: 'sessions sur 30 jours',
    rank: 'Rang',
    student: 'Etudiant',
    university: 'Universite',
    sessions30: 'Sessions (30j)',
    avgMinutes: 'Moyenne min/session',
    actions30: 'Actions (30j)',
    score: "Score d'usage",
    loading: 'Chargement du classement des etudiants...',
    loadingError: 'Impossible de charger le classement pour le moment.',
    noData: "Aucune donnee d'activite etudiante disponible pour le moment.",
  },
}

const t = computed(() => translations[locale.value] || translations.en)

onMounted(async () => {
  await loadStudents()
})
</script>

<style scoped>
.usage-page {
  display: grid;
  gap: 16px;
}

.page-header,
.top-card,
.table-card {
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
.top-label,
.top-meta {
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
h2 {
  margin: 0;
}

h1 {
  margin-top: 6px;
}

.hint {
  margin-top: 8px;
  color: var(--muted);
}

.top-label {
  color: var(--muted);
  font-size: 0.8rem;
}

.top-card h2 {
  margin-top: 6px;
}

.top-meta {
  margin-top: 6px;
  color: var(--muted);
}

.table-card {
  overflow-x: auto;
}

@media (max-width: 760px) {
  .page-header {
    flex-direction: column;
  }
}
</style>
