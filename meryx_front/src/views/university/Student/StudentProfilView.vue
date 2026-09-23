<template>
  <div class="student-profile-page">
    <header class="page-header">
      <div>
        <p class="eyebrow">{{ t.eyebrow }}</p>
        <h1>{{ t.title }}</h1>
        <p class="page-copy">{{ t.subtitle }}</p>
      </div>

      <div class="header-actions">
        <button type="button" class="ghost-btn" @click="toggleLocale">
          {{ locale === 'fr' ? 'FR' : 'EN' }}
        </button>
        <button type="button" class="ghost-btn" @click="toggleTheme">
          {{ isDarkMode ? t.lightMode : t.darkMode }}
        </button>
        <button type="button" class="primary-btn" :disabled="saving" @click="saveProfile">
          {{ saving ? t.saving : t.save }}
        </button>
      </div>
    </header>

    <section class="summary-card">
      <img :src="form.personal.photo || fallbackAvatar" :alt="t.studentAvatar" />
      <div>
        <h2>{{ form.personal.fullName || t.unknownStudent }}</h2>
        <p>{{ form.academic.program || '-' }} - {{ form.academic.level || '-' }}</p>
        <div class="summary-meta">
          <span>{{ t.status }}: {{ form.academic.status || '-' }}</span>
          <span>GPA: {{ form.academic.gpa || '0.00' }}</span>
          <span>{{ t.academicYear }}: {{ form.academic.academicYear || '-' }}</span>
        </div>
      </div>
    </section>

    <nav class="section-tabs">
      <button
        v-for="section in sectionList"
        :key="section"
        type="button"
        class="tab-btn"
        :class="{ active: activeSection === section }"
        @click="activeSection = section"
      >
        {{ t.sections[section] }}
      </button>
    </nav>

    <section v-if="activeSection === 'personal'" class="panel">
      <h3>{{ t.sections.personal }}</h3>
      <div class="form-grid two-col">
        <label>
          <span>{{ t.fullName }}</span>
          <input v-model="form.personal.fullName" type="text" />
        </label>
        <label>
          <span>{{ t.gender }}</span>
          <input v-model="form.personal.gender" type="text" />
        </label>
        <label>
          <span>{{ t.email }}</span>
          <input v-model="form.personal.email" type="email" />
        </label>
        <label>
          <span>{{ t.phone }}</span>
          <input v-model="form.personal.phone" type="tel" />
        </label>
        <label>
          <span>{{ t.nationality }}</span>
          <input v-model="form.personal.nationality" type="text" />
        </label>
        <label>
          <span>{{ t.studentId }}</span>
          <input v-model="form.personal.studentId" type="text" />
        </label>
        <label>
          <span>{{ t.birthDate }}</span>
          <input v-model="form.personal.dob" type="date" />
        </label>
        <label>
          <span>{{ t.photoUrl }}</span>
          <input v-model="form.personal.photo" type="url" />
        </label>
        <label class="full">
          <span>{{ t.address }}</span>
          <textarea v-model="form.personal.address" rows="2"></textarea>
        </label>
      </div>
    </section>

    <section v-else-if="activeSection === 'academic'" class="panel">
      <h3>{{ t.sections.academic }}</h3>
      <div class="form-grid two-col">
        <label>
          <span>{{ t.program }}</span>
          <input v-model="form.academic.program" type="text" />
        </label>
        <label>
          <span>{{ t.level }}</span>
          <input v-model="form.academic.level" type="text" />
        </label>
        <label>
          <span>{{ t.faculty }}</span>
          <input v-model="form.academic.faculty" type="text" />
        </label>
        <label>
          <span>{{ t.department }}</span>
          <input v-model="form.academic.department" type="text" />
        </label>
        <label>
          <span>{{ t.academicYear }}</span>
          <input v-model="form.academic.academicYear" type="text" />
        </label>
        <label>
          <span>{{ t.gpa }}</span>
          <input v-model="form.academic.gpa" type="text" />
        </label>
        <label>
          <span>{{ t.status }}</span>
          <input v-model="form.academic.status" type="text" />
        </label>
      </div>
    </section>

    <section v-else class="panel">
      <div class="list-header">
        <h3>{{ t.sections[activeSection] }}</h3>
        <button type="button" class="secondary-btn" @click="addEntry(activeSection)">
          + {{ t.addEntry }}
        </button>
      </div>

      <div v-if="activeEntries.length" class="entry-list">
        <article
          v-for="(entry, index) in activeEntries"
          :key="`${activeSection}-${index}`"
          class="entry-card"
        >
          <div class="entry-actions">
            <strong>#{{ index + 1 }}</strong>
            <button type="button" class="danger-btn" @click="removeEntry(activeSection, index)">
              {{ t.remove }}
            </button>
          </div>

          <div class="form-grid">
            <template v-if="activeSection === 'stage'">
              <label>
                <span>{{ t.company }}</span>
                <input v-model="entry.company" type="text" />
              </label>
              <label>
                <span>{{ t.duration }}</span>
                <input v-model="entry.duration" type="text" />
              </label>
              <label class="full">
                <span>{{ t.description }}</span>
                <textarea v-model="entry.description" rows="2"></textarea>
              </label>
              <label>
                <span>{{ t.proofName }}</span>
                <input v-model="entry.proofName" type="text" />
              </label>
              <label>
                <span>{{ t.proofFile }}</span>
                <input v-model="entry.proofFile" type="text" />
              </label>
            </template>

            <template v-else-if="activeSection === 'numerique'">
              <label>
                <span>{{ t.projectName }}</span>
                <input v-model="entry.projectName" type="text" />
              </label>
              <label class="full">
                <span>{{ t.projectDescription }}</span>
                <textarea v-model="entry.projectDescription" rows="2"></textarea>
              </label>
              <label>
                <span>{{ t.proofName }}</span>
                <input v-model="entry.proofName" type="text" />
              </label>
              <label>
                <span>{{ t.proofFile }}</span>
                <input v-model="entry.proofFile" type="text" />
              </label>
            </template>

            <template v-else-if="activeSection === 'certificate'">
              <label>
                <span>{{ t.trainingName }}</span>
                <input v-model="entry.trainingName" type="text" />
              </label>
              <label>
                <span>{{ t.proofName }}</span>
                <input v-model="entry.proofName" type="text" />
              </label>
              <label>
                <span>{{ t.proofFile }}</span>
                <input v-model="entry.proofFile" type="text" />
              </label>
            </template>

            <template v-else-if="activeSection === 'langue'">
              <label class="full">
                <span>{{ t.languages }}</span>
                <input
                  v-model="entry.languages"
                  type="text"
                  placeholder="English:Fluent; French:Advanced"
                />
              </label>
            </template>
          </div>
        </article>
      </div>

      <p v-else class="empty">{{ t.noEntries }}</p>
    </section>

    <p v-if="message" class="feedback">{{ message }}</p>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useStudentStore } from '@/stores/student.store'
