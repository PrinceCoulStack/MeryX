/**
 * JSON-LD Hydra response and error parser
 * Handles Hydra collections and error formats from backend
 */

/**
 * Check if response is a Hydra error
 */
export function isHydraError(data) {
  if (!data || typeof data !== 'object') return false

  return (
    data['@type'] === 'Error' ||
    data['@context']?.includes('/contexts/Error') ||
    (data.status && data.status >= 400)
  )
}

/**
 * Parse Hydra error response
 * Returns normalized error object with title, description
 */
export function parseHydraError(data) {
  if (!isHydraError(data)) {
    throw new Error('Data is not a Hydra error response')
  }

  return {
    type: data['@type'] || 'Error',
    id: data['@id'] || 'unknown',
    title: data.title || data['hydra:title'] || 'An error occurred',
    description: data.description || data['hydra:description'] || data.detail || 'Unknown error',
    status: data.status || 500,
    detail: data.detail || null,
    trace: data.trace || null,
  }
}

/**
 * Parse Hydra collection response
 * Returns array of members from hydra:member or direct array
 * Handles nested @type and @id fields
 */
export function parseHydraCollection(data) {
  if (!data || typeof data !== 'object') {
    return []
  }

  // If it's an error response, return empty array
  if (isHydraError(data)) {
    return []
  }

  // Handle hydra:member format (most common)
  if (Array.isArray(data['hydra:member'])) {
    return data['hydra:member'].map(normalizeHydraItem)
  }

  // Handle direct array
  if (Array.isArray(data)) {
    return data.map(normalizeHydraItem)
  }

  // Handle single item response
  if (data['@type'] && data.id) {
    return [normalizeHydraItem(data)]
  }

  return []
}

/**
 * Normalize a single Hydra item
 * Removes @context, @type, @id and returns clean object
 */
function normalizeHydraItem(item) {
  if (!item || typeof item !== 'object') return item

  const normalized = { ...item }

  // Keep Hydra metadata but on separate keys if needed for debugging
  if (import.meta.env.DEV) {
    normalized._hydra = {
      type: item['@type'],
      id: item['@id'],
      context: item['@context'],
    }
  }

  return normalized
}

/**
 * Extract pagination info from Hydra response
 */
export function parseHydraPagination(data) {
  if (!data || typeof data !== 'object') {
    return {
      total: 0,
      page: 1,
      limit: 20,
      hasNext: false,
      hasPrev: false,
      firstUrl: null,
      lastUrl: null,
      nextUrl: null,
      prevUrl: null,
    }
  }

  const total = data['hydra:totalItems'] || 0
  const view = data['hydra:view'] || {}

  return {
    total,
    page: view['@id']?.includes('page=') ? parseInt(view['@id'].split('page=')[1]) : 1,
    limit: view['hydra:itemsPerPage'] || 20,
    hasNext: !!view['hydra:next'],
    hasPrev: !!view['hydra:previous'],
    firstUrl: view['hydra:first'],
    lastUrl: view['hydra:last'],
    nextUrl: view['hydra:next'],
    prevUrl: view['hydra:previous'],
  }
}

/**
 * Extract nested resource from Hydra item
 * Example: item.opportunity or item.student
 */
export function getNestedResource(item, resourceKey) {
  if (!item || typeof item !== 'object') return null

  const resource = item[resourceKey]

  if (!resource || typeof resource !== 'object') {
    return null
  }

  // If it's an IRI string (starts with /api/), return null
  // (backend should return full object, not IRI reference)
  if (typeof resource === 'string') {
    return null
  }

  return resource
}

/**
 * Extract ID from Hydra IRI string
 * Example: "/api/opportunities/123" -> 123
 */
export function extractIdFromIri(iri) {
  if (!iri || typeof iri !== 'string') return null

  const parts = iri.split('/')
  const lastPart = parts[parts.length - 1]

  const id = parseInt(lastPart, 10)
  return isNaN(id) ? null : id
}

/**
 * Build IRI string from resource type and ID
 * Example: buildIri("opportunities", 123) -> "/api/opportunities/123"
 */
export function buildIri(resourceType, id) {
  if (!resourceType || !id) return null

  return `/api/${resourceType}/${id}`
}

export default {
  isHydraError,
  parseHydraError,
  parseHydraCollection,
  parseHydraPagination,
  getNestedResource,
  extractIdFromIri,
  buildIri,
}
