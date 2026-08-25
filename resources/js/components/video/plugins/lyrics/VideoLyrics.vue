<script setup lang="ts">
import type { LyricItem } from '@/types/types';

import { computed, onMounted, onUnmounted, ref, useTemplateRef, watch, nextTick, Teleport } from 'vue';
import { useContentStore } from '@/stores/ContentStore';
import { useModalStore } from '@/stores/ModalStore';
import { useLyricStore } from '@/stores/LyricStore';
import { storeToRefs } from 'pinia';
import { onSeek } from '@/service/player/seekBus';

import PlayerToolbarButton from '@/components/video/button/PlayerToolbarButton.vue';
import VideoLyricItem from '@/components/video/plugins/lyrics/VideoLyricItem.vue';

let unsubscribe: () => boolean;

const { stateLyrics, dirtyLyric, isLoadingLyrics } = storeToRefs(useLyricStore());
const { handleGenerateLyrics, handleOpenLyricsModal } = useLyricStore();
const { stateVideo } = storeToRefs(useContentStore());

const emit = defineEmits<{ seek: [value: number] }>();
const props = defineProps<{ rawLyrics: string; player: HTMLVideoElement | null; timeDuration: number; isPaused: boolean; isShowingLyrics: boolean }>();

const activeLyricElement = ref<HTMLElement | null>(null);
const lyricsContainer = useTemplateRef<HTMLElement | null>('lyrics-container');
const lyricObserver = ref<IntersectionObserver>();

const activeTime = ref(0);
const activeIndex = ref(-1);
const isActiveLyricVisible = ref(false);
const isContainerVisible = ref(false);
const isFocusedScroll = ref(false);

const useFocusedScroll = ref(true);

const lyrics = computed(() => {
    const availableLyrics = stateLyrics.value;
    if (!availableLyrics) return [{ text: 'No lyrics yet...' }];

    const result = availableLyrics.split('\n').map((line) => {
        const match = line.match(/\[(?:(\d+):)?(\d+):(\d+(?:\.\d+)?)](.*)/);
        if (!match) return { text: line.trim() };

        const [, hour, min, sec, text] = match;
        const seconds = Number.parseInt(hour ?? '0') * 3600 + Number.parseInt(min) * 60 + Number.parseFloat(sec);
        return { text: text.trim(), time: seconds, percentage: toPercentageTime(seconds) };
    });

    return result;
});

const lyricItems = computed(() => {
    return lyrics.value.filter((line) => {
        return line.time !== undefined;
    }) as LyricItem[];
});

const toPercentageTime = (seconds: number): number => {
    seconds = Math.min(Math.max(seconds, 0), props.timeDuration);

    return (seconds / props.timeDuration) * 100;
};

const handleClick = (id: string, seconds: number) => {
    if (seconds === activeTime.value) focusScroll(document.getElementById(id));
    if (!Number.isNaN(seconds)) emit('seek', seconds);
};

function findCurrentLyric(lyrics: LyricItem[], currentTime: number, asPercentage: boolean = true): number {
    let low = 0,
        high = lyrics.length - 1;
    let resultIndex = -1;

    while (low <= high) {
        const mid = Math.floor((low + high) / 2);
        if ((asPercentage ? lyrics[mid].percentage : lyrics[mid].time) <= currentTime) {
            resultIndex = mid;
            low = mid + 1;
        } else {
            high = mid - 1;
        }
    }

    return resultIndex;
}

const scrollToCurrent = () => {
    handleUpdate(true);
};

const handleUpdateEvent = () => {
    handleUpdate();
};

const handleUpdate = async (scrollOverride: boolean = false) => {
    /**
     * Find index ...
     * Find Lyric ...
     * Find element ...
     * If last lyric is in view and container is in view, then scroll to new lyric
     * Wait for render
     * Unobserve last lyric
     * Set observer on new lyric
     */

    if (!props.player) return;

    const currentTime = (props.player.currentTime / props.timeDuration) * 100;

    if (Number.isNaN(currentTime) || !lyricItems.value) return;

    const index = findCurrentLyric(lyricItems.value, currentTime);
    if (index < 0) {
        if (activeIndex.value > 0) resetComponent();
        return;
    }

    const current = lyricItems.value[index];

    if (!current || current.time === undefined || (current.time === activeTime.value && isActiveLyricVisible.value)) return;

    activeTime.value = current.time;
    activeIndex.value = lyrics.value.findIndex((lyric) => lyric.time === current.time);

    const target = document.getElementById(`lyric-${current.time}`);
    if (!target) return;

    if (scrollOverride || props.isPaused || (isContainerVisible.value && isActiveLyricVisible.value)) focusScroll(target);

    observeLyricElement(target);
};

