import { includeIgnoreFile } from '@eslint/compat';
import { fileURLToPath } from 'node:url';

import vueTsEslintConfig from '@vue/eslint-config-typescript';
import skipFormatting from '@vue/eslint-config-prettier/skip-formatting';
import pluginVue from 'eslint-plugin-vue';
import path from 'node:path';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const gitignorePath = path.resolve(__dirname, '.gitignore');

export default [
    includeIgnoreFile(gitignorePath),
    {
        name: 'app/files-to-lint',
        files: ['resources/*.{js,mjs,cjs,vue,ts}'],
        languageOptions: {
            parserOptions: {
                parser: {
                    // Script parser for `<script>`
                    js: '@typescript-eslint/parser',

                    // Script parser for `<script lang="ts">`
                    ts: '@typescript-eslint/parser',

                    // Script parser for vue directives (e.g. `v-if=` or `:attribute=`)
                    // and vue interpolations (e.g. `{{variable}}`).
                    // If not specified, the parser determined by `<script lang ="...">` is used.
                    '<template>': 'espree',
                },
                sourceType: 'module',
                project: ['./tsconfig.json', './tsconfig.node.json'],
                extraFileExtensions: ['.vue'],
            },
        },
        rules: {
            // Note: you must disable the base rule as it can report incorrect errors
            'no-unused-vars': '0',
            '@typescript-eslint/no-unused-vars': '0',
            '@typescript-eslint/no-explicit-any': '0',
        },
    },

    // {
    //     name: 'app/files-to-ignore',
    //     ignores: ['**/dist/**', '**/dist-ssr/**', '**/coverage/**'],
    // },

    ...pluginVue.configs['flat/essential'],
    ...vueTsEslintConfig(),
    skipFormatting,
];
