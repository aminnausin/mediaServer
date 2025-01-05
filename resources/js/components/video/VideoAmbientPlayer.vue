<script setup lang="ts">
import { onMounted, onUnmounted, ref, useTemplateRef, watch } from 'vue';
import { useAppStore } from '@/stores/AppStore';
import { storeToRefs } from 'pinia';

import VideoPlayer from '@/components/video/VideoPlayer.vue';

const { lightMode, ambientMode } = storeToRefs(useAppStore());

const container = ref<null | HTMLElement>(null);
const player = ref<null | HTMLVideoElement>(null);
const audioPoster = ref<null | HTMLDivElement>(null);
const step = ref<undefined | number>(undefined);
const canvas = ref<null | HTMLCanvasElement>(null);
const ctx = ref<null | CanvasRenderingContext2D>(null);

const videoPlayer = useTemplateRef('video-player');
const isAudio = ref(false);

const draw = () => {
    if (!ctx.value || !player.value || !canvas.value) return;
    ctx.value.drawImage(player.value, 0, 0, canvas.value.width, canvas.value.height);
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
    if (!step.value) return;

    window.cancelAnimationFrame(step.value);
    step.value = undefined;
};

const adjustOverlayDiv = () => {
    if (container.value && player.value && canvas.value) {
        const parentWidth = container.value.offsetWidth;
        const parentHeight = container.value.offsetHeight;
        canvas.value.style.width = `${parentWidth - 16}px`;
        canvas.value.style.height = `${parentHeight - 16}px`;
        canvas.value.style.top = '8px';
        canvas.value.style.left = '8px';
    }
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
});

watch(
    () => ambientMode.value,
    () => {
        if (!ambientMode.value) {
            drawPause();
        }
    },
);

watch([videoPlayer, () => videoPlayer?.value?.isAudio], () => {
    if (!videoPlayer.value) return;
    isAudio.value = videoPlayer.value?.isAudio ?? false;
});

watch(player, (newVal) => {
    if (newVal) {
        newVal.addEventListener('loadedmetadata', adjustOverlayDiv);
    }
});
</script>

<template>
    <section class="w-full h-fit relative" ref="container">
        <!-- <span
            v-show="!lightMode && ambientMode"
            class="snap-y absolute -left-[1%] -top-[1%] gap-6 pt-2 flex w-[102%] h-[102%] z-10 rounded-2xl px-6 bg-rose-400 pointer-events-none"
            ref="canvasContainer"
        > -->
        <canvas
            v-cloak
            v-show="!lightMode && ambientMode && !isAudio"
            width="10"
            height="6"
            aria-hidden="true"
            class="absolute z-10 opacity-100 blur-lg pointer-events-none"
            ref="canvas"
        >
            Canvas
        </canvas>
        <img v-show="isAudio" class="absolute z-10 opacity-100 blur pointer-events-none w-full h-full" :src="videoPlayer?.audioPoster ?? ''" />
        <!-- </span> -->
        <VideoPlayer
            ref="video-player"
            class="z-10 w-full"
            @loadedData="draw"
            @seeked="draw"
            @play="drawLoop"
            @pause="drawPause"
            @ended="drawPause"
            @loadedmetadata="adjustOverlayDiv"
        />
    </section>
</template>
