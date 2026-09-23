<template>
  <div class="opportunity-page">
    <section class="hero-card">
      <div>
        <p class="eyebrow">{{ t.eyebrow }}</p>
        <h1>{{ t.title }}</h1>
        <p class="hero-text">{{ t.subtitle }}</p>
        <p v-if="studentProfile" class="profile-fit-note">
          {{ t.personalizedFor }}
          <strong>{{ studentProfile.program || t.yourProfile }}</strong>
        </p>
        <p v-else class="profile-fit-note muted-note">{{ t.profileHint }}</p>
      </div>

      <div class="hero-stats">
        <article class="mini-stat">
          <span>{{ t.totalOpportunities }}</span>
          <strong>{{ normalizedOpportunities.length }}</strong>
        </article>
        <article class="mini-stat">
          <span>{{ t.recommendedItems }}</span>
          <strong>{{ recommendedCount }}</strong>
        </article>
        <article class="mini-stat">
          <span>{{ t.appliedItems }}</span>
          <strong>{{ appliedCount }}</strong>
        </article>
      </div>
    </section>

    <section class="toolbar-card">
      <div class="search-grid">
        <div class="search-input">
          <i class="bi bi-search"></i>
          <input v-model="searchTerm" type="text" :placeholder="t.searchPlaceholder" />
        </div>

        <div class="search-input">
          <i class="bi bi-geo-alt"></i>
          <input v-model="locationTerm" type="text" :placeholder="t.locationPlaceholder" />
        </div>
      </div>

      <div class="filters-row">
        <div class="category-chips">
          <button
            v-for="item in categoryOptions"
            :key="item.value"
            type="button"
            class="chip"
            :class="{ active: categoryFilter === item.value }"
            @click="categoryFilter = item.value"
          >
            {{ item.label }}
          </button>
        </div>

        <div class="right-filters">
          <select v-model="experienceFilter">
            <option value="all">{{ t.allExperience }}</option>
            <option value="Student">{{ t.student }}</option>
            <option value="Graduate">{{ t.graduate }}</option>
            <option value="Any">{{ t.any }}</option>
          </select>

          <button type="button" class="ghost-btn" @click="resetFilters">
            <i class="bi bi-arrow-counterclockwise"></i>
            {{ t.reset }}
          </button>
        </div>
      </div>

      <div class="recommendation-banner" v-if="studentProfile">
        <strong>{{ recommendedCount }}</strong>
        <span>{{ t.matchingProfile }}</span>
      </div>
    </section>

    <p v-if="isPageLoading" class="info-state">{{ t.loading }}</p>
    <p v-else-if="loadError" class="error-state">{{ loadError }}</p>

    <section class="content-grid">
      <article class="spotlight-card" v-if="featuredOpportunity">
        <div class="section-head">
          <h2>{{ t.featured }}</h2>
          <span v-if="featuredOpportunity.isRecommended">{{ t.recommended }}</span>
        </div>

        <div class="featured-inner">
          <div class="featured-badge">{{ featuredOpportunity.type }}</div>
          <h3>{{ featuredOpportunity.title }}</h3>
          <p>{{ featuredOpportunity.description }}</p>

          <div v-if="featuredOpportunity.matchReasons.length" class="match-summary">
            <strong>{{ t.whyThisFits }}</strong>
            <p>{{ featuredOpportunity.matchReasons.join(' · ') }}</p>
          </div>

          <div class="featured-meta">
            <span><i class="bi bi-building"></i> {{ featuredOpportunity.company }}</span>
            <span><i class="bi bi-geo-alt"></i> {{ featuredOpportunity.location }}</span>
            <span><i class="bi bi-clock"></i> {{ featuredOpportunity.duration }}</span>
            <span><i class="bi bi-calendar-event"></i> {{ featuredOpportunity.deadline }}</span>
          </div>

          <div class="featured-actions">
            <button class="primary-btn" type="button" @click="applyNow(featuredOpportunity)">
              {{ featuredOpportunity.applied ? t.applied : t.applyNow }}
            </button>
            <button class="ghost-btn" type="button" @click="toggleSave(featuredOpportunity)">
              {{ featuredOpportunity.saved ? t.saved : t.save }}
            </button>
          </div>
        </div>
      </article>

      <article class="list-card">
        <div class="section-head">
          <h2>{{ t.explore }}</h2>
          <span>{{ filteredOpportunities.length }} {{ t.items }}</span>
        </div>

        <div class="cards-grid" v-if="filteredOpportunities.length">
          <article class="opportunity-card" v-for="item in filteredOpportunities" :key="item.id">
            <div class="card-top">
              <span class="type-pill">{{ item.type }}</span>
              <small>{{ item.deadline }}</small>
            </div>

            <div v-if="item.matchReasons.length" class="match-pill-row">
              <span class="match-pill">{{ t.recommended }}</span>
              <small>{{ item.matchReasons[0] }}</small>
            </div>

            <h3>{{ item.title }}</h3>
            <p class="company-line"><i class="bi bi-building"></i> {{ item.company }}</p>
            <p class="meta-line"><i class="bi bi-geo-alt"></i> {{ item.location }}</p>
            <p class="meta-line"><i class="bi bi-clock-history"></i> {{ item.duration }}</p>

            <div class="skills-row">
              <span v-for="skill in item.skills" :key="skill">{{ skill }}</span>
            </div>

            <div class="card-actions">
              <button class="primary-btn" type="button" @click="applyNow(item)">
                {{ item.applied ? t.applied : t.apply }}
              </button>
              <button class="ghost-btn" type="button" @click="toggleSave(item)">
                {{ item.saved ? t.saved : t.save }}
              </button>
            </div>
          </article>
        </div>

        <div class="empty-state" v-else>{{ t.noResult }}</div>
      </article>
    </section>

    <div v-if="isApplyModalOpen" class="apply-modal-backdrop" @click="closeApplyModal"></div>
    <section v-if="isApplyModalOpen" class="apply-modal" role="dialog" aria-modal="true">
      <header class="apply-modal-header">
        <div>
          <h3>{{ t.applyFormTitle }}</h3>
          <p>{{ selectedOpportunityForApply?.title || t.opportunity }}</p>
        </div>
        <button class="icon-close" type="button" @click="closeApplyModal">x</button>
      </header>

      <form class="apply-form" @submit.prevent="submitApplicationForm">
        <div class="field-grid">
          <label>
            <span>{{ t.fullName }}</span>
            <input v-model.trim="applicationForm.fullName" type="text" required />
          </label>

          <label>
            <span>{{ t.email }}</span>
            <input v-model.trim="applicationForm.email" type="email" required />
          </label>

          <label>
            <span>{{ t.phone }}</span>
            <input v-model.trim="applicationForm.phone" type="text" required />
          </label>

          <label>
            <span>{{ t.program }}</span>
            <input v-model.trim="applicationForm.program" type="text" required />
          </label>
        </div>

        <label>
          <span>{{ t.motivationLetter }}</span>
          <textarea
            v-model.trim="applicationForm.motivationLetter"
            rows="6"
            :placeholder="t.motivationPlaceholder"
            required
          ></textarea>
        </label>

        <div class="apply-modal-actions">
          <button class="ghost-btn" type="button" @click="closeApplyModal">{{ t.cancel }}</button>
          <button class="primary-btn" type="submit" :disabled="isSubmittingApplication">
            {{ isSubmittingApplication ? t.submitting : t.submitApplication }}
          </button>
        </div>
      </form>
    </section>
  </div>
