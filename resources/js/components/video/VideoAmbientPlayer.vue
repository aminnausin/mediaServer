<script setup>
import VideoPlayer from './VideoPlayer.vue';

import { storeToRefs } from 'pinia';
import { useAppStore } from '../../stores/AppStore';
import { onMounted, onUnmounted, ref, watch } from 'vue';

const container = ref(null);
// const canvasContainer = ref(null);
const player = ref(null);
const step = ref(undefined);
const canvas = ref(null);
const ctx = ref(null);

const appStore = useAppStore();
const { lightMode, ambientMode } = storeToRefs(appStore);

const draw = () => {
    ctx.value.drawImage(player?.value, 0, 0, canvas.value?.width, canvas.value?.height);
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
    ctx.value = canvas.value?.getContext('2d');
    player.value = document.getElementById('vid-source');
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
            class="snap-y absolute -left-[1%] -top-[1%] gap-6 pt-2 flex w-[102%] h-[102%] z-10 rounded-2xl px-6 bg-red-400 pointer-events-none"
            ref="canvasContainer"
        > -->
        <canvas
            v-show="!lightMode && ambientMode"
            width="10"
            height="6"
            aria-hidden="true"
            class="absolute z-10 opacity-100 blur-lg"
            ref="canvas"
        >
            Canvas
        </canvas>
        <!-- </span> -->
        <VideoPlayer
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
