<template>
  <div class="student-profile-page">
    <header class="page-header">
      <div>
        <p class="eyebrow">Student Profile Management</p>
        <h1>Maintain student profiles</h1>
        <p class="page-copy">
          Open any section to update a student profile without leaving the page. The drawer keeps
          the profile summary visible behind it.
        </p>
      </div>
      <div class="header-actions">
        <button class="btn btn-outline-light btn-sm" @click="openDrawer('personal')">
          <i class="bi bi-plus-lg"></i>
          Add new section
        </button>
      </div>
    </header>

    <div class="section-tabs nav nav-pills flex-wrap">
      <button
        v-for="section in sections"
        :key="section.key"
        type="button"
        class="nav-link"
        :class="{ active: section.key === activeSection }"
        @click="openDrawer(section.key)"
      >
        <i :class="section.icon"></i>
        {{ section.label }}
      </button>
    </div>

    <div class="profile-layout">
      <aside class="profile-card card shadow-sm">
        <div class="profile-card-top">
          <div class="avatar-wrapper">
            <img :src="student.photo" alt="Student photo" class="avatar" />
          </div>
          <div>
            <h2>{{ student.fullName }}</h2>
            <p class="muted">{{ student.program }} · {{ student.academic.level }}</p>
          </div>
        </div>

        <div class="profile-details">
          <div class="detail-row">
            <span>Status</span>
            <strong>{{ student.academic.status }}</strong>
          </div>
          <div class="detail-row">
            <span>GPA</span>
            <strong>{{ student.academic.currentGpa }}</strong>
          </div>
          <div class="detail-row">
            <span>Credits</span>
            <strong>{{ student.academic.creditsEarned }}</strong>
          </div>
          <div class="detail-row">
            <span>Advisor</span>
            <strong>{{ student.advisor }}</strong>
          </div>
        </div>
      </aside>

      <main class="dashboard-grid">
        <section class="stat-card card shadow-sm">
          <div>
            <span class="stat-label">Completed projects</span>
            <strong>{{ student.projects.length }}</strong>
          </div>
          <span class="badge bg-info">Updated</span>
        </section>

        <section class="stat-card card shadow-sm">
          <div>
            <span class="stat-label">Skills on file</span>
            <strong>{{ student.skills.length }}</strong>
          </div>
          <span class="badge bg-success">Live</span>
        </section>

        <section class="stat-card card shadow-sm">
          <div>
            <span class="stat-label">Language profiles</span>
            <strong>{{ student.languages.length }}</strong>
          </div>
          <span class="badge bg-warning text-dark">Review</span>
        </section>

        <section class="info-card card shadow-sm">
          <h3>Academic snapshot</h3>
          <div class="info-grid">
            <div>
              <small>Faculty</small>
              <p>{{ student.academic.faculty }}</p>
            </div>
            <div>
              <small>Department</small>
              <p>{{ student.academic.department }}</p>
            </div>
            <div>
              <small>Semester</small>
              <p>{{ student.academic.semester }}</p>
            </div>
            <div>
              <small>Graduation year</small>
              <p>{{ student.academic.graduationYear }}</p>
            </div>
          </div>
        </section>
      </main>
    </div>

    <div class="drawer-backdrop" v-if="isDrawerOpen" @click="closeDrawer"></div>

    <aside class="drawer" :class="{ open: isDrawerOpen }" aria-labelledby="drawer-title">
      <div class="drawer-header">
        <div>
          <p class="eyebrow">{{ currentSection?.label }}</p>
          <h2 id="drawer-title">{{ currentSection?.title }}</h2>
          <p class="drawer-copy">{{ currentSection?.description }}</p>
        </div>
        <button
          type="button"
          class="btn-close"
          aria-label="Close drawer"
          @click="closeDrawer"
        ></button>
      </div>

      <div class="drawer-status">
        <span class="autosave">{{ autoSaveStatus }}</span>
        <small>{{ lastSaved }}</small>
      </div>

      <div class="drawer-content">
        <template v-if="activeSection === 'personal'">
          <div class="drawer-card">
            <h3>Personal information</h3>
            <div class="form-row">
              <div class="upload-card">
                <div class="upload-preview">
                  <img :src="student.photo" alt="student photo" />
                </div>
                <label class="btn btn-outline-secondary btn-sm upload-button">
                  Upload Photo
                  <input type="file" @change="updatePhoto" hidden />
                </label>
              </div>

              <div class="form-grid">
                <div class="form-floating">
                  <input
                    type="text"
                    class="form-control"
                    id="firstName"
                    v-model="student.personal.firstName"
                    placeholder="First name"
                  />
                  <label for="firstName">First Name</label>
                </div>
                <div class="form-floating">
                  <input
                    type="text"
                    class="form-control"
                    id="lastName"
                    v-model="student.personal.lastName"
                    placeholder="Last name"
                  />
                  <label for="lastName">Last Name</label>
                </div>
                <div class="form-floating">
                  <select class="form-select" id="gender" v-model="student.personal.gender">
                    <option value="Female">Female</option>
                    <option value="Male">Male</option>
                    <option value="Other">Other</option>
                  </select>
                  <label for="gender">Gender</label>
                </div>
                <div class="form-floating">
                  <input
                    type="date"
                    class="form-control"
                    id="dob"
                    v-model="student.personal.dob"
                    placeholder="Date of birth"
                  />
                  <label for="dob">Date of Birth</label>
                </div>
                <div class="form-floating">
                  <input
                    type="text"
                    class="form-control"
                    id="nationality"
                    v-model="student.personal.nationality"
                    placeholder="Nationality"
                  />
                  <label for="nationality">Nationality</label>
                </div>
                <div class="form-floating">
                  <input
                    type="text"
                    class="form-control"
                    id="studentNumber"
                    v-model="student.personal.studentNumber"
                    placeholder="Student number"
                  />
                  <label for="studentNumber">Student Number</label>
                </div>
                <div class="form-floating">
                  <input
                    type="text"
                    class="form-control"
                    id="nationalId"
                    v-model="student.personal.nationalId"
                    placeholder="National ID"
                  />
                  <label for="nationalId">National ID</label>
                </div>
                <div class="form-floating">
                  <input
                    type="tel"
                    class="form-control"
                    id="phone"
                    v-model="student.personal.phone"
                    placeholder="Phone"
                  />
                  <label for="phone">Phone</label>
                </div>
                <div class="form-floating">
                  <input
                    type="email"
                    class="form-control"
                    id="email"
                    v-model="student.personal.email"
                    placeholder="Email"
                  />
                  <label for="email">Email</label>
                </div>
                <div class="form-floating form-floating-full">
                  <textarea
                    class="form-control"
                    id="address"
                    v-model="student.personal.address"
                    placeholder="Address"
                    rows="2"
                  ></textarea>
                  <label for="address">Address</label>
                </div>
                <div class="form-floating form-floating-full">
                  <input
                    type="text"
                    class="form-control"
                    id="emergencyContact"
                    v-model="student.personal.emergencyContact"
                    placeholder="Emergency contact"
                  />
                  <label for="emergencyContact">Emergency Contact</label>
                </div>
              </div>
            </div>
          </div>
        </template>

        <template v-else-if="activeSection === 'academic'">
          <div class="drawer-card">
            <h3>Academic details</h3>
            <div class="form-grid two-column">
              <div class="form-floating">
                <select class="form-select" id="faculty" v-model="student.academic.faculty">
                  <option value="Computer Science">Computer Science</option>
                  <option value="Engineering">Engineering</option>
                  <option value="Business">Business</option>
                  <option value="Medicine">Medicine</option>
                </select>
                <label for="faculty">Faculty</label>
              </div>
              <div class="form-floating">
                <input
                  type="text"
                  class="form-control"
                  id="department"
                  v-model="student.academic.department"
                  placeholder="Department"
                />
                <label for="department">Department</label>
              </div>
              <div class="form-floating">
                <input
                  type="text"
                  class="form-control"
                  id="programme"
                  v-model="student.academic.programme"
                  placeholder="Programme"
                />
                <label for="programme">Programme</label>
              </div>
              <div class="form-floating">
                <select class="form-select" id="level" v-model="student.academic.level">
                  <option value="Undergraduate">Undergraduate</option>
                  <option value="Master">Master</option>
                  <option value="PhD">PhD</option>
                </select>
                <label for="level">Level</label>
              </div>
              <div class="form-floating">
                <input
                  type="text"
                  class="form-control"
                  id="semester"
                  v-model="student.academic.semester"
                  placeholder="Semester"
                />
                <label for="semester">Semester</label>
              </div>
              <div class="form-floating">
                <input
                  type="number"
                  class="form-control"
                  id="admissionYear"
                  v-model="student.academic.admissionYear"
                  placeholder="Admission Year"
                />
                <label for="admissionYear">Admission Year</label>
              </div>
              <div class="form-floating">
                <input
                  type="number"
                  class="form-control"
                  id="graduationYear"
                  v-model="student.academic.graduationYear"
                  placeholder="Graduation Year"
                />
                <label for="graduationYear">Graduation Year</label>
              </div>
              <div class="form-floating">
                <input
                  type="text"
                  class="form-control"
                  id="currentGpa"
                  v-model="student.academic.currentGpa"
                  placeholder="Current GPA"
                />
                <label for="currentGpa">Current GPA</label>
              </div>
              <div class="form-floating">
                <input
                  type="number"
                  class="form-control"
                  id="creditsEarned"
                  v-model="student.academic.creditsEarned"
                  placeholder="Credits Earned"
                />
                <label for="creditsEarned">Credits Earned</label>
              </div>
              <div class="form-floating form-floating-full">
                <select class="form-select" id="academicStatus" v-model="student.academic.status">
                  <option value="On track">On track</option>
                  <option value="Needs attention">Needs attention</option>
                  <option value="Probation">Probation</option>
                </select>
                <label for="academicStatus">Academic Status</label>
              </div>
            </div>
          </div>
        </template>

        <template v-else-if="activeSection === 'skills'">
          <div class="drawer-card">
            <h3>Skills</h3>
            <div class="skill-list">
              <div class="skill-row" v-for="(skill, index) in student.skills" :key="skill.id">
                <div class="form-floating">
                  <input
                    type="text"
                    class="form-control"
                    v-model="skill.name"
                    placeholder="Skill name"
                  />
                  <label>Skill name</label>
                </div>
                <div class="form-floating">
                  <select class="form-select" v-model="skill.level">
                    <option>Beginner</option>
                    <option>Intermediate</option>
                    <option>Advanced</option>
                    <option>Expert</option>
                  </select>
                  <label>Level</label>
                </div>
                <button
                  class="btn btn-outline-danger btn-sm"
                  type="button"
                  @click="removeSkill(index)"
                >
                  Remove
                </button>
              </div>
            </div>
            <button class="btn btn-outline-light btn-sm mt-3" type="button" @click="addSkill">
              <i class="bi bi-plus-lg"></i> Add Skill
            </button>
          </div>
        </template>

        <template v-else-if="activeSection === 'languages'">
          <div class="drawer-card">
            <h3>Language profile</h3>
            <div class="language-list">
              <div
                class="language-row"
                v-for="(language, index) in student.languages"
                :key="language.id"
              >
                <div class="form-floating">
                  <input
                    type="text"
                    class="form-control"
                    v-model="language.language"
                    placeholder="Language"
                  />
                  <label>Language</label>
                </div>
                <div class="form-floating">
                  <select class="form-select" v-model="language.reading">
                    <option>Basic</option>
                    <option>Intermediate</option>
                    <option>Advanced</option>
                    <option>Fluent</option>
                  </select>
                  <label>Reading</label>
                </div>
                <div class="form-floating">
                  <select class="form-select" v-model="language.writing">
                    <option>Basic</option>
                    <option>Intermediate</option>
                    <option>Advanced</option>
                    <option>Fluent</option>
                  </select>
                  <label>Writing</label>
                </div>
                <button
                  class="btn btn-outline-danger btn-sm"
                  type="button"
                  @click="removeLanguage(index)"
                >
                  Remove
                </button>
              </div>
            </div>
            <button class="btn btn-outline-light btn-sm mt-3" type="button" @click="addLanguage">
              <i class="bi bi-plus-lg"></i> Add Language
            </button>
          </div>
        </template>

        <template v-else-if="activeSection === 'projects'">
          <div class="drawer-card">
            <h3>Projects</h3>
            <div v-for="project in student.projects" :key="project.id" class="project-card card">
              <div class="project-card-header">
                <div>
                  <strong>{{ project.title }}</strong>
                  <small>{{ project.category }} · {{ project.role }}</small>
                </div>
                <span class="badge bg-secondary">{{ project.teamSize }} members</span>
              </div>
              <p>{{ project.description }}</p>
              <div class="project-meta">
                <span>Tech: {{ project.technologies }}</span>
                <span>Supervisor: {{ project.supervisor }}</span>
              </div>
            </div>
            <button class="btn btn-outline-light btn-sm mt-3" type="button" @click="addProject">
              <i class="bi bi-plus-lg"></i> Add Project
            </button>
          </div>
        </template>

        <template v-else-if="activeSection === 'assignments'">
          <div class="drawer-card">
            <h3>Assignments</h3>
            <div class="document-table">
              <div class="table-row table-header">
                <span>Course</span>
                <span>Assignment</span>
                <span>Due</span>
                <span>Grade</span>
              </div>
              <div class="table-row" v-for="assignment in student.assignments" :key="assignment.id">
                <span>{{ assignment.course }}</span>
                <span>{{ assignment.title }}</span>
                <span>{{ assignment.submissionDate }}</span>
                <span>{{ assignment.grade }}</span>
              </div>
            </div>
          </div>
        </template>

        <template v-else-if="activeSection === 'certificates'">
          <div class="drawer-card">
            <h3>Certificates</h3>
            <div class="certificate-list">
              <div
                class="certificate-row"
                v-for="certificate in student.certificates"
                :key="certificate.id"
              >
                <div>
                  <strong>{{ certificate.name }}</strong>
                  <small>{{ certificate.institution }}</small>
                </div>
                <span>{{ certificate.issue }}</span>
              </div>
            </div>
          </div>
        </template>

        <template v-else-if="activeSection === 'experience'">
          <div class="drawer-card">
            <h3>Experience</h3>
            <div class="experience-list">
              <div
                class="experience-row"
                v-for="experience in student.experience"
                :key="experience.id"
              >
                <div>
                  <strong>{{ experience.position }}</strong>
                  <small>{{ experience.company }} · {{ experience.location }}</small>
                </div>
                <span>{{ experience.start }} – {{ experience.end }}</span>
              </div>
            </div>
          </div>
        </template>

        <template v-else-if="activeSection === 'achievements'">
          <div class="drawer-card">
            <h3>Achievements</h3>
            <div class="achievement-list">
              <div
                class="achievement-row"
                v-for="achievement in student.achievements"
                :key="achievement.id"
              >
                <div>
                  <strong>{{ achievement.title }}</strong>
                  <p>{{ achievement.description }}</p>
                </div>
                <span>{{ achievement.date }}</span>
              </div>
            </div>
          </div>
        </template>

        <template v-else-if="activeSection === 'documents'">
          <div class="drawer-card">
            <h3>Documents</h3>
            <div class="upload-grid">
              <div class="upload-item" v-for="doc in student.documents" :key="doc.id">
                <div class="upload-label">
                  <strong>{{ doc.type }}</strong>
                  <span>{{ doc.status }}</span>
                </div>
                <div class="progress">
                  <div class="progress-bar" :style="{ width: doc.progress + '%' }"></div>
                </div>
              </div>
            </div>
          </div>
        </template>

        <div class="validation-messages" v-if="currentValidation.length">
          <div class="alert alert-danger p-3">
            <strong>Validation</strong>
            <ul>
              <li v-for="message in currentValidation" :key="message">{{ message }}</li>
            </ul>
          </div>
        </div>
      </div>

      <div class="drawer-actions">
        <button type="button" class="btn btn-outline-secondary" @click="closeDrawer">Cancel</button>
        <button
          type="button"
          class="btn btn-secondary"
          @click="previousSection"
          :disabled="!hasPrevious"
        >
          Previous
        </button>
        <button type="button" class="btn btn-secondary" @click="nextSection" :disabled="!hasNext">
          Next
        </button>
        <button type="button" class="btn btn-primary" @click="saveSection">Save</button>
      </div>

      <div class="drawer-footer">
        <button type="button" class="btn btn-outline-light">Save Draft</button>
        <button type="button" class="btn btn-light text-dark">Publish</button>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'

