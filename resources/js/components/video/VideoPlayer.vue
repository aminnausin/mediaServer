<!-- eslint-disable no-unused-vars -->
<script setup lang="ts">
import type { FolderResource, VideoResource } from '@/types/resources';
import type { Metadata, Series } from '@/types/model';

import { computed, onMounted, onUnmounted, ref, watch, type Ref } from 'vue';
import { UseCreatePlayback } from '@/service/mutations';
import { useVideoPlayback } from '@/service/queries';
import { useContentStore } from '@/stores/ContentStore';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';
import { getMediaUrl } from '@/service/api';

import _ from 'lodash';

const playbackDataBuffer = 5;
const defaultHeatMapData = [
    { x: 25, y: 60 },
    { x: 154, y: 48 },
    { x: 266, y: 12 },
    { x: 585, y: 18 },
    { x: 799, y: 16 },
    { x: 1000, y: 100 },
];

const ContentStore = useContentStore();
const appStore = useAppStore();
const progressCache = ref<{ metadata_id: number; progress: number }[]>([]);
const metadata_id = ref<number>(NaN);
const currentID = ref(-1);
const isAudio = ref(false);
const player = ref<null | HTMLVideoElement>(null);
// const url = ref('');

const { data: playbackData } = useVideoPlayback(metadata_id);

const { createRecord, updateViewCount } = ContentStore;
const { playbackHeatmap } = storeToRefs(appStore);
const { stateVideo, stateFolder } = storeToRefs(ContentStore) as unknown as {
    stateVideo: Ref<VideoResource | { id?: number; metadata?: Metadata; path?: string }>;
    stateFolder: Ref<FolderResource | { id?: number; series?: Series; path?: string }>;
};

const emit = defineEmits(['loadedData', 'seeked', 'play', 'pause', 'ended']);
const createPlayback = UseCreatePlayback().mutate;

const heatMap = computed(() => {
    const start = 'M 0.0,100.0 ';

    var catmullRomFitting = function (data: string | any[], alpha: number | undefined) {
        if (!data?.length || data.length < 5) return '';
        if (alpha == 0 || alpha === undefined) {
            return '';
        } else {
            var p0, p1, p2, p3, bp1, bp2, d1, d2, d3, A, B, N, M;
            var d3powA, d2powA, d3pow2A, d2pow2A, d1pow2A, d1powA;
            var d = Math.round(data[0].x) + ',' + Math.round(data[0].y) + ' ';
            var length = data.length;
            for (var i = 0; i < length - 1; i++) {
                p0 = i == 0 ? data[0] : data[i - 1];
                p1 = data[i];
                p2 = data[i + 1];
                p3 = i + 2 < length ? data[i + 2] : p2;

                d1 = Math.sqrt(Math.pow(p0.x - p1.x, 2) + Math.pow(p0.y - p1.y, 2));
                d2 = Math.sqrt(Math.pow(p1.x - p2.x, 2) + Math.pow(p1.y - p2.y, 2));
                d3 = Math.sqrt(Math.pow(p2.x - p3.x, 2) + Math.pow(p2.y - p3.y, 2));

                // Catmull-Rom to Cubic Bezier conversion matrix

                // A = 2d1^2a + 3d1^a * d2^a + d3^2a
                // B = 2d3^2a + 3d3^a * d2^a + d2^2a

                // [   0             1            0          0          ]
                // [   -d2^2a /N     A/N          d1^2a /N   0          ]
                // [   0             d3^2a /M     B/M        -d2^2a /M  ]
                // [   0             0            1          0          ]

                d3powA = Math.pow(d3, alpha);
                d3pow2A = Math.pow(d3, 2 * alpha);
                d2powA = Math.pow(d2, alpha);
                d2pow2A = Math.pow(d2, 2 * alpha);
                d1powA = Math.pow(d1, alpha);
                d1pow2A = Math.pow(d1, 2 * alpha);

                A = 2 * d1pow2A + 3 * d1powA * d2powA + d2pow2A;
                B = 2 * d3pow2A + 3 * d3powA * d2powA + d2pow2A;
                N = 3 * d1powA * (d1powA + d2powA);
                if (N > 0) {
                    N = 1 / N;
                }
                M = 3 * d3powA * (d3powA + d2powA);
                if (M > 0) {
                    M = 1 / M;
                }

                bp1 = {
                    x: (-d2pow2A * p0.x + A * p1.x + d1pow2A * p2.x) * N,
                    y: (-d2pow2A * p0.y + A * p1.y + d1pow2A * p2.y) * N,
                };

                bp2 = {
                    x: (d3pow2A * p1.x + B * p2.x - d2pow2A * p3.x) * M,
                    y: (d3pow2A * p1.y + B * p2.y - d2pow2A * p3.y) * M,
                };

                if (bp1.x == 0 && bp1.y == 0) {
                    bp1 = p1;
                }
                if (bp2.x == 0 && bp2.y == 0) {
                    bp2 = p2;
                }

                d += 'C' + bp1.x + ',' + bp1.y + ' ' + bp2.x + ',' + bp2.y + ' ' + p2.x + ',' + p2.y + ' ';
            }

            return d;
        }
    };
    return (
        start +
        catmullRomFitting(
            playbackData.value
                ? [
                      ...playbackData.value.map((entry: { progress: any; count: number }) => {
                          return { x: entry.progress, y: 100 - Math.min(entry.count, 10) * 10 };
                      }),
                      { x: 1000, y: 100 },
                  ]
                : [],
            0.5,
        )
    );
});

