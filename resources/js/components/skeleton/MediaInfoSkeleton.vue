<script setup lang="ts">
const rowGroups = [
    { header: 'w-20', rows: [3, 4, 3.5, 5, 2, 2.5] },
    { header: 'w-24', rows: [2, 4, 2] },
    { header: 'w-24', rows: [3, 4, 3.5] },
];
</script>

<template>
    <div class="pointer-events-none flex animate-pulse flex-col gap-4">
        <div v-for="(group, gi) in rowGroups" :key="gi" class="flex flex-col gap-2">
            <div :class="['bg-foreground-1/20 h-4 rounded', group.header]"></div>
            <div class="ms-4 flex flex-col gap-1.5">
                <div v-for="(w, ri) in group.rows" :key="ri" class="flex items-center gap-2">
                    <div class="bg-foreground-2/10 h-3 w-16 rounded-sm"></div>
                    <div class="bg-foreground-2/10 h-3 rounded-sm" :style="{ width: `${w}rem` }"></div>
                </div>
            </div>
        </div>
    </div>
</template>
