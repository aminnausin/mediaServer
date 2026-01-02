import plugin from 'tailwindcss/plugin';

const customVariants = plugin(({ addVariant }) => {
    addVariant('hocus', ['&:hover', '&:focus']);
    addVariant('default', 'html :where(&)');
    addVariant('scrollbar', '&::-webkit-scrollbar');
    addVariant('scrollbar-track', '&::-webkit-scrollbar-track');
    addVariant('scrollbar-thumb', '&::-webkit-scrollbar-thumb');
});

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './resources/js/components/**/*.vue',
    ],
    // theme: {

    //     aspectRatio: {
    //         video: '16 / 9',
    //         square: '1 / 1',
    //         portrait: '9 / 16',
    //         '1/2': '1 / 2',
    //         '2/3': '2 / 3',
    //         '3/4': '3 / 4',
    //     },
    //     screens: {
    //         xs: '320px',
    //         xms: '360px',
    //         '3xl': '2000px',
    //     },
    // },

    plugins: [customVariants],
};
