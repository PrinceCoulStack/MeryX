<template>
  <div class="unauthorized-page">
    <div class="card">
      <div class="icon">
        <i class="bi bi-lock-fill"></i>
      </div>

      <h1>403</h1>

      <h2>Access Denied</h2>

      <p>
        You do not have permission to access this page.
      </p>

      <div class="actions">
        <button @click="goBack" class="btn btn-secondary">
          Go Back
        </button>

        <RouterLink to="/" class="btn btn-primary">
          Home Page
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { RouterLink, useRouter } from 'vue-router'

const router = useRouter()

function goBack() {
  router.back()
}
</script>

<style scoped>
.unauthorized-page {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: #f8fafc;
  padding: 2rem;
}

.card {
  max-width: 500px;
  width: 100%;
  background: white;
  text-align: center;
  padding: 3rem;
  border-radius: 20px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

.icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

h1 {
  font-size: 5rem;
  color: #D4B26A;
  margin: 0;
}

h2 {
  margin: 1rem 0;
  color: #0d2b45;
}

p {
  color: #64748b;
  margin-bottom: 2rem;
  line-height: 1.6;
}

.actions {
  display: flex;
  justify-content: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.btn {
  padding: 12px 24px;
  border-radius: 10px;
  text-decoration: none;
  border: none;
  cursor: pointer;
  font-size: 14px;
}

.btn-primary {
  background: #0d2b45;
  color: white;
}

.btn-primary:hover {
  background: #D4A017;
}

.btn-secondary {
  background: #e2e8f0;
  color: #334155;
}

.btn-secondary:hover {
  background: #cbd5e1;
}
</style>
