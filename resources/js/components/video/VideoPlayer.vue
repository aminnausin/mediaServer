<!-- eslint-disable no-unused-vars -->
<script setup lang="ts">
import type { FolderResource, VideoResource } from '@/types/resources';
import type { ContextMenuItem, PopoverItem } from '@/types/types';
import type { Metadata, Series } from '@/types/model';

import { computed, onMounted, onUnmounted, ref, useTemplateRef, watch, type ComputedRef, type Ref } from 'vue';
import { handleStorageURL, toFormattedDate, toFormattedDuration } from '@/service/util';
import { UseCreatePlayback } from '@/service/mutations';
import { useVideoPlayback } from '@/service/queries';
import { useContentStore } from '@/stores/ContentStore';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { getMediaUrl } from '@/service/api';
import { toast } from '@/service/toaster/toastService';

import VideoPartyPanel from '@/components/video/VideoPartyPanel.vue';
import VideoPopoverItem from '@/components/video/VideoPopoverItem.vue';
import ButtonCorner from '@/components/inputs/ButtonCorner.vue';
import VideoHeatmap from '@/components/video/VideoHeatmap.vue';
import VideoPopover from '@/components/video/VideoPopover.vue';
import VideoTooltip from '@/components/video/VideoTooltip.vue';
import VideoButton from '@/components/video/VideoButton.vue';

import _, { throttle } from 'lodash';

import ProiconsFullScreenMaximize from '~icons/proicons/full-screen-maximize';
import ProiconsFullScreenMinimize from '~icons/proicons/full-screen-minimize';
import ProiconsArrowTrending from '~icons/proicons/arrow-trending';
import ProiconsFastForward from '~icons/proicons/fast-forward';
import ProiconsVolumeMute from '~icons/proicons/volume-mute';
import ProiconsVolumeLow from '~icons/proicons/volume-low';
import ProiconsCheckmark from '~icons/proicons/checkmark';
import ProiconsSparkle2 from '~icons/proicons/sparkle-2';
import ProiconsSettings from '~icons/proicons/settings';
import ProiconsSpinner from '~icons/proicons/spinner';
import ProiconsReverse from '~icons/proicons/reverse';
import ProiconsVolume from '~icons/proicons/volume';
import ProiconsCancel from '~icons/proicons/cancel';
import ProiconsPlay from '~icons/proicons/play';

const controlsHideTime = 2500;
const playbackDataBuffer = 5;
const playerHealthBuffer = 5;

const emit = defineEmits(['loadedData', 'seeked', 'play', 'pause', 'ended', 'loadedMetadata']);

// Global State
const { playbackHeatmap, ambientMode } = storeToRefs(useAppStore());
const { createRecord, updateViewCount } = useContentStore();
const { setContextMenu } = useAppStore();

const { stateVideo, stateFolder, nextVideoURL, previousVideoURL } = storeToRefs(useContentStore()) as unknown as {
    stateVideo: Ref<VideoResource | { id?: number; metadata?: Metadata; path?: string }>;
    stateFolder: Ref<FolderResource | { id?: number; series?: Series; path?: string }>;
    nextVideoURL: ComputedRef<string>;
    previousVideoURL: ComputedRef<string>;
};

// API Cache
const progressCache = ref<{ metadata_id: number; progress: number }[]>([]);
const metadataId = ref<number>(NaN);
const currentId = ref(-1);

// API
const { data: playbackData } = useVideoPlayback(metadataId);
const createPlayback = UseCreatePlayback().mutate;

// V-models for inputs
const timeDuration = computed(() => stateVideo.value?.metadata?.duration ?? 0);
const timeElapsed = ref(0);
const timeSeeking = ref('');
const currentVolume = ref(0.1);
const cachedVolume = ref(0.5);

// Player State
const controlsHideTimeout = ref<number>();
const controls = ref(false);
const isShowingParty = ref(false);
const isShowingStats = ref(false);
const isFullScreen = ref(false);
const isLoading = ref(true);
const isSeeking = ref(false);
const isLooping = ref(false);
const isPaused = ref(true);
const isMuted = ref(false);

