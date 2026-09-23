<template>
  <section class="auth-shell">
    <div class="auth-shell__backdrop"></div>

    <div class="auth-shell__inner">
      <aside class="auth-shell__story">
        <p class="auth-shell__eyebrow">{{ eyebrow }}</p>
        <h1>{{ title }}</h1>
        <p class="auth-shell__description">{{ description }}</p>

        <div class="auth-shell__highlights">
          <article v-for="item in highlights" :key="item.title" class="auth-shell__highlight">
            <span>{{ item.value }}</span>
            <div>
              <strong>{{ item.title }}</strong>
              <p>{{ item.text }}</p>
            </div>
          </article>
        </div>
      </aside>

      <div class="auth-shell__panel">
        <div class="auth-shell__panel-head">
          <p class="auth-shell__panel-eyebrow">{{ panelEyebrow }}</p>
          <h2>{{ panelTitle }}</h2>
          <p>{{ panelDescription }}</p>
        </div>

        <slot />

        <div v-if="$slots.footer" class="auth-shell__footer">
          <slot name="footer" />
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
defineProps({
  eyebrow: {
    type: String,
    default: 'MERYX ACCESS',
  },
  title: {
    type: String,
    required: true,
  },
  description: {
    type: String,
    required: true,
  },
  panelEyebrow: {
    type: String,
    default: 'BIENVENUE',
  },
  panelTitle: {
    type: String,
    required: true,
  },
  panelDescription: {
    type: String,
    required: true,
  },
  highlights: {
    type: Array,
    default: () => [],
  },
})
</script>

<style scoped>
.auth-shell {
  position: relative;
  min-height: calc(100vh - 74px);
  padding: 32px 0 56px;
  overflow: hidden;
  background:
    radial-gradient(circle at 0% 0%, rgba(17, 74, 106, 0.14), transparent 28%),
    radial-gradient(circle at 100% 10%, rgba(30, 63, 102, 0.14), transparent 32%),
    linear-gradient(180deg, #f8fafc 0%, #eef3f8 100%);
}

.auth-shell__backdrop {
  position: absolute;
  inset: 0;
  background:
    linear-gradient(135deg, rgba(255, 255, 255, 0.55), transparent 45%),
    radial-gradient(circle at 20% 80%, rgba(212, 160, 23, 0.12), transparent 24%);
  pointer-events: none;
}

.auth-shell__inner {
  position: relative;
  z-index: 1;
  width: min(1220px, calc(100% - 32px));
  margin: 0 auto;
  display: grid;
  grid-template-columns: minmax(0, 1.05fr) minmax(420px, 0.95fr);
  gap: 22px;
  align-items: stretch;
}

.auth-shell__story,
.auth-shell__panel {
  min-width: 0;
  border-radius: 28px;
  border: 1px solid rgba(13, 43, 69, 0.1);
  box-shadow: 0 18px 40px rgba(13, 43, 69, 0.08);
}

.auth-shell__story {
  padding: 34px;
  background: linear-gradient(145deg, rgba(17, 74, 106, 0.96), rgba(30, 63, 102, 0.94)), #114a6a;
  color: #f8fafc;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.auth-shell__eyebrow,
.auth-shell__panel-eyebrow {
  margin: 0;
  font-size: 0.76rem;
  font-weight: 800;
  letter-spacing: 0.08em;
}

.auth-shell__story h1 {
  margin: 14px 0 14px;
  font-size: clamp(2.2rem, 4vw, 3.5rem);
  line-height: 1.02;
}

.auth-shell__description {
  margin: 0;
  max-width: 42ch;
  color: rgba(248, 250, 252, 0.82);
  font-size: 1rem;
}

.auth-shell__highlights {
  display: grid;
  gap: 12px;
  margin-top: 28px;
}

.auth-shell__highlight {
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 12px;
  align-items: start;
  padding: 14px;
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.12);
}

.auth-shell__highlight span {
  width: 48px;
  height: 48px;
  border-radius: 16px;
  display: grid;
  place-items: center;
  background: rgba(255, 255, 255, 0.14);
  color: #f5c75d;
  font-weight: 800;
}

.auth-shell__highlight strong {
  display: block;
  margin: 0;
}

.auth-shell__highlight p {
  margin: 4px 0 0;
  color: rgba(248, 250, 252, 0.78);
}

.auth-shell__panel {
  padding: 30px;
  background: rgba(255, 255, 255, 0.94);
  backdrop-filter: blur(12px);
}

.auth-shell__panel-head h2 {
  margin: 10px 0 8px;
  color: #0d2b45;
  font-size: clamp(1.7rem, 2vw, 2.2rem);
}

.auth-shell__panel-head p {
  margin: 0;
  color: rgba(13, 43, 69, 0.72);
}

.auth-shell__footer {
  margin-top: 18px;
}

@media (max-width: 1040px) {
  .auth-shell__inner {
    grid-template-columns: 1fr;
  }

  .auth-shell__story {
    padding: 28px;
  }
}

@media (max-width: 640px) {
  .auth-shell {
    padding: 20px 0 36px;
  }

  .auth-shell__inner {
    width: min(100% - 20px, 1220px);
  }

  .auth-shell__story,
  .auth-shell__panel {
    border-radius: 22px;
    padding: 22px;
  }
}
</style>
