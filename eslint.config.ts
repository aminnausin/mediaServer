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
        rules: {
            '@typescript-eslint/no-unused-vars': 'warn',
            '@typescript-eslint/no-explicit-any': 'off',
            'vue/block-lang': [
                'error',
                {
                    script: { allowNoLang: true },
                },
            ],
        },
    },

    globalIgnores(['**/node_modules/**', '**/dist/**', '**/dist-ssr/**', '**/coverage/**', '**/.output/**']),

    pluginVue.configs['flat/essential'],
    vueTsConfigs.recommended,
    skipFormatting,
);

//   {
//     ...pluginVitest.configs.recommended,
//     files: ['src/**/__tests__/*'],
//   },

//   {
//     ...pluginPlaywright.configs['flat/recommended'],
//     files: ['e2e/**/*.{test,spec}.{js,ts,jsx,tsx}'],
//   },
