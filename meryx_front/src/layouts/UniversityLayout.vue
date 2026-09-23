<template>
  <div class="university-layout" :class="theme">
    <aside class="sidebar" :class="{ open: isSidebarOpen }">
      <div class="brand">
        <div class="logo">
          <img
            :src="connectedUniversityLogo"
            width="40"
            height="50"
            :alt="connectedUniversityName"
          />
        </div>
        <div>
          <span style="font-weight: 800; color: #f2f3f7"
            >Mery<span style="color: var(--primary); font-weight: 800">X</span></span
          >
          <p>{{ connectedUniversityName }}</p>
        </div>
      </div>

      <nav class="nav-links" @click="handleNavClick">
        <RouterLink to="/university" class="nav-item">
          <i class="bi bi-house"></i>
          {{ labels.dashboard }}
        </RouterLink>

        <div class="nav-group">
          <button type="button" class="group-toggle" @click="toggleSection('students')">
            <span>
              <i class="bi bi-people"></i>
              {{ labels.students }}
            </span>
            <span class="group-icon" :class="{ open: openSections.students }">▾</span>
          </button>
          <div v-show="openSections.students" class="group-items">
            <RouterLink to="/university/listStudents" class="nav-subitem">{{
              labels.allStudents
            }}</RouterLink>
            <RouterLink to="/university/studentRequests" class="nav-subitem">{{
              labels.studentRequests
            }}</RouterLink>
            <RouterLink to="/university/academicRecords" class="nav-subitem">{{
              labels.academicRecords
            }}</RouterLink>
            <!-- <RouterLink to="/university/attendance" class="nav-subitem">Attendance</RouterLink>
            <RouterLink to="/university/projects" class="nav-subitem">Projects</RouterLink>
            <RouterLink to="/university/skills" class="nav-subitem">Skills</RouterLink> -->
          </div>
        </div>

        <RouterLink to="/university/partnerCompanies" class="nav-item">
          <i class="bi bi-building"></i>
          {{ labels.companies }}
        </RouterLink>

        <!-- Opportunity -->
        <RouterLink to="/university/opportunities" class="nav-item">
          <i class="bi bi-briefcase"></i>
          {{ labels.opportunities }}
        </RouterLink>

        <!-- <div class="nav-group">
          <button type="button" class="group-toggle" @click="toggleSection('opportunities')">
            <span>
              <i class="bi bi-briefcase"></i>
              {{ labels.opportunities }}
            </span>
            <span class="group-icon" :class="{ open: openSections.opportunities }">▾</span>
          </button>
          <div v-show="openSections.opportunities" class="group-items">
            <RouterLink to="/university/opportunities" class="nav-subitem">{{
              labels.opportunitiesManagement
            }}</RouterLink>
          </div>
        </div> -->

        <!-- <RouterLink to="/university/formation" class="nav-item">
          <i class="bi bi-mortarboard"></i>
          Formation
        </RouterLink> -->

        <!-- <div class="nav-group">
          <button type="button" class="group-toggle" @click="toggleSection('career')">
            <span>
              <i class="bi bi-briefcase"></i>
              Career Center
            </span>
            <span class="group-icon" :class="{ open: openSections.career }">▾</span>
          </button>
          <div v-show="openSections.career" class="group-items">
            <RouterLink to="/university/career/recommendations" class="nav-subitem"
              >Recommendations</RouterLink
            >
            <RouterLink to="/university/career/alumni-tracking" class="nav-subitem"
              >Alumni Tracking</RouterLink
            >
          </div>
        </div> -->

        <!-- <RouterLink to="/university/events" class="nav-item">Events</RouterLink> -->
        <RouterLink to="/university/messageCompany" class="nav-item">
          <i class="bi bi-envelope"></i>
          {{ labels.messages }}
        </RouterLink>
        <!-- <RouterLink to="/university/documents" class="nav-item">Documents</RouterLink> -->
        <RouterLink to="/university/reports" class="nav-item">
          <i class="bi bi-bar-chart"></i>
          {{ labels.reports }}
        </RouterLink>
        <!-- <RouterLink to="/university/analytics" class="nav-item">Analytics</RouterLink> -->
        <!-- <RouterLink to="/university/notifications" class="nav-item">Notifications</RouterLink> -->
        <RouterLink to="/university/settings" class="nav-item">
          <i class="bi bi-gear"></i>
          {{ labels.settings }}
        </RouterLink>
      </nav>
    </aside>

    <div v-if="isMobile && isSidebarOpen" class="sidebar-backdrop" @click="closeSidebar"></div>

    <div class="main">
      <header class="topbar">
        <button v-if="isMobile" class="menu-toggle" type="button" @click="toggleSidebar">
          <i class="bi bi-list"></i>
        </button>

        <div class="top-left">
          <h2>{{ labels.topTitle }}</h2>
          <p>{{ connectedTopSubtitle }}</p>
        </div>

        <div class="top-actions">
          <button class="action-button" @click="toggleLocale">
            {{ labels.languageToggle }}
          </button>
          <button class="action-button" @click="toggleTheme">
            {{ themeToggleLabel }}
          </button>
          <button class="action-button" type="button" @click="logout">
            <i class="bi bi-box-arrow-right"></i>
            {{ labels.logout }}
          </button>
          <div class="user-badge">
            <span>{{ connectedUniversityInitials }}</span>
            <div>
              <strong>{{ labels.role }}</strong>
              <small>{{ connectedUniversityName }}</small>
            </div>
          </div>
        </div>
      </header>

      <section class="content">
        <RouterView />
      </section>
    </div>
  </div>
