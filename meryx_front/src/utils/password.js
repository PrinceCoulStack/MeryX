// Shared password policy: min 8 characters, at least 1 letter and 1 digit (matches backend rule).
export const PASSWORD_POLICY_HINT = 'At least 8 characters, with 1 letter and 1 digit.'

export const isPasswordValid = (password) =>
  typeof password === 'string' &&
  password.length >= 8 &&
  /[A-Za-z]/.test(password) &&
  /\d/.test(password)

export const validatePassword = (password) => {
  if (!password) return 'Password is required.'
  if (!isPasswordValid(password)) return PASSWORD_POLICY_HINT
  return ''
}

// Surfaces backend 422 field errors / 403 "reason" / generic message from an Axios error.
export const extractApiErrorMessage = (error, fallback = 'Something went wrong. Please retry.') => {
  const data = error?.response?.data
  if (!data) return error?.message || fallback

  if (data.reason) return data.reason
  if (data.errors && typeof data.errors === 'object') {
    const firstField = Object.values(data.errors)[0]
    const firstMessage = Array.isArray(firstField) ? firstField[0] : firstField
    if (firstMessage) return firstMessage
  }
  return data.detail || data.message || data['hydra:description'] || fallback
}
