// src/stores/company.store.js

import { defineStore } from 'pinia'
import api from '@/api/axios'

export const useCompanyStore = defineStore('company', {
  state: () => ({
    companies: [],
    company: null,
    loading: false,
  }),

  getters: {
    totalCompanies: (state) => state.companies.length,
  },

  actions: {
    async fetchCompanies() {
      this.loading = true

      try {
        const response = await api.get('/companies')
        this.companies = response.data['hydra:member']
      } finally {
        this.loading = false
      }
    },

    async fetchCompany(id) {
      const response = await api.get(`/companies/${id}`)
      this.company = response.data
    },

    async createCompany(data) {
      await api.post('/companies', data)
      await this.fetchCompanies()
    },

    async updateCompany(id, data) {
      await api.put(`/companies/${id}`, data)
      await this.fetchCompanies()
    },

    async deleteCompany(id) {
      await api.delete(`/companies/${id}`)
      this.companies = this.companies.filter(c => c.id !== id)
    }
  }
})
