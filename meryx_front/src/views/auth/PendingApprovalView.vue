<template>
  <div class="pending-page">
    <div class="pending-card">
      <div class="pending-icon">⏳</div>
      <h1>{{ content.title }}</h1>
      <p>
        {{ content.body }}
      </p>
      <p class="sub">
        {{ content.hint }}
      </p>
      <div class="actions">
        <button class="btn-secondary" @click="logout">Se deconnecter</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.store'

const router = useRouter()
const auth = useAuthStore()

const normalizeRoleName = (value) => {
  if (!value) return null
  return String(value)
    .trim()
    .toUpperCase()
    .replace(/^ROLE_/, '')
    .replace(/\s+/g, '_')
    .replace(/-/g, '_')
}

const role = computed(() => normalizeRoleName(auth.role))

const content = computed(() => {
  if (role.value === 'STUDENT') {
    return {
      title: 'Profil etudiant en cours de verification',
      body: "Votre demande a ete transmise a l'universite selectionnee. Votre espace etudiant sera active des validation.",
      hint: "L'universite peut approuver, rejeter, ou maintenir votre demande en attente. Vous recevrez une notification apres decision.",
    }
  }

  if (role.value === 'COMPANY') {
    return {
      title: 'Compte entreprise en cours de validation',
      body: "Votre demande d'inscription a bien ete recue. Notre equipe examine actuellement les informations de votre entreprise.",
      hint: 'Vous recevrez une notification par email des que votre compte sera approuve.',
    }
  }

  return {
    title: 'Compte en cours de validation',
    body: "Votre demande est en cours d'analyse avant activation de votre espace MeryX.",
    hint: 'Vous recevrez une notification par email apres la decision.',
  }
})

function logout() {
  auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.pending-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f0f4f8;
  padding: 24px;
}

.pending-card {
  background: #ffffff;
  border-radius: 20px;
  padding: 48px 40px;
  max-width: 540px;
  width: 100%;
  text-align: center;
  box-shadow: 0 8px 40px rgba(13, 43, 69, 0.1);
  display: grid;
  gap: 16px;
}

.pending-icon {
  font-size: 3rem;
}

h1 {
  margin: 0;
  color: #0d2b45;
  font-size: 1.5rem;
}

p {
  margin: 0;
  color: rgba(13, 43, 69, 0.72);
  line-height: 1.7;
}

.sub {
  font-size: 0.9rem;
  color: rgba(13, 43, 69, 0.5);
}

.actions {
  margin-top: 8px;
}

.btn-secondary {
  padding: 12px 28px;
  border: 1px solid rgba(13, 43, 69, 0.2);
  border-radius: 12px;
  background: transparent;
  color: #114a6a;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s ease;
}

.btn-secondary:hover {
  background: rgba(17, 74, 106, 0.06);
}
</style>
