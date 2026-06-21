<script setup lang="ts">
import { FormLabel } from '@/components/cedar-ui/form';

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
    <div class="flex items-start justify-between" :class="{ 'input-disabled': disabled, '-mb-1': description }">
        <FormLabel :for="id" :text="label" class="flex h-fit flex-col gap-1">
            <template #subtext v-if="description">
                <p class="text-foreground-2 text-xs font-normal tracking-normal normal-case">
                    {{ description }}
                </p>
            </template>
        </FormLabel>
        <div :class="['flex shrink-0 items-center gap-2', { 'pt-0.5': description }]">
            <ToggleBase v-model="model" :name="id" class="h-6 w-12 shrink-0 rounded-full dark:border-neutral-700/70 dark:has-checked:border-neutral-700" />
            <p :class="['w-8 shrink-0 text-end font-medium uppercase', { 'text-foreground-1': !model }]">
                {{ model ? 'On' : 'Off' }}
            </p>
        </div>
    </div>
</template>
