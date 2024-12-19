<script setup>
import { RouterLink } from 'vue-router';
const props = defineProps(['label', 'colour', 'URL', 'active']);
const activeColour = `bg-neutral-300 dark:bg-violet-600/60`;
</script>

<template>
    <router-link
        :id="'btn-nav-' + props.label"
        :to="props.URL"
        :class="`${props.active ? activeColour : `bg-white dark:bg-primary-dark-800`} hover:bg-neutral-100 hover:dark:bg-violet-600/60 dark:text-neutral-200 flex justify-center items-center shrink-0 h-8 w-8 rounded-lg shadow-lg`"
        :aria-label="props.label"
    >
        <slot name="icon"></slot>
    </router-link>
</template>
