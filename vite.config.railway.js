import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default defineConfig({
  plugins: [vue()],
  
  // Configuración de rutas (asume estructura monorepo)
  root: process.env.FRONTEND_PATH || '.',
  
  server: {
    middlewareMode: false,
    hmr: process.env.RAILWAY_ENVIRONMENT_NAME === 'production' 
      ? false 
      : {
          host: 'localhost',
          port: 5173
        },
    port: 5173
  },

  build: {
    // Output para Laravel public
    outDir: './public/dist',
    emptyOutDir: false,
    manifest: true,
    sourcemap: false,
    minify: 'terser',
    
    rollupOptions: {
      output: {
        manualChunks: {
          vue: ['vue', 'vue-router'],
          http: ['axios']
        }
      }
    },

    chunkSizeWarningLimit: 1000,
    reportCompressedSize: false
  },

  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    }
  },

  define: {
    __API_URL__: JSON.stringify(process.env.VITE_API_URL || 'http://localhost:8000')
  },

  optimizeDeps: {
    include: ['vue', 'vue-router', 'axios']
  }
})
