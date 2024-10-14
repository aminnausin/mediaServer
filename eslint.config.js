import pluginJs from '@eslint/js';
import pluginVue from 'eslint-plugin-vue';

import { includeIgnoreFile } from '@eslint/compat';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const gitignorePath = path.resolve(__dirname, '.gitignore');

export default [
    includeIgnoreFile(gitignorePath),
    {
        files: ['resources/*.{js,mjs,cjs,vue,ts}'],
    },
    pluginJs.configs.recommended,
    ...pluginVue.configs['flat/essential'],
];
