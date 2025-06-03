<script setup lang="ts">
import { computed, onMounted, ref, useTemplateRef, watch } from 'vue';
import { isInputLikeElement } from '@/service/util';
import { OnClickOutside } from '@vueuse/components';
import { UseFocusTrap } from '@vueuse/integrations/useFocusTrap/component.mjs';
import { toast } from '@/service/toaster/toastService';

import useMultiSelect from '@/composables/useMultiSelect';
import ButtonIcon from '@/components/inputs/ButtonIcon.vue';
import TextInput from '@/components/inputs/TextInput.vue';
import ChipTag from '@/components/labels/ChipTag.vue';

import MdiLightPlus from '~icons/mdi-light/plus';

interface SelectItem {
    id: number;
    name: string;
    relationships?: any;
    disabled?: boolean;
}

const props = withDefaults(
    defineProps<{
        class?: string;
        rootClass?: string;
        placeholder?: string;
        defaultItems?: SelectItem[];
        options?: SelectItem[];
        max?: number;
        disabled?: boolean;
        title?: string;
    }>(),
    {
        placeholder: 'Select Item',
        defaultItems: () => [],
        options: () => [],
        max: 32,
    },
);

const emit = defineEmits(['createAction', 'selectItems', 'removeAction']);
const selectButton = useTemplateRef('selectButton');
const selectInput = useTemplateRef('selectInput');
const selectableItemsList = useTemplateRef('selectableItemsList');
const select = useMultiSelect(props, { selectableItemsList, selectButton });
const newValue = ref('');
const lastActiveItemId = ref(-1);

const filteredItemsList = computed(() => {
    return (
        select.selectableItems.filter((selectable: SelectItem) => {
            return !select.selectedItems?.find((selected: SelectItem) => selectable.name === selected.name) && selectable.name.includes(newValue.value.toLocaleLowerCase());
        }) ?? []
    );
});

const handleItemClick = (item: any, setFocus = true, triggerSelect = true) => {
    if (!item?.name) return;

    select.selectedItems = [...select.selectedItems, item];
    select.toggleSelect(false);

    if (setFocus && selectButton.value) selectButton.value.focus();
    if (triggerSelect) emit('selectItems', select.selectedItems);
};

const handleItemHover = (item: any) => {
    select.selectableItemActive = item;
    lastActiveItemId.value = item.id;

    if (isInputLikeElement(document.activeElement as HTMLElement, '')) return;

    (document.activeElement as HTMLElement)?.blur();
};

const handleItemFocus = (item: any) => {
    select.selectableItemActive = item;
    lastActiveItemId.value = item.id;
    // select.selectScrollToActiveItem(item.id); => ISSUE: this blocks clicks and scrolls an item into view instead of registering a click. Only the centred items allow clicks.
};

const handleListFocus = () => {
    if (lastActiveItemId.value <= 0) return;

    const el = document.getElementById(lastActiveItemId.value + '-' + select.selectId);
    el?.focus();
};

const handleRemoveChip = (name: string) => {
    const item = select.selectedItems.find((item: SelectItem) => item.name === name);
    select.selectedItems = select.selectedItems.filter((item: SelectItem) => item.name !== name);

    emit('removeAction', item);
};

const handleCreate = (e: Event) => {
    e.preventDefault();
    e.stopPropagation();

    const parsedValue = newValue.value?.toLocaleLowerCase().trim();
    if (!parsedValue) return;

    if (select.selectableItemActive && (select.selectableItemActive as unknown as SelectItem).name === parsedValue) {
        handleItemClick(select.selectableItemActive);
        return;
    }

    if (select.selectedItems.some((item: SelectItem) => item.name === parsedValue)) {
        toast.info('This tag was already added');
        return;
    }

    const selectableFound = select.selectableItems.find((item: SelectItem) => item.name === parsedValue);
    if (selectableFound) {
        handleItemClick(selectableFound);
        return;
    }

    emit('createAction', parsedValue);

    newValue.value = '';
    select.toggleSelect(false);
};

const selectableItemActiveNext = async () => {
    if (!select.selectableItemActive) return;
    const index = filteredItemsList.value.indexOf(select.selectableItemActive);
    if (index + 1 < filteredItemsList.value.length) {
        select.selectableItemActive = filteredItemsList.value[index + 1];
        select.selectScrollToActiveItem(filteredItemsList.value[index + 1].id);
    }
};
const selectableItemActivePrevious = async () => {
    if (!select.selectableItemActive) return;
    const index = filteredItemsList.value.indexOf(select.selectableItemActive);
    if (index - 1 >= 0) {
        select.selectableItemActive = filteredItemsList.value[index - 1];
        select.selectScrollToActiveItem(filteredItemsList.value[index - 1].id);
    }
};

