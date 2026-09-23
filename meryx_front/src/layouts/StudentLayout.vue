<template>
  <div class="student-layout" :class="theme">
    <TopNavbar />

    <main class="content">
      <RouterView />
    </main>
  </div>
</template>

<script setup>
import { provide, onMounted } from 'vue'
import { RouterView } from 'vue-router'
import TopNavbar from '@/components/student/TopNavbar.vue'
import { useUiPreferences } from '@/compasables/useUiPreferences'

const { theme, locale, initializeUiPreferences } = useUiPreferences()

onMounted(() => {
  initializeUiPreferences()
})

provide('locale', locale)
</script>

<style scoped>
.student-layout {
  min-height: 100vh;
  width: 100%;
  display: flex;
  flex-direction: column;
  background: var(--bg);
}

.content {
  flex: 1;
  width: 100%;
  padding-top: 72px;
  min-height: calc(100vh - 72px);
}

@media (max-width: 900px) {
  .content {
    padding-top: 130px;
    min-height: calc(100vh - 130px);
  }
}
</style>
