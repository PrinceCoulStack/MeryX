import { describe, expect, it, vi, beforeEach } from 'vitest'
import api from '@/api/axios'
import { useCompanyOpportunities } from '@/compasables/useCompanyOpportunities'

vi.mock('@/api/axios', () => ({
  default: {
    listItems: vi.fn(),
    createItem: vi.fn(),
  },
}))

describe('useCompanyOpportunities', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('returns the matching opportunity by id after fetching the collection', async () => {
    api.listItems.mockResolvedValue({
      data: {
        'hydra:member': [
          {
            id: 7,
            title: 'Frontend Internship',
            companyId: '/api/companies/3',
            type: 'Internship',
            department: 'Engineering',
            location: 'Remote',
            remoteType: 'Remote',
            salaryLabel: 'Stipend',
            description: 'Build ui',
            status: 'Open',
            publishedAt: '2026-08-20T00:00:00Z',
            createdAt: '2026-08-20T00:00:00Z',
          },
        ],
      },
    })

    const { fetchOpportunities, getOpportunityById } = useCompanyOpportunities()

    await fetchOpportunities()

    expect(getOpportunityById(7)).toMatchObject({
      id: 7,
      title: 'Frontend Internship',
      companyId: 3,
      type: 'Internship',
      status: 'Open',
    })
  })
})
