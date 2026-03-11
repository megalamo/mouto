import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
    build: {
        outDir: resolve(__dirname, 'public'),
        emptyOutDir: true,
        rollupOptions: {
            input: {
                app: resolve(__dirname, './public/js/application.js'),
                style: resolve(__dirname, './public/css/application.css')
            },
            output: {
                entryFileNames: '/public/js/application.js',
                assetFileNames: '/public/css/application.css'
            }
        }
    }
});