</template>

<script setup>
import { RouterView, RouterLink, useRouter } from 'vue-router'
import { computed, ref, provide, onMounted, onBeforeUnmount } from 'vue'
import { useUiPreferences } from '@/compasables/useUiPreferences'
import { useAuthStore } from '@/stores/auth.store'

const { theme, locale, toggleTheme, toggleLocale, initializeUiPreferences } = useUiPreferences()
const router = useRouter()
const auth = useAuthStore()
const defaultUniversityLogo = new URL('../assets/logos/meryxbgu.png', import.meta.url).href
const openSections = ref({
  students: true,
  companies: false,
  opportunities: false,
  career: false,
})
const isMobile = ref(false)
const isSidebarOpen = ref(false)

const updateViewportState = () => {
  isMobile.value = window.innerWidth <= 1000
  if (!isMobile.value) {
    isSidebarOpen.value = false
  }
}

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value
}

const closeSidebar = () => {
  isSidebarOpen.value = false
}

const handleNavClick = () => {
  if (isMobile.value) {
    closeSidebar()
  }
}

const toggleSection = (section) => {
  openSections.value[section] = !openSections.value[section]
}

const logout = () => {
  auth.logout()
  router.push('/login')
}

const normalizeText = (value) => {
  const text = String(value || '').trim()
  return text || ''
}

const connectedUniversityName = computed(() => {
  const user = auth.user || {}
  const universityNode = user.university || user.universityProfile || user.universityId || {}

  return (
    normalizeText(universityNode.name) ||
    normalizeText(user.universityName) ||
    normalizeText(user.organizationName) ||
    labels.value.universityName
  )
})

const connectedUniversityLogo = computed(() => {
  const user = auth.user || {}
  const universityNode = user.university || user.universityProfile || user.universityId || {}

  return (
    normalizeText(universityNode.logoUrl) ||
    normalizeText(universityNode.logo) ||
    normalizeText(user.logoUrl) ||
    defaultUniversityLogo
  )
})

const connectedUniversityInitials = computed(() => {
  const parts = connectedUniversityName.value.split(/\s+/).filter(Boolean)
  if (!parts.length) return 'U'
  if (parts.length === 1) return parts[0].slice(0, 1).toUpperCase()
  return `${parts[0][0]}${parts[1][0]}`.toUpperCase()
})

const connectedTopSubtitle = computed(() => {
  if (locale.value === 'fr') {
    return `${labels.value.topSubtitle} ${connectedUniversityName.value}`
  }
  return `${labels.value.topSubtitle} ${connectedUniversityName.value}`
})

