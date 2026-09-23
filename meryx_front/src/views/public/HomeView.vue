<template>
  <div class="landing-page">
    <section class="hero-section reveal-up">
      <img
        class="crystal crystal-left"
        src="@/assets/logos/MeryXBGu.png"
        alt=""
        aria-hidden="true"
      />
      <img
        class="crystal crystal-right"
        src="@/assets/logos/MeryXBGu.png"
        alt=""
        aria-hidden="true"
      />

      <div class="shell hero-shell">
        <div class="hero-copy">
          <p class="eyebrow">MERYX STUDENT INTELLIGENCE</p>
          <h1>
            Votre progression merite d'etre
            <span>reconnue.</span>
          </h1>

          <p class="hero-lead">
            MeryX transforme vos performances academiques, vos competences et vos certifications en
            un profil professionnel fiable qui evolue avec vous.
          </p>

          <div class="hero-points">
            <article>
              <i class="bi bi-bar-chart-line"></i>
              <div>
                <strong>MESUREZ</strong>
                <p>Vos performances</p>
              </div>
            </article>
            <article>
              <i class="bi bi-graph-up-arrow"></i>
              <div>
                <strong>PROGRESSEZ</strong>
                <p>Chaque jour</p>
              </div>
            </article>
            <article>
              <i class="bi bi-stars"></i>
              <div>
                <strong>ACCEDEZ</strong>
                <p>A de nouvelles opportunites</p>
              </div>
            </article>
          </div>

          <div class="hero-actions">
            <RouterLink to="/register/student" class="cta-primary"
              >Commencer mon parcours</RouterLink
            >
            <RouterLink to="/companies" class="cta-link">
              Vous recrutez ? Decouvrir MeryX Entreprises
            </RouterLink>
          </div>
        </div>

        <div class="hero-visual">
          <article class="profile-panel">
            <header>
              <div class="avatar">AD</div>
              <div>
                <h3>Aminata D.</h3>
                <p>Geologie &amp; Mines · Licence 3</p>
                <span class="verified"><i class="bi bi-patch-check-fill"></i> Profil verifie</span>
              </div>
              <div class="progress-pill">
                <span>82%</span>
                <small>Progression globale</small>
              </div>
            </header>

            <div class="panel-grid">
              <section class="skills-chart">
                <h4>Mes competences</h4>
                <div class="radar">
                  <div class="polygon one"></div>
                  <div class="polygon two"></div>
                  <div class="polygon three"></div>
                </div>
                <div class="skill-lines">
                  <p>Academique 85%</p>
                  <p>Numerique 90%</p>
                  <p>Professionnel 78%</p>
                </div>
              </section>

              <section class="cert-list" id="certifications">
                <h4>Certifications</h4>
                <ul >
                  <li>
                    <i class="bi bi-award"></i> Cartographie geologique <small>Mai 2024</small>
                  </li>
                  <li><i class="bi bi-award"></i> Geostatistique <small>Avr 2024</small></li>
                  <li><i class="bi bi-award"></i> Anglais technique <small>Fev 2024</small></li>
                </ul>
                <p class="next-step">Prochaine etape : Renforcer votre anglais technique.</p>
              </section>
            </div>
          </article>
        </div>
      </div>
    </section>

    <section class="trust-section reveal-up" id="how-it-works">
      <div class="shell">
        <h2>
          Un diplome dit ce que vous avez etudie.<br />
          <span>Pas forcement ce que vous savez faire.</span>
        </h2>
        <p class="trust-intro">
          Chaque annee, des etudiants progressent, developpent des competences et se preparent au
          monde professionnel. Pourtant, une grande partie de cette valeur reste difficile a mesurer
          et a montrer.
        </p>

        <div class="trust-cards">
          <article class="trust-card">
            <span class="icon-circle"><i class="bi bi-mortarboard"></i></span>
            <small>Pour les etudiants</small>
            <p>Mes efforts sont-ils reellement visibles ?</p>
          </article>

          <article class="trust-card">
            <span class="icon-circle"><i class="bi bi-briefcase"></i></span>
            <small>Pour les entreprises</small>
            <p>Comment identifier rapidement les profils reellement pertinents ?</p>
          </article>

          <article class="trust-card">
            <span class="icon-circle"><i class="bi bi-building"></i></span>
            <small>Pour les universites</small>
            <p>Comment mesurer la progression et mieux preparer les etudiants au marche ?</p>
          </article>
        </div>
      </div>
    </section>

    <section class="process-section reveal-up">
      <div class="shell">
        <h2>
          La valeur existe deja.<br />
          <span>MeryX la rend visible.</span>
        </h2>

        <div class="process-steps">
          <article>
            <span>01</span>
            <i class="bi bi-bar-chart-line"></i>
            <h3>MESURER</h3>
            <p>Performance academique</p>
          </article>
          <i class="bi bi-arrow-right step-arrow"></i>
          <article>
            <span>02</span>
            <i class="bi bi-graph-up-arrow"></i>
            <h3>PROGRESSER</h3>
            <p>Competences et evolution</p>
          </article>
          <i class="bi bi-arrow-right step-arrow"></i>
          <article>
            <span>03</span>
            <i class="bi bi-patch-check"></i>
            <h3>CERTIFIER</h3>
            <p>Evaluations techniques</p>
          </article>
          <i class="bi bi-arrow-right step-arrow"></i>
          <article>
            <span>04</span>
            <i class="bi bi-person-badge"></i>
            <h3>ETRE RECONNU</h3>
            <p>Profil structure et fiable</p>
          </article>
          <i class="bi bi-arrow-right step-arrow"></i>
          <article>
            <span>05</span>
            <i class="bi bi-briefcase"></i>
            <h3>ACCEDER</h3>
            <p>Stages et emplois</p>
          </article>
        </div>
      </div>
    </section>

    <section class="value-section reveal-up" id="students-value">
      <div class="shell split">
        <div class="value-copy">
          <p class="eyebrow">POUR LES ETUDIANTS</p>
          <h2>Construisez un profil qui evolue avec vous.</h2>
          <p>
            Les etudiants ne creent pas seulement un CV statique. Leur profil grandit avec leurs
            performances academiques, competences, projets, certifications, langues, experiences et
            realisations.
          </p>
        </div>
        <article class="value-mockup student-mockup">
          <h3>Mon Profil MeryX</h3>
          <div class="stat-grid">
            <div><small>Progression</small><strong>82%</strong></div>
            <div><small>Competences</small><strong>24</strong></div>
            <div><small>Certifications</small><strong>7</strong></div>
            <div><small>Projets</small><strong>12</strong></div>
            <div><small>Resultats acad.</small><strong>16.8/20</strong></div>
            <div><small>Experiences</small><strong>3</strong></div>
          </div>
        </article>
      </div>
    </section>

    <section class="value-section reveal-up" id="companies-value">
      <div class="shell split reverse">
        <article class="value-mockup company-mockup">
          <h3>Decouverte de talents</h3>
          <ul>
            <li><i class="bi bi-check-circle"></i> Competences verifiees</li>
            <li><i class="bi bi-check-circle"></i> Performance academique analysee</li>
            <li><i class="bi bi-check-circle"></i> Certifications et projets qualifies</li>
            <li><i class="bi bi-check-circle"></i> Niveau de langues et experiences</li>
          </ul>
        </article>
        <div class="value-copy">
          <p class="eyebrow">POUR LES ENTREPRISES</p>
          <h2>Ne cherchez plus seulement un CV. Decouvrez le potentiel.</h2>
          <p>
            MeryX permet aux entreprises de decouvrir des etudiants selon des donnees fiables :
            competences, performances academiques, certifications, projets, experiences et langues.
          </p>
          <RouterLink to="/companies" class="cta-link strong"
            >Decouvrir MeryX Entreprises</RouterLink
          >
        </div>
      </div>
    </section>

    <section class="value-section reveal-up" id="universities-value">
      <div class="shell split">
        <div class="value-copy">
          <p class="eyebrow">POUR LES UNIVERSITES</p>
          <h2>Transformez les donnees academiques en parcours visibles.</h2>
          <p>
            Les universites peuvent gerer les profils, suivre la progression academique, verifier
            les certifications, monitorer les competences, suivre les stages et mieux preparer les
            etudiants a l'emploi.
          </p>
          <RouterLink to="/universities" class="cta-link strong"
            >Decouvrir MeryX Universites</RouterLink
          >
        </div>
        <article class="value-mockup university-mockup">
          <h3>Tableau universite</h3>
          <div class="timeline">
            <p><span></span> Dossiers academiques centralises</p>
            <p><span></span> Certifications verifiees</p>
            <p><span></span> Suivi des competences par filiere</p>
            <p><span></span> Suivi des stages et de l'insertion</p>
          </div>
        </article>
      </div>
    </section>

    <section class="final-cta reveal-up">
      <div class="shell">
        <article class="cta-card">
          <h2>Tout le monde peut gravir les echelons.</h2>
          <p>
            Commencez a construire un profil qui evolue avec vos competences et vous mene la ou vous
            voulez aller.
          </p>
          <div class="cta-actions">
            <RouterLink to="/register/student" class="cta-primary"
              >Creer mon profil MeryX</RouterLink
            >
            <RouterLink to="/companies" class="cta-outline">Decouvrir MeryX Entreprises</RouterLink>
          </div>
        </article>
      </div>
    </section>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'
