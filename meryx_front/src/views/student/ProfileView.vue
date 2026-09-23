<template>
  <div class="profile-page">
    <!-- ================= HEADER COVER ================= -->

    <section class="profile-cover">
      <div class="cover-overlay"></div>
    </section>

    <!-- ================= PROFILE HEADER ================= -->

    <section class="profile-card">
      <div class="avatar">
        <img :src="avatarUrl" class="avatar" />
      </div>

      <div class="student-info">
        <h1>{{ profileName }}</h1>

        <h3>{{ headline }}</h3>

        <p>
          <i class="bi bi-building"></i>

          {{ universityName }}
        </p>

        <p>
          <i class="bi bi-geo-alt"></i>

          {{ locationText }}
        </p>
      </div>
    </section>

    <!-- ================= TAB NAVIGATION ================= -->

    <div class="tab-container">
      <div class="tabs">
        <button
          v-for="tab in tabs"
          :key="tab"
          :class="['tab-btn', { active: activeTab === tab }]"
          @click="activeTab = tab"
        >
          {{ tab }}
        </button>
      </div>

      <div class="profile-actions">
        <button class="edit" @click="goToSettings">
          <i class="bi bi-pencil"></i> Edit Profile
        </button>

        <button class="download" @click="downloadCv">
          <i class="bi bi-download"></i> Download CV
        </button>
      </div>
    </div>

    <!-- ================= CONTENT AREA ================= -->

    <div class="profile-container">
      <!-- PERSONAL TAB -->

      <div v-if="activeTab === 'Personal'" class="content-section">
        <div class="personal-grid">
          <div class="personal-main">
            <section class="box">
              <h2>Personal Information</h2>

              <div class="item">
                <strong>Full Name</strong>
                <span>{{ profileName }}</span>
              </div>

              <div class="item">
                <strong>Email</strong>
                <span>{{ emailText }}</span>
              </div>

              <div class="item">
                <strong>Phone</strong>
                <span>{{ phoneText }}</span>
              </div>

              <div class="item">
                <strong>Gender</strong>
                <span>{{ genderText }}</span>
              </div>

              <div class="item">
                <strong>Nationality</strong>
                <span>{{ nationalityText }}</span>
              </div>

              <div class="item">
                <strong>Address</strong>
                <span>{{ locationText }}</span>
              </div>

              <div class="item">
                <strong>Student ID</strong>
                <span>{{ studentIdText }}</span>
              </div>
            </section>

            <section class="box">
              <h2>About Me</h2>

              <p>{{ aboutMe }}</p>
            </section>

            <section class="box match">
              <h2>MeryX Opportunity Match</h2>

              <div class="circle">{{ profileCompletion }}%</div>

              <p>
                Your profile matches with <strong>{{ opportunityCount }} opportunities</strong>
              </p>
            </section>
          </div>

          <aside class="box tracker-box">
            <div class="tracker-head">
              <h2>Application Tracker</h2>
              <span>{{ trackedApplications.length }} applications</span>
            </div>

            <p v-if="isLoadingCandidatures && !trackedApplications.length" class="tracker-info">
              Loading your applications...
            </p>
            <p v-else-if="candidatureError" class="tracker-error">{{ candidatureError }}</p>
            <p v-else-if="!trackedApplications.length" class="tracker-empty">
              You have not posted to an opportunity yet.
            </p>

            <div v-else class="tracker-list">
              <article
                v-for="application in trackedApplications"
                :key="application.id"
                class="tracker-item"
              >
                <div class="tracker-item-top">
                  <strong>{{ application.opportunity?.title || 'Opportunity' }}</strong>
                  <span class="tracker-status" :class="statusClass(application.status)">
                    {{ formatStatus(application.status) }}
                  </span>
                </div>

                <p class="tracker-company">
                  <i class="bi bi-building"></i>
                  {{ application.opportunity?.company || 'Company not specified' }}
                </p>

                <div class="tracker-dates">
                  <span>Applied: {{ formatDate(application.appliedDate) }}</span>
                  <span>
                    Interview:
                    {{
                      application.interviewDate
                        ? formatDate(application.interviewDate)
                        : 'Not scheduled yet'
                    }}
                  </span>
                </div>
              </article>
            </div>
          </aside>
        </div>
      </div>

      <!-- ACADEMIC TAB -->

      <div v-if="activeTab === 'Academic'" class="content-section">
        <section class="box academic-box">
          <div class="section-head">
            <h2>Academic Information</h2>

            <button class="edit academic-edit-btn" @click="openAcademicEditor">
              <i class="bi bi-pencil"></i> Edit Academic Record
            </button>
          </div>

          <div class="item">
            <strong>University</strong>

            <span>{{ universityName }}</span>
          </div>

          <div class="item">
            <strong>Degree</strong>

            <span>{{ degreeText }}</span>
          </div>

          <div class="item">
            <strong>Level</strong>

            <span>{{ levelText }}</span>
          </div>

          <div class="item">
            <strong>GPA</strong>

            <span class="score">{{ gpaText }}</span>
          </div>

          <div class="item">
            <strong>Faculty</strong>

            <span>{{ facultyText }}</span>
          </div>

          <div class="item">
            <strong>Department</strong>

            <span>{{ departmentText }}</span>
          </div>

          <div class="item">
            <strong>Academic Year</strong>

            <span>{{ academicYearText }}</span>
          </div>

          <div class="item">
            <strong>Student ID</strong>

            <span>{{ studentIdText }}</span>
          </div>

          <div class="item">
            <strong>Status</strong>

            <span>{{ statusText }}</span>
          </div>

          <div class="item">
            <strong>Admission Date</strong>

            <span>{{ admissionDateText }}</span>
          </div>

          <div v-if="academicRecords.length" class="academic-records">
            <h3>Academic Records</h3>

            <article
              v-for="record in academicRecords"
              :key="record.id || record.name || record.title"
              class="record-item"
            >
              <strong>{{ record.title || record.name || 'Academic record' }}</strong>

              <p>
                {{ record.description || record.details || record.value || 'No details provided.' }}
              </p>
            </article>
          </div>
        </section>
      </div>

      <!-- SKILLS TAB -->

      <div v-if="activeTab === 'Skills'" class="content-section">
        <div class="skills-wrapper">
          <section class="box skill-radar-box">
            <h2>Skill Radar</h2>

            <canvas ref="radarChart" width="300" height="300"></canvas>
          </section>

          <section class="box">
            <h2>Top Skills</h2>

            <div class="skills">
              <span v-for="skill in skills" :key="skill">{{ skill }}</span>
            </div>

            <button class="add-skills-btn" @click="goToSettings">+ Add</button>
          </section>
        </div>
      </div>

      <!-- LANGUAGES TAB -->

      <div v-if="activeTab === 'Languages'" class="content-section">
        <section class="box">
          <h2>Languages</h2>

          <div class="language-item" v-for="lang in languages" :key="lang.name">
            <div class="lang-info">
              <strong>{{ lang.name }}</strong>

              <span class="lang-level">{{ lang.level }}</span>
            </div>
          </div>
        </section>
      </div>

      <!-- PROJECTS TAB -->

      <div v-if="activeTab === 'Projects'" class="content-section">
        <section class="box">
          <h2>Projects</h2>

          <div class="project" v-for="project in projects" :key="project.title">
            <h3>{{ project.title }}</h3>

            <p>{{ project.description }}</p>
          </div>
        </section>
      </div>

      <!-- ASSIGNMENTS TAB -->

      <div v-if="activeTab === 'Assignments'" class="content-section">
        <section class="box">
          <h2>Assignments</h2>

          <p>{{ assignmentsMessage }}</p>
        </section>
      </div>

      <!-- CERTIFICATES TAB -->

      <div v-if="activeTab === 'Certificates'" class="content-section">
        <section class="box">
          <h2>Certificates</h2>

          <ul>
            <li v-for="cert in certificates" :key="cert">{{ cert }}</li>
          </ul>
        </section>
      </div>

      <!-- EXPERIENCE TAB -->

      <div v-if="activeTab === 'Experience'" class="content-section">
        <section class="box">
          <h2>Experience</h2>

          <div class="timeline" v-for="exp in experiences" :key="exp.title">
            <h3>{{ exp.title }}</h3>

            <p>{{ exp.company }}</p>

            <span>{{ exp.date }}</span>
          </div>
        </section>
      </div>

      <!-- ACHIEVEMENTS TAB -->

      <div v-if="activeTab === 'Achievements'" class="content-section">
        <section class="box">
          <h2>Achievements</h2>

          <p>{{ achievementsMessage }}</p>
        </section>
      </div>

      <!-- DOCUMENTS TAB -->

      <div v-if="activeTab === 'Documents'" class="content-section">
        <section class="box">
          <h2>Documents</h2>

          <div class="document" v-for="doc in documents" :key="doc.name + doc.type">
            <i class="bi bi-file-earmark-pdf"></i>

            <span>{{ doc.name }}</span>

            <button @click="openDocument(doc)">View</button>
          </div>
        </section>
      </div>
    </div>

    <div v-if="isAcademicEditorOpen" class="modal-overlay" @click.self="closeAcademicEditor">
      <div class="modal-card">
        <div class="modal-header">
          <div>
            <p class="modal-eyebrow">Student / Academic Record</p>
            <h2>Edit Academic Record</h2>
          </div>

          <button class="modal-close" type="button" @click="closeAcademicEditor">&times;</button>
        </div>

        <form class="academic-form" @submit.prevent="saveAcademicRecord">
          <div class="academic-form-grid">
            <label>
              <span>University</span>
              <input v-model.trim="academicForm.universityName" type="text" readonly />
            </label>

            <label>
              <span>Program</span>
              <input v-model.trim="academicForm.program" type="text" />
            </label>

            <label>
              <span>Level / Class</span>
              <input v-model.trim="academicForm.level" type="text" />
            </label>

            <label>
              <span>Academic year</span>
              <input v-model.trim="academicForm.academicYear" type="text" />
            </label>

            <label>
              <span>Faculty</span>
              <input v-model.trim="academicForm.faculty" type="text" />
            </label>

            <label>
              <span>Department</span>
              <input v-model.trim="academicForm.department" type="text" />
            </label>

            <label>
              <span>Student ID</span>
              <input v-model.trim="academicForm.studentId" type="text" />
            </label>

            <label>
              <span>GPA</span>
              <input v-model.trim="academicForm.gpa" type="text" />
            </label>
          </div>

          <div class="modal-actions">
            <button type="button" class="download" @click="closeAcademicEditor">Cancel</button>
            <button type="submit" class="edit" :disabled="savingAcademicRecord">
              {{ savingAcademicRecord ? 'Saving...' : 'Save Academic Record' }}
            </button>
          </div>
        </form>

        <p v-if="academicEditMessage" class="status-message">{{ academicEditMessage }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import Chart from 'chart.js/auto'
import { useAuthStore } from '@/stores/auth.store'
import { useStudentStore } from '@/stores/student.store'
import { useCandidature } from '@/compasables/useCandidature'

const router = useRouter()
const authStore = useAuthStore()
const studentStore = useStudentStore()
const { candidatures, error: candidatureError, fetchCandidatures } = useCandidature()

const activeTab = ref('Personal')
const isLoading = ref(false)
const isLoadingCandidatures = ref(false)
const selectedStudent = ref(null)
const isAcademicEditorOpen = ref(false)
const savingAcademicRecord = ref(false)
const academicEditMessage = ref('')

const academicForm = reactive({
  universityName: '',
  program: '',
  level: '',
  academicYear: '',
  faculty: '',
  department: '',
  studentId: '',
  gpa: '',
})

const radarChart = ref(null)
let chartInstance = null

const tabs = [
  'Personal',
  'Academic',
  'Skills',
  'Languages',
  'Projects',
  'Assignments',
  'Certificates',
  'Experience',
  'Achievements',
  'Documents',
]

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
  const raw = student?.raw?.bio
  if (!raw) return {}
  try {
    const parsed = JSON.parse(raw)
    return parsed && typeof parsed === 'object' ? parsed : {}
  } catch {
    return {}
  }
}