const dictionary = {
  en: {
    dashboard: 'Dashboard',
    students: 'Students',
    allStudents: 'All Students',
    studentRequests: 'Student Requests',
    academicRecords: 'Academic Records',
    companies: 'Companies',
    opportunities: 'Opportunities',
    opportunitiesManagement: 'Opportunities Management',
    messages: 'Messages',
    reports: 'Reports',
    settings: 'Settings',
    topTitle: 'University Dashboard',
    topSubtitle: 'Overview of student activity, partnerships, and academic performance for',
    role: 'Admin',
    universityName: 'University account',
    themeToggle: 'Dark',
    languageToggle: 'FR',
    logout: 'Logout',
  },
  fr: {
    dashboard: 'Tableau de bord',
    students: 'Etudiants',
    allStudents: 'Tous les etudiants',
    studentRequests: 'Demandes etudiantes',
    academicRecords: 'Dossiers academiques',
    companies: 'Entreprises',
    opportunities: 'Opportunites',
    opportunitiesManagement: 'Gestion des opportunites',
    messages: 'Messages',
    reports: 'Rapports',
    settings: 'Parametres',
    topTitle: 'Tableau de bord universite',
    topSubtitle: "Vue d'ensemble des etudiants, partenariats et performances academiques pour",
    role: 'Admin',
    universityName: 'Compte universite',
    themeToggle: 'Clair',
    languageToggle: 'EN',
    logout: 'Deconnexion',
  },
}

const labels = computed(() => dictionary[locale.value] || dictionary.en)
const themeToggleLabel = computed(() => {
  if (locale.value === 'fr') {
    return theme.value === 'light' ? 'Sombre' : 'Clair'
  }
  return theme.value === 'light' ? 'Dark' : 'Light'
})

onMounted(() => {
  initializeUiPreferences()
  updateViewportState()
  window.addEventListener('resize', updateViewportState)
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', updateViewportState)
})

provide('locale', locale)
</script>

<style scoped>
.university-layout {
  display: flex;
  width: 100%;
  min-height: 100vh;
  background: var(--bg);
}

