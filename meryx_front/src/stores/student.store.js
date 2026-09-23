import { defineStore } from 'pinia'
import api from '@/api/axios'

const parseHydraCollection = (data) => {
  if (Array.isArray(data?.['hydra:member'])) return data['hydra:member']
  if (Array.isArray(data)) return data
  return []
}

const resourceId = (value) => {
  if (typeof value === 'number') return value
  if (typeof value === 'string') {
    const parts = value.split('/')
    return Number(parts[parts.length - 1])
  }
  if (value && typeof value === 'object') {
    if (typeof value.id === 'number') return value.id
    if (typeof value['@id'] === 'string') {
      const parts = value['@id'].split('/')
      return Number(parts[parts.length - 1])
    }
  }
  return null
}

const parseMeta = (bio) => {
  if (!bio) return {}
  if (typeof bio === 'object') return bio
  try {
    const parsed = JSON.parse(bio)
    return parsed && typeof parsed === 'object' ? parsed : {}
  } catch {
    return {}
  }
}

const RADAR_CATEGORIES = ['academic', 'certificate', 'numerique', 'langue', 'stage']

// Backend expects flat arrays per radar category; older saved profiles may still use { entries: [...] }.
const getRadarEntries = (value) => {
  if (Array.isArray(value)) return value
  if (value && Array.isArray(value.entries)) return value.entries
  return []
}

const normalizeRadarForRead = (radar) => {
  const source = radar && typeof radar === 'object' ? radar : {}
  const normalized = {}
  RADAR_CATEGORIES.forEach((category) => {
    normalized[category] = getRadarEntries(source[category])
  })
  return normalized
}

const containsFile = (value) => {
  if (!value) return false
  if (typeof File !== 'undefined' && value instanceof File) return true
  if (typeof Blob !== 'undefined' && value instanceof Blob) return true
  if (Array.isArray(value)) return value.some(containsFile)
  if (typeof value === 'object') return Object.values(value).some(containsFile)
  return false
}

// Recursively flattens a nested object/array into bracket-notation form keys (bio[radar][academic][0][proofFile]).
const appendFormValue = (formData, key, value) => {
  if (value === undefined || value === null) return
  if (typeof File !== 'undefined' && value instanceof File) {
    formData.append(key, value, value.name || 'upload')
    return
  }
  if (typeof Blob !== 'undefined' && value instanceof Blob) {
    formData.append(key, value)
    return
  }
  if (Array.isArray(value)) {
    value.forEach((item, index) => appendFormValue(formData, `${key}[${index}]`, item))
    return
  }
  if (typeof value === 'object') {
    Object.entries(value).forEach(([childKey, childValue]) =>
      appendFormValue(formData, `${key}[${childKey}]`, childValue),
    )
    return
  }
  formData.append(key, String(value))
}

const buildStudentProfileFormData = (payload) => {
  const formData = new FormData()
  Object.entries(payload).forEach(([key, value]) => appendFormValue(formData, key, value))
  return formData
}

const readFirstValue = (...values) =>
  values.find((value) => value !== undefined && value !== null && value !== '')

const extractNestedObject = (value) => (value && typeof value === 'object' ? value : {})

const normalizeRequestStatus = (profile, meta = {}) => {
  const raw = (meta.status || profile?.status || '').toString().trim().toLowerCase()

  if (raw.includes('approve')) return 'approved'
  if (raw.includes('reject')) return 'rejected'
  if (raw.includes('pend')) return 'pending'

  if (typeof profile?.isApproved === 'boolean') {
    return profile.isApproved ? 'approved' : 'pending'
  }

  return raw || 'active'
}

const nowIso = () => new Date().toISOString()

const asProfileIri = (id) => `/api/studentProfiles/${id}`
const asUserIri = (id) => `/api/users/${id}`
const asUniversityIri = (id) => `/api/university/${id}`

const toRelationIri = (value, builder) => {
  if (!value) return null
  if (typeof value === 'string' && value.startsWith('/api/')) return value

  const id = resourceId(value)
  if (!id) return null
  return builder(id)
}

