import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    prefix: '',
    darkMode: 'class',
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        './resources/js/components/**/*.vue', 
    ],
    theme: {
        extend: {
            // colors: {
            //     'primary': {
            //         800: '#e1e5eb',
            //         900: '#f4f1f0',
            //         950: '#FCF9F8',
            //     },
            //     'primary-dark': {
            //         800: '#1D2128',//#1D2128 //#13171d //#121216
            //         900: '#211E1D',
            //         950: '#121216',//original: #121216 rtc: #161312 figma: 
            //     }

            // },
            colors: { // Default
                'primary': {
                    800: '#f4f4f5',
                    900: '#fafafa',
                    950: '#FCF9F8',
                },
                'primary-dark': {
                    800: '#27272a',
                    900: '#18181b',
                    950: '#101016',
                },
                'secondary': {},
                'accent': {
                    500: '#f59e0b',
                    600: '#ea580c'
                },
                'text': {},
                'input': {
                    900: '#212529'
                }
            },
            aspectRatio: {
                'video': '16 / 9',
                'square': '1 / 1',
                'portrait': '9 / 16'
            },
        },
    },
    variants: {
        extend: {
            visibility: ["group-hover"],
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [
        // eslint-disable-next-line no-undef
        require('@tailwindcss/forms'),
        // eslint-disable-next-line no-undef
        require('@tailwindcss/aspect-ratio'),
    ]
}

