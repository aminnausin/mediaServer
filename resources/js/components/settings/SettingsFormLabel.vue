<script setup lang="ts">
import { FormLabel } from '@/components/cedar-ui/form';
const props = defineProps<{ for: string; text: string; subtext?: any }>();
</script>

<template>
    <FormLabel :for="props.for" :text="text" class="flex h-fit flex-col gap-1">
        <template #subtext v-if="subtext">
            <p class="text-foreground-2 3xl:text-sm text-xs leading-relaxed font-normal">
                {{ subtext }}
            </p>
        </template>
    </FormLabel>
</template>
