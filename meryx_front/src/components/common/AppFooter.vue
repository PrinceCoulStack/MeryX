<template>
  <footer class="app-footer">
    <p>© {{ year }} MeryX - University Platform</p>
  </footer>
</template>

<script setup>
const year = new Date().getFullYear()
</script>

<style scoped>
.app-footer {
  text-align: center;
  padding: 12px;
  background: rgba(13, 43, 69, 0.86);
  font-size: 14px;
  color: #f2f4f7;
  border-top: 1px solid rgba(242, 244, 247, 0.14);
}
</style>
