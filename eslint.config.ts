import { defineConfigWithVueTs, vueTsConfigs } from '@vue/eslint-config-typescript';
import { globalIgnores } from 'eslint/config';

import skipFormatting from '@vue/eslint-config-prettier/skip-formatting';
import pluginVue from 'eslint-plugin-vue';
import globals from 'globals';

export default defineConfigWithVueTs(
    {
        name: 'app/files-to-lint',
        files: ['**/*.{js,mjs,cjs,vue,ts,mts,tsx}'],
        languageOptions: {
            ecmaVersion: 'latest',
            sourceType: 'module',
            globals: globals.browser,
            parserOptions: {
                parser: {
                    js: '@typescript-eslint/parser',
                    ts: '@typescript-eslint/parser',
                },
                sourceType: 'module',
                extraFileExtensions: ['.vue'],
            },
        },
    },

    globalIgnores(['**/node_modules/**', 'vendor', '**/dist/**', '**/dist-ssr/**', '**/coverage/**', '**/.output/**', 'public/**', '.vite/**']),

    pluginVue.configs['flat/essential'],
    vueTsConfigs.recommended,
    skipFormatting,

    {
        name: 'overrides',
        rules: {
            'no-unused-vars': 'off',
            '@typescript-eslint/no-unused-vars': 'off',
            '@typescript-eslint/no-explicit-any': 'off',
            'vue/block-lang': [
                'error',
                {
                    script: { lang: 'ts', allowNoLang: true },
                },
            ],
        },
    },
);

//   {
//     ...pluginVitest.configs.recommended,
//     files: ['src/**/__tests__/*'],
//   },

//   {
//     ...pluginPlaywright.configs['flat/recommended'],
//     files: ['e2e/**/*.{test,spec}.{js,ts,jsx,tsx}'],
//   },
