<script setup lang="ts">
import IconDelete from '@/components/icons/IconDelete.vue';

const props = defineProps(['positionClasses', 'colourClasses', 'textClasses', 'to', 'label', 'disabled']);
</script>

<template>
    <router-link
        v-if="to"
        :aria-label="label ?? 'Link'"
        :title="label ?? 'Link'"
        :to="to ?? '/'"
        class="flex items-center justify-center rounded-full aria-disabled:cursor-not-allowed aria-disabled:hover:ring-neutral-200 aria-disabled:hover:dark:ring-neutral-700 aria-disabled:opacity-60"
        :class="`${positionClasses ?? ' absolute top-0 right-0 w-8 h-8 mt-5 mr-5'} ${props?.colourClasses ?? 'hover:bg-gray-50 hover:dark:bg-gray-700'} ${props?.textClasses ?? 'text-gray-600 dark:text-gray-400 hover:text-gray-800 hover:dark:text-gray-200'}`"
        :aria-disabled="disabled"
    >
        <slot name="icon">
            <IconDelete />
        </slot>
    </router-link>
    <button
        v-else
        :aria-label="props?.label ?? 'Close Modal'"
        :title="props?.label ?? 'Close Modal'"
        class="flex items-center justify-center rounded-full disabled:cursor-not-allowed disabled:hover:ring-neutral-200 disabled:hover:dark:ring-neutral-700 disabled:opacity-60"
        :class="`${props?.positionClasses ?? 'absolute top-0 right-0 w-8 h-8 mt-5 mr-5'} ${props?.colourClasses ?? 'hover:bg-gray-50 hover:dark:bg-gray-700'} ${props?.textClasses ?? 'text-gray-600 dark:text-gray-400 hover:text-gray-800 hover:dark:text-gray-200'}`"
        :disabled="disabled"
        type="button"
    >
        <slot name="icon">
            <IconDelete />
        </slot>
    </button>
</template>