</template>

<script setup>
import { computed, inject, onMounted, reactive, ref } from 'vue'
import { useAuthStore } from '@/stores/auth.store'
import { useStudentStore } from '@/stores/student.store'
import { useCompanyOpportunities } from '@/compasables/useCompanyOpportunities'
import { useCompanyAdmin } from '@/compasables/useCompanyAdmin'
import { useCandidature } from '@/compasables/useCandidature'
import { useToast } from '@/compasables/useToast'

const locale = inject('locale', ref('en'))
const authStore = useAuthStore()
const studentStore = useStudentStore()
const { opportunities, isLoadingOpportunities, opportunityError, fetchOpportunities } =
  useCompanyOpportunities()
const { fetchCompanies, getCompanyById } = useCompanyAdmin()
const {
  candidatures,
  // savedOpportunities,
  saveOpportunity,
  unsaveOpportunity,
  applyCandidature,
  fetchCandidatures,
  fetchSavedOpportunities,
  isSaved,
  isApplied,
} = useCandidature()
const { showToast } = useToast()

const translations = {
  en: {
    eyebrow: 'Student / Opportunities',
    title: 'Discover Your Next Opportunity',
    subtitle:
      'Browse the latest internships, graduate tracks, scholarships, and research openings from company partners.',
    totalOpportunities: 'Opportunities',
    recommendedItems: 'Recommended',
    appliedItems: 'Applied',
    searchPlaceholder: 'Search by role, skill, or company',
    locationPlaceholder: 'Filter by location',
    allExperience: 'All experience levels',
    student: 'Student',
    graduate: 'Graduate',
    any: 'Any',
    reset: 'Reset',
    featured: 'Featured Opportunity',
    applyNow: 'Apply Now',
    apply: 'Apply',
    applied: 'Applied',
    save: 'Save',
    saved: 'Saved',
    explore: 'Explore Opportunities',
    items: 'items',
    noResult: 'No opportunities match your filters.',
    allCategories: 'All',
    internship: 'Internship',
    fulltime: 'Full Time',
    scholarship: 'Scholarship',
    research: 'Research',
    personalizedFor: 'Recommendations based on',
    yourProfile: 'your profile',
    profileHint: 'Complete your student profile to improve opportunity recommendations.',
    matchingProfile: 'opportunities currently match your program, skills, or projects.',
    loading: 'Loading opportunities and your profile...',
    recommended: 'Recommended',
    whyThisFits: 'Why this fits',
    applyFormTitle: 'Application Form',
    opportunity: 'Opportunity',
    fullName: 'Full name',
    email: 'Email',
    phone: 'Phone',
    program: 'Program',
    motivationLetter: 'Motivation letter',
    motivationPlaceholder: 'Explain why you are a good fit for this opportunity.',
    cancel: 'Cancel',
    submitApplication: 'Submit application',
    submitting: 'Submitting...',
  },
  fr: {
    eyebrow: 'Etudiant / Opportunites',
    title: 'Trouvez Votre Prochaine Opportunite',
    subtitle:
      'Consultez les dernieres offres de stage, programmes graduate, bourses et postes de recherche des entreprises partenaires.',
    totalOpportunities: 'Opportunites',
    recommendedItems: 'Recommandees',
    appliedItems: 'Postulees',
    searchPlaceholder: 'Rechercher par poste, competence ou entreprise',
    locationPlaceholder: 'Filtrer par lieu',
    allExperience: 'Tous les niveaux',
    student: 'Etudiant',
    graduate: 'Diplome',
    any: 'Tous',
    reset: 'Reinitialiser',
    featured: 'Opportunite a la une',
    applyNow: 'Postuler maintenant',
    apply: 'Postuler',
    applied: 'Postule',
    save: 'Sauvegarder',
    saved: 'Sauvegarde',
    explore: 'Explorer les opportunites',
    items: 'elements',
    noResult: 'Aucune opportunite ne correspond aux filtres.',
    allCategories: 'Tout',
    internship: 'Stage',
    fulltime: 'Temps plein',
    scholarship: 'Bourse',
    research: 'Recherche',
    personalizedFor: 'Recommandations basees sur',
    yourProfile: 'votre profil',
    profileHint: 'Completez votre profil etudiant pour ameliorer les recommandations.',
    matchingProfile:
      'opportunites correspondent actuellement a votre programme, vos competences ou vos projets.',
    loading: 'Chargement des opportunites et de votre profil...',
    recommended: 'Recommande',
    whyThisFits: 'Pourquoi cette offre',
    applyFormTitle: 'Formulaire de candidature',
    opportunity: 'Opportunite',
    fullName: 'Nom complet',
    email: 'Email',
    phone: 'Telephone',
    program: 'Programme',
    motivationLetter: 'Lettre de motivation',
    motivationPlaceholder: 'Expliquez pourquoi vous etes un bon profil pour cette opportunite.',
    cancel: 'Annuler',
    submitApplication: 'Envoyer la candidature',
    submitting: 'Envoi en cours...',
  },
}

