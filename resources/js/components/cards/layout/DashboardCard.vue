<script setup lang="ts">
const props = withDefaults(
    defineProps<{
        cols?: number | string;
        rows?: number;
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
        :class="[
            `col-span-full flex row-span-${rows} dark:bg-primary-dark-800/70 w-full flex-col gap-2 rounded-xl bg-white p-3 shadow-lg ring-1 ring-gray-900/5`,
            `${parseInt(`${cols}`) < 3 ? `sm:col-span-2 lg:col-span-${cols}` : `lg:col-span-${cols}`}`,
        ]"
    >
        <header class="mb-3 flex flex-wrap items-center justify-between gap-4">
            <div class="max-w-full flex-1 grow-10000 basis-0">
                <div class="flex items-start gap-2 overflow-hidden">
                    <div class="[&>svg]:stroke-foreground-2 text-foreground-3 dark:text-neutral-400/80 [&>svg]:h-6 [&>svg]:w-6 [&>svg]:shrink-0 dark:[&>svg]:stroke-neutral-400/80">
                        <slot name="icon"></slot>
                    </div>
                    <hgroup class="flex flex-wrap items-baseline gap-x-2 overflow-hidden">
                        <h2 class="text-foreground-1 truncate text-base font-medium dark:text-neutral-300" :title="title">
                            {{ name }}
                        </h2>
                        <p v-if="props.details" class="text-foreground-2 truncate font-medium dark:text-neutral-400/80">
                            <small class="text-xs">{{ props.details }}</small>
                        </p>
                    </hgroup>
                </div>
            </div>
            <div class="flex grow items-center">
                <slot name="actions"></slot>
            </div>
        </header>
        <slot></slot>
    </div>
</template>
