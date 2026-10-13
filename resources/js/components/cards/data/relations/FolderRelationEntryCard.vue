<script setup lang="ts">
import type { FolderRelationEntry } from '@/types/relationRequests';

import { ButtonIcon } from '@/components/cedar-ui/button';
import { computed } from 'vue';
import { cn } from '@aminnausin/cedar-ui';

import BlurhashImage from '@/components/lazy/BlurhashImage.vue';

import ProiconsDelete from '~icons/proicons/delete';

const props = defineProps<{ entry: FolderRelationEntry; disabled?: boolean }>();

defineEmits<{ remove: []; restore: [] }>();

const status = computed(() => {
    if (props.entry.originalType === null) return 'new';
    return props.entry.type === props.entry.originalType ? null : 'edited';
});
</script>

<template>
    <div :class="cn('bg-surface-2 dark:bg-surface-2/70 group relative flex items-stretch rounded-lg ring-1 ring-gray-900/5 transition-opacity', { 'opacity-65': entry.isRemoved })">
        <BlurhashImage
            alt="Poster"
            loading="lazy"
            :src="entry.image"
            :blurhash="entry.blurhash"
            :animate="false"
            wrapper-class="relative h-24 w-16 shrink-0 overflow-hidden rounded-l-lg bg-black/10"
            class="h-full w-full object-cover"
        />

        <span
            v-if="!entry.isRemoved && status"
            class="bg-primary/40 group-hover:bg-primary/80 absolute top-1 left-1 z-2 rounded-md px-1.5 py-0.5 leading-none text-white transition-colors sm:text-xs"
        >
            {{ status }}
        </span>

        <div class="flex min-w-0 flex-1 flex-col justify-between gap-2 p-2">
            <div class="text-foreground-2 flex items-start gap-2">
                <div class="min-w-0 flex-1 space-y-1">
                    <p :title="entry.title" :class="cn('text-foreground-0 truncate text-sm font-medium', { 'line-through': entry.isRemoved })">
                        {{ entry.title }}
                    </p>
                    <p class="truncate text-xs">{{ entry.library || 'Unknown library' }}</p>
                </div>

                <ButtonIcon
                    v-if="entry.isRemoved"
                    type="button"
                    title="Undo removal"
                    aria-label="Undo removal"
                    class="size-7 shrink-0 bg-transparent p-1 shadow-none ring-transparent"
                    :disabled="disabled"
                    @click="$emit('restore')"
                >
                    <template #icon>
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" class="size-4">
                            <path d="M0 0h24v24H0z" fill="none" />
                            <path
                                d="M9.25 12L5.957 8.707A1 1 0 0 1 5.664 8M9.25 4L5.957 7.293A1 1 0 0 0 5.664 8M7.25 19.5h6.25a5.75 5.75 0 0 0 0-11.5H5.664"
                                fill="none"
                                stroke="currentColor"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                            />
                        </svg>
                    </template>
                </ButtonIcon>

                <ButtonIcon
                    v-else
                    type="button"
                    title="Remove relation"
                    aria-label="Remove relation"
                    class="size-7 shrink-0 bg-transparent p-1 shadow-none ring-transparent"
                    :disabled="disabled"
                    @click="$emit('remove')"
                >
                    <template #icon>
                        <ProiconsDelete class="size-4" />
                    </template>
                </ButtonIcon>
            </div>

            <p v-if="entry.isRemoved" class="text-danger text-xs">This relation will be removed</p>
            <slot v-else />
        </div>
    </div>
</template>
