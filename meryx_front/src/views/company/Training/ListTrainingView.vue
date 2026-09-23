<template>
  <div class="training-list-page">
    <section class="page-header">
      <div>
        <p class="eyebrow">{{ t.eyebrow }}</p>
        <h1>{{ t.title }}</h1>
        <p class="intro">{{ t.subtitle }}</p>
      </div>

      <button class="primary-btn" type="button" @click="goToCreate">
        <i class="bi bi-plus-lg"></i>
        {{ t.createTraining }}
      </button>
    </section>

    <section class="toolbar-card">
      <div class="search-box">
        <i class="bi bi-search"></i>
        <input v-model="search" type="search" :placeholder="t.searchPlaceholder" />
      </div>

      <div class="toolbar-filters">
        <label>
          <span>{{ t.type }}</span>
          <select v-model="typeFilter">
            <option value="all">{{ t.allTypes }}</option>
            <option v-for="option in typeOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
        </label>

        <label>
          <span>{{ t.status }}</span>
          <select v-model="statusFilter">
            <option value="all">{{ t.allStatuses }}</option>
            <option value="Open">{{ t.open }}</option>
            <option value="Closed">{{ t.closed }}</option>
            <option value="Draft">{{ t.draft }}</option>
          </select>
        </label>
      </div>
    </section>

    <section class="cards-grid" v-if="filteredTrainings.length">
      <article v-for="training in filteredTrainings" :key="training.id" class="training-card">
        <div class="card-top">
          <div>
            <strong>{{ training.title }}</strong>
            <small>{{ training.location }} · {{ training.mode }}</small>
          </div>
          <span class="badge" :class="statusClass(training.status)">{{
            statusLabel(training.status)
          }}</span>
        </div>

        <p class="training-type">{{ typeLabel(training.type) }} · {{ training.duration }}</p>
        <p class="description">{{ training.description }}</p>

        <div class="meta-row">
          <span>{{ training.postedDate }}</span>
          <span>{{ training.seats }} {{ t.seats }}</span>
          <span>{{ formatDate(training.startDate) }} - {{ formatDate(training.endDate) }}</span>
        </div>

        <div class="tags-row">
          <span v-for="req in training.requirements" :key="req">{{ req }}</span>
        </div>

        <div class="actions-row">
          <button class="ghost-btn" type="button" @click="editTraining(training)">
            {{ t.edit }}
          </button>
          <button class="ghost-btn" type="button" @click="duplicateTraining(training)">
            {{ t.duplicate }}
          </button>
        </div>
      </article>
    </section>

    <section v-else class="empty-state">{{ t.empty }}</section>
  </div>
</template>

<script setup>
import { computed, inject, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useCompanyTrainings } from '@/compasables/useCompanyTrainings'

const router = useRouter()
const locale = inject('locale', ref('en'))
const { fetchTrainings, companyTrainings, getCurrentCompanyId } = useCompanyTrainings()
const companyId = getCurrentCompanyId()
const trainings = ref([])
const search = ref('')
const typeFilter = ref('all')
const statusFilter = ref('all')
const copy = {
  en: {
    eyebrow: 'Company / Training',
    title: 'Training List',
    subtitle: 'Manage all training programs posted by your company.',
    createTraining: 'Create Training',
    searchPlaceholder: 'Search title, location, skills',
    type: 'Type',
    allTypes: 'All types',
    status: 'Status',
    allStatuses: 'All statuses',
    open: 'Open',
    closed: 'Closed',
    draft: 'Draft',
    seats: 'seats',
    edit: 'Edit',
    duplicate: 'Duplicate',
    empty: 'No training programs match your filters.',
    editHint: 'Edit training:',
    editInfo: 'This can be connected to an edit page.',
    types: {
      bootcamp: 'Bootcamp',
      internship: 'Internship Training',
      graduate: 'Graduate Program',
      certification: 'Certification Track',
    },
  },
  fr: {
    eyebrow: 'Entreprise / Formation',
    title: 'Liste des formations',
    subtitle: 'Gerez toutes les formations publiees par votre entreprise.',
    createTraining: 'Creer une formation',
    searchPlaceholder: 'Rechercher titre, lieu, competences',
    type: 'Type',
    allTypes: 'Tous les types',
    status: 'Statut',
    allStatuses: 'Tous les statuts',
    open: 'Ouvert',
    closed: 'Ferme',
    draft: 'Brouillon',
    seats: 'places',
    edit: 'Modifier',
    duplicate: 'Dupliquer',
    empty: 'Aucune formation ne correspond a vos filtres.',
    editHint: 'Modifier la formation :',
    editInfo: 'Cette action peut etre reliee a une page de modification.',
    types: {
      bootcamp: 'Bootcamp',
      internship: 'Formation stage',
      graduate: 'Programme diplome',
      certification: 'Parcours certifiant',
    },
  },
}

const t = computed(() => copy[locale.value] || copy.en)

