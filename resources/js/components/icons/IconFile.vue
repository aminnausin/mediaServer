<script setup lang="ts">
import CircumFileOn from '~icons/circum/file-on';
import ProiconsFile from '~icons/proicons/file';

withDefaults(defineProps<{ variant?: 'Proicons' | 'Circum' }>(), { variant: 'Circum' });
</script>
<template>
    <component :is="variant === 'Circum' ? CircumFileOn : ProiconsFile" stroke-width="0.5" stroke="currentColor" />
</template>
