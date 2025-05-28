import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath, URL } from 'url';

// ❗ Only include vueDevTools if you have it installed or set up
// import vueDevTools from 'vite-plugin-vue-devtools';

export default defineConfig({
  plugins: [
    vue(),
    // vueDevTools(), // Uncomment this only if you're using and have installed it
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost:80',
        changeOrigin: true,
        rewrite: (path) => path.replace(/^\/api/, '/explore-riga'),
      }
    }
  }
});
