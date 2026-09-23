import { beforeEach, describe, expect, it, vi } from 'vitest'
import api from '@/api/axios'
import { useCompanyTrainings } from '@/compasables/useCompanyTrainings'

vi.mock('@/api/axios', () => ({
  default: {
    listItems: vi.fn(),
    createItem: vi.fn(),
    updateItem: vi.fn(),
  },
}))

describe('useCompanyTrainings', () => {
  beforeEach(() => {
    localStorage.clear()
    vi.clearAllMocks()
  })

  it('does not send a fake company id when the logged user has no company relation', async () => {
    localStorage.setItem('user', JSON.stringify({ id: 99, email: 'company@example.com' }))

    const { createTraining, getCurrentCompanyId } = useCompanyTrainings()

    expect(getCurrentCompanyId()).toBe(0)

    const result = await createTraining({
      title: 'Safety Bootcamp',
      description: 'Must be trained safely',
    })

    expect(result).toBeNull()
    expect(api.createItem).not.toHaveBeenCalled()
  })
})
