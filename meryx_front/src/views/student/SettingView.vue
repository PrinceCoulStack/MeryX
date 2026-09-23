<template>
  <div class="student-settings-page">
    <section class="header-card">
      <p class="eyebrow">Student / Settings</p>
      <h1>{{ t.title }}</h1>
      <p>{{ t.subtitle }}</p>
    </section>

    <section class="settings-grid">
      <article class="setting-card profile-card-wide">
        <div class="card-head">
          <h2>{{ t.profileTitle }}</h2>
          <span>{{ profileStatus }}</span>
        </div>
        <p>{{ t.profileText }}</p>

        <form class="form-grid" @submit.prevent="saveProfile">
          <label>
            <span>{{ t.fullName }}</span>
            <input v-model.trim="profileForm.fullName" type="text" required />
          </label>

          <label>
            <span>{{ t.email }}</span>
            <input v-model.trim="profileForm.email" type="email" required />
          </label>

          <label>
            <span>{{ t.phone }}</span>
            <input v-model.trim="profileForm.phone" type="text" />
          </label>

          <label>
            <span>{{ t.gender }}</span>
            <select v-model="profileForm.gender">
              <option value="Male">Male</option>
              <option value="Female">Female</option>
              <option value="prefer_not_to_say">Prefer not to say</option>
            </select>
          </label>

          <label>
            <span>{{ t.program }}</span>
            <input v-model.trim="profileForm.program" type="text" />
          </label>

          <label>
            <span>{{ t.level }}</span>
            <input v-model.trim="profileForm.level" type="text" />
          </label>

          <label>
            <span>{{ t.academicYear }}</span>
            <input v-model.trim="profileForm.academicYear" type="text" />
          </label>

          <label>
            <span>{{ t.address }}</span>
            <input v-model.trim="profileForm.address" type="text" />
          </label>

          <label>
            <span>{{ t.university }}</span>
            <input :value="profileForm.universityName" type="text" readonly />
          </label>

          <div class="row-actions">
            <button type="button" class="option-btn" @click="resetProfileForm">
              {{ t.reset }}
            </button>
            <button type="submit" class="option-btn active" :disabled="savingProfile">
              {{ savingProfile ? t.saving : t.saveProfile }}
            </button>
          </div>
        </form>

        <p v-if="profileMessage" class="status-message">{{ profileMessage }}</p>
      </article>

      <article class="setting-card">
        <div class="card-head">
          <h2>{{ t.passwordTitle }}</h2>
          <span>{{ t.securityBadge }}</span>
        </div>
        <p>{{ t.passwordText }}</p>

        <form class="form-grid" @submit.prevent="changePassword">
          <label>
            <span>{{ t.newPassword }}</span>
            <input v-model="passwordForm.newPassword" type="password" minlength="8" required />
          </label>

          <label>
            <span>{{ t.confirmPassword }}</span>
            <input v-model="passwordForm.confirmPassword" type="password" minlength="8" required />
          </label>

          <div class="row-actions">
            <button type="button" class="option-btn" @click="resetPasswordForm">
              {{ t.reset }}
            </button>
            <button type="submit" class="option-btn active" :disabled="savingPassword">
              {{ savingPassword ? t.saving : t.updatePassword }}
            </button>
          </div>
        </form>

        <p v-if="passwordMessage" class="status-message">{{ passwordMessage }}</p>
      </article>

      <article class="setting-card">
        <div class="card-head">
          <h2>{{ t.languageTitle }}</h2>
          <span>{{ locale.toUpperCase() }}</span>
        </div>
        <p>{{ t.languageText }}</p>

        <div class="option-grid">
          <button
            class="option-btn"
            :class="{ active: locale === 'en' }"
            @click="setLocale('en')"
            type="button"
          >
            <strong>English</strong>
            <small>{{ t.englishHelp }}</small>
          </button>
          <button
            class="option-btn"
            :class="{ active: locale === 'fr' }"
            @click="setLocale('fr')"
            type="button"
          >
            <strong>Francais</strong>
            <small>{{ t.frenchHelp }}</small>
          </button>
        </div>
      </article>

      <article class="setting-card">
        <div class="card-head">
          <h2>{{ t.themeTitle }}</h2>
          <span>{{ themeLabel }}</span>
        </div>
        <p>{{ t.themeText }}</p>

        <div class="option-grid">
          <button
            class="option-btn"
            :class="{ active: theme === 'light' }"
            @click="setTheme('light')"
            type="button"
          >
            <strong>{{ t.light }}</strong>
            <small>{{ t.lightHelp }}</small>
          </button>
          <button
            class="option-btn"
            :class="{ active: theme === 'dark' }"
            @click="setTheme('dark')"
            type="button"
          >
            <strong>{{ t.dark }}</strong>
            <small>{{ t.darkHelp }}</small>
          </button>
        </div>
      </article>
    </section>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import api from '@/api/axios'
