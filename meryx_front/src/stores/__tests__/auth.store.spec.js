import { beforeEach, describe, expect, it, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'

import api from '@/api/axios'
import { useAuthStore } from '@/stores/auth.store'

vi.mock('@/api/axios', () => ({
  default: {
    listItems: vi.fn(),
    createItem: vi.fn(),
  },
}))

describe('useAuthStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
    localStorage.clear()
  })

  it('normalizes role names from the backend and exposes the correct ROLE_* value', () => {
    const store = useAuthStore()
    store.applyAuthPayload({
      userTypeId: {
        name: 'Super Admin',
      },
    })

    expect(store.normalizedRole).toBe('ROLE_SUPER_ADMIN')
    store.applyAuthPayload({
      userTypeId: {
        name: 'Company',
      },
    })

    expect(store.normalizedRole).toBe('ROLE_COMPANY')
  })

  it('creates the address before the user and links the user to that address', async () => {
    api.createItem
      .mockResolvedValueOnce({ data: { id: 42 } })
      .mockResolvedValueOnce({ data: { id: 8 } })
      .mockResolvedValueOnce({ data: { id: 99 } })

    const store = useAuthStore()
    const companyData = {
      name: 'MeryX Group',
    }

    const result = await store.registerCompany(
      {
        email: 'contact@company.com',
        password: 'strongPassword1',
        phone: '+223 62 00 00 00',
        status: 'pending',
        isActived: true,
        userTypeId: '/api/user_types/2',
      },
      companyData,
      {
        city: 'Bamako',
        state: 'Bamako',
        country: 'Mali',
        latitude: '12.6392',
        longitude: '-8.0029',
      },
    )

    expect(api.createItem).toHaveBeenNthCalledWith(1, 'addresses', {
      city: 'Bamako',
      state: 'Bamako',
      country: 'Mali',
      latitude: '12.6392',
      longitude: '-8.0029',
    })

    expect(api.createItem).toHaveBeenNthCalledWith(
      2,
      'users',
      expect.objectContaining({
        AddressId: 42,
        addressId: 42,
        address: '/api/addresses/42',
      }),
    )

    expect(api.createItem).toHaveBeenNthCalledWith(3, 'companies', {
      name: 'MeryX Group',
      userId: 8,
      addressId: 42,
      AddressId: 42,
      email: 'contact@company.com',
    })

    expect(result).toEqual({ ok: true })
  })

  it('creates the university after the user and address are created', async () => {
    api.createItem
      .mockResolvedValueOnce({ data: { id: 51 } })
      .mockResolvedValueOnce({ data: { id: 12 } })
      .mockResolvedValueOnce({ data: { id: 77 } })

    const store = useAuthStore()
    const universityData = {
      name: 'University of Bamako',
      type: 'Public',
      description: 'A leading academic institution',
      websiteUrl: 'https://example.edu',
      registrationNumber: 'REG-UNI-1001',
      accreditationNumber: 'ACC-2025-900',
      rankingScore: 0,
      isApproved: false,
      status: 'pending',
    }

    const result = await store.registerUniversity(
      {
        email: 'contact@example.edu',
        password: 'strongPassword1',
        phone: '+223 61 00 00 00',
        status: 'pending',
        isActived: true,
        userTypeId: '/api/user_types/3',
      },
      universityData,
      {
        city: 'Bamako',
        state: 'Bamako',
        country: 'Mali',
        latitude: '12.6392',
        longitude: '-8.0029',
      },
    )

    expect(api.createItem).toHaveBeenNthCalledWith(1, 'addresses', {
      city: 'Bamako',
      state: 'Bamako',
      country: 'Mali',
      latitude: '12.6392',
      longitude: '-8.0029',
    })

    expect(api.createItem).toHaveBeenNthCalledWith(
      2,
      'users',
      expect.objectContaining({
        AddressId: 51,
        addressId: 51,
        address: '/api/addresses/51',
      }),
    )

    expect(api.createItem).toHaveBeenNthCalledWith(3, 'university', {
      ...universityData,
      userId: 12,
      addressId: 51,
      AddressId: 51,
      email: 'contact@example.edu',
    })

    expect(result).toEqual({ ok: true })
  })

  it('routes authenticated users to their interface even when the backend returns /unauthorized', async () => {
    api.createItem.mockResolvedValueOnce({
      data: {
        token: 'token-123',
        userTypeId: {
          name: 'Company',
        },
        redirectTarget: '/unauthorized',
      },
    })

    const store = useAuthStore()

    const result = await store.login({
      email: 'company@example.com',
      password: 'strongPassword1',
    })

    expect(result).toEqual({
      ok: true,
      status: 200,
      redirectTarget: '/company',
    })
    expect(store.resolveRouteByRole()).toBe('/company')
  })

  it('registers student when university email is missing but name uniquely matches', async () => {
    api.listItems.mockResolvedValueOnce({
      data: {
        'hydra:member': [
          {
            id: 2,
            name: 'fatim',
            status: 'approved',
          },
        ],
      },
    })

    api.createItem
      .mockResolvedValueOnce({ data: { id: 101 } })
      .mockResolvedValueOnce({ data: { id: 202 } })

    const store = useAuthStore()
    const result = await store.registerStudent(
      {
        email: 'student@example.com',
        password: 'strongPassword1',
        phone: '+223 70 11 22 33',
        status: 'pending',
        isActived: true,
        userTypeId: '/api/user_types/4',
      },
      {
        fullName: 'Student Name',
        universityName: 'fatim',
        universityEmail: 'fatim@gmail.com',
        program: 'Informatique',
      },
    )

    expect(result).toEqual({ ok: true })
    expect(api.listItems).toHaveBeenCalledWith('university')
    expect(api.createItem).toHaveBeenNthCalledWith(
      1,
      'users',
      expect.objectContaining({
        universityId: '/api/university/2',
        universityName: 'fatim',
      }),
    )
    expect(api.createItem).toHaveBeenNthCalledWith(
      2,
      'studentProfiles',
      expect.objectContaining({
        universityId: '/api/university/2',
      }),
    )
  })

  it('rejects student registration when university name is ambiguous and email is missing', async () => {
    api.listItems.mockResolvedValueOnce({
      data: {
        'hydra:member': [
          {
            id: 2,
            name: 'fatim',
            status: 'approved',
          },
          {
            id: 7,
            name: 'fatim',
            status: 'approved',
          },
        ],
      },
    })

    const store = useAuthStore()
    const result = await store.registerStudent(
      {
        email: 'student@example.com',
        password: 'strongPassword1',
        phone: '+223 70 11 22 33',
        status: 'pending',
        isActived: true,
        userTypeId: '/api/user_types/4',
      },
      {
        fullName: 'Student Name',
        universityName: 'fatim',
        universityEmail: 'fatim@gmail.com',
        program: 'Informatique',
      },
    )

    expect(result.ok).toBe(false)
    expect(result.message).toContain('Universite introuvable')
  })
})
