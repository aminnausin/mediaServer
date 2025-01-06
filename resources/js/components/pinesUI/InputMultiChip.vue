<script setup lang="ts">
import { onMounted, ref, useTemplateRef, watch } from 'vue';
import { OnClickOutside } from '@vueuse/components';
import { UseFocusTrap } from '@vueuse/integrations/useFocusTrap/component.mjs';

import useMultiSelect from '../../composables/useMultiSelect';
import ButtonIcon from '../inputs/ButtonIcon.vue';
import TextInput from '../inputs/TextInput.vue';
import ChipTag from '../labels/ChipTag.vue';

import MdiLightPlus from '~icons/mdi-light/plus';

// import CircumCirclePlus from '~icons/circum/circle-plus';

interface SelectItem {
    id: number;
    name: string;
    relationships?: any;
}

const props = withDefaults(
    defineProps<{
        class?: string;
        rootClass?: string;
        placeholder?: string;
        defaultItems?: SelectItem[];
        options?: {
            name: string;
            value: string;
            disabled?: boolean;
        }[];
        max?: number;
        disabled?: boolean;
        title?: string;
    }>(),
    {
        placeholder: 'Select Item',
        defaultItems: () => [],
        options: () => [
            {
                name: 'name',
                value: 'name',
                disabled: false,
            },
            {
                name: 'Date Uploaded',
                value: 'date',
                disabled: false,
            },
            {
                name: 'Date released',
                value: 'date_released',
                disabled: false,
            },
            {
                name: 'Episode',
                value: 'episode',
                disabled: false,
            },
            {
                name: 'Season',
                value: 'season',
                disabled: false,
            },
        ],
        max: 32,
    },
);

const emit = defineEmits(['createAction', 'selectItems', 'removeAction']);
const selectButton = useTemplateRef('selectButton');
const selectableItemsList = useTemplateRef('selectableItemsList');
const select = useMultiSelect(props, { selectableItemsList, selectButton });
const newValue = ref('');

const handleItemClick = (item: any, setFocus = true, triggerSelect = true) => {
    if (!item?.name) return;

    select.selectedItems = [...select.selectedItems, item];
    select.toggleSelect(false);

    if (setFocus && selectButton.value) selectButton.value.focus();
    if (triggerSelect) emit('selectItems', select.selectedItems);
};

const handleRemoveChip = (name: string) => {
    const item = select.selectedItems.find((item: SelectItem) => item.name === name);
    select.selectedItems = select.selectedItems.filter((item: SelectItem) => item.name !== name);

    emit('removeAction', item);
};

const handleCreate = (e: Event) => {
    e.preventDefault();
    e.stopPropagation();
    if (!newValue.value) return;
    emit('createAction', newValue.value);
    newValue.value = '';
};

onMounted(() => {
    if (props.defaultItems != undefined && props.defaultItems.length < props.options.length && props.defaultItems.length >= 0) {
        // Default items is a list of selected tags
        // I assume this should handle click for all of the provided default items
        // idk where i got this code because this isn't PinesUI
        // idek if this should happen because it kind of worked without
        select.selectedItems = [];
        props.options.forEach((element) => {
            if (props.defaultItems.find((item) => item.name === element.name)) {
                handleItemClick(element, false);
            }
        });
    }
});

watch(
    () => selectButton.value,
    (value) => {
        if (!value) return;
        select.selectButton = selectButton;
    },
    { immediate: true },
);
watch(
    () => selectableItemsList.value,
    (value) => {
        if (!value) return;
    },
    { immediate: true },
);

watch([() => selectButton.value, () => selectableItemsList.value], ([newSelectButton, newSelectableItemsList]) => {
    // console.log(newSelectButton, newSelectableItemsList, { selectButton, selectableItemsList });

    // if (!newSelectableItemsList) return;
    select.updateRefs({ selectButton, selectableItemsList });
});

