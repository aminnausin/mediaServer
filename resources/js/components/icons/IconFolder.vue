<script setup lang="ts">
import CircumFolderOn from '~icons/circum/folder-on';
import ProiconsFolder from '~icons/proicons/folder';

withDefaults(defineProps<{ variant?: 'Proicons' | 'Circum' }>(), { variant: 'Circum' });
</script>
<template>
    <component :is="variant === 'Circum' ? CircumFolderOn : ProiconsFolder" stroke-width="0.5" stroke="currentColor" />
</template>
