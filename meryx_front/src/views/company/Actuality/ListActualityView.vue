<template>
  <div class="actuality-list-page">
    <section class="page-header">
      <div>
        <p class="eyebrow">{{ t.eyebrow }}</p>
        <h1>{{ t.title }}</h1>
        <p class="intro">{{ t.subtitle }}</p>
      </div>

      <button class="primary-btn" type="button" @click="goToCreate">
        <i class="bi bi-plus-lg"></i>
        {{ t.createActuality }}
      </button>
    </section>

    <section class="toolbar-card">
      <div class="search-box">
        <i class="bi bi-search"></i>
        <input v-model="search" type="search" :placeholder="t.searchPlaceholder" />
      </div>

      <div class="toolbar-filters">
        <label>
          <span>{{ t.scope }}</span>
          <select v-model="scopeFilter">
            <option value="all">{{ t.allNews }}</option>
            <option value="mine">{{ t.myNews }}</option>
            <option value="others">{{ t.otherNews }}</option>
          </select>
        </label>

        <label>
          <span>{{ t.category }}</span>
          <select v-model="categoryFilter">
            <option value="all">{{ t.allCategories }}</option>
            <option v-for="option in categoryOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
        </label>
      </div>
    </section>

    <section class="news-grid" v-if="filteredNews.length">
      <article v-for="item in filteredNews" :key="item.id" class="news-card">
        <img class="cover" :src="resolveImage(item.image)" :alt="item.title" />

        <div class="card-content">
          <div class="card-top">
            <span class="pill">{{ categoryLabel(item.category) }}</span>
            <small>{{ formatDate(item.postedAt) }}</small>
          </div>

          <h2>{{ item.title }}</h2>
          <p class="company-name">{{ item.companyName }} · {{ item.author }}</p>
          <p class="summary">{{ item.content }}</p>

          <div class="meta-row">
            <button class="tiny-btn" type="button" @click="handleReaction(item.id)">
              <i class="bi bi-heart"></i> {{ item.likes }}
            </button>
            <span><i class="bi bi-chat-dots"></i> {{ item.comments }}</span>
            <span v-if="item.companyId === companyId" class="my-post">{{ t.yourPost }}</span>
          </div>

          <div class="comment-box" v-if="getComments(item.id).length">
            <div
              v-for="comment in getComments(item.id)"
              :key="comment.id || comment.createdAt"
              class="comment-item"
            >
              <strong>{{ comment.author || 'Member' }}</strong>
              <span>{{ comment.message || comment.content }}</span>
            </div>
          </div>

          <form class="comment-form" @submit.prevent="submitComment(item.id)">
            <input v-model="commentDrafts[item.id]" type="text" placeholder="Write a comment..." />
            <button class="secondary-btn" type="submit">Comment</button>
          </form>

          <div class="actions-row" v-if="Number(item.companyId) === Number(companyId)">
            <button class="ghost-btn" type="button" @click="editActuality(item)">Edit</button>
          </div>
        </div>
      </article>
    </section>

    <section v-else class="empty-state">{{ t.empty }}</section>
  </div>
</template>

<script setup>
import { computed, inject, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useCompanyActualities } from '@/compasables/useCompanyActualities'

const router = useRouter()
const locale = inject('locale', ref('en'))
const { fetchActualities, getCurrentCompanyId, addReaction, addComment } = useCompanyActualities()
const companyId = getCurrentCompanyId()

const posts = ref([])
const search = ref('')
const scopeFilter = ref('all')
const categoryFilter = ref('all')
const commentDrafts = ref({})

onMounted(async () => {
  const data = await fetchActualities().catch(() => [])
  posts.value = data
})

const copy = {
  en: {
    eyebrow: 'Company / Actuality',
    title: 'Actuality Feed',
    subtitle: 'See your company news and updates posted by other companies.',
    createActuality: 'Create Actuality',
    searchPlaceholder: 'Search title, company, author, content',
    scope: 'Scope',
    category: 'Category',
    allNews: 'All news',
    myNews: 'My news',
    otherNews: 'Other companies',
    allCategories: 'All categories',
    recruitment: 'Recruitment',
    event: 'Event',
    announcement: 'Announcement',
    yourPost: 'Your post',
    empty: 'No actuality item matches your filters.',
  },
  fr: {
    eyebrow: 'Entreprise / Actualite',
    title: "Fil d'actualite",
    subtitle: 'Consultez les actualites de votre entreprise et celles des autres entreprises.',
    createActuality: 'Creer une actualite',
    searchPlaceholder: 'Rechercher titre, entreprise, auteur, contenu',
    scope: 'Portee',
    category: 'Categorie',
    allNews: 'Toutes les actualites',
    myNews: 'Mes actualites',
    otherNews: 'Autres entreprises',
    allCategories: 'Toutes les categories',
    recruitment: 'Recrutement',
    event: 'Evenement',
    announcement: 'Annonce',
    yourPost: 'Votre publication',
    empty: 'Aucune actualite ne correspond a vos filtres.',
  },
}

