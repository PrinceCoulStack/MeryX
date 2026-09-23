<template>
  <div class="create-actuality-page">
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
      <form @submit.prevent="createActuality" class="create-form">
        <div class="grid-two">
          <label>
            {{ t.newsTitle }}
            <input type="text" v-model="form.title" :placeholder="t.titlePlaceholder" required />
          </label>

          <label>
            {{ t.category }}
            <select v-model="form.category" required>
              <option value="Recruitment">{{ t.recruitment }}</option>
              <option value="Event">{{ t.event }}</option>
              <option value="Announcement">{{ t.announcement }}</option>
            </select>
          </label>
        </div>

        <label>
          {{ t.content }}
          <textarea
            v-model="form.content"
            rows="5"
            :placeholder="t.contentPlaceholder"
            required
          ></textarea>
        </label>

        <div class="grid-two">
          <label>
            {{ t.imageUrl }}
            <input type="file" accept="image/*" @change="handleImageSelect" />
          </label>

          <label>
            {{ t.visibility }}
            <select v-model="form.visibility">
              <option value="Public">{{ t.public }}</option>
              <option value="Internal">{{ t.internal }}</option>
            </select>
          </label>
        </div>

        <div class="actions-row">
          <button type="button" class="ghost-btn" @click="resetForm">{{ t.reset }}</button>
          <button
            type="submit"
            class="primary-btn"
            :disabled="isSubmitting || !form.title || !form.content"
          >
            {{ t.publishActuality }}
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
import { useCompanyActualities } from '@/compasables/useCompanyActualities'

const router = useRouter()
const locale = inject('locale', ref('en'))
const { createActuality: createActualityApi, actualityError } = useCompanyActualities()
const isSubmitting = ref(false)
const submitError = ref('')

const copy = {
  en: {
    eyebrow: 'Company / Actuality',
    title: 'Create Actuality',
    subtitle: 'Publish a new company update in the shared news feed.',
    backToList: 'Back to List',
    newsTitle: 'News title',
    titlePlaceholder: 'e.g. New Internship Batch Opened',
    category: 'Category',
    recruitment: 'Recruitment',
    event: 'Event',
    announcement: 'Announcement',
    content: 'Content',
    contentPlaceholder: 'Share your announcement, event details, or hiring update...',
    imageUrl: 'Image URL (optional)',
    imagePlaceholder: 'https://images.unsplash.com/...',
    visibility: 'Visibility',
    public: 'Public',
    internal: 'Internal',
    reset: 'Reset',
    publishActuality: 'Publish Actuality',
  },
  fr: {
    eyebrow: 'Entreprise / Actualite',
    title: 'Creer une actualite',
    subtitle: "Publiez une nouvelle information d'entreprise dans le fil partage.",
    backToList: 'Retour a la liste',
    newsTitle: "Titre de l'actualite",
    titlePlaceholder: 'ex: Ouverture du nouveau batch de stage',
    category: 'Categorie',
    recruitment: 'Recrutement',
    event: 'Evenement',
    announcement: 'Annonce',
    content: 'Contenu',
    contentPlaceholder: 'Partagez votre annonce, vos details ou votre actualite recrutement...',
    imageUrl: "URL d'image (optionnel)",
    imagePlaceholder: 'https://images.unsplash.com/...',
    visibility: 'Visibilite',
    public: 'Publique',
    internal: 'Interne',
    reset: 'Reinitialiser',
    publishActuality: "Publier l'actualite",
  },
}

const t = computed(() => copy[locale.value] || copy.en)

const form = reactive({
  title: '',
  category: 'Recruitment',
  content: '',
  image: '',
  visibility: 'Public',
})

const handleImageSelect = (event) => {
  const file = event.target?.files?.[0]
  if (!file) {
    form.image = ''
    return
  }

  const reader = new FileReader()
  reader.onload = () => {
    form.image = String(reader.result || '')
  }
  reader.readAsDataURL(file)
}

const resetForm = () => {
  Object.assign(form, {
    title: '',
    category: 'Recruitment',
    content: '',
    image: '',
    visibility: 'Public',
  })
}

const createActuality = async () => {
  submitError.value = ''
  isSubmitting.value = true

  const payload = {
    title: form.title,
    content: form.content,
    category: form.category,
    visibility: form.visibility,
    imageUrl: form.image || '',
    publiedAt: new Date().toISOString(),
    createdAt: new Date().toISOString(),
    updatedAt: new Date().toISOString(),
  }

  try {
    const created = await createActualityApi(payload)
    if (created) {
      resetForm()
      await router.push({ name: 'companyActualityList' })
      return
    }

    submitError.value = actualityError.value || 'Unable to publish actuality. Please try again.'
  } catch {
    submitError.value = actualityError.value || 'Unable to publish actuality. Please try again.'
  } finally {
    isSubmitting.value = false
  }
}

const goToList = () => {
  router.push({ name: 'companyActualityList' })
}
</script>

<style scoped>
.create-actuality-page {
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
