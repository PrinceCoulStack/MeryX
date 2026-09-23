<template>
  <div class="create-role-page">
    <header class="page-header card">
      <div>
        <p class="kicker">{{ t.roleManagement }}</p>
        <h1>{{ t.createTitle }}</h1>
        <p class="subtitle">{{ t.createSubtitle }}</p>
      </div>
      <RouterLink class="text-btn" :to="{ name: 'adminRoles' }">{{ t.backToList }}</RouterLink>
    </header>

    <section class="card form-card">
      <form class="role-form" @submit.prevent="handleSubmit">
        <label>
          {{ t.roleName }}
          <input
            v-model.trim="form.name"
            type="text"
            required
            :placeholder="t.roleNamePlaceholder"
          />
        </label>

        <label>
          {{ t.description }}
          <textarea
            v-model.trim="form.description"
            rows="4"
            required
            :placeholder="t.descriptionPlaceholder"
          ></textarea>
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

        <p v-if="errorMessage" class="error-text">{{ errorMessage }}</p>

        <div class="form-actions">
          <button type="submit" class="primary-btn" :disabled="isSubmitting">
            {{ isSubmitting ? t.creatingRole : t.createRole }}
          </button>
          <RouterLink class="text-btn" :to="{ name: 'adminRoles' }">{{ t.cancel }}</RouterLink>
        </div>
      </form>
    </section>
  </div>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useUiPreferences } from '@/compasables/useUiPreferences'
import { useRoleManagement } from '@/compasables/useRoleManagement'
import { getPermissionGroups } from '@/data/permissionCatalog'

const router = useRouter()
const { createRole } = useRoleManagement()
const { locale } = useUiPreferences()

const form = reactive({
  name: '',
  description: '',
  permissions: [],
})

const errorMessage = ref('')
const isSubmitting = ref(false)
const allSelected = ref(false)

const translations = {
  en: {
    roleManagement: 'Role Management',
    createTitle: 'Create Role',
    createSubtitle: 'Create a new role. Assignment is done on the dedicated edit page.',
    backToList: 'Back to Roles List',
    roleName: 'Role Name',
    roleNamePlaceholder: 'Example: University Reviewer',
    description: 'Description',
    descriptionPlaceholder: 'Describe what users with this role can do.',
    permissions: 'Permissions',
    selectAllPermissions: 'Select all permissions',
    selectGroup: 'Select group',
    createRole: 'Create Role',
    creatingRole: 'Creating...',
    cancel: 'Cancel',
    selectOnePermission: 'Select at least one permission.',
    createFailed: 'Unable to create role. Please try again.',
  },
  fr: {
    roleManagement: 'Gestion des roles',
    createTitle: 'Creer un role',
    createSubtitle:
      "Creez un nouveau role. L'assignation se fait sur la page de modification dediee.",
    backToList: 'Retour a la liste des roles',
    roleName: 'Nom du role',
    roleNamePlaceholder: 'Exemple : Verificateur Universite',
    description: 'Description',
    descriptionPlaceholder: 'Decrivez les droits de ce role.',
    permissions: 'Permissions',
    selectAllPermissions: 'Tout selectionner',
    selectGroup: 'Selectionner le groupe',
    createRole: 'Creer le role',
    creatingRole: 'Creation en cours...',
    cancel: 'Annuler',
    selectOnePermission: 'Selectionnez au moins une permission.',
    createFailed: 'Impossible de creer le role. Veuillez reessayer.',
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

const handleSubmit = async () => {
  if (!form.permissions.length) {
    errorMessage.value = t.value.selectOnePermission
    return
  }

  errorMessage.value = ''
  isSubmitting.value = true

  try {
    await createRole({
      name: form.name,
      description: form.description,
      permissions: form.permissions,
      isEnabled: true,
    })

    router.push({ name: 'adminRoles' })
  } catch {
    errorMessage.value = t.value.createFailed
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style scoped>
.create-role-page {
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
.subtitle {
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
}

.form-card {
  padding: 16px;
}

.role-form {
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

.permission-item input {
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
}

@media (max-width: 760px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .form-actions {
    flex-wrap: wrap;
  }

  .permissions-header,
  .group-head {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
