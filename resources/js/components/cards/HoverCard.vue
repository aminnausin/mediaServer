<script setup lang="ts">
import { ref, watch } from 'vue';

import ProiconsCommentExclamation from '~icons/proicons/comment-exclamation';

const props = withDefaults(
    defineProps<{
        content?: string;
        contentTitle?: string;
        positionClasses?: string;
        hoverCardDelay?: number;
        hoverCardLeaveDelay?: number;
        margin?: number;
        iconHidden?: boolean;
        paddingLeft?: number;
        scrollContainer?: 'body' | 'window';
        disabled?: boolean;
    }>(),
    {
        hoverCardDelay: 600,
        hoverCardLeaveDelay: 500,
        margin: 0,
        paddingLeft: 0,
        scrollContainer: 'body',
        disabled: false,
    },
);

const hoverCardHovered = ref<boolean>(false);
const hoverCardTimout = ref<number | null>(null);
const hoverCardLeaveTimeout = ref<number | null>(null);
const tooltipStyles = ref<Record<string, string>>({});

const init = ref(false);

const hoverCardEnter = (event: MouseEvent) => {
    if (props.content === '' || props.disabled) return;

    if (hoverCardLeaveTimeout.value) clearTimeout(hoverCardLeaveTimeout.value);

    if (hoverCardHovered.value) return;

    if (hoverCardTimout.value) clearTimeout(hoverCardTimout.value);

    if (!init.value) init.value = true; // Loads into Dom after hover for the first time

    hoverCardTimout.value = window.setTimeout(() => {
        hoverCardHovered.value = true;
        updateTooltipPosition(event);
    }, props.hoverCardDelay);
};

const hoverCardLeave = () => {
    if (hoverCardTimout.value) clearTimeout(hoverCardTimout.value);

    if (!hoverCardHovered.value) return;
    if (hoverCardLeaveTimeout.value) clearTimeout(hoverCardLeaveTimeout.value);

    hoverCardLeaveTimeout.value = window.setTimeout(() => {
        hoverCardHovered.value = false;
    }, props.hoverCardLeaveDelay);
};

const updateTooltipPosition = (event: MouseEvent) => {
    const elem = event.target as HTMLElement;
    const parentRect = elem.parentElement?.getBoundingClientRect();

    let rect = elem.getBoundingClientRect();

    if (parentRect && parentRect.height < rect.height) {
        rect = parentRect;
    }

    const scrollY = props.scrollContainer === 'body' ? document.body.scrollTop : window.scrollY;
    const scrollX = props.scrollContainer === 'body' ? document.body.scrollLeft : window.scrollX;

    tooltipStyles.value = { left: `${rect.left + scrollX + props.paddingLeft}px`, top: `${rect.bottom + props.margin + scrollY}px` };
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
                    :class="[
                        positionClasses,
                        `z-30 flex absolute overflow-auto transition-opacity ease-in-out duration-200 md:max-w-xl xl:max-w-3xl text-sm p-3 h-fit max-h-[50vh] scrollbar-minimal bg-white dark:odd:bg-primary-dark-600/70 dark:bg-neutral-800/70 backdrop-blur-lg border dark:border-none rounded-md shadow-md border-neutral-200/70 gap-2 items-center`,
                    ]"
                    :style="tooltipStyles"
                >
                    <slot name="icon">
                        <ProiconsCommentExclamation v-if="!iconHidden" class="h-5 w-5 mb-auto shrink-0" />
                    </slot>
                    <slot name="content">
                        <div class="flex flex-col gap-2">
                            <h4 v-if="contentTitle">{{ contentTitle }}</h4>
                            <p class="dark:text-neutral-400 text-pretty whitespace-pre-wrap w-full" v-if="content">{{ content }}</p>
                        </div>
                    </slot>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
