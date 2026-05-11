<script setup lang="ts">
import SettingsHeader from '@/components/settings/SettingsHeader.vue';

defineProps<{ heading: string; subtext?: string; dirty?: boolean }>();
</script>

<template>
    <SettingsHeader class="block max-w-none">
        <h3 class="3xl:text-xl text-base font-medium">{{ heading }} <span v-if="dirty" class="text-danger"> *</span></h3>
        <p class="text-foreground-2">
            <slot>{{ subtext }}</slot>
        </p>
    </SettingsHeader>
</template>
