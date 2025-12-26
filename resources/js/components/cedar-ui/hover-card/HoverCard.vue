<script setup lang="ts">
import type { HoverCardProps } from '@aminnausin/cedar-ui';

import { ProiconsCommentExclamation } from '../icons';
import { ref, watch } from 'vue';
import { cn } from '@aminnausin/cedar-ui';

const props = withDefaults(defineProps<HoverCardProps>(), {
    hoverCardDelay: 600,
    hoverCardLeaveDelay: 500,
    margin: 0,
    paddingLeft: 0,
    scrollContainer: 'body',
    disabled: false,
    icon: ProiconsCommentExclamation,
});

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
                    :class="
                        cn(
                            'absolute z-30',
                            'transition-opacity duration-200 ease-in-out',
                            'flex h-fit max-h-[50vh] items-center gap-2 overflow-auto p-3',
                            'border border-neutral-200/70 dark:border-none',
                            'dark:odd:bg-primary-dark-600/70 bg-white dark:bg-neutral-800/70',
                            'scrollbar-minimal rounded-md text-sm shadow-md backdrop-blur-lg',
                            'md:max-w-xl xl:max-w-3xl',
                            positionClasses,
                        )
                    "
                    :style="tooltipStyles"
                >
                    <slot name="icon">
                        <template v-if="!iconHidden">
                            <component class="mb-auto h-5 w-5 shrink-0" :is="icon" />
                        </template>
                    </slot>
                    <slot name="content">
                        <div class="flex flex-col gap-2">
                            <h4 v-if="contentTitle">{{ contentTitle }}</h4>
                            <p class="w-full text-pretty whitespace-pre-wrap dark:text-neutral-400" v-if="content">{{ content }}</p>
                        </div>
                    </slot>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
