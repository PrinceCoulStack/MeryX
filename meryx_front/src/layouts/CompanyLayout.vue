<template>
  <div class="company-layout" :class="theme">
    <aside class="sidebar" :class="{ open: isSidebarOpen }">
      <div>
        <div class="brand">
          <div class="brand-logo">C</div>
          <div>
            <h2>{{ t.brand }}</h2>
            <p>{{ t.portal }}</p>
          </div>
        </div>

        <nav class="nav-links" @click="handleNavClick">
          <RouterLink to="/company" class="nav-item" active-class="active">
            <span><i class="bi bi-house"></i> {{ t.dashboard }}</span>
          </RouterLink>

          <!-- <div class="nav-group">
            <button type="button" class="group-toggle" @click="toggleSection('candidates')">
              <span><i class="bi bi-people"></i> {{ t.candidates }}</span>
              <span class="group-icon" :class="{ open: openSections.candidates }">▾</span>
            </button> -->
          <!-- <div v-show="openSections.candidates" class="">
              <RouterLink to="/company/candidates" class="nav-subitem">{{
                t.candidateList
              }}</RouterLink>
            </div> -->
          <!-- </div> -->

          <!-- Actuality -->
          <RouterLink to="/company/actuality" class="nav-item" active-class="active">
            <span><i class="bi bi-newspaper"></i> {{ t.actuality }}</span>
          </RouterLink>

          <!-- <RouterLink to="/company/actuality/create" class="nav-item" active-class="active">
            <span><i class="bi bi-plus-square"></i> {{ t.createActuality }}</span>
          </RouterLink> -->

          <!-- Candidates -->
          <RouterLink to="/company/candidates" class="nav-item" active-class="active">
            <span><i class="bi bi-people"></i> {{ t.candidates }}</span>
          </RouterLink>

          <!-- <div class="nav-group">
            <button type="button" class="group-toggle" @click="toggleSection('opportunities')">
              <span><i class="bi bi-briefcase"></i> {{ t.opportunities }}</span>
              <span class="group-icon" :class="{ open: openSections.opportunities }">▾</span>
            </button> -->
          <!-- <div v-show="openSections.opportunities" class="">
              <RouterLink to="/company/opportunities" class="nav-subitem">{{
                t.myOpportunities
              }}</RouterLink> -->
          <!-- <RouterLink to="/company/opportunities/create" class="nav-subitem">{{
                t.createOpportunity
              }}</RouterLink> -->
          <!-- </div> -->
          <!-- </div> -->

          <!-- Opportunities -->
          <RouterLink to="/company/opportunities" class="nav-item" active-class="active">
            <span><i class="bi bi-briefcase"></i> {{ t.opportunities }}</span>
          </RouterLink>

          <RouterLink to="/company/training" class="nav-item" active-class="active">
            <span><i class="bi bi-mortarboard"></i> {{ t.training }}</span>
          </RouterLink>

          <!-- <RouterLink to="/company/training/create" class="nav-item" active-class="active">
            <span><i class="bi bi-plus-square"></i> {{ t.createTraining }}</span>
          </RouterLink> -->

          <!-- Message -->
          <RouterLink to="/company/messages" class="nav-item" active-class="active">
            <span><i class="bi bi-chat-dots"></i> {{ t.messages }}</span>
          </RouterLink>

          <RouterLink to="/company/profile" class="nav-item" active-class="active">
            <span><i class="bi bi-building"></i> {{ t.profile }}</span>
          </RouterLink>

          <!-- Setting -->
          <RouterLink to="/company/settings" class="nav-item" active-class="active">
            <span><i class="bi bi-gear"></i> {{ t.settings }}</span>
          </RouterLink>
        </nav>
      </div>
    </aside>

    <div v-if="isMobile && isSidebarOpen" class="sidebar-backdrop" @click="closeSidebar"></div>

    <div class="main">
      <header class="topbar">
        <div class="topbar-left">
          <button v-if="isMobile" class="menu-toggle" type="button" @click="toggleSidebar">
            <i class="bi bi-list"></i>
          </button>
          <div>
            <h1>{{ t.portal }}</h1>
            <p>{{ t.welcome }}</p>
          </div>
        </div>

        <div class="topbar-actions">
          <button type="button" class="topbar-pill topbar-action-btn" @click="toggleLocale">
            {{ localeLabel }}
          </button>
          <button type="button" class="topbar-pill topbar-action-btn" @click="toggleTheme">
            {{ themeLabel }}
          </button>
          <button type="button" class="topbar-pill topbar-action-btn" @click="logout">
            <i class="bi bi-box-arrow-right"></i>
            {{ t.logout }}
          </button>
          <div class="profile-chip">
            <span>AC</span>
            <div>
              <strong>Acme Corp</strong>
              <small>{{ t.companyAccount }}</small>
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
import { computed, onBeforeUnmount, onMounted, provide, ref } from 'vue'
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { useUiPreferences } from '@/compasables/useUiPreferences'
import { useAuthStore } from '@/stores/auth.store'