const activeSection = ref(null)
const autoSaveStatus = ref('All changes saved')
const lastSaved = ref('Just now')

const sections = [
  { key: 'personal', label: 'Personal', title: 'Personal Details', description: 'Update student demographics, contacts, and ID information.', icon: 'bi bi-person-circle' },
  { key: 'academic', label: 'Academic', title: 'Academic Record', description: 'Manage faculty, program, GPA and progress details.', icon: 'bi bi-mortarboard' },
  { key: 'skills', label: 'Skills', title: 'Skills', description: 'Capture technical and soft skills with proficiency levels.', icon: 'bi bi-lightning-charge' },
  { key: 'languages', label: 'Languages', title: 'Languages', description: 'Track language fluency and CEFR levels.', icon: 'bi bi-translate' },
  { key: 'projects', label: 'Projects', title: 'Projects', description: 'Record major student projects and outcomes.', icon: 'bi bi-kanban' },
  { key: 'assignments', label: 'Assignments', title: 'Assignments', description: 'View course submissions, grades and attachments.', icon: 'bi bi-journal-text' },
  { key: 'certificates', label: 'Certificates', title: 'Certificates', description: 'Manage awards, credentials and verification links.', icon: 'bi bi-award' },
  { key: 'experience', label: 'Experience', title: 'Experience', description: 'Track internships and work experience.', icon: 'bi bi-briefcase' },
  { key: 'achievements', label: 'Achievements', title: 'Achievements', description: 'Document honors, awards and recognition.', icon: 'bi bi-trophy' },
  { key: 'documents', label: 'Documents', title: 'Documents', description: 'Upload and manage student records with progress feedback.', icon: 'bi bi-folder2-open' },
]

