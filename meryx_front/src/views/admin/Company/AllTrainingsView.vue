<template>
  <div class="all-posts-page">
    <header class="card page-header">
      <div>
        <p class="kicker">{{ t.systemOverview }}</p>
        <h1>{{ t.allTrainings }}</h1>
        <p class="hint">{{ t.hint }}</p>
      </div>
      <RouterLink class="text-btn" :to="{ name: 'adminCompanies' }">{{ t.back }}</RouterLink>
    </header>

    <section class="card table-card">
      <table>
        <thead>
          <tr>
            <th>{{ t.title }}</th>
            <th>{{ t.company }}</th>
            <th>{{ t.modality }}</th>
            <th>{{ t.duration }}</th>
            <th>{{ t.postedAt }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="training in trainingPosts" :key="training.id">
            <td>
              <strong>{{ training.title }}</strong>
            </td>
            <td>{{ companyName(training.companyId) }}</td>
            <td>{{ training.modality }}</td>
            <td>{{ training.duration }}</td>
            <td>{{ training.postedAt }}</td>
          </tr>
        </tbody>
      </table>
    </section>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useUiPreferences } from '@/compasables/useUiPreferences'
import { useCompanyAdmin } from '@/compasables/useCompanyAdmin'

const { locale } = useUiPreferences()
const { trainingPosts, getCompanyById } = useCompanyAdmin()

const translations = {
  en: {
    systemOverview: 'System Overview',
    allTrainings: 'All Trainings Posted',
    hint: 'Complete list of trainings posted by companies on the platform.',
    back: 'Back to Companies',
    title: 'Title',
    company: 'Company',
    modality: 'Modality',
    duration: 'Duration',
    postedAt: 'Posted At',
  },
  fr: {
    systemOverview: 'Vue Systeme',
    allTrainings: 'Toutes les formations publiees',
    hint: 'Liste complete des formations publiees par les entreprises sur la plateforme.',
    back: 'Retour aux entreprises',
    title: 'Titre',
    company: 'Entreprise',
    modality: 'Modalite',
    duration: 'Duree',
    postedAt: 'Publie le',
  },
}

const t = computed(() => translations[locale.value] || translations.en)

const companyName = (companyId) => getCompanyById(companyId)?.name || 'Unknown'
</script>

<style scoped>
.all-posts-page {
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
  align-items: center;
  gap: 10px;
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

h1 {
  margin: 6px 0 0;
}

.hint {
  margin-top: 8px;
  color: var(--muted);
}

.table-card {
  overflow-x: auto;
}

@media (max-width: 760px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