.sidebar {
  width: 280px;
  background: linear-gradient(180deg, #0d2b45 0%, #114a6a 62%, #1e3f66 100%);
  color: white;
  padding: 28px 22px;
  display: flex;
  flex-direction: column;
  gap: 36px;
  border-right: 1px solid rgba(242, 244, 247, 0.16);
}

.brand {
  display: flex;
  align-items: center;
  gap: 14px;
}

.logo {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  background: rgba(13, 43, 69, 0.88);
  display: grid;
  place-items: center;
  font-weight: 700;
  font-size: 1.25rem;
  box-shadow:
    inset 0 0 0 1px rgba(212, 160, 23, 0.44),
    0 10px 22px rgba(2, 16, 28, 0.28);
}

.brand h1 {
  margin: 0;
  font-size: 1.35rem;
}

.brand p {
  margin: 4px 0 0;
  color: rgba(255, 255, 255, 0.82);
  font-size: 0.95rem;
}

.nav-links {
  display: grid;
  gap: 10px;
}

.nav-item,
.nav-subitem,
.group-toggle {
  display: block;
  width: 100%;
  color: white;
  text-decoration: none;
  transition:
    background 0.2s ease,
    padding 0.2s ease;
}

.nav-item {
  padding: 14px 16px;
  border-radius: 16px;
  background: rgba(242, 244, 247, 0.06);
}

.nav-group {
  display: grid;
  gap: 8px;
  padding: 12px 0 0;
  border-top: 1px solid rgba(242, 244, 247, 0.14);
}

.group-toggle {
  padding: 12px 16px;
  border: none;
  border-radius: 16px;
  background: rgba(242, 244, 247, 0.06);
  color: white;
  text-align: left;
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
}

.group-toggle:hover {
  background: rgba(30, 63, 102, 0.92);
}

.group-items {
  display: grid;
  gap: 8px;
  padding-left: 12px;
}

.group-icon {
  transition: transform 0.2s ease;
}

.group-icon.open {
  transform: rotate(180deg);
}

.nav-subitem {
  padding: 10px 18px;
  border-radius: 14px;
  color: rgba(255, 255, 255, 0.92);
  background: rgba(242, 244, 247, 0.05);
}

.nav-item.router-link-active,
.nav-subitem.router-link-active,
.nav-item:hover,
.nav-subitem:hover,
.group-toggle:hover {
  background: rgba(30, 63, 102, 0.98);
  color: #d4a017;
  box-shadow: inset 0 0 0 1px rgba(212, 160, 23, 0.5);
}

.main {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px 28px;
  gap: 24px;
  background: rgba(13, 43, 69, 0.7);
  border-bottom: 1px solid var(--border);
  backdrop-filter: blur(8px);
}

.menu-toggle {
  display: none;
  border: 1px solid var(--border);
  border-radius: 12px;
  background: var(--surface);
  color: var(--text);
  width: 42px;
  height: 42px;
  cursor: pointer;
}

.menu-toggle i {
  font-size: 1.4rem;
}

.top-left h2 {
  margin: 0;
  font-size: 1.5rem;
  color: var(--text);
}

.top-left p {
  margin: 8px 0 0;
  color: var(--muted);
}

.top-actions {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}

.action-button {
  border: 1px solid var(--border);
  border-radius: 999px;
  background: rgba(30, 63, 102, 0.72);
  color: var(--text);
  padding: 10px 16px;
  cursor: pointer;
  transition:
    background 0.2s ease,
    color 0.2s ease;
}

.action-button:hover {
  background: rgba(30, 63, 102, 1);
  border-color: rgba(212, 160, 23, 0.56);
}

.user-badge {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 14px;
  border-radius: 16px;
  background: rgba(17, 74, 106, 0.5);
  border: 1px solid rgba(242, 244, 247, 0.16);
}

.user-badge span {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background: #d4a017;
  display: grid;
  place-items: center;
  color: #0d2b45;
  font-weight: 700;
}

.user-badge strong {
  display: block;
  color: var(--text);
}

.user-badge small {
  display: block;
  color: var(--muted);
}

.content {
  flex: 1;
  padding: 28px;
}

.university-layout.light .topbar {
  background: var(--surface);
  backdrop-filter: none;
}

.university-layout.light .action-button {
  background: rgba(13, 43, 69, 0.08);
  color: #0d2b45;
  border-color: rgba(17, 74, 106, 0.28);
}

.university-layout.light .action-button:hover {
  background: rgba(13, 43, 69, 0.14);
  border-color: rgba(17, 74, 106, 0.34);
}

.university-layout.light .user-badge {
  background: #e8edf3;
  border-color: rgba(17, 74, 106, 0.24);
}

.university-layout.light .user-badge strong {
  color: #0d2b45;
}

.university-layout.light .user-badge small {
  color: rgba(13, 43, 69, 0.72);
}

@media (max-width: 1000px) {
  .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    width: min(84vw, 320px);
    z-index: 1001;
    transform: translateX(-100%);
    transition: transform 0.25s ease;
    overflow-y: auto;
    padding-bottom: 24px;
  }

  .sidebar.open {
    transform: translateX(0);
  }

  .sidebar-backdrop {
    position: fixed;
    inset: 0;
    z-index: 1000;
    background: rgba(2, 15, 24, 0.56);
  }

  .nav-links {
    display: grid;
    flex-direction: initial;
    flex-wrap: initial;
  }

  .main {
    width: 100%;
  }

  .menu-toggle {
    display: inline-grid;
    place-items: center;
  }

  .topbar {
    align-items: flex-start;
    padding: 16px 20px;
  }

  .top-actions {
    justify-content: flex-start;
  }

  .content {
    padding: 18px;
  }
}

@media (max-width: 680px) {
  .top-left h2 {
    font-size: 1.2rem;
  }

  .top-left p {
    font-size: 0.9rem;
  }

  .top-actions {
    width: 100%;
    gap: 10px;
  }

  .action-button {
    padding: 8px 12px;
  }

  .user-badge {
    width: 100%;
  }
}
</style>
