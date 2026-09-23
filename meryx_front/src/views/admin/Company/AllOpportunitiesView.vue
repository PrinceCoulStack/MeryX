<template>
  <div class="all-posts-page">
    <header class="card page-header">
      <div>
        <p class="kicker">{{ t.systemOverview }}</p>
        <h1>{{ t.allOpportunities }}</h1>
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
            <th>{{ t.type }}</th>
            <th>{{ t.location }}</th>
            <th>{{ t.postedAt }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="opportunity in opportunityPosts" :key="opportunity.id">
            <td>
              <strong>{{ opportunity.title }}</strong>
            </td>
            <td>{{ companyName(opportunity.companyId) }}</td>
            <td>{{ opportunity.type }}</td>
            <td>{{ opportunity.location }}</td>
            <td>{{ opportunity.postedAt }}</td>
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
const { opportunityPosts, getCompanyById } = useCompanyAdmin()

const translations = {
  en: {
    systemOverview: 'System Overview',
    allOpportunities: 'All Jobs and Internships Posted',
    hint: 'Complete list of jobs and internships posted by companies on the platform.',
    back: 'Back to Companies',
    title: 'Title',
    company: 'Company',
    type: 'Type',
    location: 'Location',
    postedAt: 'Posted At',
  },
  fr: {
    systemOverview: 'Vue Systeme',
    allOpportunities: 'Tous les emplois et stages publies',
    hint: 'Liste complete des emplois et stages publies par les entreprises sur la plateforme.',
    back: 'Retour aux entreprises',
    title: 'Titre',
    company: 'Entreprise',
    type: 'Type',
    location: 'Lieu',
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
