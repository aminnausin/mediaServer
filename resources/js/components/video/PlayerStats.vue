<script setup lang="ts">
import { useContentStore } from '@/stores/ContentStore';
import { formatBitrate, formatFileSize } from '@/service/util';
import { ButtonCorner } from '@/components/cedar-ui/button';
import { storeToRefs } from 'pinia';

import ProiconsCancel from '~icons/proicons/cancel';

// TODO: localise some of these ? maybe provide and injects? framehealth is only seen here
const props = defineProps<{
    isShowingStats: boolean;
    isMaximised: boolean;
    isAudio: boolean;
    frameHealth: string;
    bufferHealth: string;
    closeStats: () => void;
    player?: HTMLVideoElement | null;
}>();

const { stateVideo } = storeToRefs(useContentStore());

const getPlayerDimensions = (player: HTMLVideoElement) => {
    const rect = player.getBoundingClientRect();
    const ratio = window.devicePixelRatio;
    if (rect) return `${Math.round(rect.width * ratio)}x${Math.round(rect.height * ratio)}`;
    return 'Unknown';
};
</script>
<template>
    <section :class="['pointer-events-auto absolute top-0 left-0 p-1 sm:p-4', { 'top-6 p-4': isMaximised }]" v-show="isShowingStats" style="z-index: 7">
        <div class="w-fit rounded-md border border-neutral-700/10 bg-neutral-800/90 p-2 backdrop-blur-xs sm:min-w-52">
            <div class="scrollbar-minimal scrollbar-dark xs:h-auto xs:pe-0 flex h-12 w-full gap-2 overflow-y-auto pe-1">
                <span class="text-right *:line-clamp-1 *:break-all">
                    <p title="Dropped Frames vs Total Frames" v-if="!isAudio">Dropped Frames:</p>
                    <p title="File Buffer Health">Buffer Health:</p>
                    <p title="File Resolution" v-if="!isAudio">Resolution:</p>
                    <p title="Total Size">Total Size:</p>
                    <p title="File Framerate" v-if="stateVideo.metadata?.frame_rate">Framerate:</p>
                    <p title="File Bitrate" v-if="stateVideo.metadata?.bitrate">Bitrate:</p>
                    <p title="Viewport Resolution" v-if="!isAudio && player">Player:</p>
                    <p title="File Codec" v-if="stateVideo.metadata?.codec">Codec:</p>
                </span>
                <span class="w-full flex-1 *:line-clamp-1">
                    <p v-if="!isAudio">{{ frameHealth }}</p>
                    <p>{{ bufferHealth }}</p>
                    <template v-if="!isAudio">
                        <p v-if="stateVideo.metadata?.resolution_width">{{ stateVideo.metadata?.resolution_width }}x{{ stateVideo.metadata?.resolution_height }}</p>
                        <p v-else>Unknown</p>
                    </template>
                    <p>{{ stateVideo.file_size ? formatFileSize(stateVideo.file_size) : 'Unknown' }}</p>
                    <p v-if="stateVideo.metadata?.frame_rate">{{ stateVideo.metadata.frame_rate }}fps</p>
                    <p v-if="stateVideo.metadata?.bitrate" class="capitalize">{{ formatBitrate(stateVideo.metadata.bitrate) }}</p>
                    <p v-if="!isAudio && player">{{ getPlayerDimensions(player) }}</p>
                    <p v-if="stateVideo.metadata?.codec">{{ stateVideo.metadata.codec }}</p>
                </span>
                <ButtonCorner :title="'Close Stats'" @click="closeStats" colour-classes="hover:bg-transparent" text-classes="hover:text-danger-2" position-classes="size-4">
                    <template #icon><ProiconsCancel /></template>
                </ButtonCorner>
            </div>
        </div>
    </section>
</template>
