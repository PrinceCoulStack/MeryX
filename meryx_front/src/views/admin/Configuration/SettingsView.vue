<template>
  <div class="config-page">
    <header class="card page-header">
      <div>
        <p class="kicker">{{ t.configuration }}</p>
        <h1>{{ t.settingsTitle }}</h1>
        <p class="hint">{{ t.settingsHint }}</p>
      </div>
    </header>

    <section class="card panel">
      <p v-if="isLoading" class="hint">{{ t.loading }}</p>
      <p v-else-if="loadError" class="hint error">{{ loadError }}</p>

      <h2>{{ t.platformControls }}</h2>
      <div class="settings-grid">
        <label class="setting-item">
          <span>{{ t.allowSelfRegistration }}</span>
          <input v-model="settings.allowSelfRegistration" type="checkbox" :disabled="isLoading" />
        </label>

        <label class="setting-item">
          <span>{{ t.requireEmailVerification }}</span>
          <input
            v-model="settings.requireEmailVerification"
            type="checkbox"
            :disabled="isLoading"
          />
        </label>

        <label class="setting-item">
          <span>{{ t.maintenanceMode }}</span>
          <input v-model="settings.maintenanceMode" type="checkbox" :disabled="isLoading" />
        </label>

        <label class="setting-item">
          <span>{{ t.autoApproveTrustedUniversities }}</span>
          <input
            v-model="settings.autoApproveTrustedUniversities"
            type="checkbox"
            :disabled="isLoading"
          />
        </label>
      </div>

      <label>
        {{ t.defaultSessionTimeout }}
        <select v-model="settings.sessionTimeoutMinutes" :disabled="isLoading">
          <option :value="15">15 min</option>
          <option :value="30">30 min</option>
          <option :value="45">45 min</option>
          <option :value="60">60 min</option>
        </select>
      </label>

      <div class="actions">
        <button type="button" class="primary-btn" :disabled="isSaving" @click="saveSettings">
          {{ isSaving ? t.savingChanges : t.saveChanges }}
        </button>
      </div>
      <p v-if="savedMessage" class="saved-msg">{{ savedMessage }}</p>
    </section>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import api from '@/api/axios'
import { useUiPreferences } from '@/compasables/useUiPreferences'

const { locale } = useUiPreferences()

const STORAGE_KEY = 'admin.settings.v1'

const DEFAULT_SETTINGS = {
  allowSelfRegistration: true,
  requireEmailVerification: true,
  maintenanceMode: false,
  autoApproveTrustedUniversities: false,
  sessionTimeoutMinutes: 30,
}

const settings = reactive({
  ...DEFAULT_SETTINGS,
})

const settingsResourceId = ref('')
const isLoading = ref(false)
const isSaving = ref(false)
const loadError = ref('')
const savedMessage = ref('')

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

const toBool = (value, fallback = false) => {
  if (typeof value === 'boolean') return value
  if (typeof value === 'number') return value === 1
  if (typeof value === 'string') {
    const normalized = value.trim().toLowerCase()
    if (['true', '1', 'yes', 'enabled', 'active'].includes(normalized)) return true
    if (['false', '0', 'no', 'disabled', 'inactive'].includes(normalized)) return false
  }
  return fallback
}

const toInt = (value, fallback = 30) => {
  const parsed = Number.parseInt(String(value), 10)
  return Number.isNaN(parsed) ? fallback : parsed
}

const mapFromBackend = (item = {}) => {
  settings.allowSelfRegistration = toBool(
    item.allowSelfRegistration ?? item.allow_self_registration,
    DEFAULT_SETTINGS.allowSelfRegistration,
  )
  settings.requireEmailVerification = toBool(
    item.requireEmailVerification ?? item.require_email_verification,
    DEFAULT_SETTINGS.requireEmailVerification,
  )
  settings.maintenanceMode = toBool(
    item.maintenanceMode ?? item.maintenance_mode,
    DEFAULT_SETTINGS.maintenanceMode,
  )
  settings.autoApproveTrustedUniversities = toBool(
    item.autoApproveTrustedUniversities ?? item.auto_approve_trusted_universities,
    DEFAULT_SETTINGS.autoApproveTrustedUniversities,
  )
  settings.sessionTimeoutMinutes = toInt(
    item.sessionTimeoutMinutes ?? item.session_timeout_minutes,
    DEFAULT_SETTINGS.sessionTimeoutMinutes,
  )

  settingsResourceId.value = String(item.id || item['@id'] || item.code || item.key || '').trim()
}

const toPayload = () => ({
  allowSelfRegistration: settings.allowSelfRegistration,
  requireEmailVerification: settings.requireEmailVerification,
  maintenanceMode: settings.maintenanceMode,
  autoApproveTrustedUniversities: settings.autoApproveTrustedUniversities,
  sessionTimeoutMinutes: settings.sessionTimeoutMinutes,
})

