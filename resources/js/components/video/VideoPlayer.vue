<script setup lang="ts">
import type { FolderResource, VideoResource } from '@/types/resources';
import type { ContextMenuItem, PopoverItem } from '@/types/types';
import type { ComputedRef, Ref } from 'vue';

import { controlsHideTime, playbackDataBuffer, playerHealthBuffer, volumeDelta, playbackDelta, playbackMin, playbackMax } from '@/service/player/playerConstants';
import { getScreenSize, handleStorageURL, isInputLikeElement, isMobileDevice, toFormattedDate, toFormattedDuration } from '@/service/util';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, useTemplateRef, watch } from 'vue';
import { copyVideoFrame, saveVideoFrame } from '@/service/player/frameService';
import { useRoute, useRouter } from 'vue-router';
import { UseCreatePlayback } from '@/service/mutations';
import { useVideoPlayback } from '@/service/queries';
import { useContentStore } from '@/stores/ContentStore';
import { debounce, round } from 'lodash';
import { useAuthStore } from '@/stores/AuthStore';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { getMediaUrl } from '@/service/api';
import { MediaType } from '@/types/types';
import { onSeek } from '@/service/player/seekBus';
import { toast } from '@/service/toaster/toastService';

import VideoPopoverSlider from '@/components/video/VideoPopoverSlider.vue';
import VideoPopoverItem from '@/components/video/VideoPopoverItem.vue';
import ToastController from '@/components/pinesUI/ToastController.vue';
import VideoPartyPanel from '@/components/video/VideoPartyPanel.vue';
import VideoTimeline from '@/components/video/VideoTimeline.vue';
import ButtonCorner from '@/components/inputs/ButtonCorner.vue';
import VideoHeatmap from '@/components/video/VideoHeatmap.vue';
import VideoPopover from '@/components/video/VideoPopover.vue';
import VideoButton from '@/components/video/VideoButton.vue';
import VideoSlider from '@/components/video/VideoSlider.vue';
import VideoLyrics from '@/components/video/VideoLyrics.vue';
import ContextMenu from '@/components/pinesUI/ContextMenu.vue';

import ProiconsPictureInPictureEnter from '~icons/proicons/picture-in-picture-enter';
import ProiconsFullScreenMaximize from '~icons/proicons/full-screen-maximize';
import ProiconsFullScreenMinimize from '~icons/proicons/full-screen-minimize';
import ProiconsArrowTrending from '~icons/proicons/arrow-trending';
import TablerMicrophone2Off from '~icons/tabler/microphone-2-off';
import ProiconsFastForward from '~icons/proicons/fast-forward';
import ProiconsVolumeMute from '~icons/proicons/volume-mute';
import ProiconsVolumeLow from '~icons/proicons/volume-low';
import ProiconsCheckmark from '~icons/proicons/checkmark';
import LucideCaptionsOff from '~icons/lucide/captions-off';
import TablerMicrophone2 from '~icons/tabler/microphone-2';
import ProiconsSparkle2 from '~icons/proicons/sparkle-2';
import ProiconsSettings from '~icons/proicons/settings';
import ProiconsSpinner from '~icons/proicons/spinner';
import ProiconsReverse from '~icons/proicons/reverse';
import ProiconsVolume from '~icons/proicons/volume';
import LucideCaptions from '~icons/lucide/captions';
import ProiconsCancel from '~icons/proicons/cancel';
import ProiconsPlay from '~icons/proicons/play';
import MagePlaylist from '~icons/mage/playlist';
import CircumTimer from '~icons/circum/timer';

/**
 * Z-Index Layout (lowest on list is in front)
 *
 * 3  → Video
 *     → Audio Background Blur
 *
 * 4  → Controls Gradient
 *     → Title Gradient
 *     → Tap Controls
 *
 * 5  → Title
 *     → Lyrics / Captions
 *     → Loading Icon
 *     → Play Icon
 *     → Pause Icon
 *
 * 6  → Lyrics Top / Bottom Padding
 *       ↳ (Used to prevent overlap or accidental interaction
 *          with lyrics buttons at the top and bottom areas)
 *
 * 7  → Lyrics Preview / Edit Button
 *     → Player Controls
 *     → Timeline Buffer / Progress / Thumb / Input
 *     → Player Buttons (Play, Mute, FS, etc.)
 *     → Volume Slider
 *     → Watch Party Panel
 *     → Video Stats Panel
 *
 * 9  → Video Tooltip Slider (Timeline Tooltip)
 *
 * 10 → Video Popover (Settings, Sliders, etc.)
 *
 * 30 → Context Menu (Global overlay in fullscreen)
 */

const router = useRouter();
const route = useRoute();

let unSub: () => boolean; // Unsub from seek bus

const emit = defineEmits(['loadedData', 'seeked', 'play', 'pause', 'ended', 'loadedMetadata']);

// Global State
const { contextMenuItems, contextMenuStyle, contextMenuItemStyle, playbackHeatmap, ambientMode, lightMode, isAutoPlay, isPlaylist } = storeToRefs(useAppStore());
const { createRecord, updateViewCount } = useContentStore();
const { setContextMenu } = useAppStore();

const playerContextMenu = useTemplateRef('contextMenu');