import { useUiPreferences } from '@/compasables/useUiPreferences'
import { useAuthStore } from '@/stores/auth.store'
import { useStudentStore } from '@/stores/student.store'

const { locale, theme, setLocale, setTheme } = useUiPreferences()
const authStore = useAuthStore()
const studentStore = useStudentStore()

const loadingProfile = ref(false)
const savingProfile = ref(false)
const savingPassword = ref(false)
const profileMessage = ref('')
const passwordMessage = ref('')
const selectedStudent = ref(null)

const profileForm = reactive({
  fullName: '',
  email: '',
  phone: '',
  gender: 'prefer_not_to_say',
  program: '',
  level: '',
  academicYear: '',
  address: '',
  universityName: '',
})

const passwordForm = reactive({
  newPassword: '',
  confirmPassword: '',
})

const parseId = (value) => {
  if (!value && value !== 0) return null
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

const parseMeta = (student) => {
  const rawBio = student?.raw?.bio
  if (!rawBio) return {}
  try {
    const parsed = JSON.parse(rawBio)
    return parsed && typeof parsed === 'object' ? parsed : {}
  } catch {
    return {}
  }
}

const authUserId = computed(() => parseId(authStore.user?.id || authStore.user?.userId))
const authUserEmail = computed(() =>
  String(authStore.user?.email || '')
    .trim()
    .toLowerCase(),
)

const copy = {
  en: {
    title: 'Student Interface Settings',
    subtitle: 'Adjust language and appearance preferences. Changes apply on all student pages.',
    languageTitle: 'Language',
    languageText: 'Choose the language for student navigation and shared interface labels.',
    englishHelp: 'Default English labels',
    frenchHelp: 'French interface labels',
    themeTitle: 'Appearance',
    themeText: 'Choose your preferred display mode for all student pages.',
    light: 'Light',
    dark: 'Dark',
    lightHelp: 'Bright interface for daytime use',
    darkHelp: 'Dark interface for low light and focus',
    profileTitle: 'My Profile Information',
    profileText: 'Update your student information used across your profile and applications.',
    passwordTitle: 'Password',
    passwordText: 'Set a new password for your account.',
    securityBadge: 'Security',
    fullName: 'Full name',
    email: 'Email',
    phone: 'Phone',
    gender: 'Gender',
    program: 'Program',
    level: 'Level / Class',
    academicYear: 'Academic year',
    address: 'Address',
    university: 'University',
    reset: 'Reset',
    saveProfile: 'Save Profile',
    updatePassword: 'Update Password',
    saving: 'Saving...',
    profileReady: 'Profile loaded',
    profileLoading: 'Loading...',
    profileSaved: 'Profile updated successfully.',
    profileSaveError: 'Unable to update profile now. Please retry.',
    passwordMismatch: 'Passwords do not match.',
    passwordTooShort: 'Password must contain at least 8 characters.',
    passwordSaved: 'Password updated successfully.',
    passwordSaveError: 'Unable to update password now. Please retry.',
    newPassword: 'New password',
    confirmPassword: 'Confirm password',
  },
  fr: {
    title: "Parametres de l'interface etudiant",
    subtitle:
      "Ajustez la langue et l'apparence. Les changements s'appliquent a toutes les pages etudiant.",
    languageTitle: 'Langue',
    languageText: "Choisissez la langue pour la navigation et les libelles communs de l'interface.",
    englishHelp: 'Libelles anglais par defaut',
    frenchHelp: "Libelles de l'interface en francais",
    themeTitle: 'Apparence',
    themeText: "Choisissez votre mode d'affichage pour toutes les pages etudiant.",
    light: 'Clair',
    dark: 'Sombre',
    lightHelp: 'Interface lumineuse pour la journee',
    darkHelp: 'Interface sombre pour la concentration',
    profileTitle: 'Mes informations de profil',
    profileText: 'Mettez a jour vos informations etudiant utilisees dans votre profil.',
    passwordTitle: 'Mot de passe',
    passwordText: 'Definissez un nouveau mot de passe pour votre compte.',
    securityBadge: 'Securite',
    fullName: 'Nom complet',
    email: 'Email',
    phone: 'Telephone',
    gender: 'Genre',
    program: 'Programme',
    level: 'Niveau / Classe',
    academicYear: 'Annee academique',
    address: 'Adresse',
    university: 'Universite',
    reset: 'Reinitialiser',
    saveProfile: 'Enregistrer le profil',
    updatePassword: 'Mettre a jour le mot de passe',
    saving: 'Enregistrement...',
    profileReady: 'Profil charge',
    profileLoading: 'Chargement...',
    profileSaved: 'Profil mis a jour avec succes.',
    profileSaveError: 'Impossible de mettre a jour le profil. Veuillez reessayer.',
    passwordMismatch: 'Les mots de passe ne correspondent pas.',
    passwordTooShort: 'Le mot de passe doit contenir au moins 8 caracteres.',
    passwordSaved: 'Mot de passe mis a jour avec succes.',
    passwordSaveError: 'Impossible de mettre a jour le mot de passe. Veuillez reessayer.',
    newPassword: 'Nouveau mot de passe',
    confirmPassword: 'Confirmer le mot de passe',
  },
}

const t = computed(() => copy[locale.value] || copy.en)
const themeLabel = computed(() => (theme.value === 'dark' ? t.value.dark : t.value.light))
const profileStatus = computed(() =>
  loadingProfile.value ? t.value.profileLoading : t.value.profileReady,
)

const fillProfileForm = (student) => {
  const meta = parseMeta(student)
  const personal = meta.personal || {}
  const academic = meta.academic || {}
  const verification = student?.raw?.verificationData || {}

  profileForm.fullName = student?.fullName || personal.fullName || authStore.user?.fullName || ''
  profileForm.email =
    student?.email || verification.email || personal.email || authStore.user?.email || ''
  profileForm.phone = student?.phone || verification.phone || personal.phone || ''
  profileForm.gender =
    student?.gender || verification.gender || personal.gender || 'prefer_not_to_say'
  profileForm.program = student?.program || verification.program || academic.program || ''
  profileForm.level =
    student?.level || verification.level || academic.currentClass || academic.level || ''
  profileForm.academicYear =
    student?.academicYear || academic.academicYear || verification.academicYear || ''
  profileForm.address = student?.address || personal.address || verification.address || ''
  profileForm.universityName =
    student?.university?.name || verification.universityName || meta.universityName || ''
}

const findCurrentStudent = () =>
  studentStore.students.find((student) => {
    const sameUser = authUserId.value && Number(student.userId) === Number(authUserId.value)
    const sameEmail =
      authUserEmail.value &&
      String(student.email || '')
        .trim()
        .toLowerCase() === authUserEmail.value
    return sameUser || sameEmail
  })

const loadProfile = async () => {
  loadingProfile.value = true
  profileMessage.value = ''
  try {
    await studentStore.fetchStudents()
    const found = findCurrentStudent()
    if (found?.id) {
      await studentStore.fetchStudent(found.id)
      selectedStudent.value = studentStore.student || found
      fillProfileForm(selectedStudent.value)
      return
    }

    selectedStudent.value = null
    fillProfileForm(null)
  } finally {
    loadingProfile.value = false
  }
}

const resetProfileForm = () => {
  fillProfileForm(selectedStudent.value)
  profileMessage.value = ''
}

const saveProfile = async () => {
  if (!selectedStudent.value?.id) return

  savingProfile.value = true
  profileMessage.value = ''

  try {
    await studentStore.updateStudent(selectedStudent.value.id, {
      fullName: profileForm.fullName,
      email: profileForm.email,
      phone: profileForm.phone,
      gender: profileForm.gender,
      program: profileForm.program,
      level: profileForm.level,
      academicYear: profileForm.academicYear,
      enrollmentYear: profileForm.academicYear,
      address: profileForm.address,
      personal: {
        fullName: profileForm.fullName,
        email: profileForm.email,
        phone: profileForm.phone,
        gender: profileForm.gender,
        address: profileForm.address,
      },
      academic: {
        program: profileForm.program,
        currentClass: profileForm.level,
        level: profileForm.level,
        academicYear: profileForm.academicYear,
      },
    })

    if (selectedStudent.value.userId) {
      await api.updateItem('users', selectedStudent.value.userId, {
        email: profileForm.email,
        phone: profileForm.phone,
      })
    }

    await loadProfile()
    profileMessage.value = t.value.profileSaved
  } catch {
    profileMessage.value = t.value.profileSaveError
  } finally {
    savingProfile.value = false
  }
}

const resetPasswordForm = () => {
  passwordForm.newPassword = ''
  passwordForm.confirmPassword = ''
  passwordMessage.value = ''
}

const changePassword = async () => {
  passwordMessage.value = ''

  if (!selectedStudent.value?.userId) {
    passwordMessage.value = t.value.passwordSaveError
    return
  }

  if (passwordForm.newPassword !== passwordForm.confirmPassword) {
    passwordMessage.value = t.value.passwordMismatch
    return
  }

  if (passwordForm.newPassword.length < 8) {
    passwordMessage.value = t.value.passwordTooShort
    return
  }

  savingPassword.value = true
  try {
    await api.updateItem('users', selectedStudent.value.userId, {
      password: passwordForm.newPassword,
    })
    resetPasswordForm()
    passwordMessage.value = t.value.passwordSaved
  } catch {
    passwordMessage.value = t.value.passwordSaveError
  } finally {
    savingPassword.value = false
  }
}

onMounted(() => {
  loadProfile().catch(() => {})
})
</script>

<style scoped>
.student-settings-page {
  display: grid;
  gap: 16px;
  padding: 14px;
}

.header-card,
.setting-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 18px;
  box-shadow: 0 10px 26px rgba(15, 23, 42, 0.05);
}

