<script setup lang="ts">
defineProps<{ text?: string; subtext?: string }>();
</script>

<template>
    <p v-if="text || $slots">
        <slot>{{ text }}</slot>
        <slot name="subtext" class="block font-normal">
            <span v-if="subtext" class="text-foreground-2 block leading-none font-normal normal-case">
                {{ subtext }}
            </span>
        </slot>
    </p>
</template>
