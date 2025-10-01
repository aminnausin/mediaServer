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
            class: props.data.syncedLyrics ? '!bg-violet-600 dark:!bg-white dark:text-neutral-900 shadow-md' : '!bg-neutral-800 opacity-70',
        },
    ];
});
</script>
<template>
    <div
        :class="[
            'relative flex flex-wrap justify-between items-center gap-2',
            'rounded-lg shadow p-3 w-full transition',
            'bg-neutral-50 dark:bg-primary-dark-800/70 dark:hover:bg-violet-700/70 hover:bg-primary-800',
            'text-neutral-600 dark:text-neutral-100',
            `ring-[0.125rem] ${data.id === dirtyLyric?.id ? 'ring-violet-700/60' : 'ring-transparent dark:ring-neutral-700/20'}`,
        ]"
    >
        <section class="flex justify-between gap-2 w-full items-center">
            <h3 class="flex-1 truncate min-w-[30%] text-gray-900 dark:text-white" :title="data.trackName">
                {{ data.trackName }}
            </h3>
            <ButtonIcon
                class="!h-6 !w-6 ring-0 transition !p-1 rounded-md"
                type="button"
                :variant="'transparent'"
                :title="data.id === dirtyLyric?.id ? 'Already Selected' : 'Select'"
                :disabled="data.id === dirtyLyric?.id"
                @click="$emit('select')"
            >
                <template #icon>
                    <PrimeSave class="w-4 h-4" />
                </template>
            </ButtonIcon>
            <ButtonIcon
                v-if="data.syncedLyrics"
                class="!h-6 !w-6 ring-0 transition !p-1 rounded-md"
                type="button"
                :variant="'transparent'"
                :title="'Preview'"
                @click="$emit('preview')"
            >
                <template #icon>
                    <ProiconsEye class="w-4 h-4" />
                </template>
            </ButtonIcon>
        </section>
        <section class="flex flex-wrap justify-between gap-x-4 gap-y-2 w-full items-start text-sm">
            <span class="flex gap-2 items-center w-full flex-1">
                <span class="flex gap-1 truncate flex-1">
                    <h4 class="text-nowrap text-start truncate" title="Album">
                        {{ data.albumName }}
                    </h4>

                    <h4>-</h4>
                    <h4 class="text-ellipsis text-wrap line-clamp-1 min-w-fit" title="Artist">
                        {{ data.artistName }}
                    </h4>
                </span>

                <span class="hidden sm:flex gap-1 max-h-[22px] justify-end overflow-clip [overflow-clip-margin:4px]">
                    <ChipTag
                        v-for="(tag, index) in tags"
                        :key="index"
                        :label="tag.name"
                        :textClass="'text-xs'"
                        :colour="
                            tag.class ??
                            'bg-neutral-200 leading-none text-neutral-500 shadow dark:bg-neutral-900 hover:bg-violet-600 hover:text-neutral-50 hover:dark:bg-violet-600/90'
                        "
                    />
                </span>
            </span>

            <span class="sm:hidden w-full flex flex-wrap gap-1 overflow-clip [overflow-clip-margin:4px]">
                <ChipTag
                    v-for="(tag, index) in tags"
                    :key="index"
                    :label="tag.name"
                    :textClass="'text-xs'"
                    :colour="
                        tag.class ?? 'bg-neutral-200 leading-none text-neutral-500 shadow dark:bg-neutral-900 hover:bg-violet-600 hover:text-neutral-50 hover:dark:bg-violet-600/90'
                    "
                />
            </span>
        </section>
    </div>
</template>
