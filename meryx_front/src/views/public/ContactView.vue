<template>
  <section class="container">
    <h1>Contact Us</h1>

    <form @submit.prevent="submitForm">
      <input
        v-model="form.name"
        placeholder="Your name"
        required
      />

      <input
        v-model="form.email"
        type="email"
        placeholder="Your email"
        required
      />

      <textarea
        v-model="form.message"
        rows="6"
        placeholder="Your message"
        required
      />

      <button type="submit">
        Send Message
      </button>
    </form>
  </section>
</template>

<script setup>
import { reactive } from 'vue'

const form = reactive({
  name: '',
  email: '',
  message: ''
})

function submitForm() {
  console.log(form)

  alert('Message sent successfully!')

  form.name = ''
  form.email = ''
  form.message = ''
}
</script>

<style scoped>
.container {
  max-width: 700px;
  margin: auto;
  padding: 3rem;
}

form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

input,
textarea {
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 8px;
}

button {
  padding: 12px;
  border: none;
  background: #0d2b45;
  color: white;
  border-radius: 8px;
  cursor: pointer;
}
</style>
