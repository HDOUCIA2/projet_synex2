import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import html from '@rollup/plugin-html';
import { glob } from 'glob';
import path from 'path';
import iconsPlugin from './vite.icons.plugin.js';

/**
 * Get Files from a directory
 * @param {string} query
 * @returns array
 */
function GetFilesArray(query) {
  return glob.sync(query);
}

// Page JS Files
const pageJsFiles = GetFilesArray('resources/assets/js/*.js');

// Processing Vendor JS Files
const vendorJsFiles = GetFilesArray('resources/assets/vendor/js/*.js');

// Processing Libs JS Files
const LibsJsFiles = GetFilesArray('resources/assets/vendor/libs/**/*.js');

// Processing Libs Scss & Css Files
const LibsScssFiles = GetFilesArray('resources/assets/vendor/libs/**/!(_)*.scss');
const LibsCssFiles = GetFilesArray('resources/assets/vendor/libs/**/*.css');

// Processing Core, Themes & Pages Scss Files
const CoreScssFiles = GetFilesArray('resources/assets/vendor/scss/**/!(_)*.scss');

// Processing Fonts Scss & JS Files
const FontsScssFiles = GetFilesArray('resources/assets/vendor/fonts/!(_)*.scss');
const FontsJsFiles = GetFilesArray('resources/assets/vendor/fonts/**/!(_)*.js');
const FontsCssFiles = GetFilesArray('resources/assets/vendor/fonts/**/!(_)*.css');

const vitePort = 5173;
const codespacesName = process.env.CODESPACE_NAME;
const codespacesDomain = process.env.GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN;
const codespacesViteHost =
  codespacesName && codespacesDomain
    ? `${codespacesName}-${vitePort}.${codespacesDomain}`
    : null;
const codespacesAppOrigin =
  codespacesName && codespacesDomain
    ? `https://${codespacesName}-8000.${codespacesDomain}`
    : null;

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '');
  const appUrlHost = new URL(env.APP_URL || 'http://localhost').hostname;
  const useCodespacesTunnel = Boolean(
    codespacesViteHost && !['localhost', '127.0.0.1'].includes(appUrlHost)
  );

  return {
    plugins: [
      laravel({
        input: [
          'resources/css/app.css',
          'resources/assets/css/demo.css',
          'resources/assets/css/veille.css',
          'resources/assets/css/home-page-2.css',
          'resources/assets/css/dashboard-synex-two.css',
          'resources/assets/css/program-news.css',
          'resources/assets/css/public-site.css',
          'resources/assets/css/prise-en-charge.css',
          'resources/assets/css/prevention.css',
          'resources/assets/css/contact.css',
          'resources/assets/css/admin-dashboard.css',
          'resources/js/app.js',
          ...pageJsFiles,
          ...vendorJsFiles,
          ...LibsJsFiles,
          ...CoreScssFiles,
          ...LibsScssFiles,
          ...LibsCssFiles,
          ...FontsScssFiles,
          ...FontsJsFiles,
          ...FontsCssFiles
        ],
        refresh: true
      }),
      html(),
      iconsPlugin()
    ],
    resolve: {
      alias: {
        '@': path.resolve(__dirname, 'resources')
      }
    },
    server: {
      host: '0.0.0.0',
      port: vitePort,
      strictPort: true,
      origin: codespacesViteHost
        ? useCodespacesTunnel
          ? `https://${codespacesViteHost}`
          : `http://localhost:${vitePort}`
        : undefined,
      cors: codespacesViteHost
        ? {
            origin: [codespacesAppOrigin, 'http://127.0.0.1:8000', 'http://localhost:8000']
          }
        : undefined,
      hmr: useCodespacesTunnel
        ? {
            protocol: 'wss',
            host: codespacesViteHost,
            clientPort: 443
          }
        : {
            host: 'localhost'
          }
    },
    json: {
      stringify: true // Helps with JSON import compatibility
    },
    build: {
      commonjsOptions: {
        include: [/node_modules/] // Helps with importing CommonJS modules
      }
    }
  };
});