const formatDate = (value) => {
  if (!value) return 'Not scheduled yet'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return 'Not scheduled yet'
  return date.toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })
}

const currentUserId = computed(() => parseId(authStore.user?.id || authStore.user?.userId))
const currentStudentId = computed(() =>
  parseId(
    selectedStudent.value?.id ||
      selectedStudent.value?.userId ||
      authStore.user?.profile?.id ||
      authStore.user?.profile?.studentId ||
      authStore.user?.studentProfile?.id ||
      authStore.user?.studentId ||
      authStore.user?.id,
  ),
)
const currentUserEmail = computed(() =>
  String(authStore.user?.email || '')
    .trim()
    .toLowerCase(),
)

const meta = computed(() => parseMeta(selectedStudent.value))
const verificationData = computed(() => selectedStudent.value?.raw?.verificationData || {})
const personalMeta = computed(() => meta.value.personal || {})
const academicMeta = computed(() => meta.value.academic || {})
const radarMeta = computed(() => meta.value.radar || {})
const academicRecords = computed(() => {
  const fromStudent = Array.isArray(selectedStudent.value?.academicRecords)
    ? selectedStudent.value.academicRecords
    : []
  if (fromStudent.length) return fromStudent

  return Array.isArray(meta.value.academicRecords) ? meta.value.academicRecords : []
})

