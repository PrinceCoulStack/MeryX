<template>
  <div class="create-opportunity-page">
    <section class="page-header">
      <div>
        <p class="eyebrow">Company / Opportunities</p>
        <h1>Create Opportunity</h1>
        <p class="intro">Publish a new internship or job opportunity for students.</p>
      </div>
      <button class="ghost-btn" type="button" @click="goToList">
        <i class="bi bi-arrow-left"></i>
        Back to List
      </button>
    </section>

    <section class="form-card">
      <form @submit.prevent="createOpportunity" class="create-form">
        <div class="grid-two">
          <label>
            Title
            <input
              type="text"
              v-model="form.title"
              placeholder="e.g. Frontend Internship"
              required
            />
          </label>

          <label>
            Opportunity type
            <select v-model="form.type" required>
              <option v-for="option in typeOptions" :key="option" :value="option">
                {{ option }}
              </option>
            </select>
          </label>
        </div>

        <div class="grid-two">
          <label>
            Location
            <input
              type="text"
              v-model="form.location"
              placeholder="Remote / Bamako / Office"
              required
            />
          </label>

          <label>
            Remote type
            <select v-model="form.remoteType" required>
              <option value="Remote">Remote</option>
              <option value="On-site">On-site</option>
              <option value="Hybrid">Hybrid</option>
            </select>
          </label>
        </div>

        <div class="grid-two">
          <label>
            Department
            <input type="text" v-model="form.department" placeholder="e.g. Engineering" required />
          </label>

          <label>
            Salary / stipend
            <input
              type="text"
              v-model="form.salary"
              placeholder="e.g. 200,000 XOF / month"
              required
            />
          </label>
        </div>

        <label>
          Description
          <textarea
            v-model="form.description"
            rows="4"
            placeholder="Describe responsibilities and candidate profile."
            required
          ></textarea>
        </label>

        <label>
          Requirements
          <input
            type="text"
            v-model="form.requirements"
            placeholder="Comma separated skills, e.g. Vue.js, CSS, teamwork"
          />
        </label>

        <div class="grid-two">
          <label>
            Application deadline
            <input type="date" v-model="form.deadline" />
          </label>

          <label>
            Initial status
            <select v-model="form.status">
              <option value="Open">Open</option>
              <option value="Draft">Draft</option>
            </select>
          </label>
        </div>

        <div class="actions-row">
          <button type="button" class="ghost-btn" @click="resetForm">Reset</button>
          <button
            type="submit"
            class="primary-btn"
            :disabled="isSubmitting || !form.title || !form.description"
          >
            Publish Opportunity
          </button>
        </div>

        <p v-if="submitError" class="form-error">{{ submitError }}</p>
      </form>
    </section>
  </div>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useCompanyOpportunities } from '@/compasables/useCompanyOpportunities'

const router = useRouter()
const { createOpportunity: createOpportunityApi, opportunityError } = useCompanyOpportunities()
const typeOptions = ['Internship', 'Job - Full time', 'Job - Part time', 'Job - Remote']
const isSubmitting = ref(false)
const submitError = ref('')
const fallbackErrorMessage = computed(
  () => opportunityError.value || 'Unable to publish opportunity. Please try again.',
)

const form = reactive({
  title: '',
  type: 'Internship',
  location: 'Remote',
  remoteType: 'Remote',
  department: '',
  salary: '',
  description: '',
  requirements: '',
  deadline: '',
  status: 'Open',
})

const resetForm = () => {
  Object.assign(form, {
    title: '',
    type: 'Internship',
    location: 'Remote',
    remoteType: 'Remote',
    department: '',
    salary: '',
    description: '',
    requirements: '',
    deadline: '',
    status: 'Open',
  })
}

const createOpportunity = async () => {
  submitError.value = ''
  isSubmitting.value = true

  const payload = {
    title: form.title,
    type: form.type,
    location: form.location,
    department: form.department,
    salaryLabel: form.salary,
    salary: form.salary,
    status: form.status,
    description: form.description,
    remoteType: form.remoteType,
    requirements: form.requirements
      .split(',')
      .map((item) => item.trim())
      .filter(Boolean),
    applicationDeadLine: form.deadline ? new Date(form.deadline).toISOString() : null,
    deadline: form.deadline ? new Date(form.deadline).toISOString() : null,
    category: form.type,
    experienceLevel: 'Not specified',
    numberOfPositions: '1',
    isEnabled: true,
    isDeleted: false,
  }

  try {
    const created = await createOpportunityApi(payload)
    if (created) {
      resetForm()
      await router.push({ name: 'companyOpportunities' })
      return
    }

    submitError.value = fallbackErrorMessage.value
  } catch {
    submitError.value = fallbackErrorMessage.value
  } finally {
    isSubmitting.value = false
  }
}

const goToList = () => {
  router.push({ name: 'companyOpportunities' })
}
</script>

<style scoped>
.create-opportunity-page {
  display: grid;
  gap: 14px;
}

.page-header,
.form-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 18px;
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
}

.page-header {
  padding: 14px;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 10px;
}

.eyebrow {
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: var(--primary);
  font-size: 0.75rem;
  font-weight: 700;
}

.page-header h1 {
  margin: 0;
  color: var(--text);
}

.intro {
  margin: 6px 0 0;
  color: var(--muted);
}

.form-card {
  padding: 14px;
}

.create-form {
  display: grid;
  gap: 10px;
}

.grid-two {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 8px;
}

label {
  display: grid;
  gap: 6px;
  color: var(--text);
  font-weight: 600;
}

input,
select,
textarea {
  width: 100%;
  border: 1px solid var(--border);
  border-radius: 12px;
  background: var(--surface-soft);
  color: var(--text);
  padding: 10px 12px;
  font-weight: 400;
}

textarea {
  resize: vertical;
}

.actions-row {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

.form-error {
  margin: 0;
  color: #c0392b;
  font-weight: 600;
}

.primary-btn,
.ghost-btn {
  border: none;
  border-radius: 999px;
  padding: 9px 12px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.primary-btn {
  background: var(--primary);
  color: #fff;
}

.ghost-btn {
  border: 1px solid var(--border);
  background: var(--surface);
  color: var(--text);
}

@media (max-width: 900px) {
  .page-header {
    flex-direction: column;
  }

  .grid-two {
    grid-template-columns: 1fr;
  }

  .page-header .ghost-btn,
  .actions-row,
  .actions-row button {
    width: 100%;
  }

  .actions-row {
    display: grid;
  }
}
</style>