const { theme, locale, toggleTheme, toggleLocale, initializeUiPreferences } = useUiPreferences()
const router = useRouter()
const auth = useAuthStore()

const isMobile = ref(false)
const isSidebarOpen = ref(false)
// const openSections = ref({
//   candidates: true,
//   opportunities: true,
// })

const translations = {
  en: {
    brand: 'MeryX Company',
    portal: 'Company Portal',
    welcome: 'Manage opportunities, candidates, and company profile from one workspace.',
    dashboard: 'Dashboard',
    actuality: 'Actuality',
    createActuality: 'Create Actuality',
    candidates: 'Candidates',
    candidateList: 'Candidate List',
    opportunities: 'Opportunities',
    myOpportunities: 'My Opportunities',
    createOpportunity: 'Create Opportunity',
    training: 'Training ',
    createTraining: 'Create Training',
    messages: 'Messages',
    settings: 'Settings',
    profile: 'Profile',
    formation: 'Training',
    companyAccount: 'Company account',
    logout: 'Logout',
  },
  fr: {
    brand: 'MeryX Entreprise',
    portal: 'Portail Entreprise',
    welcome: 'Gerez opportunites, candidats et profil entreprise depuis un seul espace.',
    dashboard: 'Tableau de bord',
    actuality: 'Actualité',
    createActuality: 'Creer une actualite',
    candidates: 'Candidats',
    candidateList: 'Liste des candidats',
    opportunities: 'Opportunites',
    myOpportunities: 'Mes opportunites',
    createOpportunity: 'Creer une opportunite',
    trainingList: 'Liste des formations',
    createTraining: 'Creer une formation',
    messages: 'Messages',
    settings: 'Parametres',
    profile: 'Profil',
    formation: 'Formation',
    companyAccount: 'Compte entreprise',
    logout: 'Deconnexion',
  },
}

const t = computed(() => translations[locale.value] || translations.en)
const localeLabel = computed(() => (locale.value === 'en' ? 'FR' : 'EN'))
const themeLabel = computed(() => {
  if (locale.value === 'fr') return theme.value === 'light' ? 'Sombre' : 'Clair'
  return theme.value === 'light' ? 'Dark' : 'Light'
})

const updateViewportState = () => {
  isMobile.value = window.innerWidth <= 1020
  if (!isMobile.value) isSidebarOpen.value = false
}

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value
}

const closeSidebar = () => {
  isSidebarOpen.value = false
}

const handleNavClick = () => {
  if (isMobile.value) closeSidebar()
}

const logout = () => {
  auth.logout()
  router.push('/login')
}

// const toggleSection = (section) => {
//   openSections.value[section] = !openSections.value[section]
// }

onMounted(() => {
  initializeUiPreferences()
  updateViewportState()
  window.addEventListener('resize', updateViewportState)
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', updateViewportState)
})

provide('locale', locale)
provide('theme', theme)
</script>

<style scoped>
.company-layout {
  display: grid;
  grid-template-columns: 280px 1fr;
  min-height: 100vh;
  background: var(--bg);
  /* color: #f2f3f7; */
}

