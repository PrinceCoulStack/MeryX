/**
 * useCandidature Composable
 * Manages candidature and saved opportunity state and operations
 * Uses strictly-typed candidatureApiClient for all API calls
 */

import { computed, ref } from 'vue'
import candidatureApiClient from '@/api/candidatureApiClient'
import { parseHydraCollection } from '@/utils/hydraParser'

// State
const candidatures = ref([])
const savedOpportunities = ref([])
const isLoading = ref(false)
const error = ref('')

/**
 * Normalize candidature from API response
 */
const normalizeCandidature = (item) => ({
  id: item.id,
  opportunityId: item.opportunityId || item.opportunity?.id,
  opportunity: item.opportunity || { title: '', company: '', id: null },
  studentId: item.studentId || item.student?.id,
  student: item.student || { fullName: '', id: null },
  status: item.status || 'applied',
  appliedDate: item.appliedDate || item.createdAt,
  lastUpdated: item.lastUpdated || item.updatedAt,
  feedback: item.feedback || '',
  notes: item.notes || '',
  interviewDate: item.interviewDate || null,
  raw: item,
})

/**
 * Normalize saved opportunity from API response
 */
const normalizeSavedOpportunity = (item) => ({
  id: item.id,
  opportunityId: item.opportunityId || item.opportunity?.id,
  opportunity: item.opportunity || { title: '', company: '', id: null },
  studentId: item.studentId || item.student?.id,
  student: item.student || { fullName: '', id: null },
  savedDate: item.savedDate || item.createdAt,
  notes: item.notes || '',
  raw: item,
})

/**
 * Fetch candidatures for a student
 * GET /api/candidatures?studentId=:studentId
 */
const fetchCandidatures = async (studentId = null) => {
  isLoading.value = true
  error.value = ''

  try {
    const filters = {}
    if (studentId) {
      filters.studentId = studentId
    }

    const response = await candidatureApiClient.getCandidatures(filters)
    const items = parseHydraCollection(response.raw)

    candidatures.value = items.map(normalizeCandidature)
    return candidatures.value
  } catch (err) {
    error.value = err?.message || 'Unable to fetch candidatures'
    console.error('[useCandidature] Error fetching candidatures:', err)
    throw err
  } finally {
    isLoading.value = false
  }
}

/**
 * Fetch saved opportunities for a student
 * GET /api/savedOpportunities?studentId=:studentId
 */
const fetchSavedOpportunities = async (studentId = null) => {
  isLoading.value = true
  error.value = ''

  try {
    const filters = {}
    if (studentId) {
      filters.studentId = studentId
    }

    const response = await candidatureApiClient.getSavedOpportunities(filters)
    const items = parseHydraCollection(response.raw)

    savedOpportunities.value = items.map(normalizeSavedOpportunity)
    return savedOpportunities.value
  } catch (err) {
    error.value = err?.message || 'Unable to fetch saved opportunities'
    console.error('[useCandidature] Error fetching saved opportunities:', err)
    throw err
  } finally {
    isLoading.value = false
  }
}

/**
 * Apply for an opportunity (create candidature)
 * POST /api/candidatures
 */
const applyCandidature = async (opportunityId, studentId, notes = '') => {
  isLoading.value = true
  error.value = ''

  try {
    // Check if already applied
    const existing = candidatures.value.find(
      (c) => c.opportunityId === opportunityId && c.studentId === studentId,
    )

    if (existing) {
      throw new Error('You have already applied to this opportunity')
    }

    // Send POST request
    const response = await candidatureApiClient.createCandidature({
      opportunityId,
      studentId,
      notes: notes || undefined,
    })

    // Normalize and add to state
    const normalized = normalizeCandidature(response.data)
    candidatures.value.push(normalized)

    return normalized
  } catch (err) {
    error.value = err?.message || 'Unable to apply to opportunity'
    console.error('[useCandidature] Error applying:', err)
    throw err
  } finally {
    isLoading.value = false
  }
}

/**
 * Save an opportunity for later
 * POST /api/savedOpportunities (idempotent)
 *
 * Returns:
 * - 201: Created new record
 * - 200: Already exists (treat as success)
 */
