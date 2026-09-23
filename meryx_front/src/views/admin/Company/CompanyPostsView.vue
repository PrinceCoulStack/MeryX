<template>
  <div class="company-posts-page" v-if="company">
    <header class="card page-header">
      <div>
        <p class="kicker">{{ t.companyPosts }}</p>
        <h1>{{ company.name }}</h1>
        <p class="hint">{{ company.email }} · {{ company.country }}</p>
      </div>
      <RouterLink class="text-btn" :to="{ name: 'adminCompanies' }">{{
        t.backToCompanies
      }}</RouterLink>
    </header>

    <section class="card panel">
      <div class="panel-head">
        <h2>{{ t.trainingPosts }} ({{ trainings.length }})</h2>
      </div>

      <table v-if="trainings.length">
        <thead>
          <tr>
            <th>{{ t.title }}</th>
            <th>{{ t.modality }}</th>
            <th>{{ t.duration }}</th>
            <th>{{ t.postedAt }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="training in trainings" :key="training.id">
            <td>
              <strong>{{ training.title }}</strong>
            </td>
            <td>{{ training.modality }}</td>
            <td>{{ training.duration }}</td>
            <td>{{ training.postedAt }}</td>
          </tr>
        </tbody>
      </table>
      <p v-else class="empty-state">{{ t.noTraining }}</p>
    </section>

    <section class="card panel">
      <div class="panel-head">
        <h2>{{ t.opportunityPosts }} ({{ opportunities.length }})</h2>
      </div>

      <table v-if="opportunities.length">
        <thead>
          <tr>
            <th>{{ t.title }}</th>
            <th>{{ t.type }}</th>
            <th>{{ t.location }}</th>
            <th>{{ t.postedAt }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="opportunity in opportunities" :key="opportunity.id">
            <td>
              <strong>{{ opportunity.title }}</strong>
            </td>
            <td>{{ opportunity.type }}</td>
            <td>{{ opportunity.location }}</td>
            <td>{{ opportunity.postedAt }}</td>
          </tr>
        </tbody>
      </table>
      <p v-else class="empty-state">{{ t.noOpportunity }}</p>
    </section>
  </div>

  <div v-else class="card not-found">
    <h2>{{ t.companyNotFound }}</h2>
    <RouterLink class="text-btn" :to="{ name: 'adminCompanies' }">{{
      t.backToCompanies
    }}</RouterLink>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useUiPreferences } from '@/compasables/useUiPreferences'
import { useCompanyAdmin } from '@/compasables/useCompanyAdmin'

const route = useRoute()
const { locale } = useUiPreferences()
const { getCompanyById, getCompanyTrainings, getCompanyOpportunities } = useCompanyAdmin()

const company = computed(() => getCompanyById(route.params.id))
const trainings = computed(() => getCompanyTrainings(route.params.id))
const opportunities = computed(() => getCompanyOpportunities(route.params.id))

const translations = {
  en: {
    companyPosts: 'Company Posts',
    backToCompanies: 'Back to Companies',
    trainingPosts: 'Training Posts',
    opportunityPosts: 'Job/Internship Posts',
    title: 'Title',
    modality: 'Modality',
    duration: 'Duration',
    type: 'Type',
    location: 'Location',
    postedAt: 'Posted At',
    noTraining: 'No training posts for this company.',
    noOpportunity: 'No job/internship posts for this company.',
    companyNotFound: 'Company not found.',
  },
  fr: {
    companyPosts: 'Publications Entreprise',
    backToCompanies: 'Retour aux entreprises',
    trainingPosts: 'Publications de formation',
    opportunityPosts: "Publications d'emploi/stage",
    title: 'Titre',
    modality: 'Modalite',
    duration: 'Duree',
    type: 'Type',
    location: 'Lieu',
    postedAt: 'Publie le',
    noTraining: 'Aucune publication de formation pour cette entreprise.',
    noOpportunity: "Aucune publication d'emploi/stage pour cette entreprise.",
    companyNotFound: 'Entreprise introuvable.',
  },
}

const t = computed(() => translations[locale.value] || translations.en)
</script>

<style scoped>
.company-posts-page {
  display: grid;
  gap: 16px;
}

.page-header,
.panel,
.not-found {
  padding: 16px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
}

.kicker,
.hint,
.empty-state {
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

.panel-head {
  margin-bottom: 10px;
}

.empty-state {
  color: var(--muted);
}

@media (max-width: 760px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