// Resets scroll position and active lyric / line
const resetComponent = () => {
    activeTime.value = 0;
    activeIndex.value = -1;

    if (activeLyricElement.value && lyricObserver.value) {
        lyricObserver.value.unobserve(activeLyricElement.value);
    }

    activeLyricElement.value = null;
    nextTick(() => {
        lyricsContainer.value?.scrollTo({ top: 0, behavior: 'smooth' });
        if (lyrics.value?.[0]?.percentage) {
            activeTime.value = lyrics.value[0].percentage;
        }

        if (!!lyricItems.value.at(0)?.percentage) {
            isFocusedScroll.value = true;
            activeIndex.value = 0;
        } else {
            isFocusedScroll.value = false;
        }

        const modal = useModalStore();
        modal.close();
    });
};

const handleForceScroll = (seconds: number) => {
    const index = findCurrentLyric(lyricItems.value, seconds, false);
    if (index < 0) return;

    const current = lyricItems.value[index];
    const target = document.getElementById(`lyric-${current.time}`);
    if (!target) return;

    focusScroll(target);
    observeLyricElement(target);
};

const observeLyricElement = (target: HTMLElement) => {
    nextTick(() => {
        if (!lyricObserver.value) return;

        if (activeLyricElement.value) {
            lyricObserver.value.unobserve(activeLyricElement.value);
        }

        activeLyricElement.value = target;
        lyricObserver.value.observe(target);
    });
};

const focusScroll = (target?: HTMLElement | null) => {
    if (!target) return;
    isFocusedScroll.value = true;
    target?.scrollIntoView({ behavior: 'smooth', block: 'center' });
};

const handleUserScroll = () => {
    isFocusedScroll.value = false;
};

const toggleFocusScroll = () => {
    useFocusedScroll.value = !useFocusedScroll.value;

    if (!useFocusedScroll.value) return;
    focusScroll(document.getElementById(`lyric-${activeTime.value}`));
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

    if (activeLyricElement.value) {
        lyricObserver.value.observe(activeLyricElement.value);
    }

    unsubscribe = onSeek(handleForceScroll);
});

onUnmounted(() => {
    props.player?.removeEventListener('timeupdate', handleUpdateEvent);
    lyricObserver.value?.disconnect();
    if (unsubscribe) unsubscribe();
});

watch(() => stateVideo.value, resetComponent);
watch(() => props.isPaused, handleUpdate);
watch(
    () => props.player,
    (newPlayer, oldPlayer) => {
        if (oldPlayer) {
            oldPlayer.removeEventListener('timeupdate', handleUpdateEvent);
        }
        if (newPlayer) {
            newPlayer.addEventListener('timeupdate', handleUpdateEvent);
        }
    },
    { immediate: true },
);

defineExpose({ scrollToCurrent });
</script>
<template>
    <section
        class="fade-mask scrollbar-hide flex h-full w-full flex-col overflow-y-scroll text-center text-sm sm:text-xl"
        ref="lyrics-container"
        v-show="lyrics.length > 0"
        @wheel="handleUserScroll"
        @touchmove="handleUserScroll"
    >
        <div class="shrink-0" style="height: 45%"></div>
        <VideoLyricItem
            v-for="(lyric, index) in lyrics"
            v-show="lyric.time || lyric.text.trim().length != 0"
            :key="index"
            :lyric="lyric"
            :index="index"
            :is-active="lyric.time === activeTime"
            :distance="lyrics.length === 1 && lyrics[0].text === 'No lyrics yet...' ? undefined : activeIndex < 0 ? Infinity : Math.abs(index - activeIndex)"
            :title="`${lyric.time}s`"
            :is-blur-enabled="useFocusedScroll && isFocusedScroll && activeIndex >= 0"
            @clicked="lyric.time !== undefined ? handleClick(`lyric-${lyric.time}`, lyric.time) : null"
        />
        <VideoLyricItem
            v-if="lyrics.length === 1 && lyrics[0].text === 'No lyrics yet...'"
            :lyric="{ text: `${isLoadingLyrics ? 'Generating' : 'Generate with Magic'}...` }"
            :is-active="false"
            :index="0"
            :class="[isLoadingLyrics ? '*:cursor-wait!' : 'hocus:text-yellow-500 *:cursor-pointer!']"
            @clicked="handleGenerateLyrics"
        />
        <div class="shrink-0" style="height: 45%"></div>
    </section>
    <div class="pointer-events-auto absolute top-0 right-0 left-0 h-12" style="z-index: 6"></div>
    <div class="pointer-events-auto absolute right-0 bottom-0 left-0 h-16" style="z-index: 6"></div>
    <Teleport defer to="#player-toolbar" v-if="isShowingLyrics">
        <PlayerToolbarButton @click="toggleFocusScroll" title="Focus active lyric"> {{ useFocusedScroll ? 'disable' : 'enable' }} focus </PlayerToolbarButton>
        <PlayerToolbarButton @click="handleOpenLyricsModal" title="Edit lyrics" :is-active="!!dirtyLyric">
            {{ dirtyLyric ? 'preview' : 'edit' }}
        </PlayerToolbarButton>
    </Teleport>
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