const slugify = (value) =>
  String(value || '')
    .trim()
    .toLowerCase()
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
    .replace(/^-|-$/g, '')

const buildProfileUrl = ({ profileUrl, fullName, studentId, userId, profileId }) => {
  if (profileUrl) return profileUrl

  const slugSource = studentId || fullName || userId || profileId
  const slug = slugify(slugSource) || `student-${profileId || userId || Date.now()}`
  return `/students/${slug}`
}

const normalizeStudentProfile = (profile) => {
  const meta = parseMeta(profile.bio)
  const verificationData = extractNestedObject(profile.verificationData || meta.verificationData)
  const personalMeta = extractNestedObject(meta.personal)
  const academicMeta = extractNestedObject(meta.academic)
  const status = normalizeRequestStatus(profile, meta)
  const userRelationId =
    resourceId(profile.userId) || Number(meta.userId || verificationData.userId || 0) || null
  const universityRelationId =
    resourceId(profile.universityId) ||
    Number(meta.universityId || verificationData.universityId || 0) ||
    null

  const fullName =
    profile.fullName ||
    profile.userId?.fullName ||
    verificationData.fullName ||
    personalMeta.fullName ||
    `${profile.userId?.firstName || ''} ${profile.userId?.lastName || ''}`.trim() ||
    'Student'

  const skills = Array.isArray(profile.skill)
    ? profile.skill.map((item) => item.name).filter(Boolean)
    : Array.isArray(profile.skills)
      ? profile.skills
      : []

  const languages = Array.isArray(profile.languages)
    ? profile.languages.map((item) => item.name).filter(Boolean)
    : []

  return {
    id: profile.id,
    userId: userRelationId,
    user: {
      id: userRelationId,
      fullName:
        profile.userId?.fullName || verificationData.fullName || personalMeta.fullName || '',
      email: profile.userId?.email || verificationData.email || meta.email || '',
      phone: profile.userId?.phone || verificationData.phone || meta.phone || '',
    },
    universityId: universityRelationId,
    university: {
      id: universityRelationId,
      name: verificationData.universityName || meta.universityName || '',
      email: verificationData.universityEmail || meta.universityEmail || '',
    },
    fullName,
    email:
      profile.userId?.email || verificationData.email || meta.email || personalMeta.email || '',
    phone:
      verificationData.phone || meta.phone || profile.userId?.phone || personalMeta.phone || '',
    program:
      verificationData.program || meta.program || academicMeta.program || academicMeta.course || '',
    level:
      verificationData.level ||
      meta.level ||
      meta.currentClass ||
      academicMeta.level ||
      academicMeta.currentClass ||
      '',
    status,
    enrollmentYear:
      verificationData.enrollmentYear || meta.enrollmentYear || meta.academicYear || '',
    gpa: profile.gpa || meta.gpa || '0.00',
    gender:
      profile.gender ||
      verificationData.gender ||
      personalMeta.gender ||
      meta.gender ||
      'prefer_not_to_say',
    attendance: meta.attendance || 0,
    applicationCount: Array.isArray(profile.applications) ? profile.applications.length : 0,
    faculty: verificationData.faculty || meta.faculty || academicMeta.faculty || '',
    department: verificationData.department || meta.department || academicMeta.department || '',
    academicYear:
      verificationData.academicYear || meta.academicYear || verificationData.enrollmentYear || '',
    studentId: verificationData.studentId || meta.studentId || '',
    nationality: verificationData.nationality || meta.nationality || personalMeta.nationality || '',
    address: verificationData.address || meta.address || personalMeta.address || '',
    admissionDate: verificationData.admissionDate || meta.admissionDate || personalMeta.dob || '',
    summary: verificationData.summary || meta.summary || '',
    projects: Array.isArray(meta.projects) ? meta.projects : [],
    academicRecords: Array.isArray(meta.academicRecords) ? meta.academicRecords : [],
    skills,
    languages,
    profileUrl: buildProfileUrl({
      profileUrl: profile.profileUrl || meta.profileUrl,
      fullName,
      studentId: meta.studentId,
      userId: resourceId(profile.userId) || Number(meta.userId || 0) || null,
      profileId: profile.id,
    }),
    profileCompletion: profile.profileCompletion || 0,
    createdAt: profile.createdAt || null,
    updatedAt: profile.updatedAt || null,
    studentDocuments: Array.isArray(profile.studentDocuments) ? profile.studentDocuments : [],
    raw: profile,
  }
}