.sidebar {
  position: sticky;
  top: 0;
  height: 100vh;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 22px 18px;
  background: linear-gradient(180deg, #0d2b45 0%, #114a6a 62%, #1e3f66 100%);
  border-right: 1px solid rgba(255, 255, 255, 0.16);
  color: #f2f3f7;
}

.brand {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 18px;
}

.brand-logo {
  width: 48px;
  height: 48px;
  border-radius: 16px;
  display: grid;
  place-items: center;
  background: rgba(13, 43, 69, 0.86);
  color: white;
  font-weight: 700;
  font-size: 1.2rem;
  border: 1px solid rgba(212, 160, 23, 0.45);
}

.brand h2 {
  margin: 0;
  color: white;
  font-size: 1.08rem;
}

.brand p {
  margin: 4px 0 0;
  color: rgba(255, 255, 255, 0.85);
  font-size: 0.88rem;
}

.nav-links {
  display: grid;
  gap: 10px;
}

.nav-item,
.group-toggle,
.nav-subitem {
  width: 100%;
  border-radius: 14px;
  text-decoration: none;
  color: white;
}

.nav-item,
.group-toggle {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
  padding: 12px 14px;
  background: rgba(242, 244, 247, 0.06);
  border: 1px solid rgba(242, 244, 247, 0.16);
}

.nav-item span,
.group-toggle span,
.nav-subitem {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.nav-item.active,
.nav-item:hover,
.group-toggle:hover,
.nav-subitem:hover {
  background: rgba(30, 63, 102, 0.98);
  color: #d4a017;
  box-shadow: inset 0 0 0 1px rgba(212, 160, 23, 0.48);
}

.nav-group {
  display: grid;
  gap: 8px;
}

.group-toggle {
  border: none;
  cursor: pointer;
}

.group-items {
  display: grid;
  gap: 7px;
  padding-left: 10px;
}

.nav-subitem {
  padding: 10px 12px;
  background: rgba(255, 255, 255, 0.07);
}

.group-icon {
  transition: transform 0.2s ease;
}

.group-icon.open {
  transform: rotate(180deg);
}

.sidebar-footer {
  display: grid;
  gap: 10px;
}

.toggle-btn {
  width: 100%;
  padding: 11px 13px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.12);
  color: white;
  cursor: pointer;
  text-align: left;
}

.main {
  display: grid;
  grid-template-rows: auto 1fr;
}

.topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  width: 100%;
  padding: 18px 22px;
  border-bottom: 1px solid var(--border);
  background: rgba(13, 43, 69, 0.72);
  backdrop-filter: blur(8px);
}

.topbar-left {
  display: flex;
  align-items: center;
  gap: 10px;
}

.menu-toggle {
  border: 1px solid var(--border);
  border-radius: 10px;
  width: 38px;
  height: 38px;
  background: var(--surface);
  color: var(--text);
  display: grid;
  place-items: center;
  cursor: pointer;
}

.topbar-left h1 {
  margin: 0;
  color: var(--text);
  font-size: 1.4rem;
}

.topbar-left p {
  margin: 6px 0 0;
  color: var(--muted);
  font-size: 0.92rem;
}

.topbar-actions {
  display: flex;
  gap: 10px;
  align-items: center;
  flex-wrap: wrap;
}

.topbar-pill {
  padding: 8px 12px;
  border-radius: 999px;
  background: rgba(30, 63, 102, 0.76);
  color: var(--text);
  font-size: 0.86rem;
}

.topbar-action-btn {
  border: 1px solid var(--border);
  cursor: pointer;
}

.topbar-action-btn:hover {
  background: rgba(30, 63, 102, 1);
  border-color: rgba(212, 160, 23, 0.56);
}

.profile-chip {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 12px;
  border-radius: 14px;
  background: rgba(17, 74, 106, 0.55);
  border: 1px solid rgba(242, 244, 247, 0.16);
}

.profile-chip span {
  width: 34px;
  height: 34px;
  border-radius: 11px;
  display: grid;
  place-items: center;
  background: #d4a017;
  color: #0d2b45;
  font-weight: 700;
}

.profile-chip strong {
  display: block;
  color: var(--text);
}

.profile-chip small {
  display: block;
  color: var(--muted);
  font-size: 0.82rem;
}

.content {
  padding: 18px 22px;
  background: var(--bg);
  min-height: calc(100vh - 90px);
}

.sidebar-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(2, 15, 24, 0.56);
  z-index: 25;
}

.company-layout.light .topbar {
  background: var(--surface);
  backdrop-filter: none;
}

.company-layout.light .topbar-pill {
  background: rgba(13, 43, 69, 0.08);
  color: #0d2b45 !important;
  border-color: rgba(17, 74, 106, 0.28);
}

.company-layout.light .topbar-action-btn:hover {
  background: rgba(13, 43, 69, 0.14);
  border-color: rgba(17, 74, 106, 0.34);
}

.company-layout.light .profile-chip {
  background: #e8edf3;
  border-color: rgba(17, 74, 106, 0.24);
}

.company-layout.light .profile-chip strong {
  color: #0d2b45;
}

.company-layout.light .profile-chip small {
  color: rgba(13, 43, 69, 0.72);
}

@media (max-width: 1020px) {
  .company-layout {
    grid-template-columns: 1fr;
  }

  .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: min(300px, 85vw);
    transform: translateX(-100%);
    transition: transform 0.25s ease;
    z-index: 30;
  }

  .sidebar.open {
    transform: translateX(0);
  }

  .topbar {
    padding: 14px;
  }

  .content {
    padding: 14px;
  }
}

@media (max-width: 760px) {
  .topbar {
    flex-direction: column;
    align-items: flex-start;
  }

  .topbar-actions {
    width: 100%;
  }

  .topbar-pill,
  .profile-chip {
    width: 100%;
  }
}
</style>
