import { beforeEach, describe, expect, it, vi } from 'vitest'

import api from '@/api/axios'
import { useRoleManagement } from '@/compasables/useRoleManagement'

vi.mock('@/api/axios', () => ({
  default: {
    listItems: vi.fn(),
    updateItem: vi.fn(),
  },
}))

describe('useRoleManagement', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('persists the selected role to each user via userTypeId', async () => {
    api.listItems.mockResolvedValueOnce({
      data: {
        'hydra:member': [
          {
            id: 2,
            name: 'Company',
            description: 'Company role',
            permissions: [],
            assignedUserIds: [],
          },
        ],
      },
    })

    api.listItems.mockResolvedValueOnce({
      data: {
        'hydra:member': [
          { id: 10, email: 'a@test.com' },
          { id: 11, email: 'b@test.com' },
        ],
      },
    })

    api.updateItem.mockResolvedValue({ data: {} })

    const { fetchRoles, fetchUsers, saveRoleAssignments } = useRoleManagement()

    await fetchRoles()
    await fetchUsers()

    const result = await saveRoleAssignments(2, [10, 11])

    expect(result.ok).toBe(true)
    expect(api.updateItem).toHaveBeenCalledTimes(2)
    expect(api.updateItem).toHaveBeenNthCalledWith(
      1,
      'users',
      10,
      expect.objectContaining({
        userTypeId: '/api/user_types/2',
      }),
    )
    expect(api.updateItem).toHaveBeenNthCalledWith(
      2,
      'users',
      11,
      expect.objectContaining({
        userTypeId: '/api/user_types/2',
      }),
    )
  })
})