const t = computed(() => translations[locale.value] || translations.en)

const searchTerm = ref('')
const locationTerm = ref('')
const categoryFilter = ref('all')
const experienceFilter = ref('all')
const studentProfile = ref(null)
const isStudentProfileLoading = ref(false)
const studentProfileError = ref('')
const isApplyModalOpen = ref(false)
const selectedOpportunityForApply = ref(null)
const isSubmittingApplication = ref(false)
const applicationForm = reactive({
  fullName: '',
  email: '',
  phone: '',
  program: '',
  motivationLetter: '',
})

const APPLICATION_FORM_PREFIX = 'MERYX_APPLICATION_FORM::'

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

const toTimestamp = (...values) => {
  for (const value of values) {
    if (!value) continue
    const date = new Date(value)
    if (!Number.isNaN(date.getTime())) return date.getTime()
  }
  return 0
}

const formatDateLabel = (value) => {
  if (!value) return '—'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return String(value)
  return date.toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })
}

const toWords = (value) =>
  String(value || '')
    .toLowerCase()
    .replace(/[^a-z0-9]+/gi, ' ')
    .split(' ')
    .map((item) => item.trim())
    .filter((item) => item.length > 2 && !stopWords.has(item))

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
  const projectTerms = projects.flatMap((item) =>
    toWords([item?.title, item?.description, item?.stack, item?.role].join(' ')),
  )
  const keywords = Array.from(
    new Set([
      ...toWords(program),
      ...toWords(department),
      ...toWords(faculty),
      ...toWords(level),
      ...skills.flatMap((item) => toWords(item)),
      ...projectTerms,
    ]),
  )

  return {
    program,
    department,
    faculty,
    level,
    skills,
    projectTerms,
    keywords,
    hasSignals: Boolean(program || skills.length || projectTerms.length),
  }
})

