/**
 * Request payload validators for Candidature API
 * Fail fast with clear error messages before sending to backend
 */

/**
 * Validate candidature creation request
 * Required: opportunityId, studentId
 * Optional: notes
 */
export function validateCandidatureRequest(payload) {
  if (!payload || typeof payload !== 'object') {
    throw new Error('Candidature request payload must be an object')
  }

  // Check required fields
  if (!payload.opportunityId && payload.opportunityId !== 0) {
    throw new Error('opportunityId is required for candidature request')
  }

  if (!payload.studentId && payload.studentId !== 0) {
    throw new Error('studentId is required for candidature request')
  }

  // Validate types
  const opportunityIdType = typeof payload.opportunityId
  const studentIdType = typeof payload.studentId

  if (!['number', 'string'].includes(opportunityIdType)) {
    throw new Error(`opportunityId must be a number or string (IRI), got ${opportunityIdType}`)
  }

  if (!['number', 'string'].includes(studentIdType)) {
    throw new Error(`studentId must be a number or string (IRI), got ${studentIdType}`)
  }

  // If IRI string, validate format
  if (typeof payload.opportunityId === 'string' && !payload.opportunityId.startsWith('/api/')) {
    throw new Error(
      `opportunityId IRI string must start with '/api/', got: ${payload.opportunityId}`,
    )
  }

  if (typeof payload.studentId === 'string' && !payload.studentId.startsWith('/api/')) {
    throw new Error(`studentId IRI string must start with '/api/', got: ${payload.studentId}`)
  }

  // Validate optional notes
  if (payload.notes !== undefined && payload.notes !== null && typeof payload.notes !== 'string') {
    throw new Error(`notes must be a string, got ${typeof payload.notes}`)
  }

  return true
}

/**
 * Validate saved opportunity creation request
 * Required: opportunityId, studentId
 * Optional: notes
 */
export function validateSavedOpportunityRequest(payload) {
  if (!payload || typeof payload !== 'object') {
    throw new Error('Saved opportunity request payload must be an object')
  }

  // Check required fields
  if (!payload.opportunityId && payload.opportunityId !== 0) {
    throw new Error('opportunityId is required for saved opportunity request')
  }

  if (!payload.studentId && payload.studentId !== 0) {
    throw new Error('studentId is required for saved opportunity request')
  }

  // Validate types
  const opportunityIdType = typeof payload.opportunityId
  const studentIdType = typeof payload.studentId

  if (!['number', 'string'].includes(opportunityIdType)) {
    throw new Error(`opportunityId must be a number or string (IRI), got ${opportunityIdType}`)
  }

  if (!['number', 'string'].includes(studentIdType)) {
    throw new Error(`studentId must be a number or string (IRI), got ${studentIdType}`)
  }

  // If IRI string, validate format
  if (typeof payload.opportunityId === 'string' && !payload.opportunityId.startsWith('/api/')) {
    throw new Error(
      `opportunityId IRI string must start with '/api/', got: ${payload.opportunityId}`,
    )
  }

  if (typeof payload.studentId === 'string' && !payload.studentId.startsWith('/api/')) {
    throw new Error(`studentId IRI string must start with '/api/', got: ${payload.studentId}`)
  }

  // Validate optional notes
  if (payload.notes !== undefined && payload.notes !== null && typeof payload.notes !== 'string') {
    throw new Error(`notes must be a string, got ${typeof payload.notes}`)
  }

  return true
}

/**
 * Validate status transition according to backend rules
 * Allowed transitions:
 * - applied -> interview or rejected
 * - interview -> offer or rejected
 * - offer -> accepted or rejected
 * - accepted -> (terminal)
 * - rejected -> (terminal)
 */
export function validateStatusTransition(currentStatus, newStatus) {
  const allowedTransitions = {
    applied: ['interview', 'rejected'],
    interview: ['offer', 'rejected'],
    offer: ['accepted', 'rejected'],
    accepted: [],
    rejected: [],
  }

  if (!allowedTransitions[currentStatus]) {
    throw new Error(`Unknown current status: ${currentStatus}`)
  }

  if (!allowedTransitions[newStatus]) {
    throw new Error(`Unknown new status: ${newStatus}`)
  }

  if (!allowedTransitions[currentStatus].includes(newStatus)) {
    throw new Error(
      `Invalid transition from '${currentStatus}' to '${newStatus}'. Allowed: ${allowedTransitions[currentStatus].join(', ') || 'none (terminal)'}`,
    )
  }

  return true
}

/**
 * Validate status value
 */
export function validateStatus(status) {
  const validStatuses = ['applied', 'interview', 'offer', 'accepted', 'rejected']

  if (!validStatuses.includes(status)) {
    throw new Error(`Invalid status: ${status}. Must be one of: ${validStatuses.join(', ')}`)
  }

  return true
}

/**
 * Validate ID (must be positive number)
 */
export function validateId(id) {
  const numId = Number(id)

  if (!Number.isInteger(numId) || numId <= 0) {
    throw new Error(`Invalid ID: must be a positive integer, got ${id}`)
  }

  return numId
}

/**
 * Validate ISO 8601 datetime string
 */
export function validateISODateTime(dateString) {
  if (!dateString) return true // Optional

  const date = new Date(dateString)
  if (isNaN(date.getTime())) {
    throw new Error(`Invalid ISO 8601 datetime: ${dateString}`)
  }

  return dateString
}

export default {
  validateCandidatureRequest,
  validateSavedOpportunityRequest,
  validateStatusTransition,
  validateStatus,
  validateId,
  validateISODateTime,
}
