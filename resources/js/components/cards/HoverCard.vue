<script setup lang="ts">
import { ref, watch } from 'vue';

import ProiconsCommentExclamation from '~icons/proicons/comment-exclamation';

const props = withDefaults(
    defineProps<{
        content?: string;
        positionClasses?: string;
        hoverCardDelay?: number;
        hoverCardLeaveDelay?: number;
        margin?: number;
        iconHidden?: boolean;
        paddingLeft?: number;
    }>(),
    {
        hoverCardDelay: 600,
        hoverCardLeaveDelay: 500,
        margin: 0,
        paddingLeft: 0,
    },
);

const hoverCardHovered = ref<boolean>(false);
const hoverCardTimout = ref<number | null>(null);
const hoverCardLeaveTimeout = ref<number | null>(null);
const tooltipStyles = ref<Record<string, string>>({});

const init = ref(false);

const hoverCardEnter = (event: MouseEvent) => {
    if (props.content === '') return;

    if (hoverCardLeaveTimeout.value) clearTimeout(hoverCardLeaveTimeout.value);

    if (hoverCardHovered.value) return;

    if (hoverCardTimout.value) clearTimeout(hoverCardTimout.value);

    if (!init.value) init.value = true; // Loads into Dom after hover for the first time

    hoverCardTimout.value = setTimeout(() => {
        hoverCardHovered.value = true;
        updateTooltipPosition(event);
    }, props.hoverCardDelay);
};

const hoverCardLeave = () => {
    if (hoverCardTimout.value) clearTimeout(hoverCardTimout.value);

    if (!hoverCardHovered.value) return;
    if (hoverCardLeaveTimeout.value) clearTimeout(hoverCardLeaveTimeout.value);

    hoverCardLeaveTimeout.value = setTimeout(() => {
        hoverCardHovered.value = false;
    }, props.hoverCardLeaveDelay);
};

const updateTooltipPosition = (event: MouseEvent) => {
    const rect = (event.target as HTMLElement).getBoundingClientRect();
    tooltipStyles.value = { left: `${rect.left + window.scrollX + props.paddingLeft}px`, top: `${rect.bottom + props.margin + window.scrollY}px` };
};

watch(
    () => props.content,
    () => {
        if (!props.content || props.content.trim() === '') hoverCardLeave();
    },
);
//relative
</script>

<template>
    <div class="" @mouseover="hoverCardEnter" @mouseleave="hoverCardLeave">
        <slot name="trigger">
            <a href="#_" class="hover:underline"></a>
        </slot>
        <Teleport to="body" v-if="init">
            <Transition enter-from-class="opacity-0" enter-to-class="opacity-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div
                    @mouseover="hoverCardEnter"
                    @mouseleave="hoverCardLeave"
                    v-show="hoverCardHovered"
                    v-cloak
                    :class="`${positionClasses ?? ''} z-30 flex absolute overflow-auto transition-opacity ease-in-out duration-200 md:max-w-xl xl:max-w-3xl text-sm p-3 h-fit scrollbar-minimal bg-white dark:odd:bg-primary-dark-600/70 dark:bg-neutral-800/70 backdrop-blur-lg border dark:border-none rounded-md shadow-md border-neutral-200/70 gap-2 items-center`"
                    :style="tooltipStyles"
                >
                    <slot name="icon">
                        <ProiconsCommentExclamation v-if="!iconHidden" class="h-5 w-5 mb-auto shrink-0" />
                    </slot>
                    <slot name="content">
                        <p class="text-pretty h-fit max-h-[50vh] w-full break-words whitespace-pre-wrap">
                            {{ content }}
                        </p>
                    </slot>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
