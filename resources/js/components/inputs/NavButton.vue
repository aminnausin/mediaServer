<script setup lang="ts">
import { ref } from 'vue';

const props = defineProps(['label', 'colour', 'active']);
const activeColour = ref(`ring-3 ring-inset ring-[0.1rem] dark:ring-0 ring-violet-500 dark:bg-violet-700 bg-white`);
</script>
<template>
    <button
        :id="'btn-nav-' + props.label"
        :class="`${props.active ? activeColour : `dark:bg-primary-dark-800 bg-white`} flex h-8 w-8 shrink-0 cursor-pointer items-center justify-center rounded-lg shadow-lg hover:bg-neutral-100 dark:text-neutral-100 dark:hover:bg-violet-700`"
        :aria-label="props.label"
    >
        <slot name="icon"></slot>
        <span :class="`hidden ${activeColour}`"></span>
    </button>
</template>