const t = computed(() => copy[locale.value] || copy.en)

const categoryOptions = computed(() => [
  { value: 'Recruitment', label: t.value.recruitment },
  { value: 'Event', label: t.value.event },
  { value: 'Announcement', label: t.value.announcement },
])

const filteredNews = computed(() => {
  const term = search.value.trim().toLowerCase()

  return posts.value.filter((item) => {
    if (scopeFilter.value === 'mine' && Number(item.companyId) !== Number(companyId)) return false
    if (scopeFilter.value === 'others' && Number(item.companyId) === Number(companyId)) return false
    if (categoryFilter.value !== 'all' && item.category !== categoryFilter.value) return false

    if (!term) return true

    return [item.title, item.companyName, item.author, item.content]
      .join(' ')
      .toLowerCase()
      .includes(term)
  })
})

const categoryLabel = (category) => {
  if (category === 'Recruitment') return t.value.recruitment
  if (category === 'Event') return t.value.event
  return t.value.announcement
}

const resolveImage = (value) => {
  if (!value || typeof value !== 'string' || value.trim() === '') {
    return 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d'
  }

  return value
}

const getComments = (id) => {
  const target = posts.value.find((item) => Number(item.id) === Number(id))
  return Array.isArray(target?.commentList) ? target.commentList : []
}

const handleReaction = (id) => {
  addReaction(id)
}

const submitComment = (id) => {
  const value = (commentDrafts.value[id] || '').trim()
  if (!value) return

  addComment(id, value)
  commentDrafts.value[id] = ''
}

const formatDate = (value) => {
  if (!value) return '--'
  return new Date(value).toLocaleDateString(locale.value === 'fr' ? 'fr-FR' : 'en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const editActuality = (item) => {
  if (!item?.id) return
  router.push({ name: 'companyEditActuality', params: { id: item.id } })
}

const goToCreate = () => {
  router.push({ name: 'companyCreateActuality' })
}
</script>

<style scoped>
.actuality-list-page {
  display: grid;
  gap: 14px;
}

.page-header,
.toolbar-card,
.news-card,
.empty-state {
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

.page-header h1,
.news-card h2 {
  margin: 0;
  color: var(--text);
}

.intro,
label span,
.summary,
.company-name,
.card-top small,
.empty-state {
  color: var(--muted);
}

.toolbar-card {
  padding: 12px;
  display: grid;
  gap: 10px;
}

.search-box {
  display: flex;
  align-items: center;
  gap: 8px;
  border: 1px solid var(--border);
  border-radius: 12px;
  background: var(--surface-soft);
  padding: 10px 12px;
}

.search-box input,
select {
  width: 100%;
  border: none;
  outline: none;
  background: transparent;
  color: var(--text);
}

.toolbar-filters {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 8px;
}

label {
  display: grid;
  gap: 6px;
}

select {
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 10px 12px;
  background: var(--surface);
}

.news-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
}

.news-card {
  overflow: hidden;
}

.cover {
  width: 100%;
  height: 170px;
  object-fit: cover;
  display: block;
}

.card-content {
  padding: 12px;
  display: grid;
  gap: 8px;
}

.card-top,
.meta-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.pill,
.my-post {
  border-radius: 999px;
  padding: 4px 10px;
  font-size: 0.74rem;
  font-weight: 700;
}

.pill {
  background: rgba(37, 99, 235, 0.12);
  color: #1d4ed8;
}

.my-post {
  background: rgba(16, 185, 129, 0.16);
  color: #047857;
}

.company-name,
.summary {
  margin: 0;
}

.meta-row span {
  color: var(--muted);
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.actions-row {
  display: flex;
  justify-content: flex-end;
}

.ghost-btn {
  border: 1px solid var(--border);
  background: var(--surface);
  color: var(--text);
  border-radius: 999px;
  padding: 8px 12px;
  cursor: pointer;
}

.primary-btn {
  border: none;
  border-radius: 999px;
  padding: 9px 12px;
  cursor: pointer;
  background: var(--primary);
  color: #fff;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.empty-state {
  padding: 18px;
  text-align: center;
}

@media (max-width: 980px) {
  .page-header {
    flex-direction: column;
  }

  .page-header .primary-btn {
    width: 100%;
    justify-content: center;
  }

  .toolbar-filters,
  .news-grid {
    grid-template-columns: 1fr;
  }
}
</style>
