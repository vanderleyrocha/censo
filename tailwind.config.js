import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.jsx',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.jsx',
    ],
    
    safelist: [
        // Cores para mensagens e estados
        'bg-green-500',
        'bg-green-600',
        'bg-green-700',
        'bg-green-800',
        'bg-green-900',
        'bg-red-500',
        'bg-red-600',
        'bg-red-700',
        'bg-red-800',
        'bg-red-900',
        'bg-yellow-500',
        'bg-yellow-600',
        'bg-yellow-700',
        'bg-yellow-800',
        'bg-yellow-900',
        'bg-blue-500',
        'bg-blue-600',
        'bg-blue-700',
        'bg-blue-800',
        'bg-blue-900',
        'text-green-100',
        'text-red-100',
        'text-blue-100',
        
        // Animações
        'animate-fade-in',
        'animate-fade-out',
        
        // Classes dinâmicas do sidebar
        'w-64',
        'w-20',
        'ml-64',
        'ml-20'
    ],
    
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            
            // Extensões de animação
            animation: {
                'fade-in': 'fade-in 0.3s ease-out forwards',
                'fade-out': 'fade-out 0.3s ease-out forwards',
                'slide-in-right': 'slide-in-right 0.3s ease-out',
                'slide-out-right': 'slide-out-right 0.3s ease-out',
            },
            
            // Definição das keyframes
            keyframes: {
                'fade-in': {
                    'from': { 
                        opacity: '0', 
                        transform: 'translateY(-10px)' 
                    },
                    'to': { 
                        opacity: '1', 
                        transform: 'translateY(0)' 
                    },
                },
                'fade-out': {
                    'from': { 
                        opacity: '1', 
                        transform: 'translateY(0)' 
                    },
                    'to': { 
                        opacity: '0', 
                        transform: 'translateY(-10px)' 
                    },
                },
                'slide-in-right': {
                    'from': { 
                        transform: 'translateX(100%)' 
                    },
                    'to': { 
                        transform: 'translateX(0)' 
                    },
                },
                'slide-out-right': {
                    'from': { 
                        transform: 'translateX(0)' 
                    },
                    'to': { 
                        transform: 'translateX(100%)' 
                    },
                },
            },
            
            // Extensões de cores
            colors: {
                'primary': {
                    '50': '#f0fdf4',
                    '100': '#dcfce7',
                    '200': '#bbf7d0',
                    '300': '#86efac',
                    '400': '#4ade80',
                    '500': '#22c55e',
                    '600': '#16a34a',
                    '700': '#15803d',
                    '800': '#166534',
                    '900': '#14532d',
                },
                'danger': {
                    '50': '#fef2f2',
                    '100': '#fee2e2',
                    '200': '#fecaca',
                    '300': '#fca5a5',
                    '400': '#f87171',
                    '500': '#ef4444',
                    '600': '#dc2626',
                    '700': '#b91c1c',
                    '800': '#991b1b',
                    '900': '#7f1d1d',
                },
            },
        },
    },

    plugins: [
        forms,
        require('@tailwindcss/typography'),
        require('@tailwindcss/aspect-ratio'),
    ],
};