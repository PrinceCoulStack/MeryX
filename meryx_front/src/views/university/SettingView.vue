<template>
  <div class="settings-page">
    <header class="page-header">
      <div>
        <p class="eyebrow">{{ t.eyebrow }}</p>
        <h1>{{ t.title }}</h1>
        <p class="intro">{{ t.subtitle }}</p>
      </div>
    </header>

    <section class="settings-grid">
      <article class="settings-card">
        <div class="card-head">
          <h2>{{ t.languageTitle }}</h2>
          <span>{{ locale.toUpperCase() }}</span>
        </div>
        <p>{{ t.languageText }}</p>

        <div class="option-list" role="radiogroup" :aria-label="t.languageTitle">
          <button
            type="button"
            class="option-btn"
            :class="{ active: locale === 'en' }"
            @click="setLocale('en')"
          >
            <strong>English</strong>
            <small>{{ t.englishNote }}</small>
          </button>

          <button
            type="button"
            class="option-btn"
            :class="{ active: locale === 'fr' }"
            @click="setLocale('fr')"
          >
            <strong>Francais</strong>
            <small>{{ t.frenchNote }}</small>
          </button>
        </div>
      </article>

      <article class="settings-card">
        <div class="card-head">
          <h2>{{ t.themeTitle }}</h2>
          <span>{{ theme === 'dark' ? t.darkLabel : t.lightLabel }}</span>
        </div>
        <p>{{ t.themeText }}</p>

        <div class="option-list" role="radiogroup" :aria-label="t.themeTitle">
          <button
            type="button"
            class="option-btn"
            :class="{ active: theme === 'light' }"
            @click="setTheme('light')"
          >
            <strong>{{ t.lightLabel }}</strong>
            <small>{{ t.lightNote }}</small>
          </button>

          <button
            type="button"
            class="option-btn"
            :class="{ active: theme === 'dark' }"
            @click="setTheme('dark')"
          >
            <strong>{{ t.darkLabel }}</strong>
            <small>{{ t.darkNote }}</small>
          </button>
        </div>
      </article>
    </section>

    <section class="settings-card profile-card">
      <div class="card-head">
        <h2>{{ t.universityInfoTitle }}</h2>
        <span v-if="currentUniversity.status">{{ currentUniversity.status }}</span>
      </div>
      <p>{{ t.universityInfoText }}</p>

      <p v-if="isLoadingUniversities" class="info-line">{{ t.loadingUniversity }}</p>
      <p v-else-if="universityAdminError" class="error-line">{{ universityAdminError }}</p>

      <form class="profile-grid" @submit.prevent="saveUniversity" v-if="!isLoadingUniversities">
        <label>
          <span>{{ t.universityName }}</span>
          <input v-model="form.name" type="text" required />
        </label>

        <label>
          <span>{{ t.universityType }}</span>
          <input v-model="form.type" type="text" />
        </label>

        <label>
          <span>{{ t.contactEmail }}</span>
          <input v-model="form.email" type="email" />
        </label>

        <label>
          <span>{{ t.contactPhone }}</span>
          <input v-model="form.phone" type="tel" />
        </label>

        <label>
          <span>{{ t.website }}</span>
          <input v-model="form.websiteUrl" type="url" />
        </label>

        <label>
          <span>{{ t.country }}</span>
          <input v-model="form.country" type="text" />
        </label>

        <label>
          <span>{{ t.registrationNumber }}</span>
          <input v-model="form.registrationNumber" type="text" />
        </label>

        <label>
          <span>{{ t.accreditationNumber }}</span>
          <input v-model="form.accreditationNumber" type="text" />
        </label>

        <label>
          <span>{{ t.logoUrl }}</span>
          <input v-model="form.logoUrl" type="url" />
        </label>

        <label class="full">
          <span>{{ t.description }}</span>
          <textarea v-model="form.description" rows="3"></textarea>
        </label>

        <div class="full profile-actions">
          <button type="submit" class="primary-btn" :disabled="isSaving">
            {{ isSaving ? t.saving : t.save }}
          </button>
          <button type="button" class="secondary-btn" @click="loadUniversity(true)">
            {{ t.refresh }}
          </button>
        </div>
      </form>

      <p v-if="saveMessage" class="success-line">{{ saveMessage }}</p>
      <p v-if="saveError" class="error-line">{{ saveError }}</p>
    </section>

    <section class="preview-card">
      <div>
        <h2>{{ t.previewTitle }}</h2>
        <p>{{ t.previewText }}</p>
      </div>
      <div class="preview-badges">
        <span>{{ t.activeLanguage }}: {{ locale.toUpperCase() }}</span>
        <span>{{ t.activeTheme }}: {{ theme === 'dark' ? t.darkLabel : t.lightLabel }}</span>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import api from '@/api/axios'