const saveLocal = () => {
  const record = {
    id: settingsResourceId.value,
    ...toPayload(),
  }
  localStorage.setItem(STORAGE_KEY, JSON.stringify(record))
}

const loadLocal = () => {
  const raw = localStorage.getItem(STORAGE_KEY)
  if (!raw) {
    mapFromBackend(DEFAULT_SETTINGS)
    return
  }

  try {
    const parsed = JSON.parse(raw)
    mapFromBackend(parsed)
  } catch {
    mapFromBackend(DEFAULT_SETTINGS)
  }
}

const pickSettingsRecord = (rows) => {
  if (!rows.length) return null
  return (
    rows.find((item) => String(item?.key || item?.code || '').toLowerCase() === 'platform') ||
    rows.find((item) => item?.type === 'platform') ||
    rows[0]
  )
}

const loadSettings = async () => {
  isLoading.value = true
  loadError.value = ''
  savedMessage.value = ''

  try {
    const response = await api.listItems('settings')
    const rows = extractCollection(response.data)
    const record = pickSettingsRecord(rows)

    if (record) {
      mapFromBackend(record)
      saveLocal()
      return
    }

    loadLocal()
  } catch (error) {
    const status = error?.response?.status
    loadLocal()
    if (status !== 404 && status !== 405) {
      loadError.value =
        error?.response?.data?.detail || error?.message || 'Unable to load settings.'
    }
  } finally {
    isLoading.value = false
  }
}

const translations = {
  en: {
    configuration: 'Configuration',
    settingsTitle: 'Platform Settings',
    settingsHint: 'Control global platform behavior for registration, verification, and access.',
    platformControls: 'Platform Controls',
    allowSelfRegistration: 'Allow new users to self-register',
    requireEmailVerification: 'Require email verification for activation',
    maintenanceMode: 'Enable maintenance mode',
    autoApproveTrustedUniversities: 'Auto-approve trusted universities',
    defaultSessionTimeout: 'Default session timeout',
    saveChanges: 'Save Changes',
    savingChanges: 'Saving...',
    loading: 'Loading settings...',
    saved: 'Settings saved successfully.',
    saveFailed: 'Failed to save settings.',
  },
  fr: {
    configuration: 'Configuration',
    settingsTitle: 'Parametres de la plateforme',
    settingsHint:
      "Controlez le comportement global de la plateforme pour l'inscription, la verification et l'acces.",
    platformControls: 'Controles de la plateforme',
    allowSelfRegistration: "Autoriser l'inscription autonome des nouveaux utilisateurs",
    requireEmailVerification: "Exiger la verification d'email pour l'activation",
    maintenanceMode: 'Activer le mode maintenance',
    autoApproveTrustedUniversities: 'Approuver automatiquement les universites de confiance',
    defaultSessionTimeout: 'Duree de session par defaut',
    saveChanges: 'Enregistrer les modifications',
    savingChanges: 'Enregistrement...',
    loading: 'Chargement des parametres...',
    saved: 'Parametres enregistres avec succes.',
    saveFailed: "Echec de l'enregistrement des parametres.",
  },
}

const t = computed(() => translations[locale.value] || translations.en)

const tryUpdateSettings = async (id, payload) => {
  await api.patchItem('settings', id, payload)
}

const saveSettings = async () => {
  if (isSaving.value) return

  isSaving.value = true
  savedMessage.value = ''
  const payload = toPayload()

  try {
    if (settingsResourceId.value) {
      await tryUpdateSettings(settingsResourceId.value, payload)
    } else {
      try {
        await tryUpdateSettings('platform', payload)
        settingsResourceId.value = 'platform'
      } catch (error) {
        const status = error?.response?.status
        if (status === 404 || status === 405) {
          const created = await api.createItem('settings', { key: 'platform', ...payload })
          const createdData = normalizeApiData(created?.data)
          settingsResourceId.value = String(createdData?.id || createdData?.['@id'] || 'platform')
        } else {
          throw error
        }
      }
    }

    saveLocal()
    savedMessage.value = t.value.saved
  } catch (error) {
    const status = error?.response?.status
    if (status === 404 || status === 405) {
      saveLocal()
      savedMessage.value = t.value.saved
    } else {
      savedMessage.value = error?.response?.data?.detail || t.value.saveFailed
    }
  } finally {
    isSaving.value = false
  }
}

onMounted(async () => {
  await loadSettings()
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

.settings-grid {
  margin: 12px 0;
  display: grid;
  gap: 10px;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
}

.setting-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 10px;
}

.setting-item input {
  width: auto;
}

.actions {
  margin-top: 12px;
}

.saved-msg {
  margin-top: 10px;
  color: var(--muted);
}
</style>