import { useUiPreferences } from '@/compasables/useUiPreferences'

const route = useRoute()
const studentStore = useStudentStore()
const { locale, isDarkMode, initializeUiPreferences, toggleTheme, toggleLocale } =
  useUiPreferences()

const profileId = computed(() => Number(route.params.id || 0))
const activeSection = ref('personal')
const saving = ref(false)
const message = ref('')
const fallbackAvatar = 'https://ui-avatars.com/api/?background=0D8ABC&color=fff&name=Student'

const form = reactive({
  personal: {
    fullName: '',
    gender: '',
    email: '',
    phone: '',
    nationality: '',
    studentId: '',
    dob: '',
    address: '',
    photo: '',
  },
  academic: {
    program: '',
    level: '',
    faculty: '',
    department: '',
    academicYear: '',
    gpa: '0.00',
    status: 'pending',
  },
  radar: {
    stage: { entries: [] },
    numerique: { entries: [] },
    certificate: { entries: [] },
    langue: { entries: [] },
  },
})

const sectionList = ['personal', 'academic', 'stage', 'numerique', 'certificate', 'langue']

const translations = {
  en: {
    eyebrow: 'University Student Profile',
    title: 'Student profile workspace',
    subtitle: 'Manage registration information in the same structure used during student creation.',
    darkMode: 'Dark mode',
    lightMode: 'Light mode',
    save: 'Save profile',
    saving: 'Saving...',
    unknownStudent: 'Unknown student',
    studentAvatar: 'Student avatar',
    status: 'Status',
    academicYear: 'Academic year',
    sections: {
      personal: 'Personal',
      academic: 'Academic',
      stage: 'Stage',
      numerique: 'Numerique',
      certificate: 'Certificate',
      langue: 'Language',
    },
    fullName: 'Full name',
    gender: 'Gender',
    email: 'Email',
    phone: 'Phone',
    nationality: 'Nationality',
    studentId: 'Student ID',
    birthDate: 'Birth date',
    address: 'Address',
    photoUrl: 'Photo URL',
    program: 'Program',
    level: 'Level / Class',
    faculty: 'Faculty',
    department: 'Department',
    gpa: 'GPA',
    addEntry: 'Add entry',
    remove: 'Remove',
    company: 'Company / Organization',
    duration: 'Duration',
    description: 'Description',
    proofName: 'Proof name',
    proofFile: 'Proof file',
    projectName: 'Project name',
    projectDescription: 'Project description',
    trainingName: 'Training name',
    languages: 'Languages',
    noEntries: 'No entries yet for this section.',
    saveOk: 'Profile updated successfully.',
    saveError: 'Unable to update profile right now.',
  },
  fr: {
    eyebrow: 'Profil Etudiant Universite',
    title: 'Espace profil etudiant',
    subtitle: 'Gerez les informations avec la meme structure que lors de la creation etudiante.',
    darkMode: 'Mode sombre',
    lightMode: 'Mode clair',
    save: 'Enregistrer le profil',
    saving: 'Enregistrement...',
    unknownStudent: 'Etudiant inconnu',
    studentAvatar: "Avatar de l'etudiant",
    status: 'Statut',
    academicYear: 'Annee academique',
    sections: {
      personal: 'Personnel',
      academic: 'Academique',
      stage: 'Stage',
      numerique: 'Numerique',
      certificate: 'Certificat',
      langue: 'Langue',
    },
    fullName: 'Nom complet',
    gender: 'Genre',
    email: 'Email',
    phone: 'Telephone',
    nationality: 'Nationalite',
    studentId: 'Matricule',
    birthDate: 'Date de naissance',
    address: 'Adresse',
    photoUrl: 'URL photo',
    program: 'Programme',
    level: 'Niveau / Classe',
    faculty: 'Faculte',
    department: 'Departement',
    gpa: 'Moyenne',
    addEntry: 'Ajouter une entree',
    remove: 'Supprimer',
    company: 'Entreprise / Organisation',
    duration: 'Duree',
    description: 'Description',
    proofName: 'Nom de preuve',
    proofFile: 'Fichier preuve',
    projectName: 'Nom du projet',
    projectDescription: 'Description du projet',
    trainingName: 'Nom de formation',
    languages: 'Langues',
    noEntries: 'Aucune entree pour cette section.',
    saveOk: 'Profil mis a jour avec succes.',
    saveError: 'Impossible de mettre a jour le profil pour le moment.',
  },
}

