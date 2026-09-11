import { fileURLToPath, URL } from 'node:url';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import legacy from '@vitejs/plugin-legacy';
import { viteStaticCopy } from 'vite-plugin-static-copy';
import { imagetools } from 'vite-imagetools';
import manifestSRI from 'vite-plugin-manifest-sri';

const resolvePath = (path) => fileURLToPath(new URL(path, import.meta.url));

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.js'],
      refresh: true,
    }),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
    tailwindcss(),
    viteStaticCopy({
      // stripBase keeps these flat in public/. Without it vite-plugin-static-copy
      // v4 preserves the source tree and writes them to public/resources/.
      targets: [
        {
          src: 'resources/img/favicon/*',
          dest: '../',
          rename: { stripBase: true },
        },
        {
          src: 'resources/pwa/*',
          dest: '../',
          rename: { stripBase: true },
        },
      ],
    }),
    imagetools({
      cache: {
        enabled: true,
        dir: './node_modules/.cache/imagetools',
        retention: 172800,
      },
    }),
    legacy({
      targets: ['defaults', 'not IE 11'],
      polyfills: true,
    }),
    manifestSRI(),
  ],
  build: {
    reportCompressedSize: true,
    chunkSizeWarningLimit: 1600,
    rollupOptions: {
      output: {
        manualChunks(id) {
          if (id.includes('node_modules')) {
            const modulePath = id.split('node_modules/')[1];
            const topLevelFolder = modulePath.split('/')[0];
            if (topLevelFolder !== '.pnpm') {
              return topLevelFolder;
            }
            const scopedPackageName = modulePath.split('/')[1];
            const chunkName = scopedPackageName.split('@')[scopedPackageName.startsWith('@') ? 1 : 0];
            return chunkName;
          }
        },
      },
    },
    modulePreload: {
      polyfill: true,
    },
  },
  css: {
    // Vite 8 processes CSS with Lightning CSS, which is stricter than the old
    // esbuild pipeline. vue3-easy-data-table ships var() references that are
    // missing the leading "--", so recover from those rather than hard failing.
    lightningcss: {
      errorRecovery: true,
    },
  },
  resolve: {
    alias: {
      '~': resolvePath('./node_modules'),
      '@': resolvePath('./resources/js'),
      '@css': resolvePath('./resources/css'),
      '@img': resolvePath('./resources/img'),
      '@views': resolvePath('./resources/js/views'),
      '@pages': resolvePath('./resources/js/views/pages'),
      '@layouts': resolvePath('./resources/js/layouts'),
      '@kiosk': resolvePath('./resources/js/views/kiosk'),
      '@home': resolvePath('./resources/js/views/home'),
      '@admin': resolvePath('./resources/js/views/admin'),
      '@auth': resolvePath('./resources/js/views/auth'),
      '@errors': resolvePath('./resources/js/views/errors'),
      '@login': resolvePath('./resources/js/views/login'),
      '@misc': resolvePath('./resources/js/views/misc'),
      '@posts': resolvePath('./resources/js/views/posts'),
      '@category': resolvePath('./resources/js/views/category'),
      '@register': resolvePath('./resources/js/views/register'),
      '@store': resolvePath('./resources/js/store'),
      '@services': resolvePath('./resources/js/services'),
      '@router': resolvePath('./resources/js/router'),
      '@routes': resolvePath('./resources/js/routes'),
      '@components': resolvePath('./resources/js/components'),
      '@composables': resolvePath('./resources/js/composables'),
      vue: resolvePath('./node_modules/vue/dist/vue.esm-bundler.js'),
    },
  },
});
