<script setup>
    import { RouterLink } from 'vue-router';
    const props = defineProps(['label', 'colour', 'URL', 'active'])
    const activeColour = `bg-neutral-300 dark:bg-${props.colour ?? 'violet'}-600/60` ;
</script>

<template>
    <router-link :id="'btn-nav-' + props.label" :to="props.URL" :class="`flex justify-center items-center shrink-0 h-8 w-8 rounded-lg shadow-lg dark:text-neutral-200 hover:bg-neutral-200 hover:dark:bg-${props.colour ?? 'violet'}-600/60 ${props.active ? activeColour : 'bg-neutral-300 dark:bg-neutral-700'}`" :aria-label="props.label">
        <slot name="icon"></slot>
    </router-link>
</template>

