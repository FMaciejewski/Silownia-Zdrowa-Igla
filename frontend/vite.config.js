import { defineConfig } from 'vite'
import path from 'path'

export default defineConfig({
  root: path.resolve(__dirname, './'),
  server: {
    open: true,
    port: 5173,
    host: '0.0.0.0'
  },
  build: {
    outDir: '../dist',
    emptyOutDir: true
  }
}) 