const student = reactive({
  photo: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=400&q=80',
  fullName: 'Amina Diallo',
  program: 'BSc Computer Science',
  academic: {
    level: 'Undergraduate',
    status: 'On track',
    currentGpa: '3.86',
    creditsEarned: 98,
    faculty: 'Faculty of Science',
    department: 'Software Engineering',
    programme: 'BSc Computer Science',
    semester: 'Spring',
    admissionYear: '2021',
    graduationYear: '2025',
  },
  advisor: 'Dr. Kone',
  personal: {
    firstName: 'Amina',
    lastName: 'Diallo',
    gender: 'Female',
    dob: '2001-08-12',
    nationality: 'Malian',
    studentNumber: 'UNI-4321',
    nationalId: '123456789',
    phone: '+223 70 00 00 01',
    email: 'amina.diallo@university.edu',
    address: 'Banconi, Bamako Mali',
    emergencyContact: 'Mr. Diallo · +223 70 00 00 02',
  },
  skills: [
    { id: 1, name: 'JavaScript', level: 'Advanced' },
    { id: 2, name: 'Vue 3', level: 'Intermediate' },
    { id: 3, name: 'Project Management', level: 'Intermediate' },
  ],
  languages: [
    { id: 1, language: 'French', reading: 'Advanced', writing: 'Advanced', speaking: 'Advanced', listening: 'Advanced', cefr: 'C1' },
    { id: 2, language: 'English', reading: 'Advanced', writing: 'Intermediate', speaking: 'Advanced', listening: 'Advanced', cefr: 'B2' },
  ],
  projects: [
    {
      id: 1,
      title: 'Campus Portal',
      category: 'Web App',
      description: 'Student portal supporting course registration, messaging and internships.',
      technologies: 'Vue 3, Bootstrap 5, Firebase',
      role: 'Frontend Lead',
      teamSize: 4,
      startDate: '2024-01-10',
      endDate: '2024-05-20',
      github: 'https://github.com/meryx/campus-portal',
      demo: 'https://portal.university.edu',
      supervisor: 'Dr. Sidibe',
    },
  ],
  assignments: [
    { id: 1, course: 'Algorithms', title: 'Graph Theory Report', description: 'Analysis of pathfinding techniques.', submissionDate: '2025-04-12', grade: 'A-' },
    { id: 2, course: 'Operating Systems', title: 'Process Scheduler', description: 'Design of a scheduler simulator.', submissionDate: '2025-03-18', grade: 'A' },
  ],
  certificates: [
    { id: 1, name: 'Data Science Fundamentals', institution: 'Coursera', issue: '2024-03-14', expiry: '2026-03-14', credential: 'DSF-2024-077', verify: 'https://verify.example.com/DSF-2024-077' },
  ],
  experience: [
    { id: 1, company: 'TechLabs', position: 'Intern Developer', location: 'Bamako', employmentType: 'Internship', responsibilities: 'Built dashboard modules and API integrations.', start: '2023-06-01', end: '2023-08-30', supervisor: 'Mme. Traore', reference: 'traore@techlabs.com' },
  ],
  achievements: [
    { id: 1, title: 'Dean\'s List', description: 'Top 5% GPA award for Spring semester.', organization: 'Sorbonne University', date: '2024-05-20', evidence: 'Dean List Certificate' },
  ],
  documents: [
    { id: 1, type: 'CV', status: 'Uploaded', progress: 100 },
    { id: 2, type: 'Transcript', status: 'Processing', progress: 72 },
    { id: 3, type: 'Passport', status: 'Uploaded', progress: 100 },
  ],
})

