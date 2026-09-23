<template>
  <div class="public-layout">
    <header class="public-header" :class="{ scrolled: isScrolled }">
      <div class="header-inner">
        <RouterLink to="/" class="brand" @click="closeMobileMenu">
          <img src="@/assets/logos/MeryXBGu.png" alt="MeryX" />
        </RouterLink>

        <nav class="desktop-nav" aria-label="Navigation principale">
          <RouterLink :to="{ path: '/', hash: '#how-it-works' }">Comment ca marche</RouterLink>
          <RouterLink :to="{ path: '/', hash: '#students-value' }">Etudiants</RouterLink>
          <RouterLink :to="{ path: '/', hash: '#companies-value' }">Entreprises</RouterLink>
          <RouterLink :to="{ path: '/', hash: '#universities-value' }">Universites</RouterLink>
          <RouterLink :to="{ path: '/', hash: '#certifications' }">Certifications</RouterLink>
          <RouterLink to="/about">A propos</RouterLink>
        </nav>

        <div class="header-actions desktop-actions">
          <RouterLink to="/login" class="signin-link">Se connecter</RouterLink>
          <RouterLink to="/register-choice" class="profile-cta profile-trigger"
            >Creer mon profil</RouterLink
          >
          <!--  <div class="profile-menu">
            <button
              type="button"
              class="profile-cta profile-trigger"
              @click="choiceProfileToCreate"
              :aria-expanded="String(isProfileMenuOpen)"
              @click.stop="toggleProfileMenu"
              >
              Creer mon profil
              <i class="bi" :class="isProfileMenuOpen ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
            </button>

            <div v-show="isProfileMenuOpen" class="profile-dropdown">
              <RouterLink to="/register/university" @click="closeProfileMenu">
                Profil universite
              </RouterLink>
              <RouterLink to="/register/company" @click="closeProfileMenu">
                Profil entreprise
              </RouterLink>
            </div>
          </div> -->
        </div>

        <div class="mobile-actions">
          <button
            type="button"
            class="menu-toggle"
            aria-label="Menu"
            :aria-expanded="String(isMobileMenuOpen)"
            @click="toggleMobileMenu"
          >
            <i class="bi" :class="isMobileMenuOpen ? 'bi-x-lg' : 'bi-list'" aria-hidden="true"></i>
          </button>
        </div>
      </div>

      <div v-show="isMobileMenuOpen" class="mobile-menu" role="menu">
        <RouterLink :to="{ path: '/', hash: '#how-it-works' }" @click="closeMobileMenu"
          >Comment ca marche</RouterLink
        >
        <RouterLink :to="{ path: '/', hash: '#students-value' }" @click="closeMobileMenu"
          >Etudiants</RouterLink
        >
        <RouterLink :to="{ path: '/', hash: '#companies-value' }" @click="closeMobileMenu"
          >Entreprises</RouterLink
        >
        <RouterLink :to="{ path: '/', hash: '#universities-value' }" @click="closeMobileMenu"
          >Universites</RouterLink
        >
        <RouterLink :to="{ path: '/', hash: '#certifications' }" @click="closeMobileMenu"
          >Certifications</RouterLink
        >
        <RouterLink to="/about" @click="closeMobileMenu">A propos</RouterLink>
        <RouterLink to="/login" class="mobile-login" @click="closeMobileMenu"
          >Se connecter</RouterLink
        >
        <RouterLink to="/register/student" class="mobile-register" @click="closeMobileMenu"
          >Creer un profil étudiant</RouterLink
        >
        <RouterLink to="/register/university" class="mobile-register" @click="closeMobileMenu"
          >Creer un profil université</RouterLink
        >
        <RouterLink to="/register/company" class="mobile-register" @click="closeMobileMenu"
          >Creer un profil entreprise</RouterLink
        >
      </div>
    </header>

    <main class="public-content">
      <RouterView />
    </main>

    <footer class="public-footer">
      <div class="footer-inner">
        <div class="footer-brand">
          <img src="@/assets/logos/MeryXBGu.png" alt="MeryX" />
          <p>
            MeryX rend la progression des etudiants visible, structuree et fiable pour les
            universites et les entreprises.
          </p>
        </div>

        <div class="footer-links">
          <div>
            <h4>Plateforme</h4>
            <RouterLink :to="{ path: '/', hash: '#students-value' }">Etudiants</RouterLink>
            <RouterLink :to="{ path: '/', hash: '#companies-value' }">Entreprises</RouterLink>
            <RouterLink :to="{ path: '/', hash: '#universities-value' }">Universites</RouterLink>
            <RouterLink :to="{ path: '/', hash: '#certifications' }">Certifications</RouterLink>
          </div>

          <div>
            <h4>Entreprise</h4>
            <RouterLink to="/about">A propos</RouterLink>
            <RouterLink to="/contact">Contact</RouterLink>
            <a href="#">Mentions legales</a>
            <a href="#">Politique de confidentialite</a>
          </div>
        </div>

        <div class="footer-social">
          <h4>Suivez-nous</h4>
          <div class="social-icons">
            <a href="#" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
            <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
            <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
            <a href="#" aria-label="X"><i class="bi bi-twitter-x"></i></a>
          </div>
        </div>
      </div>

      <p class="copyright">© 2026 MeryX. Tous droits reserves.</p>
    </footer>
  </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { RouterView, RouterLink } from 'vue-router'

