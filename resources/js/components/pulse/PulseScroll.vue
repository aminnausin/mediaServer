<script setup lang="ts">
import { nextTick, onMounted, ref } from 'vue';

const props = withDefaults(defineProps<{ expand?: boolean; loading: boolean }>(), { expand: false });
const content = ref<HTMLDivElement>();
const fade = ref<HTMLDivElement>();

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
    <div :class="`flex w-full grow overflow-hidden ${props.expand ? '' : 'basis-56'} ${loading && 'animate-pulse opacity-25'}`">
        <div
            ref="content"
            @scroll.passive="scroll"
            class="scrollbar:w-1.5 scrollbar:h-1.5 scrollbar:bg-transparent scrollbar-thumb:rounded scrollbar-track:rounded supports-scrollbars scrollbar-minimal grow basis-full overflow-y-auto"
        >
            <slot></slot>
            <div ref="fade" class="pointer-events-none fixed right-0 bottom-0 left-0 h-6 origin-bottom bg-linear-to-t from-white dark:from-gray-900" wire:ignore></div>
        </div>
    </div>
</template>