watch(
    () => props.defaultItems,
    (newVal) => {
        select.selectedItems = newVal;
    },
    { immediate: true },
);
watch(
    () => props.options,
    (newVal) => {
        select.selectableItems = newVal;
    },
    { immediate: true },
);
</script>
<template>
    <div class="relative">
        <button
            ref="selectButton"
            @click="select.toggleSelect(true)"
            :class="
                'relative h-10 py-2 pl-3 pr-10 rounded-md shadow-sm mt-1 w-full flex items-center justify-between' +
                'focus:outline-none border-none cursor-pointer ' +
                'disabled:cursor-not-allowed disabled:opacity-50 ' +
                'text-left text-gray-900 dark:text-neutral-100 bg-white dark:bg-neutral-700 placeholder:text-neutral-400 ' +
                'ring-inset ring-1 ring-neutral-200 dark:ring-neutral-700 ' +
                `${select.selectOpen ? 'hocus:ring-0' : 'hocus:ring-[0.125rem]'} hover:ring-violet-400 hover:dark:ring-violet-700 focus:ring-indigo-400 dark:focus:ring-indigo-500 focus:outline-none`
            "
            :disabled="disabled"
            type="button"
            :title="title ?? 'Make Selection'"
        >
            <span class="truncate">{{ placeholder }}</span>
            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="w-5 h-5 text-gray-400">
                    <path
                        fill-rule="evenodd"
                        d="M10 3a.75.75 0 01.55.24l3.25 3.5a.75.75 0 11-1.1 1.02L10 4.852 7.3 7.76a.75.75 0 01-1.1-1.02l3.25-3.5A.75.75 0 0110 3zm-3.76 9.2a.75.75 0 011.06.04l2.7 2.908 2.7-2.908a.75.75 0 111.1 1.02l-3.25 3.5a.75.75 0 01-1.1 0l-3.25-3.5a.75.75 0 01.04-1.06z"
                        clip-rule="evenodd"
                    ></path>
                </svg>
            </span>
        </button>
        <Transition enter-active-class="transition ease-out duration-50" enter-from-class="opacity-0 -translate-y-1" enter-to-class="opacity-100">
            <UseFocusTrap
                v-if="select.selectOpen"
                :class="{
                    'bottom-0 mb-11': select.selectDropdownPosition == 'top',
                    'top-0 mt-11': select.selectDropdownPosition == 'bottom',
                }"
                class="z-30 absolute w-full mt-1 overflow-auto text-sm rounded-md shadow-md max-h-56 focus:outline-none ring-1 ring-opacity-5 ring-black dark:ring-neutral-700 bg-white dark:bg-neutral-800/70 backdrop-blur-lg"
                :options="{ allowOutsideClick: true }"
            >
                <OnClickOutside
                    @trigger.stop="select.toggleSelect(false)"
                    @keydown.esc="
                        (event: Event) => {
                            if (select.selectOpen) {
                                select.toggleSelect(false);
                                event.stopPropagation();
                            }
                        }
                    "
                    @keydown.down="
                        (event: Event) => {
                            if (select.selectOpen) {
                                select.selectableItemActiveNext();
                            } else {
                                select.toggleSelect(true);
                            }
                            event.preventDefault();
                        }
                    "
                    @keydown.up="
                        (event: Event) => {
                            if (select.selectOpen) {
                                select.selectableItemActivePrevious();
                            } else {
                                select.toggleSelect(true);
                            }
                            event.preventDefault();
                        }
                    "
                    @keydown.enter="handleItemClick(select.selectableItemActive)"
                    @keydown.space="handleItemClick(select.selectableItemActive)"
                    @keydown="select.selectKeydown($event)"
                    v-cloak
                >
                    <ul ref="selectableItemsList" class="max-h-56">
                        <li class="p-2 flex gap-2 w-full">
                            <TextInput
                                :placeholder="'Add a new tag here'"
                                tabindex="548"
                                v-model="newValue"
                                :maxlength="props.max"
                                @keydown.enter="handleCreate"
                                @keydown.space.stop="() => {}"
                            />
                            <ButtonIcon :type="'button'" tabindex="549" :disabled="!newValue" @click="handleCreate">
                                <template #icon>
                                    <MdiLightPlus class="w-6 h-6" />
                                </template>
                            </ButtonIcon>
                        </li>
                        <template
                            v-for="(item, index) in select.selectableItems.filter(
                                (selectable: SelectItem) => !select.selectedItems?.find((selected: SelectItem) => selectable.name === selected.name),
                            )"
                            :key="item.value"
                        >
                            <li
                                @click="handleItemClick(item)"
                                @focus="select.selectableItemActive = item"
                                :id="index + '-' + select.selectId"
                                :data-disabled="item.disabled ? item.disabled : ''"
                                :class="{
                                    'bg-neutral-100 dark:bg-neutral-900/70 text-gray-900 dark:text-neutral-100': select.selectableItemActive === item,
                                    'text-gray-700 dark:text-neutral-300': !select.selectableItemActive === item,
                                }"
                                @mousemove="
                                    () => {
                                        select.selectableItemActive = item;
                                    }
                                "
                                class="relative flex items-center h-full py-2 pl-8 cursor-pointer select-none data-[disabled=true]:opacity-50 data-[disabled=true]:pointer-events-none"
                                :tabindex="`55${index}`"
                            >
                                <span class="block font-medium truncate">{{ item.name }}</span>
                            </li>
                        </template>
                    </ul>
                </OnClickOutside>
            </UseFocusTrap>
        </Transition>
    </div>
    <span v-if="select.selectedItems?.length > 0" class="flex flex-wrap gap-1 pt-2 pb-1">
        <ChipTag v-for="(chip, index) in select.selectedItems" v-bind:key="index" :label="chip.name" :removeable="true" @clickAction="handleRemoveChip(chip.name)" />
    </span>
</template>
