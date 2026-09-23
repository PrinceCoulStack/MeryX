<template>
  <AuthShell
    eyebrow="MERYX ENTREPRISES"
    title="Recrutez avec plus de contexte, plus vite."
    description="Ouvrez votre espace entreprise pour identifier des profils fiables selon leurs competences, performances et progression reelle."
    panel-eyebrow="INSCRIPTION"
    panel-title="Creer un compte entreprise"
    panel-description="Renseignez les informations principales de votre structure pour commencer votre onboarding MeryX."
    :highlights="highlights"
  >
    <!-- ── Pending approval screen ── -->
    <div v-if="submitted" class="pending-screen">
      <div class="pending-icon">⏳</div>
      <h2>Demande envoyee</h2>
      <p>
        Votre dossier a ete transmis a notre equipe de validation. Vous recevrez une confirmation
        par email une fois votre compte approuve.
      </p>
      <p class="pending-hint">En attendant, vous pouvez fermer cette page.</p>
      <RouterLink to="/login" class="back-link">Retour a la connexion</RouterLink>
    </div>

    <!-- ── Registration form ── -->
    <form v-else class="auth-form" @submit.prevent="register">
      <p v-if="errorMessage" class="error-banner">{{ errorMessage }}</p>

      <fieldset class="section">
        <legend>Informations entreprise</legend>
        <div class="field-grid">
          <label class="field field--full">
            <span>Nom de l'entreprise *</span>
            <input v-model="form.name" placeholder="MeryX Mining Group" required />
          </label>

          <label class="field">
            <span>Secteur d'activite *</span>
            <select v-model="form.sector" required>
              <option value="" disabled>Choisir un secteur</option>
              <option v-for="s in sectors" :key="s" :value="s">{{ s }}</option>
            </select>
          </label>

          <label class="field">
            <span>Site web</span>
            <input v-model="form.websiteUrl" type="url" placeholder="https://monentreprise.com" />
          </label>

          <label class="field field--full">
            <span>Description *</span>
            <textarea
              v-model="form.description"
              rows="3"
              placeholder="Decrivez brievement votre entreprise..."
              required
            ></textarea>
          </label>

          <label class="field">
            <span>Numero d'immatriculation *</span>
            <input v-model="form.registrationNumber" placeholder="RC-2024-00123" required />
          </label>

          <label class="field">
            <span>Identifiant fiscal *</span>
            <input v-model="form.taxId" placeholder="TIN-000000000" required />
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
              placeholder="contact@entreprise.com"
              required
            />
          </label>

          <label class="field">
            <span>Telephone *</span>
            <input v-model="form.phone" type="tel" placeholder="+223 62 00 00 00 00" required />
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
        <legend>Adresse de l'entreprise</legend>
        <div class="field-grid">
          <label class="field">
            <span>Pays *</span>
            <input v-model="form.country" placeholder="Mali" required />
          </label>

          <label class="field">
            <span>Ville *</span>
            <input v-model="form.city" placeholder="Bamako" required />
          </label>

          <label class="field">
            <span>Etat / Region *</span>
            <input v-model="form.state" placeholder="Bamako" required />
          </label>

          <label class="field">
            <span>Latitude</span>
            <input v-model="form.latitude" type="number" step="0.000001" placeholder="12.6392" />
          </label>

          <label class="field">
            <span>Longitude</span>
            <input v-model="form.longitude" type="number" step="0.000001" placeholder="-8.0029" />
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
              placeholder="https://monentreprise.com/logo.png"
            />
          </label>

          <label class="field field--full">
            <span>Logo de l'entreprise</span>
            <input type="file" accept="image/*" @change="handleLogo" />
          </label>

          <div v-if="logoPreviewUrl" class="media-preview field--full">
            <img :src="logoPreviewUrl" alt="Apercu du logo entreprise" />
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
        Vous representez une universite ?
        <RouterLink to="/register/university">Creer un compte universite</RouterLink>
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
  email: '',
  password: '',
  phone: '',
  sector: '',
  description: '',
  websiteUrl: '',
  registrationNumber: '',
  rankingScore: '',
  taxId: '',
  country: '',
  city: '',
  state: '',
  latitude: '',
  longitude: '',
  logoUrl: '',
})

let logoFile = null
const logoPreviewUrl = ref('')

const sectors = [
  'Mines & Ressources naturelles',
  'Energie & Utilities',
  'Finance & Banque',
  'Technologie & Telecoms',
  'Construction & BTP',
  'Agriculture & Agroalimentaire',
  'Sante & Pharmaceutique',
  'Commerce & Distribution',
  'Transport & Logistique',
  'Education & Formation',
  'Autre',
]

const highlights = [
  {
    value: '01',
    title: 'Sourcing qualifie',
    text: 'Reperez les bons profils via leurs competences, leurs certifications et leur progression.',
  },
  {
    value: '02',
    title: 'Visibilite immediate',
    text: 'Publiez vos opportunites et centralisez vos interactions avec les talents.',
  },
  {
    value: '03',
    title: 'Decision plus sure',
    text: 'Comparez les candidats sur des donnees structurees plutot que sur un CV seul.',
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

  // User data (minimal fields for auth)
  // Note: userTypeId should be the ID of 'Company' UserType from backend
  const userData = {
    email: form.email,
    password: form.password,
    phone: form.phone,
    status: 'pending',
    isActived: true,
    userTypeId: '/api/userTypes/2', // Adjust ID based on your UserType IDs
  }

  // Company data (business details)
  const companyData = {
    name: form.name,
    sector: form.sector,
    description: form.description,
    websiteUrl: form.websiteUrl || '',
    registrationNumber: form.registrationNumber,
    rankingScore: 0,
    taxId: form.taxId,
    isApproved: false,
    status: 'pending',
    logo: form.logoUrl || (logoFile ? logoFile.name : ''),
  }

  const result = await auth.registerCompany(userData, companyData, addressData)

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
  font-size: 1rem;
  cursor: pointer;
  box-shadow: 0 12px 24px rgba(17, 74, 106, 0.22);
  transition: opacity 0.2s ease;
}

.submit-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error-banner {
  padding: 12px 16px;
  border-radius: 10px;
  background: rgba(220, 38, 38, 0.08);
  border: 1px solid rgba(220, 38, 38, 0.2);
  color: #dc2626;
  font-size: 0.9rem;
  margin: 0;
}

/* Pending screen */
.pending-screen {
  display: grid;
  gap: 16px;
  margin-top: 32px;
  text-align: center;
  padding: 24px;
  border: 1px solid rgba(17, 74, 106, 0.12);
  border-radius: 16px;
  background: rgba(17, 74, 106, 0.03);
}

.pending-icon {
  font-size: 2.5rem;
}

.pending-screen h2 {
  margin: 0;
  color: #0d2b45;
  font-size: 1.3rem;
}

.pending-screen p {
  margin: 0;
  color: rgba(13, 43, 69, 0.72);
  font-size: 0.95rem;
  line-height: 1.6;
}

.pending-hint {
  font-size: 0.85rem !important;
  color: rgba(13, 43, 69, 0.45) !important;
}

.back-link {
  display: inline-block;
  margin-top: 8px;
  color: #114a6a;
  font-weight: 700;
  text-decoration: none;
}

.auth-footer-text {
  margin: 0;
  color: rgba(13, 43, 69, 0.72);
}

.auth-footer-text a {
  color: #114a6a;
  font-weight: 700;
}

@media (max-width: 640px) {
  .field-grid {
    grid-template-columns: 1fr;
  }
}
</style>