const route = useRoute()
const isScrolled = ref(false)
const isMobileMenuOpen = ref(false)
const isProfileMenuOpen = ref(false)

const handleScroll = () => {
  isScrolled.value = window.scrollY > 10
}

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
}

const closeMobileMenu = () => {
  isMobileMenuOpen.value = false
}

const toggleProfileMenu = () => {
  isProfileMenuOpen.value = !isProfileMenuOpen.value
}

const closeProfileMenu = () => {
  isProfileMenuOpen.value = false
}

const handleDocumentClick = (event) => {
  if (!event.target.closest('.profile-menu')) {
    closeProfileMenu()
  }
}

watch(
  () => route.fullPath,
  () => {
    closeMobileMenu()
    closeProfileMenu()
  },
)

onMounted(() => {
  handleScroll()
  window.addEventListener('scroll', handleScroll)
  document.addEventListener('click', handleDocumentClick)
})

onBeforeUnmount(() => {
  window.removeEventListener('scroll', handleScroll)
  document.removeEventListener('click', handleDocumentClick)
})
</script>

<style scoped>
.public-layout {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  width: 100%;
  background: #f2f4f7;
}

.public-header {
  position: sticky;
  top: 0;
  z-index: 60;
  background: rgba(255, 255, 255, 0.88);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(13, 43, 69, 0.08);
  transition:
    box-shadow 0.25s ease,
    background-color 0.25s ease;
}

.public-header.scrolled {
  box-shadow: 0 8px 20px rgba(13, 43, 69, 0.08);
  background: rgba(255, 255, 255, 0.94);
}

.header-inner {
  width: min(1220px, calc(100% - 28px));
  margin: 0 auto;
  min-height: 74px;
  display: grid;
  grid-template-columns: auto 1fr auto;
  align-items: center;
  gap: 18px;
}

.brand img {
  height: 44px;
  width: auto;
  display: block;
}

.desktop-nav {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 18px;
}

.desktop-nav a {
  color: #0d2b45;
  font-size: 0.9rem;
  font-weight: 600;
}

.desktop-nav a:hover,
.desktop-nav a.router-link-active {
  color: #114a6a;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.signin-link {
  color: #0d2b45;
  font-weight: 700;
}