const buildMetaFromStudent = (student, existingMeta = {}) => ({
  ...existingMeta,
  email:
    student.email ||
    student.personal?.email ||
    existingMeta.email ||
    existingMeta.personal?.email ||
    '',
  phone:
    student.phone ||
    student.personal?.phone ||
    existingMeta.phone ||
    existingMeta.personal?.phone ||
    '',
  program:
    student.program ||
    student.academic?.program ||
    existingMeta.program ||
    existingMeta.academic?.program ||
    '',
  level:
    student.level ||
    student.academic?.currentClass ||
    student.academic?.level ||
    existingMeta.level ||
    existingMeta.academic?.currentClass ||
    existingMeta.academic?.level ||
    '',
  currentClass:
    student.currentClass ||
    student.level ||
    student.academic?.currentClass ||
    existingMeta.currentClass ||
    existingMeta.academic?.currentClass ||
    '',
  status:
    student.status ||
    student.academic?.status ||
    existingMeta.status ||
    existingMeta.academic?.status ||
    'Active',
  enrollmentYear:
    student.enrollmentYear ||
    student.academicYear ||
    student.academic?.academicYear ||
    existingMeta.enrollmentYear ||
    existingMeta.academicYear ||
    existingMeta.academic?.academicYear ||
    '',
  academicYear:
    student.academicYear ||
    student.enrollmentYear ||
    student.academic?.academicYear ||
    existingMeta.academicYear ||
    existingMeta.enrollmentYear ||
    existingMeta.academic?.academicYear ||
    '',
  faculty:
    student.faculty ||
    student.academic?.faculty ||
    existingMeta.faculty ||
    existingMeta.academic?.faculty ||
    '',
  department:
    student.department ||
    student.academic?.department ||
    existingMeta.department ||
    existingMeta.academic?.department ||
    '',
  studentId:
    student.studentId ||
    student.academic?.studentId ||
    existingMeta.studentId ||
    existingMeta.academic?.studentId ||
    '',
  nationality:
    student.nationality ||
    student.personal?.nationality ||
    existingMeta.nationality ||
    existingMeta.personal?.nationality ||
    '',
  address:
    student.address ||
    student.personal?.address ||
    existingMeta.address ||
    existingMeta.personal?.address ||
    '',
  admissionDate:
    student.admissionDate ||
    student.personal?.dob ||
    student.academic?.admissionDate ||
    existingMeta.admissionDate ||
    existingMeta.personal?.dob ||
    '',
  attendance: student.attendance ?? existingMeta.attendance ?? 0,
  summary: student.summary || existingMeta.summary || '',
  projects: Array.isArray(student.projects) ? student.projects : existingMeta.projects || [],
  academicRecords: Array.isArray(student.academicRecords)
    ? student.academicRecords
    : existingMeta.academicRecords || [],
  radar: normalizeRadarForRead(
    student.radar && typeof student.radar === 'object' ? student.radar : existingMeta.radar,
  ),
  personal:
    student.personal && typeof student.personal === 'object'
      ? {
          ...existingMeta.personal,
          ...student.personal,
          gender: readFirstValue(
            student.gender,
            student.personal.gender,
            existingMeta.personal?.gender,
          ),
          email: readFirstValue(
            student.email,
            student.personal.email,
            existingMeta.personal?.email,
          ),
          phone: readFirstValue(
            student.phone,
            student.personal.phone,
            existingMeta.personal?.phone,
          ),
          dob: readFirstValue(
            student.admissionDate,
            student.personal.dob,
            existingMeta.personal?.dob,
          ),
          nationality: readFirstValue(
            student.nationality,
            student.personal.nationality,
            existingMeta.personal?.nationality,
          ),
          address: readFirstValue(
            student.address,
            student.personal.address,
            existingMeta.personal?.address,
          ),
        }
      : existingMeta.personal || {},
  academic:
    student.academic && typeof student.academic === 'object'
      ? {
          ...existingMeta.academic,
          ...student.academic,
          program: readFirstValue(
            student.program,
            student.academic.program,
            existingMeta.academic?.program,
          ),
          currentClass: readFirstValue(
            student.currentClass,
            student.level,
            student.academic.currentClass,
            student.academic.level,
            existingMeta.academic?.currentClass,
          ),
          level: readFirstValue(
            student.level,
            student.academic.level,
            existingMeta.academic?.level,
          ),
          faculty: readFirstValue(
            student.faculty,
            student.academic.faculty,
            existingMeta.academic?.faculty,
          ),
          department: readFirstValue(
            student.department,
            student.academic.department,
            existingMeta.academic?.department,
          ),
          academicYear: readFirstValue(
            student.academicYear,
            student.enrollmentYear,
            student.academic.academicYear,
            existingMeta.academic?.academicYear,
          ),
          status: readFirstValue(
            student.status,
            student.academic.status,
            existingMeta.academic?.status,
          ),
          studentId: readFirstValue(
            student.studentId,
            student.academic.studentId,
            existingMeta.academic?.studentId,
          ),
        }
      : existingMeta.academic || {},
  verificationData: {
    ...existingMeta.verificationData,
    fullName:
      student.fullName ||
      existingMeta.verificationData?.fullName ||
      '',
    email:
      student.email ||
      student.personal?.email ||
      existingMeta.verificationData?.email ||
      existingMeta.email ||
      '',
    phone:
      student.phone ||
      student.personal?.phone ||
      existingMeta.verificationData?.phone ||
      existingMeta.phone ||
      '',
    gender:
      student.gender ||
      student.personal?.gender ||
      existingMeta.verificationData?.gender ||
      existingMeta.personal?.gender ||
      'prefer_not_to_say',
    program:
      student.program ||
      student.academic?.program ||
      existingMeta.verificationData?.program ||
      existingMeta.program ||
      '',
    level:
      student.level ||
      student.academic?.currentClass ||
      student.academic?.level ||
      existingMeta.verificationData?.level ||
      existingMeta.level ||
      '',
    universityId:
      student.universityId ||
      existingMeta.verificationData?.universityId ||
      existingMeta.universityId ||
      null,
    universityName:
      student.universityName ||
      existingMeta.verificationData?.universityName ||
      existingMeta.universityName ||
      '',
    universityEmail:
      student.universityEmail ||
      existingMeta.verificationData?.universityEmail ||
      existingMeta.universityEmail ||
      '',
  },
})