const typeOptions = computed(() => [
  { value: 'Bootcamp', label: t.value.types.bootcamp },
  { value: 'Internship Training', label: t.value.types.internship },
  { value: 'Graduate Program', label: t.value.types.graduate },
  { value: 'Certification Track', label: t.value.types.certification },
])

const currentTrainings = computed(() =>
  trainings.value.filter((item) => Number(item.companyId) === Number(companyId)),
)

const filteredTrainings = computed(() => {
  const term = search.value.trim().toLowerCase()
  return currentTrainings.value.filter((training) => {
    if (typeFilter.value !== 'all' && training.type !== typeFilter.value) return false
    if (statusFilter.value !== 'all' && (training.status || 'Open') !== statusFilter.value)
      return false
    if (!term) return true

    return [
      training.title,
      training.location,
      training.description,
      (training.requirements || []).join(' '),
    ]
      .join(' ')
      .toLowerCase()
      .includes(term)
  })
})

const statusClass = (status) => {
  if (status === 'Closed') return 'closed'
  if (status === 'Draft') return 'draft'
  return 'open'
}

const statusLabel = (status) => {
  if (status === 'Closed') return t.value.closed
  if (status === 'Draft') return t.value.draft
  return t.value.open
}

const typeLabel = (value) => {
  if (value === 'Internship Training') return t.value.types.internship
  if (value === 'Graduate Program') return t.value.types.graduate
  if (value === 'Certification Track') return t.value.types.certification
  return t.value.types.bootcamp
}

const formatDate = (value) => {
  if (!value) return '--'
  return new Date(value).toLocaleDateString(locale.value === 'fr' ? 'fr-FR' : 'en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const loadTrainings = async () => {
  const data = await fetchTrainings()
  trainings.value = data
}

const editTraining = (training) => {
  if (!training?.id) return
  router.push({ name: 'companyEditTraining', params: { id: training.id } })
}

const duplicateTraining = (training) => {
  trainings.value.unshift({
    ...training,
    id: Date.now(),
    title: `${training.title} (Copy)`,
    status: 'Draft',
    postedDate: 'Just now',
  })
}

const goToCreate = () => {
  router.push({ name: 'companyCreateTraining' })
}

onMounted(() => {
  loadTrainings()
})
</script>

<style scoped>
.training-list-page {
  display: grid;
  gap: 14px;
}

.page-header,
.toolbar-card,
.training-card,
.empty-state {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 18px;
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
}

.page-header {
  padding: 14px;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 10px;
}

.eyebrow {
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: var(--primary);
  font-size: 0.75rem;
  font-weight: 700;
}

.page-header h1,
.card-top strong {
  margin: 0;
  color: var(--text);
}

.intro,
.card-top small,
.description,
.meta-row,
label span,
.empty-state {
  color: var(--muted);
}

.toolbar-card {
  padding: 12px;
  display: grid;
  gap: 10px;
}

.search-box {
  display: flex;
  align-items: center;
  gap: 8px;
  border: 1px solid var(--border);
  border-radius: 12px;
  background: var(--surface-soft);
  padding: 10px 12px;
}

.search-box input,
select {
  width: 100%;
  border: none;
  outline: none;
  background: transparent;
  color: var(--text);
}

.toolbar-filters {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 8px;
}

label {
  display: grid;
  gap: 6px;
}

select {
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 10px 12px;
  background: var(--surface);
}

.cards-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
}

.training-card {
  padding: 12px;
  display: grid;
  gap: 8px;
}

.card-top {
  display: flex;
  justify-content: space-between;
  gap: 10px;
  align-items: flex-start;
}

.badge {
  border-radius: 999px;
  padding: 5px 10px;
  font-size: 0.74rem;
  font-weight: 700;
}

.badge.open {
  color: #0f766e;
  background: rgba(20, 184, 166, 0.14);
}

.badge.closed {
  color: #b91c1c;
  background: rgba(254, 205, 211, 0.5);
}

.badge.draft {
  color: #854d0e;
  background: rgba(234, 179, 8, 0.2);
}

.training-type {
  margin: 0;
  color: var(--primary);
  font-weight: 600;
}

.description {
  margin: 0;
  line-height: 1.55;
}

.meta-row,
.tags-row,
.actions-row {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.tags-row span {
  border-radius: 999px;
  padding: 5px 9px;
  font-size: 0.76rem;
  background: rgba(15, 118, 110, 0.1);
  color: #0f766e;
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
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.ghost-btn {
  border: 1px solid var(--border);
  color: var(--text);
  background: var(--surface);
}

.empty-state {
  padding: 18px;
  text-align: center;
}

@media (max-width: 980px) {
  .page-header {
    flex-direction: column;
  }

  .cards-grid,
  .toolbar-filters {
    grid-template-columns: 1fr;
  }

  .page-header .primary-btn {
    width: 100%;
    justify-content: center;
  }
}
</style>
