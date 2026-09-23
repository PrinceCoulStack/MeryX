import { computed, ref } from 'vue'

const THEME_KEY = 'meryx.theme'
const LOCALE_KEY = 'meryx.locale'

const theme = ref('light')
const locale = ref('en')
const initialized = ref(false)

const applyTheme = (value) => {
  document.body.classList.toggle('theme-dark', value === 'dark')
}

const applyLocale = (value) => {
  document.documentElement.lang = value
  document.body.setAttribute('data-locale', value)
}

const normalizeTheme = (value) => (value === 'dark' ? 'dark' : 'light')
const normalizeLocale = (value) => (value === 'fr' ? 'fr' : 'en')

const initializeUiPreferences = () => {
  if (initialized.value || typeof window === 'undefined') return

  theme.value = normalizeTheme(window.localStorage.getItem(THEME_KEY))
  locale.value = normalizeLocale(window.localStorage.getItem(LOCALE_KEY))

  applyTheme(theme.value)
  applyLocale(locale.value)

  initialized.value = true
}

const setTheme = (value) => {
  const nextTheme = normalizeTheme(value)
  theme.value = nextTheme
  applyTheme(nextTheme)
  window.localStorage.setItem(THEME_KEY, nextTheme)
}

const toggleTheme = () => {
  setTheme(theme.value === 'light' ? 'dark' : 'light')
}

const setLocale = (value) => {
  const nextLocale = normalizeLocale(value)
  locale.value = nextLocale
  applyLocale(nextLocale)
  window.localStorage.setItem(LOCALE_KEY, nextLocale)
}

const toggleLocale = () => {
  setLocale(locale.value === 'en' ? 'fr' : 'en')
}

const isDarkMode = computed(() => theme.value === 'dark')
const isFrench = computed(() => locale.value === 'fr')

export const useUiPreferences = () => ({
  theme,
  locale,
  isDarkMode,
  isFrench,
  initializeUiPreferences,
  setTheme,
  toggleTheme,
  setLocale,
  toggleLocale,
})
