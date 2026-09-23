// src/composables/usePagination.js

import { computed, ref, unref } from 'vue'

export function usePagination(data, perPage = 10) {
  const currentPage = ref(1)

  const totalItems = computed(() => unref(data).length)

  const totalPages = computed(() =>
    Math.ceil(totalItems.value / perPage)
  )

  const paginatedItems = computed(() => {
    const items = unref(data)

    const start = (currentPage.value - 1) * perPage

    return items.slice(start, start + perPage)
  })

  const nextPage = () => {
    if (currentPage.value < totalPages.value) {
      currentPage.value++
    }
  }

  const previousPage = () => {
    if (currentPage.value > 1) {
      currentPage.value--
    }
  }

  const goToPage = (page) => {
    currentPage.value = Math.max(
      1,
      Math.min(page, totalPages.value)
    )
  }

  const reset = () => {
    currentPage.value = 1
  }

  return {
    currentPage,
    totalItems,
    totalPages,
    paginatedItems,

    nextPage,
    previousPage,
    goToPage,
    reset,
  }
}
