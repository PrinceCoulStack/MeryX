<template>
  <div class="not-found">
    <h1>404</h1>

    <p>Page not found.</p>

    <RouterLink to="/">
      Return Home
    </RouterLink>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'
</script>

<style scoped>
.not-found {
  min-height: 80vh;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  gap: 16px;
}

h1 {
  font-size: 5rem;
}
</style>
