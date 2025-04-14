<script setup lang="ts">
import { Title } from 'chart.js';
import { computed } from 'vue';
import { RouterLink } from 'vue-router';
const props = defineProps<{
    linkData: { url?: string; text: string; shortcut?: string; title?: string };
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
        <span class="ml-auto text-xs tracking-widest opacity-60">{{ linkData.shortcut ?? '' }}</span>
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
        <span class="ml-auto text-xs tracking-widest opacity-60">{{ linkData.shortcut ?? '' }}</span>
    </component>
</template>
<!--  OLD CODE
    <a
        v-if="external"
        :class="{ 'font-bold text-violet-500': selected }"
        :href="linkData.url"
        target="_blank"
        class="cursor-pointer relative w-full flex select-none hover:bg-neutral-100 dark:hover:bg-neutral-900 items-center rounded px-2 py-1.5 text-sm outline-none transition-colors data-[disabled]:pointer-events-none data-[disabled]:opacity-50"
        role="menuitem"
    >
        <slot name="icon">
            <span width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2">
            </span>
        </slot>
        <span class="text-nowrap">{{ linkData.text }}</span
        ><span class="ml-auto text-xs tracking-widest opacity-60">{{ linkData.shortcut ?? '' }}</span>
    </a>
    <RouterLink
        v-else-if="linkData.url"
        :class="{ 'font-bold text-violet-500': selected }"
        :to="disabled ? '' : linkData.url"
        :data-disabled="disabled"
        class="cursor-pointer relative w-full flex select-none hover:bg-neutral-100 dark:hover:bg-neutral-900 items-center rounded px-2 py-1.5 text-sm outline-none transition-colors"
        role="menuitem"
    >
        <slot name="icon">
            <span width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2">
            </span>
        </slot>
        <span class="text-nowrap">{{ linkData.text }}</span>
        <span class="ml-auto text-xs tracking-widest opacity-60">{{ linkData.shortcut ?? '' }}</span>
    </RouterLink>
    <button
        v-else
        :class="{ 'font-bold text-violet-500': selected }"
        :disabled="disabled"
        class="cursor-pointer relative w-full flex select-none hover:bg-neutral-100 dark:hover:bg-neutral-900 items-center rounded px-2 py-1.5 text-sm outline-none transition-colors data-[disabled=true]:pointer-events-none data-[disabled=true]:opacity-50"
        role="menuitem"
    >
        <slot name="icon">
            <span width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2">
            </span>
        </slot>
        <span class="text-nowrap">{{ linkData.text }}</span>
        <span class="ml-auto text-xs tracking-widest opacity-60">{{ linkData.shortcut ?? '' }}</span>
    </button>
-->
