<template>
  <header class="navbar">
    <div class="left">
      <div class="logo" @click="$router.push({ name: 'dashboard' })">
        <span class="x"
          ><img src="@/assets/logos/meryxbgu.png" alt="" srcset="" width="35px" height="45px"
        /></span>
        <span style="font-weight: 800"
          >Mery<span style="color: var(--primary); font-weight: 800">X</span></span
        >
      </div>

      <!-- <div class="search-box">
        <i class="bi bi-search"></i>

        <input type="text" :placeholder="labels.searchPlaceholder" />
      </div> -->
    </div>

    <div class="menu">
      <!-- <div class="menu-item active">
        <i class="bi bi-house-fill"></i>
        <span>Accueil</span>
      </div> -->

      <div
        class="menu-item"
        :class="{ active: activeMenu === 'dashboard' }"
        @click="selectMenu('dashboard')"
      >
        <i class="bi bi-house-fill"></i>
        <span>{{ labels.dashboard }}</span>
      </div>

      <div
        class="menu-item"
        :class="{ active: activeMenu === 'studentOpportunities' }"
        @click="selectMenu('studentOpportunities')"
      >
        <i class="bi bi-briefcase-fill"></i>
        <span>{{ labels.opportunities }}</span>
      </div>

      <div
        class="menu-item"
        :class="{ active: activeMenu === 'formation' }"
        @click="selectMenu('formation')"
      >
        <i class="bi bi-mortarboard-fill"></i>
        <span>{{ labels.formation }}</span>
      </div>

      <div
        class="menu-item notification"
        :class="{ active: activeMenu === 'messages' }"
        @click="selectMenu('messages')"
      >
        <i class="bi bi-chat-dots-fill"></i>
        <span class="badge">2</span>
        <span>{{ labels.messages }}</span>
      </div>

      <div
        class="menu-item"
        :class="{ active: activeMenu === 'profile' }"
        @click="selectMenu('profile')"
      >
        <i class="bi bi-people-fill"></i>
        <!-- <span class="badge">5</span> -->
        <span>{{ labels.profile }}</span>
      </div>

      <div
        class="menu-item"
        :class="{ active: activeMenu === 'settings' }"
        @click="selectMenu('settings')"
      >
        <i class="bi bi-gear-fill"></i>
        <span>{{ labels.settings }}</span>
      </div>

      <div class="menu-item" @click="logout">
        <i class="bi bi-box-arrow-right"></i>
        <span>{{ labels.logout }}</span>
      </div>

      <!-- <div class="menu-item utility" @click="toggleLocale">
        <i class="bi bi-translate"></i>
        <span>{{ labels.languageToggle }}</span>
      </div>

      <div class="menu-item utility" @click="toggleTheme">
        <i class="bi bi-circle-half"></i>
        <span>{{ themeToggleLabel }}</span>
      </div> -->

      <div class="profile">
        <img src="https://i.pravatar.cc/150?img=11" />

        <div>
          <strong>Amadou Coulibaly</strong>
          <small>Étudiant</small>
        </div>

        <i class="bi bi-chevron-down"></i>
      </div>
    </div>
  </header>
</template>

<style scoped>
.navbar {
  position: fixed;
  top: 0;
  left: 0;
  z-index: 999;
  height: 72px;
  width: 100%;
  /* color: #d7e6f5; */
  color: #d7e6f5;
  background: rgba(13, 43, 69, 0.92);
  display: flex;
  justify-content: space-between;
  padding: 0 40px;
  border-bottom: 1px solid rgba(242, 244, 247, 0.16);
  backdrop-filter: blur(8px);
}

.left {
  display: flex;
  align-items: center;
  gap: 25px;
  width: 35%;
}

.logo {
  font-size: 25px;
  font-weight: bold;
  display: flex;
  align-items: center;
  gap: 8px;
}

.x {
  color: #d4a017;
}

.search-box {
  background: var(--surface-soft);
  width: 500px;
  height: 46px;
  border-radius: 12px;

  display: flex;
  align-items: center;
  padding: 0 10px;
  gap: 10px;
}

.search-box input {
  width: 90%;
  border: none;
  background: transparent;
  outline: none;
  color: var(--text);
}

.menu {
  display: flex;
  gap: 20px;
  align-items: center;
  /* margin-left: -50px; */
}

.menu-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  cursor: pointer;
  position: relative;
  color: #d7e6f5;
  border-radius: 10px;
  padding: 6px 8px;
}

.menu-item i {
  font-size: 20px;
}

.menu-item span {
  font-size: 13px;
  color: #d7e6f5;
}

.active {
  color: #d4a017;
  border-bottom: 3px solid #d4a017;
  padding-bottom: 8px;
}

.badge {
  position: absolute;
  top: -6px;
  right: 10px;
  background: #d4a017;
  color: #0d2b45;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  font-size: 10px;

  display: flex;
  justify-content: center;
  align-items: center;
}

.profile {
  display: flex;
  align-items: center;
  gap: 12px;
  border-radius: 999px;
  padding: 6px 10px;
  background: rgba(17, 74, 106, 0.48);
  border: 1px solid rgba(242, 244, 247, 0.16);
}

.profile img {
  width: 45px;
  height: 45px;
  border-radius: 50%;
}

.profile small {
  display: block;
  color: rgba(242, 244, 247, 0.72);
}

