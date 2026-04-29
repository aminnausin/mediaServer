<script setup lang="ts">
// Needs to be converted to TypeScript and use library elements but this is a lot of work
import type { SelectProps } from '@aminnausin/cedar-ui';

import { onMounted, ref, useTemplateRef, watch } from 'vue';
import { CedarCheckMark, CedarChevronUpDown } from '../icons';
import { useScrollbarDetection } from '@/composables/design/useScrollbarDetection';
import { cn, useSelect } from '@aminnausin/cedar-ui';

const props = withDefaults(defineProps<SelectProps>(), {
    class: '',
    rootClass: '',
    prefix: '',
    defaultItem: null,
    options: () => [
        {
            title: 'Title',
            value: 'title',
            disabled: false,
        },
    ],
});

const emit = defineEmits(['selectItem']);
const selectButton = useTemplateRef('selectButton');
const selectableItemsList = useTemplateRef('selectableItemsList');
const selectableItemsRoot = useTemplateRef('selectableItemsRoot');
const select = useSelect(props.options, { selectableItemsList, selectButton });

const closeFocusOutTimeout = ref<NodeJS.Timeout | null>(null);

const { hasScrollbar } = useScrollbarDetection(selectableItemsList);

const handleItemClick = (item: any, setFocus = true) => {
    if (!item) return;

    select.selectedItem = item;
    select.toggleSelect(false);

    if (setFocus) selectButton.value?.focus();
    emit('selectItem', select.selectedItem);
};

const handleItemHover = (item: any) => {
    select.selectableItemActive = item;
    select.selectScrollToActiveItem();
};

const handleItemFocus = (item: any) => {
    select.selectableItemActive = item;
};

const handleFocusOut = () => {
    if (closeFocusOutTimeout.value) {
        clearTimeout(closeFocusOutTimeout.value);
    }

    closeFocusOutTimeout.value = globalThis.setTimeout(() => {
        if (!selectableItemsRoot.value) return;

        const active = document.activeElement;

        if (!selectableItemsRoot.value.contains(active)) {
            select.toggleSelect(false);
        }
    }, 0);
};

onMounted(() => {
    if (props.disabled) return;

    if (props.defaultItem != undefined && props.defaultItem < props.options.length && props.defaultItem >= 0) {
        handleItemClick(props.options[props.defaultItem], false);
    }
});

watch(
    () => props.options,
    () => {
        select.selectableItems = props.options;
    },
);
watch(
    selectButton,
    () => {
        if (!selectButton.value) return;
        select.selectButton = selectButton;
    },
    { immediate: true },
);
watch(
    () => selectableItemsList.value,
    () => {
        if (!selectableItemsList.value) return;
        select.selectableItemsList = selectableItemsList; // this composable is really bad
    },
    { immediate: true },
);
</script>
<template>
    <section :class="[`group relative w-full text-sm`, rootClass]" @focusout="handleFocusOut" ref="selectableItemsRoot">
        <button
            @click="select.toggleSelect(!select.selectOpen)"
            :id="props.name"
            :title="title ?? 'Make Selection'"
            :disabled="disabled"
            :class="
                cn(
                    'transition-input ease-in-out focus:outline-hidden', // Animation
                    { 'button-disabled-pointer button-disabled': disabled }, // Disabled
                    'bg-surface-2 button-base', // Background
                    'ring-r-button hover:ring-primary-muted hocus:ring-2 focus:ring-primary ring-1', // Ring
                    { 'ring-primary-muted ring-2': select.selectOpen }, // Alt Ring
                    { 'text-foreground-3': placeholder && !select.selectedItem }, // Alt text
                    'flex items-center justify-between gap-2', // Layout
                    'cursor-pointer shadow-xs', // Style
                    'h-8 max-h-full w-full p-2 pl-3', // Size
                    'rounded-md break-all', // Shape
                    'group',
                    props.class,
                )
            "
            ref="selectButton"
            type="button"
        >
            <span class="line-clamp-1">
                {{
                    //@ts-ignore
                    select.selectedItem ? `${prefix}${select.selectedItem.title}` : placeholder
                }}
            </span>
            <slot name="selectButtonIcon">
                <CedarChevronUpDown
                    :class="
                        cn('text-foreground-2 duration-input group-focus:text-foreground-0 group-hover:text-foreground-0 size-5 transition-[color]', {
                            'text-foreground-0': select.selectOpen,
                        })
                    "
                />
            </slot>
        </button>

        <Transition enter-from-class="opacity-0" enter-to-class="opacity-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div
                v-show="select.selectOpen"
                :class="[select.selectDropdownPosition == 'top' ? `bottom-0 ${menuMargin?.bottom ?? 'mb-11'}` : `top-0 ${menuMargin?.top ?? 'mt-11'}`]"
                class="bg-overlay-t ring-r-button absolute z-30 mt-1 max-h-56 w-full overflow-clip rounded-md shadow-md ring-1 backdrop-blur-lg transition duration-(--duration-input) ease-in-out"
                @keydown.esc.stop="select.toggleSelect(false)"
                @keydown.down.stop.prevent="select.selectableItemActiveNext()"
                @keydown.up.stop.prevent="select.selectableItemActivePrevious()"
                @keydown.enter.stop.prevent="handleItemClick(select.selectableItemActive)"
                @keydown.stop="select.selectKeydown($event)"
            >
                <ul
                    ref="selectableItemsList"
                    :class="['scrollbar-minimal scrollbar-thumb:rounded-l-none! scrollbar-thumb:rounded-r-md! max-h-56 w-full overflow-auto focus:outline-hidden']"
                    role="listbox"
                >
                    <template v-for="item in select.selectableItems" :key="item.value">
                        <li
                            @click="handleItemClick(item)"
                            @keydown.enter="handleItemClick(item)"
                            @keydown.space="handleItemClick(item)"
                            @focus="handleItemFocus(item)"
                            @mousemove="handleItemHover(item)"
                            :id="item.value + '-' + select.selectId"
                            :title="item.title"
                            :data-disabled="item.disabled ? item.disabled : ''"
                            :tabindex="'0'"
                            :class="[
                                {
                                    'bg-overlay-accent dark:bg-overlay-accent/70': select.selectableItemActive === item,
                                    'text-foreground-6': select.selectableItemActive !== item && select.selectedItem !== item,
                                },
                                'data-[disabled=true]:button-disabled data-[disabled=true]:button-disabled-pointer flex h-full cursor-pointer items-center gap-2 py-2 focus-within:outline-none',
                                hasScrollbar ? 'rounded-l-md' : 'rounded-md',
                            ]"
                            role="option"
                            :aria-selected="select.selectableItemIsActive(item) ? 'true' : 'false'"
                        >
                            <CedarCheckMark
                                :class="[
                                    'text-foreground-2 invisible ml-2 size-4 stroke-current',
                                    //@ts-ignore
                                    { visible: select.selectedItem.value == item.value },
                                ]"
                            />
                            <span class="block truncate font-medium">{{ item.title }}</span>
                        </li>
                    </template>
                </ul>
            </div>
        </Transition>
    </section>
</template>
