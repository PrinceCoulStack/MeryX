<template>
  <section class="container">
    <div class="header">
      <h1>Partner Companies</h1>
      <p>
        Explore companies collaborating with MeryX to provide internships,
        jobs, scholarships, and professional opportunities.
      </p>
    </div>

    <div class="grid">
      <article
        v-for="company in companies"
        :key="company.id"
        class="card"
      >
        <img
          :src="company.logo"
          :alt="company.name"
          class="logo"
        />

        <h2>{{ company.name }}</h2>

        <span class="industry">
          {{ company.industry }}
        </span>

        <p>{{ company.description }}</p>

        <div class="info">
          <span>📍 {{ company.location }}</span>
          <span>👥 {{ company.employees }} employees</span>
        </div>

        <button @click="viewCompany(company.id)">
          View Profile
        </button>
      </article>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const companies = ref([
  {
    id: 1,
    name: 'Resolute Mining',
    industry: 'Mining',
    location: 'Bamako, Mali',
    employees: 1200,
    description:
      'International mining company providing internships and graduate programs.',
    logo: 'https://via.placeholder.com/120'
  },
  {
    id: 2,
    name: 'Orange Mali',
    industry: 'Telecommunications',
    location: 'Bamako, Mali',
    employees: 850,
    description:
      'Technology and telecommunications leader supporting innovation programs.',
    logo: 'https://via.placeholder.com/120'
  },
  {
    id: 3,
    name: 'Tech Innov Africa',
    industry: 'Software Engineering',
    location: 'Dakar, Senegal',
    employees: 300,
    description:
      'Software company recruiting graduates and offering remote internships.',
    logo: 'https://via.placeholder.com/120'
  }
])

function viewCompany(id) {
  router.push(`/companies/${id}`)
}
</script>

<style scoped>
.container {
  max-width: 1400px;
  margin: auto;
  padding: 3rem;
}

.header {
  text-align: center;
  margin-bottom: 3rem;
}

.header h1 {
  margin-bottom: 1rem;
}

.header p {
  color: #666;
  max-width: 700px;
  margin: auto;
}

.grid {
  display: grid;
  gap: 2rem;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
}

.card {
  background: white;
  padding: 2rem;
  border-radius: 16px;
  box-shadow: 0 6px 18px rgba(0,0,0,.08);
  text-align: center;
  transition: 0.3s;
}

.card:hover {
  transform: translateY(-6px);
}

.logo {
  width: 100px;
  height: 100px;
  object-fit: contain;
  margin-bottom: 1rem;
}

.industry {
  display: inline-block;
  margin-bottom: 1rem;
  padding: 6px 12px;
  border-radius: 20px;
  background: #e0f2fe;
  color: #D4A017;
}

.info {
  margin: 1rem 0;
  display: flex;
  flex-direction: column;
  gap: .5rem;
  color: #666;
}

button {
  margin-top: 1rem;
  border: none;
  background: #0d2b45;
  color: white;
  padding: 12px 20px;
  border-radius: 8px;
  cursor: pointer;
}
</style>
