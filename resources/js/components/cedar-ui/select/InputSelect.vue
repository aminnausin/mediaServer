<script setup lang="ts">
// Needs to be converted to TypeScript and use library elements but this is a lot of work
import type { SelectItem, SelectProps } from '@aminnausin/cedar-ui';

import { onMounted, ref, useTemplateRef, watch } from 'vue';
import { CedarCheckMark, CedarChevronUpDown } from '../icons';
import { OnClickOutside } from '@vueuse/components';
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
        {
            title: 'Date Uploaded',
            value: 'date',
            disabled: false,
        },
        {
            title: 'Date released',
            value: 'date_released',
            disabled: false,
        },
        {
            title: 'Episode',
            value: 'episode',
            disabled: true,
        },
        {
            title: 'Season',
            value: 'season',
            disabled: true,
        },
    ],
});

const emit = defineEmits(['selectItem']);
const selectButton = useTemplateRef('selectButton');
const selectableItemsList = useTemplateRef('selectableItemsList');
const selectableItemsRoot = useTemplateRef('selectableItemsRoot');
const select = useSelect(props.options, { selectableItemsList, selectButton });

const closeFocusOutTimeout = ref<number | null>(null);

const handleItemClick = (item: any, setFocus = true) => {
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

    closeFocusOutTimeout.value = window.setTimeout(() => {
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
        select.selectableItemsList = selectableItemsList;
    },
    { immediate: true },
);
</script>
<template>
    <section :class="[`group relative text-sm`, rootClass]" @focusout="handleFocusOut" ref="selectableItemsRoot">
        <button
            @click="select.toggleSelect(!select.selectOpen)"
            :id="props.name"
            :title="title ?? 'Make Selection'"
            :disabled="disabled"
            :class="
                cn(
                    'transition-input ease-in-out focus:outline-hidden', // Animation
                    'disabled:button-disabled disabled:button-disabled-pointer', // Disabled
                    'relative flex items-center justify-between gap-2', // Layout
                    'cursor-pointer rounded-md shadow-xs', // Style
                    'h-10 max-h-full w-full py-2 pr-10 pl-3', // Size
                    'bg-surface-2 button-base',
                    'ring-r-button hocus:ring-2 ring-1',
                    { 'hocus:ring-0': select.selectOpen },
                    { 'text-foreground-3': placeholder && !select.selectedItem },
                    'hover:ring-primary-muted focus:ring-primary focus-within:ring-primary-muted',
                    props.class,
                )
            "
            ref="selectButton"
            type="button"
        >
            <span class="truncate"
                >{{
                    //@ts-ignore
                    select.selectedItem ? `${prefix}${select.selectedItem.title}` : placeholder
                }}
            </span>
            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                <slot name="selectButtonIcon">
                    <CedarChevronUpDown :class="['text-foreground-2 size-5']" />
                </slot>
            </span>
        </button>

        <Transition enter-from-class="opacity-0" enter-to-class="opacity-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <OnClickOutside
                v-show="select.selectOpen"
                :class="[select.selectDropdownPosition == 'top' ? `bottom-0 ${menuMargin?.bottom ?? 'mb-11'}` : `top-0 ${menuMargin?.top ?? 'mt-11'}`]"
                class="bg-overlay-t ring-r-button absolute z-30 mt-1 max-h-56 w-full overflow-clip rounded-md shadow-md ring-1 backdrop-blur-lg transition duration-(--duration-input) ease-in-out"
                @trigger="select.toggleSelect(false)"
                @keydown.esc.stop="
                    (event: Event) => {
                        if (select.selectOpen) {
                            select.toggleSelect(false);
                            event.stopPropagation();
                        }
                    }
                "
                @keydown.down.stop.prevent="
                    (event: Event) => {
                        if (select.selectOpen) {
                            select.selectableItemActiveNext();
                        } else {
                            select.toggleSelect(true);
                        }
                        event.preventDefault();
                    }
                "
                @keydown.up.stop.prevent="
                    (event: Event) => {
                        if (select.selectOpen) {
                            select.selectableItemActivePrevious();
                        } else {
                            select.toggleSelect(true);
                        }
                        event.preventDefault();
                    }
                "
                @keydown.enter.stop.prevent="
                    //@ts-ignore
                    select.selectedItem = select.selectableItemActive;
                    select.toggleSelect(false);
                    console.log('enter');
                "
                @keydown.stop="select.selectKeydown($event)"
            >
                <ul ref="selectableItemsList" class="scrollbar-minimal max-h-56 w-full overflow-auto focus:outline-hidden" role="listbox">
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
                                    'text-foreground-6': !select.selectableItemActive === item,
                                },
                                'data-[disabled=true]:button-disabled data-[disabled=true]:button-disabled-pointer relative flex h-full cursor-pointer items-center py-2 pl-8 focus:rounded-md',
                            ]"
                            role="option"
                            :aria-selected="select.selectableItemIsActive(item) ? 'true' : 'false'"
                        >
                            <CedarCheckMark
                                v-if="
                                    //@ts-ignore
                                    select.selectedItem.value == item.value
                                "
                                class="text-foreground-2 absolute left-0 ml-2 size-4 stroke-current"
                            />
                            <span class="block truncate font-medium">{{ item.title }}</span>
                        </li>
                    </template>
                </ul>
            </OnClickOutside>
        </Transition>
    </section>
</template>
