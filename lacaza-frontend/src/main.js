// Punto de entrada de la aplicación Vue.
// Aquí creamos la app, le añadimos el router y la montamos en el HTML.

import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import './assets/estilos.css'

const app = createApp(App)
app.use(router) // activamos el sistema de rutas
app.mount('#app') // montamos en el div con id="app" del index.html