const profileName = computed(
  () =>
    selectedStudent.value?.fullName ||
    personalMeta.value.fullName ||
    authStore.user?.fullName ||
    'Student Profile',
)

const universityName = computed(
  () =>
    selectedStudent.value?.university?.name ||
    verificationData.value.universityName ||
    meta.value.universityName ||
    authStore.user?.university?.name ||
    'University',
)

const locationText = computed(
  () =>
    selectedStudent.value?.address ||
    personalMeta.value.address ||
    meta.value.address ||
    'Not specified',
)

const headline = computed(() => {
  const program =
    selectedStudent.value?.program ||
    verificationData.value.program ||
    academicMeta.value.program ||
    'Program'
  return `${program} Student`
})

const aboutMe = computed(
  () => selectedStudent.value?.summary || meta.value.summary || 'No biography added yet.',
)

const profileCompletion = computed(() => Number(selectedStudent.value?.profileCompletion || 0))
const opportunityCount = computed(() => Number(selectedStudent.value?.applicationCount || 0))

const degreeText = computed(() => {
  const program =
    selectedStudent.value?.program ||
    verificationData.value.program ||
    academicMeta.value.program ||
    '--'
  const department = selectedStudent.value?.department || academicMeta.value.department || ''
  return department ? `${program} / ${department}` : program
})

