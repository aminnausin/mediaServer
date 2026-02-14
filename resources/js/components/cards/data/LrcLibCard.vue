<script setup lang="ts">
import type { LrcLibResult } from '@/types/types';

import { toFormattedDuration } from '@/service/util';
import { useLyricStore } from '@/stores/LyricStore';
import { storeToRefs } from 'pinia';
import { ButtonIcon } from '@/components/cedar-ui/button';
import { computed } from 'vue';
import { cn } from '@aminnausin/cedar-ui';

import MediaTag from '@/components/labels/MediaTag.vue';

import ProiconsEye from '~icons/proicons/eye';
import PrimeSave from '~icons/prime/save';

const { dirtyLyric } = storeToRefs(useLyricStore());

const props = defineProps<{ data: LrcLibResult }>();
const emit = defineEmits(['preview', 'select']);

const tags = computed(() => {
    return [
        {
            name: toFormattedDuration(props.data.duration),
        },
        {
            name: props.data.syncedLyrics ? 'Synced' : 'Plain',
            class: props.data.syncedLyrics ? 'bg-primary! dark:bg-white! text-foreground-i!' : '',
        },
    ];
});
</script>
<template>
    <div
        :class="[
            'relative flex flex-wrap items-center justify-between gap-2',
            'w-full rounded-lg p-3 text-sm shadow-sm transition',
            'data-card dark:hover:bg-primary-active/70',
            `ring-2 ${data.id === dirtyLyric?.id ? 'ring-primary-active/60' : 'ring-transparent dark:ring-neutral-700/20'}`,
        ]"
    >
        <section class="flex w-full items-center justify-between gap-2">
            <h3 class="min-w-[30%] flex-1 truncate" :title="data.trackName">
                {{ data.trackName }}
            </h3>
            <ButtonIcon
                class="size-6 bg-transparent p-1 shadow-none ring-transparent"
                type="button"
                :title="data.id === dirtyLyric?.id ? 'Already Selected' : 'Select'"
                :disabled="data.id === dirtyLyric?.id"
                @click="$emit('select')"
            >
                <template #icon>
                    <PrimeSave class="size-4" />
                </template>
            </ButtonIcon>
            <ButtonIcon v-if="data.syncedLyrics" class="size-6 bg-transparent p-1 shadow-none ring-transparent" type="button" title="Preview" @click="$emit('preview')">
                <template #icon>
                    <ProiconsEye class="size-4" />
                </template>
            </ButtonIcon>
        </section>
        <section class="text-foreground-1 flex w-full flex-wrap items-start justify-between gap-x-4 gap-y-2 dark:text-inherit">
            <div class="flex w-full flex-1 items-center gap-2">
                <span class="flex flex-1 gap-1 truncate">
                    <h4 class="truncate text-start text-nowrap" title="Album">
                        {{ data.albumName }}
                    </h4>

                    <h4>-</h4>
                    <h4 class="line-clamp-1 min-w-fit text-wrap text-ellipsis" title="Artist">
                        {{ data.artistName }}
                    </h4>
                </span>

                <span class="hidden max-h-5.5 justify-end gap-1 overflow-clip [overflow-clip-margin:4px] sm:flex">
                    <MediaTag
                        v-for="(tag, index) in tags"
                        :key="index"
                        :label="tag.name"
                        :class="
                            cn(
                                'flex items-center text-xs',
                                tag.class ??
                                    'hover:bg-primary dark:hover:bg-primary/90 hover:text-foreground-i text-foreground-7 bg-neutral-200 leading-none shadow-sm dark:bg-neutral-900',
                            )
                        "
                    />
                </span>
            </div>

            <span class="flex w-full flex-wrap gap-1 overflow-clip text-xs [overflow-clip-margin:4px] sm:hidden">
                <MediaTag
                    v-for="(tag, index) in tags"
                    :key="index"
                    :label="tag.name"
                    :class="
                        cn(
                            'text-xs',
                            tag.class ??
                                'hover:bg-primary dark:hover:bg-primary/90 hover:text-foreground-i text-foreground-7 bg-neutral-200 leading-none shadow-sm dark:bg-neutral-900',
                        )
                    "
                />
            </span>
        </section>
    </div>
</template>