.header-card {
  padding: 18px;
}

.eyebrow {
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  color: var(--primary);
  font-size: 0.74rem;
  font-weight: 700;
}

h1,
h2 {
  margin: 0;
  color: var(--text);
}

.header-card h1 {
  margin-top: 8px;
  font-size: 1.6rem;
}

.header-card p,
.setting-card p,
.card-head span,
.option-btn small {
  color: var(--muted);
}

.settings-grid {
  display: grid;
  gap: 14px;
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.setting-card {
  padding: 16px;
  display: grid;
  gap: 10px;
}

.profile-card-wide {
  grid-column: 1 / -1;
}

.card-head {
  display: flex;
  justify-content: space-between;
  gap: 10px;
  align-items: center;
}

.option-grid {
  display: grid;
  gap: 10px;
}

.option-btn {
  border: 1px solid var(--border);
  border-radius: 14px;
  background: var(--surface-soft);
  text-align: left;
  cursor: pointer;
  padding: 12px;
  display: grid;
  gap: 4px;
}

.option-btn strong {
  color: var(--text);
}

.option-btn.active {
  border-color: rgba(6, 170, 197, 0.45);
  background: rgba(6, 170, 197, 0.1);
}

.form-grid {
  display: grid;
  gap: 10px;
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.form-grid label {
  display: grid;
  gap: 6px;
}

.form-grid span {
  color: var(--text);
  font-weight: 600;
}

.form-grid input,
.form-grid select {
  border: 1px solid var(--border);
  border-radius: 12px;
  background: var(--surface-soft);
  color: var(--text);
  padding: 10px 12px;
}

.form-grid input[readonly] {
  opacity: 0.75;
  cursor: not-allowed;
}

.row-actions {
  grid-column: 1 / -1;
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

.status-message {
  margin: 0;
  color: var(--text);
  font-weight: 600;
}

@media (max-width: 900px) {
  .settings-grid {
    grid-template-columns: 1fr;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }

  .row-actions {
    flex-direction: column;
  }
}
</style>
