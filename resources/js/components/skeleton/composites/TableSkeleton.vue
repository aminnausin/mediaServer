<script setup lang="ts">
import SkeletonCard from '@/components/skeleton/cards/SkeletonCard.vue';
</script>

<template>
    <div class="contents">
        <SkeletonCard v-for="i in 12" :key="i" />
    </div>
</template>