const openDrawer = (section) => {
  activeSection.value = section
}

const closeDrawer = () => {
  activeSection.value = null
}

const sectionIndex = computed(() => sections.findIndex((section) => section.key === activeSection.value))
const currentSection = computed(() => sections[sectionIndex.value] || {})
const isDrawerOpen = computed(() => Boolean(activeSection.value))
const hasPrevious = computed(() => sectionIndex.value > 0)
const hasNext = computed(() => sectionIndex.value < sections.length - 1)

const saveSection = () => {
  autoSaveStatus.value = 'Saving changes...'
  setTimeout(() => {
    autoSaveStatus.value = 'All changes saved'
    lastSaved.value = 'Updated just now'
  }, 800)
}

const previousSection = () => {
  if (hasPrevious.value) {
    activeSection.value = sections[sectionIndex.value - 1].key
  }
}

const nextSection = () => {
  if (hasNext.value) {
    activeSection.value = sections[sectionIndex.value + 1].key
  }
}

const updatePhoto = (event) => {
  const file = event.target.files?.[0]
  if (!file) return
  const reader = new FileReader()
  reader.onload = () => {
    student.photo = reader.result
    saveSection()
  }
  reader.readAsDataURL(file)
}

const addSkill = () => {
  student.skills.push({ id: Date.now(), name: '', level: 'Beginner' })
}