const { userData } = storeToRefs(useAuthStore());
const { stateVideo, stateFolder, nextVideoURL, previousVideoURL } = storeToRefs(useContentStore()) as unknown as {
    stateVideo: Ref<VideoResource>;
    stateFolder: Ref<FolderResource>;
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
const timeDuration = computed(() => {
    return stateVideo.value?.metadata?.duration ?? 1;
});
const timeElapsed = ref(0); // Out of 100
const timeSeeking = ref('');
const timeAutoSeek = ref(10);
const currentVolume = ref(0.5);
const cachedVolume = ref(0.5);
const currentSpeed = ref(1);

// Player State
const latestPlayRequestId = ref<number>(0);
const controlsHideTimeout = ref<number>();
const volumeChangeTimeout = ref<number>();
const autoSeekTimeout = ref<number>();
const timeDisplay = ref<'timeElapsed' | 'timeRemaining'>('timeElapsed');

const isPictureInPicture = ref(false);
const isShowingControls = ref(false);
const isChangingVolume = ref(false);
const isShowingLyrics = ref(false);
const isShowingParty = ref(false);
const isShowingStats = ref(false);
const isMediaSession = ref(false);
const isFastForward = ref(false);
const isFullScreen = ref(false);
const isLoading = ref(false);
const isScrubbing = ref(false);
const isLooping = ref(false);
const isRewind = ref(false);
const isPaused = ref(true);
const isMuted = ref(false);

// Player Info
const endsAtTime = ref('00:00');
const bufferTime = ref<number>(0);
const bufferPercentage = ref<number>(0);
const frameHealth = ref<string>('0/0');
const playerHealthCounter = ref(0);
const bufferHealth = computed(() => {
    return toFormattedDuration(bufferTime.value, false) ?? '0s';
});
const videoButtonOffset = computed(() => {
    return 8 + (isFullScreen.value ? 8 : 0);
});
const timeStrings = computed(() => {
    const timeElapsedVerbose = toFormattedDuration((timeElapsed.value / 100) * timeDuration.value, false, 'verbose') ?? 'Unknown';
    const timeDurationVerbose = toFormattedDuration(timeDuration.value, false, 'verbose') ?? 'Unknown';
    return {
        timeElapsed: toFormattedDuration((timeElapsed.value / 100) * timeDuration.value, true, 'digital') ?? '00:00',
        timeRemaining: '-' + (toFormattedDuration((1 - timeElapsed.value / 100) * timeDuration.value, true, 'digital') ?? '00:00'),
        timeDuration: toFormattedDuration(timeDuration.value, true, 'digital') ?? '00:00',
        timeVerbose: `${timeElapsedVerbose} out of ${timeDurationVerbose}`,
        timeElapsedVerbose,
    };
});
const keyBinds = computed(() => {
    let keys = {
        mute: ` (m)`,
        previous: ' (shift+p)',
        play: ' (k)',
        next: ' (shift+n)',
        fullscreen: ' (f)',
        lyrics: ' (c)',
    };

    if (isMobileDevice()) {
        keys = {
            mute: ``,
            previous: '',
            play: '',
            next: '',
            fullscreen: '',
            lyrics: '',
        };
    }

    return {
        mute: `${isMuted.value ? 'Unmute' : 'Mute'}${keys.mute}`,
        previous: `Play Previous${keys.previous}`,
        play: `${isPaused.value ? 'Play' : 'Pause'}${keys.play}`,
        next: `Play Next${keys.next}`,
        fullscreen: `${isFullScreen.value ? 'Exit Full Screen' : 'Full Screen'}${keys.fullscreen}`,
        lyrics: `${isShowingLyrics.value ? 'Disable' : 'Enable'} ${isAudio.value || stateFolder.value.is_majority_audio ? 'Lyrics' : 'Captions'}${keys.lyrics}`,
    };
});

// Elements
const player = useTemplateRef('player');
const popover = useTemplateRef('popover');
const container = useTemplateRef('video-container');
const timeline = useTemplateRef('video-timeline');
const progressTooltip = timeline.value?.progressTooltip;
// const url = ref('');

const playerContextMenuItems = computed(() => {
    const items: ContextMenuItem[] = [
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
            disabled: !userData.value?.id,
            action: () => {
                isShowingParty.value = !isShowingParty.value;
            },
        },
        {
            text: 'Save Frame',
            hidden: isAudio.value,
            action: () => {
                if (!player.value) return;
                saveVideoFrame(player.value);
            },
        },
        {
            text: 'Copy Frame',
            hidden: isAudio.value,
            action: () => {
                if (!player.value) return;
                copyVideoFrame(player.value);
            },
        },
    ];
    return items;
});

