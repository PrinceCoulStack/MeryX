<template>
  <AuthShell
    eyebrow="MERYX ACCESS"
    title="Accedez a l'ecosysteme MeryX."
    description="Connectez-vous pour piloter vos recrutements, votre gouvernance universitaire ou vos operations quotidiennes depuis une interface claire et fiable."
    panel-eyebrow="CONNEXION"
    panel-title="Bienvenue"
    panel-description="Saisissez vos identifiants pour acceder a votre espace MeryX."
    :highlights="highlights"
  >
    <form class="auth-form" @submit.prevent="login">
      <p v-if="auth.authError" class="auth-error" role="alert">{{ auth.authError }}</p>

      <label class="field">
        <span>Adresse email</span>
        <input v-model="email" type="email" placeholder="vous@organisation.com" required />
      </label>

      <label class="field">
        <span>Mot de passe</span>
        <input v-model="password" type="password" placeholder="Votre mot de passe" required />
      </label>

      <button type="submit" class="submit-button" :disabled="auth.loading">
        {{ auth.loading ? 'Connexion en cours...' : 'Se connecter' }}
      </button>
    </form>

    <template #footer>
      <div class="auth-footer">
        <RouterLink to="/forgot-password">Mot de passe oublie ?</RouterLink>
        <p>
          Nouveau sur MeryX ?
          <RouterLink to="/register/company">Creer un compte entreprise</RouterLink>
          ou
          <RouterLink to="/register/university">universite</RouterLink>
        </p>
      </div>
    </template>
  </AuthShell>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth.store'
import { useRouter } from 'vue-router'
import { RouterLink } from 'vue-router'
import AuthShell from '@/components/common/AuthShell.vue'

const email = ref('')
const password = ref('')

const highlights = [
  {
    value: '01',
    title: 'Vue operationnelle',
    text: 'Accedez a vos tableaux de bord, vos activites et vos flux en un seul point.',
  },
  {
    value: '02',
    title: 'Donnees fiables',
    text: 'Travaillez sur des profils, performances et validations centralises.',
  },
  {
    value: '03',
    title: 'Collaboration directe',
    text: 'Universites et entreprises avancent sur la meme base de lecture.',
  },
]

const auth = useAuthStore()
const router = useRouter()

async function login() {
  const result = await auth.login({
    email: email.value,
    password: password.value,
  })

  if (result.ok) {
    const target = result.redirectTarget || auth.resolveRouteByRole()
    console.info('[auth] Redirect target selected after login', {
      role: auth.role,
      roles: auth.roles,
      interfaceKey: auth.interfaceKey,
      target,
    })
    router.push(target)
  }
}
</script>

<style scoped>
.auth-form {
  display: grid;
  gap: 16px;
  margin-top: 24px;
}

.auth-error {
  margin: 0;
  color: #b91c1c;
  background: rgba(220, 38, 38, 0.08);
  border: 1px solid rgba(220, 38, 38, 0.24);
  border-radius: 12px;
  padding: 10px 12px;
  font-weight: 600;
}

.field {
  display: grid;
  gap: 8px;
  text-align: left;
}

.field span {
  color: #0d2b45;
  font-weight: 700;
  font-size: 0.92rem;
}

.field input {
  width: 100%;
  padding: 14px 16px;
  border-radius: 14px;
  border: 1px solid rgba(13, 43, 69, 0.14);
  background: #ffffff;
  color: #0d2b45;
  outline: none;
  transition:
    border-color 0.2s ease,
    box-shadow 0.2s ease;
}

.field input:focus {
  border-color: rgba(17, 74, 106, 0.5);
  box-shadow: 0 0 0 4px rgba(17, 74, 106, 0.12);
}

.submit-button {
  width: 100%;
  padding: 14px 18px;
  border: 0;
  border-radius: 14px;
  background: linear-gradient(120deg, #d4a017, #e2b84a);
  color: #0d2b45;
  font-weight: 800;
  box-shadow: 0 12px 24px rgba(212, 160, 23, 0.24);
}

.submit-button:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.auth-footer {
  display: grid;
  gap: 12px;
}

.auth-footer a {
  color: #114a6a;
  font-weight: 700;
}

.auth-footer p {
  margin: 0;
  color: rgba(13, 43, 69, 0.72);
}
</style>