const removeSkill = (index) => {
  student.skills.splice(index, 1)
}

const addLanguage = () => {
  student.languages.push({ id: Date.now(), language: '', reading: 'Basic', writing: 'Basic', speaking: 'Basic', listening: 'Basic', cefr: 'A1' })
}

const removeLanguage = (index) => {
  student.languages.splice(index, 1)
}

const addProject = () => {
  student.projects.push({ id: Date.now(), title: 'New Project', category: 'Research', description: 'Project description', technologies: '', role: 'Contributor', teamSize: 1, startDate: '2025-06-01', endDate: '2025-07-30', github: '', demo: '', supervisor: '' })
}

const currentValidation = computed(() => {
  if (activeSection.value === 'personal') {
    const messages = []
    if (!student.personal.firstName) messages.push('First name is required.')
    if (!student.personal.lastName) messages.push('Last name is required.')
    if (!student.personal.email) messages.push('Email is required.')
    if (!student.personal.phone) messages.push('Phone number is required.')
    return messages
  }
  return []
})
</script>

<style scoped>
.student-profile-page {
  min-height: 100vh;
  background: #f8f9fa;
  color: #0d2b45;
  padding: 24px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  gap: 24px;
  align-items: flex-start;
  margin-bottom: 20px;
}

.page-header h1 {
  margin: 0;
  font-size: clamp(2rem, 2.4vw, 2.6rem);
}

