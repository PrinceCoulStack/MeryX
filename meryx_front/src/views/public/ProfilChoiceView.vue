<template>
  <section class="profile-choice-page">
    <div class="profile-choice-shell">
      <div class="profile-choice-hero">
        <div class="hero-copy">
          <p class="eyebrow">INSCRIPTION MERYX</p>
          <h1>Choisissez l'espace qui correspond a votre role.</h1>
          <p class="hero-description">
            MeryX structure l'entree des etudiants, des universites et des entreprises autour d'un
            meme cadre de confiance. Selectionnez votre parcours pour lancer une inscription adaptee
            a vos usages.
          </p>

          <div class="hero-pills" aria-label="Points cles">
            <span>3 parcours dedies</span>
            <span>Activation guidee</span>
            <span>Experience claire sur mobile</span>
          </div>
        </div>

        <aside class="hero-panel" aria-label="Resume du demarrage">
          <p class="panel-label">DEMARRAGE</p>
          <div class="panel-stat">
            <strong>01</strong>
            <span>Choisissez votre profil</span>
          </div>
          <div class="panel-stat muted">
            <strong>02</strong>
            <span>Renseignez vos informations</span>
          </div>
          <div class="panel-stat muted">
            <strong>03</strong>
            <span>Accedez a votre espace MeryX</span>
          </div>

          <RouterLink to="/login" class="panel-link">J'ai deja un compte</RouterLink>
        </aside>
      </div>

      <div class="profile-grid">
        <RouterLink
          v-for="option in profileOptions"
          :key="option.to"
          :to="option.to"
          class="profile-card"
        >
          <div class="card-topline">
            <span class="card-badge">{{ option.badge }}</span>
            <span class="card-icon" aria-hidden="true">{{ option.icon }}</span>
          </div>

          <div class="card-content">
            <h2>{{ option.title }}</h2>
            <p>{{ option.description }}</p>
          </div>

          <ul class="card-features">
            <li v-for="feature in option.features" :key="feature">{{ feature }}</li>
          </ul>

          <div class="card-footer">
            <span>Creer ce profil</span>
            <span class="card-arrow" aria-hidden="true">→</span>
          </div>
        </RouterLink>
      </div>
    </div>
  </section>
</template>

<script setup>
const profileOptions = [
  {
    to: '/register/student',
    badge: 'ETUDIANT',
    icon: '01',
    title: 'Profil etudiant',
    description:
      'Presentez votre parcours, vos competences et vos ambitions pour gagner en visibilite.',
    features: [
      'Profil academique structure',
      'Mise en avant des competences',
      'Candidature plus rapide',
    ],
  },
  {
    to: '/register/university',
    badge: 'UNIVERSITE',
    icon: '02',
    title: 'Profil universite',
    description:
      'Cadrez vos cohortes, vos validations et vos interactions entreprises depuis un meme espace.',
    features: [
      'Pilotage des promotions',
      'Suivi des validations',
      'Coordination avec les partenaires',
    ],
  },
  {
    to: '/register/company',
    badge: 'ENTREPRISE',
    icon: '03',
    title: 'Profil entreprise',
    description:
      'Centralisez vos recrutements, vos besoins terrain et vos relations avec les talents formes.',
    features: ['Publication ciblee', 'Vue sur les profils', 'Collaboration avec les universites'],
  },
]
</script>

<style scoped>
.profile-choice-page {
  min-height: 100vh;
  padding: clamp(32px, 5vw, 56px) 20px;
  background:
    radial-gradient(circle at top left, rgba(212, 160, 23, 0.16), transparent 30%),
    linear-gradient(180deg, rgba(13, 43, 69, 0.04), rgba(13, 43, 69, 0)), var(--bg);
}

.profile-choice-shell {
  width: min(1180px, 100%);
  margin: 0 auto;
  display: grid;
  gap: 28px;
}

.profile-choice-hero {
  display: grid;
  grid-template-columns: minmax(0, 1.55fr) minmax(280px, 0.9fr);
  gap: 24px;
  align-items: stretch;
}

.hero-copy,
.hero-panel,
.profile-card {
  position: relative;
  overflow: hidden;
  border: 1px solid var(--border);
  border-radius: 28px;
  box-shadow: var(--shadow-soft);
}

.hero-copy {
  padding: clamp(28px, 4vw, 44px);
  background:
    linear-gradient(135deg, rgba(13, 43, 69, 0.96), rgba(17, 74, 106, 0.92)), var(--surface);
  color: var(--meryx-white);
}

.hero-copy::after {
  content: '';
  position: absolute;
  inset: auto -8% -26% auto;
  width: 220px;
  height: 220px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(212, 160, 23, 0.34), rgba(212, 160, 23, 0));
}

