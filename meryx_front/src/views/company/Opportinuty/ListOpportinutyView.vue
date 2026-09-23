<template>
  <div class="opportunity-list-page">
    <section class="page-header">
      <div>
        <p class="eyebrow">Company / Opportunities</p>
        <h1>Opportunity List</h1>
        <p class="intro">Review and manage all roles published by your company.</p>
      </div>

      <button class="primary-btn" type="button" @click="goToCreate">
        <i class="bi bi-plus-lg"></i>
        Create Opportunity
      </button>
    </section>

    <section class="toolbar-card">
      <div class="search-box">
        <i class="bi bi-search"></i>
        <input v-model="search" type="search" placeholder="Search title, location, department" />
      </div>

      <div class="toolbar-filters">
        <label>
          <span>Type</span>
          <select v-model="typeFilter">
            <option value="all">All types</option>
            <option v-for="option in typeOptions" :key="option" :value="option">
              {{ option }}
            </option>
          </select>
        </label>

        <label>
          <span>Status</span>
          <select v-model="statusFilter">
            <option value="all">All statuses</option>
            <option value="Open">Open</option>
            <option value="Closed">Closed</option>
            <option value="Draft">Draft</option>
          </select>
        </label>
      </div>
    </section>

    <section class="cards-grid" v-if="filteredOpportunities.length">
      <article
        v-for="opportunity in filteredOpportunities"
        :key="opportunity.id"
        class="opportunity-card"
      >
        <div class="card-top">
          <div>
            <strong>{{ opportunity.title }} . </strong>

            <small>{{ opportunity.department }} · {{ opportunity.location }}</small>
          </div>
          <span class="badge" :class="statusClass(opportunity.status)">
            {{ opportunity.status }}
          </span>
        </div>

        <p class="opportunity-type">{{ opportunity.type }} · {{ opportunity.remoteType }}</p>
        <p class="description">{{ opportunity.description }}</p>

        <div class="meta-row">
          <span>{{ opportunity.postedDate }}</span>
          <span>{{ opportunity.salary }}</span>
        </div>

        <div class="tags-row">
          <span v-for="requirement in opportunity.requirements" :key="requirement">{{
            requirement
          }}</span>
        </div>

        <div class="actions-row">
          <button class="ghost-btn" type="button" @click="viewCandidates(opportunity)">
            <i class="bi bi-people"></i>
            View Candidates
          </button>
          <button class="ghost-btn" type="button" @click="editOpportunity(opportunity)">
            Edit
          </button>
          <button class="ghost-btn" type="button" @click="duplicateOpportunity(opportunity)">
            Duplicate
          </button>
        </div>
      </article>
    </section>

    <section v-else class="empty-state">No opportunities match your filters.</section>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useCompanyOpportunities } from '@/compasables/useCompanyOpportunities'

const router = useRouter()
const search = ref('')
const typeFilter = ref('all')
const statusFilter = ref('all')
const typeOptions = ['Internship', 'Job - Full time', 'Job - Part time', 'Job - Remote']

const { companyOpportunities, fetchOpportunities } = useCompanyOpportunities()

onMounted(() => {
  fetchOpportunities().catch(() => {})
})

const filteredOpportunities = computed(() => {
  const term = search.value.trim().toLowerCase()
  return companyOpportunities.value.filter((opportunity) => {
    if (typeFilter.value !== 'all' && opportunity.type !== typeFilter.value) return false
    if (statusFilter.value !== 'all' && (opportunity.status || 'Open') !== statusFilter.value)
      return false

    if (!term) return true

    return [
      opportunity.title,
      opportunity.department,
      opportunity.location,
      opportunity.description,
      (opportunity.requirements || []).join(' '),
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

const editOpportunity = (opportunity) => {
  if (!opportunity?.id) return
  router.push({ name: 'companyEditOpportunity', params: { id: opportunity.id } })
}

const viewCandidates = (opportunity) => {
  if (!opportunity?.id) return
  router.push({ name: 'opportunityCandidates', params: { id: opportunity.id } })
}

const duplicateOpportunity = (opportunity) => {
  if (!opportunity?.id) return
  window.alert(`Duplicate opportunity: ${opportunity.title}. Connect this to the backend later.`)
}

const goToCreate = () => {
  router.push({ name: 'companyCreateOpportunity' })
}
</script>

<style scoped>
.opportunity-list-page {
  display: grid;
  gap: 14px;
}

.page-header,
.toolbar-card,
.opportunity-card,
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

.opportunity-card {
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

.opportunity-type {
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
