import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import legacy from '@vitejs/plugin-legacy'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    react(),
    legacy({
      targets: ['defaults', 'not IE 11', 'iOS >= 9', 'Android >= 4.4']
    })
  ],
  base: './',
  build: {
    outDir: '../waiter-app-dist',
    emptyOutDir: true,
    target: 'es2015' // Fallback target
  }
})
