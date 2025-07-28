import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.jsx',
                'resources/css/app.css'
            ],
            refresh: true,
        }),
        react({
            jsxRuntime: 'automatic', // Usa o novo JSX runtime automático
            babel: {
                plugins: [], // Remova a configuração do plugin que estava causando o erro
            },
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
            '@Components': path.resolve(__dirname, './resources/js/Components'),
            '@Pages': path.resolve(__dirname, './resources/js/Pages'),
            '@Helpers': path.resolve(__dirname, './resources/js/Helpers'),
            '@Layouts': path.resolve(__dirname, './resources/js/Layouts'),
        },
    },
    build: {
        chunkSizeWarningLimit: 1600,
        rollupOptions: {
            output: {
                manualChunks: {
                    react: ['react', 'react-dom'],
                    vendor: ['lodash', 'axios'],
                },
            },
        },
    },
    server: {
        watch: {
            usePolling: true,
        },
        hmr: {
            host: 'localhost',
        },
    },
    optimizeDeps: {
        include: [
            'react',
            'react-dom',
            '@inertiajs/react',
            'axios',
        ],
    },
});