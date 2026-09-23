import { computed, ref } from 'vue'
import { storeToRefs } from 'pinia'
import { useStudentStore } from '@/stores/student.store'

let studentStore = null
let storeStudents = null
let storeLoading = null
let storeError = null

const ensureStudentStore = () => {
  if (studentStore && storeStudents && storeLoading && storeError) {
    return { studentStore, storeStudents, storeLoading, storeError }
  }

  studentStore = useStudentStore()
  const refs = storeToRefs(studentStore)
  storeStudents = refs.students
  storeLoading = refs.loading
  storeError = refs.error

  return { studentStore, storeStudents, storeLoading, storeError }
}

const hasLoaded = ref(false)
const loading = ref(false)
const error = ref('')

const safeDate = (value) => {
  if (!value) return null
  const date = new Date(value)
  return Number.isNaN(date.getTime()) ? null : date
}

const formatDateTime = (value) => {
  const date = safeDate(value)
  if (!date) return '-'
  return date.toLocaleString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const normalizeStatus = (status) => {
  const normalized = String(status || '')
    .trim()
    .toLowerCase()
  if (!normalized) return 'inactive'
  if (normalized === 'active' || normalized === 'approved') return 'active'
  if (normalized.includes('approve')) return 'active'
  return 'inactive'
}

const deriveUsage = (student) => {
  const profileCompletion = Number(student.profileCompletion || 0)
  const applications = Number(student.applicationCount || 0)
  const sessionsLast30Days = Math.max(1, Math.round(profileCompletion / 4 + applications * 2))
  const avgSessionMinutes = Math.max(5, Math.round(8 + profileCompletion / 15))
  const actionsLast30Days = Math.max(
    sessionsLast30Days,
    Math.round(sessionsLast30Days * avgSessionMinutes * 0.9 + applications * 18),
  )

  return {
    sessionsLast30Days,
    avgSessionMinutes,
    actionsLast30Days,
    lastSeenAt: formatDateTime(student.updatedAt || student.createdAt || student.raw?.updatedAt),
  }
}

const toAdminStudent = (student) => ({
  id: student.id,
  fullName: student.fullName || 'Student',
  email: student.email || student.user?.email || '-',
  university: student.university?.name || student.raw?.verificationData?.universityName || '-',
  level: student.level || '-',
  status: normalizeStatus(student.status),
  usage: deriveUsage(student),
  raw: student,
})

const students = computed(() => {
  const { storeStudents: studentRows } = ensureStudentStore()
  return studentRows.value.map(toAdminStudent)
})

const loadStudents = async ({ force = false } = {}) => {
  const { studentStore: store } = ensureStudentStore()

  if (loading.value) return
  if (hasLoaded.value && !force) return

  loading.value = true
  error.value = ''
  try {
    await store.fetchStudents()
    hasLoaded.value = true
  } catch (err) {
    error.value = err?.message || 'Unable to load students'
  } finally {
    loading.value = false
  }
}

const usageScore = (student) => {
  const usage = student.usage
  return usage.sessionsLast30Days * 2 + usage.avgSessionMinutes + usage.actionsLast30Days * 0.1
}

const studentsByUsage = computed(() =>
  [...students.value].sort((a, b) => usageScore(b) - usageScore(a)),
)

const mostActiveStudent = computed(() => studentsByUsage.value[0] || null)

const getStudentById = (id) => students.value.find((student) => student.id === Number(id)) || null

export const useStudentAdmin = () => {
  const { storeLoading: loadingRef, storeError: errorRef } = ensureStudentStore()

  if (!hasLoaded.value && !loading.value) {
    loadStudents()
  }

  return {
    students,
    studentsByUsage,
    mostActiveStudent,
    usageScore,
    getStudentById,
    loadStudents,
    loading,
    error,
    storeLoading: loadingRef,
    storeError: errorRef,
  }
}
