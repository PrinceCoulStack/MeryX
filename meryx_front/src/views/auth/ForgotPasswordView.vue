<template>
  <div class="auth-container">
    <h1>Forgot Password</h1>

    <form @submit.prevent="sendReset">
      <input v-model="email" type="email" placeholder="Enter email" required />

      <button>Send reset link</button>
    </form>

    <router-link to="/login">Back to login</router-link>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import api from '@/api/axios'

const email = ref('')

async function sendReset() {
  await api.post('/forgot-password', { email: email.value })

  alert('Reset link sent to email')
}
</script>
