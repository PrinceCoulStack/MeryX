<template>
  <div class="company-management-page">
    <header class="page-header">
      <div>
        <p class="eyebrow">University / Company Management</p>
        <h1>Partner Company Workspace</h1>
        <p class="intro">
          Manage partner companies, monitor collaboration status, and coordinate opportunities for
          students from one place.
        </p>
      </div>

      <div class="header-actions">
        <button class="secondary-btn" type="button">
          <i class="bi bi-download"></i>
          Export
        </button>
        <button class="primary-btn" type="button">
          <i class="bi bi-plus-lg"></i>
          Add Company
        </button>
      </div>
    </header>

    <section class="kpi-grid">
      <article class="kpi-card">
        <span>Total Companies</span>
        <strong>{{ totalCompanies }}</strong>
      </article>
      <article class="kpi-card">
        <span>Active Partners</span>
        <strong>{{ activePartners }}</strong>
      </article>
      <article class="kpi-card">
        <span>Pending Approval</span>
        <strong>{{ pendingPartners }}</strong>
      </article>
      <article class="kpi-card">
        <span>Active Opportunities</span>
        <strong>{{ totalOpportunities }}</strong>
      </article>
    </section>

    <section class="toolbar-card">
      <div class="search-box">
        <i class="bi bi-search"></i>
        <input
          v-model="filters.query"
          type="search"
          placeholder="Search by company, industry, location, contact"
        />
      </div>

      <div class="filters-grid">
        <select v-model="filters.status">
          <option value="all">All status</option>
          <option value="active">Active partner</option>
          <option value="pending">Pending approval</option>
          <option value="inactive">Inactive</option>
          <option value="rejected">Rejected</option>
        </select>

        <select v-model="filters.industry">
          <option value="all">All industries</option>
          <option v-for="industry in industryOptions" :key="industry" :value="industry">
            {{ industry }}
          </option>
        </select>

        <select v-model="filters.location">
          <option value="all">All locations</option>
          <option v-for="location in locationOptions" :key="location" :value="location">
            {{ location }}
          </option>
        </select>

        <button class="secondary-btn" type="button" @click="resetFilters">
          <i class="bi bi-arrow-counterclockwise"></i>
          Reset
        </button>
      </div>
    </section>

    <p v-if="isLoadingCompanies" class="info-line">Loading companies...</p>
    <p v-else-if="companyAdminError" class="error-line">{{ companyAdminError }}</p>

    <section class="workspace-grid">
      <article class="company-list-card">
        <div class="card-head">
          <h2>Companies</h2>
          <span>{{ filteredCompanies.length }} results</span>
        </div>

        <div class="company-list">
          <button
            v-for="company in filteredCompanies"
            :key="company.id"
            class="company-item"
            :class="{ active: selectedCompany?.id === company.id }"
            type="button"
            @click="selectedCompanyId = company.id"
          >
            <div class="company-avatar">{{ company.name.charAt(0) }}</div>

            <div class="company-core">
              <strong>{{ company.name }}</strong>
              <small>{{ company.industry }} · {{ company.location }}</small>
              <div class="mini-tags">
                <span>{{ company.contactName }}</span>
                <span>{{ company.totalStudents }} students</span>
              </div>
            </div>

            <span class="status-pill" :class="statusClass(company.statusKey)">{{
              company.statusLabel
            }}</span>
          </button>

          <div v-if="!filteredCompanies.length" class="empty-state">
            No companies match filters.
          </div>
        </div>
      </article>

      <article class="company-details-card" v-if="selectedCompany">
        <div class="card-head details-head">
          <div class="head-left">
            <div class="company-avatar large">{{ selectedCompany.name.charAt(0) }}</div>
            <div>
              <h2>{{ selectedCompany.name }}</h2>
              <p>{{ selectedCompany.industry }} · {{ selectedCompany.location }}</p>
            </div>
          </div>

          <span class="status-pill" :class="statusClass(selectedCompany.statusKey)">
            {{ selectedCompany.statusLabel }}
          </span>
        </div>

        <div class="overview-grid">
          <article>
            <span>Contact Person</span>
            <strong>{{ selectedCompany.contactName }}</strong>
          </article>
          <article>
            <span>Contact Email</span>
            <strong>{{ selectedCompany.contactEmail }}</strong>
          </article>
          <article>
            <span>Phone</span>
            <strong>{{ selectedCompany.phone }}</strong>
          </article>
          <article>
            <span>Academic Year</span>
            <strong>{{ selectedCompany.academicYear }}</strong>
          </article>
          <article>
            <span>Total Students Linked</span>
            <strong>{{ selectedCompany.totalStudents }}</strong>
          </article>
          <article>
            <span>Open Opportunities</span>
            <strong>{{ selectedCompany.openOpportunities }}</strong>
          </article>
        </div>

        <section class="section-box">
          <h3>Partnership Notes</h3>
          <p>{{ selectedCompany.notes }}</p>
        </section>

        <section class="section-box">
          <div class="section-head-row">
            <h3>Opportunity Slots</h3>
            <span>{{ selectedCompany.opportunities.length }} items</span>
          </div>

          <div class="opportunity-table">
            <div class="table-row table-head">
              <span>Title</span>
              <span>Type</span>
              <span>Deadline</span>
              <span>Status</span>
            </div>
            <div v-for="slot in selectedCompany.opportunities" :key="slot.id" class="table-row">
              <strong>{{ slot.title }}</strong>
              <span>{{ slot.type }}</span>
              <span>{{ slot.deadline }}</span>
              <span class="status-pill" :class="slot.isOpen ? 'active' : 'inactive'">
                {{ slot.statusLabel }}
              </span>
            </div>
          </div>
        </section>

        <div class="actions-row">
          <button class="secondary-btn" type="button">Edit Profile</button>
          <button class="secondary-btn" type="button">Suspend Partnership</button>
          <button class="primary-btn" type="button" @click="openMessageCenter">
            <i class="bi bi-chat-dots"></i>
            Open Messaging
          </button>
        </div>
      </article>

      <article class="company-details-card" v-else>
        <div class="empty-state details-empty">Select a company to view management details.</div>
      </article>
    </section>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useCompanyAdmin } from '@/compasables/useCompanyAdmin'

