/**
 * Candidature & Saved Opportunities API Client
 * Strictly enforces the Symfony backend contract
 * - Base: /api
 * - All routes require Bearer token auth
 * - Response format: JSON-LD Hydra
 * - Date format: ISO 8601 UTC
 */

import api from './axios'
import {
  validateCandidatureRequest,
  validateSavedOpportunityRequest,
  validateStatusTransition,
} from './validators'
import { parseHydraCollection, parseHydraError, isHydraError } from '@/utils/hydraParser'

// Logging enabled in dev mode
const DEV_MODE = import.meta.env.DEV
const log = (method, route, payload, status, error = null) => {
  if (!DEV_MODE) return

  const timestamp = new Date().toISOString()
  const logEntry = {
    timestamp,
    method,
    route: `/api${route}`,
    payload,
    status,
    error: error ? error.message : null,
  }

  console.log('[API Client]', JSON.stringify(logEntry, null, 2))
}

/**
 * GET /api/candidatures
 * Fetch student's candidatures with optional filtering
 *
 * Query params:
 * - studentId (number): Filter by student
 * - status (string): Filter by status (applied|interview|offer|accepted|rejected)
 * - opportunityId (number): Filter by opportunity
 * - createdAfter (ISO datetime): Filter by creation date
 * - createdBefore (ISO datetime): Filter by creation date
 * - sortBy (string): Field to sort by (default: createdAt)
 * - order (asc|desc): Sort order
 * - page (number): Page number (default: 1)
 * - limit (number): Items per page (default: 20)
 */
export async function getCandidatures(filters = {}) {
  const startTime = performance.now()
  let params = {}

  try {
    if (filters.studentId) params.studentId = filters.studentId
    if (filters.status) params.status = filters.status
    if (filters.opportunityId) params.opportunityId = filters.opportunityId
    if (filters.createdAfter) params.createdAfter = filters.createdAfter
    if (filters.createdBefore) params.createdBefore = filters.createdBefore
    if (filters.sortBy) params.sortBy = filters.sortBy
    if (filters.order) params.order = filters.order
    if (filters.page) params.page = filters.page
    if (filters.limit) params.limit = filters.limit

    const response = await api.listItems('candidatures', params)
    const duration = performance.now() - startTime

    log('GET', '/candidatures', params, response.status)

    return {
      data: parseHydraCollection(response.data),
      raw: response.data,
      pagination: {
        total: response.data['hydra:totalItems'] || 0,
        view: response.data['hydra:view'] || {},
      },
      status: response.status,
    }
  } catch (error) {
    const duration = performance.now() - startTime
    log('GET', '/candidatures', params, error.response?.status, error)

    if (isHydraError(error.response?.data)) {
      const hydraError = parseHydraError(error.response.data)
      throw new Error(`[${error.response.status}] ${hydraError.title}: ${hydraError.description}`)
    }

    throw error
  }
}

/**
 * POST /api/candidatures
 * Apply for an opportunity (create candidature)
 *
 * Request body:
 * - opportunityId (number | IRI string): Required
 * - studentId (number | IRI string): Required
 * - notes (string): Optional
 *
 * Response:
 * - 201: Created successfully
 * - 400: Invalid request
 * - 409: Already applied (if enforced by backend)
 */
export async function createCandidature(payload) {
  const startTime = performance.now()

  try {
    // Validate request
    validateCandidatureRequest(payload)

    // Normalize IDs to numbers for logging, but send as provided
    const requestBody = {
      opportunityId: payload.opportunityId,
      studentId: payload.studentId,
    }

    if (payload.notes !== undefined && payload.notes !== null) {
      requestBody.notes = payload.notes
    }

    log('POST', '/candidatures', requestBody, null)

    const response = await api.createItem('candidatures', requestBody)
    const duration = performance.now() - startTime

    log('POST', '/candidatures', requestBody, response.status)

    return {
      data: response.data,
      status: response.status,
    }
  } catch (error) {
    const duration = performance.now() - startTime
    log('POST', '/candidatures', payload, error.response?.status, error)

    if (isHydraError(error.response?.data)) {
      const hydraError = parseHydraError(error.response.data)
      throw new Error(`[${error.response.status}] ${hydraError.title}: ${hydraError.description}`)
    }

    throw error
  }
}

/**
 * PATCH /api/candidatures/:id
 * Update candidature status, feedback, notes, or interview date
 *
 * Request body:
 * - status (string): Optional, must respect transition rules
 * - feedback (string | null): Optional
 * - notes (string | null): Optional
 * - interviewDate (ISO datetime | null): Optional
 *
 * Response:
 * - 200: Updated successfully
 * - 400: Invalid transition or request
 * - 404: Not found
 */
export async function updateCandidature(id, payload) {
  const startTime = performance.now()

  try {
    if (!id) throw new Error('Candidature ID is required')

    const requestBody = {}

    if (payload.status !== undefined) {
      const allowedStatuses = ['applied', 'interview', 'offer', 'accepted', 'rejected']
      if (!allowedStatuses.includes(payload.status)) {
        throw new Error(
          `Invalid status: ${payload.status}. Must be one of: ${allowedStatuses.join(', ')}`,
        )
      }
      requestBody.status = payload.status
    }

    if (payload.feedback !== undefined) {
      requestBody.feedback = payload.feedback
    }

    if (payload.notes !== undefined) {
      requestBody.notes = payload.notes
    }

    if (payload.interviewDate !== undefined) {
      requestBody.interviewDate = payload.interviewDate
    }

    log('PATCH', `/candidatures/${id}`, requestBody, null)

    const response = await api.patchItem('candidatures', id, requestBody)
    const duration = performance.now() - startTime

    log('PATCH', `/candidatures/${id}`, requestBody, response.status)

    return {
      data: response.data,
      status: response.status,
    }
  } catch (error) {
    const duration = performance.now() - startTime
    log('PATCH', `/candidatures/${id}`, payload, error.response?.status, error)

    if (isHydraError(error.response?.data)) {
      const hydraError = parseHydraError(error.response.data)
      throw new Error(`[${error.response.status}] ${hydraError.title}: ${hydraError.description}`)
    }

    throw error
  }
}

