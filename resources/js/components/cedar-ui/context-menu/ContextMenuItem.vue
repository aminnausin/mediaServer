<script setup lang="ts">
import type { ContextMenuItem } from '@aminnausin/cedar-ui';

import { ButtonBase } from '@/components/cedar-ui/button';
import { computed } from 'vue';
import { cn } from '@aminnausin/cedar-ui';

import ProiconsChevronRight from '~icons/proicons/chevron-right';

const props = withDefaults(defineProps<ContextMenuItem & { divider?: boolean; children?: ContextMenuItem[]; submenuStyle?: string }>(), {
    selectedStyle: 'text-primary font-bold',
});

const wrapperProps = computed(() => {
    if (!props.url) return {};
    if (props.external) return { href: props.url, target: props.target ?? '_blank' };
    return { to: props.url, target: props.target };
});

const hasChildren = computed(() => (props.children?.length ?? 0) > 0);
</script>
<template>
    <div v-if="divider" class="bg-hr dark:bg-hr/30 -mx-1 my-1 h-px" />

    <div v-else class="relative" :class="{ 'group/submenu': hasChildren }">
        <ButtonBase
            v-bind="wrapperProps"
            :class="cn({ [selectedStyle]: selected }, 'hocus:bg-overlay-accent h-7 w-full justify-start rounded-sm px-2 py-1.5 select-none', style)"
            :disabled="disabled"
            @click.stop="if (!hasChildren) action?.();"
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
            data-submenu
            data-floating="true"
            :class="
                cn(
                    'floating-menu peer max-h-0 px-2',
                    'opacity-0',
                    'group-hover/submenu:max-h-none group-hover/submenu:opacity-100',
                    'group-focus-within/submenu:pointer-events-auto group-focus-within/submenu:opacity-100',
                    submenuStyle,
                )
            "
        >
            <div class="submenu-divider bg-hr dark:bg-hr/30 -mx-3 my-1 h-px" />
            <ContextMenuItem v-for="(child, index) in children" :key="index" v-bind="child" />
        </div>
    </div>
</template>
<style lang="css" scoped>
@reference '@css/app.css';
.floating-menu {
    @apply bg-overlay-2-t border-overlay-border/10 absolute top-0 max-h-none w-48 rounded-md border p-1 px-1! shadow-xs backdrop-blur-xs transition-opacity duration-100 ease-in group-hover/submenu:ease-out;
}

[data-submenu][data-floating='true'] > .submenu-divider {
    display: none;
}
</style>
