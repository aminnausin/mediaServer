<script setup lang="ts">
import type { MediaInfoResource } from '@/contracts/mediaInfo';
import type { VideoResource } from '@/contracts/media';

import { ButtonBase } from '@/components/cedar-ui/button';

import useClipboard from '@/composables/useClipboard';

import ProiconsChevronRight from '~icons/proicons/chevron-right';

defineProps<{ data: VideoResource; mediaInfo: MediaInfoResource }>();

const clipboard = useClipboard();
</script>

<template>
    <div class="flex flex-col gap-4">
        <details
            v-for="block in [
                {
                    label: 'Metadata',
                    value: {
                        ...data,
                        metadata: {
                            ...data.metadata,
                            lyrics: '...',
                        },
                        fonts: '...',
                    },
                },
                { label: 'Media Info', value: mediaInfo.raw_metadata },
            ]"
            :key="block.label"
            :open="true"
            :class="['flex flex-col open:gap-2', `group/block`]"
        >
            <summary class="hover:text-foreground-0 flex w-full cursor-pointer items-center gap-1.5 transition-colors">
                <ProiconsChevronRight :class="['-ms-1 size-3 transition-transform duration-200', `group-open/block:rotate-90`]" />
                <p>{{ block.label }}</p>
                <div class="relative z-20 ms-auto flex items-center">
                    <Transition
                        enter-active-class="transition ease-out duration-300"
                        enter-from-class="opacity-0 translate-x-2"
                        enter-to-class="opacity-100 translate-x-0"
                        leave-active-class="transition ease-in duration-300"
                        leave-from-class="opacity-100 translate-x-0"
                        leave-to-class="opacity-0 translate-x-2"
                    >
                        <div v-if="clipboard.copyNotification.value.has(block.label)" class="absolute -left-2 flex w-0" v-cloak>
                            <div class="border-success bg-success -ml-1.5 flex h-7 -translate-x-full items-center rounded-md border-r px-3 text-xs text-white">
                                <span>Copied!</span>
                                <div class="absolute top-1.75 -right-3 -mt-px inline-block h-full overflow-hidden">
                                    <div class="bg-success size-3 origin-top-left rotate-45 transform border border-transparent"></div>
                                </div>
                            </div>
                        </div>
                    </Transition>
                    <ButtonBase class="text-foreground-2 h-fit px-0 py-0 text-xs transition-colors" @click="clipboard.copyToClipboard(block.value, block.label)">Copy</ButtonBase>
                </div>
            </summary>
            <pre
                class="scrollbar-minimal bg-surface-2 text-foreground-0 max-h-90 overflow-auto rounded-lg p-3 text-xs leading-relaxed whitespace-pre-wrap shadow-sm dark:bg-neutral-700"
                >{{ JSON.stringify(block.value, null, 2) }}</pre
            >
        </details>
    </div>
</template>
