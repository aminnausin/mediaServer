<script setup lang="ts">
import type { VideoResource } from '@/types/resources';

import { computed, onMounted, onUnmounted, ref, useTemplateRef, watch, nextTick, type Ref } from 'vue';
import { useContentStore } from '@/stores/ContentStore';
import { storeToRefs } from 'pinia';
import { toast } from '@/service/toaster/toastService';
import { API } from '@/service/api';

interface NumericLyricLine {
    text: string;
    time: number;
    percentage: number;
}

const { stateVideo } = storeToRefs(useContentStore()) as { stateVideo: Ref<VideoResource> };

const emit = defineEmits<{ seek: [value: number] }>();
const props = defineProps<{ rawLyrics: string; timeElapsed: string | number; timeDuration: number; isPaused: boolean }>();

const numericLyrics = ref<NumericLyricLine[]>();
const lyrics = computed(() => {
    if (!props.rawLyrics) return [{ text: 'No lyrics yet...' }];

    const result = props.rawLyrics.split('\n').map((line) => {
        const match = line.match(/\[(\d+):(\d+(?:\.\d+)?)](.*)/);
        if (!match) return { text: line.trim() };

        const [, min, sec, text] = match;
        const seconds = parseInt(min) * 60 + parseFloat(sec);
        return { text: text.trim(), time: seconds, percentage: toPercentageTime(seconds) };
    });

    numericLyrics.value = result.filter((line) => {
        return line.time !== undefined;
    }) as NumericLyricLine[];

    return result;
});

const lyricsContainer = useTemplateRef<HTMLElement | null>('lyrics-container');
const $activeLyric = ref<HTMLElement | null>(null);
const lyricObserver = ref<IntersectionObserver>();

const activeTime = ref(0);
const isActiveLyricVisible = ref(false);
const isContainerVisible = ref(false);

const toPercentageTime = (seconds: number): number => {
    seconds = Math.min(Math.max(seconds, 0), props.timeDuration);

    return (seconds / props.timeDuration) * 100;
};

const handleClick = (id: string, seconds: number) => {
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    if (!isNaN(seconds)) emit('seek', seconds);
};

function findCurrentLyric(lyrics: NumericLyricLine[], currentTime: number): number {
    let low = 0,
        high = lyrics.length - 1;
    let resultIndex = -1;

    while (low <= high) {
        const mid = Math.floor((low + high) / 2);
        if (lyrics[mid].percentage <= currentTime) {
            resultIndex = mid;
            low = mid + 1;
        } else {
            high = mid - 1;
        }
    }

    return resultIndex;
}