const router = useRouter()
const { companies, fetchCompanies, isLoadingCompanies, companyAdminError } = useCompanyAdmin()

const statusFromRaw = (status) => {
  const value = String(status || '')
    .trim()
    .toLowerCase()
  if (value.includes('approve') || value.includes('active')) return 'active'
  if (value.includes('reject')) return 'rejected'
  if (value.includes('inactive') || value.includes('suspend')) return 'inactive'
  if (value.includes('pending')) return 'pending'
  return 'pending'
}

const statusLabel = (statusKey) => {
  if (statusKey === 'active') return 'Active partner'
  if (statusKey === 'rejected') return 'Rejected'
  if (statusKey === 'inactive') return 'Inactive'
  return 'Pending approval'
}

const normalizeOpportunity = (item, index) => {
  const rawStatus = String(item?.status || '').toLowerCase()
  const isOpen = rawStatus.includes('open') || rawStatus.includes('active') || rawStatus === ''

  return {
    id: item?.id || `${index}-${item?.title || 'opportunity'}`,
    title: item?.title || item?.name || 'Opportunity',
    type: item?.type || item?.category || 'Opportunity',
    deadline: item?.deadline || item?.deadlineAt || item?.closingDate || '-',
    isOpen,
    statusLabel: isOpen ? 'Open' : 'Closed',
  }
}

const normalizeCompany = (company) => {
  const raw = company?.raw || {}
  const statusKey = statusFromRaw(company?.status || raw?.status)
  const opportunities = Array.isArray(raw?.opportunities)
    ? raw.opportunities.map(normalizeOpportunity)
    : []

  return {
    id: company.id,
    name: company.name || 'Company',
    industry: company.sector || raw.sector || raw.industry || '-',
    location: company.country || raw.location || raw.city || '-',
    statusKey,
    statusLabel: statusLabel(statusKey),
    contactName:
      raw.contactName || raw.contactPerson || raw.userId?.fullName || raw.user?.fullName || '-',
    contactEmail: company.email || raw.contactEmail || '-',
    phone: company.phone || raw.phone || '-',
    academicYear: raw.academicYear || raw.enrollmentYear || '-',
    totalStudents: Number(raw.totalStudents || raw.linkedStudents || raw.studentCount || 0),
    openOpportunities: opportunities.filter((item) => item.isOpen).length,
    notes: company.reviewNotes || company.description || raw.notes || '-',
    opportunities,
  }
}

