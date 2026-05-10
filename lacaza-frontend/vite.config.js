import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// Configuración de Vite (la herramienta que compila y sirve nuestra app Vue)
export default defineConfig({
  plugins: [vue()],
  server: {
    port: 5173,
  },
})
