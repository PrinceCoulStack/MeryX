<template>
  <div class="dashboard">

    <h1>Admin Dashboard</h1>

    <div class="cards">
      <div class="card">
        <h3>Total Students</h3>
        <p>{{ stats.students }}</p>
      </div>

      <div class="card">
        <h3>Universities</h3>
        <p>{{ stats.universities }}</p>
      </div>

      <div class="card">
        <h3>Companies</h3>
        <p>{{ stats.companies }}</p>
      </div>

      <div class="card">
        <h3>Applications</h3>
        <p>{{ stats.applications }}</p>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
// import api from '@/api/axios'

const stats = ref({
  students: 0,
  universities: 0,
  companies: 0,
  applications: 0
})

onMounted(async () => {
  // replace later with Symfony API
  stats.value = {
    students: 1200,
    universities: 35,
    companies: 80,
    applications: 4500
  }
})
</script>

<style scoped>
.dashboard {
  padding: 20px;
  width: 100%;
}

.cards {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  margin-top: 20px;
}

.card {
  background: white;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  text-align: center;
}
</style>