const t = computed(() => translations[locale.value] || translations.en)

const parseBio = (rawBio) => {
  if (!rawBio) return {}
  if (typeof rawBio === 'object') return rawBio
  try {
    const parsed = JSON.parse(rawBio)
    return parsed && typeof parsed === 'object' ? parsed : {}
  } catch {
    return {}
  }
}

const asEntries = (value, fallbackFactory) => {
  if (Array.isArray(value) && value.length) return value
  return [fallbackFactory()]
}

const emptyStage = () => ({
  company: '',
  duration: '',
  description: '',
  proofName: '',
  proofFile: '',
})
const emptyNumerique = () => ({
  projectName: '',
  projectDescription: '',
  proofName: '',
  proofFile: '',
})
const emptyCertificate = () => ({ trainingName: '', proofName: '', proofFile: '' })
const emptyLangue = () => ({ languages: '' })

const hydrate = () => {
  const profile = studentStore.student
  if (!profile) return

  const meta = parseBio(profile.raw?.bio)
  const personal = meta.personal || {}
  const academic = meta.academic || {}
  const radar = meta.radar || {}

  form.personal.fullName = profile.fullName || personal.fullName || ''
  form.personal.gender = profile.gender || personal.gender || ''
  form.personal.email = profile.email || meta.email || personal.email || ''
  form.personal.phone = profile.phone || meta.phone || personal.phone || ''
  form.personal.nationality = profile.nationality || personal.nationality || ''
  form.personal.studentId = profile.studentId || personal.studentId || ''
  form.personal.dob = profile.admissionDate || personal.dob || ''
  form.personal.address = profile.address || personal.address || ''
  form.personal.photo = profile.profileUrl || personal.profileUrl || ''

  form.academic.program = profile.program || meta.program || academic.program || ''
  form.academic.level = profile.level || meta.level || academic.currentClass || ''
  form.academic.faculty = profile.faculty || meta.faculty || academic.faculty || ''
  form.academic.department = profile.department || meta.department || academic.department || ''
  form.academic.academicYear =
    profile.academicYear || meta.academicYear || academic.academicYear || ''
  form.academic.gpa = profile.gpa || academic.gpa || '0.00'
  form.academic.status = profile.status || meta.status || academic.status || 'pending'

  form.radar.stage.entries = asEntries(radar.stage?.entries, emptyStage)
  form.radar.numerique.entries = asEntries(radar.numerique?.entries, emptyNumerique)
  form.radar.certificate.entries = asEntries(radar.certificate?.entries, emptyCertificate)
  form.radar.langue.entries = asEntries(radar.langue?.entries, emptyLangue)
}