const levelText = computed(
  () =>
    selectedStudent.value?.level ||
    verificationData.value.level ||
    academicMeta.value.currentClass ||
    academicMeta.value.level ||
    '--',
)

const gpaText = computed(() => selectedStudent.value?.gpa || academicMeta.value.gpa || '0.00')

const emailText = computed(
  () => selectedStudent.value?.email || personalMeta.value.email || 'Not specified',
)
const phoneText = computed(
  () => selectedStudent.value?.phone || personalMeta.value.phone || 'Not specified',
)
const genderText = computed(() => {
  const value = selectedStudent.value?.gender || personalMeta.value.gender || ''
  const map = {
    Male: 'Male',
    Female: 'Female',
    prefer_not_to_say: 'Prefer not to say',
  }
  return map[value] || value || 'Not specified'
})
const nationalityText = computed(
  () => selectedStudent.value?.nationality || personalMeta.value.nationality || 'Not specified',
)
const studentIdText = computed(
  () =>
    selectedStudent.value?.studentId ||
    verificationData.value.studentId ||
    academicMeta.value.studentId ||
    'Not specified',
)
const facultyText = computed(
  () => selectedStudent.value?.faculty || academicMeta.value.faculty || 'Not specified',
)
const departmentText = computed(
  () => selectedStudent.value?.department || academicMeta.value.department || 'Not specified',
)
const academicYearText = computed(
  () => selectedStudent.value?.academicYear || academicMeta.value.academicYear || 'Not specified',
)
const statusText = computed(() => selectedStudent.value?.status || 'Active')
const admissionDateText = computed(() => {
  const value = selectedStudent.value?.admissionDate
  return value ? formatDate(value) : 'Not specified'
})

const fillAcademicForm = (student) => {
  const metaData = parseMeta(student)
  const academic = metaData.academic || {}
  const verification = student?.raw?.verificationData || {}

  academicForm.universityName =
    student?.university?.name || verification.universityName || metaData.universityName || ''
  academicForm.program = student?.program || verification.program || academic.program || ''
  academicForm.level =
    student?.level || verification.level || academic.currentClass || academic.level || ''
  academicForm.academicYear =
    student?.academicYear || academic.academicYear || verification.academicYear || ''
  academicForm.faculty = student?.faculty || academic.faculty || verification.faculty || ''
  academicForm.department =
    student?.department || academic.department || verification.department || ''
  academicForm.studentId = student?.studentId || academic.studentId || verification.studentId || ''
  academicForm.gpa = student?.gpa || metaData.gpa || '0.00'
}

const openAcademicEditor = () => {
  if (!selectedStudent.value?.id) return
  academicEditMessage.value = ''
  fillAcademicForm(selectedStudent.value)
  isAcademicEditorOpen.value = true
}

const closeAcademicEditor = () => {
  isAcademicEditorOpen.value = false
}

const saveAcademicRecord = async () => {
  if (!selectedStudent.value?.id) return

  savingAcademicRecord.value = true
  academicEditMessage.value = ''

  try {
    const updated = await studentStore.updateStudent(selectedStudent.value.id, {
      gpa: academicForm.gpa,
      program: academicForm.program,
      level: academicForm.level,
      academicYear: academicForm.academicYear,
      faculty: academicForm.faculty,
      department: academicForm.department,
      studentId: academicForm.studentId,
      academic: {
        program: academicForm.program,
        currentClass: academicForm.level,
        level: academicForm.level,
        academicYear: academicForm.academicYear,
        faculty: academicForm.faculty,
        department: academicForm.department,
        studentId: academicForm.studentId,
      },
    })

    selectedStudent.value = updated
    fillAcademicForm(updated)
    academicEditMessage.value = 'Academic record updated successfully.'
    closeAcademicEditor()
  } catch {
    academicEditMessage.value = 'Unable to update academic record now. Please retry.'
  } finally {
    savingAcademicRecord.value = false
  }
}

const trackedApplications = computed(() => {
  const toTimestamp = (value) => {
    const parsed = new Date(value || '').getTime()
    return Number.isNaN(parsed) ? 0 : parsed
  }

  return [...candidatures.value].sort(
    (left, right) =>
      toTimestamp(right.lastUpdated || right.appliedDate) -
      toTimestamp(left.lastUpdated || left.appliedDate),
  )
})

const formatStatus = (status) => {
  const map = {
    applied: 'Applied',
    interview: 'Interview Scheduled',
    offer: 'Offer Received',
    accepted: 'Accepted',
    rejected: 'Rejected',
  }
  return map[status] || 'Pending'
}

const statusClass = (status) => `status-${String(status || 'pending').toLowerCase()}`

