import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': resolve(__dirname, 'src'),
    },
  },
  build: {
    outDir: '../dist',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: resolve(__dirname, 'index.html'),
    },
    // Disable source maps for headless environments
    sourcemap: false,
    // Minify without terser to avoid Qt issues
    minify: 'esbuild',
  },
  server: {
    port: 3000,
    proxy: {
      '/wp-json': {
        target: 'http://localhost:8080',
        changeOrigin: true,
      },
    },
  },
  // Optimize deps for headless environment
  optimizeDeps: {
    esbuildOptions: {
      target: 'es2020',
    },
  },
})
