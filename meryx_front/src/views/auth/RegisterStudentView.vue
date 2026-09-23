<template>
  <AuthShell
    eyebrow="MERYX ETUDIANTS"
    title="Demandez la creation de votre profil etudiant en toute confiance."
    description="Votre demande est rattachee a une universite precise puis verifiee avant activation du profil."
    panel-eyebrow="INSCRIPTION"
    panel-title="Creer un profil etudiant"
    panel-description="Renseignez vos informations et l'identite de votre universite pour lancer la verification."
    :highlights="highlights"
  >
    <div v-if="submitted" class="pending-screen">
      <div class="pending-icon">⏳</div>
      <h2>Demande envoyee</h2>
      <p>
        Votre demande a ete transmise a l'universite ciblee. Votre profil reste en attente tant que
        l'universite n'a pas valide les informations.
      </p>
      <p class="pending-hint">Statut possible: approuve, rejete, ou en attente.</p>
      <RouterLink to="/login" class="back-link">Retour a la connexion</RouterLink>
    </div>

    <form v-else class="auth-form" @submit.prevent="register">
      <p v-if="errorMessage" class="error-banner">{{ errorMessage }}</p>

      <fieldset class="section">
        <legend>Informations etudiant</legend>
        <div class="field-grid">
          <label class="field field--full">
            <span>Nom complet *</span>
            <input v-model="form.name" placeholder="Aminata Diallo" required />
          </label>

          <label class="field">
            <span>Adresse email *</span>
            <input v-model="form.email" type="email" placeholder="prenom.nom@email.com" required />
          </label>

          <label class="field">
            <span>Telephone *</span>
            <input v-model="form.phone" type="tel" placeholder="+223 70 00 00 00" required />
          </label>

          <label class="field">
            <span>Mot de passe *</span>
            <input
              v-model="form.password"
              type="password"
              placeholder="Choisissez un mot de passe"
              required
            />
          </label>

          <label class="field">
            <span>Genre *</span>
            <select v-model="form.gender" required>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </select>
          </label>

          <label class="field field--full">
            <span>Filiere / Programme *</span>
            <input v-model="form.field" placeholder="Geologie, Informatique..." required />
          </label>
        </div>
      </fieldset>

      <fieldset class="section">
        <legend>Universite de verification</legend>
        <div class="field-grid">
          <label class="field">
            <span>Nom de l'universite *</span>
            <input v-model="form.universityName" placeholder="Universite de Bamako" required />
          </label>

          <label class="field">
            <span>Email de l'universite *</span>
            <input
              v-model="form.universityEmail"
              type="email"
              placeholder="contact@universite.edu"
              required
            />
          </label>
        </div>
      </fieldset>

      <fieldset class="section">
        <legend>Profil visuel</legend>
        <div class="field-grid">
          <label class="field field--full">
            <span>URL de la photo de profil (optionnel)</span>
            <input
              v-model="form.profileImageUrl"
              type="url"
              placeholder="https://example.com/photo-etudiant.png"
            />
          </label>

          <label class="field field--full">
            <span>Photo de profil</span>
            <input type="file" accept="image/*" @change="handleProfileImage" />
          </label>

          <div v-if="profilePreviewUrl" class="media-preview field--full">
            <img :src="profilePreviewUrl" alt="Apercu de la photo de profil" />
            <span>Apercu de la photo</span>
          </div>
        </div>
      </fieldset>

      <button type="submit" class="submit-button" :disabled="auth.loading">
        {{ auth.loading ? 'Envoi en cours...' : 'Soumettre ma demande' }}
      </button>
    </form>

    <template #footer>
      <p class="auth-footer-text" v-if="!submitted">
        La demande sera visible dans l'interface de l'universite indiquee, qui pourra approuver,
        rejeter, ou laisser en attente.
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
  phone: '',
  password: '',
  gender: 'Male',
  field: '',
  universityName: '',
  universityEmail: '',
  profileImageUrl: '',
})

let profileImageFile = null
const profilePreviewUrl = ref('')

const highlights = [
  {
    value: '01',
    title: 'Rattachement verifiable',
    text: "Le profil est relie a l'universite indiquee par son nom et son email.",
  },
  {
    value: '02',
    title: 'Validation institutionnelle',
    text: "L'universite confirme vos informations avant activation du profil.",
  },
  {
    value: '03',
    title: 'Statut de suivi',
    text: 'Votre demande suit un cycle clair: en attente, approuvee, ou rejetee.',
  },
]

function handleProfileImage(event) {
  const nextFile = event.target.files[0] || null

  if (profilePreviewUrl.value && profilePreviewUrl.value.startsWith('blob:')) {
    URL.revokeObjectURL(profilePreviewUrl.value)
  }

  profileImageFile = nextFile
  profilePreviewUrl.value = nextFile ? URL.createObjectURL(nextFile) : form.profileImageUrl || ''

  if (nextFile && !form.profileImageUrl) {
    form.profileImageUrl = nextFile.name
  }
}

onBeforeUnmount(() => {
  if (profilePreviewUrl.value && profilePreviewUrl.value.startsWith('blob:')) {
    URL.revokeObjectURL(profilePreviewUrl.value)
  }
})

async function register() {
  errorMessage.value = ''

  const userData = {
    email: form.email,
    password: form.password,
    phone: form.phone,
    status: 'pending',
    isActived: true,
    userTypeId: '/api/user_types/4',
  }

  const studentProfileData = {
    fullName: form.name,
    gender: form.gender,
    program: form.field,
    universityName: form.universityName,
    universityEmail: form.universityEmail,
    status: 'pending',
    profileCompletion: 10,
    profileUrl: form.profileImageUrl || (profileImageFile ? profileImageFile.name : ''),
  }

  const result = await auth.registerStudent(userData, studentProfileData)

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
.field select {
  width: 100%;
  padding: 14px 16px;
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

.field input:focus,
.field select:focus {
  border-color: rgba(17, 74, 106, 0.5);
  box-shadow: 0 0 0 4px rgba(17, 74, 106, 0.12);
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

.submit-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
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