const skills = computed(() => {
  const fromStore = Array.isArray(selectedStudent.value?.skills) ? selectedStudent.value.skills : []
  if (fromStore.length) return fromStore

  const radarLabels = []
  if ((radarMeta.value.academic?.entries || []).length) radarLabels.push('Academic')
  if ((radarMeta.value.certificate?.entries || []).length) radarLabels.push('Certificate')
  if ((radarMeta.value.numerique?.entries || []).length) radarLabels.push('Numerique')
  if ((radarMeta.value.langue?.entries || []).length) radarLabels.push('Language')
  if ((radarMeta.value.stage?.entries || []).length) radarLabels.push('Stage')
  return radarLabels.length ? radarLabels : ['No skills yet']
})

const languages = computed(() => {
  const fromMeta = Array.isArray(meta.value.languages) ? meta.value.languages : []
  if (fromMeta.length) {
    return fromMeta.map((item) =>
      typeof item === 'string' ? { name: item, level: 'Recorded' } : item,
    )
  }

  const fromStore = Array.isArray(selectedStudent.value?.languages)
    ? selectedStudent.value.languages
    : []
  if (fromStore.length) {
    return fromStore.map((item) =>
      typeof item === 'string' ? { name: item, level: 'Recorded' } : item,
    )
  }

  const raw = radarMeta.value.langue?.entries || []
  return raw
    .flatMap((entry) => String(entry.languages || '').split(';'))
    .map((value) => value.trim())
    .filter(Boolean)
    .map((value) => ({
      name: value.split(':')[0].trim(),
      level: value.split(':')[1]?.trim() || 'Recorded',
    }))
})

const projects = computed(() => {
  const list = Array.isArray(selectedStudent.value?.projects)
    ? selectedStudent.value.projects
    : Array.isArray(meta.value.projects)
      ? meta.value.projects
      : []

  if (list.length) return list

  const radarProjects = radarMeta.value.numerique?.entries || []
  return radarProjects
    .filter((entry) => entry.projectName)
    .map((entry) => ({
      title: entry.projectName,
      description: entry.projectDescription || 'No description',
    }))
})

const experiences = computed(() => {
  const list = Array.isArray(meta.value.experiences) ? meta.value.experiences : []
  if (list.length) return list

  const radarStages = radarMeta.value.stage?.entries || []
  return radarStages
    .filter((entry) => entry.company)
    .map((entry) => ({
      title: 'Stage',
      company: entry.company,
      date: entry.duration || 'N/A',
    }))
})

const certificates = computed(() => {
  const list = Array.isArray(meta.value.certificates) ? meta.value.certificates : []
  if (list.length) return list

  const radarCerts = radarMeta.value.certificate?.entries || []
  return radarCerts.map((entry) => entry.trainingName).filter(Boolean)
})

const documents = computed(() => {
  const docs = Array.isArray(selectedStudent.value?.studentDocuments)
    ? selectedStudent.value.studentDocuments
    : []

  const normalizedStoreDocs = docs.map((doc) => ({
    name: doc.name || doc.title || doc.fileName || 'Document',
    type: doc.type || 'Document',
    url: doc.url || doc.path || doc.fileUrl || '',
  }))

  const proofDocs = [
    ...(radarMeta.value.certificate?.entries || []),
    ...(radarMeta.value.numerique?.entries || []),
    ...(radarMeta.value.stage?.entries || []),
  ]
    .filter((entry) => entry.proofName || entry.proofFile)
    .map((entry) => ({
      name: entry.proofName || 'Proof file',
      type: 'Proof',
      url: entry.proofFile || '',
    }))

  const merged = [...normalizedStoreDocs, ...proofDocs]
  return merged.length ? merged : [{ name: 'No documents yet', type: 'Info', url: '' }]
})

const assignmentsMessage = computed(() => {
  const count = Array.isArray(meta.value.assignments) ? meta.value.assignments.length : 0
  return count ? `${count} assignments available.` : 'No assignments yet.'
})

const achievementsMessage = computed(() => {
  const count = Array.isArray(meta.value.achievements) ? meta.value.achievements.length : 0
  return count ? `${count} achievements recorded.` : 'No achievements yet.'
})

const avatarUrl = computed(() => {
  const profileUrl =
    selectedStudent.value?.profileUrl || selectedStudent.value?.raw?.profileUrl || ''
  if (/^https?:\/\//i.test(profileUrl) || /^data:image\//i.test(profileUrl)) {
    return profileUrl
  }

  return `https://ui-avatars.com/api/?background=0D8ABC&color=fff&name=${encodeURIComponent(profileName.value)}`
})

const radarScores = computed(() => {
  const scoreFor = (entries) => {
    const length = Array.isArray(entries) ? entries.length : 0
    if (!length) return 20
    return Math.min(100, 45 + length * 15)
  }

  return [
    scoreFor(radarMeta.value.academic?.entries),
    scoreFor(radarMeta.value.certificate?.entries),
    scoreFor(radarMeta.value.numerique?.entries),
    scoreFor(radarMeta.value.langue?.entries),
    scoreFor(radarMeta.value.stage?.entries),
  ]
})