// Player Info
const endsAtTime = ref('00:00');
const bufferHealth = ref<string>('0s');
const frameHealth = ref<string>('0/0');
const playerHealthCounter = ref(0);
const timeStrings = computed(() => {
    let timeElapsedVerbose = toFormattedDuration((timeElapsed.value / 100) * timeDuration.value, false, 'verbose') ?? 'Unknown';
    let timeDurationVerbose = toFormattedDuration(timeDuration.value, false, 'verbose') ?? 'Unknown';
    return {
        timeElapsed: toFormattedDuration((timeElapsed.value / 100) * timeDuration.value, true, 'digital') ?? '00:00',
        timeDuration: toFormattedDuration(timeDuration.value, true, 'digital') ?? '00:00',
        timeVerbose: `${timeElapsedVerbose} out of ${timeDurationVerbose}`,
        timeElapsedVerbose,
    };
});

// Elements
const container = useTemplateRef('video-container');
const progressBar = useTemplateRef('progress-bar');
const popover = useTemplateRef('popover');
const tooltip = useTemplateRef('tooltip');
const player = useTemplateRef('player');
// const url = ref('');

const contextMenuItems = computed(() => {
    let items: ContextMenuItem[] = [
        {
            text: 'Loop',
            icon: isLooping.value ? ProiconsCheckmark : undefined,
            action: () => {
                isLooping.value = !isLooping.value;
            },
        },
        {
            text: 'Show Stats',
            icon: isShowingStats.value ? ProiconsCheckmark : undefined,
            action: () => {
                isShowingStats.value = !isShowingStats.value;
            },
        },
        {
            text: 'Show Party Demo',
            icon: isShowingParty.value ? ProiconsCheckmark : undefined,
            action: () => {
                isShowingParty.value = !isShowingParty.value;
            },
        },
    ];
    return items;
});

const videoPopoverItems = computed(() => {
    let items: PopoverItem[] = [
        {
            text: 'Ambient Mode',
            title: 'Toggle Ambient Mode',
            icon: ProiconsSparkle2,
            selectedIcon: ProiconsCheckmark,
            selected: ambientMode.value ?? false,
            selectedIconStyle: 'text-purple-600',
            action: () => {
                ambientMode.value = !ambientMode.value;
            },
        },
        {
            text: 'Heatmap',
            title: 'Toggle Playback Heatmap',
            icon: ProiconsArrowTrending,
            selectedIcon: ProiconsCheckmark,
            selected: playbackHeatmap.value ?? false,
            selectedIconStyle: 'text-purple-600',
            action: () => {
                playbackHeatmap.value = !playbackHeatmap.value;
            },
        },
    ];
    return items;
});

// Computed Player State

const isAudio = computed(() => {
    return stateVideo.value.metadata?.mime_type?.startsWith('audio') ?? false;
});

const isPortrait = computed(() => {
    return (
        stateVideo.value.metadata?.resolution_width &&
        stateVideo.value.metadata.resolution_height &&
        stateVideo.value.metadata.resolution_width < stateVideo.value.metadata.resolution_height
    );
});

const audioPoster = computed(() => {
    return (
        handleStorageURL(stateVideo.value?.metadata?.poster_url) ??
        handleStorageURL(stateFolder.value.series?.thumbnail_url) ??
        'https://m.media-amazon.com/images/M/MV5BMjVjZGU5ZTktYTZiNC00N2Q1LThiZjMtMDVmZDljN2I3ZWIwXkEyXkFqcGdeQXVyMTUzMTg2ODkz._V1_.jpg'
    );
});

const initVideoPlayer = async () => {
    let root = document.getElementById('root');

    isPaused.value = true;
    isLooping.value = false;

    if (!root) return;

    root.scrollIntoView();

    if (progressCache.value && progressCache.value.length > 0) {
        createPlayback({ entries: progressCache.value });
        progressCache.value = [];
    }

    metadataId.value = stateVideo.value?.metadata ? stateVideo.value?.metadata.id : NaN;
    // url.value = await getMediaUrl(stateVideo.value.path ?? '');
};

//#region Player Events

const handleProgress = (override = false) => {
    if (!player.value || !stateVideo.value.metadata?.id) return;

    let progress = player.value.currentTime / player.value.duration;

    if (isNaN(progress) || !progress) return;

    progressCache.value = [...progressCache.value, { metadata_id: stateVideo.value?.metadata?.id, progress: parseFloat(progress.toFixed(2)) * 1000 }];

    if (progressCache.value.length >= playbackDataBuffer || override) {
        createPlayback({ entries: progressCache.value });
        progressCache.value = [];
    }
};

