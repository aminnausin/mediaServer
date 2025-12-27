<script setup lang="ts">
import type { LrcLibResult } from '@/types/types';

import { toFormattedDuration } from '@/service/util';
import { useLyricStore } from '@/stores/LyricStore';
import { storeToRefs } from 'pinia';
import { computed } from 'vue';

import ButtonIcon from '@/components/inputs/ButtonIcon.vue';
import ChipTag from '@/components/labels/ChipTag.vue';

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
            class: props.data.syncedLyrics ? 'bg-violet-600! dark:bg-white! dark:text-neutral-900 shadow-md' : 'bg-neutral-800! opacity-70',
        },
    ];
});
</script>
<template>
    <div
        :class="[
            'relative flex flex-wrap items-center justify-between gap-2',
            'w-full rounded-lg p-3 shadow-sm transition',
            'dark:bg-primary-dark-800/70 hover:bg-primary-800 dark:hover:bg-primary-active/70 bg-neutral-50',
            'text-foreground-1 dark:text-neutral-100',
            `ring-2 ${data.id === dirtyLyric?.id ? 'ring-primary-active/60' : 'ring-transparent dark:ring-neutral-700/20'}`,
        ]"
    >
        <section class="flex w-full items-center justify-between gap-2">
            <h3 class="min-w-[30%] flex-1 truncate text-gray-900 dark:text-white" :title="data.trackName">
                {{ data.trackName }}
            </h3>
            <ButtonIcon
                class="h-6! w-6! rounded-md p-1! ring-0 transition"
                type="button"
                :variant="'transparent'"
                :title="data.id === dirtyLyric?.id ? 'Already Selected' : 'Select'"
                :disabled="data.id === dirtyLyric?.id"
                @click="$emit('select')"
            >
                <template #icon>
                    <PrimeSave class="h-4 w-4" />
                </template>
            </ButtonIcon>
            <ButtonIcon
                v-if="data.syncedLyrics"
                class="h-6! w-6! rounded-md p-1! ring-0 transition"
                type="button"
                :variant="'transparent'"
                :title="'Preview'"
                @click="$emit('preview')"
            >
                <template #icon>
                    <ProiconsEye class="h-4 w-4" />
                </template>
            </ButtonIcon>
        </section>
        <section class="flex w-full flex-wrap items-start justify-between gap-x-4 gap-y-2 text-sm">
            <span class="flex w-full flex-1 items-center gap-2">
                <span class="flex flex-1 gap-1 truncate">
                    <h4 class="truncate text-start text-nowrap" title="Album">
                        {{ data.albumName }}
                    </h4>

                    <h4>-</h4>
                    <h4 class="line-clamp-1 min-w-fit text-wrap text-ellipsis" title="Artist">
                        {{ data.artistName }}
                    </h4>
                </span>

                <span class="hidden max-h-[22px] justify-end gap-1 overflow-clip [overflow-clip-margin:4px] sm:flex">
                    <ChipTag
                        v-for="(tag, index) in tags"
                        :key="index"
                        :label="tag.name"
                        :textClass="'text-xs'"
                        :colour="
                            tag.class ??
                            'bg-neutral-200 leading-none text-neutral-500 shadow-sm dark:bg-neutral-900 hover:bg-violet-600 hover:text-neutral-50 dark:hover:bg-violet-600/90'
                        "
                    />
                </span>
            </span>

            <span class="flex w-full flex-wrap gap-1 overflow-clip [overflow-clip-margin:4px] sm:hidden">
                <ChipTag
                    v-for="(tag, index) in tags"
                    :key="index"
                    :label="tag.name"
                    :textClass="'text-xs'"
                    :colour="
                        tag.class ??
                        'bg-neutral-200 leading-none text-neutral-500 shadow-sm dark:bg-neutral-900 hover:bg-violet-600 hover:text-neutral-50 dark:hover:bg-violet-600/90'
                    "
                />
            </span>
        </section>
    </div>
</template>
