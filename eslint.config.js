import { includeIgnoreFile } from '@eslint/compat';
import { fileURLToPath } from 'node:url';

import vueTsEslintConfig from '@vue/eslint-config-typescript';
import skipFormatting from '@vue/eslint-config-prettier/skip-formatting';
import pluginVue from 'eslint-plugin-vue';
import path from 'node:path';
import globals from 'globals';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const gitignorePath = path.resolve(__dirname, '.gitignore');

export default [
    includeIgnoreFile(gitignorePath),
    {
        ...pluginVue.configs['flat/essential'],
        ...vueTsEslintConfig(),
        files: ['resources/*.{js,mjs,cjs,vue,ts}'],
        skipFormatting,
        languageOptions: {
            ecmaVersion: 'latest',
            sourceType: 'module',
            globals: globals.browser,
            parserOptions: {
                parser: {
                    // Script parser for `<script>`
                    js: '@typescript-eslint/parser',

                    // Script parser for `<script lang="ts">`
                    ts: '@typescript-eslint/parser',
                },
                sourceType: 'module',
                extraFileExtensions: ['.vue'],
            },
        },
        rules: {
            // Note: you must disable the base rule as it can report incorrect errors
            'no-unused-vars': '0',
            '@typescript-eslint/no-unused-vars': '0',
            '@typescript-eslint/no-explicit-any': '0',
            'vue/block-lang': [
                'error',
                {
                    script: { allowNoLang: true },
                },
            ],
        },
        overrides: [
            {
                files: ['*.md'],
                extends: [],
                rules: {
                    indent: 2,
                },
            },
        ],
    },
];
