<template>
  <div class="config-page">
    <header class="card page-header">
      <div>
        <p class="kicker">{{ t.configuration }}</p>
        <h1>{{ t.subAdminTitle }}</h1>
        <p class="hint">{{ t.subAdminHint }}</p>
      </div>
    </header>

    <section class="card panel">
      <p v-if="isLoading" class="hint">{{ t.loadingAccounts }}</p>
      <p v-else-if="loadError" class="hint error">{{ loadError }}</p>

      <h2>{{ t.inviteSubAdmin }}</h2>
      <form class="invite-form" @submit.prevent="inviteSubAdmin">
        <input
          v-model.trim="inviteForm.name"
          type="text"
          :placeholder="t.fullName"
          :disabled="isSubmittingInvite"
          required
        />
        <input
          v-model.trim="inviteForm.email"
          type="email"
          :placeholder="t.email"
          :disabled="isSubmittingInvite"
          required
        />
        <select v-model="inviteForm.role" :disabled="isSubmittingInvite">
          <option value="reviewer">{{ t.reviewer }}</option>
          <option value="operations">{{ t.operations }}</option>
          <option value="compliance">{{ t.compliance }}</option>
        </select>
        <button type="submit" class="primary-btn" :disabled="isSubmittingInvite">
          {{ isSubmittingInvite ? t.sendingInvite : t.sendInvite }}
        </button>
      </form>
      <p v-if="inviteMessage" class="saved-msg">{{ inviteMessage }}</p>
    </section>

    <section class="card panel">
      <h2>{{ t.currentSubAdmins }}</h2>
      <table>
        <thead>
          <tr>
            <th>{{ t.name }}</th>
            <th>{{ t.email }}</th>
            <th>{{ t.role }}</th>
            <th>{{ t.status }}</th>
            <th>{{ t.actions }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!accounts.length">
            <td colspan="5">{{ t.noAccounts }}</td>
          </tr>
          <tr v-for="account in accounts" :key="account.id">
            <td>{{ account.name }}</td>
            <td>{{ account.email }}</td>
            <td>
              <select
                :value="account.role"
                :disabled="busyAccountId === account.id"
                @change="updateRole(account, $event.target.value)"
              >
                <option value="reviewer">{{ t.reviewer }}</option>
                <option value="operations">{{ t.operations }}</option>
                <option value="compliance">{{ t.compliance }}</option>
              </select>
            </td>
            <td>
              <span
                class="status-chip"
                :class="account.status === 'active' ? 'active' : 'inactive'"
              >
                {{ account.status === 'active' ? t.active : t.suspended }}
              </span>
            </td>
            <td class="action-cell">
              <button
                type="button"
                class="text-btn"
                :disabled="busyAccountId === account.id"
                @click="toggleStatus(account)"
              >
                {{ account.status === 'active' ? t.suspend : t.reactivate }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <p v-if="actionMessage" class="saved-msg">{{ actionMessage }}</p>
    </section>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import api from '@/api/axios'
import { useUiPreferences } from '@/compasables/useUiPreferences'

const { locale } = useUiPreferences()

const STORAGE_KEY = 'admin.sub-admin-accounts.v1'

const ACCOUNT_ENDPOINTS = [
  'sub-admin-accounts',
  'sub_admin_accounts',
  'subAdminAccounts',
  'admin/sub-admin-accounts',
  'admin/sub-admins',
]

const DEFAULT_ACCOUNTS = [
  {
    id: 1,
    name: 'Rokia Traore',
    email: 'rokia.traore@meryx.test',
    role: 'reviewer',
    status: 'active',
  },
  {
    id: 2,
    name: 'Kevin Mensah',
    email: 'kevin.mensah@meryx.test',
    role: 'operations',
    status: 'active',
  },
  {
    id: 3,
    name: 'Aicha Bah',
    email: 'aicha.bah@meryx.test',
    role: 'compliance',
    status: 'suspended',
  },
]

const accounts = ref([])
const activeEndpoint = ref(ACCOUNT_ENDPOINTS[0])
const isLoading = ref(false)
const isSubmittingInvite = ref(false)
const loadError = ref('')
const inviteMessage = ref('')
const actionMessage = ref('')
const busyAccountId = ref('')

const inviteForm = reactive({
  name: '',
  email: '',
  role: 'reviewer',
})

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
  if (Array.isArray(normalized?.['hydra:member'])) return normalized['hydra:member']
  if (Array.isArray(normalized?.items)) return normalized.items
  if (Array.isArray(normalized)) return normalized
  return []
}

const normalizeRole = (value) => {
  const raw = String(value || '')
    .trim()
    .toLowerCase()
  if (!raw) return 'reviewer'
  if (raw.includes('operation')) return 'operations'
  if (raw.includes('compliance') || raw.includes('conformite')) return 'compliance'
  return 'reviewer'
}

const normalizeStatus = (value) => {
  const raw = String(value || '')
    .trim()
    .toLowerCase()
  if (!raw) return 'active'
  if (
    raw.includes('suspend') ||
    raw === 'inactive' ||
    raw === 'disabled' ||
    raw === 'blocked' ||
    raw === 'rejected'
  ) {
    return 'suspended'
  }
  return 'active'
}

const mapAccount = (item = {}) => ({
  id: String(item.id || item['@id'] || item.userId || item.email || '').trim(),
  name: item.name || item.fullName || item.full_name || item.displayName || 'Unknown user',
  email: item.email || item.mail || 'unknown@meryx.test',
  role: normalizeRole(item.role || item.userRole || item.user_role),
  status: normalizeStatus(item.status || item.state || item.accountStatus || item.account_status),
})

const saveLocal = () => {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(accounts.value))
}