const activeEntries = computed(() => {
  if (activeSection.value === 'stage') return form.radar.stage.entries
  if (activeSection.value === 'numerique') return form.radar.numerique.entries
  if (activeSection.value === 'certificate') return form.radar.certificate.entries
  if (activeSection.value === 'langue') return form.radar.langue.entries
  return []
})

const addEntry = (section) => {
  if (section === 'stage') form.radar.stage.entries.push(emptyStage())
  if (section === 'numerique') form.radar.numerique.entries.push(emptyNumerique())
  if (section === 'certificate') form.radar.certificate.entries.push(emptyCertificate())
  if (section === 'langue') form.radar.langue.entries.push(emptyLangue())
}

const removeEntry = (section, index) => {
  const entries =
    section === 'stage'
      ? form.radar.stage.entries
      : section === 'numerique'
        ? form.radar.numerique.entries
        : section === 'certificate'
          ? form.radar.certificate.entries
          : form.radar.langue.entries

  entries.splice(index, 1)
  if (!entries.length) addEntry(section)
}

const saveProfile = async () => {
  if (!profileId.value) return

  saving.value = true
  message.value = ''

  try {
    await studentStore.updateStudent(profileId.value, {
      fullName: form.personal.fullName,
      email: form.personal.email,
      phone: form.personal.phone,
      gender: form.personal.gender,
      nationality: form.personal.nationality,
      address: form.personal.address,
      studentId: form.personal.studentId,
      admissionDate: form.personal.dob,
      program: form.academic.program,
      level: form.academic.level,
      academicYear: form.academic.academicYear,
      enrollmentYear: form.academic.academicYear,
      faculty: form.academic.faculty,
      department: form.academic.department,
      status: form.academic.status,
      gpa: form.academic.gpa,
      profileUrl: form.personal.photo,
      personal: {
        fullName: form.personal.fullName,
        gender: form.personal.gender,
        dob: form.personal.dob,
        nationality: form.personal.nationality,
        phone: form.personal.phone,
        email: form.personal.email,
        address: form.personal.address,
        studentId: form.personal.studentId,
        profileUrl: form.personal.photo,
      },
      academic: {
        gpa: form.academic.gpa,
        currentClass: form.academic.level,
        program: form.academic.program,
        faculty: form.academic.faculty,
        department: form.academic.department,
        academicYear: form.academic.academicYear,
        status: form.academic.status,
      },
      radar: form.radar,
    })

    await studentStore.fetchStudent(profileId.value)
    hydrate()
    message.value = t.value.saveOk
  } catch (error) {
    console.error('Unable to save student profile', error)
    message.value = t.value.saveError
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  initializeUiPreferences()
  if (!profileId.value) return
  await studentStore.fetchStudent(profileId.value)
  hydrate()
})
</script>