const viewCompanies = computed(() => companies.value.map(normalizeCompany))

const filters = ref({
  query: '',
  status: 'all',
  industry: 'all',
  location: 'all',
})

const selectedCompanyId = ref(null)

const filteredCompanies = computed(() => {
  const query = filters.value.query.trim().toLowerCase()

  return viewCompanies.value.filter((company) => {
    const matchesQuery =
      !query ||
      company.name.toLowerCase().includes(query) ||
      company.industry.toLowerCase().includes(query) ||
      company.location.toLowerCase().includes(query) ||
      company.contactName.toLowerCase().includes(query)

    const matchesStatus =
      filters.value.status === 'all' || company.statusKey === filters.value.status
    const matchesIndustry =
      filters.value.industry === 'all' || company.industry === filters.value.industry
    const matchesLocation =
      filters.value.location === 'all' || company.location === filters.value.location

    return matchesQuery && matchesStatus && matchesIndustry && matchesLocation
  })
})

const selectedCompany = computed(() => {
  if (selectedCompanyId.value === null) return filteredCompanies.value[0] || null
  return filteredCompanies.value.find((item) => item.id === selectedCompanyId.value) || null
})

const industryOptions = computed(() => [
  ...new Set(viewCompanies.value.map((company) => company.industry).filter(Boolean)),
])
const locationOptions = computed(() => [
  ...new Set(viewCompanies.value.map((company) => company.location).filter(Boolean)),
])

const totalCompanies = computed(() => viewCompanies.value.length)
const activePartners = computed(
  () => viewCompanies.value.filter((company) => company.statusKey === 'active').length,
)
const pendingPartners = computed(
  () => viewCompanies.value.filter((company) => company.statusKey === 'pending').length,
)
const totalOpportunities = computed(() =>
  viewCompanies.value.reduce((sum, company) => sum + company.openOpportunities, 0),
)

const statusClass = (status) => {
  if (status === 'pending') return 'pending'
  if (status === 'inactive' || status === 'rejected') return 'inactive'
  return 'active'
}

const resetFilters = () => {
  filters.value = {
    query: '',
    status: 'all',
    industry: 'all',
    location: 'all',
  }
}

const openMessageCenter = () => {
  router.push({ name: 'messageCompany' })
}

watch(
  filteredCompanies,
  (rows) => {
    if (!rows.length) {
      selectedCompanyId.value = null
      return
    }

    const exists = rows.some((item) => item.id === selectedCompanyId.value)
    if (!exists) {
      selectedCompanyId.value = rows[0].id
    }
  },
  { immediate: true },
)

onMounted(() => {
  fetchCompanies().catch(() => {})
})
</script>

<style scoped>
.company-management-page {
  display: grid;
  gap: 20px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
}

.eyebrow {
  margin: 0 0 6px;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  color: var(--primary);
  font-size: 0.78rem;
  font-weight: 700;
}

.page-header h1,
.card-head h2,
.section-box h3 {
  margin: 0;
  color: var(--text);
}

.intro {
  margin: 10px 0 0;
  color: var(--muted);
  max-width: 780px;
}

.info-line,
.error-line {
  margin: 0;
  color: var(--muted);
}

.error-line {
  color: #b91c1c;
}

.header-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.primary-btn,
.secondary-btn,
.company-item {
  border: none;
  border-radius: 14px;
  cursor: pointer;
}

.primary-btn {
  background: var(--primary);
  color: white;
  padding: 12px 16px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.secondary-btn {
  background: var(--surface);
  color: var(--text);
  border: 1px solid var(--border);
  padding: 12px 16px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.kpi-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 14px;
}

.kpi-card,
.toolbar-card,
.company-list-card,
.company-details-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 22px;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
}

.kpi-card {
  padding: 20px;
}

.kpi-card span,
.card-head span,
.overview-grid span,
.section-box p,
.table-row span,
.company-core small {
  color: var(--muted);
}

.kpi-card strong {
  margin-top: 10px;
  display: block;
  color: var(--text);
  font-size: 1.8rem;
}

.toolbar-card {
  padding: 16px;
  display: grid;
  gap: 14px;
}

.search-box {
  display: flex;
  align-items: center;
  gap: 10px;
  border-radius: 14px;
  border: 1px solid var(--border);
  background: var(--surface-soft);
  padding: 12px 14px;
}

