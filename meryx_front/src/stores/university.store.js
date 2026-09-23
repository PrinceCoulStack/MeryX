// src/stores/university.store.js

import { defineStore } from 'pinia'
import api from '@/api/axios'

export const useUniversityStore = defineStore('university', {
  state: () => ({
    universities: [],
    university: null,
    loading: false,
  }),

  getters: {
    totalUniversities: (state) =>
      state.universities.length,
  },

  actions: {
    async fetchUniversities() {
      this.loading = true

      try {
        const response = await api.get('/universities')

        this.universities =
          response.data['hydra:member']
      } finally {
        this.loading = false
      }
    },

    async fetchUniversity(id) {
      const response =
        await api.get(`/universities/${id}`)

      this.university = response.data
    },

    async createUniversity(data) {
      await api.post('/universities', data)
      await this.fetchUniversities()
    },

    async updateUniversity(id, data) {
      await api.put(`/universities/${id}`, data)
      await this.fetchUniversities()
    },

    async deleteUniversity(id) {
      await api.delete(`/universities/${id}`)

      this.universities =
        this.universities.filter(
          university => university.id !== id
        )
    }
  }
})
