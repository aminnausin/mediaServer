<script setup lang="ts">
import { FormLabel } from '@/components/cedar-ui/form';
const props = defineProps<{ for: string; text: string; subtext?: any }>();
</script>

<template>
    <FormLabel :for="props.for" :text="text" class="3xl:text-sm flex h-fit flex-col gap-1 text-xs tracking-widest">
        <template #subtext v-if="subtext">
            <p class="text-foreground-2 font-normal tracking-normal normal-case">
                {{ subtext }}
            </p>
        </template>
    </FormLabel>
</template>