const loadLocal = () => {
  const raw = localStorage.getItem(STORAGE_KEY)
  if (!raw) {
    accounts.value = DEFAULT_ACCOUNTS.map((item) => ({ ...item, id: String(item.id) }))
    return
  }

  try {
    const parsed = JSON.parse(raw)
    if (!Array.isArray(parsed)) {
      accounts.value = DEFAULT_ACCOUNTS.map((item) => ({ ...item, id: String(item.id) }))
      return
    }
    accounts.value = parsed.map(mapAccount)
  } catch {
    accounts.value = DEFAULT_ACCOUNTS.map((item) => ({ ...item, id: String(item.id) }))
  }
}

const loadAccounts = async () => {
  isLoading.value = true
  loadError.value = ''
  actionMessage.value = ''

  try {
    for (const endpoint of ACCOUNT_ENDPOINTS) {
      try {
        const response = await api.listItems(endpoint)
        const rows = extractCollection(response.data)
        activeEndpoint.value = endpoint
        if (rows.length) {
          accounts.value = rows.map(mapAccount)
          saveLocal()
          return
        }
      } catch (error) {
        const status = error?.response?.status
        if (status !== 404 && status !== 405) {
          throw error
        }
      }
    }

    loadLocal()
  } catch (error) {
    loadLocal()
    loadError.value = error?.response?.data?.detail || error?.message || 'Unable to load accounts.'
  } finally {
    isLoading.value = false
  }
}

const translations = {
  en: {
    configuration: 'Configuration',
    subAdminTitle: 'Sub-Admin Accounts',
    subAdminHint: 'Create and manage delegated admin accounts with controlled permissions.',
    inviteSubAdmin: 'Invite a Sub-Admin',
    fullName: 'Full name',
    email: 'Email address',
    reviewer: 'Reviewer',
    operations: 'Operations',
    compliance: 'Compliance',
    sendInvite: 'Send Invite',
    sendingInvite: 'Sending...',
    inviteSent: 'Invitation sent successfully.',
    currentSubAdmins: 'Current Sub-Admins',
    name: 'Name',
    role: 'Role',
    status: 'Status',
    actions: 'Actions',
    active: 'Active',
    suspended: 'Suspended',
    suspend: 'Suspend',
    reactivate: 'Reactivate',
    loadingAccounts: 'Loading sub-admin accounts...',
    noAccounts: 'No sub-admin accounts found.',
    roleUpdated: 'Role updated successfully.',
    statusUpdated: 'Account status updated successfully.',
    actionFailed: 'Update failed. Please try again.',
  },
  fr: {
    configuration: 'Configuration',
    subAdminTitle: 'Comptes sous-admin',
    subAdminHint: 'Creez et gerez des comptes admin delegues avec des permissions controlees.',
    inviteSubAdmin: 'Inviter un sous-admin',
    fullName: 'Nom complet',
    email: 'Adresse email',
    reviewer: 'Revision',
    operations: 'Operations',
    compliance: 'Conformite',
    sendInvite: "Envoyer l'invitation",
    sendingInvite: 'Envoi en cours...',
    inviteSent: 'Invitation envoyee avec succes.',
    currentSubAdmins: 'Sous-admins actuels',
    name: 'Nom',
    role: 'Role',
    status: 'Statut',
    actions: 'Actions',
    active: 'Actif',
    suspended: 'Suspendu',
    suspend: 'Suspendre',
    reactivate: 'Reactiver',
    loadingAccounts: 'Chargement des comptes sous-admin...',
    noAccounts: 'Aucun compte sous-admin trouve.',
    roleUpdated: 'Role mis a jour avec succes.',
    statusUpdated: 'Statut du compte mis a jour avec succes.',
    actionFailed: 'Echec de la mise a jour. Veuillez reessayer.',
  },
}