.profile-cta {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  border-radius: 12px;
  background: linear-gradient(120deg, #114a6a, #1e3f66);
  border: 1px solid rgba(17, 74, 106, 0.65);
  color: #f2f4f7;
  font-weight: 700;
  box-shadow: 0 10px 20px rgba(17, 74, 106, 0.2);
}

.profile-menu {
  position: relative;
}

.profile-trigger {
  cursor: pointer;
}

.profile-cta:hover {
  color: #f2f4f7;
  transform: translateY(-1px);
}

.profile-dropdown {
  position: absolute;
  top: calc(100% + 10px);
  right: 0;
  min-width: 220px;
  padding: 8px;
  border-radius: 14px;
  border: 1px solid rgba(13, 43, 69, 0.12);
  background: #ffffff;
  box-shadow: 0 18px 30px rgba(13, 43, 69, 0.12);
  display: grid;
  gap: 4px;
}

.profile-dropdown a {
  padding: 10px 12px;
  border-radius: 10px;
  color: #0d2b45;
  font-weight: 600;
}

.profile-dropdown a:hover {
  background: rgba(17, 74, 106, 0.08);
}

.mobile-actions,
.mobile-menu {
  display: none;
}

.menu-toggle {
  width: 42px;
  height: 42px;
  border-radius: 10px;
  border: 1px solid rgba(13, 43, 69, 0.2);
  background: #ffffff;
  color: #0d2b45;
  display: grid;
  place-items: center;
}

.public-content {
  flex: 1;
  background: #f2f4f7;
}

.public-footer {
  border-top: 1px solid rgba(13, 43, 69, 0.1);
  background: linear-gradient(180deg, #ffffff 0%, #f4f7fb 100%);
  padding: 42px 0 18px;
}

.footer-inner {
  width: min(1220px, calc(100% - 28px));
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1.5fr 1.1fr 0.8fr;
  gap: 22px;
}

.footer-brand img {
  height: 46px;
  width: auto;
}

.footer-brand p {
  margin: 12px 0 0;
  max-width: 46ch;
  color: rgba(13, 43, 69, 0.74);
}

.footer-links {
  display: grid;
  grid-template-columns: repeat(2, minmax(120px, 1fr));
  gap: 16px;
}

.footer-links h4,
.footer-social h4 {
  margin: 0 0 10px;
  color: #0d2b45;
  font-size: 0.95rem;
}

.footer-links a {
  display: block;
  margin: 8px 0;
  color: rgba(13, 43, 69, 0.74);
  font-weight: 500;
}

.social-icons {
  display: flex;
  gap: 8px;
}

.social-icons a {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  border: 1px solid rgba(13, 43, 69, 0.14);
  background: #ffffff;
  color: #0d2b45;
  display: grid;
  place-items: center;
}

.social-icons a:hover {
  border-color: rgba(212, 160, 23, 0.62);
  color: #114a6a;
}

.copyright {
  margin: 24px auto 0;
  width: min(1220px, calc(100% - 28px));
  padding-top: 14px;
  border-top: 1px solid rgba(13, 43, 69, 0.1);
  color: rgba(13, 43, 69, 0.7);
  font-size: 0.88rem;
}

@media (max-width: 1080px) {
  .desktop-nav,
  .desktop-actions {
    display: none;
  }

  .header-inner {
    grid-template-columns: auto 1fr auto;
  }

  .mobile-actions {
    display: flex;
    justify-self: end;
    align-items: center;
    gap: 10px;
  }

  .mobile-menu {
    display: grid;
    width: min(1220px, calc(100% - 28px));
    margin: 0 auto 12px;
    padding: 10px;
    border-radius: 14px;
    border: 1px solid rgba(13, 43, 69, 0.14);
    background: #ffffff;
    gap: 4px;
  }

  .mobile-menu a {
    padding: 10px;
    border-radius: 10px;
    color: #0d2b45;
    font-weight: 600;
  }

  .mobile-menu a:hover,
  .mobile-menu a.router-link-active {
    background: rgba(17, 74, 106, 0.08);
  }

  .mobile-login {
    border-top: 1px solid rgba(13, 43, 69, 0.1);
    margin-top: 4px;
  }

  .mobile-register {
    color: #114a6a;
  }

  .footer-inner {
    grid-template-columns: 1fr;
  }

  .footer-links {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 620px) {
  .header-inner {
    min-height: 66px;
  }

  .brand img {
    height: 40px;
  }

  .footer-links {
    grid-template-columns: 1fr;
  }
}
</style>
