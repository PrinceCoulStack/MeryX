<template>
  <div class="auth-container">
    <h1>Reset Password</h1>

    <form @submit.prevent="reset">
      <input v-model="password" type="password" placeholder="New password" required />
      <input v-model="confirm" type="password" placeholder="Confirm password" required />

      <button>Reset Password</button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/api/axios'

const password = ref('')
const confirm = ref('')

const route = useRoute()
const router = useRouter()

async function reset() {
  if (password.value !== confirm.value) {
    alert('Passwords do not match')
    return
  }

  await api.post('/reset-password', {
    token: route.query.token,
    password: password.value
  })

  alert('Password updated')
  router.push('/login')
}
</script>
