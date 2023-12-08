import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// https://vitejs.dev/config/
export default defineConfig({
  base: './',
  plugins: [react()],
  build: {
    outDir: '../binbooker.test/dist/sales-crm'
  },
  server: {
    proxy: {
      '/api': 'http://binbooker.test/sales-crm/',
    }
  }
})
