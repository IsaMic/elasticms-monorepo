import { defineConfig } from 'vite'
import inject from '@rollup/plugin-inject'
import liveReload from 'vite-plugin-live-reload'

export default defineConfig({
  base: './',
  build: {
    manifest: true,
    outDir: '../public',
    sourcemap: false,
    emptyOutDir: true,
    copyPublicDir: true,
    rollupOptions: {
      input: {
        action: 'src/action.js',
        app: 'src/app.js',
        calendar: 'src/calendar.js',
        'criteria-table': 'src/criteria-table.js',
        'criteria-view': 'src/criteria-view.js',
        'edit-revision': 'src/edit-revision.js',
        hierarchical: 'src/hierarchical.js',
        i18n: 'src/i18n.js',
        'managed-alias': 'src/managed-alias.js'
      }
    }
  },
  css: {
    preprocessorOptions: {
      scss: {
        api: 'modern-compiler',
      }
    }
  },
  plugins: [
    liveReload('../templates/**/*.twig'),
    liveReload('../../core-bundle/templates/**/*.twig'),
    inject({
      jQuery: 'jquery',
      $: 'jquery',
      exclude: ['**/*.scss', '**/*.css']
    })
  ],
  resolve: {
    extensions: ['.js', '.ts']
  },
  server: {
    host: '0.0.0.0',
    origin: 'http://localhost:5173',
    port: 5173,
    strictPort: true,
    hmr: true,
    watch: {
      usePolling: true,
    }
  }
})