onMounted(() => {
    if (props.defaultItems != undefined && props.defaultItems.length < props.options.length) {
        // Default items is a list of selected tags
        // I assume this should handle click for all of the provided default items
        // idk where i got this code because this isn't PinesUI
        // idek if this should happen because it kind of worked without

        // haha this is inefficient
        select.selectedItems = [];
        props.options.forEach((element) => {
            if (props.defaultItems.find((item) => item.name === element.name)) {
                handleItemClick(element, false);
            }
        });
    }
});

watch([() => selectButton.value, () => selectableItemsList.value], () => {
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
                class="z-30 absolute w-full mt-1 text-sm rounded-md shadow-md focus:outline-none ring-1 ring-opacity-5 ring-black dark:ring-neutral-700 bg-white dark:bg-neutral-800/70 backdrop-blur-lg"
                :options="{ allowOutsideClick: true, initialFocus: selectInput?.$el, returnFocusOnDeactivate: false }"
            >
                <OnClickOutside
                    @trigger="select.toggleSelect(false)"
                    @keydown.esc.stop="
                        (event: Event) => {
                            if (select.selectOpen) {
                                select.toggleSelect(false);
                                event.stopPropagation();
                            }
                        }
                    "
                    @keydown.down.stop="
                        (event: Event) => {
                            if (select.selectOpen) {
                                selectableItemActiveNext();
                            } else {
                                select.toggleSelect(true);
                            }
                            event.preventDefault();
                        }
                    "
                    @keydown.up.stop="
                        (event: Event) => {
                            if (select.selectOpen) {
                                selectableItemActivePrevious();
                            } else {
                                select.toggleSelect(true);
                            }
                            event.preventDefault();
                        }
                    "
                    @keydown="select.selectKeydown($event)"
                    v-cloak
                >
                    <section class="p-2 flex gap-2 w-full" @focusin="lastActiveItemId = -1">
                        <TextInput
                            :placeholder="'Search for a tag'"
                            v-model="newValue"
                            :maxlength="props.max"
                            ref="selectInput"
                            role="combobox"
                            aria-autocomplete="list"
                            aria-controls="selectableItemsList"
                            :aria-expanded="select.selectOpen"
                            :aria-activedescendant="lastActiveItemId ? `${lastActiveItemId}-${select.selectId}` : null"
                            class="scroll-m-4"
                            @keydown.enter.stop="handleCreate"
                            @keydown.space.stop=""
                            @change="selectInput?.$el.scrollIntoView({ behavior: 'smooth', block: 'center' })"
                            @focus="selectButton?.scrollIntoView({ behavior: 'smooth', block: 'center' })"
                        />
                        <ButtonIcon :type="'button'" :disabled="!newValue" @click="handleCreate" class="ring-inset" title="Add a new tag">
                            <template #icon>
                                <MdiLightPlus class="w-6 h-6" />
                            </template>
                        </ButtonIcon>
                    </section>
                    <section
                        v-show="filteredItemsList.length == 0"
                        class="text-gray-700 dark:text-neutral-300 relative flex items-center h-full py-2 pl-8 select-none"
                        @focusin="lastActiveItemId = -1"
                    >
                        <span class="block truncate">No Results... Add New?</span>
                    </section>
                    <ul
                        class="max-h-48 overflow-auto scrollbar-thin last:rounded-b-md"
                        aria-describedby="selectable-items-list"
                        ref="selectableItemsList"
                        role="listbox"
                        @focusin="handleListFocus"
                    >
                        <template v-for="item in filteredItemsList" :key="item.value">
                            <li
                                @keydown.enter.prevent.stop="handleItemClick(select.selectableItemActive)"
                                @keydown.space.prevent.stop="handleItemClick(select.selectableItemActive)"
                                @click.prevent.stop="handleItemClick(item)"
                                @mousemove="handleItemHover(item)"
                                @focus="handleItemFocus(item)"
                                :id="item.id + '-' + select.selectId"
                                :data-disabled="item.disabled ? item.disabled : ''"
                                :class="{
                                    'bg-neutral-100 dark:bg-neutral-900/70 text-gray-900 dark:text-neutral-100': select.selectableItemActive === item,
                                    'text-gray-700 dark:text-neutral-300': !select.selectableItemActive === item,
                                }"
                                :tabindex="'0'"
                                class="relative flex items-center focus:rounded-md h-full py-2 pl-8 cursor-pointer select-none data-[disabled=true]:opacity-50 data-[disabled=true]:pointer-events-none"
                                role="option"
                                :aria-selected="select.selectableItemActive === item"
                            >
                                <span class="block truncate">{{ item.name }}</span>
                            </li>
                        </template>
                    </ul>
                </OnClickOutside>
            </UseFocusTrap>
        </Transition>
    </div>
    <span v-if="select.selectedItems?.length > 0" class="flex flex-wrap gap-1 pt-2 pb-1">
        <ChipTag v-for="(chip, index) in select.selectedItems" :key="index" :label="chip.name" :removeable="true" @clickAction="handleRemoveChip(chip.name)" />
    </span>
</template>
