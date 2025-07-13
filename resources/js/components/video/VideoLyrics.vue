<script setup lang="ts">
import type { VideoResource } from '@/types/resources';
import type { LyricItem } from '@/types/types';
import type { Ref } from 'vue';

import { computed, onMounted, onUnmounted, ref, useTemplateRef, watch, nextTick } from 'vue';
import { toFormattedDuration } from '@/service/util';
import { useContentStore } from '@/stores/ContentStore';
import { useLyricStore } from '@/stores/LyricStore';
import { storeToRefs } from 'pinia';
import { onSeek } from '@/service/video/seekBus';

import VideoLyricItem from '@/components/video/VideoLyricItem.vue';
import EditLyrics from '@/components/forms/EditLyrics.vue';
import ButtonIcon from '@/components/inputs/ButtonIcon.vue';
import ModalBase from '@/components/pinesUI/ModalBase.vue';

const { stateLyrics, editLyricsModal, dirtyLyric, isLoadingLyrics } = storeToRefs(useLyricStore());
const { stateVideo } = storeToRefs(useContentStore()) as unknown as { stateVideo: Ref<VideoResource> };

const { handleGenerateLyrics, handleOpenLyricsModal } = useLyricStore();
const { updateVideoData } = useContentStore();

const emit = defineEmits<{ seek: [value: number] }>();
const props = defineProps<{ rawLyrics: string; timeElapsed: string | number; timeDuration: number; isPaused: boolean }>();

const lyrics = computed(() => {
    const availableLyrics = stateLyrics.value;
    if (!availableLyrics) return [{ text: 'No lyrics yet...' }];

    const result = availableLyrics.split('\n').map((line) => {
        const match = line.match(/\[(?:(\d+):)?(\d+):(\d+(?:\.\d+)?)](.*)/);
        if (!match) return { text: line.trim() };

        const [, hour, min, sec, text] = match;
        const seconds = parseInt(hour ?? '0') * 3600 + parseInt(min) * 60 + parseFloat(sec);
        return { text: text.trim(), time: seconds, percentage: toPercentageTime(seconds) };
    });

    return result;
});

const lyricItems = computed(() => {
    return lyrics.value.filter((line) => {
        return line.time !== undefined;
    }) as LyricItem[];
});

const activeLyricElement = ref<HTMLElement | null>(null);
const lyricsContainer = useTemplateRef<HTMLElement | null>('lyrics-container');
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

    if (!current || current.time === undefined || (current.time === activeTime.value && isActiveLyricVisible.value)) return;

    activeTime.value = current.time;

    const target = document.getElementById(`lyric-${current.time}`);
    if (!target) return;

    if (props.isPaused || (isContainerVisible.value && isActiveLyricVisible.value)) {
        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    observeLyricElement(target);
};

// Resets scroll position and active lyric / line
const resetComponent = () => {
    activeTime.value = 0;
    if (activeLyricElement.value && lyricObserver.value) {
        lyricObserver.value.unobserve(activeLyricElement.value);
    }

    activeLyricElement.value = null;

    nextTick(() => {
        lyricsContainer.value?.scrollTo({ top: 0, behavior: 'smooth' });
        if (lyrics.value?.[0]?.percentage) activeTime.value = lyrics.value[0].percentage;
    });
};

const handleLyricsUpdated = (data: VideoResource) => {
    editLyricsModal.value.toggleModal(false);
    updateVideoData(data);
};

const handleForceScroll = (seconds: number) => {
    const index = findCurrentLyric(lyricItems.value, seconds, false);
    if (index < 0) return;

    const current = lyricItems.value[index];
    const target = document.getElementById(`lyric-${current.time}`);
    if (!target) return;

    target.scrollIntoView({ behavior: 'smooth', block: 'center' });

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

    const unsubscribe = onSeek(handleForceScroll);
    onUnmounted(unsubscribe);
});

onUnmounted(() => {
    lyricObserver.value?.disconnect();
});

watch(() => props.timeElapsed, handleUpdate);
watch(() => props.isPaused, handleUpdate);

watch(() => stateVideo.value, resetComponent);
</script>
<template>
    <section class="flex flex-col h-full w-full overflow-y-scroll scrollbar-hide text-sm sm:text-xl text-center fade-mask" ref="lyrics-container" v-show="lyrics.length > 0">
        <div class="shrink-0" style="height: 45%"></div>
        <VideoLyricItem
            v-for="(lyric, index) in lyrics"
            :key="index"
            v-show="lyric.time || lyric.text.trim().length != 0"
            :lyric="lyric"
            :index="index"
            :is-active="lyric.time === activeTime"
            :title="lyric.time ? toFormattedDuration(lyric.time) : ''"
            @clicked="lyric.time !== undefined ? handleClick(`lyric-${lyric.time}`, lyric.time) : null"
        />
        <VideoLyricItem
            v-if="lyrics.length === 1 && lyrics[0].text === 'No lyrics yet...'"
            :lyric="{ text: `${isLoadingLyrics ? 'Generating' : 'Generate with Magic'}...` }"
            :is-active="false"
            :index="0"
            :class="{ '!opacity-60': isLoadingLyrics }"
            @clicked="handleGenerateLyrics"
        />
        <div class="shrink-0" style="height: 45%"></div>
    </section>
    <div class="absolute top-0 left-0 right-0 h-12 pointer-events-auto" style="z-index: 6"></div>
    <div class="absolute bottom-0 left-0 right-0 h-16 pointer-events-auto" style="z-index: 6"></div>
    <div class="absolute top-4 right-4 flex gap-1" style="z-index: 7">
        <ButtonIcon
            variant="ghost"
            :class="`${dirtyLyric ? 'rounded-full opacity-90' : 'opacity-70 rounded-md bg-transparent'} px-2 pointer-events-auto hover:opacity-100 hover:text-yellow-500 hover:bg-neutral-900/30 bg-neutral-900/10 transition p-1`"
            @click="handleOpenLyricsModal"
            title="Edit Lyrics"
        >
            <template #text
                ><p class="h-4">{{ dirtyLyric ? 'preview' : 'edit' }}</p></template
            >
        </ButtonIcon>
    </div>
    <ModalBase :modalData="editLyricsModal" :useControls="false">
        <template #content>
            <EditLyrics :video="stateVideo" @handleFinish="handleLyricsUpdated" />
        </template>
    </ModalBase>
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