/**
 * Handle On Play
 *
 * If the player is not paused, pause the player and return.
 *
 * Otherwise, play the player.
 * Generate end time.
 * Emit 'play' event.
 *
 * If this is the first time the player has played this video or if override is set...
 * Record video progress and force publish it, ignoring the buffer.
 * Create a history record.
 * Update video view count.
 *
 * Otherwise, record video progress and quit.
 * @param override force first time playback of video.
 */
const onPlayerPlay = async (override = false) => {
    if (!player.value || !stateVideo.value.id) return;
    try {
        isLoading.value = true;
        await player.value.play();
        isLoading.value = false;
        isPaused.value = false;
        getEndTime();
        emit('loadedData');
        emit('play');

        if (currentId.value === stateVideo.value.id && !override == true) {
            handleProgress();
            return; // stop recording every time video seek
        }

        currentId.value = stateVideo.value.id;
        createRecord(stateVideo.value.id);
        updateViewCount(stateVideo.value.id);
        handleProgress(true);
    } catch (error) {
        isLoading.value = false;
    }
};

const onPlayerPause = () => {
    if (!player.value) return;
    player.value.pause();
    isPaused.value = true;
    emit('pause');
    return;
};

const onPlayerEnded = () => {
    currentId.value = -1;
    if (isLooping.value) {
        onPlayerPlay();
        return;
    }

    emit('ended');
    onPlayerPause();
};

const onPlayerLoadStart = () => {
    isLoading.value = true;
};

const onPlayerLoadeddata = () => {
    emit('loadedData');
    emit('loadedMetadata');
    if (stateVideo.value) isLoading.value = false;
};

const onPlayerWaiting = () => {
    if (player.value?.loop) {
        createRecord(stateVideo.value.id);
        updateViewCount(stateVideo.value.id);
        handleProgress(true);
    }
};

const cacheVolume = () => {
    if (!player.value) return;
    localStorage.setItem('videoVolume', currentVolume.value.toString());
};

//#endregion

const debouncedCacheVolume = _.debounce(cacheVolume, 300);

const handleVolumeChange = () => {
    if (!player.value) return;

    player.value.volume = currentVolume.value;
    debouncedCacheVolume();
};

const handleMute = () => {
    if (!player.value) return;

    if (isMuted.value) {
        currentVolume.value = cachedVolume.value;
        player.value.volume = cachedVolume.value;
    } else if (!isMuted.value) {
        cachedVolume.value = currentVolume.value;
        currentVolume.value = 0;
        player.value.volume = 0;
    }

    isMuted.value = !isMuted.value;
};

const handlePlayerToggle = () => {
    if (isPaused.value) {
        onPlayerPlay();
        return;
    }

    onPlayerPause();
};

const handleFullScreen = async () => {
    if (!container.value) return;

    try {
        if (!isFullScreen.value || document.fullscreenElement == null) {
            await container.value?.requestFullscreen();
            isFullScreen.value = true;
            document.documentElement.classList.add('fullscreen');
        } else {
            await document.exitFullscreen();
            isFullScreen.value = false;
            document.documentElement.classList.remove('fullscreen');
        }
    } catch (error) {
        document.documentElement.classList.remove('fullscreen');
        isFullScreen.value = false;
        toast.error('Unable to switch fullscreen mode...');
        console.log(error);
    }
};

const handlePlayerTimeUpdate = (event: any) => {
    playerHealthCounter.value += 1;

    if (playerHealthCounter.value >= playerHealthBuffer) {
        getPlayerInfo();
        playerHealthCounter.value = 0;
    }

    if (!isSeeking.value) timeElapsed.value = (event.target.currentTime / timeDuration.value) * 100;
};

const handleSeek = () => {
    if (!player.value || timeElapsed.value < 0 || timeElapsed.value > 100) return;

    isSeeking.value = false;
    player.value.currentTime = (timeElapsed.value / 100) * timeDuration.value;

    if (!isPaused.value) onPlayerPlay();
};

const handleSeekPreview = () => {
    if (!player.value || isSeeking.value) return;
    if (!isSeeking.value) isSeeking.value = true;
};

function resetControlsTimeout() {
    controls.value = true;

    clearTimeout(controlsHideTimeout.value);
    controlsHideTimeout.value = setTimeout(() => {
        controls.value = false;
        popover.value?.handleClose();
    }, controlsHideTime);
}

