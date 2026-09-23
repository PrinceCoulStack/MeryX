// src/compasables/useDarkMode.js

import { ref, watch, computed } from 'vue'

const isDarkMode = ref(localStorage.getItem('darkMode') === 'true')

export const useDarkMode = () => {
  watch(isDarkMode, (newValue) => {
    localStorage.setItem('darkMode', String(newValue))
    if (newValue) {
      document.documentElement.setAttribute('data-theme', 'dark')
    } else {
      document.documentElement.removeAttribute('data-theme')
    }
  })

  // Initialize on first load
  if (isDarkMode.value) {
    document.documentElement.setAttribute('data-theme', 'dark')
  }

  const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value
  }

  return {
    isDarkMode,
    toggleDarkMode,
  }
}
