<script setup lang="ts">
import type { FormLabelProps } from '@aminnausin/cedar-ui';

const props = defineProps<FormLabelProps>();
</script>

<template>
    <label v-if="text || $slots" :for="props.for" class="font-medium">
        <slot>{{ text }}</slot>
        <slot name="subtext" class="block font-normal">
            <span v-if="subtext" class="text-foreground-2 block leading-none font-normal">
                {{ subtext }}
            </span>
        </slot>
    </label>
</template>