const buildMatchData = (item) => {
  const context = profileContext.value
  if (!context.hasSignals) {
    return { score: 0, reasons: [], isRecommended: false }
  }

  const haystack = [
    item.title,
    item.company,
    item.location,
    item.type,
    item.department,
    item.description,
    ...(item.skills || []),
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

  return {
    score,
    reasons,
    isRecommended: score > 0,
  }
}

const normalizedOpportunities = computed(() =>
  opportunities.value.map((item) => {
    const companyId = parseId(item.companyId || item.raw?.companyId)
    const company =
      getCompanyById(companyId)?.name ||
      item.raw?.companyId?.name ||
      item.raw?.company?.name ||
      item.raw?.companyName ||
      item.company ||
      'Company'
    const skills = Array.isArray(item.requirements) ? item.requirements.filter(Boolean) : []
    const deadlineSource = item.deadLine || item.raw?.applicationDeadLine || item.raw?.deadline
    const postedTimestamp = toTimestamp(
      item.raw?.publishedAt,
      item.raw?.createdAt,
      deadlineSource,
      item.postedDate,
    )
    const match = buildMatchData({
      title: item.title,
      company,
      location: item.location,
      type: item.type,
      department: item.department,
      description: item.description,
      skills,
    })

    return {
      id: item.id,
      company,
      title: item.title || 'Untitled opportunity',
      type: item.type || 'Opportunity',
      location: item.location || 'Remote',
      deadline: formatDateLabel(deadlineSource),
      duration:
        item.raw?.duration ||
        item.raw?.contractDuration ||
        item.remoteType ||
        item.raw?.remoteType ||
        'Open role',
      experience: item.raw?.experienceLevel || item.raw?.experience || 'Any',
      description: item.description || 'No description provided yet.',
      skills,
      postedTimestamp,
      matchReasons: match.reasons,
      relevanceScore: match.score,
      isRecommended: match.isRecommended,
      saved: isSaved(item.id, studentProfile.value?.id),
      applied: isApplied(item.id, studentProfile.value?.id),
    }
  }),
)

const rankedOpportunities = computed(() =>
  [...normalizedOpportunities.value].sort((left, right) => {
    if (right.postedTimestamp !== left.postedTimestamp) {
      return right.postedTimestamp - left.postedTimestamp
    }

    if (left.isRecommended !== right.isRecommended) {
      return Number(right.isRecommended) - Number(left.isRecommended)
    }

    if (right.relevanceScore !== left.relevanceScore) {
      return right.relevanceScore - left.relevanceScore
    }

    return Number(right.id) - Number(left.id)
  }),
)

const categoryOptions = computed(() => [
  { value: 'all', label: t.value.allCategories },
  { value: 'Internship', label: t.value.internship },
  { value: 'Full Time', label: t.value.fulltime },
  { value: 'Scholarship', label: t.value.scholarship },
  { value: 'Research', label: t.value.research },
])

const filteredOpportunities = computed(() => {
  const query = searchTerm.value.trim().toLowerCase()
  const location = locationTerm.value.trim().toLowerCase()

  return rankedOpportunities.value.filter((item) => {
    const categoryMatch = categoryFilter.value === 'all' || item.type === categoryFilter.value
    const experienceMatch =
      experienceFilter.value === 'all' ||
      item.experience === experienceFilter.value ||
      item.experience === 'Any' ||
      item.experience === 'Not specified'

    const queryMatch =
      !query ||
      item.title.toLowerCase().includes(query) ||
      item.company.toLowerCase().includes(query) ||
      item.skills.join(' ').toLowerCase().includes(query) ||
      item.description.toLowerCase().includes(query)

    const locationMatch = !location || item.location.toLowerCase().includes(location)

    return categoryMatch && experienceMatch && queryMatch && locationMatch
  })
})

const featuredOpportunity = computed(
  () =>
    filteredOpportunities.value.find((item) => item.isRecommended) ||
    filteredOpportunities.value[0] ||
    null,
)
const recommendedCount = computed(
  () => normalizedOpportunities.value.filter((item) => item.isRecommended).length,
)
const appliedCount = computed(() => candidatures.value.length)
const isPageLoading = computed(() => isLoadingOpportunities.value || isStudentProfileLoading.value)
const loadError = computed(() => studentProfileError.value || opportunityError.value || '')

const toggleSave = async (item) => {
  const studentId = studentProfile.value?.id
  if (!studentId) {
    showToast('Please complete your profile first', 'warning')
    return
  }

  try {
    if (isSaved(item.id, studentId)) {
      await unsaveOpportunity(item.id, studentId)
      showToast('Opportunity removed from saved', 'success')
    } else {
      await saveOpportunity(item.id, studentId)
      showToast('Opportunity saved successfully', 'success')
    }
  } catch (error) {
    showToast(error?.message || 'Unable to save opportunity', 'error')
  }
}

const prefillApplicationForm = () => {
  const student = studentProfile.value || {}
  applicationForm.fullName = student.fullName || authStore.user?.fullName || ''
  applicationForm.email = student.email || authStore.user?.email || ''
  applicationForm.phone = student.phone || authStore.user?.phone || ''
  applicationForm.program = student.program || ''
  applicationForm.motivationLetter = ''
}

const closeApplyModal = () => {
  isApplyModalOpen.value = false
  selectedOpportunityForApply.value = null
}

const openApplyModal = (item) => {
  const studentId = studentProfile.value?.id
  if (!studentId) {
    showToast('Please complete your profile first', 'warning')
    return
  }

  if (isApplied(item.id, studentId)) {
    showToast('You have already applied to this opportunity', 'info')
    return
  }

  selectedOpportunityForApply.value = item
  prefillApplicationForm()
  isApplyModalOpen.value = true
}

const applyNow = (item) => {
  openApplyModal(item)
}

const submitApplicationForm = async () => {
  const studentId = studentProfile.value?.id
  const opportunityId = selectedOpportunityForApply.value?.id

  if (!studentId || !opportunityId) {
    showToast('Missing student or opportunity details', 'error')
    return
  }

  if (!applicationForm.motivationLetter.trim()) {
    showToast('Motivation letter is required', 'warning')
    return
  }

  const payload = {
    type: 'student_application_form',
    fullName: applicationForm.fullName,
    email: applicationForm.email,
    phone: applicationForm.phone,
    program: applicationForm.program,
    motivationLetter: applicationForm.motivationLetter,
    submittedAt: new Date().toISOString(),
  }

  isSubmittingApplication.value = true
  try {
    await applyCandidature(
      opportunityId,
      studentId,
      `${APPLICATION_FORM_PREFIX}${JSON.stringify(payload)}`,
    )
    closeApplyModal()
    showToast('Application submitted successfully', 'success')
  } catch (error) {
    showToast(error?.message || 'Unable to submit application', 'error')
  } finally {
    isSubmittingApplication.value = false
  }
}

const resetFilters = () => {
  searchTerm.value = ''
  locationTerm.value = ''
  categoryFilter.value = 'all'
  experienceFilter.value = 'all'
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

  await Promise.allSettled([
    fetchCompanies().catch(() => {}),
    fetchOpportunities(),
    loadStudentProfile(),
  ])

  // After loading the student profile, fetch their candidatures and saved opportunities
  if (studentProfile.value?.id) {
    await Promise.allSettled([
      fetchCandidatures(studentProfile.value.id),
      fetchSavedOpportunities(studentProfile.value.id),
    ])
  }
})
</script>