const initVideoPlayer = async () => {
    let root = document.getElementById('root');

    if (!root) return;

    root.scrollIntoView();

    if (progressCache.value && progressCache.value.length > 0) {
        createPlayback({ entries: progressCache.value });
        progressCache.value = [];
    }

    metadata_id.value = stateVideo.value?.metadata ? stateVideo.value?.metadata.id : NaN;
    isAudio.value = stateVideo.value.metadata?.mime_type?.startsWith('audio') ?? false;
    // url.value = await getMediaUrl(stateVideo.value.path ?? '');
};

const handlePlayVideo = (override = false) => {
    if (!stateVideo.value.id || (currentID.value === stateVideo.value.id && !override)) return; // stop recording every time video seek
    currentID.value = stateVideo.value.id;
    createRecord(stateVideo.value.id);
    updateViewCount(stateVideo.value.id);
    handleProgress(true);
};

const handleStorageURL = (url: string | undefined) => {
    if (!url) return null;

    if (window.location.protocol === 'http:' && url.startsWith(`https://${window.location.host}`)) return url.replace('https:', 'http:');

    if (window.location.protocol === 'https:' && url.startsWith(`http://${window.location.host}`)) return url.replace('http:', 'https:');
    return url;
};

const handlePlayerSeeked = () => {
    // add heatmap data ?

    emit('seeked');
};

console.log(window.location.host);

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

const onPlayerPlay = (event: any) => {
    // player.setPlaying(true);
    handlePlayVideo();
    emit('play');
    handleProgress();
};

const onPlayerPause = (event: any) => {
    // console.log(event.type);
    // player.setPlaying(false);
    emit('pause');
};

const onPlayerEnded = (event: any) => {
    // console.log(event.type);
    // player.setPlaying(false);
    emit('ended');

    // if (player.value?.loop) handlePlayVideo(true);
};

const onPlayerLoadeddata = (event: any) => {
    // console.log(event.type);
    emit('loadedData');
};

const onPlayerWaiting = (event: any) => {
    // console.log(event.type);
    if (player.value?.loop) {
        createRecord(stateVideo.value.id);
        updateViewCount(stateVideo.value.id);
        handleProgress(true);
    }
};

const onPlayerPlaying = (event: any) => {
    // console.log(event.type);
};

const onPlayerTimeupdate = (event: any) => {
    // console.log({ event: event.type, time: event.target.currentTime });
};

const onPlayerCanplay = (event: any) => {
    // console.log(event.type);
};

const onPlayerCanplaythrough = (event: any) => {
    // console.log(event.type);
};

const onPlayerSeek = (event: any) => {
    // console.log(event.type);
    handlePlayerSeeked();
};

const playerStateChanged = (event: any) => {
    // console.log(event.type);
};

const cacheVolume = () => {
    if (player.value) {
        localStorage.setItem('videoVolume', player.value.volume.toString());
    }
};

//#endregion

