import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    root: './backend',
    base: '/',
    
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            publicDirectory: '../public',
            buildDirectory: 'dist',
        }),
        tailwindcss(),
    ],
    
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    
    build: {
        outDir: '../public/dist',
        emptyOutDir: false,
        manifest: true,
        sourcemap: false,
        minify: 'terser',
    },
});
