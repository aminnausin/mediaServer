<script setup lang="ts">
import useSelect from '@/composables/useSelect';

import { onMounted, ref, useTemplateRef, watch } from 'vue';
import { OnClickOutside } from '@vueuse/components';

interface SelectItem {
    title: string;
    value: any;
    key?: any;
    disabled?: boolean;
}

const props = withDefaults(
    defineProps<{
        name?: string;
        class?: string;
        rootClass?: string;
        placeholder?: string;
        defaultItem?: number | null;
        options?: SelectItem[];
        disabled?: boolean;
        title?: string;
        prefix?: string;
    }>(),
    {
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
    },
);

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

    closeFocusOutTimeout.value = setTimeout(() => {
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
    <section :class="`relative ${rootClass}`" @focusout="handleFocusOut" ref="selectableItemsRoot">
        <button
            :id="name ?? 'Select'"
            ref="selectButton"
            @click="select.toggleSelect()"
            :class="`${select.selectOpen && 'hocus:ring-0'} relative h-10 flex items-center justify-between w-full py-2 pl-3 pr-10
            text-left rounded-md shadow-sm cursor-pointer text-sm border-none focus:outline-none
            ring-inset ring-1 ring-neutral-200 dark:ring-neutral-700 hocus:ring-[0.125rem] hover:ring-violet-400 hover:dark:ring-violet-700 focus:ring-indigo-400 dark:focus:ring-indigo-500
            text-gray-900 dark:text-neutral-100 bg-white dark:bg-primary-dark-800 ${props.class} ${placeholder && !select.selectedItem ? '!text-neutral-400' : ''}
            disabled:cursor-not-allowed disabled:hover:ring-neutral-200 disabled:hover:dark:ring-neutral-700 disabled:opacity-60
            `"
            :disabled="disabled"
            type="button"
            :title="title ?? 'Make Selection'"
        >
            <span class="truncate"
                >{{
                    //@ts-ignore
                    select.selectedItem ? `${prefix}${select.selectedItem.title}` : placeholder
                }}
            </span>
            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                <slot name="selectButtonIcon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="w-5 h-5 text-gray-400">
                        <path
                            fill-rule="evenodd"
                            d="M10 3a.75.75 0 01.55.24l3.25 3.5a.75.75 0 11-1.1 1.02L10 4.852 7.3 7.76a.75.75 0 01-1.1-1.02l3.25-3.5A.75.75 0 0110 3zm-3.76 9.2a.75.75 0 011.06.04l2.7 2.908 2.7-2.908a.75.75 0 111.1 1.02l-3.25 3.5a.75.75 0 01-1.1 0l-3.25-3.5a.75.75 0 01.04-1.06z"
                            clip-rule="evenodd"
                        ></path>
                    </svg>
                </slot>
            </span>
        </button>

        <Transition enter-active-class="transition ease-out duration-50" enter-from-class="opacity-0 -translate-y-1" enter-to-class="opacity-100">
            <OnClickOutside
                v-cloak
                v-if="select.selectOpen"
                :class="{
                    'bottom-0 mb-11': select.selectDropdownPosition == 'top',
                    'top-0 mt-11': select.selectDropdownPosition == 'bottom',
                }"
                class="z-30 absolute w-full mt-1 overflow-clip text-sm rounded-md shadow-md max-h-56 ring-1 ring-opacity-5 ring-black dark:ring-neutral-700 bg-white dark:bg-neutral-800/70 backdrop-blur-lg"
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
                @keydown.enter.stop="
                    //@ts-ignore
                    select.selectedItem = select.selectableItemActive;
                    select.toggleSelect(false);
                "
                @keydown.stop="select.selectKeydown($event)"
            >
                <ul ref="selectableItemsList" class="w-full overflow-auto max-h-56 scrollbar-thin focus:outline-none" tabindex="-1" role="listbox">
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
                            :tabindex="select.selectableItemIsActive(item) ? 0 : -1"
                            :class="{
                                'bg-neutral-100 dark:bg-neutral-900/70 text-gray-900 dark:text-neutral-100': select.selectableItemIsActive(item),
                                'text-gray-700 dark:text-neutral-300': !select.selectableItemIsActive(item),
                            }"
                            class="focus:rounded-md relative flex items-center h-full py-2 pl-8 cursor-pointer data-[disabled=true]:opacity-50 data-[disabled=true]:pointer-events-none"
                            role="option"
                            :aria-selected="select.selectableItemIsActive(item) ?? false"
                        >
                            <svg
                                v-if="
                                    //@ts-ignore
                                    select.selectedItem.value == item.value
                                "
                                class="absolute left-0 w-4 h-4 ml-2 stroke-current text-neutral-400"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            <span class="block font-medium truncate">{{ item.title }}</span>
                        </li>
                    </template>
                </ul>
            </OnClickOutside>
        </Transition>
    </section>
</template>