export const useStudentStore = defineStore('student', {
  state: () => ({
    students: [],
    student: null,
    studentSkills: [],
    studentLanguages: [],
    studentDocuments: [],
    loading: false,
    error: null,
  }),

  getters: {
    totalStudents: (state) => state.students.length,
  },

  actions: {
    async fetchStudents(universityId = null) {
      this.loading = true
      this.error = null

      try {
        const response = await api.listItems('studentProfiles')
        const rows = parseHydraCollection(response.data)
        const mapped = rows.map(normalizeStudentProfile)
        const targetUniversityId = Number(universityId || 0)

        this.students = targetUniversityId
          ? mapped.filter((student) => Number(student.universityId) === targetUniversityId)
          : mapped
      } catch (error) {
        this.error = error
        console.warn('Unable to fetch student profiles.', error)
      } finally {
        this.loading = false
      }
    },

    async fetchStudentsByUniversity(universityId) {
      this.loading = true
      this.error = null

      try {
        const targetUniversityId = Number(universityId || 0)
        if (!targetUniversityId) {
          this.students = []
          return
        }

        const response = await api.listItems(
          `studentProfiles?universityId=${encodeURIComponent(asUniversityIri(targetUniversityId))}`,
        )
        const rows = parseHydraCollection(response.data)
        this.students = rows.map(normalizeStudentProfile)
      } catch (error) {
        this.error = error
        console.warn('Unable to fetch student profiles for university.', error)
      } finally {
        this.loading = false
      }
    },

    async fetchStudent(id) {
      const response = await api.getItem('studentProfiles', id)
      this.student = normalizeStudentProfile(response.data)
      await Promise.all([
        this.fetchSkillsForStudent(id),
        this.fetchLanguagesForStudent(id),
        this.fetchDocumentsForStudent(id),
      ])
      return this.student
    },

    async reviewStudentRegistrationRequest(id, nextStatus, reason = '') {
      const normalized = String(nextStatus || '')
        .trim()
        .toLowerCase()
      const allowed = ['approved', 'rejected', 'pending']

      if (!allowed.includes(normalized)) {
        throw new Error('Invalid registration review status.')
      }

      const currentResponse = await api.getItem('studentProfiles', id)
      const current = currentResponse.data || {}
      const currentMeta = parseMeta(current.bio)

      const meta = {
        ...currentMeta,
        status: normalized,
        reviewReason: reason || '',
        reviewedAt: nowIso(),
      }

      const putPayload = {
        fullName: current.fullName || '',
        gpa: current.gpa || '0.00',
        gender: current.gender || 'Male',
        profileUrl: current.profileUrl || '',
        profileCompletion: current.profileCompletion ?? 0,
        skills: Array.isArray(current.skills) ? current.skills : [],
        status: normalized,
        isApproved: normalized === 'approved',
        bio: JSON.stringify(meta),
        updatedAt: nowIso(),
        ...(current.createdAt ? { createdAt: current.createdAt } : {}),
        ...(current.userId ? { userId: toRelationIri(current.userId, asUserIri) } : {}),
        ...(current.universityId
          ? { universityId: toRelationIri(current.universityId, asUniversityIri) }
          : {}),
      }

      const response = await api.updateItem('studentProfiles', id, putPayload)
      const updated = normalizeStudentProfile(response.data)

      const linkedUserId = resourceId(current.userId)
      if (linkedUserId) {
        const userStatus = normalized === 'approved' ? 'active' : normalized
        const userIsActived = normalized !== 'rejected'

        await api.updateItem('users', linkedUserId, {
          status: userStatus,
          isActived: userIsActived,
        })
      }

      if (this.student?.id === Number(id)) {
        this.student = updated
      }

      const idx = this.students.findIndex((item) => item.id === Number(id))
      if (idx >= 0) {
        this.students[idx] = updated
      }

      return updated
    },

    async createStudent(data) {
      const createdAt = nowIso()
      const sanitized = data || {}

      const providedBio =
        typeof sanitized.bio === 'string'
          ? parseMeta(sanitized.bio)
          : sanitized.bio && typeof sanitized.bio === 'object'
            ? sanitized.bio
            : {}

      const linkedUniversityId = resourceId(sanitized.universityId)
      const normalizedEmail =
        sanitized.email || sanitized.personal?.email || providedBio.email || ''
      const normalizedPhone =
        sanitized.phone || sanitized.personal?.phone || providedBio.phone || ''
      const normalizedProgram =
        sanitized.program || sanitized.academic?.program || providedBio.program || ''
      const normalizedLevel =
        sanitized.level || sanitized.academic?.currentClass || providedBio.level || ''
      const normalizedStatus =
        sanitized.status ||
        sanitized.userStatus ||
        sanitized.academic?.status ||
        providedBio.status ||
        'Active'

      let linkedUserId = resourceId(sanitized.userId)
      // Backend auto-creates a minimal studentProfile when registering the user; reuse it instead of POSTing a duplicate.
      let existingStudentProfileId = resourceId(sanitized.studentProfileId)
      if (!linkedUserId && normalizedEmail) {
        const tempPassword = sanitized.password || `Student@${Date.now()}`
        const token = localStorage.getItem('token')

        // A ROLE_UNIVERSITY caller auto-assigns the student to their own university server-side,
        // so universityId/universityName/universityEmail are no longer needed on this call.
        const userPayload = {
          email: normalizedEmail,
          password: tempPassword,
          phone: normalizedPhone,
          status: normalizedStatus,
          isActived: true,
          userTypeId: sanitized.userTypeId || '/api/user_types/4',
        }

        const userResponse = await api.createItem('users', userPayload, {
          headers: token ? { Authorization: `Bearer ${token}` } : {},
        })
        linkedUserId = Number(userResponse?.data?.id || 0) || null
        existingStudentProfileId =
          resourceId(userResponse?.data?.studentProfile?.id) ||
          resourceId(userResponse?.data?.studentProfile) ||
          existingStudentProfileId
      }

      const normalizedRadar = normalizeRadarForRead(sanitized.radar)

      const meta = {
        ...buildMetaFromStudent(sanitized),
        userId: linkedUserId,
        universityId: linkedUniversityId,
        email: normalizedEmail,
        phone: normalizedPhone,
        program: normalizedProgram,
        level: normalizedLevel,
        currentClass:
          sanitized.currentClass ||
          sanitized.academic?.currentClass ||
          providedBio.currentClass ||
          normalizedLevel ||
          '',
        status: normalizedStatus,
        enrollmentYear:
          sanitized.enrollmentYear ||
          sanitized.academic?.academicYear ||
          providedBio.enrollmentYear ||
          providedBio.academicYear ||
          '',
        academicYear:
          sanitized.academicYear ||
          sanitized.academic?.academicYear ||
          providedBio.academicYear ||
          sanitized.enrollmentYear ||
          '',
        faculty: sanitized.faculty || sanitized.academic?.faculty || providedBio.faculty || '',
        department:
          sanitized.department || sanitized.academic?.department || providedBio.department || '',
        studentId:
          sanitized.studentId || sanitized.academic?.studentId || providedBio.studentId || '',
        nationality:
          sanitized.nationality || sanitized.personal?.nationality || providedBio.nationality || '',
        address: sanitized.address || sanitized.personal?.address || providedBio.address || '',
        admissionDate:
          sanitized.admissionDate ||
          sanitized.academic?.admissionDate ||
          providedBio.admissionDate ||
          '',
        summary: sanitized.summary || providedBio.summary || '',
        projects: Array.isArray(sanitized.projects)
          ? sanitized.projects
          : providedBio.projects || [],
        academicRecords: Array.isArray(sanitized.academicRecords)
          ? sanitized.academicRecords
          : providedBio.academicRecords || [],
        radar: normalizedRadar,
        personal: {
          ...providedBio.personal,
          fullName: sanitized.fullName || providedBio.personal?.fullName || '',
          gender: sanitized.gender || providedBio.personal?.gender || 'prefer_not_to_say',
          email: normalizedEmail,
          phone: normalizedPhone,
          nationality: sanitized.nationality || providedBio.personal?.nationality || '',
          address: sanitized.address || providedBio.personal?.address || '',
          studentId: sanitized.studentId || providedBio.personal?.studentId || '',
          dob: sanitized.admissionDate || providedBio.personal?.dob || '',
          profileUrl: sanitized.profileUrl || providedBio.personal?.profileUrl || '',
        },
        academic: {
          ...providedBio.academic,
          program: normalizedProgram,
          currentClass: normalizedLevel,
          level: normalizedLevel,
          faculty:
            sanitized.faculty || sanitized.academic?.faculty || providedBio.academic?.faculty || '',
          department:
            sanitized.department ||
            sanitized.academic?.department ||
            providedBio.academic?.department ||
            '',
          academicYear:
            sanitized.academicYear ||
            sanitized.enrollmentYear ||
            sanitized.academic?.academicYear ||
            providedBio.academic?.academicYear ||
            '',
          status: normalizedStatus,
          studentId:
            sanitized.studentId ||
            sanitized.academic?.studentId ||
            providedBio.academic?.studentId ||
            '',
          gpa: sanitized.gpa || providedBio.academic?.gpa || '0.00',
          admissionDate: sanitized.admissionDate || providedBio.academic?.admissionDate || '',
        },
        verificationData: {
          ...providedBio.verificationData,
          email: normalizedEmail,
          phone: normalizedPhone,
          gender: sanitized.gender || providedBio.personal?.gender || 'prefer_not_to_say',
          program: normalizedProgram,
          level: normalizedLevel,
          universityId: linkedUniversityId,
          universityName: providedBio.verificationData?.universityName || '',
          universityEmail: providedBio.verificationData?.universityEmail || '',
          studentId: sanitized.studentId || providedBio.studentId || '',
        },
      }

      const computedFullName = sanitized.fullName || sanitized.name || ''
      const computedProfileUrl = buildProfileUrl({
        profileUrl: sanitized.profileUrl,
        fullName: computedFullName,
        studentId: meta.studentId,
        userId: linkedUserId,
      })

      const payload = {
        fullName: computedFullName,
        gpa: sanitized.gpa || '0.00',
        gender: sanitized.gender || providedBio.personal?.gender || 'prefer_not_to_say',
        profileUrl: computedProfileUrl,
        profileCompletion: sanitized.profileCompletion ?? 20,
        skills: Array.isArray(sanitized.skills) ? sanitized.skills.filter(Boolean) : [],
        bio: meta,
        verificationData: meta.verificationData,
        // Students created directly by their university are pre-approved; self-registered students stay pending until reviewed.
        status: sanitized.status || 'approved',
        isApproved: sanitized.isApproved ?? true,
        ...(linkedUserId ? { userId: toRelationIri(linkedUserId, asUserIri) } : {}),
        ...(linkedUniversityId
          ? { universityId: toRelationIri(linkedUniversityId, asUniversityIri) }
          : {}),
        createdAt,
        updatedAt: createdAt,
      }

      // Proof files must travel as real multipart uploads; plain JSON is used when there are none.
      const hasProofFiles = containsFile(meta.radar)
      const requestBody = hasProofFiles
        ? buildStudentProfileFormData(payload)
        : { ...payload, bio: JSON.stringify(meta) }

      const response = existingStudentProfileId
        ? await api.updateItem('studentProfiles', existingStudentProfileId, requestBody)
        : await api.createItem('studentProfiles', requestBody)
      await this.fetchStudents()
      return normalizeStudentProfile(response.data)
    },

    async updateStudent(id, data) {
      const response = await api.getItem('studentProfiles', id)
      const current = response.data
      const currentNormalized = normalizeStudentProfile(current)
      const meta = buildMetaFromStudent(data, parseMeta(current.bio))

      const payload = {
        fullName: data.fullName || currentNormalized.fullName,
        gpa: data.gpa || currentNormalized.gpa || '0.00',
        gender:
          data.gender || data.personal?.gender || currentNormalized.gender || 'prefer_not_to_say',
        profileUrl: data.profileUrl ?? current.profileUrl ?? '',
        profileCompletion: data.profileCompletion ?? current.profileCompletion ?? 50,
        skills: Array.isArray(data.skills) ? data.skills : currentNormalized.skills,
        bio: meta,
        verificationData: meta.verificationData,
        createdAt: current.createdAt,
        updatedAt: nowIso(),
      }

      const hasProofFiles = containsFile(meta.radar)
      const requestBody = hasProofFiles
        ? buildStudentProfileFormData(payload)
        : { ...payload, bio: JSON.stringify(meta) }

      const updated = await api.updateItem('studentProfiles', id, requestBody)
      const normalized = normalizeStudentProfile(updated.data)
      this.student = normalized

      const idx = this.students.findIndex((item) => item.id === Number(id))
      if (idx >= 0) {
        this.students[idx] = normalized
      }

      return normalized
    },

    // { currentPassword, newPassword } for a user changing their own password;
    // { newPassword } only (no currentPassword) when a ROLE_ADMIN resets someone else's password.
    async changeUserPassword(userId, { currentPassword, newPassword } = {}) {
      const payload = currentPassword ? { currentPassword, newPassword } : { newPassword }
      const response = await api.createItem(`users/${userId}/change-password`, payload)
      return response.data
    },

    async deleteStudent(id) {
      await api.deleteItem('studentProfiles', id)
      this.students = this.students.filter((student) => student.id !== Number(id))
      if (this.student?.id === Number(id)) {
        this.student = null
      }
    },

    async fetchSkillsForStudent(studentProfileId) {
      const response = await api.listItems('skills')
      const rows = parseHydraCollection(response.data)
      const targetId = Number(studentProfileId)

      this.studentSkills = rows.filter((item) => {
        const itemStudentId = resourceId(item.studentProfileId)
        return itemStudentId === targetId
      })

      return this.studentSkills
    },

    async createSkill(studentProfileId, skill) {
      const timestamp = nowIso()
      const payload = {
        name: skill.name,
        level: skill.level || 'Beginner',
        isEnabled: skill.isEnabled ?? true,
        isDeleted: false,
        studentProfileId: asProfileIri(studentProfileId),
        createdAt: timestamp,
        updatedAt: timestamp,
      }

      await api.createItem('skills', payload)
      return this.fetchSkillsForStudent(studentProfileId)
    },

    async updateSkill(skillId, payload) {
      const existing = this.studentSkills.find((item) => item.id === Number(skillId))
      const body = {
        ...existing,
        ...payload,
        studentProfileId: existing?.studentProfileId?.['@id'] || existing?.studentProfileId,
        updatedAt: nowIso(),
      }
      await api.updateItem('skill', skillId, body)
    },

    async deleteSkill(skillId, studentProfileId) {
      await api.deleteItem('skill', skillId)
      this.studentSkills = this.studentSkills.filter((item) => item.id !== Number(skillId))
      if (studentProfileId) {
        await this.fetchSkillsForStudent(studentProfileId)
      }
    },

    async fetchLanguagesForStudent(studentProfileId) {
      const response = await api.listItems('languages')
      const rows = parseHydraCollection(response.data)
      const targetId = Number(studentProfileId)

      this.studentLanguages = rows.filter((item) => {
        const itemStudentId = resourceId(item.studentProfileId)
        return itemStudentId === targetId
      })

      return this.studentLanguages
    },

    async createLanguage(studentProfileId, language) {
      const timestamp = nowIso()
      const payload = {
        name: language.name,
        level: language.level || 'Basic',
        studentProfileId: asProfileIri(studentProfileId),
        createdAt: timestamp,
        updatedAt: timestamp,
      }

      await api.createItem('languages', payload)
      return this.fetchLanguagesForStudent(studentProfileId)
    },

    async deleteLanguage(languageId, studentProfileId) {
      await api.deleteItem('languages', languageId)
      this.studentLanguages = this.studentLanguages.filter((item) => item.id !== Number(languageId))
      if (studentProfileId) {
        await this.fetchLanguagesForStudent(studentProfileId)
      }
    },

    async fetchDocumentsForStudent(studentProfileId) {
      const response = await api.listItems('studentDocuments')
      const rows = parseHydraCollection(response.data)
      const targetId = Number(studentProfileId)

      this.studentDocuments = rows.filter((item) => {
        const itemStudentId = resourceId(item.studentProfileId)
        return itemStudentId === targetId
      })

      return this.studentDocuments
    },

    async createDocument(studentProfileId, document) {
      const timestamp = nowIso()
      const payload = {
        type: document.type || 'Document',
        title: document.title || document.fileName || 'Student Document',
        fileName: document.fileName || 'document.pdf',
        filePath: document.filePath || '',
        mimeType: document.mimeType || 'application/octet-stream',
        fileSize: String(document.fileSize || '0 KB'),
        isPublic: document.isPublic ?? false,
        uploadedAt: timestamp,
        updatedAt: timestamp,
        studentProfileId: asProfileIri(studentProfileId),
      }

      await api.createItem('studentDocuments', payload)
      return this.fetchDocumentsForStudent(studentProfileId)
    },

    async deleteDocument(documentId, studentProfileId) {
      await api.deleteItem('studentDocuments', documentId)
      this.studentDocuments = this.studentDocuments.filter((item) => item.id !== Number(documentId))
      if (studentProfileId) {
        await this.fetchDocumentsForStudent(studentProfileId)
      }
    },
  },
})