<style scoped>
.opportunity-page {
  display: grid;
  gap: 14px;
  padding: 14px;
  background: var(--bg);
  min-height: 100%;
}

.hero-card,
.toolbar-card,
.spotlight-card,
.list-card,
.mini-stat,
.opportunity-card {
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
    radial-gradient(circle at 88% 12%, rgba(6, 170, 197, 0.16), transparent 44%),
    linear-gradient(130deg, rgba(15, 118, 110, 0.1), rgba(14, 165, 233, 0.05));
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
.featured-inner h3,
.opportunity-card h3 {
  margin: 0;
  color: var(--text);
}

.hero-card h1 {
  margin-top: 6px;
  font-size: clamp(1.26rem, 2.2vw, 1.75rem);
}

.hero-text,
.mini-stat span,
.section-head span,
.featured-inner p,
.company-line,
.meta-line,
.empty-state,
small,
label span {
  color: var(--muted);
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

.hero-stats {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 8px;
  align-content: start;
}

.mini-stat {
  padding: 10px;
  background: rgba(255, 255, 255, 0.72);
  display: grid;
  gap: 2px;
}

.mini-stat strong {
  color: var(--text);
  font-size: 1.18rem;
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
  padding: 12px;
  display: grid;
  gap: 10px;
}

.search-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}

.search-input {
  display: flex;
  align-items: center;
  gap: 8px;
  border: 1px solid var(--border);
  border-radius: 12px;
  background: var(--surface-soft);
  padding: 10px 12px;
}

.search-input input,
select {
  width: 100%;
  border: none;
  background: transparent;
  outline: none;
  color: var(--text);
}

.filters-row {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 8px;
  align-items: center;
}

.category-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.chip,
.primary-btn,
.ghost-btn {
  border: none;
  border-radius: 999px;
  padding: 8px 12px;
  cursor: pointer;
}

.chip {
  background: var(--surface-soft);
  color: var(--text);
  border: 1px solid var(--border);
}

.chip.active {
  background: var(--primary);
  color: #fff;
  border-color: var(--primary);
}

.right-filters {
  display: flex;
  align-items: center;
  gap: 8px;
}

.right-filters select {
  border: 1px solid var(--border);
  border-radius: 999px;
  background: var(--surface);
  padding: 8px 10px;
}

.content-grid {
  display: grid;
  grid-template-columns: 360px minmax(0, 1fr);
  gap: 10px;
}

.apply-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(2, 6, 23, 0.48);
  z-index: 45;
}

