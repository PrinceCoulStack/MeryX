<template>
  <header class="app-header">
    <div class="left">
      <h2 class="logo">MeryX</h2>
    </div>

    <nav class="center">
      <slot name="nav" />
    </nav>

    <div class="right">
      <slot name="actions">
        <button class="btn">Logout</button>
      </slot>
    </div>
  </header>
</template>

<script setup></script>

<style scoped>
.app-header {
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
  background: linear-gradient(120deg, #0d2b45 0%, #114a6a 100%);
  color: #f2f4f7;
  border-bottom: 1px solid rgba(242, 244, 247, 0.16);
}

.logo {
  font-weight: bold;
}

.btn {
  background: #d4a017;
  border: 1px solid rgba(212, 160, 23, 0.8);
  padding: 6px 12px;
  color: #0d2b45;
  cursor: pointer;
  border-radius: 10px;
  font-weight: 700;
}
</style>
