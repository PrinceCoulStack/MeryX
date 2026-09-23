<template>
  <div v-if="visible" class="overlay">
    <div class="spinner"></div>
  </div>
</template>

<script setup>
defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
})
</script>

<style scoped>
.overlay {
  position: fixed;
  inset: 0;
  background: rgba(2, 15, 24, 0.55);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  backdrop-filter: blur(3px);
}

.spinner {
  width: 56px;
  height: 56px;
  border: 5px solid rgba(242, 244, 247, 0.25);
  border-top: 5px solid #d4a017;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  box-shadow: 0 0 0 5px rgba(17, 74, 106, 0.35);
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
