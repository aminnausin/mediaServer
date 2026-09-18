<script setup lang="ts">
import type { StreamField } from '@/service/media/ffprobe';

const props = defineProps<{ title: string; fields: StreamField[] }>();
</script>

<template>
    <div class="flex flex-col gap-1">
        <p class="text-foreground-0">{{ title }}</p>
        <div class="flex flex-col">
            <div v-for="field in fields" :key="field.label" class="whitespace-pre-wrap">
                <span class="text-foreground-1">{{ field.label }}: </span>
                <span class="text-foreground-0">{{ field.value ?? '—' }}</span>
            </div>
        </div>
    </div>
</template>
