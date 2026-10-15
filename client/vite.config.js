import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
  plugins: [vue()],
  server: {
    port: 3000,
    proxy: {
      '/api': {
        target: 'https://vendor.sinargrafika.my.id',
        changeOrigin: true,
        secure: true
      },
      '/storage': {
        target: 'https://vendor.sinargrafika.my.id',
        changeOrigin: true,
        secure: true
      },
      '/uploads': {
        target: 'https://vendor.sinargrafika.my.id',
        changeOrigin: true,
        secure: true
      }
    }
  }
});
