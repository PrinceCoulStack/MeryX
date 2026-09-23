<template>
  <AuthShell
    eyebrow="MERYX UNIVERSITES"
    title="Activez un pilotage moderne des parcours etudiants."
    description="Votre espace universite centralise la progression academique, les certifications et la creation des profils etudiants sur une base fiable."
    panel-eyebrow="INSCRIPTION"
    panel-title="Creer un compte universite"
    panel-description="Renseignez les informations de votre institution pour lancer votre espace MeryX."
    :highlights="highlights"
  >
    <div v-if="submitted" class="pending-screen">
      <div class="pending-icon">⏳</div>
      <h2>Demande envoyee</h2>
      <p>
        Votre demande d'inscription a ete transmise a notre equipe de validation. Votre universite
        sera examinee avant activation.
      </p>
      <p class="pending-hint">
        Vous pourrez ensuite vous connecter une fois votre compte approuve.
      </p>
      <RouterLink to="/login" class="back-link">Retour a la connexion</RouterLink>
    </div>

    <form v-else class="auth-form" @submit.prevent="register">
      <p v-if="errorMessage" class="error-banner">{{ errorMessage }}</p>

      <fieldset class="section">
        <legend>Informations universitaires</legend>
        <div class="field-grid">
          <label class="field field--full">
            <span>Nom de l'universite *</span>
            <input v-model="form.name" placeholder="Universite Gamal Abdel Nasser" required />
          </label>

          <label class="field">
            <span>Type d'etablissement *</span>
            <select v-model="form.type" required>
              <option value="" disabled>Choisir un type</option>
              <option value="Public">Public</option>
              <option value="Prive">Prive</option>
              <option value="International">International</option>
            </select>
          </label>

          <label class="field">
            <span>Site web</span>
            <input v-model="form.websiteUrl" type="url" placeholder="https://universite.edu" />
          </label>

          <label class="field">
            <span>Numero d'enregistrement *</span>
            <input v-model="form.registrationNumber" placeholder="REG-UNI-2025-001" required />
          </label>

          <label class="field">
            <span>Numero d'accreditation *</span>
            <input v-model="form.accreditationNumber" placeholder="ACC-2025-900" required />
          </label>

          <label class="field field--full">
            <span>Description *</span>
            <textarea
              v-model="form.description"
              rows="3"
              placeholder="Decrivez votre universite, ses programmes, et son role academique..."
              required
            ></textarea>
          </label>
        </div>
      </fieldset>

      <fieldset class="section">
        <legend>Compte administrateur</legend>
        <div class="field-grid">
          <label class="field">
            <span>Adresse email *</span>
            <input
              v-model="form.email"
              type="email"
              placeholder="contact@universite.edu"
              required
            />
          </label>

          <label class="field">
            <span>Telephone *</span>
            <input v-model="form.phone" type="tel" placeholder="+223 00 00 00 00" required />
          </label>

          <label class="field field--full">
            <span>Mot de passe *</span>
            <input
              v-model="form.password"
              type="password"
              placeholder="Choisissez un mot de passe solide"
              required
            />
          </label>
        </div>
      </fieldset>

      <fieldset class="section">
        <legend>Adresse de l'universite</legend>
        <div class="field-grid">
          <label class="field">
            <span>Pays *</span>
            <input v-model="form.country" placeholder="Guinee" required />
          </label>

          <label class="field">
            <span>Ville *</span>
            <input v-model="form.city" placeholder="Conakry" required />
          </label>

          <label class="field">
            <span>Region / Etat *</span>
            <input v-model="form.state" placeholder="Conakry" required />
          </label>

          <label class="field">
            <span>Latitude</span>
            <input v-model="form.latitude" type="number" step="0.000001" placeholder="9.5092" />
          </label>

          <label class="field">
            <span>Longitude</span>
            <input v-model="form.longitude" type="number" step="0.000001" placeholder="-13.7122" />
          </label>
        </div>
      </fieldset>

      <fieldset class="section">
        <legend>Profil visuel</legend>
        <div class="field-grid">
          <label class="field field--full">
            <span>URL du logo (optionnel)</span>
            <input
              v-model="form.logoUrl"
              type="url"
              placeholder="https://universite.edu/logo.png"
            />
          </label>

          <label class="field field--full">
            <span>Logo de l'universite</span>
            <input type="file" accept="image/*" @change="handleLogo" />
          </label>

          <div v-if="logoPreviewUrl" class="media-preview field--full">
            <img :src="logoPreviewUrl" alt="Apercu du logo universite" />
            <span>Apercu du logo</span>
          </div>
        </div>
      </fieldset>

      <button type="submit" class="submit-button" :disabled="auth.loading">
        {{ auth.loading ? 'Envoi en cours...' : 'Soumettre ma demande' }}
      </button>
    </form>

    <template #footer>
      <p class="auth-footer-text" v-if="!submitted">
        Les profils etudiants seront ensuite geres par votre etablissement depuis son espace.
      </p>
    </template>
  </AuthShell>
</template>

<script setup>
import { onBeforeUnmount, reactive, ref } from 'vue'
import { RouterLink } from 'vue-router'
import AuthShell from '@/components/common/AuthShell.vue'
import { useAuthStore } from '@/stores/auth.store'

const auth = useAuthStore()
const submitted = ref(false)
const errorMessage = ref('')

