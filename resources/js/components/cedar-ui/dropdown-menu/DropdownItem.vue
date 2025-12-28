<script setup lang="ts">
import type { AnchorTarget } from '@aminnausin/cedar-ui';
import type { Component } from 'vue';

import { ButtonBase } from '../button';
import { computed } from 'vue';

const props = defineProps<{
    linkData: { url?: string; text: string; shortcut?: string | Component; shortcutTitle?: string; title?: string; target?: AnchorTarget };
    selected: boolean | undefined;
    external: boolean | undefined;
    disabled?: boolean;
}>();

const wrapperProps = computed(() => {
    if (!props.linkData.url) return {};
    if (props.external) return { href: props.linkData.url, target: props.external ? '_blank' : '_self' };
    return { to: props.linkData.url, target: props.linkData.target ?? '_self' };
});
</script>
<template>
    <ButtonBase
        role="menuitem"
        v-bind="wrapperProps"
        :class="['hover:bg-overlay-accent relative w-full rounded px-2 py-1.5 text-sm select-none', { 'text-primary dark:text-primary-muted font-bold': selected }]"
        :disabled="disabled"
        :title="linkData.title"
    >
        <slot name="icon">
            <span class="mr-2 h-4 w-4"></span>
        </slot>

        <span class="mr-auto text-nowrap">
            <slot>
                {{ linkData.text }}
            </slot>
        </span>

        <span v-if="!linkData.shortcut || typeof linkData.shortcut === 'string'" class="text-xs tracking-widest opacity-60" :title="linkData.shortcutTitle">
            {{ linkData.shortcut }}
        </span>
        <component v-else :is="linkData.shortcut" class=""></component>
    </ButtonBase>
</template>