.page-copy {
  margin: 10px 0 0;
  max-width: 620px;
  color: #47505c;
}

.eyebrow {
  margin: 0 0 12px;
  color: #d4a017;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  font-weight: 700;
  font-size: 0.78rem;
}

.header-actions .btn {
  border-radius: 999px;
}

.section-tabs {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 10px;
  margin-bottom: 24px;
}

.nav-link {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  justify-content: center;
  border-radius: 999px;
  padding: 12px 16px;
  border: 1px solid #c8d4dd;
  background: white;
  color: #0d2b45;
  transition:
    background 0.2s ease,
    color 0.2s ease,
    transform 0.2s ease;
}

.nav-link.active,
.nav-link:hover {
  background: #d4a017;
  color: white;
  transform: translateY(-1px);
}

.profile-layout {
  display: grid;
  grid-template-columns: 300px 1fr;
  gap: 24px;
}

.profile-card,
.stat-card,
.info-card,
.drawer-card,
.project-card {
  background: white;
  border: 1px solid #dde3e8;
  border-radius: 24px;
}

.profile-card {
  padding: 24px;
}

.profile-card-top {
  display: flex;
  align-items: center;
  gap: 18px;
  margin-bottom: 22px;
}

.avatar-wrapper {
  width: 82px;
  height: 82px;
  border-radius: 22px;
  overflow: hidden;
  display: grid;
  place-items: center;
  background: #e5f4fb;
}

