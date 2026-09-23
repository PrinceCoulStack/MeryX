<template>
  <div class="config-page">
    <header class="card page-header">
      <div>
        <p class="kicker">{{ t.configuration }}</p>
        <h1>{{ t.integrationsTitle }}</h1>
        <p class="hint">{{ t.integrationsHint }}</p>
      </div>
    </header>

    <section class="card panel">
      <p v-if="isLoading" class="hint">{{ t.loading }}</p>
      <p v-else-if="loadError" class="hint error">{{ loadError }}</p>
      <p v-else-if="!integrations.length" class="hint">{{ t.noIntegrations }}</p>

      <div class="integration-list">
        <article v-for="integration in integrations" :key="integration.id" class="integration-item">
          <div>
            <h2>{{ integration.name }}</h2>
            <p>{{ integration.description }}</p>
          </div>
          <div class="integration-actions">
            <span class="status-chip" :class="integration.enabled ? 'active' : 'inactive'">
              {{ integration.enabled ? t.connected : t.disconnected }}
            </span>
            <button
              type="button"
              class="topbar-pill"
              :disabled="savingId === integration.id"
              @click="toggleIntegration(integration.id)"
            >
              {{ integration.enabled ? t.disable : t.enable }}
            </button>
          </div>
        </article>
      </div>

      <p v-if="actionMessage" class="hint">{{ actionMessage }}</p>
    </section>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import api from '@/api/axios'
import { useUiPreferences } from '@/compasables/useUiPreferences'

const { locale } = useUiPreferences()
const isLoading = ref(false)
const loadError = ref('')
const actionMessage = ref('')
const savingId = ref('')

const STORAGE_KEY = 'admin.integrations.v1'

const DEFAULT_INTEGRATIONS = [
  {
    id: 'mail',
    name: 'Email Gateway',
    description: 'Sends account approvals, rejections, and platform notifications.',
    enabled: true,
  },
  {
    id: 'analytics',
    name: 'Usage Analytics',
    description: 'Tracks student and recruiter activity metrics across the platform.',
    enabled: true,
  },
  {
    id: 'storage',
    name: 'Document Storage',
    description: 'Stores verification files provided by universities and companies.',
    enabled: false,
  },
  {
    id: 'sso',
    name: 'Single Sign-On',
    description: 'Allows institutional SSO integration with trusted identity providers.',
    enabled: false,
  },
]

const integrations = ref([])

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

const toBool = (value) => {
  if (typeof value === 'boolean') return value
  if (typeof value === 'number') return value === 1
  if (typeof value === 'string') {
    const normalized = value.trim().toLowerCase()
    return ['true', '1', 'yes', 'enabled', 'active', 'connected'].includes(normalized)
  }
  return false
}

const mapIntegration = (item) => ({
  id:
    String(item.id || item.key || item.code || item.slug || '').trim() ||
    `integration-${Date.now()}`,
  name: item.name || item.label || 'Integration',
  description: item.description || item.details || '',
  enabled: toBool(item.enabled ?? item.isEnabled ?? item.active ?? item.connected),
  raw: item,
})

const saveLocal = (items) => {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(items))
}

const loadLocal = () => {
  const raw = localStorage.getItem(STORAGE_KEY)
  if (!raw) return DEFAULT_INTEGRATIONS
  try {
    const parsed = JSON.parse(raw)
    if (!Array.isArray(parsed)) return DEFAULT_INTEGRATIONS
    return parsed
  } catch {
    return DEFAULT_INTEGRATIONS
  }
}

const loadIntegrations = async () => {
  isLoading.value = true
  loadError.value = ''
  actionMessage.value = ''

  try {
    const response = await api.listItems('integrations')
    const rows = extractCollection(response.data)
    if (rows.length) {
      integrations.value = rows.map(mapIntegration)
      saveLocal(integrations.value)
      return
    }

    integrations.value = loadLocal()
  } catch (error) {
    const status = error?.response?.status
    if (status === 404 || status === 405) {
      integrations.value = loadLocal()
    } else {
      integrations.value = loadLocal()
      loadError.value =
        error?.response?.data?.detail || error?.message || 'Unable to load integrations.'
    }
  } finally {
    isLoading.value = false
  }
}

const translations = {
  en: {
    configuration: 'Configuration',
    integrationsTitle: 'Integrations',
    integrationsHint: 'Manage third-party services and core platform connectors.',
    connected: 'Connected',
    disconnected: 'Disconnected',
    enable: 'Enable',
    disable: 'Disable',
    loading: 'Loading integrations...',
    noIntegrations: 'No integrations found.',
    updateSuccess: 'Integration updated successfully.',
    updateFailed: 'Failed to update integration.',
  },
  fr: {
    configuration: 'Configuration',
    integrationsTitle: 'Integrations',
    integrationsHint: 'Gerez les services tiers et les connecteurs de la plateforme.',
    connected: 'Connecte',
    disconnected: 'Deconnecte',
    enable: 'Activer',
    disable: 'Desactiver',
    loading: 'Chargement des integrations...',
    noIntegrations: 'Aucune integration trouvee.',
    updateSuccess: 'Integration mise a jour avec succes.',
    updateFailed: "Echec de la mise a jour de l'integration.",
  },
}

const t = computed(() => translations[locale.value] || translations.en)

const toggleIntegration = async (id) => {
  const integration = integrations.value.find((item) => item.id === id)
  if (!integration || savingId.value) return

  savingId.value = id
  actionMessage.value = ''

  const previous = integration.enabled
  integration.enabled = !integration.enabled
  saveLocal(integrations.value)

  try {
    await api.patchItem('integrations', id, { enabled: integration.enabled })
    actionMessage.value = t.value.updateSuccess
  } catch (error) {
    const status = error?.response?.status
    if (status === 404 || status === 405) {
      actionMessage.value = t.value.updateSuccess
    } else {
      integration.enabled = previous
      saveLocal(integrations.value)
      actionMessage.value = error?.response?.data?.detail || t.value.updateFailed
    }
  } finally {
    savingId.value = ''
  }
}

onMounted(async () => {
  await loadIntegrations()
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

h1,
h2,
p {
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

.integration-list {
  display: grid;
  gap: 10px;
}

.integration-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 12px;
}

.integration-item p {
  margin-top: 6px;
  color: var(--muted);
}

.integration-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

@media (max-width: 800px) {
  .integration-item {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
