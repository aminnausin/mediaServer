<script setup>
    const {label, colour} = defineProps(['label', 'colour']);
</script>
<template>
    <button :id="'btn-nav-' + label" :class="`flex justify-center items-center shrink-0 h-8 w-8 rounded-lg shadow-lg dark:text-neutral-200 bg-neutral-300 dark:bg-neutral-700 hover:bg-neutral-200 hover:dark:bg-${colour ?? 'violet'}-600/60`" :aria-label="label">
        <slot name="icon"></slot>
    </button>
</template>