const debouncedCacheVolume = _.debounce(cacheVolume, 300);

const handleVolumeChange = () => {
    debouncedCacheVolume();
};

watch(stateVideo, initVideoPlayer);

onMounted(() => {
    const savedVolume = localStorage.getItem('videoVolume');
    if (savedVolume && player.value) {
        player.value.volume = parseFloat(savedVolume);
    }

    isAudio.value = stateVideo.value?.metadata?.mime_type?.startsWith('audio') ?? false;

    //     document.querySelector('video')?.addEventListener('ended', function () {
    //         console.count('loop restart');
    //         this.play();
    //     });
});

onUnmounted(() => {
    debouncedCacheVolume.cancel();
});
</script>

<template>
    <div class="relative group rounded-xl overflow-clip">
        <div
            v-if="isAudio"
            class="absolute top-0 left-0 w-full h-full blur"
            :style="`background: transparent url('${
                handleStorageURL(stateVideo?.metadata?.poster_url) ??
                handleStorageURL(stateFolder.series?.thumbnail_url) ??
                'https://m.media-amazon.com/images/M/MV5BMjVjZGU5ZTktYTZiNC00N2Q1LThiZjMtMDVmZDljN2I3ZWIwXkEyXkFqcGdeQXVyMTUzMTg2ODkz._V1_.jpg'
            }') 50% 50% / cover no-repeat`"
        ></div>
        <video
            id="vid-source"
            width="100%"
            controls
            type="video/mp4"
            ref="player"
            style="z-index: 3"
            :src="stateVideo?.path ? `../${stateVideo?.path}` : ''"
            :class="`relative focus:outline-none flex object-contain ${stateVideo?.path ? (isAudio ? 'max-h-[60vh]' : '') : 'aspect-video'}`"
            :poster="
                isAudio
                    ? (handleStorageURL(stateVideo?.metadata?.poster_url) ??
                      handleStorageURL(stateFolder.series?.thumbnail_url) ??
                      'https://m.media-amazon.com/images/M/MV5BMjVjZGU5ZTktYTZiNC00N2Q1LThiZjMtMDVmZDljN2I3ZWIwXkEyXkFqcGdeQXVyMTUzMTg2ODkz._V1_.jpg')
                    : ''
            "
            @play="onPlayerPlay"
            @pause="onPlayerPause"
            @ended="onPlayerEnded"
            @loadeddata="onPlayerLoadeddata"
            @waiting="onPlayerWaiting"
            @playing="onPlayerPlaying"
            @timeupdate="onPlayerTimeupdate"
            @canplay="onPlayerCanplay"
            @canplaythrough="onPlayerCanplaythrough"
            @statechanged="playerStateChanged"
            @seeked="onPlayerSeek"
            @volumechange="handleVolumeChange"
        >
            <track kind="captions" />
        </video>
        <section
            style="z-index: 4"
            :class="`absolute ${isAudio ? 'bottom-[52px] z-20 rounded-sm overflow-clip' : 'bottom-6'} w-[94.95%] m-auto left-0 right-0 opacity-0 group-hover:opacity-65 transition-opacity duration-75 h-5 pointer-events-none`"
            v-show="playbackHeatmap"
        >
            <svg class="ytp-heat-map-svg fill-indigo-200/20 h-full w-full" preserveAspectRatio="none" viewBox="0 0 1000 100">
                <defs>
                    <clipPath id="4">
                        <path class="ytp-heat-map-path" :d="heatMap"></path>
                    </clipPath>
                </defs>
                <rect class="ytp-heat-map-graph" clip-path="url(#4)" height="100%" width="100%" x="0" y="0"></rect>
                <rect class="ytp-heat-map-hover" clip-path="url(#4)" fill="white" fill-opacity="0.7" height="100%" width="100%" x="0" y="0"></rect>
                <rect class="ytp-heat-map-play" clip-path="url(#4)" height="100%" x="0" y="0"></rect>
            </svg>
        </section>
    </div>
</template>

<style scoped>
.group:hover .show-fade {
    animation: fadeOut 1s forwards; /* Adjust the time as needed */
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

/* video {
    object-fit: cover;
    background: transparent url('') 50% 50% / cover no-repeat;
} */
</style>
