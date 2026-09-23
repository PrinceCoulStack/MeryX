<template>
  <div class="create-training-page">
    <section class="page-header">
      <div>
        <p class="eyebrow">{{ t.eyebrow }}</p>
        <h1>{{ t.title }}</h1>
        <p class="intro">{{ t.subtitle }}</p>
      </div>
      <button class="ghost-btn" type="button" @click="goToList">
        <i class="bi bi-arrow-left"></i>
        {{ t.backToList }}
      </button>
    </section>

    <section class="form-card">
      <form @submit.prevent="createTrainingAction" class="create-form">
        <div class="grid-two">
          <label>
            {{ t.trainingTitle }}
            <input type="text" v-model="form.title" :placeholder="t.titlePlaceholder" required />
          </label>

          <label>
            {{ t.trainingType }}
            <select v-model="form.type" required>
              <option v-for="option in typeOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </option>
            </select>
          </label>
        </div>

        <div class="grid-two">
          <label>
            {{ t.location }}
            <input
              type="text"
              v-model="form.location"
              :placeholder="t.locationPlaceholder"
              required
            />
          </label>

          <label>
            {{ t.deliveryMode }}
            <select v-model="form.mode" required>
              <option value="Remote">{{ t.remote }}</option>
              <option value="On-site">{{ t.onSite }}</option>
              <option value="Hybrid">{{ t.hybrid }}</option>
            </select>
          </label>
        </div>

        <div class="grid-two">
          <label>
            {{ t.duration }}
            <input
              type="text"
              v-model="form.duration"
              :placeholder="t.durationPlaceholder"
              required
            />
          </label>

          <label>
            {{ t.seats }}
            <input type="number" v-model="form.seats" min="1" required />
          </label>
        </div>

        <div class="grid-two">
          <label>
            {{ t.startDate }}
            <input type="date" v-model="form.startDate" required />
          </label>

          <label>
            {{ t.endDate }}
            <input type="date" v-model="form.endDate" required />
          </label>
        </div>

        <label>
          {{ t.description }}
          <textarea
            v-model="form.description"
            rows="4"
            :placeholder="t.descriptionPlaceholder"
            required
          ></textarea>
        </label>

        <label>
          {{ t.requirements }}
          <input type="text" v-model="form.requirements" :placeholder="t.requirementsPlaceholder" />
        </label>

        <label>
          {{ t.initialStatus }}
          <select v-model="form.status">
            <option value="Open">{{ t.open }}</option>
            <option value="Draft">{{ t.draft }}</option>
          </select>
        </label>

        <div class="actions-row">
          <button type="button" class="ghost-btn" @click="resetForm">{{ t.reset }}</button>
          <button
            type="submit"
            class="primary-btn"
            :disabled="isSubmitting || !form.title || !form.description"
          >
            {{ t.publishTraining }}
          </button>
        </div>

        <p v-if="submitError" class="form-error">{{ submitError }}</p>
      </form>
    </section>
  </div>
</template>

<script setup>
import { computed, inject, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useCompanyTrainings } from '@/compasables/useCompanyTrainings'

const router = useRouter()
const locale = inject('locale', ref('en'))
const { createTraining, trainingError } = useCompanyTrainings()
const isSubmitting = ref(false)
const submitError = ref('')