const debouncedEndTime = _.debounce(getEndTime, 100);

function playerMouseActivity() {
    if (!isPaused.value) {
        resetControlsTimeout();
        return;
    }

    debouncedEndTime();

    if (!controlsHideTimeout.value && controls.value == true) return;

    controls.value = true;
    clearTimeout(controlsHideTimeout.value);
}

function getEndTime() {
    if (!player.value || currentId.value == -1) return;
    endsAtTime.value = toFormattedDate(new Date(new Date().getTime() + (timeDuration.value - (timeElapsed.value / 100) * timeDuration.value) * 1000), true, {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true,
    });
}

function getPlayerInfo() {
    if (!player.value) return;
    let playbackQuality = player.value.getVideoPlaybackQuality();
    bufferHealth.value = toFormattedDuration(player.value.buffered.length, false) ?? '0s';
    frameHealth.value = `${playbackQuality.droppedVideoFrames} / ${playbackQuality.totalVideoFrames}`;
}

const handleProgressTooltip = throttle((event: MouseEvent) => {
    getProgressTooltip(event);
    requestAnimationFrame(() => {
        if (!tooltip.value) return;
        tooltip.value.calculateTooltipPosition(event);
    });
}, 7);

const getProgressTooltip = (event: MouseEvent) => {
    if (!player.value || !timeDuration.value || !progressBar.value) return;
    const rect = progressBar.value.getBoundingClientRect();
    const offsetX = event.clientX - rect.left;
    const percent = offsetX / rect.width;

    const time = percent < 0 ? 0 : Math.min(timeDuration.value, percent * timeDuration.value);

    timeSeeking.value = toFormattedDuration(time, true, 'digital') ?? '00:00';
};

watch(stateVideo, initVideoPlayer);

onMounted(() => {
    const savedVolume = parseFloat(localStorage.getItem('videoVolume') ?? '');
    if (!isNaN(savedVolume) && player.value) {
        currentVolume.value = savedVolume;
        player.value.volume = savedVolume;
    }
});

onUnmounted(() => {
    debouncedCacheVolume.cancel();
});

defineExpose({
    isAudio,
    audioPoster,
});
</script>

