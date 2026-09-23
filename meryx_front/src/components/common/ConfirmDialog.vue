<template>
  <div v-if="visible" class="overlay">
    <div class="dialog">
      <h3>{{ title }}</h3>
      <p>{{ message }}</p>

      <div class="actions">
        <button @click="$emit('cancel')">Cancel</button>
        <button class="danger" @click="$emit('confirm')">Confirm</button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  visible: Boolean,
  title: {
    type: String,
    default: 'Confirm Action',
  },
  message: {
    type: String,
    default: 'Are you sure?',
  },
})
</script>

<style scoped>
.overlay {
  position: fixed;
  inset: 0;
  background: rgba(2, 15, 24, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1400;
  backdrop-filter: blur(4px);
}

.dialog {
  background: #114a6a;
  padding: 22px;
  width: min(92vw, 360px);
  border-radius: 16px;
  border: 1px solid rgba(242, 244, 247, 0.18);
  box-shadow: 0 20px 42px rgba(2, 16, 28, 0.42);
}

.dialog h3 {
  color: #f2f4f7;
  margin-bottom: 8px;
}

.dialog p {
  color: rgba(242, 244, 247, 0.84);
}

.actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 15px;
}

button {
  padding: 8px 14px;
  border: 1px solid rgba(242, 244, 247, 0.2);
  cursor: pointer;
  border-radius: 10px;
  background: #1e3f66;
  color: #f2f4f7;
  font-weight: 600;
}

.danger {
  background: #d4a017;
  color: #0d2b45;
  border-color: rgba(212, 160, 23, 0.88);
}
</style>
