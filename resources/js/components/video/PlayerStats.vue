<script setup lang="ts">
import { formatBitrate, formatFileSize, toFormattedDuration } from '@/service/util';
import { useContentStore } from '@/stores/ContentStore';
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
    <div :class="['pointer-events-auto absolute top-0 left-0 p-1 sm:p-4', { 'top-6 p-4': isMaximised }]" v-show="isShowingStats" style="z-index: 7">
        <div class="w-fit rounded-md border border-neutral-700/10 bg-neutral-800/90 p-2 backdrop-blur-xs sm:min-w-52">
            <div class="scrollbar-minimal scrollbar-dark xs:h-16 flex h-12 max-h-64 w-full gap-2 overflow-y-auto pe-1 sm:h-auto sm:pe-0">
                <div class="space-y-2">
                    <section class="*:ms-4">
                        <h5 class="ms-0!">Player Info</h5>
                        <template v-if="!isAudio">
                            <p title="Viewport Resolution" v-if="player">
                                Player Resolution: <span>{{ getPlayerDimensions(player) }}</span>
                            </p>
                            <p title="Video Resolution">
                                Video Resolution:
                                <span>{{
                                    stateVideo.metadata?.resolution_width ? `${stateVideo.metadata.resolution_width}x${stateVideo.metadata?.resolution_height}` : 'Unknown'
                                }}</span>
                            </p>
                            <p title="Dropped Frames vs Total Frames">
                                Dropped Frames: <span>{{ frameHealth }}</span>
                            </p>
                        </template>
                        <p title="File Buffer Health">
                            Buffer Health: <span>{{ bufferHealth }}</span>
                        </p>
                    </section>
                    <section class="*:ms-4">
                        <h5 class="ms-0!">Media Info</h5>

                        <template v-if="stateVideo.metadata?.duration">
                            <p title="Total Duration">
                                Total Duration: <span>{{ toFormattedDuration(stateVideo.metadata.duration) }}</span>
                            </p>
                            <p title="Raw Duration">
                                Raw Duration: <span>{{ stateVideo.metadata.duration }}s</span>
                            </p>
                        </template>
                        <p title="Total Size">
                            File Size: <span>{{ stateVideo.file_size ? formatFileSize(stateVideo.file_size) : 'Unknown' }}</span>
                        </p>
                        <p title="File Bitrate" v-if="stateVideo.metadata?.bitrate">
                            Bitrate: <span>{{ formatBitrate(stateVideo.metadata.bitrate) }}</span>
                        </p>
                        <p title="File Framerate" v-if="stateVideo.metadata?.frame_rate">
                            Framerate: <span>{{ stateVideo.metadata.frame_rate }}fps</span>
                        </p>
                        <p title="File Codec" v-if="stateVideo.metadata?.codec">
                            Codec: <span>{{ stateVideo.metadata.codec }}</span>
                        </p>
                    </section>
                </div>
                <ButtonCorner
                    title="Close Stats"
                    @click="closeStats"
                    colour-classes="hover:bg-transparent"
                    text-classes="hover:text-danger-2"
                    position-classes="size-4 sticky top-0 self-start"
                >
                    <template #icon><ProiconsCancel /></template>
                </ButtonCorner>
            </div>
        </div>
    </div>
</template>

<style lang="css" scoped>
span {
    color: var(--color-neutral-300); /* text-foreground-1 but dark only */
}
</style>
