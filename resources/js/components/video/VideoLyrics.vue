<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, useTemplateRef, watch } from 'vue';
import { nextTick } from 'vue';

const emit = defineEmits<{ (event: 'seek', value: number): void }>();
const props = defineProps<{ rawLyrics: string; timeElapsed: string | number; timeDuration: number; isPaused: boolean }>();

const lyrics = computed(() => {
    if (!props.rawLyrics) return [];

    return props.rawLyrics
        .split('\n')
        .map((line) => {
            const match = line.match(/\[(\d+):(\d+\.\d+)](.*)/);
            if (!match) return null;

            const [, min, sec, text] = match;
            const seconds = parseInt(min) * 60 + parseFloat(sec);
            return { time: seconds, text: text.trim(), percentage: toPercentageTime(seconds) };
        })
        .filter((line) => {
            return line !== null;
        });
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

function findCurrentLyric(lyrics: { percentage: number }[], currentTime: number): number {
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
    if (isNaN(currentTime)) return;

    const index = findCurrentLyric(lyrics.value, currentTime);
    if (index < 0) return;

    const current = lyrics.value[index];
    if (!current || !current.time) return;

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

const resetComponent = () => {
    activeTime.value = 0;
    $activeLyric.value = null;
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
    <section class="relative flex flex-col h-full w-full my-auto overflow-y-scroll scrollbar-hide" ref="lyrics-container" v-show="lyrics.length > 0">
        <div class="shrink-0" style="height: 45%"></div>
        <div
            v-for="(lyric, index) in lyrics"
            @click="handleClick(`lyric-${lyric?.time ?? index}`, lyric?.time ?? NaN)"
            :class="`hover:bg-neutral-800/20 ${lyric?.time === activeTime || index === activeTime ? 'bg-neutral-800/40' : ''} w-full drop-shadow-lg text-center text-lg pointer-events-auto cursor-pointer`"
            :id="`lyric-${lyric?.time ?? index}`"
            :title="`${lyric.time}`"
        >
            {{ lyric?.text || '-' }}
        </div>
        <div class="shrink-0" style="height: 45%"></div>
    </section>
</template>
