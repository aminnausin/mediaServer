<script setup>
import { onMounted, ref, watch } from 'vue';
import { OnClickOutside } from '@vueuse/components';
import { UseFocusTrap } from '@vueuse/integrations/useFocusTrap/component.mjs';

import useMultiSelect from '../../composables/useMultiSelect';
import ButtonIcon from '../inputs/ButtonIcon.vue';
import TextInput from '../inputs/TextInput.vue';
import ChipTag from '../labels/ChipTag.vue';

import MdiLightPlus from '~icons/mdi-light/plus';

// import CircumCirclePlus from '~icons/circum/circle-plus';

const props = defineProps({
    placeholder: {
        type: String,
        default: 'Select Item',
    },
    defaultItems: {
        type: Array,
        default: () => [],
    },
    options: {
        type: Array,
        default: () => {
            return [
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
            ];
        },
    },
    max: {
        type: Number,
        default: 32,
    },
});

const emit = defineEmits(['createAction', 'selectItems', 'removeAction']);
const selectButton = ref(null);
const selectableItemsList = ref(null);
const select = useMultiSelect(props, { selectableItemsList, selectButton });
const newValue = ref('');

const handleItemClick = (item, setFocus = true, triggerSelect = true) => {
    if (!item?.name) return;

    select.selectedItems = [...select.selectedItems, item];
    select.toggleSelect(false);

    if (setFocus) selectButton.value?.focus();
    if (triggerSelect) emit('selectItems', select.selectedItems);
};

const handleRemoveChip = (name) => {
    const item = select.selectedItems.find((item) => item.name === name);
    select.selectedItems = select.selectedItems.filter((item) => item.name !== name);

    emit('removeAction', item);
};

const handleCreate = (e) => {
    e.preventDefault();
    e.stopPropagation();
    if (!newValue.value) return;
    emit('createAction', newValue.value);
    newValue.value = '';
};

onMounted(() => {
    if (props.defaultItems != undefined && props.defaultItems < props.options.length && props.defaultItems >= 0) {
        handleItemClick(props.options[props.defaultItems], false);
    }
});

watch(
    selectButton,
    () => {
        select.selectButton = selectButton;
    },
    { immediate: true },
);
watch(
    selectableItemsList,
    () => {
        select.selectableItemsList = selectableItemsList;
    },
    { immediate: true },
);

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

watch(
    props.defaultItems,
    () => {
        select.selectedItems = props.defaultItems ?? [];
    },
    { immediate: false },
);
watch(
    props.options,
    () => {
        select.selectableItems = props.options;
    },
    { immediate: false },
);
</script>
<template>
    <div
        @keydown.esc="
            if (select.selectOpen) {
                select.toggleSelect(false);
            }
        "
        @keydown.down="
            if (select.selectOpen) {
                select.selectableItemActiveNext();
            } else {
                select.toggleSelect(true);
            }
            event.preventDefault();
        "
        @keydown.up="
            if (select.selectOpen) {
                select.selectableItemActivePrevious();
            } else {
                select.toggleSelect(true);
            }
            event.preventDefault();
        "
        @keydown.enter="handleItemClick(select.selectableItemActive)"
        @keydown.space="handleItemClick(select.selectableItemActive)"
        @keydown="select.selectKeydown($event)"
        class="relative"
    >
        <OnClickOutside @trigger="select.toggleSelect(false)" class="relative">
            <button
                ref="selectButton"
                @click="select.toggleSelect()"
                :class="
                    'relative h-10 py-2 pl-3 pr-10 rounded-md shadow-sm mt-1 w-full flex items-center justify-between' +
                    'focus:outline-none border-none cursor-pointer ' +
                    'disabled:cursor-not-allowed disabled:opacity-50 ' +
                    'text-left text-gray-900 dark:text-neutral-100 bg-white dark:bg-neutral-700 placeholder:text-neutral-400 ' +
                    'ring-inset ring-[1px] ring-neutral-200 dark:ring-neutral-700 ' +
                    `${select.selectOpen ? 'hocus:ring-0' : 'hocus:ring-[0.125rem]'} hover:ring-violet-400 hover:dark:ring-violet-700 focus:ring-indigo-400 dark:focus:ring-indigo-500 focus:outline-none`
                "
                type="button"
            >
                <span class="truncate">{{ placeholder }}</span>
                <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        aria-hidden="true"
                        class="w-5 h-5 text-gray-400"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M10 3a.75.75 0 01.55.24l3.25 3.5a.75.75 0 11-1.1 1.02L10 4.852 7.3 7.76a.75.75 0 01-1.1-1.02l3.25-3.5A.75.75 0 0110 3zm-3.76 9.2a.75.75 0 011.06.04l2.7 2.908 2.7-2.908a.75.75 0 111.1 1.02l-3.25 3.5a.75.75 0 01-1.1 0l-3.25-3.5a.75.75 0 01.04-1.06z"
                            clip-rule="evenodd"
                        ></path>
                    </svg>
                </span>
            </button>

            <Transition
                enter-active-class="transition ease-out duration-50"
                enter-from-class="opacity-0 -translate-y-1"
                enter-to-class="opacity-100"
            >
                <ul
                    v-show="select.selectOpen"
                    ref="selectableItemsList"
                    :class="{
                        'bottom-0 mb-11': select.selectDropdownPosition == 'top',
                        'top-0 mt-11': select.selectDropdownPosition == 'bottom',
                    }"
                    class="z-30 absolute w-full mt-1 overflow-auto text-sm rounded-md shadow-md max-h-56 focus:outline-none ring-1 ring-opacity-5 ring-black dark:ring-neutral-700 bg-white dark:bg-neutral-800/70 backdrop-blur-lg"
                    v-cloak
                >
                    <UseFocusTrap v-if="select.selectOpen" :options="{ immediate: true }">
                        <li class="p-2 flex gap-2 w-full">
                            <TextInput
                                :placeholder="'Add a new tag here'"
                                tabindex="548"
                                v-model="newValue"
                                :maxlength="props.max"
                                @keydown.enter="handleCreate"
                                @keydown.space="handleCreate"
                            />
                            <ButtonIcon :type="'button'" tabindex="549" :disabled="!newValue" @click="handleCreate">
                                <template #icon>
                                    <MdiLightPlus class="w-6 h-6" />
                                </template>
                            </ButtonIcon>
                        </li>
                        <template
                            v-for="(item, index) in select.selectableItems.filter(
                                (selectable) => !select.selectedItems?.find((selected) => selectable.name === selected.name),
                            )"
                            :key="item.value"
                        >
                            <li
                                @click="handleItemClick(item)"
                                :id="index + '-' + select.selectId"
                                :data-disabled="item.disabled ? item.disabled : ''"
                                :class="{
                                    'bg-neutral-100 dark:bg-neutral-900/70 text-gray-900 dark:text-neutral-100':
                                        select.selectableItemActive === item,
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
                    </UseFocusTrap>
                </ul>
            </Transition>
        </OnClickOutside>
        <span v-if="select.selectedItems?.length > 0" class="flex flex-wrap gap-1 pt-2 pb-1">
            <ChipTag
                v-for="(chip, index) in select.selectedItems"
                v-bind:key="index"
                :label="chip.name"
                :removeable="true"
                @clickAction="handleRemoveChip(chip.name)"
            />
        </span>
    </div>
</template>