.eyebrow,
.panel-label,
.card-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  margin: 0;
  font-size: 0.76rem;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  font-weight: 800;
}

.eyebrow {
  color: rgba(242, 244, 247, 0.74);
}

.hero-copy h1 {
  margin: 14px 0 16px;
  max-width: 12ch;
  color: var(--meryx-white);
  font-size: clamp(2.2rem, 4vw, 4.1rem);
  line-height: 0.98;
  letter-spacing: -0.04em;
}

.hero-description {
  margin: 0;
  max-width: 62ch;
  color: rgba(242, 244, 247, 0.84);
  font-size: 1.03rem;
}

.hero-pills {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-top: 28px;
}

.hero-pills span {
  padding: 10px 14px;
  border-radius: 999px;
  border: 1px solid rgba(242, 244, 247, 0.12);
  background: rgba(248, 251, 255, 0.08);
  color: var(--meryx-white);
  font-weight: 600;
}

.hero-panel {
  display: grid;
  gap: 16px;
  padding: 24px;
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(248, 251, 255, 0.9));
}

body.theme-dark .hero-panel {
  background: linear-gradient(180deg, rgba(16, 63, 91, 0.96), rgba(22, 55, 88, 0.92));
}

.panel-label {
  color: var(--secondary);
}

.panel-stat {
  display: flex;
  gap: 14px;
  align-items: center;
  padding: 14px 16px;
  border-radius: 18px;
  background: rgba(17, 74, 106, 0.08);
}

.panel-stat strong {
  width: 42px;
  height: 42px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 14px;
  background: linear-gradient(135deg, rgba(212, 160, 23, 0.9), rgba(225, 182, 73, 0.96));
  color: var(--meryx-dark);
  font-size: 1rem;
}

.panel-stat span {
  color: var(--text);
  font-weight: 600;
}

.panel-stat.muted {
  background: rgba(17, 74, 106, 0.04);
}

.panel-link {
  margin-top: 8px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 52px;
  border-radius: 16px;
  background: rgba(17, 74, 106, 0.92);
  color: var(--meryx-white);
  font-weight: 700;
}

.panel-link:hover {
  color: var(--meryx-white);
}

.profile-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 20px;
}

.profile-card {
  display: grid;
  gap: 18px;
  padding: 24px;
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(242, 244, 247, 0.88));
  color: inherit;
  min-height: 100%;
  transition:
    transform 0.22s ease,
    border-color 0.22s ease,
    box-shadow 0.22s ease;
}

body.theme-dark .profile-card {
  background: linear-gradient(180deg, rgba(16, 63, 91, 0.98), rgba(13, 43, 69, 0.94));
}

.profile-card::before {
  content: '';
  position: absolute;
  inset: 0 auto auto 0;
  width: 100%;
  height: 4px;
  background: linear-gradient(90deg, rgba(212, 160, 23, 0.92), rgba(17, 74, 106, 0.4));
}

.profile-card:hover {
  transform: translateY(-6px);
  border-color: rgba(212, 160, 23, 0.38);
  box-shadow: 0 18px 38px rgba(13, 43, 69, 0.16);
}

.card-topline {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.card-badge {
  color: var(--secondary);
}

.card-icon {
  width: 48px;
  height: 48px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 16px;
  background: rgba(212, 160, 23, 0.12);
  color: var(--primary);
  font-weight: 800;
}

.card-content h2 {
  margin: 0 0 10px;
  font-size: 1.5rem;
}

.card-content p {
  margin: 0;
  color: var(--muted);
}

.card-features {
  display: grid;
  gap: 10px;
  padding: 0;
  margin: 0;
  list-style: none;
}

.card-features li {
  position: relative;
  padding-left: 18px;
  color: var(--text);
}

.card-features li::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0.62em;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--primary);
  transform: translateY(-50%);
}

.card-footer {
  margin-top: auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding-top: 6px;
  color: var(--heading);
  font-weight: 800;
}

.card-arrow {
  font-size: 1.2rem;
  transition: transform 0.2s ease;
}

.profile-card:hover .card-arrow {
  transform: translateX(4px);
}

@media (max-width: 980px) {
  .profile-choice-hero,
  .profile-grid {
    grid-template-columns: 1fr;
  }

  .hero-copy h1 {
    max-width: none;
  }
}

@media (max-width: 640px) {
  .profile-choice-page {
    padding-inline: 14px;
  }

  .hero-copy,
  .hero-panel,
  .profile-card {
    border-radius: 22px;
  }

  .hero-copy,
  .hero-panel,
  .profile-card {
    padding: 20px;
  }

  .hero-description,
  .card-content p,
  .panel-stat span {
    font-size: 0.96rem;
  }
}
</style>