import { useUiPreferences } from '@/compasables/useUiPreferences'
import { useAuthStore } from '@/stores/auth.store'
import { useUniversityAdmin } from '@/compasables/useUniversityAdmin'

const { locale, theme, setLocale, setTheme, initializeUiPreferences } = useUiPreferences()
const authStore = useAuthStore()
const { fetchUniversities, getUniversityById, isLoadingUniversities, universityAdminError } =
  useUniversityAdmin()

const isSaving = ref(false)
const saveMessage = ref('')
const saveError = ref('')
const currentUniversityId = ref(null)
const currentUserId = ref(null)

const form = reactive({
  name: '',
  type: '',
  email: '',
  phone: '',
  websiteUrl: '',
  country: '',
  registrationNumber: '',
  accreditationNumber: '',
  logoUrl: '',
  description: '',
})

const currentUniversity = reactive({
  status: '',
})

const parseId = (value) => {
  if (!value) return null
  if (typeof value === 'number') return value
  if (typeof value === 'string') {
    const parts = value.split('/')
    const parsed = Number(parts[parts.length - 1])
    return Number.isFinite(parsed) ? parsed : null
  }
  if (typeof value === 'object') {
    if (typeof value.id === 'number') return value.id
    if (typeof value['@id'] === 'string') {
      const parts = value['@id'].split('/')
      const parsed = Number(parts[parts.length - 1])
      return Number.isFinite(parsed) ? parsed : null
    }
  }
  return null
}

const connectedUniversityId = computed(() => {
  const user = authStore.user || {}
  return (
    parseId(user.universityId) ||
    parseId(user.university) ||
    parseId(user.universityProfile) ||
    (authStore.isUniversity ? parseId(user.id) : null)
  )
})

const updateResource = async (resource, id, payload) => {
  try {
    return await api.patchItem(resource, id, payload)
  } catch (error) {
    if (error?.response?.status === 405) {
      return api.updateItem(resource, id, payload)
    }
    throw error
  }
}

const hydrateForm = (university) => {
  if (!university) return

  const raw = university.raw || {}
  currentUniversityId.value = parseId(university.id)
  currentUserId.value = parseId(raw.userId)
  currentUniversity.status = university.status || ''

  form.name = university.name || ''
  form.type = university.type || ''
  form.email = university.email || raw.email || ''
  form.phone = university.phone || raw.phone || ''
  form.websiteUrl = university.websiteUrl || ''
  form.country = university.country || ''
  form.registrationNumber = university.registrationNumber || ''
  form.accreditationNumber = university.accreditationNumber || ''
  form.logoUrl = raw.logoUrl || ''
  form.description = university.description || ''
}

const loadUniversity = async (force = false) => {
  saveMessage.value = ''
  saveError.value = ''

  try {
    await fetchUniversities(force)

    const targetId = connectedUniversityId.value
    if (!targetId) {
      saveError.value = t.value.noUniversityLink
      return
    }

    const university = getUniversityById(targetId)
    if (!university) {
      saveError.value = t.value.universityNotFound
      return
    }

    hydrateForm(university)
  } catch (error) {
    saveError.value = error?.message || t.value.loadFailed
  }
}

