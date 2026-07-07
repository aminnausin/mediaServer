<script setup lang="ts">
import { cn } from '@aminnausin/cedar-ui';

import SkeletonCard from '@/components/skeleton/cards/SkeletonCard.vue';

withDefaults(defineProps<{ tableLength?: number }>(), { tableLength: 12 });
</script>

<template>
    <div :class="cn('contents', $attrs.class)">
        <SkeletonCard v-for="i in tableLength" :key="i" />
    </div>
</template>
