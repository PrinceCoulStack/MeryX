<template>
  <div class="company-profile-page">
    <section class="page-top">
      <div>
        <p class="eyebrow">Company / Profile</p>
        <h1>Company profile</h1>
        <p class="intro">
          Manage your company information, contact details, and dashboard settings.
        </p>
      </div>
    </section>

    <section class="profile-summary">
      <div class="profile-card">
        <div class="company-avatar">AC</div>
        <div>
          <h2>Acme Corporation</h2>
          <p class="muted">Talent acquisition and employer branding</p>
        </div>
      </div>

      <div class="details-grid">
        <div class="detail-item">
          <span>Industry</span>
          <strong>Technology</strong>
        </div>
        <div class="detail-item">
          <span>Location</span>
          <strong>Bamako, Mali</strong>
        </div>
        <div class="detail-item">
          <span>Employees</span>
          <strong>120</strong>
        </div>
        <div class="detail-item">
          <span>Open roles</span>
          <strong>6</strong>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup></script>

<style scoped>
.company-profile-page {
  display: grid;
  gap: 24px;
  width: 200%;
}

.page-top {
  display: grid;
  gap: 14px;
}

.eyebrow {
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 0.16em;
  color: var(--primary);
  margin: 0;
}

.page-top h1 {
  margin: 0;
  font-size: clamp(2rem, 2.5vw, 2.4rem);
}

.intro {
  margin: 10px 0 0;
  color: var(--muted);
  max-width: 650px;
}

.profile-summary {
  display: grid;
  gap: 18px;
}

.profile-card {
  display: flex;
  align-items: center;
  gap: 18px;
  padding: 24px;
  border-radius: 24px;
  background: var(--surface);
  border: 1px solid var(--border);
}

.company-avatar {
  width: 72px;
  height: 72px;
  display: grid;
  place-items: center;
  border-radius: 20px;
  background: var(--primary);
  color: white;
  font-size: 1.5rem;
  font-weight: 700;
}

.profile-card h2 {
  margin: 0;
}

.profile-card p {
  margin: 6px 0 0;
  color: var(--muted);
}

.details-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
}

.detail-item {
  padding: 18px;
  border-radius: 20px;
  background: var(--surface);
  border: 1px solid var(--border);
}

.detail-item span {
  display: block;
  color: var(--muted);
  margin-bottom: 8px;
}

@media (max-width: 980px) {
  .details-grid {
    grid-template-columns: 1fr;
  }
}
</style>
