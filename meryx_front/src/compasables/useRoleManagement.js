import { ref } from 'vue'
import api from '@/api/axios'

const roles = ref([])
const users = ref([])
const isLoadingRoles = ref(false)
const isLoadingUsers = ref(false)
const lastRoleError = ref('')

let rolesLoaded = false
let usersLoaded = false

const normalizeApiData = (data) => {
  if (typeof data !== 'string') return data

  try {
    return JSON.parse(data)
  } catch {
    return data
  }
}

const extractCollection = (data) => {
  const normalized = normalizeApiData(data)

  if (Array.isArray(normalized)) return normalized
  if (Array.isArray(normalized?.['hydra:member'])) return normalized['hydra:member']
  if (Array.isArray(normalized?.member)) return normalized.member
  if (Array.isArray(normalized?.items)) return normalized.items
  return []
}

const buildUserName = (item) => {
  const firstName = item.firstName || item.firstname || ''
  const lastName = item.lastName || item.lastname || ''
  const fullName = `${firstName} ${lastName}`.trim()
  return fullName || item.name || item.email || `User #${item.id}`
}

const mapApiRole = (item) => ({
  id: Number(item.id),
  name: item.name || '',
  description: item.description || '',
  permissions: Array.isArray(item.permission)
    ? item.permission
    : Array.isArray(item.permissions)
      ? item.permissions
      : [],
  assignedUserIds: Array.isArray(item.assignedUserIds)
    ? item.assignedUserIds.map((value) => Number(value))
    : [],
  isEnabled: typeof item.isEnabled === 'boolean' ? item.isEnabled : true,
  createdAt: item.createdAt || null,
})

const mapApiUser = (item) => ({
  id: Number(item.id),
  name: buildUserName(item),
  email: item.email || '',
  status: item.isEnabled === false || item.isActive === false ? 'inactive' : 'active',
})

const upsertRole = (role) => {
  const index = roles.value.findIndex((item) => item.id === role.id)
  if (index === -1) {
    roles.value.push(role)
    return role
  }

  roles.value[index] = {
    ...roles.value[index],
    ...role,
  }

  return roles.value[index]
}

const getApiErrorMessage = (error, fallback) => {
  const status = error?.response?.status
  const payload = normalizeApiData(error?.response?.data)
  const detail = payload?.detail || payload?.message || error?.message

  if (status && detail) return `[${status}] ${detail}`
  if (status) return `[${status}] ${fallback}`
  if (detail) return detail
  return fallback
}

const getRoleById = (id) => roles.value.find((role) => role.id === Number(id)) || null

const fetchRoles = async (force = false) => {
  if (rolesLoaded && !force) return roles.value

  isLoadingRoles.value = true
  lastRoleError.value = ''
  try {
    const response = await api.listItems('userTypes')
    roles.value = extractCollection(response.data).map(mapApiRole)
    rolesLoaded = true
    return roles.value
  } catch (error) {
    const message = getApiErrorMessage(error, 'Unable to load roles.')
    lastRoleError.value = message
    // throw new Error(message)
  } finally {
    isLoadingRoles.value = false
  }
}

const fetchUsers = async (force = false) => {
  if (usersLoaded && !force) return users.value

  isLoadingUsers.value = true
  try {
    const response = await api.listItems('users')
    users.value = extractCollection(response.data).map(mapApiUser)
    usersLoaded = true
    return users.value
  } catch {
    users.value = []
    usersLoaded = true
    return users.value
  } finally {
    isLoadingUsers.value = false
  }
}

const createRole = async ({ name, description, permissions = [], isEnabled = true }) => {
  const payload = {
    name: name.trim(),
    description: description.trim(),
    permission: [...new Set(permissions)],
    isEnabled,
  }

  const response = await api.createItem('userTypes', payload)
  const createdRole = mapApiRole(response.data)
  return upsertRole(createdRole)
}

const updateRole = async (id, payload) => {
  const current = getRoleById(id)
  if (!current) return null

  const request = {
    name: payload.name?.trim() ?? current.name,
    description: payload.description?.trim() ?? current.description,
    permission: Array.isArray(payload.permissions) ? payload.permissions : current.permissions,
    isEnabled: typeof payload.isEnabled === 'boolean' ? payload.isEnabled : current.isEnabled,
  }

  const response = await api.updateItem('userTypes', id, request)
  const updatedRole = mapApiRole(response.data)
  updatedRole.assignedUserIds = current.assignedUserIds
  return upsertRole(updatedRole)
}

const setRoleAssignments = (id, userIds) => {
  const role = getRoleById(id)
  if (!role) return null

  role.assignedUserIds = [...new Set(userIds.map((userId) => Number(userId)))]
  return role
}

const saveRoleAssignments = async (id, userIds) => {
  const role = getRoleById(id)
  if (!role) return { ok: false, message: 'Role not found.' }

  const selectedUserIds = [...new Set(userIds.map((userId) => Number(userId)))]

  try {
    await Promise.all(
      selectedUserIds.map((userId) =>
        api.updateItem('users', userId, {
          userTypeId: `/api/user_types/${id}`,
        }),
      ),
    )

    role.assignedUserIds = selectedUserIds
    return { ok: true }
  } catch (error) {
    return {
      ok: false,
      message: getApiErrorMessage(error, 'Unable to save role assignments.'),
    }
  }
}

const assignRoleToAllUsers = (id) => {
  const role = getRoleById(id)
  if (!role) return null

  role.assignedUserIds = users.value.map((user) => user.id)
  return role
}

const removeRole = async (id) => {
  await api.deleteItem('userTypes', id)
  roles.value = roles.value.filter((role) => role.id !== Number(id))
  return true
}

export const useRoleManagement = () => ({
  roles,
  users,
  isLoadingRoles,
  isLoadingUsers,
  lastRoleError,
  fetchRoles,
  fetchUsers,
  getRoleById,
  createRole,
  updateRole,
  setRoleAssignments,
  saveRoleAssignments,
  assignRoleToAllUsers,
  removeRole,
})
