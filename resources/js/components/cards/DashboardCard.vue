<script setup lang="ts">
const props = withDefaults(
    defineProps<{
        cols?: number | string;
        rows?: number;
        class?: string;
        name?: string;
        title?: string;
        details?: string | null;
    }>(),
    {
        cols: 'full',
        rows: 1,
        name: '',
        title: '',
        details: null,
    },
);
</script>
<template>
    <div
        :class="`flex col-span-${cols} row-span-${rows} flex-col gap-2 p-4 overflow-clip rounded-xl shadow-lg dark:bg-primary-dark-800/70 bg-white ring-1 ring-gray-900/5 w-full ${props.class}`"
    >
        <header class="flex flex-wrap justify-between items-center gap-4 mb-3 @md:mb-6">
            <div class="flex-1 basis-0 flex-grow-[10000] max-w-full">
                <div class="flex overflow-hidden gap-2 items-start">
                    <div
                        class="[&>svg]:flex-shrink-0 [&>svg]:w-6 [&>svg]:h-6 [&>svg]:stroke-gray-600/80 [&>svg]:dark:stroke-neutral-400/80 text-gray-400 dark:text-neutral-400/80"
                    >
                        <slot name="icon"></slot>
                    </div>
                    <hgroup class="flex flex-wrap items-baseline gap-x-2 overflow-hidden">
                        <h2 class="text-base font-bold text-gray-600 dark:text-gray-300 truncate" :title="props.title">
                            {{ props.name }}
                        </h2>
                        <p v-if="props.details" class="text-gray-600/80 dark:text-neutral-400/80 font-medium truncate">
                            <small class="text-xs">{{ props.details }}</small>
                        </p>
                    </hgroup>
                </div>
            </div>
            <div class="flex flex-grow">
                <div class="w-full flex items-center gap-4">
                    <slot name="actions"></slot>
                </div>
            </div>
        </header>
        <slot name="slot"></slot>
    </div>
</template>
