<script setup>
import { ref } from 'vue';

const props = defineProps(['label', 'colour','active'])
const activeColour = ref(`bg-neutral-200 dark:bg-${props.colour ?? 'violet'}-600/60`) ;
</script>
<template>
    <button :id="'btn-nav-' + props.label"
        :class="`${props.active ? activeColour : `bg-neutral-300 dark:bg-neutral-700 hover:bg-neutral-200 hover:dark:bg-${props.colour ?? 'violet'}-600/60 `} dark:text-neutral-200 flex justify-center items-center shrink-0 h-8 w-8 rounded-lg shadow-lg`"
        :aria-label="props.label">
        <slot name="icon"></slot>
        <span :class="`hidden ${activeColour}`"></span>
    </button>
</template>