const saveOpportunity = async (opportunityId, studentId, notes = '') => {
  isLoading.value = true
  error.value = ''

  try {
    // Check if already saved locally
    const existing = savedOpportunities.value.find(
      (s) => s.opportunityId === opportunityId && s.studentId === studentId,
    )

    if (existing) {
      // Already in local state, but still send to backend for sync
    }

    // Send POST request (idempotent)
    const response = await candidatureApiClient.saveopportunity({
      opportunityId,
      studentId,
      notes: notes || undefined,
    })

    // Normalize response
    const normalized = normalizeSavedOpportunity(response.data)

    // If creating new record, add to state
    if (response.isNewRecord) {
      savedOpportunities.value.push(normalized)
    } else {
      // If already existed, update local reference
      const idx = savedOpportunities.value.findIndex(
        (s) => s.opportunityId === opportunityId && s.studentId === studentId,
      )
      if (idx >= 0) {
        savedOpportunities.value[idx] = normalized
      } else {
        savedOpportunities.value.push(normalized)
      }
    }

    return normalized
  } catch (err) {
    error.value = err?.message || 'Unable to save opportunity'
    console.error('[useCandidature] Error saving opportunity:', err)
    throw err
  } finally {
    isLoading.value = false
  }
}

/**
 * Remove saved opportunity
 * DELETE /api/savedOpportunities/:id
 */
const unsaveOpportunity = async (opportunityId, studentId) => {
  isLoading.value = true
  error.value = ''

  try {
    // Find the saved record
    const saved = savedOpportunities.value.find(
      (s) => s.opportunityId === opportunityId && s.studentId === studentId,
    )

    if (!saved) {
      throw new Error('Opportunity not found in saved list')
    }

    // Send DELETE request
    await candidatureApiClient.deleteSavedOpportunity(saved.id)

    // Remove from state
    savedOpportunities.value = savedOpportunities.value.filter((s) => s.id !== saved.id)

    return true
  } catch (err) {
    error.value = err?.message || 'Unable to unsave opportunity'
    console.error('[useCandidature] Error unsaving opportunity:', err)
    throw err
  } finally {
    isLoading.value = false
  }
}

/**
 * Update candidature status
 * PATCH /api/candidatures/:id
 */
const updateCandidatureStatus = async (
  candidatureId,
  newStatus,
  feedback = '',
  notes = null,
  interviewDate = null,
) => {
  error.value = ''

  try {
    // Find candidature
    const candidature = candidatures.value.find((c) => c.id === candidatureId)
    if (!candidature) {
      throw new Error('Candidature not found')
    }

    // Send PATCH request
    const response = await candidatureApiClient.updateCandidature(candidatureId, {
      status: newStatus,
      feedback: feedback || undefined,
      notes: notes || undefined,
      interviewDate: interviewDate || undefined,
    })

    // Normalize and update state
    const normalized = normalizeCandidature(response.data)
    const idx = candidatures.value.findIndex((c) => c.id === candidatureId)
    if (idx >= 0) {
      candidatures.value[idx] = normalized
    }

    return normalized
  } catch (err) {
    error.value = err?.message || 'Unable to update candidature'
    console.error('[useCandidature] Error updating candidature:', err)
    throw err
  }
}

/**
 * Query: Check if opportunity is saved
 */
const isSaved = (opportunityId, studentId) =>
  savedOpportunities.value.some(
    (s) => s.opportunityId === opportunityId && s.studentId === studentId,
  )

/**
 * Query: Check if already applied
 */
const isApplied = (opportunityId, studentId) =>
  candidatures.value.some((c) => c.opportunityId === opportunityId && c.studentId === studentId)

/**
 * Query: Get candidature for opportunity
 */
const getCandidatureByOpportunity = (opportunityId, studentId) =>
  candidatures.value.find((c) => c.opportunityId === opportunityId && c.studentId === studentId) ||
  null

/**
 * Query: Get saved opportunity record
 */
const getSavedOpportunityByOpportunity = (opportunityId, studentId) =>
  savedOpportunities.value.find(
    (s) => s.opportunityId === opportunityId && s.studentId === studentId,
  ) || null

export function useCandidature() {
  return {
    // State
    candidatures: computed(() => candidatures.value),
    savedOpportunities: computed(() => savedOpportunities.value),
    isLoading: computed(() => isLoading.value),
    error: computed(() => error.value),

    // Actions
    fetchCandidatures,
    fetchSavedOpportunities,
    applyCandidature,
    saveOpportunity,
    unsaveOpportunity,
    updateCandidatureStatus,

    // Queries
    isSaved,
    isApplied,
    getCandidatureByOpportunity,
    getSavedOpportunityByOpportunity,
  }
}