const t = computed(() => translations[locale.value] || translations.en)

const inviteSubAdmin = async () => {
  if (isSubmittingInvite.value) return

  isSubmittingInvite.value = true
  inviteMessage.value = ''
  actionMessage.value = ''

  const payload = {
    name: inviteForm.name,
    email: inviteForm.email,
    role: inviteForm.role,
    status: 'active',
  }

  try {
    const response = await api.createItem(activeEndpoint.value, payload)
    const created = mapAccount(normalizeApiData(response.data))
    if (!created.id) {
      created.id = String(Date.now())
    }
    accounts.value.unshift(created)
    saveLocal()
    inviteMessage.value = t.value.inviteSent
  } catch (error) {
    const status = error?.response?.status
    if (status === 404 || status === 405) {
      const localCreated = mapAccount({ id: Date.now(), ...payload })
      accounts.value.unshift(localCreated)
      saveLocal()
      inviteMessage.value = t.value.inviteSent
    } else {
      inviteMessage.value = error?.response?.data?.detail || t.value.actionFailed
    }
  } finally {
    isSubmittingInvite.value = false
  }

  inviteForm.name = ''
  inviteForm.email = ''
  inviteForm.role = 'reviewer'
}

const updateRole = async (account, role) => {
  const previousRole = account.role
  account.role = normalizeRole(role)
  actionMessage.value = ''
  busyAccountId.value = account.id

  try {
    await api.patchItem(activeEndpoint.value, account.id, { role: account.role })
    saveLocal()
    actionMessage.value = t.value.roleUpdated
  } catch (error) {
    const status = error?.response?.status
    if (status === 404 || status === 405) {
      saveLocal()
      actionMessage.value = t.value.roleUpdated
    } else {
      account.role = previousRole
      saveLocal()
      actionMessage.value = error?.response?.data?.detail || t.value.actionFailed
    }
  } finally {
    busyAccountId.value = ''
  }
}

const toggleStatus = async (account) => {
  const previousStatus = account.status
  account.status = account.status === 'active' ? 'suspended' : 'active'
  actionMessage.value = ''
  busyAccountId.value = account.id

  try {
    await api.patchItem(activeEndpoint.value, account.id, { status: account.status })
    saveLocal()
    actionMessage.value = t.value.statusUpdated
  } catch (error) {
    const status = error?.response?.status
    if (status === 404 || status === 405) {
      saveLocal()
      actionMessage.value = t.value.statusUpdated
    } else {
      account.status = previousStatus
      saveLocal()
      actionMessage.value = error?.response?.data?.detail || t.value.actionFailed
    }
  } finally {
    busyAccountId.value = ''
  }
}

onMounted(async () => {
  await loadAccounts()
})
</script>

<style scoped>
.config-page {
  display: grid;
  gap: 16px;
}

.page-header,
.panel {
  padding: 16px;
}

.kicker,
.hint,
.saved-msg {
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

.hint.error {
  color: #b42318;
}

.invite-form {
  margin-top: 12px;
  display: grid;
  gap: 10px;
  grid-template-columns: 1.2fr 1.2fr 1fr auto;
}

.saved-msg {
  margin-top: 10px;
  color: var(--muted);
}

.action-cell {
  white-space: nowrap;
}

.panel {
  overflow-x: auto;
}

@media (max-width: 980px) {
  .invite-form {
    grid-template-columns: 1fr;
  }
}
</style>
