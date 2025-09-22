import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import path from 'path'
import tailwind from '@tailwindcss/vite'

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.js'],
      refresh: true,
    }),
    vue(),
    tailwind(),
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources/js'),
    },
  },
  server: {
    host: true, // allows external access (important on Replit, Docker, etc.)
    allowedHosts: [
      '98e21e26-6951-414b-b58a-8c97d04a3c56-00-3uxigluvr3rt3.worf.replit.dev'
    ]
  },
})