const saveUniversity = async () => {
  saveMessage.value = ''
  saveError.value = ''

  if (!currentUniversityId.value) {
    saveError.value = t.value.noUniversityLink
    return
  }

  isSaving.value = true

  try {
    const current = getUniversityById(currentUniversityId.value)
    const raw = current?.raw || {}

    const universityPayload = {
      name: form.name,
      type: form.type || '',
      description: form.description || '',
      websiteUrl: form.websiteUrl || '',
      registrationNumber: form.registrationNumber || '',
      accreditationNumber: form.accreditationNumber || '',
      logoUrl: form.logoUrl || '',
      email: form.email || '',
      ...(raw.addressId ? { addressId: raw.addressId } : {}),
      ...(typeof raw.rankingScore !== 'undefined' ? { rankingScore: raw.rankingScore } : {}),
      ...(typeof raw.isApproved === 'boolean' ? { isApproved: raw.isApproved } : {}),
      ...(raw.status ? { status: raw.status } : {}),
    }

    await updateResource('university', currentUniversityId.value, universityPayload)

    if (currentUserId.value) {
      await updateResource('users', currentUserId.value, {
        email: form.email || '',
        phone: form.phone || '',
      })
    }

    await loadUniversity(true)
    saveMessage.value = t.value.saveSuccess
  } catch (error) {
    const payload = error?.response?.data
    saveError.value = payload?.detail || payload?.message || t.value.saveFailed
  } finally {
    isSaving.value = false
  }
}

const copy = {
  en: {
    eyebrow: 'University / Settings',
    title: 'Interface Settings',
    subtitle: 'Configure preferences and manage your university profile details from one page.',
    languageTitle: 'Language Mode',
    languageText:
      'Switch between English and French for navigation labels and shared dashboard interface text.',
    englishNote: 'Default interface language',
    frenchNote: 'French interface labels',
    themeTitle: 'Appearance Mode',
    themeText:
      'Choose a visual theme. Light and dark mode update all pages using the shared design tokens.',
    lightLabel: 'Light',
    darkLabel: 'Dark',
    lightNote: 'Clear background and bright cards',
    darkNote: 'Low-light interface for focus',
    universityInfoTitle: 'University Information',
    universityInfoText:
      'Review and edit your university profile data used across registration and governance modules.',
    loadingUniversity: 'Loading university information...',
    universityName: 'University Name',
    universityType: 'Type',
    contactEmail: 'Contact Email',
    contactPhone: 'Contact Phone',
    website: 'Website',
    country: 'Country',
    registrationNumber: 'Registration Number',
    accreditationNumber: 'Accreditation Number',
    logoUrl: 'Logo URL',
    description: 'Description',
    save: 'Save Changes',
    saving: 'Saving...',
    refresh: 'Refresh',
    saveSuccess: 'University profile updated successfully.',
    saveFailed: 'Unable to update university profile.',
    loadFailed: 'Unable to load university information.',
    noUniversityLink: 'No university profile is linked to this account.',
    universityNotFound: 'Linked university profile was not found.',
    previewTitle: 'Live Preferences Preview',
    previewText: 'Changes are applied instantly and persist after refresh.',
    activeLanguage: 'Language',
    activeTheme: 'Theme',
  },
  fr: {
    eyebrow: 'Universite / Parametres',
    title: "Parametres de l'interface",
    subtitle:
      'Configurez vos preferences et gerez les informations de votre universite depuis une seule page.',
    languageTitle: 'Mode de langue',
    languageText:
      "Basculez entre anglais et francais pour la navigation et les textes partages de l'interface.",
    englishNote: "Langue d'interface par defaut",
    frenchNote: "Libelles d'interface en francais",
    themeTitle: "Mode d'apparence",
    themeText:
      'Choisissez un theme visuel. Les modes clair et sombre mettent a jour toutes les pages avec des variables communes.',
    lightLabel: 'Clair',
    darkLabel: 'Sombre',
    lightNote: 'Fond lumineux et cartes claires',
    darkNote: 'Interface faible luminosite pour le confort',
    universityInfoTitle: "Informations de l'universite",
    universityInfoText:
      "Consultez et modifiez les informations de votre profil universite utilisees dans les modules d'inscription et de gouvernance.",
    loadingUniversity: "Chargement des informations de l'universite...",
    universityName: "Nom de l'universite",
    universityType: "Type d'etablissement",
    contactEmail: 'Email de contact',
    contactPhone: 'Telephone de contact',
    website: 'Site web',
    country: 'Pays',
    registrationNumber: "Numero d'enregistrement",
    accreditationNumber: "Numero d'accreditation",
    logoUrl: 'URL du logo',
    description: 'Description',
    save: 'Enregistrer les modifications',
    saving: 'Enregistrement...',
    refresh: 'Rafraichir',
    saveSuccess: 'Profil universite mis a jour avec succes.',
    saveFailed: 'Impossible de mettre a jour le profil universite.',
    loadFailed: "Impossible de charger les informations de l'universite.",
    noUniversityLink: "Aucun profil universite n'est lie a ce compte.",
    universityNotFound: 'Le profil universite lie est introuvable.',
    previewTitle: 'Apercu des preferences',
    previewText: 'Les changements sont appliques instantanement et conserves apres actualisation.',
    activeLanguage: 'Langue',
    activeTheme: 'Theme',
  },
}

