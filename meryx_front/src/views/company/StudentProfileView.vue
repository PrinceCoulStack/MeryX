<template>
  <div v-if="isLoading" class="empty-state">
    <p>Loading candidate profile...</p>
  </div>

  <div class="profile-page" v-else-if="candidate">
    <section class="profile-cover"></section>

    <section class="profile-card">
      <div class="avatar">
        <img :src="avatarUrl" :alt="candidate.fullName" class="avatar-image" />
      </div>

      <div class="student-info">
        <h1>{{ candidate.fullName }}</h1>
        <h3>{{ candidate.program }} Student</h3>

        <p>
          <i class="bi bi-building"></i>
          {{ candidate.academic.faculty }}
        </p>

        <p>
          <i class="bi bi-geo-alt"></i>
          {{ candidate.academic.department }}
        </p>
      </div>
    </section>

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
        <button class="download">
          <i class="bi bi-download"></i>
          Download CV
        </button>
        <button class="back-btn" @click="goBack">
          <i class="bi bi-arrow-left"></i>
          Back
        </button>
      </div>
    </div>

    <div class="profile-container">
      <div v-if="activeTab === 'Personal'" class="content-section">
        <section class="box">
          <h2>About Me</h2>
          <p>{{ candidate.summary }}</p>
        </section>

        <section class="box match-box">
          <h2>MeryX Opportunity Match</h2>
          <div class="circle">{{ candidate.matchScore }}%</div>
          <p>
            Your profile matches with
            <strong>{{ candidate.matchTags.length }} opportunities</strong>
          </p>
        </section>
      </div>

      <div v-if="activeTab === 'Academic'" class="content-section">
        <section class="box">
          <h2>Academic Information</h2>

          <div class="item">
            <strong>University</strong>
            <span>{{ candidate.academic.faculty }}</span>
          </div>

          <div class="item">
            <strong>Degree</strong>
            <span>{{ candidate.program }}</span>
          </div>

          <div class="item">
            <strong>Level</strong>
            <span>{{ candidate.level }}</span>
          </div>

          <div class="item">
            <strong>Department</strong>
            <span>{{ candidate.academic.department }}</span>
          </div>

          <div class="item">
            <strong>Semester</strong>
            <span>{{ candidate.academic.semester }}</span>
          </div>

          <div class="item">
            <strong>Graduation Year</strong>
            <span>{{ candidate.academic.graduationYear }}</span>
          </div>

          <div class="item">
            <strong>GPA</strong>
            <span class="score">{{ candidate.gpa }}</span>
          </div>

          <div class="item">
            <strong>Attendance</strong>
            <span>{{ candidate.attendance }}%</span>
          </div>
        </section>
      </div>

      <div v-if="activeTab === 'Skills'" class="content-section skills-wrapper">
        <section class="box skill-radar-box">
          <h2>Skill Radar</h2>
          <canvas ref="radarChart" width="300" height="300"></canvas>
        </section>

        <section class="box">
          <h2>Top Skills</h2>
          <div class="skills">
            <span v-for="skill in candidate.skills" :key="skill">{{ skill }}</span>
          </div>
        </section>
      </div>

      <div v-if="activeTab === 'Languages'" class="content-section">
        <section class="box">
          <h2>Languages</h2>
          <div class="language-item" v-for="language in candidate.languages" :key="language">
            <div class="lang-info">
              <strong>{{ language }}</strong>
              <span class="lang-level">Selected by candidate</span>
            </div>
          </div>
        </section>
      </div>

      <div v-if="activeTab === 'Documents'" class="content-section">
        <section class="box">
          <h2>Documents</h2>

          <div class="document-item">
            <i class="bi bi-file-earmark-pdf"></i>
            <div>
              <strong>CV_{{ candidate.fullName }}.pdf</strong>
              <p>Latest resume uploaded by the student</p>
            </div>
            <button>View</button>
          </div>

          <div class="document-item">
            <i class="bi bi-file-earmark-text"></i>
            <div>
              <strong>Transcript.pdf</strong>
              <p>Academic transcript</p>
            </div>
            <button>View</button>
          </div>
        </section>
      </div>
    </div>
  </div>

  <div v-else class="empty-state">
    <p>Candidate not found.</p>
    <button class="back-btn" @click="goBack">Back</button>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Chart from 'chart.js/auto'