.search-box input {
  width: 100%;
  border: none;
  outline: none;
  background: transparent;
  color: var(--text);
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 10px;
}

.filters-grid select {
  width: 100%;
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 11px 12px;
  background: var(--surface);
  color: var(--text);
}

.workspace-grid {
  display: grid;
  grid-template-columns: 1fr 1.3fr;
  gap: 16px;
}

.company-list-card,
.company-details-card {
  padding: 16px;
  display: grid;
  gap: 14px;
  min-width: 0;
}

.card-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
}

.company-list {
  display: grid;
  gap: 10px;
  max-height: 620px;
  overflow: auto;
}

.company-item {
  width: 100%;
  text-align: left;
  display: grid;
  grid-template-columns: 46px minmax(0, 1fr) auto;
  gap: 12px;
  align-items: center;
  padding: 14px;
  background: var(--surface-soft);
  border: 1px solid transparent;
}

.company-item.active {
  border-color: rgba(6, 170, 197, 0.45);
  background: rgba(6, 170, 197, 0.09);
}

.company-avatar {
  width: 46px;
  height: 46px;
  border-radius: 14px;
  background: rgba(6, 170, 197, 0.16);
  color: var(--primary);
  font-weight: 700;
  display: grid;
  place-items: center;
}

.company-avatar.large {
  width: 62px;
  height: 62px;
  border-radius: 18px;
  font-size: 1.2rem;
}

.company-core {
  min-width: 0;
  display: grid;
  gap: 5px;
}

.company-core strong,
.overview-grid strong,
.table-row strong {
  color: var(--text);
}

.mini-tags,
.actions-row {
  display: flex;
  flex-wrap: wrap;
  gap: 7px;
}

.mini-tags span {
  background: rgba(6, 170, 197, 0.12);
  color: var(--primary);
  border-radius: 999px;
  padding: 5px 10px;
  font-size: 0.8rem;
}

.status-pill {
  border-radius: 999px;
  padding: 8px 12px;
  font-size: 0.8rem;
  font-weight: 700;
  white-space: nowrap;
}

.status-pill.active {
  background: rgba(20, 184, 166, 0.14);
  color: #0f766e;
}

.status-pill.pending {
  background: rgba(234, 179, 8, 0.16);
  color: #854d0e;
}

.status-pill.inactive {
  background: rgba(239, 68, 68, 0.12);
  color: #b91c1c;
}

.details-head {
  align-items: flex-start;
}

.head-left {
  display: flex;
  gap: 14px;
  align-items: center;
}

.head-left p {
  margin: 6px 0 0;
  color: var(--muted);
}

.overview-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 10px;
}

.overview-grid article {
  padding: 12px;
  border-radius: 14px;
  background: var(--surface-soft);
  display: grid;
  gap: 6px;
}

.section-box {
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 14px;
  display: grid;
  gap: 10px;
}

.section-box p {
  margin: 0;
  line-height: 1.6;
}

.section-head-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
}

.opportunity-table {
  display: grid;
  gap: 8px;
}

.table-row {
  display: grid;
  grid-template-columns: 1.4fr 0.8fr 0.8fr auto;
  gap: 10px;
  align-items: center;
  padding: 12px;
  border-radius: 12px;
  background: var(--surface-soft);
}

.table-row.table-head {
  background: transparent;
  border: 1px dashed var(--border);
}

.empty-state {
  text-align: center;
  padding: 22px;
  color: var(--muted);
  border-radius: 14px;
  background: var(--surface-soft);
}

.details-empty {
  min-height: 260px;
  display: grid;
  place-items: center;
}

@media (max-width: 1180px) {
  .workspace-grid {
    grid-template-columns: 1fr;
  }

  .kpi-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 760px) {
  .page-header {
    flex-direction: column;
  }

  .filters-grid,
  .overview-grid,
  .table-row {
    grid-template-columns: 1fr;
  }

  .kpi-grid {
    grid-template-columns: 1fr;
  }

  .company-item {
    grid-template-columns: 46px minmax(0, 1fr);
  }

  .company-item .status-pill {
    grid-column: 1 / -1;
    justify-self: start;
  }

  .header-actions,
  .actions-row {
    width: 100%;
  }

  .header-actions button,
  .actions-row button,
  .filters-grid .secondary-btn {
    width: 100%;
    justify-content: center;
  }
}
</style>
