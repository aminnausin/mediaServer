<script setup lang="ts">
import SettingsHeader from '@/components/settings/SettingsHeader.vue';

defineProps<{ heading: string; subtext?: string; dirty?: boolean }>();
</script>

<template>
    <SettingsHeader class="block max-w-none">
        <h3 class="3xl:text-xl text-base font-medium">
            {{ heading }}
            <span :class="['transition-input text-danger transition-reveal overflow-hidden duration-75', dirty ? 'w-0 ps-1 opacity-100' : 'w-0 ps-0 opacity-0']">*</span>
        </h3>
        <p class="text-foreground-2">
            <slot>{{ subtext }}</slot>
        </p>
    </SettingsHeader>
</template>
