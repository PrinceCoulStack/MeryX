import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import { useUiPreferences } from './compasables/useUiPreferences'

import 'bootstrap-icons/font/bootstrap-icons.css'

const app = createApp(App)
const { initializeUiPreferences } = useUiPreferences()

initializeUiPreferences()

app.use(createPinia())
app.use(router)

app.mount('#app')
