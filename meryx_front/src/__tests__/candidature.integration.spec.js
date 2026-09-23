/**
 * Candidature API Integration Smoke Tests
 * Tests critical workflows:
 * 1. Apply for opportunity success
 * 2. Duplicate candidature handling (409 conflict)
 * 3. Save opportunity with idempotency (201 then 200)
 * 4. Fetch and parse Hydra collections
 * 5. Update candidature status with validation
 * 6. Delete saved opportunity
 */

import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest'
import {
  getCandidatures,
  createCandidature,
  updateCandidature,
  getSavedOpportunities,
  saveopportunity,
  deleteSavedOpportunity,
} from '@/api/candidatureApiClient'
import { parseHydraError, isHydraError } from '@/utils/hydraParser'
import { validateStatusTransition } from '@/api/validators'

// Mock axios
vi.mock('@/api/axios', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
    patch: vi.fn(),
    delete: vi.fn(),
  },
}))

import api from '@/api/axios'

const MOCK_STUDENT_ID = 1
const MOCK_OPPORTUNITY_ID = 42
const MOCK_CANDIDATURE_ID = 100
const MOCK_SAVED_ID = 200

const mockCandidatureResponse = {
  id: MOCK_CANDIDATURE_ID,
  opportunityId: MOCK_OPPORTUNITY_ID,
  studentId: MOCK_STUDENT_ID,
  status: 'applied',
  notes: 'Very interested',
  createdAt: '2026-01-15T10:30:00Z',
  updatedAt: '2026-01-15T10:30:00Z',
}

const mockSavedOpportunityResponse = {
  id: MOCK_SAVED_ID,
  opportunityId: MOCK_OPPORTUNITY_ID,
  studentId: MOCK_STUDENT_ID,
  notes: 'Interesting role',
  savedDate: '2026-01-14T14:20:00Z',
}

const mockHydraCollection = {
  '@context': '/api/contexts/Candidature',
  '@id': '/api/candidatures',
  '@type': 'hydra:Collection',
  'hydra:member': [mockCandidatureResponse],
  'hydra:totalItems': 1,
  'hydra:view': {
    '@id': '/api/candidatures?page=1',
    '@type': 'hydra:PartialCollectionView',
    'hydra:first': '/api/candidatures?page=1',
    'hydra:last': '/api/candidatures?page=1',
  },
}

const mockHydraError = {
  '@context': '/api/contexts/Error',
  '@id': '/api/errors/400',
  '@type': 'Error',
  title: 'Invalid Request',
  description: 'The request body is malformed',
  status: 400,
  detail: 'Field validation failed',
}

