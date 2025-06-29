<script setup lang="ts">
import type { Component } from 'vue';

import { RouterLink } from 'vue-router';
import { computed } from 'vue';

const props = defineProps<{
    linkData: { url?: string; text: string; shortcut?: string | Component; shortcutTitle?: string; title?: string };
    selected: boolean | undefined;
    external: boolean | undefined;
    disabled?: boolean;
}>();

const baseClasses =
    'cursor-pointer relative w-full flex select-none hover:bg-neutral-100 dark:hover:bg-neutral-900 items-center rounded px-2 py-1.5 text-sm outline-none transition-colors';
const disabledClasses = 'pointer-events-none opacity-50';
const selectedClass = 'font-bold text-violet-500';
const tag = computed(() => {
    if (props.external) return 'a';
    if (props.linkData.url) return RouterLink;
    return 'button';
});
</script>
<template>
    <RouterLink
        v-if="!external && linkData.url"
        :class="[baseClasses, selected && selectedClass, disabled && disabledClasses]"
        :to="disabled ? '' : linkData.url"
        :disabled="!linkData.url ? disabled : undefined"
        :data-disabled="linkData.url ? disabled : undefined"
        :title="linkData.title"
        role="menuitem"
    >
        <slot name="icon">
            <span width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2">
            </span>
        </slot>
        <span class="text-nowrap">{{ linkData.text }}</span>
        <span class="ml-auto text-xs tracking-widest opacity-60" :title="linkData.shortcutTitle">{{ linkData.shortcut ?? '' }}</span>
    </RouterLink>
    <component
        v-else
        :is="tag"
        :class="[baseClasses, selected && selectedClass, disabled && disabledClasses]"
        :href="external ? linkData.url : ''"
        :target="external ? '_blank' : undefined"
        :disabled="!linkData.url ? disabled : undefined"
        :data-disabled="linkData.url ? disabled : undefined"
        :title="linkData.title"
        role="menuitem"
    >
        <slot name="icon">
            <span class="w-4 h-4 mr-2"></span>
        </slot>
        <span class="text-nowrap">{{ linkData.text }}</span>
        <span v-if="typeof linkData.shortcut === 'string'" class="ml-auto text-xs tracking-widest opacity-60" :title="linkData.shortcutTitle">{{ linkData.shortcut ?? '' }}</span>
        <component v-else :is="linkData.shortcut" class="ml-auto"></component>
    </component>
</template>