const form = reactive({
  name: '',
  type: '',
  email: '',
  password: '',
  phone: '',
  description: '',
  websiteUrl: '',
  registrationNumber: '',
  accreditationNumber: '',
  rankingScore: 0,
  country: '',
  city: '',
  state: '',
  latitude: '',
  longitude: '',
  logoUrl: '',
})

let logoFile = null
const logoPreviewUrl = ref('')

const highlights = [
  {
    value: '01',
    title: 'Creation des profils etudiants',
    text: 'Structurez et publiez les profils etudiants depuis une source institutionnelle.',
  },
  {
    value: '02',
    title: 'Lecture des progressions',
    text: 'Suivez performances, competences et certifications dans une meme interface.',
  },
  {
    value: '03',
    title: 'Lien direct avec les entreprises',
    text: "Renforcez l'insertion professionnelle avec des profils plus lisibles.",
  },
]

function handleLogo(event) {
  const nextFile = event.target.files[0] || null

  if (logoPreviewUrl.value && logoPreviewUrl.value.startsWith('blob:')) {
    URL.revokeObjectURL(logoPreviewUrl.value)
  }

  logoFile = nextFile
  logoPreviewUrl.value = nextFile ? URL.createObjectURL(nextFile) : form.logoUrl || ''

  if (nextFile && !form.logoUrl) {
    form.logoUrl = nextFile.name
  }
}

onBeforeUnmount(() => {
  if (logoPreviewUrl.value && logoPreviewUrl.value.startsWith('blob:')) {
    URL.revokeObjectURL(logoPreviewUrl.value)
  }
})

async function register() {
  errorMessage.value = ''

  const addressData = {
    city: form.city,
    state: form.state,
    country: form.country,
    latitude: form.latitude || '',
    longitude: form.longitude || '',
  }

  const userData = {
    email: form.email,
    password: form.password,
    phone: form.phone,
    status: 'pending',
    isActived: true,
    userTypeId: '/api/user_types/3',
  }

  const universityData = {
    name: form.name,
    type: form.type,
    description: form.description,
    websiteUrl: form.websiteUrl || '',
    registrationNumber: form.registrationNumber,
    accreditationNumber: form.accreditationNumber,
    rankingScore: 0,
    isApproved: false,
    status: 'pending',
    logoUrl: form.logoUrl || (logoFile ? logoFile.name : ''),
  }

  const result = await auth.registerUniversity(userData, universityData, addressData)

  if (result.ok) {
    submitted.value = true
  } else {
    errorMessage.value = result.message || 'Une erreur est survenue. Veuillez reessayer.'
  }
}
</script>

<style scoped>
.auth-form {
  display: grid;
  gap: 24px;
  margin-top: 24px;
}

.section {
  border: 1px solid rgba(13, 43, 69, 0.1);
  border-radius: 14px;
  padding: 16px;
}

.section legend {
  padding: 0 8px;
  font-size: 0.82rem;
  font-weight: 700;
  color: #114a6a;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.field-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
  margin-top: 12px;
}

.field {
  display: grid;
  gap: 8px;
}

.field--full {
  grid-column: 1 / -1;
}

.field span {
  color: #0d2b45;
  font-size: 0.92rem;
  font-weight: 700;
}

.field input,
.field select,
.field textarea {
  width: 100%;
  padding: 12px 16px;
  border-radius: 14px;
  border: 1px solid rgba(13, 43, 69, 0.14);
  background: #ffffff;
  color: #0d2b45;
  outline: none;
  font-family: inherit;
  font-size: inherit;
  transition:
    border-color 0.2s ease,
    box-shadow 0.2s ease;
}

.field input[type='file'] {
  padding: 10px 14px;
}

.field textarea {
  resize: vertical;
}

.media-preview {
  border: 1px dashed rgba(13, 43, 69, 0.22);
  border-radius: 14px;
  padding: 12px;
  display: grid;
  gap: 8px;
  justify-items: start;
}

.media-preview img {
  width: 72px;
  height: 72px;
  border-radius: 12px;
  object-fit: cover;
  border: 1px solid rgba(13, 43, 69, 0.14);
}

.media-preview span {
  color: rgba(13, 43, 69, 0.72);
  font-size: 0.85rem;
  font-weight: 600;
}

.field input:focus,
.field select:focus,
.field textarea:focus {
  border-color: rgba(17, 74, 106, 0.5);
  box-shadow: 0 0 0 4px rgba(17, 74, 106, 0.12);
}

.submit-button {
  width: 100%;
  padding: 14px 18px;
  border: 0;
  border-radius: 14px;
  background: linear-gradient(120deg, #114a6a, #1e3f66);
  color: #f8fafc;
  font-weight: 800;
  box-shadow: 0 12px 24px rgba(17, 74, 106, 0.22);
}

.pending-screen {
  display: grid;
  gap: 12px;
  text-align: center;
  padding: 26px 12px;
}

.pending-icon {
  font-size: 48px;
}

.pending-hint,
.auth-footer-text {
  margin: 0;
  color: rgba(13, 43, 69, 0.72);
}

.back-link {
  color: #114a6a;
  font-weight: 700;
  text-decoration: none;
}

.error-banner {
  background: rgba(220, 38, 38, 0.08);
  color: #991b1b;
  border: 1px solid rgba(220, 38, 38, 0.2);
  border-radius: 12px;
  padding: 12px 14px;
  margin: 0;
}

@media (max-width: 640px) {
  .field-grid {
    grid-template-columns: 1fr;
  }
}
</style>