const copy = {
  en: {
    eyebrow: 'Company / Training',
    title: 'Create Training',
    subtitle: 'Publish a new training program for students.',
    backToList: 'Back to List',
    trainingTitle: 'Training title',
    titlePlaceholder: 'e.g. Industrial Safety Bootcamp',
    trainingType: 'Training type',
    location: 'Location',
    locationPlaceholder: 'Bamako / Remote / Dakar',
    deliveryMode: 'Delivery mode',
    remote: 'Remote',
    onSite: 'On-site',
    hybrid: 'Hybrid',
    duration: 'Duration',
    durationPlaceholder: 'e.g. 8 weeks',
    seats: 'Seats',
    startDate: 'Start date',
    endDate: 'End date',
    description: 'Description',
    descriptionPlaceholder: 'Describe learning goals and expected profile.',
    requirements: 'Requirements',
    requirementsPlaceholder: 'Comma separated skills, e.g. teamwork, safety basics',
    initialStatus: 'Initial status',
    open: 'Open',
    draft: 'Draft',
    reset: 'Reset',
    publishTraining: 'Publish Training',
    types: {
      bootcamp: 'Bootcamp',
      internship: 'Internship Training',
      graduate: 'Graduate Program',
      certification: 'Certification Track',
    },
  },
  fr: {
    eyebrow: 'Entreprise / Formation',
    title: 'Creer une formation',
    subtitle: 'Publiez un nouveau programme de formation pour les etudiants.',
    backToList: 'Retour a la liste',
    trainingTitle: 'Titre de la formation',
    titlePlaceholder: 'ex: Bootcamp securite industrielle',
    trainingType: 'Type de formation',
    location: 'Lieu',
    locationPlaceholder: 'Bamako / Remote / Dakar',
    deliveryMode: 'Mode de diffusion',
    remote: 'Distance',
    onSite: 'Sur site',
    hybrid: 'Hybride',
    duration: 'Duree',
    durationPlaceholder: 'ex: 8 semaines',
    seats: 'Places',
    startDate: 'Date de debut',
    endDate: 'Date de fin',
    description: 'Description',
    descriptionPlaceholder: 'Decrivez les objectifs et le profil attendu.',
    requirements: 'Exigences',
    requirementsPlaceholder: 'Competences separees par des virgules',
    initialStatus: 'Statut initial',
    open: 'Ouvert',
    draft: 'Brouillon',
    reset: 'Reinitialiser',
    publishTraining: 'Publier la formation',
    types: {
      bootcamp: 'Bootcamp',
      internship: 'Formation stage',
      graduate: 'Programme diplome',
      certification: 'Parcours certifiant',
    },
  },
}

const t = computed(() => copy[locale.value] || copy.en)

const typeOptions = computed(() => [
  { value: 'Bootcamp', label: t.value.types.bootcamp },
  { value: 'Internship Training', label: t.value.types.internship },
  { value: 'Graduate Program', label: t.value.types.graduate },
  { value: 'Certification Track', label: t.value.types.certification },
])

const form = reactive({
  title: '',
  type: 'Bootcamp',
  location: 'Remote',
  mode: 'Remote',
  duration: '',
  seats: 20,
  status: 'Open',
  description: '',
  requirements: '',
  startDate: '',
  endDate: '',
})

const resetForm = () => {
  Object.assign(form, {
    title: '',
    type: 'Bootcamp',
    location: 'Remote',
    mode: 'Remote',
    duration: '',
    seats: 20,
    status: 'Open',
    description: '',
    requirements: '',
    startDate: '',
    endDate: '',
  })
}

const submitTraining = async () => {
  submitError.value = ''
  isSubmitting.value = true

  const payload = {
    title: form.title,
    type: form.type,
    mode: form.mode,
    location: form.location,
    duration: form.duration,
    durationLabel: form.duration,
    seatCount: Number(form.seats),
    seats: Number(form.seats),
    status: form.status,
    description: form.description,
    requirements: form.requirements,
    startAt: form.startDate,
    endAt: form.endDate,
  }

  try {
    const result = await createTraining(payload)
    if (result) {
      resetForm()
      await router.push({ name: 'companyTrainingList' })
      return
    }

    submitError.value = trainingError.value || 'Unable to publish training. Please try again.'
  } catch {
    submitError.value = trainingError.value || 'Unable to publish training. Please try again.'
  } finally {
    isSubmitting.value = false
  }
}

const createTrainingAction = () => {
  return submitTraining()
}

const goToList = () => {
  router.push({ name: 'companyTrainingList' })
}
</script>

<style scoped>
.create-training-page {
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
