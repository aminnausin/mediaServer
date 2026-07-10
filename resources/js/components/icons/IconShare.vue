<script setup lang="ts">
import CircumShare1 from '~icons/circum/share-1';
import ProiconsOpen from '~icons/proicons/open';

withDefaults(defineProps<{ variant?: 'Proicons' | 'Circum' }>(), { variant: 'Proicons' });
</script>
<template>
    <ProiconsOpen v-if="variant === 'Proicons'" />
    <CircumShare1 v-else />
</template>
