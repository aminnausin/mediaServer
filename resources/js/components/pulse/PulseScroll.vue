@props(['expand' => false])
<script setup lang="ts">
import { nextTick, onMounted, ref } from 'vue';

const props = withDefaults(defineProps<{ expand?: boolean; loading: boolean }>(), { expand: false });
const fade = ref<HTMLDivElement>();
const content = ref<HTMLDivElement>();

function scroll() {
    if (!fade.value) return;
    if (!content.value) return;

    const distanceToBottom = content.value.scrollHeight - (content.value.scrollTop + content.value.clientHeight);

    if (distanceToBottom >= 24) {
        fade.value.style.transform = `scaleY(1)`;
    } else {
        fade.value.style.transform = `scaleY(${distanceToBottom / 24})`;
    }
}
onMounted(async () => {
    await nextTick();

    scroll();
});
</script>
<template>
    <div :class="`flex-grow flex w-full overflow-hidden ${props.expand ? '' : ' basis-56'} ${loading && 'opacity-25 animate-pulse'}`">
        <div
            ref="content"
            @scroll="scroll"
            class="flex-grow basis-full overflow-y-auto scrollbar:w-1.5 scrollbar:h-1.5 scrollbar:bg-transparent scrollbar-track:bg-gray-100 scrollbar-thumb:rounded scrollbar-thumb:bg-gray-300 scrollbar-track:rounded dark:scrollbar-track:bg-gray-500/10 dark:scrollbar-thumb:bg-gray-500/50 supports-scrollbars"
        >
            <slot name="slot"></slot>
            <div
                ref="fade"
                class="h-6 origin-bottom fixed bottom-0 left-0 right-0 bg-gradient-to-t from-white dark:from-gray-900 pointer-events-none"
                wire:ignore
            ></div>
        </div>
    </div>
</template>
