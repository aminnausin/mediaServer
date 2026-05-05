<script setup lang="ts">
import SettingsFormLabel from '@/components/settings/SettingsFormLabel.vue';
import ToggleBase from '@/components/inputs/ToggleBase.vue';

defineProps<{
    id: string;
    label: string;
    description?: string;
    disabled?: boolean;
}>();

const model = defineModel<boolean>();
</script>

<template>
    <div class="xxs:flex-nowrap flex flex-wrap items-start justify-between gap-x-6 gap-y-2" :class="{ 'pointer-events-none opacity-40': disabled }">
        <SettingsFormLabel :for="id" :text="label" :subtext="description" class="my-auto cursor-pointer" />
        <div :class="['flex shrink-0 items-center gap-2', { 'pt-0.5': description }]">
            <ToggleBase v-model="model" :name="id" class="h-6 w-12 shrink-0 rounded-full dark:border-neutral-700/70 dark:has-checked:border-neutral-700" />
            <p class="text-foreground-1 w-7 shrink-0 text-end text-xs font-medium uppercase">
                {{ model ? 'On' : 'Off' }}
            </p>
        </div>
    </div>
</template>
