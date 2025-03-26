<script setup lang="ts">
import { nextTick, onMounted, onUnmounted, ref, useTemplateRef, watch } from 'vue';
import { useContentStore } from '@/stores/ContentStore';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';

import VideoPlayer from '@/components/video/VideoPlayer.vue';

const { lightMode, ambientMode } = storeToRefs(useAppStore());
const { stateVideo } = storeToRefs(useContentStore());

const container = ref<null | HTMLElement>(null);
const player = ref<null | HTMLVideoElement>(null);
const canvas = ref<null | HTMLCanvasElement>(null);
const step = ref<undefined | number>(undefined);
const ctx = ref<null | CanvasRenderingContext2D>(null);

const adjustTimeout = ref<null | number>(null);
const videoPlayer = useTemplateRef('video-player');
const isAudio = ref(false);
const isDrawing = ref(false);

const draw = () => {
    if (!ctx.value || !player.value || !canvas.value) return;
    ctx.value.drawImage(player.value, 0, 0, canvas.value.width, canvas.value.height);
};

const drawStart = () => {
    if (isDrawing.value) return;

    isDrawing.value = true;
    drawLoop();
};

const drawLoop = () => {
    if (!player.value || lightMode.value || !canvas.value || !ambientMode.value) {
        drawPause();
        return;
    }

    draw();
    step.value = window.requestAnimationFrame(drawLoop);
};

const drawPause = () => {
    isDrawing.value = false;
    if (!step.value) return;

    window.cancelAnimationFrame(step.value);
    step.value = undefined;
};

const adjustOverlayDiv = () => {
    if (!ambientMode.value || !container.value || !player.value || !canvas.value) return;

    const parentWidth = container.value.offsetWidth;
    const parentHeight = container.value.offsetHeight;
    canvas.value.style.width = `${parentWidth - 16}px`;
    canvas.value.style.height = `${parentHeight - 16}px`;
    canvas.value.style.top = '8px';
    canvas.value.style.left = '8px';
};

const onLoadedMetadata = async () => {
    await nextTick();
    if (adjustTimeout.value) clearTimeout(adjustTimeout.value);

    adjustTimeout.value = setTimeout(() => {
        adjustOverlayDiv();
        draw();
    }, 100);
};

onMounted(() => {
    window.addEventListener('resize', adjustOverlayDiv);
    adjustOverlayDiv(); // Adjust initially in case video metadata is already loaded
    if (canvas.value) ctx.value = canvas.value.getContext('2d');
    player.value = document.getElementById('vid-source') as HTMLVideoElement;
});

onUnmounted(() => {
    drawPause();
    window.removeEventListener('resize', adjustOverlayDiv);
    if (player.value) player.value.removeEventListener('loadedMetadata', adjustOverlayDiv);
});

watch(
    () => ambientMode.value,
    () => {
        if (!ambientMode.value) {
            drawPause();
            return;
        }

        drawStart();
    },
);

watch([videoPlayer, () => videoPlayer?.value?.isAudio], () => {
    if (!videoPlayer.value) return;
    isAudio.value = videoPlayer.value?.isAudio ?? false;
});

watch(
    () => stateVideo.value,
    () => {
        if (!canvas.value) return;
        canvas.value.style.width = `0px`;
        canvas.value.style.height = `0px`;
    },
);
</script>

<template>
    <section class="w-full h-fit relative" ref="container">
        <canvas
            v-cloak
            v-show="!lightMode && ambientMode && !isAudio"
            width="6"
            :height="isAudio ? '6' : '4'"
            aria-hidden="true"
            class="absolute opacity-100 blur-xl pointer-events-none w-full h-full"
            ref="canvas"
        >
        </canvas>
        <!-- This is in place of an ambient background because audio does not have video to use for the effect -->
        <img
            v-show="isAudio && ambientMode"
            class="absolute opacity-100 blur pointer-events-none w-full h-full object-cover"
            :src="videoPlayer?.audioPoster ?? ''"
            alt="Video Poster"
        />
        <VideoPlayer ref="video-player" class="w-full" @seeked="draw" @play="drawStart" @pause="drawPause" @ended="drawPause" @loaded-metadata="onLoadedMetadata" />
    </section>
</template>