<style scoped>
.student-profile-page {
  min-height: 100vh;
  padding: 24px;
  display: grid;
  gap: 16px;
  background: var(--bg);
  color: var(--text);
}

.page-header {
  display: flex;
  justify-content: space-between;
  gap: 14px;
  align-items: flex-start;
}

.eyebrow {
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: var(--primary);
  font-size: 0.78rem;
  font-weight: 700;
}

.page-copy {
  margin: 8px 0 0;
  color: var(--muted);
  max-width: 700px;
}

.header-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.summary-card,
.panel {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 16px;
  box-shadow: var(--shadow-soft);
}

.summary-card {
  padding: 16px;
  display: flex;
  gap: 14px;
  align-items: center;
}

.summary-card img {
  width: 72px;
  height: 72px;
  border-radius: 14px;
  object-fit: cover;
  border: 1px solid var(--border);
  background: var(--surface-soft);
}

.summary-card h2 {
  margin: 0;
}

.summary-card p {
  margin: 6px 0;
  color: var(--muted);
}

.summary-meta {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.summary-meta span {
  border: 1px solid var(--border);
  background: var(--surface-soft);
  border-radius: 999px;
  padding: 4px 10px;
  font-size: 0.85rem;
}

.section-tabs {
  display: flex;
  gap: 8px;
  overflow: auto;
  padding-bottom: 2px;
}

.tab-btn {
  border: 1px solid var(--border);
  background: var(--surface);
  color: var(--text);
  border-radius: 999px;
  padding: 8px 14px;
  white-space: nowrap;
}

.tab-btn.active {
  background: var(--primary);
  color: var(--meryx-dark);
  border-color: var(--primary);
}

.panel {
  padding: 16px;
}

.panel h3 {
  margin-top: 0;
}

.form-grid {
  display: grid;
  gap: 12px;
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.form-grid.two-col {
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

label {
  display: grid;
  gap: 6px;
}

label span {
  color: var(--muted);
  font-weight: 600;
  font-size: 0.9rem;
}

input,
textarea {
  width: 100%;
  border: 1px solid var(--border);
  background: var(--surface-soft);
  color: var(--text);
  border-radius: 10px;
  padding: 10px 12px;
}

.full {
  grid-column: 1 / -1;
}

.list-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
  margin-bottom: 10px;
}

.entry-list {
  display: grid;
  gap: 10px;
}

.entry-card {
  border: 1px solid var(--border);
  background: var(--surface-soft);
  border-radius: 14px;
  padding: 12px;
  display: grid;
  gap: 12px;
}

.entry-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.danger-btn {
  border: 1px solid rgba(220, 38, 38, 0.4);
  background: rgba(220, 38, 38, 0.12);
  color: #b91c1c;
  border-radius: 999px;
  padding: 6px 10px;
}

.empty,
.feedback {
  margin: 0;
  color: var(--muted);
}

.feedback {
  font-weight: 700;
}

@media (max-width: 900px) {
  .student-profile-page {
    padding: 16px;
  }

  .page-header {
    flex-direction: column;
  }

  .form-grid,
  .form-grid.two-col {
    grid-template-columns: 1fr;
  }
}
</style>