<template>
    <div
        :class="`relative rounded-xl overflow-clip video-player`"
        ref="video-container"
        @mousemove="playerMouseActivity"
        @contextmenu="
            (e: any) => {
                setContextMenu(e, { items: contextMenuItems, style: 'w-32', itemStyle: 'text-xs' });
            }
        "
    >
        <section style="z-index: 5" class="player-controls text-white pointer-events-none">
            <section class="absolute p-1 sm:p-4 top-0 left-0 text-xs font-mono pointer-events-auto" v-show="isShowingStats" style="z-index: 5">
                <div class="flex gap-2 bg-neutral-900/80 border-slate-700/20 border rounded-md p-2 w-fit sm:min-w-52">
                    <span class="[&>*]:line-clamp-1 [&>*]:break-all text-right">
                        <p title="Dropped Frames vs Total Frames" v-if="!isAudio">Dropped Frames:</p>
                        <p title="Video Buffer Health">Buffer Health:</p>
                        <p title="Video Resolution" v-if="!isAudio">Resolution:</p>
                        <p title="Video Codec" v-if="stateVideo.metadata?.codec">Codec:</p>
                    </span>
                    <span class="flex-1 w-full [&>*]:line-clamp-1">
                        <p v-if="!isAudio">{{ frameHealth }}</p>
                        <p>{{ bufferHealth }}</p>
                        <p v-if="!isAudio">{{ player?.videoWidth }}x{{ player?.videoHeight }}</p>
                        <p v-if="stateVideo.metadata?.codec">{{ stateVideo.metadata?.codec }}</p>
                    </span>
                    <ButtonCorner
                        :title="'Close Stats'"
                        @click="isShowingStats = false"
                        colour-classes="hover:bg-transparent"
                        text-classes="hover:text-rose-600"
                        position-classes="w-4 h-4 p-0"
                    >
                        <template #icon><ProiconsCancel /></template>
                    </ButtonCorner>
                </div>
            </section>

            <section class="absolute p-1 sm:p-4 top-0 right-0 text-xs font-mono pointer-events-auto" v-show="isShowingParty" style="z-index: 4">
                <VideoPartyPanel :player="player ?? undefined" />
            </section>
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="translate-y-full"
                enter-to-class="translate-y-0"
                leave-active-class="transition ease-in duration-300"
                leave-from-class="translate-y-0"
                leave-to-class="translate-y-full"
            >
                <div v-show="controls" class="absolute bottom-0 left-0 z-20 w-full h-32 opacity-20 bg-gradient-to-b from-transparent to-black" v-cloak></div>
            </Transition>

            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="translate-y-full"
                enter-to-class="translate-y-0"
                leave-active-class="transition ease-in duration-300"
                leave-from-class="translate-y-0"
                leave-to-class="translate-y-full"
            >
                <div
                    v-cloak
                    v-show="controls"
                    class="absolute bottom-0 left-0 z-[31] w-full h-12 flex flex-col justify-end bg-gradient-to-b from-neutral-900/0 to-neutral-900/30 !pointer-events-none"
                >
                    <section class="flex-1 w-full rounded-full flex flex-col-reverse px-2 h-8 relative">
                        <VideoTooltip
                            ref="tooltip"
                            tooltip-position="top"
                            class="-top-6 left-0"
                            :tooltip-text="timeSeeking"
                            :target-element="progressBar ?? undefined"
                            :offset="8"
                            :tooltip-arrow="false"
                        />
                        <input
                            @input="handleSeekPreview"
                            @change="handleSeek"
                            @mousemove="handleProgressTooltip"
                            @mouseenter="
                                () => {
                                    if (!tooltip) return;
                                    tooltip?.tooltipToggle();
                                }
                            "
                            @mouseleave="
                                () => {
                                    if (!tooltip) return;
                                    tooltip?.tooltipToggle(false);
                                }
                            "
                            ref="progress-bar"
                            title="Video Progress"
                            placeholder="0"
                            v-model="timeElapsed"
                            type="range"
                            min="0"
                            max="100"
                            value="0"
                            step="0.01"
                            :class="
                                `peer w-full h-2 appearance-none flex items-center cursor-pointer bg-transparent slider timeline pointer-events-auto ` + // Base Class
                                `[&::-webkit-slider-thumb]:!bg-white [&::-moz-range-thumb]:!bg-white ` // Thumb Colour
                            "
                            :aria-valuetext="timeStrings.timeElapsedVerbose"
                        />
                        <VideoHeatmap :playback-data="playbackData" />
                    </section>

                    <section class="w-full flex items-center gap-2 p-2 text-xs pointer-events-auto">
                        <section class="flex gap-1 items-center">
                            <VideoButton @click="handlePlayerToggle" :title="isPaused ? 'Play' : 'Pause'" :use-tooltip="true" :target-element="player ?? undefined">
                                <template #icon>
                                    <ProiconsPlay v-if="isPaused" class="w-4 h-4" />
                                    <svg v-else class="w-4 h-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            fill-rule="evenodd"
                                            clip-rule="evenodd"
                                            d="M8 3C8.55228 3 9 3.44772 9 4L9 20C9 20.5523 8.55228 21 8 21C7.44772 21 7 20.5523 7 20L7 4C7 3.44772 7.44772 3 8 3ZM16 3C16.5523 3 17 3.44772 17 4V20C17 20.5523 16.5523 21 16 21C15.4477 21 15 20.5523 15 20V4C15 3.44772 15.4477 3 16 3Z"
                                            fill="currentColor"
                                            v-cloak
                                        ></path>
                                    </svg>
                                </template>
                            </VideoButton>
                            <!-- <VideoButton v-if="previousVideoURL" class="hidden md:block" :icon="ProiconsReverse" :link="previousVideoURL" title="Play Previous Video" /> -->
                            <VideoButton
                                v-if="nextVideoURL"
                                class="hidden sm:block"
                                :icon="ProiconsFastForward"
                                :link="nextVideoURL"
                                title="Play Next Video"
                                :use-tooltip="true"
                                :target-element="player ?? undefined"
                            />
                        </section>

                        <section
                            class="hidden xs:flex gap-1 font-mono opacity-80 hover:opacity-100 line-clamp-1"
                            :title="`The ${isAudio ? 'audio' : 'video'} will finish at ${endsAtTime}`"
                            v-show="endsAtTime !== ''"
                        >
                            <p class="line-clamp-1">Ends at</p>
                            <time class="line-clamp-1">{{ endsAtTime }}</time>
                        </section>
                        <section class="line-clamp-1 overflow-clip font-mono opacity-80 hover:opacity-100 ml-auto" :title="timeStrings.timeVerbose">
                            <time>{{ timeStrings.timeElapsed }}</time>
                            <span> / </span>
                            <time>{{ timeStrings.timeDuration }}</time>
                        </section>

                        <section class="flex items-center group">
                            <VideoButton
                                :title="`${isMuted ? 'Unmute' : 'Mute'}`"
                                class="duration-150 ease-out opacity-80 hover:opacity-100 hover:text-white"
                                @click="handleMute"
                                :use-tooltip="true"
                                :target-element="player ?? undefined"
                            >
                                <template #icon>
                                    <ProiconsVolume v-if="currentVolume > 0.3" class="w-4 h-4" />
                                    <ProiconsVolumeLow v-else-if="currentVolume > 0" class="w-4 h-4" />
                                    <ProiconsVolumeMute v-else class="w-4 h-4" />
                                </template>
                            </VideoButton>
                            <div class="relative h-1.5 mx-0 group-hover:mx-1 rounded-full group-hover:w-12 invisible group-hover:visible w-0 ease-out duration-300">
                                <input
                                    v-model="currentVolume"
                                    @input="handleVolumeChange"
                                    type="range"
                                    min="0"
                                    max="1"
                                    step="0.01"
                                    :class="`w-full h-full appearance-none flex items-center cursor-pointer bg-transparent z-30 slider volume`"
                                    :title="`Volume: ${currentVolume * 100}%`"
                                />
                            </div>
                        </section>
                        <VideoPopover
                            popoverClass="!max-w-40 rounded-lg h-16 md:h-fit"
                            ref="popover"
                            :margin="80"
                            :player="player ?? undefined"
                            :vertical-offset-pixels="48"
                            :button-attributes="{ 'use-tooltip': true, 'target-element': player ?? undefined }"
                        >
                            <template #buttonIcon>
                                <ProiconsSettings class="w-4 h-4 hover:rotate-180 transition-transform ease-in-out duration-500" />
                            </template>
                            <template #content>
                                <section class="flex flex-col text-xs h-16 md:h-fit overflow-y-auto scrollbar-minimal transition-transform">
                                    <VideoPopoverItem v-for="(item, index) in videoPopoverItems" :key="index" v-bind="item" />
                                </section>
                            </template>
                        </VideoPopover>
                        <VideoButton
                            :icon="isFullScreen ? ProiconsFullScreenMinimize : ProiconsFullScreenMaximize"
                            @click="handleFullScreen"
                            :title="!isFullScreen ? 'Make Fullscreen' : 'Exit Fullscreen'"
                            :use-tooltip="true"
                            :target-element="player ?? undefined"
                        />
                    </section>
                </div>
            </Transition>
            <section v-show="isLoading" class="w-fit h-fit absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2" style="z-index: 4">
                <ProiconsSpinner class="w-8 h-8 animate-spin" />
            </section>
            <section class="w-fit h-fit absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2" style="z-index: 4">
                <Transition
                    enter-active-class="transition ease-out duration-1000 bg-black text-white"
                    enter-from-class="scale-50 opacity-100 !text-white"
                    enter-to-class="scale-100 opacity-0 !text-white"
                    v-cloak
                >
                    <div
                        v-show="isPaused && currentId !== -1"
                        class="flex items-center justify-center rounded-full bg-opacity-40 aspect-square p-3 xs:p-4 drop-shadow-lg text-transparent"
                    >
                        <ProiconsPlay :class="`xs:w-8 xs:h-8 [&>*]:!stroke-1`" />
                    </div>
                </Transition>
            </section>
            <section class="w-fit h-fit absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2" style="z-index: 4">
                <Transition
                    enter-active-class="transition ease-out duration-1000 bg-black text-white"
                    enter-from-class="scale-50 opacity-100 !text-white"
                    enter-to-class="scale-100 opacity-0 !text-white"
                    v-cloak
                >
                    <div v-show="!isPaused" class="flex items-center justify-center rounded-full bg-opacity-40 aspect-square p-3 xs:p-4 drop-shadow-lg text-transparent">
                        <svg class="w-4 h-4 xs:w-8 xs:h-8" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                fill-rule="evenodd"
                                clip-rule="evenodd"
                                d="M8 3C8.55228 3 9 3.44772 9 4L9 20C9 20.5523 8.55228 21 8 21C7.44772 21 7 20.5523 7 20L7 4C7 3.44772 7.44772 3 8 3ZM16 3C16.5523 3 17 3.44772 17 4V20C17 20.5523 16.5523 21 16 21C15.4477 21 15 20.5523 15 20V4C15 3.44772 15.4477 3 16 3Z"
                                fill="currentColor"
                            ></path>
                        </svg>
                    </div>
                </Transition>
            </section>
        </section>
        <video
            id="vid-source"
            width="100%"
            type="video/mp4"
            ref="player"
            style="z-index: 3"
            :src="stateVideo?.path ? `../${stateVideo?.path}` : ''"
            :class="
                `relative focus:outline-none object-contain h-full rounded-xl overflow-clip` +
                `${!stateVideo?.path ? ' aspect-video' : (isAudio || isPortrait) && !isFullScreen ? ` max-h-[60vh]` : ''}` +
                `${isAudio ? '' : ' bg-black'}`
            "
            :poster="isAudio ? audioPoster : ''"
            @click="handlePlayerToggle"
            @ended="onPlayerEnded"
            @loadstart="onPlayerLoadStart"
            @loadeddata="onPlayerLoadeddata"
            @waiting="onPlayerWaiting"
            @timeupdate="handlePlayerTimeUpdate"
            controlsList="nodownload"
        >
            <track kind="captions" />
        </video>
        <div
            v-if="isAudio"
            id="audio-poster"
            class="absolute top-0 left-0 w-full h-full blur cursor-pointer flex items-center justify-center"
            :style="`background: transparent url('${audioPoster}') 50% 50% / cover no-repeat`"
        ></div>
    </div>
