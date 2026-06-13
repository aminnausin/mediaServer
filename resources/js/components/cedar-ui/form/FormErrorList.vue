<script setup lang="ts">
defineProps<{ errors: Record<string, any>; fieldName?: string }>();

const getErrorList = (errors: Record<string, any>, fieldName?: string): string[] => {
    try {
        if (fieldName) {
            const fieldError = errors[fieldName];
            return Array.isArray(fieldError) ? fieldError : [fieldError];
        }

        const { message, ...rest } = errors;

        return [...Object.values(rest).flat()].filter(Boolean);
    } catch (error) {
        console.error(error);
        return [];
    }
};
</script>

<template>
    <ul class="text-danger text-sm" v-if="fieldName ? !!errors[fieldName] : Object.keys(errors).length > 0">
        <slot>
            <li v-for="(item, index) in getErrorList(errors, fieldName)" :key="index">{{ item }}</li>
        </slot>
    </ul>
</template>
