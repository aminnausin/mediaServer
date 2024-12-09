<script setup lang="ts">
const props = defineProps<{ title: string; data?: any[]; forceCount?: number; class?: string }>();
</script>
<template>
    <div
        :class="`flex flex-col gap-2 p-4 overflow-clip rounded-xl shadow-lg dark:bg-primary-dark-800/70 bg-primary-800 ring-1 ring-gray-900/5 flex-1 w-1/3 ${props.class}`"
    >
        <p class="text-sm dark:text-slate-400 text-slate-500 max-w-full text-nowrap">{{ props.title }}</p>
        <h3 class="text-xl">{{ props.forceCount ?? data?.length ?? 0 }}</h3>
    </div>
</template>
