import pluginJs from "@eslint/js";
import pluginVue from "eslint-plugin-vue";

export default [
    { 
        files: ["resources/*.{js,mjs,cjs,vue}"],
    },
    pluginJs.configs.recommended,
    ...pluginVue.configs["flat/essential"],
];
