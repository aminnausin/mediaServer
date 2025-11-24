<script setup lang="ts">
defineProps<{ name?: string; title?: string; details?: string }>();
</script>

<template>
    <header class="flex flex-wrap justify-between items-center gap-4 mb-3 @md:mb-6">
        <div class="flex-1 basis-0 flex-grow-[10000] max-w-full">
            <div class="flex overflow-hidden gap-2 items-start">
                <div class="[&>svg]:flex-shrink-0 [&>svg]:w-6 [&>svg]:h-6 [&>svg]:stroke-gray-400 [&>svg]:dark:stroke-gray-600">
                    <slot name="icon"></slot>
                </div>
                <hgroup class="flex flex-wrap items-baseline gap-x-2 overflow-hidden">
                    <h2 class="text-base font-bold text-gray-600 dark:text-gray-300 truncate" :title="title">
                        {{ name }}
                    </h2>
                    <p v-if="details" class="text-gray-400 dark:text-gray-600 font-medium truncate">
                        <small class="text-xs">{{ details }}</small>
                    </p>
                </hgroup>
            </div>
        </div>
        <div v-if="$slots.actions" class="flex flex-grow">
            <div class="w-full flex items-center gap-4">
                <slot name="actions"></slot>
            </div>
        </div>
    </header>
</template>