.apply-modal {
  position: fixed;
  z-index: 46;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: min(720px, calc(100vw - 20px));
  max-height: calc(100vh - 28px);
  overflow: auto;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 16px;
  box-shadow: 0 22px 60px rgba(2, 6, 23, 0.3);
  padding: 16px;
  display: grid;
  gap: 14px;
}

.apply-modal-header {
  display: flex;
  align-items: start;
  justify-content: space-between;
  gap: 10px;
}

.apply-modal-header h3 {
  margin: 0;
  color: var(--text);
}

.apply-modal-header p {
  margin: 6px 0 0;
  color: var(--muted);
}

.icon-close {
  border: 1px solid var(--border);
  background: var(--surface-soft);
  color: var(--text);
  width: 30px;
  height: 30px;
  border-radius: 8px;
  cursor: pointer;
}

.apply-form {
  display: grid;
  gap: 12px;
}

.field-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
}

.apply-form label {
  display: grid;
  gap: 6px;
}

.apply-form label span {
  color: var(--muted);
  font-size: 0.85rem;
}

.apply-form input,
.apply-form textarea {
  width: 100%;
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 10px 12px;
  background: var(--surface-soft);
  color: var(--text);
  outline: none;
}

.apply-form textarea {
  resize: vertical;
  min-height: 130px;
}