</script>

<style scoped>
.landing-page {
  background:
    radial-gradient(circle at 100% 0%, rgba(30, 63, 102, 0.12), transparent 40%),
    radial-gradient(circle at 0% 20%, rgba(17, 74, 106, 0.08), transparent 30%), #f2f4f7;
}

.shell {
  width: min(1220px, calc(100% - 30px));
  margin: 0 auto;
}

.hero-section {
  position: relative;
  overflow: hidden;
  padding: 36px 0 32px;
}

h2, h3, h4, li {
  color: #0d2b45;
}

.hero-shell {
  display: grid;
  grid-template-columns: 1.1fr 1fr;
  gap: 24px;
  align-items: center;
}

.hero-copy,
.hero-visual,
.value-copy,
.value-mockup {
  min-width: 0;
}

.crystal {
  position: absolute;
  opacity: 0.14;
  pointer-events: none;
}

.crystal-left {
  width: 220px;
  left: -70px;
  top: 220px;
}

.crystal-right {
  width: 180px;
  right: -50px;
  top: 80px;
}

.eyebrow {
  margin: 0;
  color: #114a6a;
  font-weight: 700;
  letter-spacing: 0.08em;
  font-size: 0.75rem;
}

.hero-copy h1 {
  margin: 8px 0 14px;
  color: #0d2b45;
  font-size: clamp(2rem, 4vw, 3.45rem);
  max-width: 14ch;
}

