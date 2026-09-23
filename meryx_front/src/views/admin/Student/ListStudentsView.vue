<template>
  <div class="students-page">
    <header class="card page-header">
      <div>
        <p class="kicker">{{ t.studentAdministration }}</p>
        <h1>{{ t.studentsDirectory }}</h1>
        <p class="hint">{{ t.directoryHint }}</p>
      </div>
      <RouterLink class="topbar-pill" :to="{ name: 'adminStudentsUsage' }">
        {{ t.viewUsageRanking }}
      </RouterLink>
    </header>

    <section class="summary-grid">
      <article class="card summary-card">
        <p>{{ t.totalStudents }}</p>
        <strong>{{ students.length }}</strong>
      </article>
      <article class="card summary-card">
        <p>{{ t.activeStudents }}</p>
        <strong>{{ activeCount }}</strong>
      </article>
      <article class="card summary-card">
        <p>{{ t.mostActive }}</p>
        <strong>{{ mostActiveStudent?.fullName || '-' }}</strong>
      </article>
    </section>

    <section class="card table-card">
      <table>
        <thead>
          <tr>
            <th>{{ t.fullName }}</th>
            <th>{{ t.email }}</th>
            <th>{{ t.university }}</th>
            <th>{{ t.level }}</th>
            <th>{{ t.status }}</th>
            <th>{{ t.lastSeen }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading">
            <td colspan="6">{{ t.loadingStudents }}</td>
          </tr>
          <tr v-else-if="!students.length">
            <td colspan="6">{{ t.noStudents }}</td>
          </tr>
          <template v-else>
            <tr v-for="student in students" :key="student.id">
              <td>
                <strong>{{ student.fullName }}</strong>
              </td>
              <td>{{ student.email }}</td>
              <td>{{ student.university }}</td>
              <td>{{ student.level }}</td>
              <td>
                <span
                  class="status-chip"
                  :class="student.status === 'active' ? 'active' : 'inactive'"
                >
                  {{ student.status === 'active' ? t.active : t.inactive }}
                </span>
              </td>
              <td>{{ student.usage.lastSeenAt }}</td>
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
const { students, mostActiveStudent, loadStudents, loading, storeLoading } = useStudentAdmin()

const activeCount = computed(() => students.value.filter((item) => item.status === 'active').length)
const isLoading = computed(() => loading.value || storeLoading.value)

const translations = {
  en: {
    studentAdministration: 'Student Administration',
    studentsDirectory: 'Students Directory',
    directoryHint: 'Review all students registered on the platform and their activity status.',
    viewUsageRanking: 'View Usage Ranking',
    totalStudents: 'Total Students',
    activeStudents: 'Active Students',
    mostActive: 'Most Active Student',
    fullName: 'Full Name',
    email: 'Email',
    university: 'University',
    level: 'Level',
    status: 'Status',
    lastSeen: 'Last Seen',
    loadingStudents: 'Loading students...',
    noStudents: 'No student records found.',
    active: 'Active',
    inactive: 'Inactive',
  },
  fr: {
    studentAdministration: 'Administration Etudiants',
    studentsDirectory: 'Repertoire des etudiants',
    directoryHint:
      "Consultez tous les etudiants inscrits sur la plateforme et leur statut d'activite.",
    viewUsageRanking: "Voir le classement d'usage",
    totalStudents: 'Total etudiants',
    activeStudents: 'Etudiants actifs',
    mostActive: 'Etudiant le plus actif',
    fullName: 'Nom complet',
    email: 'Email',
    university: 'Universite',
    level: 'Niveau',
    status: 'Statut',
    lastSeen: 'Derniere activite',
    loadingStudents: 'Chargement des etudiants...',
    noStudents: 'Aucun etudiant trouve.',
    active: 'Actif',
    inactive: 'Inactif',
  },
}

const t = computed(() => translations[locale.value] || translations.en)

onMounted(async () => {
  await loadStudents()
})
</script>

<style scoped>
.students-page {
  display: grid;
  gap: 16px;
}

.page-header,
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

h1 {
  margin: 6px 0 0;
}

.hint {
  margin-top: 8px;
  color: var(--muted);
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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
  display: block;
  margin-top: 6px;
  font-size: 1.45rem;
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
