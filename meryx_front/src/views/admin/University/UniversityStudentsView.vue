<template>
  <div class="students-page" v-if="university">
    <header class="card page-header">
      <div>
        <p class="kicker">{{ t.universityStudents }}</p>
        <h1>{{ university.name }}</h1>
        <p class="hint">{{ university.email }} · {{ university.country }}</p>
      </div>
      <RouterLink class="text-btn" :to="{ name: 'adminUniversities' }">{{ t.back }}</RouterLink>
    </header>

    <section class="card table-card">
      <div class="panel-head">
        <h2>{{ t.studentsList }} ({{ students.length }})</h2>
      </div>

      <table v-if="students.length">
        <thead>
          <tr>
            <th>{{ t.fullName }}</th>
            <th>{{ t.email }}</th>
            <th>{{ t.program }}</th>
            <th>{{ t.level }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="student in students" :key="student.id">
            <td>
              <strong>{{ student.fullName }}</strong>
            </td>
            <td>{{ student.email }}</td>
            <td>{{ student.program }}</td>
            <td>{{ student.level }}</td>
          </tr>
        </tbody>
      </table>

      <p v-else class="empty-state">{{ t.noStudents }}</p>
    </section>
  </div>

  <div v-else class="card not-found">
    <h2>{{ t.universityNotFound }}</h2>
    <RouterLink class="text-btn" :to="{ name: 'adminUniversities' }">{{ t.back }}</RouterLink>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useUiPreferences } from '@/compasables/useUiPreferences'
import { useUniversityAdmin } from '@/compasables/useUniversityAdmin'

const route = useRoute()
const { locale } = useUiPreferences()
const { getUniversityById, getStudentsByUniversityId } = useUniversityAdmin()

const university = computed(() => getUniversityById(route.params.id))
const students = computed(() => getStudentsByUniversityId(route.params.id))

const translations = {
  en: {
    universityStudents: 'University Students',
    back: 'Back to Universities',
    studentsList: 'Students List',
    fullName: 'Full Name',
    email: 'Email',
    program: 'Program',
    level: 'Level',
    noStudents: 'No students found for this university yet.',
    universityNotFound: 'University not found.',
  },
  fr: {
    universityStudents: "Etudiants de l'universite",
    back: 'Retour aux universites',
    studentsList: 'Liste des etudiants',
    fullName: 'Nom complet',
    email: 'Email',
    program: 'Programme',
    level: 'Niveau',
    noStudents: 'Aucun etudiant trouve pour cette universite pour le moment.',
    universityNotFound: 'Universite introuvable.',
  },
}

const t = computed(() => translations[locale.value] || translations.en)
</script>

<style scoped>
.students-page {
  display: grid;
  gap: 16px;
}

.page-header,
.table-card,
.not-found {
  padding: 16px;
}

.kicker,
.hint {
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

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
}

.table-card {
  overflow-x: auto;
}

.empty-state {
  margin: 0;
  color: var(--muted);
}

@media (max-width: 760px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