.apply-modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

.spotlight-card,
.list-card {
  padding: 12px;
  display: grid;
  gap: 10px;
  min-width: 0;
}

.section-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
}

.featured-inner {
  border: 1px solid var(--border);
  background: var(--surface-soft);
  border-radius: 14px;
  padding: 12px;
  display: grid;
  gap: 10px;
}

.featured-badge,
.type-pill {
  justify-self: start;
  border-radius: 999px;
  padding: 5px 10px;
  font-size: 0.75rem;
  font-weight: 700;
  background: rgba(6, 170, 197, 0.14);
  color: var(--primary);
}

.featured-meta {
  display: grid;
  gap: 5px;
}

.featured-meta span,
.company-line,
.meta-line {
  display: inline-flex;
  gap: 6px;
  align-items: center;
}

.match-summary {
  display: grid;
  gap: 4px;
  padding: 10px 12px;
  border-radius: 12px;
  background: rgba(212, 160, 23, 0.08);
  border: 1px solid rgba(212, 160, 23, 0.22);
}

.match-summary strong {
  color: var(--heading);
}

.match-summary p {
  margin: 0;
  color: var(--text);
}

.featured-actions,
.card-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.primary-btn {
  background: var(--primary);
  color: #fff;
}

.ghost-btn {
  border: 1px solid var(--border);
  color: var(--text);
  background: var(--surface);
}

.cards-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 10px;
}

.opportunity-card {
  padding: 11px;
  display: grid;
  gap: 8px;
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

.card-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
}

.opportunity-card h3 {
  font-size: 1rem;
}

.opportunity-card p {
  margin: 0;
}

.skills-row {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}

.skills-row span {
  background: rgba(15, 118, 110, 0.08);
  color: #115e59;
  border-radius: 999px;
  padding: 5px 9px;
  font-size: 0.75rem;
}

.empty-state {
  border-radius: 12px;
  background: var(--surface-soft);
  padding: 16px;
  text-align: center;
}

@media (max-width: 1380px) {
  .cards-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 1160px) {
  .hero-card,
  .content-grid {
    grid-template-columns: 1fr;
  }

  .hero-stats {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 760px) {
  .hero-stats,
  .search-grid,
  .cards-grid,
  .filters-row,
  .right-filters {
    grid-template-columns: 1fr;
  }

  .right-filters {
    display: grid;
  }

  .field-grid {
    grid-template-columns: 1fr;
  }

  .featured-actions button,
  .card-actions button {
    width: 100%;
  }
}
</style>