const loadStudentProfile = async () => {
  isLoading.value = true
  isLoadingCandidatures.value = true
  try {
    await studentStore.fetchStudents()

    const found = studentStore.students.find((student) => {
      const sameUser = currentUserId.value && Number(student.userId) === Number(currentUserId.value)
      const sameEmail =
        currentUserEmail.value &&
        String(student.email || '')
          .trim()
          .toLowerCase() === currentUserEmail.value
      return sameUser || sameEmail
    })

    if (found?.id) {
      await studentStore.fetchStudent(found.id)
      selectedStudent.value = studentStore.student || found
      await fetchCandidatures(found.id)
      return
    }

    selectedStudent.value = found || null

    const fallbackStudentId = currentStudentId.value || currentUserId.value
    if (fallbackStudentId) {
      await fetchCandidatures(fallbackStudentId)
    }
  } finally {
    isLoading.value = false
    isLoadingCandidatures.value = false
  }
}

const goToSettings = () => {
  router.push({ name: 'studentSettings' })
}

const openDocument = (doc) => {
  if (!doc?.url) return
  window.open(doc.url, '_blank', 'noopener,noreferrer')
}

const downloadCv = () => {
  const cvDoc = documents.value.find((doc) => /cv|resume/i.test(doc.name || ''))
  if (cvDoc?.url) {
    openDocument(cvDoc)
    return
  }

  if (documents.value[0]?.url) {
    openDocument(documents.value[0])
  }
}