import { companyCandidates } from '@/data/companyCandidates'
import { useCandidature } from '@/compasables/useCandidature'

const route = useRoute()
const router = useRouter()
const { candidatures, fetchCandidatures } = useCandidature()
const activeTab = ref('Personal')
const radarChart = ref(null)
let chartInstance = null
const isLoading = ref(false)
const resolvedCandidate = ref(null)

const tabs = ['Personal', 'Academic', 'Skills', 'Languages', 'Documents']

const readNavigationCandidate = () => {
  const fromState = window.history.state?.candidatePayload
  if (!fromState || typeof fromState !== 'object') return null

  const routeId = Number(route.params.id || 0)
  const stateId = Number(fromState.studentId || fromState.id || 0)
  if (!routeId || !stateId || routeId !== stateId) return null
  return fromState
}

const normalizeCandidate = (student) => {
  if (!student || typeof student !== 'object') return null

  if (student.academic && student.matchTags) {
    return {
      ...student,
      academic: {
        faculty: student.academic?.faculty || student.faculty || student.university?.name || '—',
        department: student.academic?.department || student.department || '—',
        semester: student.academic?.semester || '—',
        graduationYear: student.academic?.graduationYear || student.academicYear || '—',
      },
    }
  }

  return {
    id: Number(student.id || 0),
    fullName: student.fullName || student.user?.fullName || 'Student',
    email: student.email || student.user?.email || '—',
    program: student.program || '—',
    level: student.level || '—',
    gpa: student.gpa || '0.00',
    attendance: Number(student.attendance || 0),
    status: student.status || 'active',
    applicationCount: Number(student.applicationCount || 0),
    skills: Array.isArray(student.skills) && student.skills.length ? student.skills : ['No skills'],
    languages:
      Array.isArray(student.languages) && student.languages.length
        ? student.languages.map((item) => (typeof item === 'string' ? item : item?.name || ''))
        : ['Not specified'],
    matchScore: Math.min(100, 40 + Number(student.profileCompletion || 0) * 0.6),
    matchTags: Array.isArray(student.skills) ? student.skills.slice(0, 3) : [],
    summary: student.summary || 'No candidate summary available.',
    academic: {
      faculty: student.faculty || student.university?.name || '—',
      department: student.department || '—',
      semester: '—',
      graduationYear: student.academicYear || student.enrollmentYear || '—',
    },
  }
}

const candidate = computed(() => {
  if (resolvedCandidate.value) return resolvedCandidate.value

  const id = Number(route.params.id)
  const fallback = companyCandidates.find((item) => Number(item.id) === id)
  return normalizeCandidate(fallback)
})

const loadCandidate = async () => {
  const id = Number(route.params.id)
  if (!id) {
    resolvedCandidate.value = null
    return
  }

  isLoading.value = true
  try {
    const fromNavigation = readNavigationCandidate()
    if (fromNavigation) {
      resolvedCandidate.value = normalizeCandidate(fromNavigation)
      return
    }

    await fetchCandidatures()
    const matchedCandidature = candidatures.value.find(
      (item) => Number(item.studentId || item.student?.id || 0) === id,
    )

    if (matchedCandidature?.student) {
      resolvedCandidate.value = normalizeCandidate({
        id,
        ...matchedCandidature.student,
        matchScore: matchedCandidature.matchScore || 70,
        matchTags: [],
      })
      return
    }

    const fallback = companyCandidates.find((item) => Number(item.id) === id)
    resolvedCandidate.value = normalizeCandidate(fallback)
  } finally {
    isLoading.value = false
  }
}

