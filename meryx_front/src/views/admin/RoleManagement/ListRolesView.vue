<template>
  <div class="roles-page">
    <header class="page-header card">
      <div>
        <p class="kicker">{{ t.roleManagement }}</p>
        <h1>{{ t.listTitle }}</h1>
        <p class="subtitle">{{ t.listSubtitle }}</p>
      </div>
      <RouterLink class="primary-btn add-btn" :to="{ name: 'adminRoleCreate' }">
        <i class="bi bi-plus-lg"></i>
        <span>{{ t.createRole }}</span>
      </RouterLink>
    </header>

    <section class="summary-grid">
      <article class="summary-card card">
        <p>{{ t.totalRoles }}</p>
        <strong>{{ roles.length }}</strong>
      </article>
      <article class="summary-card card">
        <p>{{ t.totalUsersAssigned }}</p>
        <strong>{{ totalAssignments }}</strong>
      </article>
      <article class="summary-card card">
        <p>{{ t.totalSystemUsers }}</p>
        <strong>{{ users.length }}</strong>
      </article>
    </section>

    <section class="card table-card">
      <p v-if="isLoadingRoles" class="subtitle">{{ t.loadingRoles }}</p>
      <p v-else-if="errorMessage" class="error-text">{{ errorMessage }}</p>
      <table>
        <thead>
          <tr>
            <th>{{ t.roleName }}</th>
            <th>{{ t.description }}</th>
            <th>{{ t.permissions }}</th>
            <th>{{ t.assignedUsers }}</th>
            <th>{{ t.actions }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="role in roles" :key="role.id">
            <td>
              <strong>{{ role.name }}</strong>
            </td>
            <td>{{ role.description }}</td>
            <td>{{ role.permissions.length }}</td>
            <td>{{ role.assignedUserIds.length }}</td>
            <td class="row-actions">
              <RouterLink class="text-btn" :to="{ name: 'adminRoleEdit', params: { id: role.id } }">
                {{ t.editAssign }}
              </RouterLink>
            </td>
          </tr>
        </tbody>
      </table>
    </section>
  </div>
</template>

<script>
import api from '@/api/axios'
import { RouterLink } from 'vue-router'
import { useUiPreferences } from '@/compasables/useUiPreferences'

const translations = {
  en: {
    roleManagement: 'Role Management',
    listTitle: 'Roles List',
    listSubtitle: 'Browse existing roles and open a dedicated page to edit or assign users.',
    createRole: 'Create Role',
    totalRoles: 'Total Roles',
    totalUsersAssigned: 'Total User Assignments',
    totalSystemUsers: 'System Users',
    roleName: 'Role Name',
    description: 'Description',
    permissions: 'Permissions',
    assignedUsers: 'Assigned Users',
    actions: 'Actions',
    editAssign: 'Edit / Assign',
    loadingRoles: 'Loading roles...',
    loadingFailed: 'Unable to load roles. Please refresh the page.',
  },
  fr: {
    roleManagement: 'Gestion des roles',
    listTitle: 'Liste des roles',
    listSubtitle:
      'Consultez les roles existants puis ouvrez une page dediee pour modifier et assigner les utilisateurs.',
    createRole: 'Creer un role',
    totalRoles: 'Nombre de roles',
    totalUsersAssigned: 'Total des attributions',
    totalSystemUsers: 'Utilisateurs du systeme',
    roleName: 'Nom du role',
    description: 'Description',
    permissions: 'Permissions',
    assignedUsers: 'Utilisateurs assignes',
    actions: 'Actions',
    editAssign: 'Modifier / Assigner',
    loadingRoles: 'Chargement des roles...',
    loadingFailed: 'Impossible de charger les roles. Veuillez actualiser la page.',
  },
}

const normalizeApiData = (data) => {
  if (typeof data !== 'string') return data

  try {
    return JSON.parse(data)
  } catch {
    return data
  }
}

const extractCollection = (data) => {
  const normalized = normalizeApiData(data)

  if (Array.isArray(normalized)) return normalized
  if (Array.isArray(normalized?.['hydra:member'])) return normalized['hydra:member']
  if (Array.isArray(normalized?.items)) return normalized.items
  return []
}

const mapRole = (item) => ({
  id: Number(item.id),
  name: item.name || '',
  description: item.description || '',
  permissions: Array.isArray(item.permission)
    ? item.permission
    : Array.isArray(item.permissions)
      ? item.permissions
      : [],
  assignedUserIds: Array.isArray(item.assignedUserIds)
    ? item.assignedUserIds.map((value) => Number(value))
    : [],
})

const mapUser = (item) => ({
  id: Number(item.id),
  name: item.name || item.email || `User #${item.id}`,
})

export default {
  name: 'ListRolesView',
  components: {
    RouterLink,
  },
  data() {
    const { locale, initializeUiPreferences } = useUiPreferences()
    initializeUiPreferences()

    return {
      roles: [],
      users: [],
      isLoadingRoles: false,
      lastRoleError: '',
      errorMessage: '',
      localeRef: locale,
      translations,
    }
  },
  computed: {
    t() {
      return this.translations[this.localeRef.value] || this.translations.en
    },
    totalAssignments() {
      return this.roles.reduce((sum, role) => sum + role.assignedUserIds.length, 0)
    },
  },
  methods: {
    getApiErrorMessage(error, fallback) {
      const status = error?.response?.status
      const payload = normalizeApiData(error?.response?.data)
      const detail = payload?.detail || payload?.message || error?.message

      if (status && detail) return `[${status}] ${detail}`
      if (status) return `[${status}] ${fallback}`
      if (detail) return detail
      return fallback
    },
    async fetchRoles() {
      this.isLoadingRoles = true
      this.lastRoleError = ''

      try {
        const response = await api.listItems('userTypes')
        this.roles = extractCollection(response.data).map(mapRole)
        this.errorMessage = ''
      } catch (error) {
        this.lastRoleError = this.getApiErrorMessage(error, 'Unable to load roles.')
        this.errorMessage = this.lastRoleError || this.t.loadingFailed
      } finally {
        this.isLoadingRoles = false
      }
    },
    async fetchUsers() {
      try {
        const response = await api.listItems('users')
        this.users = extractCollection(response.data).map(mapUser)
      } catch {
        this.users = []
      }
    },
  },
  async created() {
    await Promise.all([this.fetchRoles(), this.fetchUsers()])
  },
}
</script>

<style scoped>
.roles-page {
  display: grid;
  gap: 16px;
}

.page-header {
  padding: 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 14px;
}

.kicker,
.subtitle,
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
  margin: 4px 0 6px;
}

.subtitle {
  color: var(--muted);
  max-width: 60ch;
}

.error-text {
  margin: 8px 10px 0;
  color: #c92a2a;
  font-weight: 600;
}

.add-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 14px;
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
  font-size: 1.6rem;
  color: var(--heading);
}

.table-card {
  padding: 10px;
  overflow-x: auto;
}

.row-actions {
  white-space: nowrap;
}

@media (max-width: 760px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
