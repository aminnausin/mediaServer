<script setup lang="ts">
import type { Component } from 'vue';

import { computed, nextTick, onMounted, ref, useTemplateRef, watch } from 'vue';
import { TableLoadingSpinner } from '@/components/cedar-ui/table';
import { ButtonBase } from '@/components/cedar-ui/button';
import { cn } from '@aminnausin/cedar-ui';

import ProiconsChevronRight from '~icons/proicons/chevron-right';
import ProiconsChevronLeft from '~icons/proicons/chevron-left';

const props = withDefaults(
    defineProps<{
        title: string;
        useGrid?: boolean;
        isLoading?: boolean;
        itemCount?: number;
        skeletonCount?: number;
        skeletonClass?: string;
        noDataMessage?: string;
    }>(),
    {
        skeletonCount: 10,
        itemCount: 0,
    },
);

defineOptions({ inheritAttrs: false });

const scrollContainer = useTemplateRef('scrollContainer');
const canScrollLeft = ref(false);
const canScrollRight = ref(false);

const updateScrollState = () => {
    if (!scrollContainer.value) return;
    const el = scrollContainer.value;
    canScrollLeft.value = el.scrollLeft > 4;
    canScrollRight.value = el.scrollLeft + el.clientWidth < el.scrollWidth - 4;
};

const scrollByAmount = (dir: 1 | -1) => {
    const el = scrollContainer.value;
    if (!el) return;
    el.scrollBy({ left: dir * el.clientWidth * 0.9, behavior: 'smooth' });
};

const scrollDirections = computed<{ allowed: boolean; value: 1 | -1; icon: Component; title: string }[]>(() => [
    { allowed: canScrollLeft.value && props.itemCount > 0, value: -1, icon: ProiconsChevronLeft, title: 'Previous' },
    { allowed: canScrollRight.value && props.itemCount > 0, value: 1, icon: ProiconsChevronRight, title: 'Next' },
]);

watch(
    () => props.isLoading,
    async () => {
        await nextTick();
        updateScrollState();
    },
);

onMounted(async () => {
    await nextTick();
    updateScrollState();
});
</script>

<template>
    <section :class="cn('group/row content-auto space-y-3 [contain-intrinsic-size:auto_320px]', useGrid ? '' : $attrs.class)">
        <div class="flex items-center justify-between">
            <h2 class="text-lg">{{ title }}</h2>
            <div v-if="!useGrid" class="bg-surface-3/50 dark:bg-surface-3/50 flex w-fit gap-0.5 rounded-lg p-0.5 text-xs">
                <ButtonBase
                    v-for="direction in scrollDirections"
                    :key="direction.value"
                    :title="direction.title"
                    :class="
                        cn('h-7 rounded-md px-3 py-1 capitalize', 'text-foreground-2 hover:text-foreground-0 hover:bg-surface-1/50', {
                            'hover:bg-surface-1 hover:dark:bg-surface-4 hover:text-primary-active hover:dark:text-primary-muted text-foreground-0 hover:shadow-sm':
                                direction.allowed,
                        })
                    "
                    :disabled="!direction.allowed"
                    @click="scrollByAmount(direction.value)"
                >
                    <component :is="direction.icon" class="size-4" />
                </ButtonBase>
            </div>
        </div>
        <div class="relative">
            <template v-if="!useGrid && props.itemCount > 0 && (canScrollLeft || canScrollRight)">
                <div
                    :class="
                        cn(
                            'from-surface-1 duration-input bg-linear-to-r to-transparent opacity-0 transition-opacity',
                            'pointer-events-none absolute inset-y-0 left-0 z-10 w-2 dark:w-4',
                            { 'opacity-100': canScrollLeft },
                        )
                    "
                />
                <div
                    :class="
                        cn(
                            'from-surface-1 duration-input bg-linear-to-l to-transparent opacity-0 transition-opacity',
                            'pointer-events-none absolute inset-y-0 right-0 z-10 w-2 dark:w-4',
                            { 'opacity-100': canScrollRight },
                        )
                    "
                />
                <ButtonBase
                    v-for="direction in scrollDirections"
                    type="button"
                    :key="direction.value"
                    :title="direction.title"
                    :class="
                        cn('absolute top-1/2 z-20 hidden size-7 -translate-y-full', 'origin-center transform-gpu items-center justify-center p-0 @min-md:flex', {
                            'scale-100! cursor-default': !direction.allowed,
                            'right-2': direction.value === 1,
                            'left-2': direction.value === -1,
                        })
                    "
                    @click="scrollByAmount(direction.value)"
                >
                    <div
                        :class="
                            cn('size-7 rounded-full p-1.5', 'bg-neutral-900/70 text-white opacity-60 backdrop-blur-xs transition-opacity', {
                                'group-hover/row:opacity-100 hover:bg-neutral-900/90': direction.allowed,
                                'opacity-20': !direction.allowed,
                            })
                        "
                    >
                        <component :is="direction.icon" class="size-4" />
                    </div>
                </ButtonBase>
            </template>

            <div
                ref="scrollContainer"
                :class="cn('scrollbar-hide gap-3', useGrid ? $attrs.class : 'flex snap-x snap-mandatory overflow-x-auto scroll-smooth')"
                @scroll="updateScrollState"
            >
                <template v-if="isLoading">
                    <div v-for="n in skeletonCount" :key="n" class="flex shrink-0 animate-pulse snap-start flex-col gap-2">
                        <div :class="cn('dark:bg-surface-2 rounded-md bg-neutral-300', skeletonClass)" />
                        <div class="h-3 w-30 rounded-md bg-neutral-300 dark:bg-neutral-700/60"></div>
                        <div class="h-3 w-20 rounded-md bg-neutral-200 dark:bg-neutral-700/40"></div>
                    </div>
                </template>
                <slot v-else-if="props.itemCount > 0" />
                <TableLoadingSpinner v-else :is-loading="false" :no-results-message="noDataMessage" class="h-16 text-sm" />
            </div>
        </div>
    </section>
</template>