const avatarUrl = computed(() => {
  const id = Number(route.params.id) || 1
  return `https://i.pravatar.cc/240?img=${(id % 60) + 1}`
})

const goBack = () => {
  router.push({ name: 'companyCandidates' })
}

const initializeRadarChart = () => {
  if (!radarChart.value || !candidate.value) return

  if (chartInstance) {
    chartInstance.destroy()
  }

  const skillLabels = candidate.value.skills.length
    ? candidate.value.skills
    : ['Geology', 'Python', 'Data Analysis', 'Communication']

  const chartValues = skillLabels.map((_, index) => Math.max(60, 90 - index * 7))

  chartInstance = new Chart(radarChart.value.getContext('2d'), {
    type: 'radar',
    data: {
      labels: skillLabels,
      datasets: [
        {
          label: 'Skill Strength',
          data: chartValues,
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
    },
  })
}

watch(activeTab, (newTab) => {
  if (newTab === 'Skills') {
    setTimeout(() => {
      initializeRadarChart()
    }, 80)
  }
})

onMounted(() => {
  loadCandidate()
})

watch(
  () => route.params.id,
  () => {
    loadCandidate()
  },
)

watch(candidate, (nextCandidate) => {
  if (!nextCandidate) return
  if (activeTab.value === 'Skills') {
    setTimeout(() => {
      initializeRadarChart()
    }, 80)
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

.profile-cover {
  height: 260px;
  background: linear-gradient(135deg, #0d2b45, #0d2b45);
}

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

.avatar-image {
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
  margin: 0 0 5px;
}

.student-info h3 {
  color: #d4a017;
  margin: 0 0 15px;
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

.tab-container {
  width: 85%;
  margin: 30px auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
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

.download {
  background: #d4a017;
  color: white;
}

.download:hover {
  background: #059aae;
}

.back-btn {
  background: #0d2b45;
  color: white;
}

.back-btn:hover {
  background: #043545;
}

.profile-container {
  width: 85%;
  margin: 20px auto 40px;
}

.content-section {
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

.item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
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

.language-item,
.document-item {
  padding: 15px 0;
  border-bottom: 1px solid #eee;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
}

.language-item:last-child,
.document-item:last-child {
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
  color: #888;
  font-size: 13px;
}

.document-item i {
  font-size: 1.4rem;
  color: #d4a017;
}

.document-item p {
  margin: 4px 0 0;
  color: #888;
}

.document-item button {
  border: none;
  border-radius: 10px;
  background: #0d2b45;
  color: white;
  padding: 10px 14px;
  cursor: pointer;
}

.match-box {
  text-align: center;
}

.circle {
  width: 110px;
  height: 110px;
  margin: 6px auto 14px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  background: conic-gradient(#d4a017 0deg, #d4a017 calc(var(--p) * 3.6deg), #e5f8fb 0deg);
  color: #0d2b45;
  font-size: 1.5rem;
  font-weight: 700;
}

.empty-state {
  background: white;
  border: 1px solid #e6eef2;
  border-radius: 16px;
  padding: 26px;
  display: grid;
  gap: 12px;
  justify-items: start;
}

@media (max-width: 920px) {
  .profile-card,
  .tab-container,
  .profile-container {
    width: 94%;
  }

  .profile-card {
    margin-top: -62px;
    padding: 20px;
  }

  .skills-wrapper {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .profile-cover {
    height: 170px;
  }

  .profile-card {
    flex-direction: column;
    align-items: flex-start;
    gap: 14px;
  }

  .avatar-image {
    width: 102px;
    height: 102px;
  }

  .tab-container {
    flex-direction: column;
    align-items: stretch;
    padding: 14px;
  }

  .profile-actions {
    width: 100%;
    margin-left: 0;
    flex-direction: column;
  }

  .profile-actions button {
    width: 100%;
    justify-content: center;
  }

  .tabs {
    justify-content: flex-start;
  }

  .language-item,
  .item,
  .document-item {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