const initializeRadarChart = () => {
  if (!radarChart.value) return

  // Destroy existing chart if it exists
  if (chartInstance) {
    chartInstance.destroy()
  }

  const ctx = radarChart.value.getContext('2d')
  chartInstance = new Chart(ctx, {
    type: 'radar',
    data: {
      labels: ['Académique', 'Certificat', 'Numérique', 'Langue', 'Stage'],
      datasets: [
        {
          label: 'Niveau de compétences',
          data: radarScores.value,
          borderColor: '#D4A017',
          backgroundColor: 'rgba(6, 170, 197, 0.2)',
          borderWidth: 2,
          pointBackgroundColor: '#D4A017',
          pointBorderColor: '#fff',
          pointBorderWidth: 2,
          pointRadius: 5,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      scales: {
        r: {
          beginAtZero: true,
          max: 100,
          ticks: {
            stepSize: 20,
            color: '#999',
          },
          grid: {
            color: '#ddd',
          },
        },
      },
      plugins: {
        legend: {
          display: true,
          position: 'bottom',
          labels: {
            color: '#0d2b45',
            font: { size: 14 },
          },
        },
      },
    },
  })
}

// Watch for changes to activeTab
watch(activeTab, async (newTab) => {
  if (newTab === 'Skills') {
    await nextTick()
    initializeRadarChart()
  }
})

watch(radarScores, () => {
  if (activeTab.value === 'Skills') {
    initializeRadarChart()
  }
})

onMounted(async () => {
  await loadStudentProfile()
  if (activeTab.value === 'Skills') {
    await nextTick()
    initializeRadarChart()
  }
})

onBeforeUnmount(() => {
  if (chartInstance) {
    chartInstance.destroy()
    chartInstance = null
  }
})
</script>

<style scoped>
.profile-page {
  background: #f5fafc;

  min-height: 100vh;

  width: 100%;

  color: #777;

  font-family: Arial, sans-serif;
}

/* COVER */

.profile-cover {
  height: 260px;

  background: linear-gradient(135deg, #0d2b45, #0d2b45);
}

/* HEADER */

.profile-card {
  background: white;

  width: 85%;

  margin: -80px auto 0;

  border-radius: 25px;

  padding: 30px;

  display: flex;

  align-items: center;

  gap: 30px;

  position: relative;

  box-shadow: 0 20px 40px #0002;
}

.avatar img {
  width: 150px;

  height: 150px;

  border-radius: 50%;

  border: 6px solid white;

  object-fit: cover;
}

.student-info {
  flex: 1;
}

.student-info h1 {
  color: #0d2b45;

  margin: 0 0 5px 0;
}

.student-info h3 {
  color: #d4a017;

  margin: 0 0 15px 0;

  font-weight: normal;
}

.student-info p {
  margin: 8px 0;

  color: #666;
}

.student-info i {
  margin-right: 8px;

  color: #d4a017;
}

/* TAB NAVIGATION */

.tab-container {
  width: 85%;

  margin: 30px auto;

  display: flex;

  justify-content: space-between;

  align-items: center;

  background: white;

  border-radius: 20px;

  padding: 15px 25px;

  box-shadow: 0 10px 25px #0001;
}

.tabs {
  display: flex;

  gap: 5px;

  flex: 1;

  flex-wrap: wrap;
}

.tab-btn {
  padding: 10px 18px;

  border: none;

  background: transparent;

  color: #666;

  cursor: pointer;

  border-radius: 15px;

  font-size: 14px;

  font-weight: 500;

  transition: all 0.3s ease;
}

.tab-btn:hover {
  background: #e5f8fb;

  color: #d4a017;
}

.tab-btn.active {
  background: #d4a017;

  color: white;
}

.profile-actions {
  display: flex;

  gap: 10px;

  margin-left: auto;
}

.profile-actions button {
  padding: 10px 18px;

  border-radius: 10px;

  border: none;

  cursor: pointer;

  font-size: 14px;

  font-weight: 500;

  display: flex;

  align-items: center;

  gap: 8px;

  transition: all 0.3s ease;
}

.edit {
  background: #d4a017;

  color: white;
}

.edit:hover {
  background: #059aae;
}

.download {
  background: #0d2b45;

  color: white;
}

.download:hover {
  background: #043545;
}

/* CONTENT */

.profile-container {
  width: 85%;

  margin: 20px auto 40px;
}

.content-section {
  display: grid;

  grid-template-columns: 1fr;

  gap: 30px;
}

.personal-grid {
  display: grid;

  grid-template-columns: minmax(0, 1.6fr) minmax(0, 1fr);

  gap: 30px;

  align-items: start;
}

.personal-main {
  display: grid;

  gap: 30px;
}

.skills-wrapper {
  display: grid;

  grid-template-columns: 1fr 1fr;

  gap: 30px;
}

.skill-radar-box {
  display: flex;

  flex-direction: column;

  align-items: center;
}

.skill-radar-box canvas {
  max-width: 100%;

  height: auto;
}

.box {
  background: white;

  padding: 25px;

  border-radius: 20px;

  box-shadow: 0 10px 25px #0001;
}

.box h2 {
  color: #0d2b45;

  margin-bottom: 20px;
}

.section-head {
  display: flex;

  justify-content: space-between;

  align-items: center;

  gap: 16px;

  margin-bottom: 20px;
}

.section-head h2 {
  margin-bottom: 0;
}

.academic-edit-btn {
  white-space: nowrap;
}

.academic-records {
  margin-top: 22px;
  padding-top: 18px;
  border-top: 1px solid #e8eef3;
}

.academic-records h3 {
  margin: 0 0 14px;
  color: #0d2b45;
  font-size: 18px;
}

.record-item {
  padding: 14px 0;
  border-bottom: 1px solid #eef3f6;
}

.record-item:last-child {
  border-bottom: none;
}

.record-item strong {
  display: block;
  color: #0d2b45;
  margin-bottom: 6px;
}

.record-item p {
  margin: 0;
  color: #667788;
}

.modal-overlay {
  position: fixed;

  inset: 0;

  background: rgba(4, 16, 28, 0.62);

  display: flex;

  align-items: center;

  justify-content: center;

  padding: 20px;

  z-index: 1000;
}

.modal-card {
  width: min(780px, 100%);

  background: white;

  border-radius: 24px;

  padding: 24px;

  box-shadow: 0 24px 60px rgba(0, 0, 0, 0.25);
}

.modal-header {
  display: flex;

  justify-content: space-between;

  align-items: flex-start;

  gap: 16px;

  margin-bottom: 20px;
}

.modal-eyebrow {
  margin: 0 0 6px;

  text-transform: uppercase;

  letter-spacing: 0.08em;

  font-size: 12px;

  color: #d4a017;

  font-weight: 700;
}

.modal-header h2 {
  margin: 0;

  color: #0d2b45;
}

.modal-close {
  border: none;

  background: #eef4f7;

  color: #0d2b45;

  width: 40px;

  height: 40px;

  border-radius: 50%;

  font-size: 28px;

  line-height: 1;

  cursor: pointer;
}

.academic-form {
  display: grid;

  gap: 20px;
}

.academic-form-grid {
  display: grid;

  grid-template-columns: repeat(2, minmax(0, 1fr));

  gap: 16px;
}

.academic-form-grid label {
  display: grid;

  gap: 8px;
}

.academic-form-grid span {
  color: #0d2b45;

  font-weight: 600;

  font-size: 14px;
}

.academic-form-grid input {
  width: 100%;

  border: 1px solid #d9e3ea;

  border-radius: 12px;

  padding: 12px 14px;

  background: #fbfdff;

  color: #0d2b45;
}

.academic-form-grid input[readonly] {
  background: #f3f7fa;

  color: #6b7b88;
}

.modal-actions {
  display: flex;

  justify-content: flex-end;

  gap: 12px;
}

.status-message {
  margin-top: 16px;

  color: #1e7d45;

  font-weight: 600;
}

.tracker-box {
  position: sticky;

  top: 20px;
}

.tracker-head {
  display: flex;

  justify-content: space-between;

  align-items: center;

  gap: 10px;

  margin-bottom: 18px;
}

.tracker-head h2 {
  margin: 0;
}

.tracker-head span {
  font-size: 13px;

  color: #6b7785;
}

.tracker-list {
  display: grid;

  gap: 12px;
}

.tracker-item {
  border: 1px solid #e4edf1;

  border-radius: 14px;

  padding: 14px;

  background: #f8fcff;

  display: grid;

  gap: 10px;
}

.tracker-item-top {
  display: flex;

  justify-content: space-between;

  align-items: flex-start;

  gap: 10px;
}

.tracker-item-top strong {
  color: #0d2b45;
}

.tracker-status {
  font-size: 12px;

  font-weight: 700;

  padding: 6px 10px;

  border-radius: 999px;
}

.tracker-status.status-applied {
  background: #eef4ff;

  color: #315eb8;
}

.tracker-status.status-interview {
  background: #fff7e6;

  color: #ad7a08;
}

.tracker-status.status-offer {
  background: #ecfbf0;

  color: #1f8f50;
}

.tracker-status.status-accepted {
  background: #e8f7ee;

  color: #1e7d45;
}

.tracker-status.status-rejected {
  background: #ffeef0;

  color: #b03a4b;
}

.tracker-company {
  margin: 0;

  color: #536474;

  display: flex;

  align-items: center;

  gap: 8px;
}

.tracker-dates {
  display: grid;

  gap: 4px;

  font-size: 13px;

  color: #617487;
}

.tracker-info,
.tracker-empty {
  margin: 0;

  color: #617487;
}

.tracker-error {
  margin: 0;

  color: #b03a4b;
}

.item {
  display: flex;

  justify-content: space-between;

  align-items: center;

  padding: 15px 0;

  border-bottom: 1px solid #eee;
}

.item:last-child {
  border-bottom: none;
}

.item strong {
  color: #0d2b45;
}

.item span {
  color: #888;
}

.score {
  background: #e5f8fb;

  padding: 5px 12px;

  border-radius: 15px;

  color: #d4a017 !important;

  font-weight: bold;
}

.skills {
  display: flex;

  flex-wrap: wrap;

  gap: 10px;
}

.skills span {
  display: inline-block;

  background: #e5f8fb;

  color: #0d2b45;

  padding: 8px 15px;

  border-radius: 20px;

  font-size: 13px;
}

.add-skills-btn {
  background: #d4a017;

  color: white;

  border: none;

  padding: 10px 20px;

  border-radius: 15px;

  margin-top: 15px;

  cursor: pointer;

  font-weight: 500;
}

.add-skills-btn:hover {
  background: #059aae;
}

.language-item {
  padding: 15px 0;

  border-bottom: 1px solid #eee;

  display: flex;

  justify-content: space-between;

  align-items: center;
}

.language-item:last-child {
  border-bottom: none;
}

.lang-info {
  display: flex;

  flex-direction: column;
}

.lang-info strong {
  color: #0d2b45;
}

.lang-level {
  color: #999;

  font-size: 13px;

  margin-top: 5px;
}

.project {
  border-left: 4px solid #d4a017;

  padding-left: 15px;

  margin-bottom: 20px;
}

.project h3 {
  color: #0d2b45;

  margin: 0 0 8px 0;
}

.project p {
  color: #888;

  margin: 0;
}

.match {
  text-align: center;
}

.circle {
  width: 120px;

  height: 120px;

  border-radius: 50%;

  margin: 20px auto;

  display: flex;

  align-items: center;

  justify-content: center;

  background: #d4a017;

  color: white;

  font-size: 30px;

  font-weight: bold;
}

.timeline {
  padding: 15px 0;

  border-left: 3px solid #d4a017;

  padding-left: 15px;

  margin-bottom: 20px;
}

.timeline h3 {
  color: #0d2b45;

  margin: 0 0 5px 0;
}

.timeline p {
  margin: 0 0 5px 0;

  color: #888;
}

.timeline span {
  color: #aaa;

  font-size: 13px;
}

.document {
  display: flex;

  align-items: center;

  justify-content: space-between;

  background: #f5fafc;

  padding: 15px;

  border-radius: 10px;

  margin-bottom: 10px;
}

.document i {
  font-size: 25px;

  color: #d4a017;

  margin-right: 15px;
}

.document span {
  flex: 1;

  color: #666;
}

.document button {
  background: #0d2b45;

  color: white;

  border: none;

  padding: 8px 15px;

  border-radius: 8px;

  cursor: pointer;

  font-size: 12px;
}

.document button:hover {
  background: #0d2b45;
}

@media (max-width: 1200px) {
  .personal-grid {
    grid-template-columns: 1fr;
  }

  .tracker-box {
    position: static;
  }

  .skills-wrapper {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 900px) {
  .tab-container {
    flex-direction: column;

    gap: 15px;

    align-items: flex-start;
  }

  .tabs {
    width: 100%;
  }

  .profile-actions {
    width: 100%;

    margin-left: 0;
  }

  .profile-card {
    flex-direction: column;

    text-align: center;
  }

  .profile-container {
    width: 90%;
  }
}
</style>