.avatar {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.profile-card h2 {
  margin: 0;
  font-size: 1.4rem;
}

.profile-card .muted {
  color: #607080;
  margin: 6px 0 0;
}

.profile-details {
  display: grid;
  gap: 14px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  gap: 20px;
  align-items: center;
  color: #47505c;
}

.detail-row strong {
  color: #0d2b45;
}

.dashboard-grid {
  display: grid;
  gap: 20px;
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.stat-card,
.info-card {
  padding: 22px;
}

.stat-label {
  display: block;
  color: #607080;
  margin-bottom: 10px;
}

.stat-card strong {
  font-size: 2rem;
}

.info-card h3 {
  margin-top: 0;
}

.info-grid {
  display: grid;
  gap: 16px;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  margin-top: 18px;
}

.info-grid small,
.info-grid p {
  margin: 0;
  color: #47505c;
}

.drawer-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(4, 33, 44, 0.5);
  backdrop-filter: blur(2px);
  z-index: 30;
}

.drawer {
  position: fixed;
  top: 0;
  right: 0;
  height: 100vh;
  width: 45%;
  max-width: 720px;
  background: #081818;
  color: white;
  transform: translateX(100%);
  transition: transform 0.3s ease;
  z-index: 40;
  display: grid;
  grid-template-rows: auto 1fr auto;
  overflow: hidden;
}

.drawer.open {
  transform: translateX(0);
}

.drawer-header {
  padding: 28px 32px 20px;
  display: flex;
  justify-content: space-between;
  gap: 24px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.drawer-header h2 {
  margin: 0;
  font-size: 1.8rem;
}

.drawer-copy {
  color: #d1dae3;
  margin: 14px 0 0;
  max-width: 500px;
}

.btn-close {
  border: none;
  background: transparent;
  color: white;
  font-size: 1.3rem;
  width: 42px;
  height: 42px;
  border-radius: 50%;
  cursor: pointer;
}

.drawer-status {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 32px 18px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  color: #98a8b7;
}

.autosave {
  color: #d4a017;
  font-weight: 600;
}

.drawer-content {
  padding: 24px 32px 0;
  overflow-y: auto;
  gap: 20px;
  display: grid;
}

.drawer-card,
.project-card {
  background: #0b252f;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 24px;
  padding: 22px;
}

.drawer-card h3,
.project-card strong {
  margin: 0 0 14px;
}

.form-row,
.skill-list,
.language-list,
.document-table,
.certificate-list,
.experience-list,
.achievement-list,
.upload-grid {
  display: grid;
  gap: 18px;
}

.upload-card,
.project-card,
.drawer-card {
  width: 100%;
}

.upload-card {
  display: grid;
  gap: 16px;
  padding: 20px;
  border: 1px dashed rgba(255, 255, 255, 0.18);
  border-radius: 20px;
  text-align: center;
}

.upload-preview {
  width: 100%;
  min-height: 180px;
  border-radius: 20px;
  overflow: hidden;
  background: #061011;
  display: grid;
  place-items: center;
}

.upload-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.upload-button {
  width: fit-content;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
}

.two-column {
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.form-floating {
  position: relative;
}

.form-floating-full {
  grid-column: 1 / -1;
}

.form-floating input,
.form-floating textarea,
.form-floating select {
  width: 100%;
  background: #0b252f;
  border: 1px solid rgba(255, 255, 255, 0.14);
  color: white;
  padding: 18px 14px 10px;
  border-radius: 14px;
}

.form-floating label {
  position: absolute;
  top: 12px;
  left: 14px;
  color: #98a8b7;
  font-size: 0.9rem;
  pointer-events: none;
  transition: all 0.2s ease;
}

.form-floating input:focus + label,
.form-floating input:not(:placeholder-shown) + label,
.form-floating textarea:focus + label,
.form-floating textarea:not(:placeholder-shown) + label,
.form-floating select:focus + label,
.form-floating select:not([value='']) + label {
  transform: translateY(-8px);
  font-size: 0.78rem;
  color: #cbd6e1;
}

.skill-row,
.language-row {
  display: grid;
  grid-template-columns: 1fr 1fr auto;
  gap: 14px;
  align-items: end;
}

.skill-row .btn,
.language-row .btn {
  height: fit-content;
}

.project-card-header {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: start;
  margin-bottom: 12px;
}

.project-card p {
  color: #d1dae3;
}

.project-meta {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  color: #98a8b7;
  font-size: 0.95rem;
}

.document-table {
  display: grid;
  gap: 12px;
}

.table-row {
  display: grid;
  grid-template-columns: 1.6fr 2fr 1fr 1fr;
  gap: 12px;
  padding: 14px 16px;
  border-radius: 16px;
  background: #0b252f;
  align-items: center;
}

.table-header {
  font-weight: 700;
  color: #98a8b7;
}

.certificate-row,
.experience-row,
.achievement-row,
.upload-item {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: center;
  padding: 16px;
  border-radius: 18px;
  background: #0b252f;
}

.certificate-row small,
.experience-row small,
.achievement-row p {
  color: #98a8b7;
  margin: 4px 0 0;
}

.upload-item {
  flex-direction: column;
  align-items: stretch;
}

.upload-label {
  display: flex;
  justify-content: space-between;
  gap: 16px;
}

.progress {
  height: 8px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.12);
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  background: #d4a017;
  transition: width 0.25s ease;
}

.validation-messages {
  padding: 0 32px;
}

.drawer-actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  padding: 18px 32px 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  background: #081818;
}

.drawer-actions .btn {
  border-radius: 999px;
}

.drawer-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px 32px 28px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  background: #081818;
}

.drawer-footer .btn {
  min-width: 140px;
  border-radius: 999px;
}

.alert-danger {
  background: rgba(220, 53, 69, 0.15);
  border-color: rgba(220, 53, 69, 0.28);
  color: #f8d7da;
}

@media (max-width: 1120px) {
  .profile-layout {
    grid-template-columns: 1fr;
  }
  .dashboard-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 900px) {
  .drawer {
    width: 100%;
  }
  .drawer-header,
  .drawer-status,
  .drawer-actions,
  .drawer-footer,
  .drawer-content {
    padding-left: 20px;
    padding-right: 20px;
  }

  .form-grid,
  .skill-row,
  .language-row,
  .info-grid,
  .table-row {
    grid-template-columns: 1fr;
  }
}
</style>