/**
 * GET /api/savedOpportunities
 * Fetch student's saved opportunities with optional filtering
 *
 * Query params:
 * - studentId (number): Filter by student
 * - savedAfter (ISO datetime): Filter by save date
 * - savedBefore (ISO datetime): Filter by save date
 * - search (string): Search in opportunity title or company
 * - page (number): Page number (default: 1)
 * - limit (number): Items per page (default: 20)
 */
export async function getSavedOpportunities(filters = {}) {
  const startTime = performance.now()
  let params = {}

  try {
    if (filters.studentId) params.studentId = filters.studentId
    if (filters.savedAfter) params.savedAfter = filters.savedAfter
    if (filters.savedBefore) params.savedBefore = filters.savedBefore
    if (filters.search) params.search = filters.search
    if (filters.page) params.page = filters.page
    if (filters.limit) params.limit = filters.limit

    const response = await api.listItems('savedOpportunities', params)
    const duration = performance.now() - startTime

    log('GET', '/savedOpportunities', params, response.status)

    return {
      data: parseHydraCollection(response.data),
      raw: response.data,
      pagination: {
        total: response.data['hydra:totalItems'] || 0,
        view: response.data['hydra:view'] || {},
      },
      status: response.status,
    }
  } catch (error) {
    const duration = performance.now() - startTime
    log('GET', '/savedOpportunities', params, error.response?.status, error)

    if (isHydraError(error.response?.data)) {
      const hydraError = parseHydraError(error.response.data)
      throw new Error(`[${error.response.status}] ${hydraError.title}: ${hydraError.description}`)
    }

    throw error
  }
}

/**
 * POST /api/savedOpportunities
 * Save an opportunity for later (create or update)
 *
 * Request body:
 * - opportunityId (number | IRI string): Required
 * - studentId (number | IRI string): Required
 * - notes (string): Optional
 *
 * Response:
 * - 201: Created successfully
 * - 200: Already exists (idempotent - treat as success)
 * - 400: Invalid request
 *
 * This endpoint is idempotent:
 * - First call with same opportunityId + studentId returns 201
 * - Second call returns 200 with existing record
 * - BOTH are successful outcomes
 */
export async function saveopportunity(payload) {
  const startTime = performance.now()

  try {
    // Validate request
    validateSavedOpportunityRequest(payload)

    const requestBody = {
      opportunityId: payload.opportunityId,
      studentId: payload.studentId,
    }

    if (payload.notes !== undefined && payload.notes !== null) {
      requestBody.notes = payload.notes
    }

    log('POST', '/savedOpportunities', requestBody, null)

    const response = await api.createItem('savedOpportunities', requestBody)
    const duration = performance.now() - startTime

    // Treat both 200 and 201 as success (idempotent operation)
    const isSuccess = response.status === 200 || response.status === 201
    const statusLabel = response.status === 201 ? 'CREATED' : 'OK (Already Exists)'

    log('POST', '/savedOpportunities', requestBody, response.status)

    return {
      data: response.data,
      status: response.status,
      isNewRecord: response.status === 201,
      message: isSuccess ? `Opportunity saved (${statusLabel})` : null,
    }
  } catch (error) {
    const duration = performance.now() - startTime
    log('POST', '/savedOpportunities', payload, error.response?.status, error)

    if (isHydraError(error.response?.data)) {
      const hydraError = parseHydraError(error.response.data)
      throw new Error(`[${error.response.status}] ${hydraError.title}: ${hydraError.description}`)
    }

    throw error
  }
}

/**
 * DELETE /api/savedOpportunities/:id
 * Remove saved opportunity
 *
 * Response:
 * - 204: Deleted successfully (no content)
 * - 404: Not found
 */
export async function deleteSavedOpportunity(id) {
  const startTime = performance.now()

  try {
    if (!id) throw new Error('Saved Opportunity ID is required')

    log('DELETE', `/savedOpportunities/${id}`, null, null)

    const response = await api.deleteItem('savedOpportunities', id)
    const duration = performance.now() - startTime

    log('DELETE', `/savedOpportunities/${id}`, null, response.status)

    return {
      status: response.status,
      message: 'Opportunity removed from saved',
    }
  } catch (error) {
    const duration = performance.now() - startTime
    log('DELETE', `/savedOpportunities/${id}`, null, error.response?.status, error)

    if (isHydraError(error.response?.data)) {
      const hydraError = parseHydraError(error.response.data)
      throw new Error(`[${error.response.status}] ${hydraError.title}: ${hydraError.description}`)
    }

    throw error
  }
}

export default {
  getCandidatures,
  createCandidature,
  updateCandidature,
  getSavedOpportunities,
  saveopportunity,
  deleteSavedOpportunity,
}