</template>

<style scoped>
.slider {
    --thumb-size: 2;
    --thumb-rounded: 9999px; /* rounded-full */
    --track-color: rgba(255, 255, 255, 0.3); /* white with 30% opacity */
    --track-rounded: 9999px; /* rounded-full */
    --progress-color: #111827; /* gray-900 */
}

.slider.timeline {
    --thumb-size: 1;
}

.slider.timeline:hover {
    --thumb-size: 2;
}

.slider.volume {
    --thumb-color: #ffffff;
    --progress-color: #9333eaca;
}

.group:hover .show-fade {
    animation: fadeOut 1s forwards;
    animation-delay: 7s;
}
@keyframes fadeOut {
    0% {
        opacity: 0.65;
    }
    100% {
        opacity: 0;
    }
}

/* WebKit (Chrome, Safari) */
.slider::-webkit-slider-thumb {
    transition: all 200ms ease-in-out;
    appearance: none;
    border: 0;
    background: var(--thumb-color);
    border-radius: var(--thumb-rounded);
    width: calc(var(--thumb-size) * 0.25rem);
    height: calc(var(--thumb-size) * 0.25rem);
    box-shadow: -995px 0 0 992px var(--progress-color);
}

.slider::-webkit-slider-runnable-track {
    background: var(--track-color);
    border-radius: var(--track-rounded);
    overflow: hidden;
}

.slider::-webkit-slider-runnable-track {
    background: var(--track-color);
    border-radius: var(--track-rounded);
    overflow: hidden;
}

/* Firefox */
.slider::-moz-range-thumb {
    transition: all 200ms ease-in-out;
    appearance: none;
    border: 0;
    background: var(--thumb-color);
    border-radius: var(--thumb-rounded);
    width: calc(var(--thumb-size) * 0.25rem);
    height: calc(var(--thumb-size) * 0.25rem);
}

.slider::-moz-range-track {
    transition: all 200ms ease-in-out;
    background: var(--track-color);
    border-radius: var(--track-rounded);
    height: calc(var(--thumb-size) * 0.25rem);
}

.slider::-moz-range-progress {
    transition: all 200ms ease-in-out;
    background: var(--progress-color);
    border-radius: var(--track-rounded);
    height: calc(var(--thumb-size) * 0.25rem);
}

.slider.timeline:hover::-moz-range-track,
.slider.timeline:hover::-moz-range-progress {
    border-radius: 1px;
}
.slider.timeline:hover::-webkit-slider-runnable-track {
    border-radius: 1px;
}
</style>
