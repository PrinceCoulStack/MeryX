<template>
  <div class="student-form-page">
    <div class="page-header">
      <div>
        <p class="eyebrow">Student details</p>
        <h1>{{ isView ? 'Student details' : 'Edit student' }}</h1>
      </div>
      <div class="page-actions">
        <button class="text-btn" @click="cancel">Back</button>
        <button v-if="!isView" type="button" class="danger-btn" @click="deleteStudent">
          Delete student
        </button>
      </div>
    </div>

    <form @submit.prevent="submit">
      <div class="form-grid">
        <label>
          <span>Full name</span>
          <input v-model="form.fullName" :readonly="isView" required />
        </label>

        <label>
          <span>Email</span>
          <input type="email" v-model="form.email" :readonly="isView" required />
        </label>

        <label>
          <span>Program</span>
          <input v-model="form.program" :readonly="isView" required />
        </label>

        <label>
          <span>Enrollment year</span>
          <input v-model="form.enrollmentYear" :readonly="isView" />
        </label>

        <label>
          <span>Status</span>
          <select v-model="form.status" :disabled="isView">
            <option>Active</option>
            <option>Inactive</option>
            <option>Graduated</option>
          </select>
        </label>

        <label>
          <span>Phone</span>
          <input v-model="form.phone" :readonly="isView" />
        </label>
      </div>

      <div class="form-actions" v-if="!isView">
        <button type="button" class="secondary-btn" @click="cancel">Cancel</button>
        <button type="submit" class="primary-btn">Update student</button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useStudentStore } from '@/stores/student.store'

const route = useRoute()
const router = useRouter()
const studentStore = useStudentStore()

const isView = computed(() => route.name === 'student-view')

const form = reactive({
  fullName: '',
  email: '',
  program: '',
  enrollmentYear: '',
  status: 'Active',
  phone: '',
})

onMounted(async () => {
  if (!route.params.id) return
  await studentStore.fetchStudent(route.params.id)
  const student = studentStore.student || {}
  form.fullName =
    student.fullName ||
    `${student.firstName || ''} ${student.lastName || ''}`.trim() ||
    student.name ||
    ''
  form.email = student.email || ''
  form.program = student.program || ''
  form.enrollmentYear = student.enrollmentYear || ''
  form.status = student.status || 'Active'
  form.phone = student.phone || ''
})

const submit = async () => {
  if (!route.params.id) return
  await studentStore.updateStudent(route.params.id, {
    ...form,
    academicYear: form.enrollmentYear,
  })
  router.push({ name: 'listStudents' })
}

const deleteStudent = async () => {
  if (!route.params.id || !window.confirm('Delete this student?')) return
  await studentStore.deleteStudent(route.params.id)
  router.push({ name: 'listStudents' })
}

const cancel = () => {
  router.push({ name: 'listStudents' })
}
</script>

<style scoped>
.student-form-page {
  display: grid;
  gap: 24px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.page-header h1 {
  margin: 0;
  font-size: 1.9rem;
}

.page-actions {
  display: flex;
  gap: 12px;
}

.eyebrow {
  margin: 0 0 8px;
  text-transform: uppercase;
  color: var(--primary);
  letter-spacing: 0.12em;
  font-size: 0.82rem;
  font-weight: 700;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 24px;
}

label {
  display: grid;
  gap: 10px;
}

label span {
  color: var(--muted);
  font-weight: 600;
}

input,
select {
  width: 100%;
  border: 1px solid var(--border);
  border-radius: 18px;
  background: var(--surface);
  padding: 14px 16px;
  color: var(--text);
}

input[readonly] {
  opacity: 0.75;
  cursor: not-allowed;
}

select:disabled {
  opacity: 0.75;
  cursor: not-allowed;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 16px;
}

.secondary-btn,
.primary-btn,
.text-btn,
.danger-btn {
  border: none;
  border-radius: 999px;
  padding: 12px 22px;
  cursor: pointer;
}

.secondary-btn {
  background: var(--surface-soft);
  color: var(--text);
}

.primary-btn {
  background: var(--primary);
  color: white;
}

.text-btn {
  background: transparent;
  color: var(--primary);
}

.danger-btn {
  background: rgba(239, 68, 68, 0.12);
  color: #b91c1c;
}

@media (max-width: 900px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}
</style>
