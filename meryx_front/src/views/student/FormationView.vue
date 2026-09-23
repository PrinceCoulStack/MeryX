<template>
  <div class="training-page">
    <section class="hero-card">
      <div>
        <p class="eyebrow">{{ t.eyebrow }}</p>
        <h1>{{ t.title }}</h1>
        <p class="hero-text">
          {{ t.subtitle }}
        </p>
        <p v-if="studentProfile" class="profile-fit-note">
          {{ t.personalizedFor }}
          <strong>{{ studentProfile.program || t.yourProfile }}</strong>
        </p>
        <p v-else class="profile-fit-note muted-note">{{ t.profileHint }}</p>
      </div>

      <div class="hero-kpis">
        <article class="kpi-item">
          <span>{{ t.totalPrograms }}</span>
          <strong>{{ filteredPrograms.length }}</strong>
        </article>
        <article class="kpi-item">
          <span>{{ t.myApplications }}</span>
          <strong>{{ myApplications.length }}</strong>
        </article>
        <article class="kpi-item">
          <span>{{ t.recommendedPrograms }}</span>
          <strong>{{ recommendedCount }}</strong>
        </article>
      </div>
    </section>

    <section class="toolbar-card">
      <div class="search-box">
        <i class="bi bi-search"></i>
        <input v-model="search" type="search" :placeholder="t.searchPlaceholder" />
      </div>

      <div class="toolbar-filters">
        <label>
          <span>{{ t.filterType }}</span>
          <select v-model="typeFilter">
            <option value="all">{{ t.allTypes }}</option>
            <option value="Internship">{{ t.internship }}</option>
            <option value="Bootcamp">{{ t.bootcamp }}</option>
            <option value="Graduate Program">{{ t.graduateProgram }}</option>
            <option value="Certification">{{ t.certification }}</option>
          </select>
        </label>

        <label>
          <span>{{ t.filterLocation }}</span>
          <select v-model="locationFilter">
            <option value="all">{{ t.allLocations }}</option>
            <option v-for="city in locationOptions" :key="city" :value="city">{{ city }}</option>
          </select>
        </label>
      </div>

      <div class="recommendation-banner" v-if="studentProfile">
        <strong>{{ recommendedCount }}</strong>
        <span>{{ t.matchingProfile }}</span>
      </div>
    </section>

    <p v-if="isPageLoading" class="info-state">{{ t.loading }}</p>
    <p v-else-if="loadError" class="error-state">{{ loadError }}</p>

    <section class="workspace-grid">
      <article class="program-list-card">
        <div class="section-head">
          <h2>{{ t.programs }}</h2>
          <span>{{ filteredPrograms.length }} {{ t.items }}</span>
        </div>

        <div class="program-list" v-if="filteredPrograms.length">
          <button
            v-for="program in filteredPrograms"
            :key="program.id"
            class="program-item"
            :class="{
              active: selectedProgram?.id === program.id,
              recommended: isRecommended(program),
            }"
            type="button"
            @click="selectProgram(program)"
          >
            <div class="program-header">
              <strong>{{ program.title }}</strong>
              <span class="type-pill">{{ program.type }}</span>
            </div>
            <div v-if="matchReasons(program).length" class="match-pill-row">
              <span class="match-pill">{{ t.recommended }}</span>
              <small>{{ matchReasons(program)[0] }}</small>
            </div>
            <p>{{ program.company }}</p>
            <div class="program-meta">
              <span><i class="bi bi-geo-alt"></i> {{ program.place }}</span>
              <span><i class="bi bi-calendar-event"></i> {{ formatDate(program.startDate) }}</span>
              <span><i class="bi bi-calendar-check"></i> {{ formatDate(program.endDate) }}</span>
            </div>
            <div class="program-footer">
              <small>{{ program.seats }} {{ t.seats }}</small>
              <small>{{ program.duration }}</small>
            </div>
          </button>
        </div>
        <div class="empty-state" v-else>{{ t.noPrograms }}</div>
      </article>

      <article class="detail-card" v-if="selectedProgram">
        <div class="section-head">
          <h2>{{ selectedProgram.title }}</h2>
          <button class="primary-btn" type="button" @click="openApplyModal(selectedProgram)">
            <i class="bi bi-send"></i>
            {{ hasApplied(selectedProgram.id) ? t.updatePost : t.postApplication }}
          </button>
        </div>

        <div class="detail-grid">
          <div class="info-block">
            <span>{{ t.company }}</span>
            <strong>{{ selectedProgram.company }}</strong>
          </div>
          <div class="info-block">
            <span>{{ t.place }}</span>
            <strong>{{ selectedProgram.place }}</strong>
          </div>
          <div class="info-block">
            <span>{{ t.startDate }}</span>
            <strong>{{ formatDate(selectedProgram.startDate) }}</strong>
          </div>
          <div class="info-block">
            <span>{{ t.endDate }}</span>
            <strong>{{ formatDate(selectedProgram.endDate) }}</strong>
          </div>
        </div>

        <p class="detail-description">{{ selectedProgram.description }}</p>

        <div class="followup-card" v-if="applicationForSelected">
          <div class="section-head compact">
            <h3>{{ t.followUp }}</h3>
            <span class="status-pill" :class="statusClass(applicationForSelected.status)">
              {{ applicationForSelected.status }}
            </span>
          </div>

          <p>
            {{ t.appliedOn }}: {{ formatDate(applicationForSelected.appliedOn) }}
            <span class="separator">•</span>
            {{ t.contactPerson }}: {{ selectedProgram.contactName }}
          </p>

          <div class="timeline-grid">
            <div>
              <small>{{ t.programPlace }}</small>
              <strong>{{ selectedProgram.place }}</strong>
            </div>
            <div>
              <small>{{ t.programStart }}</small>
              <strong>{{ formatDate(selectedProgram.startDate) }}</strong>
            </div>
            <div>
              <small>{{ t.programEnd }}</small>
              <strong>{{ formatDate(selectedProgram.endDate) }}</strong>
            </div>
          </div>
        </div>

        <div class="contact-card">
          <div class="section-head compact">
            <h3>{{ t.directContact }}</h3>
            <span>{{ selectedProgram.contactEmail }}</span>
          </div>

          <div class="messages-list" v-if="messageThread.length">
            <div
              v-for="message in messageThread"
              :key="message.id"
              class="message-item"
              :class="message.from === 'student' ? 'mine' : 'company'"
            >
              <p>{{ message.text }}</p>
              <small>{{ message.time }}</small>
            </div>
          </div>

          <div class="empty-state" v-else>{{ t.noMessages }}</div>

          <form class="message-form" @submit.prevent="sendMessage">
            <input v-model="messageText" type="text" :placeholder="t.messagePlaceholder" />
            <button class="secondary-btn" type="submit" :disabled="!messageText.trim()">
              {{ t.send }}
            </button>
          </form>
        </div>
      </article>

      <article class="detail-card empty-detail" v-else>
        <div class="empty-state">{{ t.selectProgram }}</div>
      </article>
    </section>

    <section class="applications-card">
      <div class="section-head">
        <h2>{{ t.myPostedApplications }}</h2>
        <span>{{ myApplications.length }} {{ t.items }}</span>
      </div>

      <div class="applications-table-wrap" v-if="myApplications.length">
        <table>
          <thead>
            <tr>
              <th>{{ t.program }}</th>
              <th>{{ t.company }}</th>
              <th>{{ t.place }}</th>
              <th>{{ t.startDate }}</th>
              <th>{{ t.endDate }}</th>
              <th>{{ t.status }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="application in myApplications" :key="application.id">
              <td>{{ application.programTitle }}</td>
              <td>{{ application.company }}</td>
              <td>{{ application.place }}</td>
              <td>{{ formatDate(application.startDate) }}</td>
              <td>{{ formatDate(application.endDate) }}</td>
              <td>
                <span class="status-pill" :class="statusClass(application.status)">{{
                  application.status
                }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="empty-state" v-else>{{ t.noApplications }}</div>
    </section>

    <section v-if="applyModalOpen" class="overlay" @click="closeApplyModal"></section>
    <section v-if="applyModalOpen" class="apply-modal">
      <div class="section-head compact">
        <h3>{{ t.postForProgram }}</h3>
        <button class="icon-btn" type="button" @click="closeApplyModal">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      <p>
        <strong>{{ applyingProgram?.title }}</strong> - {{ applyingProgram?.company }}
      </p>

      <label>
        <span>{{ t.motivation }}</span>
        <textarea
          v-model="applicationMessage"
          rows="4"
          :placeholder="t.motivationPlaceholder"
        ></textarea>
      </label>

      <div class="modal-actions">
        <button class="ghost-btn" type="button" @click="closeApplyModal">{{ t.cancel }}</button>
        <button
          class="primary-btn"
          type="button"
          @click="submitApplication"
          :disabled="!applicationMessage.trim()"
        >
          {{ t.submitPost }}
        </button>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed, inject, onMounted, ref, watch } from 'vue'
import api from '@/api/axios'
import { useCompanyTrainings } from '@/compasables/useCompanyTrainings'
import { useAuthStore } from '@/stores/auth.store'
import { useStudentStore } from '@/stores/student.store'

const locale = inject('locale', ref('en'))
const authStore = useAuthStore()
const studentStore = useStudentStore()
const { fetchTrainings, isLoadingTrainings, trainingError } = useCompanyTrainings()

const translations = {
  en: {
    eyebrow: 'Student / Training Hub',
    title: 'Training Programs From Companies',
    subtitle:
      'Browse training programs published by partner companies, post your application, and stay in direct contact to track location and schedule.',
    personalizedFor: 'Recommendations based on',
    yourProfile: 'your profile',
    profileHint: 'Complete your student profile to improve training recommendations.',
    totalPrograms: 'Programs',
    myApplications: 'My Applications',
    recommendedPrograms: 'Recommended',
    searchPlaceholder: 'Search by program, company, skill, or location',
    filterType: 'Program Type',
    filterLocation: 'Location',
    allTypes: 'All types',
    internship: 'Internship',
    bootcamp: 'Bootcamp',
    graduateProgram: 'Graduate Program',
    certification: 'Certification',
    allLocations: 'All locations',
    programs: 'Company Programs',
    items: 'items',
    seats: 'seats',
    noPrograms: 'No programs found for the selected filters.',
    postApplication: 'Post Application',
    updatePost: 'Update Post',
    company: 'Company',
    place: 'Place',
    startDate: 'Start Date',
    endDate: 'End Date',
    followUp: 'Follow-up Tracker',
    appliedOn: 'Applied on',
    contactPerson: 'Contact',
    programPlace: 'Program Place',
    programStart: 'Program Start',
    programEnd: 'Program End',
    directContact: 'Direct Contact With Company',
    noMessages: 'No messages yet. Start the conversation with the company.',
    messagePlaceholder: 'Write a message to the company contact...',
    send: 'Send',
    selectProgram: 'Select a program to see full details and contact history.',
    myPostedApplications: 'My Posted Applications',
    program: 'Program',
    status: 'Status',
    noApplications: 'You have not posted an application yet.',
    postForProgram: 'Post Application',
    motivation: 'Motivation Message',
    motivationPlaceholder: 'Introduce your profile, availability, and why you fit this program.',
    cancel: 'Cancel',
    submitPost: 'Submit Post',
    matchingProfile: 'trainings match your program, skills, projects, or career goals.',
    recommended: 'Recommended',
    loading: 'Loading trainings and your profile...',
  },
  fr: {
    eyebrow: 'Etudiant / Hub de formation',
    title: 'Programmes de formation des entreprises',
    subtitle:
      "Consultez les programmes publies par les entreprises partenaires, postulez et suivez directement le lieu et le calendrier avec l'entreprise.",
    personalizedFor: 'Recommandations basees sur',
    yourProfile: 'votre profil',
    profileHint: 'Completez votre profil etudiant pour ameliorer les recommandations de formation.',
    totalPrograms: 'Programmes',
    myApplications: 'Mes candidatures',
    recommendedPrograms: 'Recommandes',
    searchPlaceholder: 'Rechercher par programme, entreprise, competence ou lieu',
    filterType: 'Type de programme',
    filterLocation: 'Lieu',
    allTypes: 'Tous les types',
    internship: 'Stage',
    bootcamp: 'Bootcamp',
    graduateProgram: 'Programme graduate',
    certification: 'Certification',
    allLocations: 'Tous les lieux',
    programs: 'Programmes entreprises',
    items: 'elements',
    seats: 'places',
    noPrograms: 'Aucun programme trouve pour les filtres selectionnes.',
    postApplication: 'Poster candidature',
    updatePost: 'Mettre a jour',
    company: 'Entreprise',
    place: 'Lieu',
    startDate: 'Date de debut',
    endDate: 'Date de fin',
    followUp: 'Suivi de candidature',
    appliedOn: 'Postule le',
    contactPerson: 'Contact',
    programPlace: 'Lieu du programme',
    programStart: 'Debut du programme',
    programEnd: 'Fin du programme',
    directContact: "Contact direct avec l'entreprise",
    noMessages: 'Aucun message pour le moment. Lancez la conversation avec l entreprise.',
    messagePlaceholder: "Ecrire un message au contact de l'entreprise...",
    send: 'Envoyer',
    selectProgram: 'Selectionnez un programme pour voir les details et le suivi.',
    myPostedApplications: 'Mes candidatures postees',
    program: 'Programme',
    status: 'Statut',
    noApplications: "Vous n'avez pas encore poste de candidature.",
    postForProgram: 'Poster candidature',
    motivation: 'Message de motivation',
    motivationPlaceholder: 'Presentez votre profil, disponibilite et motivation pour ce programme.',
    cancel: 'Annuler',
    submitPost: 'Envoyer candidature',
    matchingProfile:
      'formations correspondent actuellement a votre programme, vos competences, vos projets ou vos objectifs.',
    recommended: 'Recommande',
    loading: 'Chargement des formations et de votre profil...',
  },
}

const t = computed(() => translations[locale.value] || translations.en)

const search = ref('')
const typeFilter = ref('all')
const locationFilter = ref('all')
const selectedProgram = ref(null)
const messageText = ref('')
const applyModalOpen = ref(false)
const applyingProgram = ref(null)
const applicationMessage = ref('')
const studentProfile = ref(null)
const isStudentProfileLoading = ref(false)
const studentProfileError = ref('')

const programs = ref([])

const stopWords = new Set([
  'and',
  'the',
  'for',
  'with',
  'from',
  'your',
  'dans',
  'avec',
  'pour',
  'des',
  'les',
  'une',
  'sur',
  'projet',
  'project',
])

const resolveStoredApplications = () => {
  try {
    const raw = localStorage.getItem('studentTrainingApplications')
    if (!raw) return []
    return JSON.parse(raw)
  } catch {
    return []
  }
}

const myApplications = ref(resolveStoredApplications())

const persistApplications = () => {
  localStorage.setItem('studentTrainingApplications', JSON.stringify(myApplications.value))
}

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

const toWords = (value) =>
  String(value || '')
    .toLowerCase()
    .replace(/[^a-z0-9]+/gi, ' ')
    .split(' ')
    .map((item) => item.trim())
    .filter((item) => item.length > 2 && !stopWords.has(item))

const toTimestamp = (...values) => {
  for (const value of values) {
    if (!value) continue
    const date = new Date(value)
    if (!Number.isNaN(date.getTime())) return date.getTime()
  }
  return 0
}

const currentUserId = computed(() => parseId(authStore.user?.id || authStore.user?.userId))
const currentUserEmail = computed(() =>
  String(authStore.user?.email || '')
    .trim()
    .toLowerCase(),
)

const profileContext = computed(() => {
  const student = studentProfile.value
  const skills = Array.isArray(student?.skills)
    ? student.skills
        .map((item) => (typeof item === 'string' ? item : item?.name || ''))
        .filter(Boolean)
    : []
  const projects = Array.isArray(student?.projects) ? student.projects : []
  const program = student?.program || ''
  const department = student?.department || ''
  const faculty = student?.faculty || ''
  const level = student?.level || ''
  const summary = student?.summary || ''

  const projectTerms = projects.flatMap((item) =>
    toWords([item?.title, item?.description, item?.stack, item?.role].join(' ')),
  )
  const needTerms = toWords(summary)

  return {
    program,
    department,
    faculty,
    level,
    skills,
    projectTerms,
    needTerms,
    hasSignals: Boolean(program || skills.length || projectTerms.length || needTerms.length),
  }
})

const mapTrainingToProgram = (training) => ({
  id: Number(training.id),
  title: training.title || 'Untitled training',
  company:
    training.companyName ||
    training.company?.name ||
    training.companyId?.name ||
    training.companyId?.companyName ||
    'Company',
  type: training.type || 'Bootcamp',
  place: training.location || 'Remote',
  startDate: training.startAt || training.startDate || null,
  endDate: training.endAt || training.endDate || null,
  duration: training.durationLabel || training.duration || 'N/A',
  seats: Number(training.seatCount ?? training.seats ?? 0),
  description: training.description || '',
  requirements: Array.isArray(training.requirements)
    ? training.requirements
    : Array.isArray(training.trainingRequirements)
      ? training.trainingRequirements
          .map((item) => item?.label || item?.name || item?.title || '')
          .filter(Boolean)
      : [],
  contactName: 'Company contact',
  contactEmail: 'contact@company.com',
  messages: [],
  raw: training,
})

const buildMatchData = (program) => {
  const context = profileContext.value
  if (!context.hasSignals) {
    return { score: 0, reasons: [], isRecommended: false }
  }

  const haystack = [
    program.title,
    program.company,
    program.place,
    program.type,
    program.duration,
    program.description,
    ...(program.requirements || []),
  ]
    .join(' ')
    .toLowerCase()

  let score = 0
  const reasons = []

  if (context.program && haystack.includes(context.program.toLowerCase())) {
    score += 6
    reasons.push(`Program: ${context.program}`)
  }

  const profileArea = context.department || context.faculty
  if (profileArea && haystack.includes(profileArea.toLowerCase())) {
    score += 4
    reasons.push(`Field: ${profileArea}`)
  }

  const matchedSkills = context.skills
    .filter((skill) => haystack.includes(String(skill).toLowerCase()))
    .slice(0, 3)
  if (matchedSkills.length) {
    score += matchedSkills.length * 3
    reasons.push(`Skills: ${matchedSkills.join(', ')}`)
  }

  const matchedProjectTerms = Array.from(
    new Set(context.projectTerms.filter((term) => haystack.includes(term))),
  ).slice(0, 3)
  if (matchedProjectTerms.length) {
    score += Math.min(6, matchedProjectTerms.length * 2)
    reasons.push(`Projects: ${matchedProjectTerms.join(', ')}`)
  }

  const matchedNeedTerms = Array.from(
    new Set(context.needTerms.filter((term) => haystack.includes(term))),
  ).slice(0, 3)
  if (matchedNeedTerms.length) {
    score += Math.min(6, matchedNeedTerms.length * 2)
    reasons.push(`Needs: ${matchedNeedTerms.join(', ')}`)
  }

  if ((program.type || '').toLowerCase().includes('certification') && context.level) {
    score += 1
  }

  return {
    score,
    reasons,
    isRecommended: score >= 4,
  }
}

const programMatchMap = computed(() => {
  const result = new Map()
  for (const program of programs.value) {
    result.set(program.id, buildMatchData(program))
  }
  return result
})

const getProgramMatch = (program) =>
  programMatchMap.value.get(program?.id) || { score: 0, reasons: [], isRecommended: false }

const loadPrograms = async () => {
  const items = await fetchTrainings()
  programs.value = items.map(mapTrainingToProgram)
}

const loadStudentProfile = async () => {
  isStudentProfileLoading.value = true
  studentProfileError.value = ''

  try {
    await studentStore.fetchStudents()

    studentProfile.value =
      studentStore.students.find((student) => {
        const sameUser =
          currentUserId.value && Number(student.userId) === Number(currentUserId.value)
        const sameEmail =
          currentUserEmail.value &&
          String(student.email || student.user?.email || '')
            .trim()
            .toLowerCase() === currentUserEmail.value

        return sameUser || sameEmail
      }) || null
  } catch (error) {
    studentProfileError.value = error?.message || 'Unable to load your student profile.'
  } finally {
    isStudentProfileLoading.value = false
  }
}

onMounted(async () => {
  if (!authStore.bootstrapped && authStore.token) {
    await authStore.bootstrapAuth().catch(() => {})
  }

  await Promise.allSettled([loadPrograms(), loadStudentProfile()])
})

const rankedPrograms = computed(() =>
  [...programs.value].sort((left, right) => {
    const leftMatch = getProgramMatch(left)
    const rightMatch = getProgramMatch(right)

    if (leftMatch.isRecommended !== rightMatch.isRecommended) {
      return Number(rightMatch.isRecommended) - Number(leftMatch.isRecommended)
    }

    if (rightMatch.score !== leftMatch.score) {
      return rightMatch.score - leftMatch.score
    }

    const leftTs = toTimestamp(left.raw?.publishedAt, left.startDate, left.raw?.createdAt)
    const rightTs = toTimestamp(right.raw?.publishedAt, right.startDate, right.raw?.createdAt)
    return rightTs - leftTs
  }),
)

const baseFilteredPrograms = computed(() => {
  const term = search.value.trim().toLowerCase()
  return rankedPrograms.value.filter((program) => {
    const typeMatch = typeFilter.value === 'all' || program.type === typeFilter.value
    const locationMatch = locationFilter.value === 'all' || program.place === locationFilter.value

    const textMatch =
      !term ||
      program.title.toLowerCase().includes(term) ||
      program.company.toLowerCase().includes(term) ||
      program.place.toLowerCase().includes(term) ||
      program.description.toLowerCase().includes(term) ||
      (program.requirements || []).join(' ').toLowerCase().includes(term)

    return typeMatch && locationMatch && textMatch
  })
})

const filteredPrograms = computed(() => {
  const hasSignals = profileContext.value.hasSignals
  const recommendedOnly = baseFilteredPrograms.value.filter(
    (program) => getProgramMatch(program).isRecommended,
  )

  if (hasSignals && recommendedOnly.length) {
    return recommendedOnly
  }

  return baseFilteredPrograms.value
})

const locationOptions = computed(() => {
  const set = new Set(programs.value.map((program) => program.place))
  return Array.from(set)
})

const recommendedCount = computed(
  () => programs.value.filter((program) => getProgramMatch(program).isRecommended).length,
)

const isPageLoading = computed(() => isLoadingTrainings.value || isStudentProfileLoading.value)
const loadError = computed(() => studentProfileError.value || trainingError.value || '')

const messageThread = computed(() => selectedProgram.value?.messages || [])

const applicationForSelected = computed(() => {
  if (!selectedProgram.value) return null
  return myApplications.value.find((app) => app.programId === selectedProgram.value.id) || null
})

const selectProgram = (program) => {
  selectedProgram.value = program
}

watch(
  filteredPrograms,
  (items) => {
    if (!items.length) {
      selectedProgram.value = null
      return
    }

    if (
      !selectedProgram.value ||
      !items.some((program) => program.id === selectedProgram.value.id)
    ) {
      selectedProgram.value = items[0]
    }
  },
  { immediate: true },
)

const isRecommended = (program) => getProgramMatch(program).isRecommended
const matchReasons = (program) => getProgramMatch(program).reasons

const hasApplied = (programId) =>
  myApplications.value.some((application) => application.programId === programId)

const openApplyModal = (program) => {
  applyingProgram.value = program
  applicationMessage.value = applicationForSelected.value?.message || ''
  applyModalOpen.value = true
}

const closeApplyModal = () => {
  applyModalOpen.value = false
  applyingProgram.value = null
  applicationMessage.value = ''
}

const getStudentProfileId = () => {
  try {
    const storedUser = localStorage.getItem('user')
    if (!storedUser) return 1

    const parsed = JSON.parse(storedUser)
    return parsed?.studentProfileId || parsed?.studentProfile?.id || 1
  } catch {
    return 1
  }
}

const submitApplication = async () => {
  if (!applyingProgram.value || !applicationMessage.value.trim()) return

  const programId = Number(applyingProgram.value.id)
  const studentProfileId = getStudentProfileId()

  try {
    await api.createItem('trainingEnrollments', {
      trainingId: `/api/trainings/${programId}`,
      studentProfileId: `/api/student_profiles/${studentProfileId}`,
      status: 'Pending',
      enrolledAt: new Date().toISOString(),
      completedAt: null,
    })
  } catch {
    // The backend may not expose this endpoint yet; keep the front-end state working anyway.
  }

  const existing = myApplications.value.find((application) => application.programId === programId)
  if (existing) {
    existing.message = applicationMessage.value.trim()
    existing.appliedOn = new Date().toISOString().slice(0, 10)
    existing.status = 'Updated'
  } else {
    myApplications.value.unshift({
      id: Date.now(),
      programId,
      programTitle: applyingProgram.value.title,
      company: applyingProgram.value.company,
      place: applyingProgram.value.place,
      startDate: applyingProgram.value.startDate,
      endDate: applyingProgram.value.endDate,
      appliedOn: new Date().toISOString().slice(0, 10),
      status: 'Sent',
      message: applicationMessage.value.trim(),
    })
  }

  persistApplications()

  if (!applyingProgram.value.messages) {
    applyingProgram.value.messages = []
  }

  applyingProgram.value.messages.push({
    id: Date.now(),
    from: 'student',
    text: applicationMessage.value.trim(),
    time: 'Now',
  })

  closeApplyModal()
}

const sendMessage = () => {
  if (!selectedProgram.value || !messageText.value.trim()) return

  selectedProgram.value.messages.push({
    id: Date.now(),
    from: 'student',
    text: messageText.value.trim(),
    time: 'Now',
  })

  messageText.value = ''
}

const formatDate = (date) => {
  if (!date) return '--'
  const formatterLocale = locale.value === 'fr' ? 'fr-FR' : 'en-US'
  return new Date(date).toLocaleDateString(formatterLocale, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const statusClass = (status) => {
  if (status === 'Rejected') return 'rejected'
  if (status === 'Sent') return 'sent'
  return 'review'
}
</script>

<style scoped>
.training-page {
  display: grid;
  gap: 16px;
  padding: 14px;
  background: var(--bg);
  min-height: 100%;
}

.hero-card,
.toolbar-card,
.program-list-card,
.detail-card,
.applications-card,
.apply-modal,
.kpi-item {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 18px;
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
}

.hero-card {
  display: grid;
  grid-template-columns: 1.6fr 1fr;
  gap: 10px;
  padding: 14px;
  position: relative;
  overflow: hidden;
}

.hero-card::before {
  content: '';
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 88% 14%, rgba(6, 170, 197, 0.18), transparent 42%),
    linear-gradient(130deg, rgba(15, 118, 110, 0.08), rgba(14, 165, 233, 0.05));
  pointer-events: none;
}

.hero-card > * {
  position: relative;
  z-index: 1;
}

.eyebrow {
  margin: 0;
  color: var(--primary);
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

.hero-card h1,
.section-head h2,
.section-head h3,
.program-item strong,
.info-block strong,
.timeline-grid strong,
.message-item p {
  margin: 0;
  color: var(--text);
}

.hero-card h1 {
  font-size: clamp(1.25rem, 2.2vw, 1.75rem);
  line-height: 1.25;
}

.hero-text,
.program-item p,
.program-footer small,
.info-block span,
.detail-description,
.timeline-grid small,
.message-item small,
.section-head span,
.empty-state,
.kpi-item span,
.kpi-item small,
label span,
.contact-card .section-head span {
  color: var(--muted);
}

.hero-text {
  margin-top: 8px;
  max-width: 620px;
  line-height: 1.5;
}

.profile-fit-note {
  margin: 12px 0 0;
  color: var(--text);
}

.profile-fit-note strong {
  color: var(--heading);
}

.muted-note {
  color: var(--muted);
}

.hero-kpis {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 10px;
  align-content: start;
}

.kpi-item {
  padding: 10px 11px;
  background: rgba(255, 255, 255, 0.72);
  display: grid;
  gap: 2px;
}

.kpi-item strong {
  color: var(--text);
  font-size: 1.1rem;
}

.recommendation-banner {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 14px;
  border: 1px solid rgba(212, 160, 23, 0.28);
  background: rgba(212, 160, 23, 0.08);
}

.recommendation-banner strong {
  color: var(--heading);
  font-size: 1.1rem;
}

.recommendation-banner span {
  color: var(--text);
}

.info-state,
.error-state {
  padding: 12px 14px;
  border-radius: 14px;
  border: 1px solid var(--border);
  background: var(--surface);
}

.error-state {
  border-color: rgba(185, 28, 28, 0.2);
  color: #b91c1c;
}

.toolbar-card {
  padding: 14px;
  display: grid;
  gap: 10px;
}

.search-box {
  display: flex;
  align-items: center;
  gap: 10px;
  border-radius: 12px;
  border: 1px solid var(--border);
  background: var(--surface-soft);
  padding: 10px 12px;
}

.search-box input,
.toolbar-filters select,
.message-form input,
textarea {
  width: 100%;
  border: none;
  outline: none;
  background: transparent;
  color: var(--text);
}

.toolbar-filters {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
}

label {
  display: grid;
  gap: 6px;
}

.toolbar-filters select,
.message-form input,
textarea {
  border: 1px solid var(--border);
  border-radius: 12px;
  background: var(--surface);
  padding: 10px 12px;
}

.workspace-grid {
  display: grid;
  grid-template-columns: 360px minmax(0, 1fr);
  gap: 12px;
}

.program-list-card,
.detail-card,
.applications-card {
  padding: 14px;
  display: grid;
  gap: 12px;
  min-width: 0;
}

.section-head {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 10px;
}

.section-head.compact {
  align-items: center;
}

.program-list {
  display: grid;
  gap: 8px;
  max-height: 620px;
  overflow: auto;
}

.program-item {
  border: 1px solid transparent;
  border-radius: 14px;
  background: var(--surface-soft);
  text-align: left;
  cursor: pointer;
  padding: 10px;
  display: grid;
  gap: 8px;
}

.program-item.active {
  border-color: rgba(6, 170, 197, 0.4);
  background: rgba(6, 170, 197, 0.1);
}

.program-item.recommended {
  border-color: rgba(212, 160, 23, 0.35);
}

.match-pill-row {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
}

.match-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 4px 8px;
  border-radius: 999px;
  background: rgba(212, 160, 23, 0.12);
  color: #8a6700;
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.program-header {
  display: flex;
  justify-content: space-between;
  gap: 8px;
  align-items: center;
}

.program-meta {
  display: grid;
  gap: 4px;
}

.program-meta span {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: var(--muted);
  font-size: 0.84rem;
}

.program-footer {
  display: flex;
  justify-content: space-between;
  gap: 8px;
}

.type-pill,
.status-pill {
  border-radius: 999px;
  padding: 5px 10px;
  font-size: 0.75rem;
  font-weight: 700;
}

.type-pill {
  background: rgba(6, 170, 197, 0.12);
  color: var(--primary);
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 8px;
}

.info-block,
.timeline-grid > div {
  background: var(--surface-soft);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 10px;
  display: grid;
  gap: 4px;
}

.detail-description {
  margin: 0;
  line-height: 1.6;
}

.followup-card,
.contact-card {
  border: 1px solid var(--border);
  background: var(--surface-soft);
  border-radius: 14px;
  padding: 10px;
  display: grid;
  gap: 10px;
}

.separator {
  margin: 0 6px;
}

.timeline-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 8px;
}

.status-pill.review {
  background: rgba(37, 99, 235, 0.16);
  color: #1d4ed8;
}

.status-pill.sent {
  background: rgba(16, 185, 129, 0.16);
  color: #047857;
}

.status-pill.rejected {
  background: rgba(239, 68, 68, 0.16);
  color: #b91c1c;
}

.messages-list {
  display: grid;
  gap: 8px;
  max-height: 220px;
  overflow: auto;
}

.message-item {
  border-radius: 12px;
  padding: 8px 10px;
  max-width: 78%;
}

.message-item.company {
  background: rgba(37, 99, 235, 0.11);
}

.message-item.mine {
  background: rgba(6, 170, 197, 0.16);
  justify-self: end;
}

.message-item p {
  margin: 0 0 4px;
}

.message-form {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 8px;
}

.applications-table-wrap {
  overflow: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
  min-width: 760px;
}

thead th,
tbody td {
  border-bottom: 1px solid var(--border);
  padding: 10px;
  text-align: left;
}

thead th {
  color: var(--muted);
  font-size: 0.82rem;
  text-transform: uppercase;
}

.primary-btn,
.secondary-btn,
.ghost-btn,
.icon-btn {
  border: none;
  border-radius: 999px;
  padding: 9px 12px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
}

.primary-btn {
  background: var(--primary);
  color: #fff;
}

.secondary-btn {
  background: rgba(37, 99, 235, 0.13);
  color: #1d4ed8;
}

.ghost-btn {
  border: 1px solid var(--border);
  color: var(--text);
  background: var(--surface);
}

.icon-btn {
  width: 34px;
  height: 34px;
  padding: 0;
  background: transparent;
  color: var(--text);
  border-radius: 10px;
}

.empty-state {
  padding: 14px;
  text-align: center;
  border-radius: 12px;
  background: var(--surface-soft);
}

.empty-detail {
  min-height: 240px;
  align-content: center;
}

.overlay {
  position: fixed;
  inset: 0;
  background: rgba(4, 33, 44, 0.45);
  z-index: 40;
}

.apply-modal {
  position: fixed;
  z-index: 50;
  width: min(620px, calc(100vw - 20px));
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  padding: 14px;
  display: grid;
  gap: 10px;
}

textarea {
  resize: vertical;
  min-height: 110px;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

@media (max-width: 1180px) {
  .workspace-grid {
    grid-template-columns: 1fr;
  }

  .hero-card {
    grid-template-columns: 1fr;
    gap: 12px;
  }

  .hero-kpis {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .detail-grid,
  .timeline-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 760px) {
  .hero-kpis,
  .toolbar-filters,
  .detail-grid,
  .timeline-grid,
  .message-form {
    grid-template-columns: 1fr;
  }

  .hero-card {
    padding: 12px;
  }

  .hero-card h1 {
    font-size: 1.3rem;
  }

  .section-head {
    flex-direction: column;
    align-items: flex-start;
  }

  .section-head > .primary-btn {
    width: 100%;
  }

  .message-item {
    max-width: 100%;
  }

  .modal-actions {
    flex-direction: column;
  }

  .modal-actions button {
    width: 100%;
  }
}
</style>
