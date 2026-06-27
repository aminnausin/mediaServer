<script setup lang="ts">
import type { ContextMenuItem } from '@aminnausin/cedar-ui';

import { computed, ref, useTemplateRef } from 'vue';
import { useMutationObserver } from '@vueuse/core';
import { ButtonBase } from '@/components/cedar-ui/button';
import { cn } from '@aminnausin/cedar-ui';

import ProiconsChevronRight from '~icons/proicons/chevron-right';

const props = withDefaults(defineProps<ContextMenuItem & { divider?: boolean; children?: ContextMenuItem[]; submenuStyle?: string }>(), {
    selectedStyle: 'text-primary font-bold',
});

const isSubMenuOpen = ref(false);
const isFloating = ref(true);

const subMenu = useTemplateRef('sub-menu');

const hasChildren = computed(() => (props.children?.length ?? 0) > 0);

const wrapperProps = computed(() => {
    if (!props.url) return {};
    if (props.external) return { href: props.url, target: props.target ?? '_blank' };
    return { to: props.url, target: props.target };
});

useMutationObserver(subMenu, () => (isFloating.value = subMenu.value?.dataset.floating !== 'false'), { attributeFilter: ['data-floating'] });
</script>
<template>
    <div v-if="divider" class="bg-hr dark:bg-hr/30 -mx-1 my-1 h-px" />

    <div v-else class="relative" :class="{ 'group/submenu': hasChildren }">
        <ButtonBase
            v-bind="wrapperProps"
            :class="
                cn(
                    { [selectedStyle]: selected },
                    'hocus:bg-overlay-accent h-7 w-full justify-start rounded-md px-2 py-1.5 ring ring-transparent select-none ring-inset focus:outline-none dark:focus-within:ring-white dark:focus-visible:bg-neutral-950/90',
                    style,
                )
            "
            :disabled="disabled"
            @click="
                (e: MouseEvent) => {
                    if (hasChildren) {
                        e.stopPropagation();
                        isSubMenuOpen = !isSubMenuOpen;
                        return;
                    }

                    action?.();
                }
            "
        >
            <slot name="icon">
                <component v-if="icon" :is="icon" class="size-4 shrink-0" />
                <span v-else class="size-4 shrink-0" />
            </slot>
            <span class="mr-auto truncate text-nowrap">{{ text }}</span>
            <ProiconsChevronRight v-if="hasChildren" class="size-3 opacity-60" />
            <span v-else-if="shortcut" class="tracking-widest opacity-60">{{ shortcut }}</span>
        </ButtonBase>

        <div
            v-if="hasChildren"
            ref="sub-menu"
            data-submenu
            data-floating="true"
            :class="
                cn(
                    'opacity-0 group-focus-within/submenu:opacity-100 group-hover/submenu:opacity-100',
                    'pointer-events-none group-focus-within/submenu:pointer-events-auto group-hover/submenu:pointer-events-auto',
                    'transition-opacity duration-300 ease-in group-hover/submenu:ease-out',
                    {
                        'bg-overlay-2-t border-overlay-border/10 dark:border-overlay-border/20 absolute top-0 max-h-none w-48 rounded-md border p-1 shadow-xs backdrop-blur-xs duration-100':
                            isFloating,
                        'scrollbar-minimal -mx-1 flex flex-col gap-1 overflow-y-auto transition-[opacity,max-height]': !isFloating,
                        'max-h-0 group-focus-within/submenu:max-h-33 group-hover/submenu:max-h-33': !isFloating && !isSubMenuOpen,
                        'pointer-events-auto max-h-33 opacity-100': !isFloating && isSubMenuOpen,
                    },
                    submenuStyle,
                )
            "
        >
            <div :class="cn('bg-hr dark:bg-hr/30 top-0 h-0', { 'sticky z-3 min-h-px': !isFloating })" />
            <ContextMenuItem v-for="(child, index) in children" :key="index" v-bind="child" :class="cn({ 'ps-2 pe-1': !isFloating })" />
        </div>
    </div>
</template>
