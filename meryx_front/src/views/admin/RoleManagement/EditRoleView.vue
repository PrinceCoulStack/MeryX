<template>
  <div class="edit-role-page" v-if="roleExists">
    <header class="page-header card">
      <div>
        <p class="kicker">{{ t.roleManagement }}</p>
        <h1>{{ t.editTitle }}: {{ form.name || role?.name }}</h1>
        <p class="subtitle">{{ t.editSubtitle }}</p>
      </div>
      <RouterLink class="text-btn" :to="{ name: 'adminRoles' }">{{ t.backToList }}</RouterLink>
    </header>

    <section class="card form-card">
      <h2>{{ t.roleInformation }}</h2>
      <form class="role-form" @submit.prevent="handleSaveRole">
        <label>
          {{ t.roleName }}
          <input v-model.trim="form.name" type="text" required />
        </label>

        <label>
          {{ t.description }}
          <textarea v-model.trim="form.description" rows="4" required></textarea>
        </label>

        <fieldset>
          <div class="permissions-header">
            <legend>{{ t.permissions }}</legend>
            <label class="select-all-toggle">
              <input v-model="allSelected" type="checkbox" @change="toggleAll()" />
              <span>{{ t.selectAllPermissions }}</span>
            </label>
          </div>

          <div class="permission-groups">
            <article v-for="group in permissionGroups" :key="group.entity" class="permission-group">
              <header class="group-head">
                <strong>{{ group.label }}</strong>
                <label class="select-all-toggle">
                  <input
                    type="checkbox"
                    :checked="groupAllSelected(group)"
                    @change="toggleAll(group)"
                  />
                  <span>{{ t.selectGroup }}</span>
                </label>
              </header>

              <div class="permissions-grid">
                <label v-for="action in group.actions" :key="action.code" class="permission-item">
                  <input v-model="form.permissions" type="checkbox" :value="action.code" />
                  <span>{{ action.label }}</span>
                </label>
              </div>
            </article>
          </div>
        </fieldset>

        <p v-if="roleError" class="error-text">{{ roleError }}</p>

        <div class="form-actions">
          <button type="submit" class="primary-btn" :disabled="isSavingRole">
            {{ isSavingRole ? t.savingRole : t.saveRole }}
          </button>
        </div>
      </form>
    </section>

    <section class="card assignment-card">
      <div class="assignment-head">
        <h2>{{ t.userAssignment }}</h2>
        <button type="button" class="secondary-btn" @click="assignToAllUsers">
          {{ t.assignToAllUsers }}
        </button>
      </div>
      <p class="assignment-subtitle">{{ t.assignmentHint }}</p>

      <div class="users-list">
        <label v-for="user in users" :key="user.id" class="user-row">
          <input v-model="selectedUserIds" type="checkbox" :value="user.id" />
          <div>
            <strong>{{ user.name }}</strong>
            <small>{{ user.email }}</small>
          </div>
          <span class="status-chip" :class="user.status === 'active' ? 'active' : 'inactive'">
            {{ user.status === 'active' ? t.active : t.inactive }}
          </span>
        </label>
      </div>

      <div class="form-actions">
        <button type="button" class="primary-btn" @click="saveAssignments">
          {{ t.saveAssignments }}
        </button>
      </div>
    </section>
  </div>

  <div v-else-if="!isLoading" class="not-found card">
    <h2>{{ t.roleNotFound }}</h2>
    <RouterLink class="text-btn" :to="{ name: 'adminRoles' }">{{ t.backToList }}</RouterLink>
  </div>

  <div v-else class="card not-found">
    <h2>{{ t.loadingRole }}</h2>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useUiPreferences } from '@/compasables/useUiPreferences'
import { useRoleManagement } from '@/compasables/useRoleManagement'
import { getPermissionGroups } from '@/data/permissionCatalog'

const route = useRoute()
const { locale } = useUiPreferences()
const {
  users,
  fetchUsers,
  fetchRoles,
  getRoleById,
  updateRole,
  saveRoleAssignments,
  assignRoleToAllUsers,
} = useRoleManagement()

const role = ref(null)
const isLoading = ref(true)
const isSavingRole = ref(false)
const roleExists = computed(() => !!role.value)

const form = reactive({
  name: '',
  description: '',
  permissions: [],
})

const selectedUserIds = ref([])
const roleError = ref('')
const allSelected = ref(false)

const translations = {
  en: {
    roleManagement: 'Role Management',
    editTitle: 'Edit Role',
    editSubtitle: 'Role details and assignment are handled on this dedicated page.',
    backToList: 'Back to Roles List',
    roleInformation: 'Role Information',
    roleName: 'Role Name',
    description: 'Description',
    permissions: 'Permissions',
    selectAllPermissions: 'Select all permissions',
    selectGroup: 'Select group',
    saveRole: 'Save Role',
    savingRole: 'Saving...',
    userAssignment: 'Assign Role to Users',
    assignToAllUsers: 'Assign to All Users',
    assignmentHint: 'Select users to receive this role, or assign it to all users in one click.',
    saveAssignments: 'Save Assignments',
    active: 'Active',
    inactive: 'Inactive',
    roleNotFound: 'Role not found.',
    loadingRole: 'Loading role...',
    selectOnePermission: 'Select at least one permission.',
    saveRoleFailed: 'Unable to save role. Please try again.',
  },
  fr: {
    roleManagement: 'Gestion des roles',
    editTitle: 'Modifier le role',
    editSubtitle: "Les details du role et l'assignation sont geres sur cette page dediee.",
    backToList: 'Retour a la liste des roles',
    roleInformation: 'Informations du role',
    roleName: 'Nom du role',
    description: 'Description',
    permissions: 'Permissions',
    selectAllPermissions: 'Tout selectionner',
    selectGroup: 'Selectionner le groupe',
    saveRole: 'Enregistrer le role',
    savingRole: 'Enregistrement...',
    userAssignment: 'Assigner le role aux utilisateurs',
    assignToAllUsers: 'Assigner a tous les utilisateurs',
    assignmentHint:
      'Selectionnez les utilisateurs a qui attribuer ce role, ou assignez-le a tous en un clic.',
    saveAssignments: 'Enregistrer les assignations',
    active: 'Actif',
    inactive: 'Inactif',
    roleNotFound: 'Role introuvable.',
    loadingRole: 'Chargement du role...',
    selectOnePermission: 'Selectionnez au moins une permission.',
    saveRoleFailed: 'Impossible de sauvegarder le role. Veuillez reessayer.',
  },
}

