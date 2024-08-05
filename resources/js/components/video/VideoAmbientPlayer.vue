<script setup>
import VideoPlayer from './VideoPlayer.vue';

import { storeToRefs } from 'pinia';
import { useAppStore } from '../../stores/AppStore';
import { onMounted, onUnmounted, ref, watch } from 'vue';

const player = ref(null)
const step = ref(undefined);
const canvas = ref(null);
const ctx = ref(null);

const appStore = useAppStore();
const { lightMode, ambientMode } = storeToRefs(appStore);

const draw = () => {
    ctx.value.drawImage(player?.value, 0, 0, canvas.value?.width, canvas.value?.height)
}

const drawLoop = () => {
    if(!player.value || lightMode.value || !canvas.value || !ambientMode.value){
        drawPause();
        return;
    }

    draw();
    step.value = window.requestAnimationFrame(drawLoop);
};

const drawPause = () => {
    if(!step.value) return

    window.cancelAnimationFrame(step.value);
    step.value = undefined;
};

onMounted(() => {
    ctx.value = canvas.value?.getContext("2d")
    player.value = document.getElementById('vid-source');
})

onUnmounted(() => {
    drawPause();
})

watch(() => ambientMode.value, () => {
    if (!ambientMode.value) {
        drawPause();
    }
})
</script>

<template>
    <span v-show="!lightMode && ambientMode" class="flex flex-wrap lg:flex-nowrap snap-y w-full absolute left-0 gap-6 -mt-9 pt-2">
        <section class="w-full lg:w-1/6 lg:max-w-72 sm:min-w-32 shrink-0 hidden lg:block h-0">
        </section>
        <section class="flex w-full z-10 rounded-2xl px-6 aspect-video">
            <canvas width="10" height="6" aria-hidden="true"
                class="w-full opacity-100 h-full p-8 blur-lg" ref="canvas">
                Canvas
            </canvas>
        </section>
        <section class="w-full lg:w-1/6 lg:max-w-72 sm:min-w-32 shrink-0 hidden lg:block h-0">
        </section>
    </span>
    <VideoPlayer class="z-10"
        @loadedData="draw"
        @seeked="draw"
        @play="drawLoop"
        @pause="drawPause"
        @ended="drawPause"
    />
</template>