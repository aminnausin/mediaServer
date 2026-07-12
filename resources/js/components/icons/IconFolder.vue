<script setup lang="ts">
import CircumFolderOn from '~icons/circum/folder-on';

withDefaults(defineProps<{ variant?: 'Proicons' | 'Circum' }>(), { variant: 'Proicons' });
</script>
<template>
    <CircumFolderOn stroke-width="0.5" stroke="currentColor" />
</template>
