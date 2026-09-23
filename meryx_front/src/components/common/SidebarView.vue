<template>
  <aside class="sidebar">
    <h3 class="title">{{ title }}</h3>

    <nav>
      <ul>
        <li v-for="item in items" :key="item.name">
          <RouterLink :to="item.path">
            {{ item.label }}
          </RouterLink>
        </li>
      </ul>
    </nav>
  </aside>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: 'Menu',
  },
  items: {
    type: Array,
    default: () => [],
  },
})
</script>

<style scoped>
.sidebar {
  width: 240px;
  height: 100vh;
  background: linear-gradient(180deg, #0d2b45 0%, #114a6a 70%, #1e3f66 100%);
  color: white;
  padding: 20px;
  border-right: 1px solid rgba(242, 244, 247, 0.16);
}

.title {
  margin-bottom: 15px;
}

ul {
  list-style: none;
  padding: 0;
}

li {
  margin: 10px 0;
}

a {
  color: #f2f4f7;
  text-decoration: none;
  display: block;
  padding: 9px 11px;
  border-radius: 10px;
}

a:hover {
  color: #d4a017;
  background: rgba(30, 63, 102, 0.94);
}
</style>
