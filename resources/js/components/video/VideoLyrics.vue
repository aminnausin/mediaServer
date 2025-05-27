<script setup lang="ts">
import type { VideoResource } from '@/types/resources';
import type { LyricItem } from '@/types/types';

import { computed, onMounted, onUnmounted, ref, useTemplateRef, watch, nextTick, type Ref } from 'vue';
import { fetchSyncedLyrics } from '@/service/metadataService';
import { useContentStore } from '@/stores/ContentStore';
import { storeToRefs } from 'pinia';
import { toast } from '@/service/toaster/toastService';

import ChipTag from '@/components/labels/ChipTag.vue';
import VideoLyricItem from './VideoLyricItem.vue';

const { stateVideo } = storeToRefs(useContentStore()) as unknown as { stateVideo: Ref<VideoResource> };

const emit = defineEmits<{ seek: [value: number] }>();
const props = defineProps<{ rawLyrics: string; timeElapsed: string | number; timeDuration: number; isPaused: boolean }>();
const localLyrics = ref<string>();

const lyricItems = ref<LyricItem[]>();
const lyrics = computed(() => {
    const availableLyrics = props.rawLyrics || localLyrics.value;
    if (!availableLyrics) return [{ text: 'No lyrics yet...' }];

    const result = availableLyrics.split('\n').map((line) => {
        const match = line.match(/\[(\d+):(\d+(?:\.\d+)?)](.*)/);
        if (!match) return { text: line.trim() };

        const [, min, sec, text] = match;
        const seconds = parseInt(min) * 60 + parseFloat(sec);
        return { text: text.trim(), time: seconds, percentage: toPercentageTime(seconds) };
    });

    lyricItems.value = result.filter((line) => {
        return line.time !== undefined;
    }) as LyricItem[];

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

function findCurrentLyric(lyrics: LyricItem[], currentTime: number): number {
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
    if (isNaN(currentTime) || !lyricItems.value) return;

    const index = findCurrentLyric(lyricItems.value, currentTime);
    if (index < 0) return;

    const current = lyricItems.value[index];
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
        if (!stateVideo.value?.metadata) {
            toast.error('Data is malformed.');
            return;
        }
        const lyrics = await fetchSyncedLyrics(stateVideo.value.metadata.id);

        if (!lyrics) {
            toast.error('Lyrics not found...');
        }

        localLyrics.value = lyrics;
        stateVideo.value.metadata.lyrics = lyrics;
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
    if (localLyrics.value !== props.rawLyrics) localLyrics.value = '';
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
        <VideoLyricItem
            v-for="(lyric, index) in lyrics"
            :lyric="lyric"
            :index="index"
            :is-active="lyric.time === activeTime"
            @clicked="lyric.time ? handleClick(`lyric-${lyric.time}`, lyric.time) : null"
        />
        <VideoLyricItem
            v-if="lyrics.length === 1 && lyrics[0].text === 'No lyrics yet...'"
            :lyric="{ text: 'Magic Button...' }"
            :is-active="false"
            :index="0"
            @clicked="handleGenerateLyrics"
        />
        <div class="shrink-0" style="height: 45%"></div>
    </section>
    <div class="absolute top-0 left-0 right-0 h-12 pointer-events-auto" style="z-index: 6"></div>
    <div class="absolute bottom-0 left-0 right-0 h-16 pointer-events-auto" style="z-index: 6"></div>
    <ChipTag v-show="localLyrics" class="absolute top-4 right-4" :text-class="'!text-gray-900'" :colour="'bg-white/70'" :label="'preview'" />
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
