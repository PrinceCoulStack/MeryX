<template>
  <div class="admin-layout" :class="theme">
    <aside class="admin-sidebar" :class="{ 'sidebar-collapsed': isMobile && !isSidebarOpen }">
      <div class="sidebar-brand">
        <div class="logo" aria-hidden="true">
          <img src="@/assets/logos/meryxbgu.png" width="40" height="50" alt="" />
        </div>
        <div class="brand-copy">
          <p class="brand-title" style="font-weight: 800">
            Mery<span class="brand-x" style="font-weight: 800">X</span>
            <span class="brand-admin">{{ t.brandAdmin }}</span>
          </p>
          <p class="brand-subtitle">{{ t.brandSubtitle }}</p>
        </div>
      </div>

      <nav
        v-show="!isMobile || isSidebarOpen"
        id="admin-sidebar-nav"
        class="sidebar-nav"
        aria-label="Admin navigation"
      >
        <RouterLink to="/admin" class="nav-link">
          <i class="bi bi-grid-1x2"></i>
          <span>{{ t.dashboard }}</span>
        </RouterLink>

        <RouterLink to="/admin/students" class="nav-link">
          <i class="bi bi-mortarboard"></i>
          <span>{{ t.students }}</span>
        </RouterLink>
        <RouterLink to="/admin/universities" class="nav-link">
          <i class="bi bi-building"></i>
          <span>{{ t.universities }}</span>
        </RouterLink>
        <RouterLink to="/admin/companies" class="nav-link">
          <i class="bi bi-briefcase"></i>
          <span>{{ t.companies }}</span>
        </RouterLink>
        <RouterLink to="/admin/reports" class="nav-link">
          <i class="bi bi-bar-chart"></i>
          <span>{{ t.reports }}</span>
        </RouterLink>

        <!-- Configuration contain(role management, settings, integrations, audit logs, sub-admin-accounts)-->
        <div class="nav-group">
          <button
            type="button"
            class="nav-group-toggle"
            :aria-expanded="String(isConfigOpen)"
            aria-controls="admin-config-links"
            @click="toggleConfig"
          >
            <span class="nav-group-title">{{ t.configuration }}</span>
            <i class="bi" :class="isConfigOpen ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
          </button>

          <div v-show="isConfigOpen" id="admin-config-links" class="nav-group-links">
            <RouterLink to="/admin/settings" class="nav-link nav-link-sub">
              <i class="bi bi-gear"></i>
              <span>{{ t.settings }}</span>
            </RouterLink>
            <RouterLink to="/admin/roles" class="nav-link nav-link-sub">
              <i class="bi bi-shield-lock"></i>
              <span>{{ t.rolesPermissions }}</span>
            </RouterLink>
            <RouterLink to="/admin/integrations" class="nav-link nav-link-sub">
              <i class="bi bi-plug"></i>
              <span>{{ t.integrations }}</span>
            </RouterLink>
            <RouterLink to="/admin/audit-logs" class="nav-link nav-link-sub">
              <i class="bi bi-journal-text"></i>
              <span>{{ t.auditLogs }}</span>
            </RouterLink>
            <RouterLink to="/admin/sub-admins" class="nav-link nav-link-sub">
              <i class="bi bi-people"></i>
              <span>{{ t.subAdminAccounts }}</span>
            </RouterLink>
          </div>
          <!-- Profil -->
          <RouterLink to="/admin/profile" class="nav-link nav-link-sub">
            <i class="bi bi-person-circle"></i>
            <span>{{ t.profile }}</span>
          </RouterLink>
        </div>
      </nav>

      <div v-show="!isMobile || isSidebarOpen" class="sidebar-footer">
        <p class="footer-label">{{ t.systemStatus }}</p>
        <p class="footer-value">
          <span class="status-dot"></span>
          {{ t.servicesHealthy }}
        </p>
      </div>
    </aside>

    <div class="admin-main">
      <header class="admin-topbar">
        <div>
          <p class="topbar-title">{{ t.adminPanel }}</p>
          <p class="topbar-subtitle">{{ t.topbarSubtitle }}</p>
        </div>

        <div class="topbar-actions">
          <button type="button" class="topbar-pill topbar-action-btn" @click="toggleLocale">
            <i class="bi bi-translate"></i>
            <span>{{ localeLabel }}</span>
          </button>

          <button type="button" class="topbar-pill topbar-action-btn" @click="toggleTheme">
            <i class="bi" :class="isDarkMode ? 'bi-brightness-high' : 'bi-moon-stars'"></i>
            <span>{{ themeLabel }}</span>
          </button>

          <button
            v-if="isMobile"
            type="button"
            class="menu-toggle"
            :aria-expanded="String(isSidebarOpen)"
            aria-controls="admin-sidebar-nav"
            @click="toggleSidebar"
          >
            <i class="bi" :class="isSidebarOpen ? 'bi-x-lg' : 'bi-list'"></i>
            <span>{{ isSidebarOpen ? t.closeMenu : t.openMenu }}</span>
          </button>

          <RouterLink to="/admin/reports" class="reports-link">
            <i class="bi bi-file-earmark-bar-graph"></i>
            <span>{{ t.openReports }}</span>
          </RouterLink>

          <button type="button" class="topbar-pill topbar-action-btn" @click="logout">
            <i class="bi bi-box-arrow-right"></i>
            <span>{{ t.logout }}</span>
          </button>
        </div>
      </header>

      <section class="admin-content">
        <RouterView />
      </section>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useUiPreferences } from '@/compasables/useUiPreferences'