describe('Candidature API Integration Tests', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  describe('POST /api/candidatures - Apply for Opportunity', () => {
    it('should create candidature with valid payload', async () => {
      api.post.mockResolvedValueOnce({
        data: mockCandidatureResponse,
        status: 201,
      })

      const result = await createCandidature({
        opportunityId: MOCK_OPPORTUNITY_ID,
        studentId: MOCK_STUDENT_ID,
        notes: 'Very interested',
      })

      expect(result.status).toBe(201)
      expect(result.data.id).toBe(MOCK_CANDIDATURE_ID)
      expect(result.data.status).toBe('applied')
      expect(api.post).toHaveBeenCalledWith(
        '/candidatures',
        expect.objectContaining({
          opportunityId: MOCK_OPPORTUNITY_ID,
          studentId: MOCK_STUDENT_ID,
          notes: 'Very interested',
        }),
      )
    })

    it('should reject with missing required fields', async () => {
      const invalidPayloads = [
        { studentId: MOCK_STUDENT_ID }, // missing opportunityId
        { opportunityId: MOCK_OPPORTUNITY_ID }, // missing studentId
        { opportunityId: null, studentId: MOCK_STUDENT_ID }, // null opportunity
        {}, // empty object
      ]

      for (const payload of invalidPayloads) {
        await expect(createCandidature(payload)).rejects.toThrow()
      }
    })

    it('should handle 409 conflict (already applied) with Hydra error', async () => {
      const conflictError = new Error('Conflict')
      conflictError.response = {
        status: 409,
        data: {
          '@context': '/api/contexts/Error',
          '@type': 'Error',
          title: 'Conflict',
          description: 'You have already applied to this opportunity',
          status: 409,
        },
      }

      api.post.mockRejectedValueOnce(conflictError)

      await expect(
        createCandidature({
          opportunityId: MOCK_OPPORTUNITY_ID,
          studentId: MOCK_STUDENT_ID,
        }),
      ).rejects.toThrow('[409]')
    })

    it('should handle Hydra error responses', async () => {
      const error = new Error('Bad Request')
      error.response = {
        status: 400,
        data: mockHydraError,
      }

      api.post.mockRejectedValueOnce(error)

      await expect(
        createCandidature({
          opportunityId: MOCK_OPPORTUNITY_ID,
          studentId: MOCK_STUDENT_ID,
        }),
      ).rejects.toThrow('Invalid Request')
    })
  })

  describe('POST /api/savedOpportunities - Idempotent Save', () => {
    it('should return 201 on first save', async () => {
      api.post.mockResolvedValueOnce({
        data: mockSavedOpportunityResponse,
        status: 201,
      })

      const result = await saveopportunity({
        opportunityId: MOCK_OPPORTUNITY_ID,
        studentId: MOCK_STUDENT_ID,
        notes: 'Interesting role',
      })

      expect(result.status).toBe(201)
      expect(result.isNewRecord).toBe(true)
      expect(result.message).toContain('CREATED')
    })

    it('should return 200 when already saved (idempotent)', async () => {
      api.post.mockResolvedValueOnce({
        data: mockSavedOpportunityResponse,
        status: 200,
      })

      const result = await saveopportunity({
        opportunityId: MOCK_OPPORTUNITY_ID,
        studentId: MOCK_STUDENT_ID,
        notes: 'Interesting role',
      })

      expect(result.status).toBe(200)
      expect(result.isNewRecord).toBe(false)
      expect(result.message).toContain('Already Exists')
    })

    it('should treat both 200 and 201 as success', async () => {
      // Test with 201
      api.post.mockResolvedValueOnce({
        data: mockSavedOpportunityResponse,
        status: 201,
      })

      const result1 = await saveopportunity({
        opportunityId: MOCK_OPPORTUNITY_ID,
        studentId: MOCK_STUDENT_ID,
      })
      expect([200, 201]).toContain(result1.status)

      // Test with 200
      api.post.mockResolvedValueOnce({
        data: mockSavedOpportunityResponse,
        status: 200,
      })

      const result2 = await saveopportunity({
        opportunityId: MOCK_OPPORTUNITY_ID,
        studentId: MOCK_STUDENT_ID,
      })
      expect([200, 201]).toContain(result2.status)
    })
  })

  describe('GET /api/candidatures - Fetch with Hydra Collection', () => {
    it('should parse Hydra collection response', async () => {
      api.get.mockResolvedValueOnce({
        data: mockHydraCollection,
        status: 200,
      })

      const result = await getCandidatures({
        studentId: MOCK_STUDENT_ID,
      })

      expect(result.status).toBe(200)
      expect(Array.isArray(result.data)).toBe(true)
      expect(result.data.length).toBe(1)
      expect(result.data[0].id).toBe(MOCK_CANDIDATURE_ID)
      expect(result.pagination.total).toBe(1)
    })

    it('should support filter params', async () => {
      api.get.mockResolvedValueOnce({
        data: mockHydraCollection,
        status: 200,
      })

      await getCandidatures({
        studentId: MOCK_STUDENT_ID,
        status: 'applied',
        page: 1,
        limit: 20,
      })

      expect(api.get).toHaveBeenCalledWith('/candidatures', {
        params: expect.objectContaining({
          studentId: MOCK_STUDENT_ID,
          status: 'applied',
          page: 1,
          limit: 20,
        }),
      })
    })

    it('should handle empty collections', async () => {
      api.get.mockResolvedValueOnce({
        data: {
          '@context': '/api/contexts/Candidature',
          'hydra:member': [],
          'hydra:totalItems': 0,
        },
        status: 200,
      })

      const result = await getCandidatures({ studentId: MOCK_STUDENT_ID })

      expect(result.data).toEqual([])
      expect(result.pagination.total).toBe(0)
    })
  })

  describe('PATCH /api/candidatures/:id - Status Update', () => {
    it('should update status with validation', async () => {
      api.patch.mockResolvedValueOnce({
        data: {
          ...mockCandidatureResponse,
          status: 'interview',
        },
        status: 200,
      })

      const result = await updateCandidature(MOCK_CANDIDATURE_ID, {
        status: 'interview',
        feedback: 'Great candidate',
      })

      expect(result.status).toBe(200)
      expect(result.data.status).toBe('interview')
      expect(api.patch).toHaveBeenCalledWith(
        `/candidatures/${MOCK_CANDIDATURE_ID}`,
        expect.objectContaining({
          status: 'interview',
          feedback: 'Great candidate',
        }),
      )
    })

    it('should reject invalid status values', async () => {
      await expect(
        updateCandidature(MOCK_CANDIDATURE_ID, {
          status: 'invalid_status',
        }),
      ).rejects.toThrow('Invalid status')
    })

    it('should handle 404 not found', async () => {
      const error = new Error('Not Found')
      error.response = {
        status: 404,
        data: {
          '@context': '/api/contexts/Error',
          '@type': 'Error',
          title: 'Not Found',
          description: 'Candidature not found',
          status: 404,
        },
      }

      api.patch.mockRejectedValueOnce(error)

      await expect(updateCandidature(999, { status: 'interview' })).rejects.toThrow()
    })
  })

  describe('DELETE /api/savedOpportunities/:id', () => {
    it('should delete saved opportunity', async () => {
      api.delete.mockResolvedValueOnce({
        status: 204,
      })

      const result = await deleteSavedOpportunity(MOCK_SAVED_ID)

      expect(result.status).toBe(204)
      expect(api.delete).toHaveBeenCalledWith(`/savedOpportunities/${MOCK_SAVED_ID}`)
    })

    it('should handle 404 when not found', async () => {
      const error = new Error('Not Found')
      error.response = {
        status: 404,
        data: mockHydraError,
      }

      api.delete.mockRejectedValueOnce(error)

      await expect(deleteSavedOpportunity(999)).rejects.toThrow()
    })
  })

  describe('Status Transition Validation', () => {
    it('should allow valid transitions', () => {
      const validTransitions = [
        { from: 'applied', to: 'interview' },
        { from: 'applied', to: 'rejected' },
        { from: 'interview', to: 'offer' },
        { from: 'interview', to: 'rejected' },
        { from: 'offer', to: 'accepted' },
        { from: 'offer', to: 'rejected' },
      ]

      validTransitions.forEach(({ from, to }) => {
        expect(() => validateStatusTransition(from, to)).not.toThrow()
      })
    })

    it('should reject invalid transitions', () => {
      const invalidTransitions = [
        { from: 'applied', to: 'accepted' }, // skip steps
        { from: 'interview', to: 'applied' }, // backwards
        { from: 'accepted', to: 'rejected' }, // terminal state
        { from: 'rejected', to: 'applied' }, // terminal state
      ]

      invalidTransitions.forEach(({ from, to }) => {
        expect(() => validateStatusTransition(from, to)).toThrow()
      })
    })
  })

  describe('Hydra Error Parsing', () => {
    it('should identify Hydra errors', () => {
      expect(isHydraError(mockHydraError)).toBe(true)
      expect(isHydraError({ id: 123, name: 'Test' })).toBe(false)
      expect(isHydraError(null)).toBe(false)
    })

    it('should parse Hydra error details', () => {
      const parsed = parseHydraError(mockHydraError)

      expect(parsed.title).toBe('Invalid Request')
      expect(parsed.description).toContain('malformed')
      expect(parsed.status).toBe(400)
      expect(parsed.type).toBe('Error')
    })
  })

  describe('GET /api/savedOpportunities - Fetch Saved', () => {
    it('should fetch and parse saved opportunities', async () => {
      api.get.mockResolvedValueOnce({
        data: {
          '@context': '/api/contexts/SavedOpportunity',
          'hydra:member': [mockSavedOpportunityResponse],
          'hydra:totalItems': 1,
        },
        status: 200,
      })

      const result = await getSavedOpportunities({
        studentId: MOCK_STUDENT_ID,
      })

      expect(result.status).toBe(200)
      expect(result.data.length).toBe(1)
      expect(result.data[0].id).toBe(MOCK_SAVED_ID)
    })

    it('should support search filter', async () => {
      api.get.mockResolvedValueOnce({
        data: { 'hydra:member': [], 'hydra:totalItems': 0 },
        status: 200,
      })

      await getSavedOpportunities({
        search: 'software engineer',
      })

      expect(api.get).toHaveBeenCalledWith('/savedOpportunities', {
        params: expect.objectContaining({
          search: 'software engineer',
        }),
      })
    })
  })
})
