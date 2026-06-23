<script setup lang="ts">
defineProps<{ title: string; value?: any }>();
</script>
<template>
    <div class="space-y-0.5">
        <div class="text-nowrap">{{ title }}</div>
        <slot>
            <div class="text-foreground-1 text-xs" v-if="value">
                {{ value }}
            </div>
        </slot>
    </div>
</template>