.hero-copy h1 span,
.process-section h2 span,
.trust-section h2 span {
  color: #d4a017;
}

.hero-lead {
  margin: 0;
  color: rgba(13, 43, 69, 0.78);
  max-width: 54ch;
}

.hero-points {
  margin-top: 18px;
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 10px;
}

.hero-points article {
  border: 1px solid rgba(13, 43, 69, 0.1);
  background: rgba(255, 255, 255, 0.82);
  border-radius: 14px;
  padding: 10px;
  display: flex;
  gap: 8px;
}

.hero-points i {
  color: #114a6a;
}

.hero-points strong {
  color: #0d2b45;
  font-size: 0.8rem;
}

.hero-points p {
  margin: 2px 0 0;
  font-size: 0.78rem;
  color: rgba(13, 43, 69, 0.72);
}

.hero-actions {
  margin-top: 18px;
  display: flex;
  align-items: center;
  gap: 14px;
  flex-wrap: wrap;
}

.cta-primary {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 12px 18px;
  border-radius: 12px;
  background: linear-gradient(120deg, #114a6a, #1e3f66);
  border: 1px solid rgba(17, 74, 106, 0.65);
  color: #f2f4f7;
  font-weight: 700;
  box-shadow: 0 12px 22px rgba(13, 43, 69, 0.22);
}

.cta-primary:hover {
  color: #f2f4f7;
  transform: translateY(-1px);
}

.cta-link {
  color: #114a6a;
  font-weight: 600;
}

.cta-link.strong {
  display: inline-flex;
  margin-top: 12px;
}

.profile-panel {
  border-radius: 24px;
  border: 1px solid rgba(13, 43, 69, 0.12);
  background: rgba(255, 255, 255, 0.92);
  box-shadow: 0 20px 36px rgba(13, 43, 69, 0.16);
  padding: 16px;
}

.profile-panel header {
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 12px;
  align-items: center;
}

.avatar {
  width: 62px;
  height: 62px;
  border-radius: 18px;
  background: linear-gradient(145deg, #1e3f66, #114a6a);
  color: #f2f4f7;
  font-weight: 800;
  display: grid;
  place-items: center;
}

.profile-panel h3 {
  margin: 0;
}

.profile-panel header p {
  margin: 2px 0;
  color: rgba(13, 43, 69, 0.72);
  font-size: 0.86rem;
}

.verified {
  color: #1e3f66;
  font-size: 0.8rem;
  font-weight: 600;
}

.progress-pill {
  text-align: center;
  border-radius: 999px;
  border: 6px solid rgba(30, 63, 102, 0.16);
  width: 84px;
  height: 84px;
  display: grid;
  place-items: center;
}

.progress-pill span {
  color: #114a6a;
  font-weight: 800;
}

.progress-pill small {
  font-size: 0.6rem;
  color: rgba(13, 43, 69, 0.7);
}

.panel-grid {
  margin-top: 14px;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

.skills-chart,
.cert-list {
  border: 1px solid rgba(13, 43, 69, 0.1);
  background: #ffffff;
  border-radius: 14px;
  padding: 12px;
}

.skills-chart h4,
.cert-list h4 {
  margin: 0 0 8px;
  color: #0d2b45;
}

.radar {
  position: relative;
  margin: 0 auto;
  width: 150px;
  height: 120px;
}

.polygon {
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
  clip-path: polygon(50% 0%, 100% 38%, 82% 100%, 18% 100%, 0% 38%);
}

.polygon.one {
  width: 120px;
  height: 96px;
  border: 2px dashed rgba(17, 74, 106, 0.4);
}

.polygon.two {
  width: 88px;
  height: 70px;
  border: 2px solid rgba(17, 74, 106, 0.35);
}

.polygon.three {
  width: 68px;
  height: 54px;
  background: linear-gradient(130deg, rgba(30, 63, 102, 0.4), rgba(17, 74, 106, 0.22));
}

.skill-lines {
  margin-top: 6px;
  display: grid;
  gap: 4px;
}

.skill-lines p {
  margin: 0;
  font-size: 0.76rem;
  color: rgba(13, 43, 69, 0.72);
}

.cert-list ul {
  margin: 0;
  padding: 0;
  list-style: none;
  display: grid;
  gap: 8px;
}

.cert-list li {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 6px;
  font-size: 0.84rem;
}

.cert-list li i {
  color: #114a6a;
}

.cert-list li small {
  color: rgba(13, 43, 69, 0.65);
}

.next-step {
  margin: 10px 0 0;
  padding: 8px;
  border-radius: 10px;
  background: rgba(212, 160, 23, 0.12);
  color: #0d2b45;
  font-size: 0.8rem;
}

.trust-section {
  padding: 34px 0;
  text-align: center;
}

.trust-section h2,
.process-section h2 {
  margin: 0;
  color: #0d2b45;
}

.trust-intro {
  max-width: 82ch;
  margin: 12px auto 0;
  color: rgba(13, 43, 69, 0.72);
}

.trust-cards {
  margin-top: 20px;
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px;
}

.trust-card {
  background: #ffffff;
  border: 1px solid rgba(13, 43, 69, 0.1);
  border-radius: 18px;
  box-shadow: 0 10px 22px rgba(13, 43, 69, 0.08);
  padding: 16px;
  text-align: left;
}

.icon-circle {
  width: 42px;
  height: 42px;
  border-radius: 999px;
  background: linear-gradient(140deg, #114a6a, #1e3f66);
  color: #f2f4f7;
  display: grid;
  place-items: center;
}

.trust-card small {
  display: block;
  margin-top: 10px;
  color: rgba(13, 43, 69, 0.62);
}

.trust-card p {
  margin: 4px 0 0;
  color: #0d2b45;
  font-weight: 600;
}

.process-section {
  padding: 26px 0;
}

.process-steps {
  margin-top: 18px;
  display: grid;
  grid-template-columns: repeat(9, auto);
  gap: 8px;
  align-items: center;
  justify-content: center;
}

.process-steps article {
  width: 170px;
  padding: 14px 12px;
  border: 1px solid rgba(13, 43, 69, 0.1);
  border-radius: 16px;
  background: #ffffff;
  text-align: center;
}

.process-steps article span {
  color: #114a6a;
  font-weight: 700;
  font-size: 0.77rem;
}

.process-steps article i {
  display: block;
  margin: 8px 0;
  color: #114a6a;
  font-size: 1.2rem;
}

.process-steps article h3 {
  margin: 0;
  font-size: 0.92rem;
}

.process-steps article p {
  margin: 4px 0 0;
  color: rgba(13, 43, 69, 0.72);
  font-size: 0.82rem;
}

.step-arrow {
  color: rgba(13, 43, 69, 0.35);
  font-size: 1rem;
}

.value-section {
  padding: 28px 0;
}

.split {
  display: grid;
  grid-template-columns: 1fr 1fr;
  align-items: center;
  gap: 20px;
}

.split.reverse {
  direction: rtl;
}

.split.reverse > * {
  direction: ltr;
}

.value-copy h2 {
  margin: 6px 0 10px;
  color: #0d2b45;
  font-size: clamp(1.45rem, 2.5vw, 2rem);
}

.value-copy p {
  margin: 0;
  color: rgba(13, 43, 69, 0.74);
}

.value-mockup {
  border-radius: 20px;
  border: 1px solid rgba(13, 43, 69, 0.12);
  background: linear-gradient(150deg, #ffffff, #eef4fb);
  box-shadow: 0 18px 32px rgba(13, 43, 69, 0.11);
  padding: 16px;
}

.value-mockup h3 {
  margin: 0;
  color: #0d2b45;
}

.stat-grid {
  margin-top: 12px;
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 8px;
}

.stat-grid div {
  border: 1px solid rgba(13, 43, 69, 0.12);
  border-radius: 12px;
  padding: 10px;
  background: #ffffff;
}

.stat-grid small {
  color: rgba(13, 43, 69, 0.65);
  font-size: 0.72rem;
}

.stat-grid strong {
  display: block;
  margin-top: 3px;
  color: #0d2b45;
  font-size: 1.05rem;
}

.company-mockup ul {
  margin: 12px 0 0;
  padding: 0;
  list-style: none;
  display: grid;
  gap: 7px;
}

.company-mockup li {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #0d2b45;
}

.company-mockup li i {
  color: #114a6a;
}

.timeline {
  margin-top: 12px;
  display: grid;
  gap: 10px;
}

.timeline p {
  margin: 0;
  display: flex;
  align-items: center;
  gap: 8px;
  color: #0d2b45;
}

.timeline p span {
  width: 10px;
  height: 10px;
  border-radius: 999px;
  background: #d4a017;
  box-shadow: 0 0 0 5px rgba(212, 160, 23, 0.2);
}

.final-cta {
  padding: 32px 0 44px;
}

.cta-card {
  border-radius: 22px;
  border: 1px solid rgba(13, 43, 69, 0.12);
  background:
    radial-gradient(circle at 0% 0%, rgba(17, 74, 106, 0.12), transparent 36%),
    radial-gradient(circle at 90% 100%, rgba(30, 63, 102, 0.14), transparent 46%), #ffffff;
  box-shadow: 0 18px 30px rgba(13, 43, 69, 0.12);
  padding: 22px;
  text-align: center;
}

.cta-card h2 {
  margin: 0;
}

.cta-card p {
  margin: 10px auto 0;
  max-width: 72ch;
  color: rgba(13, 43, 69, 0.74);
}

.cta-actions {
  margin-top: 16px;
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  gap: 10px;
}

.cta-outline {
  padding: 12px 18px;
  border-radius: 12px;
  border: 1px solid rgba(13, 43, 69, 0.24);
  color: #0d2b45;
  font-weight: 700;
  background: #ffffff;
}

.cta-outline:hover {
  border-color: rgba(17, 74, 106, 0.48);
}

.reveal-up {
  animation: revealUp 0.7s ease both;
}

.trust-card,
.process-steps article,
.value-mockup,
.cta-primary,
.cta-outline {
  transition:
    transform 0.22s ease,
    box-shadow 0.22s ease;
}

.trust-card:hover,
.process-steps article:hover,
.value-mockup:hover {
  transform: translateY(-3px);
  box-shadow: 0 16px 28px rgba(13, 43, 69, 0.14);
}

@keyframes revealUp {
  from {
    opacity: 0;
    transform: translateY(16px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 1280px) {
  .hero-copy h1 {
    max-width: 16ch;
  }

  .hero-points {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 1040px) {
  .hero-shell,
  .split,
  .split.reverse {
    grid-template-columns: 1fr;
    direction: ltr;
  }

  .hero-copy h1 {
    max-width: none;
  }

  .trust-cards {
    grid-template-columns: 1fr 1fr;
  }

  .process-steps {
    grid-template-columns: 1fr;
    justify-items: stretch;
  }

  .process-steps article {
    width: auto;
  }

  .step-arrow {
    justify-self: center;
    transform: rotate(90deg);
  }
}

@media (max-width: 760px) {
  .hero-section {
    padding-top: 20px;
  }

  .hero-copy h1 {
    font-size: clamp(1.8rem, 9vw, 2.4rem);
  }

  .profile-panel header {
    grid-template-columns: auto 1fr;
  }

  .progress-pill {
    grid-column: span 2;
    justify-self: start;
  }

  .panel-grid,
  .trust-cards,
  .stat-grid {
    grid-template-columns: 1fr;
  }

  .cta-primary,
  .cta-outline {
    width: 100%;
    justify-content: center;
  }

  .cta-link {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .profile-panel {
    padding: 12px;
  }

  .avatar {
    width: 52px;
    height: 52px;
  }

  .progress-pill {
    width: 74px;
    height: 74px;
  }
}
</style>
