import { defineConfig } from 'vite'
import vue2 from '@vitejs/plugin-vue2'
import laravel from 'laravel-vite-plugin'

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/simple-likes-fieldtype.js'
            ],
            publicDirectory: 'resources/dist',
            buildDirectory: 'build'
        }),
        vue2()
    ],
    build: {
        manifest: true,
        outDir: 'resources/dist/build',
        rollupOptions: {
            external: ['vue', 'Statamic'],
            output: {
                entryFileNames: '[name].js',
                chunkFileNames: '[name].js',
                assetFileNames: '[name][extname]',
                globals: {
                    vue: 'Vue',
                    Statamic: 'Statamic'
                }
            }
        }
    }
})
