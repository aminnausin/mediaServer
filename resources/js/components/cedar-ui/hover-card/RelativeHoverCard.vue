<script setup lang="ts">
import type { RelativeHoverCardProps } from '@aminnausin/cedar-ui';

import { ProiconsCommentExclamation } from '../icons';
import { ref } from 'vue';
import { cn } from '@aminnausin/cedar-ui';

const props = withDefaults(defineProps<RelativeHoverCardProps>(), {
    hoverCardDelay: 600,
    hoverCardLeaveDelay: 500,
    icon: ProiconsCommentExclamation,
});

const hoverCardHovered = ref<boolean>(false);
const hoverCardTimout = ref<NodeJS.Timeout | null>(null);
const hoverCardLeaveTimeout = ref<NodeJS.Timeout | null>(null);
const tooltipStyles = ref<Record<string, string>>({});

const init = ref(false);

const hoverCardEnter = () => {
    if (props.content === '') return;

    if (hoverCardLeaveTimeout.value) clearTimeout(hoverCardLeaveTimeout.value);

    if (hoverCardHovered.value) return;

    if (hoverCardTimout.value) clearTimeout(hoverCardTimout.value);

    if (!init.value) init.value = true; // Loads into Dom after hover for the first time

    hoverCardTimout.value = globalThis.setTimeout(() => {
        hoverCardHovered.value = true;
    }, props.hoverCardDelay);
};

const hoverCardLeave = () => {
    if (props.content === '') return;
    if (hoverCardTimout.value) clearTimeout(hoverCardTimout.value);

    if (!hoverCardHovered.value) return;
    if (hoverCardLeaveTimeout.value) clearTimeout(hoverCardLeaveTimeout.value);

    hoverCardLeaveTimeout.value = globalThis.setTimeout(() => {
        hoverCardHovered.value = false;
    }, props.hoverCardLeaveDelay);
};
</script>

<template>
    <div class="relative" @mouseover="hoverCardEnter" @mouseleave="hoverCardLeave">
        <slot name="trigger">
            <a href="#_" class="hover:underline">@aminnausin</a>
        </slot>
        <Transition enter-from-class="opacity-0" enter-to-class="opacity-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div
                @mouseover="hoverCardEnter"
                @mouseleave="hoverCardLeave"
                v-show="hoverCardHovered && (content || $slots.content)"
                v-cloak
                :class="
                    cn(
                        'absolute z-30',
                        'transition-opacity duration-(--duration-input) ease-in-out',
                        'flex h-fit items-center gap-2 overflow-auto p-3',
                        'border-overlay-2-t border dark:border-none',
                        'bg-overlay-t text-foreground-0',
                        'rounded-md text-sm shadow-md backdrop-blur-lg',
                        'md:max-w-xl xl:max-w-3xl',
                        positionClasses,
                    )
                "
                :style="tooltipStyles"
                v-if="init"
            >
                <slot name="icon">
                    <template v-if="!iconHidden">
                        <component class="mb-auto size-5 shrink-0" :is="icon" />
                    </template>
                </slot>
                <slot name="content">
                    <p class="h-fit w-full text-pretty wrap-break-word whitespace-pre-wrap">
                        {{ content }}
                    </p>
                </slot>
            </div>
        </Transition>
    </div>
</template>
