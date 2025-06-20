<script setup lang="ts">
import { reactive } from 'vue';

const data = reactive<{
    hoverCardHovered: boolean;
    hoverCardDelay: number;
    hoverCardLeaveDelay: number;
    hoverCardTimout: number | null;
    hoverCardLeaveTimeout: number | null;
    tooltipStyles: Record<string, string>;
}>({
    hoverCardHovered: false,
    hoverCardDelay: 600,
    hoverCardLeaveDelay: 500,
    hoverCardTimout: null,
    hoverCardLeaveTimeout: null,
    tooltipStyles: {},
});

const hoverCardEnter = (event: MouseEvent) => {
    if (data.hoverCardLeaveTimeout) clearTimeout(data.hoverCardLeaveTimeout);

    if (data.hoverCardHovered) return;

    if (data.hoverCardTimout) clearTimeout(data.hoverCardTimout);

    data.hoverCardTimout = window.setTimeout(() => {
        data.hoverCardHovered = true;
        updateTooltipPosition(event);
    }, data.hoverCardDelay);
};

const hoverCardLeave = () => {
    if (data.hoverCardTimout) clearTimeout(data.hoverCardTimout);
    if (!data.hoverCardHovered) return;
    if (data.hoverCardLeaveTimeout) clearTimeout(data.hoverCardLeaveTimeout);
    data.hoverCardLeaveTimeout = window.setTimeout(() => {
        data.hoverCardHovered = false;
    }, data.hoverCardLeaveDelay);
};

const updateTooltipPosition = (event: MouseEvent) => {
    const rect = (event.target as HTMLElement).getBoundingClientRect();
    data.tooltipStyles = { left: `${rect.left + window.scrollX}px`, top: `${rect.bottom + window.scrollY}px` };
};
</script>

<template>
    <div class="relative" @mouseover="hoverCardEnter" @mouseleave="hoverCardLeave">
        <slot name="trigger">
            <a href="#_" class="hover:underline">@thedevdojo</a>
        </slot>

        <Teleport to="body">
            <Transition enter-from-class="opacity-0" enter-to-class="opacity-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div
                    v-show="data.hoverCardHovered"
                    :style="data.tooltipStyles"
                    :class="`absolute w-[365px] max-w-[100vw] max-h-[50vh] overflow-y-auto scrollbar-minimal md:max-w-lg z-30 transition-all duration-300 ease-in-out -translate-x-1/2 translate-y-3 left-1/2 h-auto bg-white space-x-3 p-5 flex items-start rounded-md shadow-sm border border-neutral-200/70`"
                    v-cloak
                >
                    <slot name="content">
                        <img src="https://cdn.devdojo.com/users/June2022/devdojo.jpg" alt="devdojo logo" class="rounded-full w-14 h-14" />
                        <div class="relative">
                            <p class="mb-1 font-bold">@thedevdojo</p>
                            <p class="mb-1 text-sm text-gray-600">The creative platform for developers. Community, tools, products, and more</p>
                            <p class="flex items-center space-x-1 text-xs text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z"
                                    />
                                </svg>
                                <span>Joined June 2020</span>
                            </p>
                        </div>
                    </slot>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