const t = computed(() => translations[locale.value] || translations.en)

const permissionGroups = computed(() => getPermissionGroups(locale.value))

const allPermissionCodes = computed(() =>
  permissionGroups.value.flatMap((group) => group.actions.map((action) => action.code)),
)

const groupAllSelected = (group) =>
  group.actions.every((action) => form.permissions.includes(action.code))

const toggleAll = (group) => {
  if (group) {
    const groupCodes = group.actions.map((action) => action.code)
    if (groupAllSelected(group)) {
      form.permissions = form.permissions.filter((value) => !groupCodes.includes(value))
      return
    }

    form.permissions = [...new Set([...form.permissions, ...groupCodes])]
    return
  }

  if (allSelected.value) {
    form.permissions = [...new Set([...form.permissions, ...allPermissionCodes.value])]
    return
  }

  form.permissions = form.permissions.filter((value) => !allPermissionCodes.value.includes(value))
}

watch(
  () => form.permissions,
  (selectedPermissions) => {
    allSelected.value =
      allPermissionCodes.value.length > 0 &&
      allPermissionCodes.value.every((code) => selectedPermissions.includes(code))
  },
  { deep: true },
)

const hydrateForm = (currentRole) => {
  form.name = currentRole.name || ''
  form.description = currentRole.description || ''
  form.permissions = [...(currentRole.permissions || [])]
  selectedUserIds.value = [...(currentRole.assignedUserIds || [])]
}

const loadRole = async () => {
  isLoading.value = true
  try {
    await Promise.all([fetchRoles(), fetchUsers()])
    role.value = getRoleById(route.params.id)
    if (role.value) {
      hydrateForm(role.value)
    }
  } finally {
    isLoading.value = false
  }
}

const handleSaveRole = async () => {
  if (!form.permissions.length) {
    roleError.value = t.value.selectOnePermission
    return
  }

  isSavingRole.value = true
  roleError.value = ''
  try {
    const updatedRole = await updateRole(route.params.id, {
      name: form.name,
      description: form.description,
      permissions: form.permissions,
    })

    if (!updatedRole) {
      roleError.value = t.value.roleNotFound
      return
    }

    role.value = updatedRole
  } catch {
    roleError.value = t.value.saveRoleFailed
  } finally {
    isSavingRole.value = false
  }
}

const saveAssignments = async () => {
  const result = await saveRoleAssignments(route.params.id, selectedUserIds.value)
  if (!result.ok) {
    roleError.value = result.message || t.value.saveRoleFailed
  }
}

const assignToAllUsers = () => {
  const updatedRole = assignRoleToAllUsers(route.params.id)
  if (updatedRole) {
    selectedUserIds.value = [...updatedRole.assignedUserIds]
  }
}

onMounted(loadRole)
</script>

<style scoped>
.edit-role-page {
  display: grid;
  gap: 16px;
}

.page-header {
  padding: 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
}

.kicker,
.subtitle,
.assignment-subtitle {
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

.subtitle,
.assignment-subtitle {
  margin-top: 8px;
  color: var(--muted);
}

.form-card,
.assignment-card,
.not-found {
  padding: 16px;
}

.role-form {
  margin-top: 12px;
  display: grid;
  gap: 14px;
}

label,
legend {
  font-weight: 600;
}

fieldset {
  margin: 0;
  border-radius: 12px;
  border: 1px solid var(--border);
  padding: 12px;
}

.permissions-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}

.permission-groups {
  margin-top: 10px;
  display: grid;
  gap: 10px;
}

.permission-group {
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 10px;
}

.group-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  margin-bottom: 8px;
}

.select-all-toggle {
  font-weight: 500;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.permissions-grid {
  display: grid;
  gap: 8px;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
}

.permission-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px;
  border-radius: 10px;
  background: rgba(17, 74, 106, 0.06);
}

.permission-item input,
.user-row input {
  width: auto;
}

.error-text {
  color: #c92a2a;
  margin: 0;
  font-weight: 600;
}

.form-actions {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: 12px;
}

.assignment-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
}

.users-list {
  margin-top: 14px;
  display: grid;
  gap: 8px;
}

.user-row {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px;
  border: 1px solid var(--border);
  border-radius: 12px;
}

.user-row div {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.user-row small {
  color: var(--muted);
}

.status-chip {
  margin-left: auto;
}

@media (max-width: 760px) {
  .page-header,
  .assignment-head,
  .permissions-header,
  .group-head {
    flex-direction: column;
    align-items: flex-start;
  }

  .form-actions {
    flex-wrap: wrap;
  }
}
</style>