@media (max-width: 1200px) {
  .navbar {
    padding: 0 20px;
  }

  .left {
    width: auto;
    min-width: 0;
    flex: 1;
    gap: 12px;
  }

  .search-box {
    width: 100%;
    max-width: 360px;
  }

  .menu {
    gap: 14px;
    overflow-x: auto;
    scrollbar-width: none;
  }

  .menu::-webkit-scrollbar {
    display: none;
  }
}

@media (max-width: 900px) {
  .navbar {
    height: auto;
    min-height: 72px;
    padding: 10px 14px;
    flex-wrap: wrap;
    row-gap: 10px;
  }

  .left {
    width: 100%;
    gap: 10px;
  }

  .logo span:last-child {
    font-size: 20px;
  }

  .search-box {
    max-width: none;
    height: 40px;
    border-radius: 10px;
    padding: 0 10px;
  }

  .menu {
    width: 100%;
    justify-content: flex-start;
    padding-bottom: 2px;
    gap: 12px;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  .menu-item i {
    font-size: 18px;
  }

  .menu-item span {
    font-size: 12px;
    white-space: nowrap;
  }

  .profile strong,
  .profile small,
  .profile .bi-chevron-down {
    display: none;
  }

  .profile {
    margin-left: 6px;
  }

  .profile img {
    width: 36px;
    height: 36px;
  }

  .active {
    padding-bottom: 4px;
    border-bottom-width: 2px;
  }
}

@media (max-width: 640px) {
  .navbar {
    padding: 8px 10px;
    row-gap: 8px;
  }

  .left {
    flex-wrap: wrap;
    gap: 8px;
  }

  .logo {
    font-size: 20px;
  }

  .search-box {
    order: 2;
    width: 100%;
    flex-basis: 100%;
    height: 38px;
  }

  .search-box input {
    font-size: 14px;
  }

  .menu {
    gap: 10px;
    padding-bottom: 6px;
  }

  .menu-item {
    min-width: 62px;
  }

  .menu-item span {
    font-size: 11px;
  }

  .badge {
    right: 6px;
  }
}

@media (max-width: 420px) {
  .logo span:last-child {
    font-size: 18px;
  }

  .search-box input::placeholder {
    font-size: 12px;
  }

  .menu {
    justify-content: space-between;
    overflow-x: visible;
    gap: 6px;
  }

  .menu-item {
    min-width: 40px;
  }

  .menu-item span:not(.badge) {
    display: none;
  }

  .menu-item i {
    font-size: 19px;
  }

  .active {
    border-bottom-width: 0;
    border-radius: 8px;
    background: var(--surface-soft);
    padding: 6px 8px;
  }

  .badge {
    top: -2px;
    right: -2px;
    width: 16px;
    height: 16px;
    font-size: 9px;
  }
}
</style>

<script>
import { useUiPreferences } from '@/compasables/useUiPreferences'
import { useAuthStore } from '@/stores/auth.store'

const uiPreferences = useUiPreferences()
const authStore = useAuthStore()

export default {
  data() {
    return {
      activeMenu: 'dashboard',
    }
  },
  computed: {
    locale() {
      return uiPreferences.locale.value
    },
    labels() {
      if (this.locale === 'fr') {
        return {
          dashboard: 'Tableau',
          opportunities: 'Opportunites',
          formation: 'Formation',
          messages: 'Messages',
          profile: 'Profil',
          settings: 'Parametres',
          logout: 'Deconnexion',
          languageToggle: 'EN',
          searchPlaceholder: 'Rechercher des opportunites, entreprises, universites...',
        }
      }
      return {
        dashboard: 'Dashboard',
        opportunities: 'Opportunities',
        formation: 'Formation',
        messages: 'Messages',
        profile: 'Profile',
        settings: 'Settings',
        logout: 'Logout',
        languageToggle: 'FR',
        searchPlaceholder: 'Search opportunities, companies, universities...',
      }
    },
    themeToggleLabel() {
      if (this.locale === 'fr') {
        return uiPreferences.theme.value === 'light' ? 'Sombre' : 'Clair'
      }
      return uiPreferences.theme.value === 'light' ? 'Dark' : 'Light'
    },
  },
  created() {
    uiPreferences.initializeUiPreferences()
  },
  watch: {
    '$route.name': {
      immediate: true,
      handler(name) {
        if (name === 'studentOpportunities') {
          this.activeMenu = 'studentOpportunities'
        } else if (name === 'studentMessages') {
          this.activeMenu = 'messages'
        } else if (name === 'profile') {
          this.activeMenu = 'profile'
        } else if (name === 'formation') {
          this.activeMenu = 'formation'
        } else if (name === 'studentSettings') {
          this.activeMenu = 'settings'
        } else {
          this.activeMenu = 'dashboard'
        }
      },
    },
  },
  methods: {
    toggleLocale() {
      uiPreferences.toggleLocale()
    },
    toggleTheme() {
      uiPreferences.toggleTheme()
    },
    logout() {
      authStore.logout()
      this.$router.push('/login')
    },
    selectMenu(menu) {
      this.activeMenu = menu

      if (menu === 'dashboard') {
        this.$router.push({ name: 'dashboard' })
      } else if (menu === 'studentOpportunities') {
        this.$router.push({ name: 'studentOpportunities' })
      } else if (menu === 'profile') {
        this.$router.push({ name: 'profile' })
      } else if (menu === 'formation') {
        this.$router.push({ name: 'formation' })
      } else if (menu === 'messages') {
        this.$router.push({ name: 'studentMessages' })
      } else if (menu === 'settings') {
        this.$router.push({ name: 'studentSettings' })
      }
    },
  },
}
</script>