import { RouterView, RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.store'

const MOBILE_BREAKPOINT = 1024
const router = useRouter()
const auth = useAuthStore()

const { locale, theme, isDarkMode, toggleTheme, toggleLocale, initializeUiPreferences } =
  useUiPreferences()

const isMobile = ref(false)
const isSidebarOpen = ref(true)
const isConfigOpen = ref(true)

const translations = {
  en: {
    brandAdmin: 'Admin',
    brandSubtitle: 'Operations Console',
    dashboard: 'Dashboard',
    students: 'Students',
    universities: 'Universities',
    companies: 'Companies',
    reports: 'Reports',
    configuration: 'Configuration',
    settings: 'Settings',
    rolesPermissions: 'Roles & Permissions',
    integrations: 'Integrations',
    auditLogs: 'Audit Logs',
    subAdminAccounts: 'Sub-Admin Accounts',
    profile: 'Profile',
    systemStatus: 'System status',
    servicesHealthy: 'All services healthy',
    adminPanel: 'Admin Panel',
    topbarSubtitle: 'Monitor users, institutions, and platform activity',
    openReports: 'Open Reports',
    openMenu: 'Open Menu',
    closeMenu: 'Close Menu',
    logout: 'Logout',
  },
  fr: {
    brandAdmin: 'Admin',
    brandSubtitle: "Console d'operations",
    dashboard: 'Tableau de bord',
    students: 'Etudiants',
    universities: 'Universites',
    companies: 'Entreprises',
    reports: 'Rapports',
    configuration: 'Configuration',
    settings: 'Parametres',
    rolesPermissions: 'Roles et permissions',
    integrations: 'Integrations',
    auditLogs: "Journaux d'audit",
    subAdminAccounts: 'Comptes sous-admin',
    profile: 'Profil',
    systemStatus: 'Etat du systeme',
    servicesHealthy: 'Tous les services sont operationnels',
    adminPanel: 'Panneau Admin',
    topbarSubtitle: 'Surveillez utilisateurs, institutions et activite de la plateforme',
    openReports: 'Ouvrir Rapports',
    openMenu: 'Ouvrir Menu',
    closeMenu: 'Fermer Menu',
    logout: 'Deconnexion',
  },
}

const t = computed(() => translations[locale.value] || translations.en)
const localeLabel = computed(() => (locale.value === 'en' ? 'EN / FR' : 'FR / EN'))
const themeLabel = computed(() => {
  if (locale.value === 'fr') return isDarkMode.value ? 'Mode Clair' : 'Mode Sombre'
  return isDarkMode.value ? 'Light Mode' : 'Dark Mode'
})

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value
}

const toggleConfig = () => {
  isConfigOpen.value = !isConfigOpen.value
}

const updateViewport = () => {
  const mobile = window.innerWidth <= MOBILE_BREAKPOINT
  if (mobile !== isMobile.value) {
    isMobile.value = mobile
    isSidebarOpen.value = !mobile
    if (!mobile) {
      isConfigOpen.value = true
    }
  }
}

const logout = () => {
  auth.logout()
  router.push('/login')
}

onMounted(() => {
  initializeUiPreferences()
  updateViewport()
  window.addEventListener('resize', updateViewport)
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', updateViewport)
})
</script>

<style scoped>
.admin-layout {
  display: flex;
  min-height: 100vh;
  background: var(--bg);
}

