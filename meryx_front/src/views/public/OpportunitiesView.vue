<template>
  <section class="container">
    <h1>Available Opportunities</h1>

    <div class="grid">
      <article
        v-for="opportunity in opportunities"
        :key="opportunity.id"
        class="card"
      >
        <h3>{{ opportunity.title }}</h3>

        <p>{{ opportunity.company }}</p>

        <span>{{ opportunity.type }}</span>

        <button>Apply</button>
      </article>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue'

const opportunities = ref([
  {
    id: 1,
    title: 'Software Engineering Internship',
    company: 'Tech Mali',
    type: 'Internship'
  },
  {
    id: 2,
    title: 'Mining Research Scholarship',
    company: 'Mali Mining Group',
    type: 'Scholarship'
  },
  {
    id: 3,
    title: 'Graduate Program',
    company: 'Innovation Africa',
    type: 'Employment'
  }
])
</script>

<style scoped>
.container {
  padding: 3rem;
}

.grid {
  display: grid;
  gap: 2rem;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
}

.card {
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 4px 10px rgba(0,0,0,.08);
}

button {
  margin-top: 1rem;
  padding: 10px 20px;
}
</style>
