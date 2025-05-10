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
const ctx = ref<null | CanvasRenderingContext2D>(null);

const adjustTimeout = ref<null | number>(null); //timeout for resizing canvas
const drawInterval = ref<null | number>(null); //interval for fading between frames
const videoPlayer = useTemplateRef('video-player');
const isAudio = ref(false);
const isDrawing = ref(false);

const prevFrame = ref<null | ImageData>(null);
const blendStrength = ref(0.03);
const drawDelay = ref(50); //ms between each draw call for fading between frames

const draw = () => {
    if (!ctx.value || !player.value || !canvas.value) return;

    const { width, height } = canvas.value;

    ctx.value.drawImage(player.value, 0, 0, width, height);

    const newFrame = ctx.value.getImageData(0, 0, width, height);

    if (prevFrame.value) {
        const newData = newFrame.data;
        const prevData = prevFrame.value.data;

        for (let i = 0; i < newData.length; i += 4) {
            newData[i] = newData[i] * blendStrength.value + prevData[i] * (1 - blendStrength.value); // R
            newData[i + 1] = newData[i + 1] * blendStrength.value + prevData[i + 1] * (1 - blendStrength.value); // G
            newData[i + 2] = newData[i + 2] * blendStrength.value + prevData[i + 2] * (1 - blendStrength.value); // B
        }

        ctx.value.putImageData(newFrame, 0, 0);
    }

    prevFrame.value = newFrame;
};

const drawStart = () => {
    if (isDrawing.value) return;

    isDrawing.value = true;
    drawLoop();
};

const drawLoop = () => {
    drawPause();

    if (!player.value || lightMode.value || !canvas.value || !ambientMode.value) return;

    // draw();
    drawInterval.value = window.setInterval(draw, drawDelay.value);
};

const drawPause = (clearFrame: boolean = false) => {
    isDrawing.value = false;

    if (drawInterval.value !== null) {
        clearInterval(drawInterval.value);
        drawInterval.value = null;
    }

    if (clearFrame) {
        prevFrame.value = null;
    }
};

const adjustOverlayDiv = () => {
    if (!ambientMode.value || !container.value || !player.value || !canvas.value) return;

    if (isAudio.value) {
        canvas.value.style.width = '0';
        canvas.value.style.height = '0';
        return;
    }

    const { videoWidth, videoHeight, offsetWidth, offsetHeight } = player.value;

    const elementAspect = offsetWidth / offsetHeight;
    const videoAspect = videoWidth / videoHeight;
    const sizeOffset = -16;

    let width, height;

    if (elementAspect > videoAspect) {
        height = offsetHeight;
        width = height * videoAspect;
    } else {
        width = offsetWidth;
        height = width / videoAspect;
    }

    canvas.value.style = `width: ${width + sizeOffset}px !important; height: ${height + sizeOffset}px; !important`;

    prevFrame.value = null;
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

    if (canvas.value) ctx.value = canvas.value.getContext('2d', { willReadFrequently: true });

    player.value = document.getElementById('vid-source') as HTMLVideoElement;
});

onUnmounted(() => {
    drawPause();
    window.removeEventListener('resize', adjustOverlayDiv);
    if (player.value) player.value.removeEventListener('loadedMetadata', adjustOverlayDiv);
});

watch(
    () => [ambientMode.value, lightMode.value, videoPlayer.value?.isPictureInPicture],
    () => {
        if (!ambientMode.value || lightMode.value || videoPlayer.value?.isPictureInPicture) {
            drawPause(videoPlayer.value?.isPictureInPicture);
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
        <span class="pointer-events-none absolute w-full h-full flex">
            <Transition enter-to-class="opacity-100" enter-from-class="opacity-0" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <canvas
                    v-cloak
                    v-show="!lightMode && ambientMode && !isAudio && !videoPlayer?.isPictureInPicture"
                    width="6"
                    :height="isAudio ? '6' : '4'"
                    aria-hidden="true"
                    tabindex="-1"
                    class="blur-xl mx-auto my-auto transition-opacity duration-700"
                    ref="canvas"
                >
                </canvas>
            </Transition>
        </span>
        <!-- This is in place of an ambient background because audio does not have video to use for the effect -->

        <Transition enter-to-class="opacity-100" enter-from-class="opacity-0" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <img
                v-show="isAudio && ambientMode"
                class="absolute transition-opacity duration-300 ease-in-out blur pointer-events-none w-full h-full object-cover"
                :src="videoPlayer?.audioPoster ?? ''"
                alt="Video Poster"
            />
        </Transition>
        <VideoPlayer ref="video-player" class="w-full" @seeked="draw" @play="drawStart" @pause="drawPause" @ended="drawPause" @loaded-metadata="onLoadedMetadata" />

        <svg class="w-0 h-0" v-if="!ambientMode && false">
            <filter id="blur-and-scale" color-interpolation-filters="sRGB" y="-50%" x="-50%" width="200%" height="200%">
                <feGaussianBlur in="SourceGraphic" stdDeviation="12" result="blurred" />
                <feComposite in="SourceGraphic" in2="blurred" operator="over" />
            </filter>
        </svg>
    </section>
</template>
<style lang="css" scoped>
.filter-blur {
    filter: url(#blur-and-scale);
}
</style>
