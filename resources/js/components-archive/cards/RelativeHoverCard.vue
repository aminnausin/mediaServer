<script setup lang="ts">
import { ref } from 'vue';

import ProiconsCommentExclamation from '~icons/proicons/comment-exclamation';

const props = withDefaults(
    defineProps<{
        content?: string;
        positionClasses?: string;
        hoverCardDelay?: number;
        hoverCardLeaveDelay?: number;
        iconHidden?: boolean;
    }>(),
    {
        hoverCardDelay: 600,
        hoverCardLeaveDelay: 500,
    },
);

const hoverCardHovered = ref<boolean>(false);
const hoverCardTimout = ref<number | null>(null);
const hoverCardLeaveTimeout = ref<number | null>(null);
const tooltipStyles = ref<Record<string, string>>({});

const init = ref(false);

const hoverCardEnter = () => {
    if (props.content === '') return;

    if (hoverCardLeaveTimeout.value) clearTimeout(hoverCardLeaveTimeout.value);

    if (hoverCardHovered.value) return;

    if (hoverCardTimout.value) clearTimeout(hoverCardTimout.value);

    if (!init.value) init.value = true; // Loads into Dom after hover for the first time

    hoverCardTimout.value = window.setTimeout(() => {
        hoverCardHovered.value = true;
    }, props.hoverCardDelay);
};

const hoverCardLeave = () => {
    if (props.content === '') return;
    if (hoverCardTimout.value) clearTimeout(hoverCardTimout.value);

    if (!hoverCardHovered.value) return;
    if (hoverCardLeaveTimeout.value) clearTimeout(hoverCardLeaveTimeout.value);

    hoverCardLeaveTimeout.value = window.setTimeout(() => {
        hoverCardHovered.value = false;
    }, props.hoverCardLeaveDelay);
};
</script>

<template>
    <div class="relative" @mouseover="hoverCardEnter" @mouseleave="hoverCardLeave">
        <slot name="trigger">
            <a href="#_" class="hover:underline">@thedevdojo</a>
        </slot>
        <Transition enter-from-class="opacity-0" enter-to-class="opacity-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div
                @mouseover="hoverCardEnter"
                @mouseleave="hoverCardLeave"
                v-show="hoverCardHovered"
                v-cloak
                :class="`${positionClasses ?? ''} z-30 flex absolute overflow-auto transition-opacity ease-in-out duration-200 md:max-w-xl xl:max-w-3xl text-sm p-3 h-fit bg-white dark:odd:bg-primary-dark-600/70 dark:bg-neutral-800/70 backdrop-blur-lg border dark:border-none rounded-md shadow-md border-neutral-200/70 gap-2 items-center`"
                :style="tooltipStyles"
                v-if="init"
            >
                <slot name="icon">
                    <ProiconsCommentExclamation v-if="!iconHidden" class="h-5 w-5 mb-auto shrink-0" />
                </slot>
                <slot name="content">
                    <p class="text-pretty h-fit w-full wrap-break-word whitespace-pre-wrap">
                        {{ content }}
                    </p>
                </slot>
            </div>
        </Transition>
    </div>
</template>
