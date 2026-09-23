<template>
  <div class="settings-page">
    <header class="page-header">
      <div>
        <p class="eyebrow">{{ t.eyebrow }}</p>
        <h1>{{ t.title }}</h1>
        <p class="intro">{{ t.subtitle }}</p>
      </div>
    </header>

    <section class="settings-grid">
      <article class="settings-card">
        <div class="card-head">
          <h2>{{ t.languageTitle }}</h2>
          <span>{{ locale.toUpperCase() }}</span>
        </div>
        <p>{{ t.languageText }}</p>

        <div class="option-list" role="radiogroup" :aria-label="t.languageTitle">
          <button
            type="button"
            class="option-btn"
            :class="{ active: locale === 'en' }"
            @click="setLocale('en')"
          >
            <strong>English</strong>
            <small>{{ t.englishNote }}</small>
          </button>

          <button
            type="button"
            class="option-btn"
            :class="{ active: locale === 'fr' }"
            @click="setLocale('fr')"
          >
            <strong>Francais</strong>
            <small>{{ t.frenchNote }}</small>
          </button>
        </div>
      </article>

      <article class="settings-card">
        <div class="card-head">
          <h2>{{ t.themeTitle }}</h2>
          <span>{{ theme === 'dark' ? t.darkLabel : t.lightLabel }}</span>
        </div>
        <p>{{ t.themeText }}</p>

        <div class="option-list" role="radiogroup" :aria-label="t.themeTitle">
          <button
            type="button"
            class="option-btn"
            :class="{ active: theme === 'light' }"
            @click="setTheme('light')"
          >
            <strong>{{ t.lightLabel }}</strong>
            <small>{{ t.lightNote }}</small>
          </button>

          <button
            type="button"
            class="option-btn"
            :class="{ active: theme === 'dark' }"
            @click="setTheme('dark')"
          >
            <strong>{{ t.darkLabel }}</strong>
            <small>{{ t.darkNote }}</small>
          </button>
        </div>
      </article>
    </section>

    <section class="preview-card">
      <div>
        <h2>{{ t.previewTitle }}</h2>
        <p>{{ t.previewText }}</p>
      </div>
      <div class="preview-badges">
        <span>{{ t.activeLanguage }}: {{ locale.toUpperCase() }}</span>
        <span>{{ t.activeTheme }}: {{ theme === 'dark' ? t.darkLabel : t.lightLabel }}</span>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useUiPreferences } from '@/compasables/useUiPreferences'

const { locale, theme, setLocale, setTheme } = useUiPreferences()

const copy = {
  en: {
    eyebrow: 'Company / Settings',
    title: 'Company Interface Settings',
    subtitle:
      'Manage language and appearance preferences for the company workspace. Changes are applied instantly and persisted across all pages.',
    languageTitle: 'Language Mode',
    languageText:
      'Switch between English and French for all pages that use shared UI translation labels.',
    englishNote: 'Default interface language',
    frenchNote: 'French interface labels',
    themeTitle: 'Appearance Mode',
    themeText: 'Choose light or dark mode for the complete dashboard using global theme variables.',
    lightLabel: 'Light',
    darkLabel: 'Dark',
    lightNote: 'Bright surfaces and clear contrast',
    darkNote: 'Low-light interface for visual comfort',
    previewTitle: 'Live Preferences Preview',
    previewText:
      'Settings are saved automatically, so your language and theme remain active after refresh or navigation.',
    activeLanguage: 'Language',
    activeTheme: 'Theme',
  },
  fr: {
    eyebrow: 'Entreprise / Parametres',
    title: "Parametres d'interface entreprise",
    subtitle:
      "Gerez la langue et l'apparence pour l'espace entreprise. Les changements sont appliques instantanement et conserves sur toutes les pages.",
    languageTitle: 'Mode de langue',
    languageText:
      'Basculez entre anglais et francais pour toutes les pages qui utilisent les libelles partages.',
    englishNote: "Langue d'interface par defaut",
    frenchNote: "Libelles d'interface en francais",
    themeTitle: "Mode d'apparence",
    themeText:
      'Choisissez le mode clair ou sombre pour tout le tableau de bord via les variables globales.',
    lightLabel: 'Clair',
    darkLabel: 'Sombre',
    lightNote: 'Surfaces lumineuses et contraste net',
    darkNote: 'Interface douce pour faible luminosite',
    previewTitle: 'Apercu en direct',
    previewText:
      'Les parametres sont enregistres automatiquement et restent actifs apres actualisation ou navigation.',
    activeLanguage: 'Langue',
    activeTheme: 'Theme',
  },
}

const t = computed(() => copy[locale.value] || copy.en)
</script>

<style scoped>
.settings-page {
  display: grid;
  gap: 18px;
}

.page-header h1,
.card-head h2,
.preview-card h2 {
  margin: 0;
  color: var(--text);
}

.eyebrow {
  margin: 0 0 6px;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  color: var(--primary);
  font-size: 0.78rem;
  font-weight: 700;
}

.intro {
  margin: 10px 0 0;
  max-width: 780px;
  color: var(--muted);
}

.settings-grid {
  display: grid;
  gap: 14px;
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.settings-card,
.preview-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 20px;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
}

.settings-card {
  padding: 18px;
  display: grid;
  gap: 12px;
}

.card-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}

.card-head span,
.settings-card p,
.intro,
.option-btn small,
.preview-card p {
  color: var(--muted);
}

.option-list {
  display: grid;
  grid-template-columns: 1fr;
  gap: 10px;
}

.option-btn {
  width: 100%;
  border: 1px solid var(--border);
  background: var(--surface-soft);
  border-radius: 14px;
  padding: 12px 14px;
  text-align: left;
  cursor: pointer;
  display: grid;
  gap: 5px;
}

.option-btn strong,
.preview-badges span {
  color: var(--text);
}

.option-btn.active {
  border-color: rgba(6, 170, 197, 0.48);
  background: rgba(6, 170, 197, 0.1);
}

.preview-card {
  padding: 18px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 14px;
}

.preview-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.preview-badges span {
  border: 1px solid var(--border);
  border-radius: 999px;
  padding: 8px 12px;
  background: var(--surface-soft);
  font-size: 0.88rem;
}

@media (max-width: 940px) {
  .settings-grid {
    grid-template-columns: 1fr;
  }

  .preview-card {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
