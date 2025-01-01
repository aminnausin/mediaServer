<script setup lang="ts">
import { ref } from 'vue';
import ProiconsCommentExclamation from '~icons/proicons/comment-exclamation';

const props = withDefaults(
    defineProps<{
        content?: string;
        positionClasses?: string;
        hoverCardDelay?: number;
        hoverCardLeaveDelay?: number;
    }>(),
    {
        positionClasses: 'z-30 left-20 bottom-10',
        hoverCardDelay: 600,
        hoverCardLeaveDelay: 500,
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
    if (props.content === '') return;
    if (hoverCardTimout.value) clearTimeout(hoverCardTimout.value);

    if (!hoverCardHovered.value) return;
    if (hoverCardLeaveTimeout.value) clearTimeout(hoverCardLeaveTimeout.value);

    hoverCardLeaveTimeout.value = setTimeout(() => {
        hoverCardHovered.value = false;
    }, props.hoverCardLeaveDelay);
};

const updateTooltipPosition = (event: MouseEvent) => {
    const rect = (event.target as HTMLElement).getBoundingClientRect();
    tooltipStyles.value = { left: `${rect.left + window.scrollX}px`, top: `${rect.bottom + window.scrollY}px` };
};
//relative
</script>

<template>
    <div class="" @mouseover="hoverCardEnter" @mouseleave="hoverCardLeave">
        <slot name="trigger">
            <a href="#_" class="hover:underline">@thedevdojo</a>
        </slot>
        <Teleport to="body" v-if="init">
            <Transition enter-from-class="opacity-0" enter-to-class="opacity-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div
                    @mouseover="hoverCardEnter"
                    @mouseleave="hoverCardLeave"
                    v-show="hoverCardHovered"
                    v-cloak
                    :class="`flex ${positionClasses} absolute overflow-auto transition-opacity ease-in-out duration-200 md:max-w-xl xl:max-w-3xl text-sm p-3 h-fit bg-white dark:odd:bg-primary-dark-600/70 dark:bg-neutral-800/70 backdrop-blur-lg border dark:border-none rounded-md shadow-md border-neutral-200/70 gap-2 items-center`"
                    :style="tooltipStyles"
                >
                    <slot name="icon">
                        <ProiconsCommentExclamation class="h-5 w-5 mb-auto shrink-0" />
                    </slot>
                    <slot name="content">
                        <p class="text-pretty h-fit w-full break-words whitespace-pre-wrap">
                            {{ content }}
                        </p>
                    </slot>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