const handleUpdate = () => {
    /**
     * Find index ...
     * Find Lyric ...
     * Find element ...
     * If last lyric is in view and container is in view, then scroll to new lyric
     * Wait for render
     * Unobserve last lyric
     * Set observer on new lyric
     */

    const currentTime = typeof props.timeElapsed === 'number' ? props.timeElapsed : parseFloat(props.timeElapsed);
    if (isNaN(currentTime) || !numericLyrics.value) return;

    const index = findCurrentLyric(numericLyrics.value, currentTime);
    if (index < 0) return;

    const current = numericLyrics.value[index];
    if (!current || !current.time || (current.time === activeTime.value && isActiveLyricVisible.value)) return;

    activeTime.value = current.time;

    const target = document.getElementById(`lyric-${current.time}`);
    if (!target) return;

    if (props.isPaused || (isContainerVisible.value && isActiveLyricVisible.value)) {
        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    nextTick(() => {
        if (!lyricObserver.value) return;

        if ($activeLyric.value) lyricObserver.value.unobserve($activeLyric.value);

        if (!target) return;
        $activeLyric.value = target;
        lyricObserver.value?.observe($activeLyric.value);
    });
};

const handleGenerateLyrics = async () => {
    try {
        const { data } = await API.get(
            `/metadata/${stateVideo.value.metadata?.id}/import/lyrics?artist_name=Borislav+Slavov&track_name=I+Want+to+Live&album_name=Baldur%27s+Gate+3+(Original+Game+Soundtrack)&duration=233`,
        );
        console.log(data);
        if (data.lrclib?.syncedLyrics && stateVideo.value?.metadata) {
            stateVideo.value.metadata.lyrics = data.lrclib.syncedLyrics;
        } else {
            toast.error('Lyrics not found...');
        }
    } catch (error: any) {
        toast.error(`${error}`);
    }
};

const resetComponent = () => {
    activeTime.value = 0;
    if ($activeLyric.value && lyricObserver.value) {
        lyricObserver.value.unobserve($activeLyric.value);
    }

    $activeLyric.value = null;
    nextTick(() => {
        lyricsContainer.value?.scrollTo({ top: 0, behavior: 'smooth' });
        if (lyrics.value[0].percentage) activeTime.value = lyrics.value[0].percentage;
    });
};

onMounted(() => {
    if (!lyricsContainer.value) return;

    const containerObserver = new IntersectionObserver(
        ([entry]) => {
            isContainerVisible.value = entry.isIntersecting;
        },
        {
            root: null,
            threshold: 1,
        },
    );

    containerObserver.observe(lyricsContainer.value);

    resetComponent();

    lyricObserver.value = new IntersectionObserver(
        ([entry]) => {
            isActiveLyricVisible.value = entry.isIntersecting;
        },
        {
            root: lyricsContainer.value,
            threshold: 0.5,
        },
    );

    if ($activeLyric.value) {
        lyricObserver.value.observe($activeLyric.value);
    }
});

onUnmounted(() => {
    lyricObserver.value?.disconnect();
});

watch(() => props.timeElapsed, handleUpdate);
watch(() => props.isPaused, handleUpdate);

watch(() => props.rawLyrics, resetComponent);
</script>
<template>
    <section class="flex flex-col h-full w-full overflow-y-scroll scrollbar-hide text-sm sm:text-xl text-center fade-mask" ref="lyrics-container" v-show="lyrics.length > 0">
        <div class="shrink-0" style="height: 45%"></div>
        <div
            v-for="(lyric, index) in lyrics"
            :class="[
                'transition-all ease-in w-full  hover:bg-neutral-800/30',
                lyric.time !== undefined ? 'cursor-pointer' : 'cursor-default',
                lyric.time === activeTime ? 'bg-neutral-800/40 text-yellow-400 opacity-100 duration-300' : 'opacity-85',
            ]"
            :id="`lyric-${lyric?.time ?? index}`"
        >
            <button class="px-4 sm:px-0 py-1 sm:mx-auto sm:w-4/5 break-normal pointer-events-auto" @click="lyric.time ? handleClick(`lyric-${lyric.time}`, lyric.time) : null">
                {{ lyric?.text || '-' }}
            </button>
        </div>
        <div
            v-if="lyrics.length === 1 && lyrics[0].text === 'No lyrics yet...'"
            :class="['transition-all ease-in w-full  hover:bg-neutral-800/30', 'cursor-pointer', 'opacity-85']"
        >
            <button class="px-4 sm:px-0 py-1 sm:mx-auto sm:w-4/5 break-normal pointer-events-auto" @click="handleGenerateLyrics">
                {{ 'Magic Button...' }}
            </button>
        </div>
        <div class="shrink-0" style="height: 45%"></div>
    </section>
    <div class="absolute top-0 left-0 right-0 h-12 pointer-events-auto" style="z-index: 6"></div>
    <div class="absolute bottom-0 left-0 right-0 h-16 pointer-events-auto" style="z-index: 6"></div>
</template>

<style lang="css" scoped>
.fade-mask {
    /**
        opacity by height starting from top
        0%  : transparent
        10% : black (opaque)
        90% : black (opaque)
        100%: transparent
    */
    mask-image: linear-gradient(to bottom, transparent 0%, black 10%, black 90%, transparent 100%);
    -webkit-mask-image: linear-gradient(to bottom, transparent 0%, black 10%, black 90%, transparent 100%);
    mask-size: 100% 100%;
    mask-repeat: no-repeat;
    -webkit-mask-size: 100% 100%;
    -webkit-mask-repeat: no-repeat;
}
</style>