.admin-sidebar {
  width: 280px;
  background:
    radial-gradient(circle at 15% 5%, rgba(212, 160, 23, 0.2), transparent 45%),
    linear-gradient(180deg, #0d2b45 0%, #114a6a 68%, #1e3f66 100%);
  color: #f2f4f7;
  padding: 26px 18px;
  display: flex;
  flex-direction: column;
  gap: 26px;
  border-right: 1px solid rgba(242, 244, 247, 0.2);
}

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  border-radius: 16px;
  background: rgba(13, 43, 69, 0.45);
  border: 1px solid rgba(242, 244, 247, 0.2);
}

.logo {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: grid;
  place-items: center;
  background: rgba(242, 244, 247, 0.08);
  border: 1px solid rgba(242, 244, 247, 0.2);
  overflow: hidden;
}

.logo img {
  width: 40px;
  height: 50px;
  object-fit: contain;
}

.brand-copy {
  min-width: 0;
}

.brand-mark {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: grid;
  place-items: center;
  font-weight: 800;
  letter-spacing: 0.08em;
  background: linear-gradient(145deg, #d4a017, #f0c656);
  color: #0d2b45;
}

.brand-title,
.brand-subtitle,
.topbar-title,
.topbar-subtitle,
.footer-label,
.footer-value {
  margin: 0;
}

.brand-title {
  color: #f2f4f7;
  font-size: 1rem;
  font-weight: 700;
  letter-spacing: 0.01em;
}

.brand-x {
  color: var(--primary);
}

.brand-admin {
  font-weight: 600;
  margin-left: 2px;
}

.brand-subtitle {
  margin-top: 2px;
  color: rgba(242, 244, 247, 0.78);
  font-size: 0.8rem;
}

.sidebar-nav {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.nav-group {
  border-top: 1px solid rgba(242, 244, 247, 0.2);
  padding-top: 12px;
  margin-top: 4px;
}

.nav-group-toggle {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border: 0;
  border-radius: 10px;
  padding: 8px 6px;
  background: transparent;
  color: rgba(242, 244, 247, 0.82);
  font: inherit;
  cursor: pointer;
}

.nav-group-toggle:hover {
  color: #f4cb62;
  background: rgba(242, 244, 247, 0.08);
}

.nav-group-title {
  margin: 0;
  font-size: 0.82rem;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
}

.nav-group-links {
  display: grid;
  gap: 6px;
  margin-top: 4px;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #f2f4f7;
  text-decoration: none;
  border-radius: 12px;
  padding: 11px 12px;
  border: 1px solid transparent;
  transition:
    background-color 0.22s ease,
    border-color 0.22s ease,
    transform 0.22s ease,
    color 0.22s ease;
}

.nav-link i {
  font-size: 1rem;
}

.nav-link-sub {
  padding-left: 18px;
}

.nav-link:hover,
.nav-link.router-link-active {
  background: rgba(242, 244, 247, 0.12);
  border-color: rgba(212, 160, 23, 0.55);
  color: #f4cb62;
  transform: translateX(2px);
}

.sidebar-footer {
  margin-top: auto;
  padding: 12px;
  border-radius: 14px;
  background: rgba(13, 43, 69, 0.42);
  border: 1px solid rgba(242, 244, 247, 0.2);
}

.footer-label {
  font-size: 0.78rem;
  color: rgba(242, 244, 247, 0.76);
}

.footer-value {
  margin-top: 8px;
  display: flex;
  align-items: center;
  gap: 8px;
  color: #f2f4f7;
  font-weight: 600;
  font-size: 0.86rem;
}

.status-dot {
  width: 9px;
  height: 9px;
  border-radius: 999px;
  background: #5de5a8;
  box-shadow: 0 0 0 5px rgba(93, 229, 168, 0.2);
}

.admin-main {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.admin-topbar {
  min-height: 76px;
  background:
    radial-gradient(circle at 95% 0%, rgba(212, 160, 23, 0.12), transparent 40%),
    linear-gradient(155deg, rgba(17, 74, 106, 0.11), rgba(30, 63, 102, 0.02));
  border-bottom: 1px solid var(--border);
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  padding: 12px 24px;
}

.topbar-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

.topbar-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
  min-height: 40px;
  padding: 9px 12px;
  border-radius: 12px;
  font-weight: 700;
  font-size: 0.85rem;
  border: 1px solid rgba(17, 74, 106, 0.35);
  background: rgba(17, 74, 106, 0.1);
  color: #0d2b45;
}

.topbar-pill:hover {
  background: rgba(212, 160, 23, 0.22);
  border-color: rgba(212, 160, 23, 0.64);
  color: #0d2b45;
}

.menu-toggle {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 9px 12px;
  border-radius: 12px;
  border: 1px solid rgba(17, 74, 106, 0.32);
  background: rgba(17, 74, 106, 0.1);
  color: var(--text);
  font-weight: 600;
}

.menu-toggle:hover {
  border-color: rgba(212, 160, 23, 0.58);
  background: rgba(212, 160, 23, 0.16);
}

.topbar-title {
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--heading);
}

.topbar-subtitle {
  margin-top: 3px;
  font-size: 0.86rem;
  color: var(--muted);
}

.reports-link {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 9px 14px;
  border-radius: 12px;
  border: 1px solid rgba(17, 74, 106, 0.32);
  background: rgba(17, 74, 106, 0.08);
  color: var(--text);
  font-weight: 600;
}

.reports-link:hover {
  border-color: rgba(212, 160, 23, 0.58);
  background: rgba(212, 160, 23, 0.16);
  color: #0d2b45;
}

:global(body.theme-dark) .admin-topbar {
  background:
    radial-gradient(circle at 95% 0%, rgba(212, 160, 23, 0.16), transparent 45%),
    linear-gradient(155deg, rgba(13, 43, 69, 0.9), rgba(17, 74, 106, 0.7));
  border-bottom-color: rgba(242, 244, 247, 0.16);
}

:global(body.theme-dark) .topbar-title {
  color: #f2f4f7;
}

:global(body.theme-dark) .topbar-subtitle {
  color: rgba(242, 244, 247, 0.82);
}

:global(body.theme-dark) .topbar-pill,
:global(body.theme-dark) .menu-toggle,
:global(body.theme-dark) .reports-link {
  border-color: rgba(242, 244, 247, 0.3);
  background: rgba(242, 244, 247, 0.08);
  color: #f2f4f7;
}

:global(body.theme-dark) .topbar-pill:hover,
:global(body.theme-dark) .menu-toggle:hover,
:global(body.theme-dark) .reports-link:hover {
  border-color: rgba(212, 160, 23, 0.72);
  background: rgba(212, 160, 23, 0.22);
  color: #f2f4f7;
}

.admin-content {
  padding: 22px;
  background: var(--bg);
  flex: 1;
}

.admin-layout.light .admin-sidebar {
  background:
    radial-gradient(circle at 10% 0%, rgba(212, 160, 23, 0.18), transparent 42%),
    linear-gradient(180deg, #e9eef5 0%, #dde7f1 60%, #d4e1ed 100%);
  color: #0d2b45;
  border-right-color: rgba(13, 43, 69, 0.14);
}

.admin-layout.light .sidebar-brand {
  background: rgba(255, 255, 255, 0.72);
  border-color: rgba(13, 43, 69, 0.15);
}

.admin-layout.light .logo {
  background: rgba(255, 255, 255, 0.86);
  border-color: rgba(13, 43, 69, 0.14);
}

.admin-layout.light .brand-title {
  color: #0d2b45;
}

.admin-layout.light .brand-subtitle,
.admin-layout.light .footer-label,
.admin-layout.light .nav-group-toggle {
  color: rgba(13, 43, 69, 0.72);
}

.admin-layout.light .nav-group {
  border-top-color: rgba(13, 43, 69, 0.16);
}

.admin-layout.light .nav-link {
  color: #0d2b45;
}

.admin-layout.light .nav-link:hover,
.admin-layout.light .nav-link.router-link-active {
  background: rgba(13, 43, 69, 0.1);
  border-color: rgba(13, 43, 69, 0.24);
  color: #0d2b45;
}

.admin-layout.light .sidebar-footer {
  background: rgba(255, 255, 255, 0.78);
  border-color: rgba(13, 43, 69, 0.16);
}

.admin-layout.light .footer-value {
  color: #0d2b45;
}

@media (max-width: 1024px) {
  .admin-layout {
    flex-direction: column;
  }

  .admin-sidebar {
    width: 100%;
    border-right: 0;
    border-bottom: 1px solid rgba(242, 244, 247, 0.22);
    padding: 16px;
    gap: 16px;
  }

  .admin-sidebar.sidebar-collapsed {
    max-height: 106px;
    overflow: hidden;
  }

  .sidebar-nav {
    flex-direction: column;
    overflow: visible;
    padding-bottom: 0;
  }

  .nav-link {
    white-space: normal;
  }

  .sidebar-footer {
    margin-top: 0;
  }
}

@media (max-width: 720px) {
  .admin-topbar {
    padding: 12px 16px;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
  }

  .admin-topbar > div {
    width: 100%;
  }

  .topbar-actions {
    width: 100%;
    flex-wrap: wrap;
  }

  .topbar-pill,
  .menu-toggle,
  .reports-link {
    flex: 1 1 150px;
    justify-content: center;
  }

  .admin-content {
    padding: 16px;
  }
}
</style>