const videoPopoverItems = computed(() => {
    const items: PopoverItem[] = [
        {
            text: 'Ambient Mode',
            title: 'Toggle Ambient Mode',
            icon: ProiconsSparkle2,
            selectedIcon: ProiconsCheckmark,
            selected: ambientMode.value ?? false,
            selectedIconStyle: 'text-purple-600',
            disabled: lightMode.value ?? false,
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
        {
            text: 'Captions',
            title: `Toggle Captions`,
            icon: isShowingLyrics.value ? LucideCaptions : LucideCaptionsOff,
            selectedIcon: ProiconsCheckmark,
            selected: isShowingLyrics.value ?? false,
            selectedIconStyle: 'text-purple-600 stroke-none',
            disabled: getScreenSize() !== 'default' || isAudio.value || stateFolder.value.is_majority_audio,
            action: () => {
                isShowingLyrics.value = !isShowingLyrics.value;
            },
        },
        {
            text: 'Lyrics',
            title: `Toggle Lyrics`,
            icon: isShowingLyrics.value ? TablerMicrophone2 : TablerMicrophone2Off,
            iconStyle: `[&>*]:stroke-[1.4px]`,
            selectedIcon: ProiconsCheckmark,
            selected: isShowingLyrics.value ?? false,
            selectedIconStyle: 'text-purple-600 stroke-none',
            disabled: getScreenSize() !== 'default' || (!isAudio.value && !stateFolder.value.is_majority_audio),
            action: () => {
                isShowingLyrics.value = !isShowingLyrics.value;
            },
        },
        {
            text: 'Autoplay',
            title: 'Toggle Autoplay',
            icon: MagePlaylist,
            selectedIcon: ProiconsCheckmark,
            selected: isPlaylist.value ?? false,
            selectedIconStyle: 'text-purple-600',
            action: () => {
                if (isLoading.value) return;
                isPlaylist.value = !isPlaylist.value;
                isAutoPlay.value = isPlaylist.value;
            },
        },
        {
            text: 'Miniplayer',
            title: 'Toggle Picture-in-picture',
            icon: ProiconsPictureInPictureEnter,
            selectedIcon: ProiconsCheckmark,
            selected: isPictureInPicture.value ?? false,
            selectedIconStyle: 'text-purple-600',
            disabled: !document.pictureInPictureEnabled || isAudio.value,
            action: () => {
                if (isLoading.value) return;
                isPictureInPicture.value = !isPictureInPicture.value;
            },
        },
    ];
    return items;
});

// Computed Player State

const isAudio = computed(() => {
    return stateVideo.value.metadata?.media_type === MediaType.AUDIO;
});

const aspectRatio = computed(() => {
    if (!stateVideo.value.metadata?.resolution_width || !stateVideo.value.metadata?.resolution_height) return { isPortrait: false, isAspectVideo: false };

    return {
        isPortrait: stateVideo.value.metadata.resolution_width < stateVideo.value.metadata.resolution_height,
        isAspectVideo: stateVideo.value.metadata.resolution_width / stateVideo.value.metadata.resolution_height == 16.0 / 9.0,
    };
});

const audioPoster = computed(() => {
    return handleStorageURL(stateVideo.value?.metadata?.poster_url) ?? handleStorageURL(stateFolder.value.series?.thumbnail_url) ?? '/storage/thumbnails/default.webp';
});

const initVideoPlayer = async () => {
    if (stateVideo.value.id === currentId.value) return;

    const root = document.getElementById('root');

    isLooping.value = false;
    isPictureInPicture.value = false;
    currentSpeed.value = 1;
    currentId.value = -1;
    bufferPercentage.value = 0;
    bufferTime.value = 0;

    timeElapsed.value = 0; // HOTFIX: I do not know why this wasn't here already but if a video is changed before the previous one loaded, the time is not reset
    if (!root) return;

    root.scrollIntoView();

    if (progressCache.value && progressCache.value.length > 0) {
        createPlayback({ entries: progressCache.value });
        progressCache.value = [];
    }

    metadataId.value = stateVideo.value?.metadata ? stateVideo.value?.metadata.id : NaN;

    handleInitMediaSession();

    if (!isFullScreen.value && !isAutoPlay.value) {
        onPlayerPause();
        debouncedEndTime(); // Generate end time on video change
        return;
    }

    await nextTick();
    onPlayerPlay();
    // url.value = await getMediaUrl(stateVideo.value.path ?? '');
};

const handleInitMediaSession = () => {
    if (isMediaSession.value && !isNaN(metadataId.value)) {
        const artworkURL =
            handleStorageURL(stateVideo.value.metadata?.poster_url) ||
            handleStorageURL(stateFolder.value.series?.thumbnail_url) ||
            new URL('/storage/thumbnails/default.webp', window.location.origin).href;

        const studioName = stateVideo.value.metadata?.artist ?? stateFolder.value?.series?.studio;
        const folderName = stateVideo.value.metadata?.album ?? stateFolder.value.series?.title ?? stateFolder.value.name;
        const artist = `${studioName ? studioName + ' · ' : ''}${folderName}`; //OLD CODE: (studioName ? `${studioName} · ${folderName}` : null) || (isAudio.value ? folderName : null);

        const newMediaSession = new MediaMetadata({
            title: stateVideo.value.metadata?.title || stateVideo.value.name,
            artist: artist || 'Unknown Artist', // Unknown artist should never happen with this logic
            album: folderName || 'Unknown Album',
            artwork: [
                { src: artworkURL, sizes: '128x128', type: 'image/webp' },
                { src: artworkURL, sizes: '256x256', type: 'image/webp' },
                { src: artworkURL, sizes: '512x512', type: 'image/webp' },
            ],
        });
        navigator.mediaSession.metadata = newMediaSession;
    } else {
        navigator.mediaSession.metadata = null;
    }
};

//#region Player Events

const handleProgress = (override = false) => {
    if (!player.value || !stateVideo.value.metadata?.id) return;

    const progress = player.value.currentTime / player.value.duration;

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
const onPlayerPlay = async (override = false, recordProgress = true) => {
    if (!player.value || !stateVideo.value.id) return;

    /* if (isLoading.value) {
        const description = stateVideo.value.metadata?.codec ? ` Make sure your browser supports the format "${stateVideo.value.metadata.codec}"` : '';
        toast.warning(`Content still loading...`, {
            description,
        });
        onPlayerPause();
        return;
    } */

    const playRequestId = ++latestPlayRequestId.value;
    try {
        isAutoPlay.value = false;
        isLoading.value = true;
        await player.value.play();

        if (playRequestId !== latestPlayRequestId.value) return;

        isLoading.value = false;
        // Set isPaused to false?
        emit('loadedData');
        emit('play');

        resetControlsTimeout();

        if (!recordProgress) return;

        if (currentId.value === stateVideo.value.id && !override) {
            handleProgress();
            return; // stop recording every time video seek
        }

        currentId.value = stateVideo.value.id;
        createRecord(stateVideo.value.id);
        updateViewCount(stateVideo.value.id);
        handleProgress(true);
        getEndTime();
        if (isMediaSession.value) navigator.mediaSession.playbackState = 'playing';
    } catch (error) {
        if ((error instanceof DOMException && error.name === 'AbortError') || playRequestId !== latestPlayRequestId.value) {
            return;
        }

        toast.error('Error playing content...', { description: `${error ?? ''}` });
        isLoading.value = false;
        console.error(error);
    }
};

const onPlayerPause = () => {
    if (!player.value) return;

    latestPlayRequestId.value++;
    player.value.pause();

    if (!isPaused.value) isPaused.value = true;
    emit('pause');

    if (isMediaSession.value) navigator.mediaSession.playbackState = 'paused';
};

const onPlayerEnded = () => {
    currentId.value = -1;
    if (isLooping.value) {
        onPlayerPlay();
        return;
    }

    emit('ended');

    if (isPlaylist.value) {
        handleNext(true);
        return;
    }

    onPlayerPause();
};

const onPlayerLoadStart = () => {
    if (isLoading.value || !stateVideo.value?.path) return;
    isLoading.value = true;
};

const onPlayerLoadeddata = () => {
    if (!stateVideo.value || !player.value) return;
    if (stateVideo.value.metadata && !stateVideo.value.metadata.duration && !isNaN(player.value.duration)) {
        stateVideo.value.metadata.duration = player.value.duration ?? 0;
        timeElapsed.value = 0;
    }

    handleLoadUrlTime();

    isLoading.value = false;
    emit('loadedData');
    emit('loadedMetadata');
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

const debouncedCacheVolume = debounce(cacheVolume, 300);

const handleVolumeChange = (dir: number = 0) => {
    if (!player.value) return;

    if (dir) {
        currentVolume.value = round(Math.max(Math.min(parseFloat(`${currentVolume.value}`) + volumeDelta * dir, 1), 0), 2);
    }

    player.value.volume = currentVolume.value;

    if (currentVolume.value === 0) isMuted.value = true;
    else isMuted.value = false;
    debouncedCacheVolume();
    return true;
};

const handleVolumeWheel = (event: WheelEvent) => {
    if (!player.value) return;
    event.preventDefault();
    isChangingVolume.value = false;

    if (!handleVolumeChange(event.deltaY < 0 ? 1 : -1)) return;

    if (volumeChangeTimeout.value) clearTimeout(volumeChangeTimeout.value);
    volumeChangeTimeout.value = window.setTimeout(() => {
        isChangingVolume.value = true;
    }, 100);
};

const handleSpeedChange = (event: Event, dir: number = 0) => {
    if (!player.value) return;

    if (dir) {
        currentSpeed.value = round(Math.max(Math.min(parseFloat(`${currentSpeed.value}`) + playbackDelta * dir, playbackMax), playbackMin), 2);
    }

    player.value.playbackRate = currentSpeed.value;
    return true;
};

const handleSpeedWheel = (event: WheelEvent) => {
    if (!player.value) return;
    event.preventDefault();

    if (!handleSpeedChange(new Event('SpeedChange'), event.deltaY < 0 ? 1 : -1)) return;
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

function handleAutoSeek(seconds: number) {
    if (!player.value) return;
    isFastForward.value = false;
    isRewind.value = false;

    let newTimeElapsed = player.value.currentTime + seconds;

    newTimeElapsed = Math.max(newTimeElapsed, 0);
    newTimeElapsed = Math.min(newTimeElapsed, timeDuration.value);

    player.value.currentTime = newTimeElapsed;
    timeElapsed.value = (newTimeElapsed / timeDuration.value) * 100;

    if (!isPaused.value) onPlayerPlay(false, false);

    if (autoSeekTimeout.value) clearTimeout(autoSeekTimeout.value);
    autoSeekTimeout.value = window.setTimeout(() => {
        timeAutoSeek.value = seconds;
        if (seconds > 0) isFastForward.value = true;
        else isRewind.value = true;
    }, 100);
}

const handleLyrics = () => {
    isShowingLyrics.value = !isShowingLyrics.value;
};

const handleFullScreen = async () => {
    if (!container.value) return;
    try {
        if (!isFullScreen.value || document.fullscreenElement === null) {
            await container.value.requestFullscreen();
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

const handleFullScreenChange = (e: Event) => {
    isFullScreen.value = document.fullscreenElement !== null;
};

const handlePlayerTimeUpdate = (event: any) => {
    // update time if not seeking, or paused
    if (isScrubbing.value || isLoading.value) return;
    getBufferHealth();

    // if playing or have not started playing yet, force seek (I do not remember what this is for)
    if (!isPaused.value || (currentId.value === -1 && timeElapsed.value)) {
        timeElapsed.value = (event.target.currentTime / timeDuration.value) * 100;
    }
};

// Causes performance degredation and is unecessary on supported platforms
const handlePositionState = () => {
    if (!player.value || !isMediaSession.value || !('setPositionState' in navigator.mediaSession)) return;

    const data = {
        duration: timeDuration.value,
        playbackRate: player.value.playbackRate,
        position: Math.min(player.value.currentTime, timeDuration.value),
    };

    navigator.mediaSession.setPositionState(data);
};

const handleManualSeek = async (seconds: number) => {
    if (!player.value) return;

    seconds = Math.min(Math.max(seconds, 0), timeDuration.value);

    timeElapsed.value = (seconds / timeDuration.value) * 100;

    handleSeek(seconds);
};

const handleSeek = (seconds?: number) => {
    if (!player.value || timeElapsed.value < 0 || timeElapsed.value > 100) return;

    isScrubbing.value = false;
    isLoading.value = true;
    player.value.currentTime = seconds ?? (timeElapsed.value / 100) * timeDuration.value;
};

const onSeeked = () => {
    debouncedEndTime();
    emit('seeked');
    getPlayerInfo();
    if (isPaused.value) {
        isLoading.value = false;
        return;
    }
    onPlayerPlay();
};

const handleSeekPreview = () => {
    if (!player.value || isScrubbing.value) return;
    if (!isScrubbing.value) isScrubbing.value = true;
};

function resetControlsTimeout() {
    isShowingControls.value = true;

    clearTimeout(controlsHideTimeout.value);
    controlsHideTimeout.value = window.setTimeout(handleControlsTimeout, controlsHideTime);
}

function handleControlsTimeout() {
    if (isPaused.value) return;
    if (controlsHideTimeout.value) clearTimeout(controlsHideTimeout.value);

    isShowingControls.value = false;
    popover.value?.handleClose();
    progressTooltip?.tooltipToggle(false);
}

const debouncedEndTime = debounce(getEndTime, 100);

function playerMouseActivity() {
    if (!isPaused.value) {
        resetControlsTimeout();
        return;
    }

    debouncedEndTime();

    if (!controlsHideTimeout.value && isShowingControls.value) return;

    isShowingControls.value = true;
    clearTimeout(controlsHideTimeout.value);
}

function getEndTime() {
    const timeDelta = (timeDuration.value - (timeElapsed.value / 100) * timeDuration.value) * 1000;
    if (!player.value || !stateVideo.value?.id || isNaN(timeDelta)) {
        return;
    }
    endsAtTime.value = toFormattedDate(new Date(new Date().getTime() + timeDelta), true, {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true,
    });
}

function getBufferHealth() {
    playerHealthCounter.value += 1;

    if (playerHealthCounter.value >= playerHealthBuffer) {
        getPlayerInfo();
        playerHealthCounter.value = 0;
    }
}

function getPlayerInfo() {
    if (!player.value) return;
    const playbackQuality = player.value.getVideoPlaybackQuality();

    const buffered = player.value.buffered;
    const currentTime = player.value.currentTime;

    let bufferedSeconds = 0;

    for (let i = 0; i < buffered.length; i++) {
        if (buffered.start(i) <= currentTime && currentTime <= buffered.end(i)) {
            bufferedSeconds = buffered.end(i) - currentTime;
            break;
        }
    }

    bufferTime.value = bufferedSeconds;
    bufferPercentage.value = (bufferedSeconds / timeDuration.value) * 100 + timeElapsed.value;
    frameHealth.value = `${playbackQuality.droppedVideoFrames} / ${playbackQuality.totalVideoFrames}`;
}

const handleLoadSavedVolume = () => {
    const savedVolume = parseFloat(localStorage.getItem('videoVolume') ?? '');
    if (isNaN(savedVolume) || !player.value) {
        handleVolumeChange();
        return;
    }

    const normalVolume = Math.max(0, Math.min(savedVolume, 1));
    currentVolume.value = normalVolume;
    player.value.volume = normalVolume;

    if (normalVolume === 0) isMuted.value = true;
};

const handleLoadUrlTime = async () => {
    if (!route.query.t) return;
    const seconds = parseInt(route.query.t.toString());
    handleManualSeek(seconds);
};

const handleNext = (useAutoPlay = isAudio.value) => {
    if (!nextVideoURL.value) {
        toast('Reached end of playlist');
        return;
    }
    isAutoPlay.value = useAutoPlay;
    router.push(nextVideoURL.value);
};

const handlePrevious = (useAutoPlay = isAudio.value) => {
    if (!previousVideoURL.value) return;
    isAutoPlay.value = useAutoPlay;
    router.push(previousVideoURL.value);
};

const handleKeyBinds = (event: KeyboardEvent, override = false) => {
    const keyBinds = ['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'l', 'N', 'j', 'k', 'm', 'c', ' ', 'f', 'MediaTrackNext', 'MediaTrackPrevious', 'MediaPlayPause'];

    if (!keyBinds.includes(event.key)) return;
    if (isInputLikeElement(event.target, event.key) && !override) return;

    switch (event.key) {
        case 'ArrowLeft':
        case 'j':
            handleAutoSeek(event.shiftKey ? -5 : -10);
            break;
        case 'ArrowRight':
        case 'l':
            handleAutoSeek(event.shiftKey ? 5 : 10);
            break;
        case 'N':
            if (!event.shiftKey) return;
            handleNext(false);
            break;
        case 'm':
            handleMute();
            break;
        case 'c':
            handleLyrics();
            break;
        case 'k':
        case ' ':
        case 'MediaPlayPause':
            event.preventDefault();
            handlePlayerToggle();
            break;
        case 'f':
            handleFullScreen();
            break;
        case 'ArrowUp':
            event.preventDefault();
            handleVolumeWheel(new WheelEvent('wheel', { deltaY: -1 }));
            break;
        case 'ArrowDown':
            event.preventDefault();
            handleVolumeWheel(new WheelEvent('wheel', { deltaY: 1 }));
            break;
        case 'MediaTrackNext':
            event.preventDefault();
            handleNext();
            break;
        case 'MediaTrackPrevious':
            event.preventDefault();
            handlePrevious();
            break;
        default:
            break;
    }
};

const handleMediaSessionEvents = () => {
    if (!('mediaSession' in navigator)) {
        console.warn('Media Session API is not supported in this browser.');
        return;
    }
    isMediaSession.value = true;
    navigator.mediaSession.setActionHandler('play', () => {
        onPlayerPlay();
    });

    navigator.mediaSession.setActionHandler('pause', () => {
        onPlayerPause();
    });

    navigator.mediaSession.setActionHandler('seekbackward', () => {
        handleAutoSeek(-10);
    });

    navigator.mediaSession.setActionHandler('seekforward', () => {
        handleAutoSeek(10);
    });

    navigator.mediaSession.setActionHandler('previoustrack', () => {
        handlePrevious();
    });

    navigator.mediaSession.setActionHandler('nexttrack', () => {
        handleNext();
    });

    navigator.mediaSession.setActionHandler('seekto', (details: MediaSessionActionDetails) => {
        if (!details.seekTime) return;
        handleManualSeek(details.seekTime);
    });
};

// Toggles PIP mode when triggered via native browser buttons. Needs to prevent default because the water for the PIP state manually sets PIP mode.
const enterPictureInPicture = (e: Event) => {
    e.preventDefault();
    isPictureInPicture.value = true;
};

// Does not need to prevent default because the watcher exits PIP mode conditionally
const leavePictureInPicture = (e: Event) => {
    isPictureInPicture.value = false;
};

const stopScrub = () => {
    isScrubbing.value = false;
};

watch(isPictureInPicture, async (value) => {
    if (!player.value || isLoading.value) return;

    try {
        await (value ? player.value.requestPictureInPicture() : document.exitPictureInPicture());

        popover.value?.handleClose();
    } catch (error) {
        console.error(error);
        toast.error('Unable to toggle miniplayer');
    }
});

watch(stateVideo, initVideoPlayer);

onMounted(() => {
    if (document.pictureInPictureElement) document.exitPictureInPicture();
    handleLoadSavedVolume();
    handleMediaSessionEvents();
    window.addEventListener('keydown', handleKeyBinds);
    document.addEventListener('fullscreenchange', handleFullScreenChange);
    window.addEventListener('pointerup', stopScrub);
    window.addEventListener('contextmenu', stopScrub);
    unSub = onSeek(handleManualSeek);
});

onBeforeUnmount(() => {
    window.removeEventListener('pointerup', stopScrub);
    window.removeEventListener('contextmenu', stopScrub);
    window.removeEventListener('keydown', handleKeyBinds);
    document.removeEventListener('fullscreenchange', handleFullScreenChange);

    if (unSub) unSub();

    debouncedCacheVolume.cancel();
});

defineExpose({
    isAudio,
    isPictureInPicture,
    audioPoster,
});
</script>

<template>
    <div
        :class="[`relative overflow-clip rounded`, { 'rounded-xl': !isFullScreen }]"
        ref="video-container"
        id="video-container"
        @mousemove="playerMouseActivity"
        @touchmove="playerMouseActivity"
        @mouseleave="handleControlsTimeout"
        @contextmenu="
            (e: any) => {
                // if (isFullScreen) return;
                // This does not work in fullscreen because the video container requests fullscreen but the context menu is higher in the document tree.
                // TODO: change the video page to be widescreen like on youtube when fullscreen
                setContextMenu(e, { items: playerContextMenuItems, style: 'w-32', itemStyle: 'text-xs' });
                playerContextMenu?.contextMenuToggle(e, true);
            }
        "
    >
        <video
            id="video-source"
            width="100%"
            type="video/mp4"
            ref="player"
            style="z-index: 3"
            preload="metadata"
            :class="[
                `relative h-full select-none object-contain focus:outline-none`,
                `${!stateVideo?.path ? 'aspect-video' : (isAudio || aspectRatio.isPortrait) && !isFullScreen ? `max-h-[71vh]` : 'aspect-video'}`,
                { 'bg-black': !isAudio && !aspectRatio.isAspectVideo },
                `${isShowingControls ? 'cursor-auto' : 'cursor-none'}`,
            ]"
            :src="stateVideo?.path ? `../${stateVideo?.path}` : ''"
            :poster="isAudio ? audioPoster : (handleStorageURL(stateVideo.metadata?.poster_url) ?? '')"
            @play="isPaused = false"
            @pause="isPaused = true"
            @ended="onPlayerEnded"
            @loadstart="onPlayerLoadStart"
            @loadedmetadata="onPlayerLoadeddata"
            @seeked="onSeeked"
            @waiting="onPlayerWaiting"
            @timeupdate="handlePlayerTimeUpdate"
            @click="handlePlayerToggle"
            @enterpictureinpicture="enterPictureInPicture"
            @leavepictureinpicture="leavePictureInPicture"
            aria-describedby="Play/Pause"
            controlsList="nodownload"
        >
            <track kind="captions" />
            Your browser does not support the video tag.
        </video>
        <section
            style="z-index: 4"
            :class="`player-controls pointer-events-none font-mono text-xs text-white ${isShowingControls ? 'cursor-auto' : 'cursor-none'}`"
            id="player-controls"
        >
            <!-- Video Stats (Z-7) -->
            <section class="pointer-events-auto absolute left-0 top-0 p-1 sm:p-4" v-show="isShowingStats" style="z-index: 7">
                <div class="flex w-fit gap-2 rounded-md border border-slate-700/20 bg-neutral-900/80 p-2 sm:min-w-52">
                    <span class="text-right [&>*]:line-clamp-1 [&>*]:break-all">
                        <p title="Dropped Frames vs Total Frames" v-if="!isAudio">Dropped Frames:</p>
                        <p title="Video Buffer Health">Buffer Health:</p>
                        <p title="Video Resolution" v-if="!isAudio">Resolution:</p>
                        <p title="Video Framerate" v-if="stateVideo.metadata?.frame_rate">Framerate:</p>
                        <p title="Video Codec" v-if="stateVideo.metadata?.codec">Codec:</p>
                    </span>
                    <span class="w-full flex-1 [&>*]:line-clamp-1">
                        <p v-if="!isAudio">{{ frameHealth }}</p>
                        <p>{{ bufferHealth }}</p>
                        <p v-if="!isAudio">{{ player?.videoWidth }}x{{ player?.videoHeight }}</p>
                        <p v-if="stateVideo.metadata?.frame_rate">{{ stateVideo.metadata.frame_rate }}</p>
                        <p v-if="stateVideo.metadata?.codec">{{ stateVideo.metadata.codec }}</p>
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

            <!-- Watch Party (Z-7) -->
            <section class="pointer-events-auto absolute right-0 top-0 p-1 sm:p-4" v-show="isShowingParty" style="z-index: 7">
                <VideoPartyPanel :player="player ?? undefined" />
            </section>

            <!-- Controls (Z-7) -->
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
                    v-show="isShowingControls"
                    :class="`absolute bottom-0 left-0 w-full ${isFullScreen ? 'p-2' : ''}h-12 !pointer-events-none flex flex-col justify-end bg-gradient-to-b from-neutral-900/0 to-neutral-900/30`"
                    style="z-index: 7"
                >
                    <!-- Heatmap and Timeline -->
                    <VideoTimeline
                        ref="video-timeline"
                        :buffer-percentage="bufferPercentage"
                        :time-duration="timeDuration"
                        :time-elapsed-verbose="timeStrings.timeElapsedVerbose"
                        :video-button-offset="videoButtonOffset"
                        v-model="timeElapsed"
                        @seek="handleSeek"
                        @seek-preview="handleSeekPreview"
                        @key-bind="handleKeyBinds"
                    >
                        <VideoHeatmap :playback-data="playbackData" />
                    </VideoTimeline>

                    <!-- Controls -->
                    <section class="pointer-events-auto flex w-full items-center gap-2 p-2">
                        <section class="flex items-center gap-1">
                            <VideoButton
                                v-if="previousVideoURL && isAudio"
                                class="hidden xs:block"
                                :title="keyBinds.previous"
                                :icon="ProiconsReverse"
                                :link="previousVideoURL"
                                :use-tooltip="true"
                                :target-element="player ?? undefined"
                                :controls="isShowingControls"
                                :offset="videoButtonOffset"
                            />
                            <VideoButton
                                @click="handlePlayerToggle"
                                :title="keyBinds.play"
                                :use-tooltip="true"
                                :target-element="player ?? undefined"
                                :controls="isShowingControls"
                                :offset="videoButtonOffset"
                            >
                                <template #icon>
                                    <ProiconsPlay v-if="isPaused" class="h-4 w-4" />
                                    <svg v-else class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
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

                            <VideoButton
                                v-if="nextVideoURL"
                                class="hidden xs:block"
                                :title="keyBinds.next"
                                :icon="ProiconsFastForward"
                                :link="nextVideoURL"
                                :use-tooltip="true"
                                :target-element="player ?? undefined"
                                :controls="isShowingControls"
                                :offset="videoButtonOffset"
                            />
                        </section>

                        <section
                            class="0 line-clamp-1 hidden gap-1 opacity-80 hover:opacity-100 sm:flex"
                            :title="`The ${isAudio ? 'audio' : 'video'} will finish at ${endsAtTime}`"
                            v-show="endsAtTime !== '00:00' && endsAtTime !== ''"
                        >
                            <p class="truncate">Ends at</p>
                            <time class="text-nowrap">{{ endsAtTime }}</time>
                        </section>
                        <section
                            class="ml-auto line-clamp-1 hidden overflow-clip opacity-80 hover:opacity-100 xs:flex"
                            :title="timeStrings.timeVerbose"
                            @click="timeDisplay = timeDisplay === 'timeElapsed' ? 'timeRemaining' : 'timeElapsed'"
                        >
                            <time>{{ timeStrings[timeDisplay] }}</time>
                            <span> / </span>
                            <time>{{ timeStrings.timeDuration }}</time>
                        </section>

                        <section class="group ml-auto flex h-full items-center xs:ml-0">
                            <VideoButton
                                :title="keyBinds.mute"
                                class="opacity-80 duration-150 ease-out hover:text-white hover:opacity-100"
                                @click="handleMute"
                                :use-tooltip="true"
                                :target-element="player ?? undefined"
                                :controls="isShowingControls"
                                :offset="videoButtonOffset"
                            >
                                <template #icon>
                                    <ProiconsVolume v-if="currentVolume > 0.3" class="h-4 w-4" />
                                    <ProiconsVolumeLow v-else-if="currentVolume > 0" class="h-4 w-4" />
                                    <ProiconsVolumeMute v-else class="h-4 w-4" />
                                </template>
                            </VideoButton>
                            <VideoSlider
                                v-model="currentVolume"
                                :text="`Volume: ${Math.round(currentVolume * 100)}%`"
                                :action="() => handleVolumeChange()"
                                :wheel-action="handleVolumeWheel"
                            />
                        </section>
                        <VideoButton
                            class="hidden xs:block"
                            @click="handleLyrics()"
                            :title="keyBinds.lyrics"
                            :use-tooltip="true"
                            :target-element="player ?? undefined"
                            :controls="isShowingControls"
                            :offset="videoButtonOffset"
                        >
                            <template #icon v-if="isAudio || stateFolder.is_majority_audio">
                                <TablerMicrophone2 v-if="isShowingLyrics" class="h-4 w-4 [&>*]:stroke-[1.4px]" />
                                <TablerMicrophone2Off v-else class="h-4 w-4 [&>*]:stroke-[1.4px]" />
                            </template>
                            <template #icon v-else>
                                <LucideCaptions v-if="isShowingLyrics" class="h-4 w-4" />
                                <LucideCaptionsOff v-else class="h-4 w-4" />
                            </template>
                        </VideoButton>
                        <VideoPopover
                            popoverClass="!max-w-40 rounded-lg h-32 md:h-fit "
                            ref="popover"
                            :margin="80"
                            :player="player ?? undefined"
                            :button-attributes="{ 'use-tooltip': true, 'target-element': player ?? undefined, offset: videoButtonOffset }"
                        >
                            <template #buttonIcon>
                                <ProiconsSettings class="h-4 w-4 transition-transform duration-500 ease-in-out hover:rotate-180" />
                            </template>
                            <template #content>
                                <section class="scrollbar-minimal flex h-28 flex-col overflow-y-auto transition-transform md:h-fit">
                                    <VideoPopoverItem v-for="(item, index) in videoPopoverItems" :key="index" v-bind="item" />
                                    <VideoPopoverSlider
                                        v-model="currentSpeed"
                                        :text="`Speed`"
                                        :shortcut="`${Math.round(currentSpeed * 100)}%`"
                                        :icon="CircumTimer"
                                        :min="playbackMin"
                                        :max="playbackMax"
                                        :step="playbackDelta"
                                        :action="handleSpeedChange"
                                        :wheel-action="handleSpeedWheel"
                                        :title="'Change Playback Speed'"
                                    />
                                    <VideoPopoverSlider
                                        v-if="false"
                                        v-model="currentVolume"
                                        :hidden="true"
                                        :text="`Volume`"
                                        :shortcut="`${Math.round(currentVolume * 100)}%`"
                                        :icon="currentVolume > 0.3 ? ProiconsVolume : currentVolume > 0 ? ProiconsVolumeLow : ProiconsVolumeMute"
                                        :min="0"
                                        :max="1"
                                        :step="0.05"
                                        :action="() => handleVolumeChange()"
                                        :wheel-action="handleVolumeWheel"
                                        :title="'Change Volume'"
                                    />
                                </section>
                            </template>
                        </VideoPopover>
                        <VideoButton
                            @click="handleFullScreen"
                            :title="keyBinds.fullscreen"
                            :use-tooltip="true"
                            :target-element="player ?? undefined"
                            :controls="isShowingControls"
                            :offset="videoButtonOffset"
                        >
                            <template #icon>
                                <ProiconsFullScreenMinimize v-if="isFullScreen" class="h-4 w-4" />
                                <ProiconsFullScreenMaximize v-else class="h-4 w-4" />
                            </template>
                        </VideoButton>
                    </section>
                </div>
            </Transition>

            <!-- Title (Z-5) -->
            <section v-show="isShowingControls && isFullScreen" :class="`absolute left-0 top-0 flex h-fit w-fit flex-col p-2 px-4 text-xl drop-shadow-md`" style="z-index: 5">
                <h2 class="line-clamp-1">{{ stateVideo.title }}</h2>
            </section>

            <!-- Lyrics / Captions (Z-5) -->
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="translate-y-full opacity-0"
                enter-to-class="translate-y-0 opacity-100"
                leave-active-class="transition ease-in duration-300"
                leave-from-class="translate-y-0 opacity-100"
                leave-to-class="translate-y-full opacity-0"
            >
                <div :class="`absolute top-0 flex h-full w-full opacity-0 transition-all`" style="z-index: 5" v-show="isShowingLyrics">
                    <VideoLyrics
                        v-if="isAudio || stateFolder.is_majority_audio"
                        @seek="handleManualSeek"
                        :raw-lyrics="stateVideo?.metadata?.lyrics ?? ''"
                        :time-duration="timeDuration"
                        :time-elapsed="timeElapsed"
                        :is-paused="isPaused"
                        :is-fullscreen="isFullScreen"
                    />
                </div>
            </Transition>

            <!-- Loading (Z-5) -->
            <section v-show="isLoading" class="absolute left-1/2 top-1/2 h-fit w-fit -translate-x-1/2 -translate-y-1/2" style="z-index: 5">
                <ProiconsSpinner class="h-8 w-8 animate-spin" />
            </section>

            <!-- Play Icon (Z-5) -->
            <section class="absolute left-1/2 top-1/2 h-fit w-fit -translate-x-1/2 -translate-y-1/2" style="z-index: 5">
                <Transition
                    enter-active-class="transition ease-out duration-1000 bg-black text-white"
                    enter-from-class="scale-50 opacity-100 !text-white"
                    enter-to-class="scale-100 opacity-100 !text-white"
                    v-cloak
                >
                    <div
                        v-show="isPaused && currentId !== -1"
                        class="flex aspect-square items-center justify-center rounded-full bg-opacity-40 p-3 text-transparent drop-shadow-lg xs:p-4"
                    >
                        <ProiconsPlay :class="`xs:h-8 xs:w-8 [&>*]:!stroke-1`" />
                    </div>
                </Transition>
            </section>

            <!-- Pause Icon (Z-5) -->
            <section class="absolute left-1/2 top-1/2 h-fit w-fit -translate-x-1/2 -translate-y-1/2" style="z-index: 5">
                <Transition
                    enter-active-class="transition ease-out duration-1000 bg-black text-white"
                    enter-from-class="scale-50 opacity-100 !text-white"
                    enter-to-class="scale-100 opacity-0 !text-white"
                    v-cloak
                >
                    <div v-show="!isPaused" class="flex aspect-square items-center justify-center rounded-full bg-opacity-40 p-3 text-transparent drop-shadow-lg xs:p-4">
                        <svg class="h-4 w-4 xs:h-8 xs:w-8" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
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

            <!-- Controls Gradient (Z-4) -->
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="translate-y-full"
                enter-to-class="translate-y-0"
                leave-active-class="transition ease-in duration-300"
                leave-from-class="translate-y-0"
                leave-to-class="translate-y-full"
            >
                <div
                    v-show="isShowingControls"
                    style="z-index: 4"
                    :class="`absolute bottom-0 left-0 h-32 w-full bg-gradient-to-b from-transparent to-black opacity-20`"
                    v-cloak
                ></div>
            </Transition>

            <!-- Title Gradient (Z-4) -->
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="-translate-y-full"
                enter-to-class="translate-y-0"
                leave-active-class="transition ease-in duration-300"
                leave-from-class="translate-y-0"
                leave-to-class="-translate-y-full"
            >
                <div
                    v-show="isShowingControls && isFullScreen"
                    style="z-index: 4"
                    :class="`absolute left-0 top-0 h-16 w-full bg-gradient-to-b from-black to-transparent opacity-40`"
                    v-cloak
                ></div>
            </Transition>

            <!-- Tap Controls (Z-4) -->
            <section :class="`select-none pointer-events-auto${isShowingControls ? 'cursor-auto' : 'cursor-none'}`" style="z-index: 4">
                <span
                    :class="`absolute ${isFullScreen ? 'w-1/4' : 'w-1/3 sm:w-1/4'} left-0 top-0 flex h-full flex-col items-center justify-center gap-1`"
                    style="z-index: 4"
                    aria-describedby="Skip Backward"
                    @dblclick="() => handleAutoSeek(-10)"
                >
                    <Transition
                        enter-active-class="transition ease-out duration-1000 bg-black text-white"
                        enter-from-class="scale-50 opacity-100 !text-white"
                        enter-to-class="scale-100 opacity-0 !text-white"
                        v-cloak
                    >
                        <div v-show="isRewind" class="flex aspect-square items-center justify-center rounded-full bg-opacity-40 p-2 text-transparent drop-shadow-lg">
                            <ProiconsReverse class="h-6 w-6" />
                        </div>
                    </Transition>
                    <Transition
                        enter-active-class="transition ease-out duration-[1.2s] text-white bg-neutral-900/30"
                        enter-from-class="scale-50 opacity-100 !text-white"
                        enter-to-class="scale-100 opacity-0 !text-white"
                        v-cloak
                    >
                        <p v-show="isRewind" class="pointer-events-none select-none rounded-full p-1 text-transparent">{{ timeAutoSeek }}s</p>
                    </Transition>
                </span>
                <span :class="`pointer-events-none absolute top-0 flex h-full w-full flex-col items-center justify-start py-4`" style="z-index: 4">
                    <Transition
                        enter-active-class="transition ease-out duration-[1.4s] text-white bg-neutral-900/30"
                        enter-from-class="scale-50 opacity-100 !text-white"
                        enter-to-class="scale-100 opacity-0 !text-white"
                        v-cloak
                    >
                        <p v-show="isChangingVolume" class="pointer-events-none select-none rounded-full px-2 py-1 text-transparent">{{ Math.round(currentVolume * 100) }}%</p>
                    </Transition>
                    <span class="pointer-events-auto absolute bottom-0 h-1/6 w-full"></span>
                </span>
                <span
                    :class="`absolute ${isFullScreen ? 'w-1/4' : 'w-1/3 sm:w-1/4'} right-0 top-0 flex h-full flex-col items-center justify-center`"
                    aria-describedby="Skip Forward"
                    @dblclick="() => handleAutoSeek(10)"
                    style="z-index: 4"
                >
                    <Transition
                        enter-active-class="transition ease-out duration-1000 bg-black text-white"
                        enter-from-class="scale-50 opacity-100 !text-white"
                        enter-to-class="scale-100 opacity-0 !text-white"
                        v-cloak
                    >
                        <div v-show="isFastForward" class="flex aspect-square items-center justify-center rounded-full bg-opacity-40 p-2 text-transparent drop-shadow-lg">
                            <ProiconsFastForward class="h-4 w-6" />
                        </div>
                    </Transition>
                    <Transition
                        enter-active-class="transition ease-out duration-[1.2s] text-white bg-neutral-900/30"
                        enter-from-class="scale-50 opacity-100 !text-white"
                        enter-to-class="scale-100 opacity-0 !text-white"
                        v-cloak
                    >
                        <p v-show="isFastForward" class="pointer-events-none select-none rounded-full p-1 text-transparent">+{{ timeAutoSeek }}s</p>
                    </Transition>
                </span>
            </section>

            <!-- Lyrics Background Blur (Z-3) -->
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition ease-in duration-300"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
                ><div
                    :class="`absolute top-0 h-full w-full bg-neutral-950/10 backdrop-blur-lg transition-all`"
                    style="z-index: 3"
                    v-show="(isAudio || stateFolder.is_majority_audio) && isShowingLyrics"
                ></div>
            </Transition>
        </section>
        <!-- Is a blurred copy of the thumbnail or poster as a backdrop to the clear poster -->
        <div
            v-if="isAudio"
            id="audio-poster"
            class="absolute left-0 top-0 flex h-full w-full cursor-pointer items-center justify-center blur"
            :style="`background: transparent url('${audioPoster}') 50% 50% / cover no-repeat;`"
        ></div>
        <div class="absolute left-0 top-0 h-full w-full" v-show="isFullScreen">
            <ToastController :teleport-disabled="true" :position="'bottom-left'" />
            <ContextMenu
                ref="contextMenu"
                :items="contextMenuItems"
                :style="contextMenuStyle"
                :itemStyle="contextMenuItemStyle ?? 'hover:bg-purple-600 hover:text-white'"
                scrollContainer="window"
                teleport-disabled
            />
        </div>
    </div>
</template>

<style scoped>
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
</style>