const t = computed(() => copy[locale.value] || copy.en)

onMounted(async () => {
  initializeUiPreferences()
  await loadUniversity()
})
</script>

<style scoped>
.settings-page {
  display: grid;
  gap: 18px;
}

.page-header h1,
.card-head h2,
.preview-card h2 {
  margin: 0;
  color: var(--text);
}

.eyebrow {
  margin: 0 0 6px;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  color: var(--primary);
  font-size: 0.78rem;
  font-weight: 700;
}

.intro {
  margin: 10px 0 0;
  max-width: 780px;
  color: var(--muted);
}

.settings-grid {
  display: grid;
  gap: 14px;
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.settings-card,
.preview-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 20px;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
}

.settings-card {
  padding: 18px;
  display: grid;
  gap: 12px;
}

.profile-card {
  display: grid;
  gap: 12px;
}

.card-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}

.card-head span,
.settings-card p,
.intro,
.option-btn small,
.preview-card p,
.info-line {
  color: var(--muted);
}

.option-list {
  display: grid;
  grid-template-columns: 1fr;
  gap: 10px;
}

.option-btn {
  width: 100%;
  border: 1px solid var(--border);
  background: var(--surface-soft);
  border-radius: 14px;
  padding: 12px 14px;
  text-align: left;
  cursor: pointer;
  display: grid;
  gap: 5px;
}

.option-btn strong,
.preview-badges span {
  color: var(--text);
}

.option-btn.active {
  border-color: rgba(6, 170, 197, 0.48);
  background: rgba(6, 170, 197, 0.1);
}

.profile-grid {
  display: grid;
  gap: 12px;
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

label {
  display: grid;
  gap: 6px;
}

label span {
  color: var(--muted);
  font-size: 0.88rem;
  font-weight: 600;
}

input,
textarea {
  width: 100%;
  border: 1px solid var(--border);
  background: var(--surface-soft);
  color: var(--text);
  border-radius: 12px;
  padding: 10px 12px;
}

.full {
  grid-column: 1 / -1;
}

.profile-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.preview-card {
  padding: 18px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 14px;
}

.preview-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.preview-badges span {
  border: 1px solid var(--border);
  border-radius: 999px;
  padding: 8px 12px;
  background: var(--surface-soft);
  font-size: 0.88rem;
}

.success-line {
  margin: 0;
  color: #0f766e;
  font-weight: 600;
}

.error-line {
  margin: 0;
  color: #b91c1c;
}

@media (max-width: 940px) {
  .settings-grid {
    grid-template-columns: 1fr;
  }

  .profile-grid {
    grid-template-columns: 1fr;
  }

  .preview-card {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
