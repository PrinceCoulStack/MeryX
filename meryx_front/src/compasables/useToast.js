// src/composables/useToast.js

import { useToast as toastification } from 'vue-toastification'

export function useToast() {
  const toast = toastification()

  const success = (message) => {
    toast.success(message)
  }

  const error = (message) => {
    toast.error(message)
  }

  const warning = (message) => {
    toast.warning(message)
  }

  const info = (message) => {
    toast.info(message)
  }

  return {
    success,
    error,
    warning,
    info,
  }
}
