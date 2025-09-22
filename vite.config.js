import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import path from 'path'
import tailwind from '@tailwindcss/vite'   // <-- add

export default defineConfig({
  plugins: [
    laravel({ input: ['resources/css/app.css', 'resources/js/app.js'], refresh: true }),
    vue(),
    tailwind(), // <-- add
  ],
  resolve: { alias: { '@': path.resolve(__dirname, 'resources/js') } },
})
