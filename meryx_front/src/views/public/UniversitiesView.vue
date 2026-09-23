<template>
  <section class="container">
    <h1>Partner Universities</h1>

    <div class="grid">
      <article
        v-for="university in universities"
        :key="university.id"
        class="card"
      >
        <h2>{{ university.name }}</h2>

        <p>{{ university.city }}</p>

        <p>{{ university.description }}</p>

        <button>
          View Details
        </button>
      </article>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue'

const universities = ref([
  {
    id: 1,
    name: 'University of Bamako',
    city: 'Bamako',
    description:
      'Leading institution in engineering and technology.'
  },
  {
    id: 2,
    name: 'Mali Business School',
    city: 'Sikasso',
    description:
      'Specialized in entrepreneurship and management.'
  },
  {
    id: 3,
    name: 'Institute of Mining Sciences',
    city: 'Kayes',
    description:
      'Focused on mining and geological studies.'
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
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
}

.card {
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 3px 10px rgba(0,0,0,.08);
}

button {
  margin-top: 1rem;
  padding: 10px 18px;
}
</style>
