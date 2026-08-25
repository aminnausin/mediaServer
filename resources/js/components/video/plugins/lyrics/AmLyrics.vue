<script setup lang="ts">
import { nextTick, onUnmounted, ref, useTemplateRef, watch } from 'vue';
import { FLAGS } from '@/config/featureFlags';

if (FLAGS.USE_AM_LYRICS) {
    await import('@uimaxbai/am-lyrics/am-lyrics.js');
}

const props = defineProps<{
    player: HTMLVideoElement | null;
    title?: string;
    artist?: string;
    album?: string;
    duration: number;
    enabled?: boolean;
}>();

const amLyrics = useTemplateRef<HTMLElement & { currentTime: number }>('am-lyrics');
const hasMounted = ref(false);
const hasStyled = ref(false);

let amLyricsTimeUpdate: (() => void) | null = null;
let amLyricsLineClick: ((e: Event) => void) | null = null;

const initAmLyrics = async () => {
    const el = amLyrics.value;
    const audioPlayer = props.player;
    if (!el || !audioPlayer) return;

    amLyricsTimeUpdate = () => {
        el!.currentTime = audioPlayer.currentTime * 1000;
    };

    amLyricsLineClick = (e: Event) => {
        const { timestamp } = (e as CustomEvent<{ timestamp: number }>).detail;

        audioPlayer.currentTime = timestamp / 1000;
        audioPlayer.play();
    };

    audioPlayer.addEventListener('timeupdate', amLyricsTimeUpdate);
    el.addEventListener('line-click', amLyricsLineClick);

    applyStyle();
};

const destroyAmLyrics = () => {
    if (props.player && amLyricsTimeUpdate) {
        props.player.removeEventListener('timeupdate', amLyricsTimeUpdate);
    }

    if (amLyrics.value && amLyricsLineClick) {
        amLyrics.value.removeEventListener('line-click', amLyricsLineClick);
    }

    amLyricsTimeUpdate = null;
    amLyricsLineClick = null;
};

const applyStyle = () => {
    if (!amLyrics.value?.shadowRoot) return;
    const style = document.createElement('style');
    style.textContent = [
        '.download-controls {!important; align-items: start !important; height: 100% !important;}',
        '.format-select {min-height: 20px !important;}',
        '.download-button {height: 20px !important;}',
    ].join('');
    amLyrics.value.shadowRoot.appendChild(style);
    hasStyled.value = true;
};

watch(
    () => props.enabled,
    async (enabled) => {
        if (!enabled || hasMounted.value) return;

        hasMounted.value = true;
        await nextTick();
        initAmLyrics();
    },
);

onUnmounted(() => {
    destroyAmLyrics();
});
</script>

<template>
    <am-lyrics
        v-if="FLAGS.USE_AM_LYRICS && hasMounted"
        v-show="enabled"
        ref="am-lyrics"
        class="pointer-events-auto! h-full w-full text-base"
        :song-title="title"
        :song-artist="artist"
        :song-album="album"
        :song-duration="duration * 1000"
        :query="`${artist} ${album} ${title}`"
        :duration="duration * 1000"
        highlight-color="#fff"
        font-family="var(--font-klee-one-mono)"
        autoscroll
        interpolate
    >
    </am-lyrics>
</template>

<style lang="css" scoped>
am-lyrics {
    --lyplus-font-size-base: 24px;
    --am-lyrics-wide-font-size: 26px;
    --am-lyrics-compact-font-size: 20px;

    --am-lyrics-line-spacing: 20px;
    --am-lyrics-wide-line-spacing: 24px;
    --am-lyrics-compact-line-spacing: 16px;

    --am-lyrics-line-height: 1.2;
    --am-lyrics-background-vocal-spacing: 15px;
    --am-lyrics-background-vocal-font-size: 0.65em;
    --am-lyrics-background-vocal-stack-shift: 7.5px;
    --am-lyrics-background-vocal-max-height: 2em;
    --am-lyrics-background-vocal-exit-duration: 450ms;
    --am-lyrics-instrumental-height: 40px;
    --am-lyrics-instrumental-spacing: 16px;
    --am-lyrics-instrumental-enter-duration: 400ms;
    --am-lyrics-instrumental-collapse-duration: 500ms;
    --am-lyrics-instrumental-exit-duration: 350ms;
    --am-lyrics-instrumental-exit-scale: 0;
    --am-lyrics-inactive-scale: 0.98;
    --am-lyrics-background-vocal-scale: 0.9;
    --am-lyrics-touch-scale: 0.96;
    --am-lyrics-highlight-radius: 16px;
    --am-lyrics-highlight-surface: rgba(255, 255, 255, 0.08);
    --am-lyrics-progression-feather: 30px;
    --am-lyrics-glow-radius: 5px;
    --am-lyrics-character-rise-peak: -1.25px;
    --am-lyrics-inline-padding: 20px;
    --lyrics-scroll-padding-top: 12%;
    --am-lyrics-compact-background-vocal-font-size: 0.857em;
    --am-lyrics-compact-selected-position: 18%;
    --am-lyrics-wide-background-vocal-font-size: 0.667em;
    --am-lyrics-wide-selected-position: 20%;
}
</style>
