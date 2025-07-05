<script setup lang="ts">
defineProps<{ errors: Record<string, any>; fieldName?: string }>();
</script>

<template>
    <ul class="text-rose-600 dark:text-rose-500 text-sm" v-if="fieldName ? !!errors[fieldName] : Object.keys(errors).length > 0">
        <slot>
            <li v-for="(item, index) in fieldName ? errors[fieldName] : errors" :key="index">{{ item }}</li>
        </slot>
    </ul>
</template>
