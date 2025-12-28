<script setup lang="ts">
import type { MultiSelectItem, MultiSelectProps } from '@aminnausin/cedar-ui';

import { computed, onMounted, ref, useTemplateRef, watch } from 'vue';
import { isInputLikeElement, toast, useMultiSelect } from '@aminnausin/cedar-ui';
import { CedarChevronUpDown, MdiLightPlus } from '@/components/cedar-ui/icons';
import { OnClickOutside } from '@vueuse/components';
import { UseFocusTrap } from '@vueuse/integrations/useFocusTrap/component.mjs';
import { ButtonIcon } from '@/components/cedar-ui/button';
import { TextInput } from '@/components/cedar-ui/input';
import { BadgeTag } from '@/components/cedar-ui/badge';

const props = withDefaults(defineProps<MultiSelectProps>(), {
    placeholder: 'Select Item',
    defaultItems: () => [],
    options: () => [],
    max: 32,
});

const emit = defineEmits(['createAction', 'selectItems', 'removeAction']);
const selectButton = useTemplateRef('selectButton');
const selectInput = useTemplateRef('selectInput');
const selectableItemsList = useTemplateRef('selectableItemsList');
const select = useMultiSelect(props, { selectableItemsList, selectButton });
const newValue = ref('');
const lastActiveItemId = ref(-1);

const filteredItemsList = computed(() => {
    return (
        select.selectableItems.filter((selectable: MultiSelectItem) => {
            return !select.selectedItems?.find((selected: MultiSelectItem) => selectable.name === selected.name) && selectable.name?.includes(newValue.value.toLocaleLowerCase());
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
    const item = select.selectedItems.find((item: MultiSelectItem) => item.name === name);
    select.selectedItems = select.selectedItems.filter((item: MultiSelectItem) => item.name !== name);

    emit('removeAction', item);
};

const handleCreate = (e: Event) => {
    e.preventDefault();
    e.stopPropagation();

    const parsedValue = newValue.value?.toLocaleLowerCase().trim();
    if (!parsedValue) return;

    if (select.selectableItemActive && (select.selectableItemActive as unknown as MultiSelectItem).name === parsedValue) {
        handleItemClick(select.selectableItemActive);
        return;
    }

    if (select.selectedItems.some((item: MultiSelectItem) => item.name === parsedValue)) {
        toast.info('This tag was already added');
        return;
    }

    const selectableFound = select.selectableItems.find((item: MultiSelectItem) => item.name === parsedValue);
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
    <div class="group relative mt-1">
        <button
            @click="select.toggleSelect(true)"
            :id="fieldName"
            :title="title ?? 'Make Selection'"
            :disabled="disabled"
            :class="[
                'transition duration-(--duration-input) ease-in-out focus:outline-hidden', // Animation
                'disabled:button-disabled disabled:button-disabled-pointer', // Disabled
                'relative flex items-center justify-between gap-2', // Layout
                'cursor-pointer rounded-md shadow-xs', // Style
                'h-10 max-h-full w-full py-2 pr-10 pl-3', // Size
                'bg-surface-2 dark:bg-neutral-700',
                'ring-r-button hocus:ring-2 ring-1',
                { 'hocus:ring-0': select.selectOpen },
                'hover:ring-primary-muted focus:ring-primary focus-within:ring-primary-muted',
                props.class,
            ]"
            ref="selectButton"
            type="button"
        >
            <span class="truncate">{{ placeholder }}</span>
            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                <slot name="selectButtonIcon">
                    <CedarChevronUpDown :class="['text-foreground-2 size-5']" />
                </slot>
            </span>
        </button>
        <Transition enter-from-class="opacity-0" enter-to-class="opacity-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <UseFocusTrap
                v-if="select.selectOpen"
                :class="{
                    'bottom-0 mb-11': select.selectDropdownPosition == 'top',
                    'top-0 mt-11': select.selectDropdownPosition == 'bottom',
                }"
                class="bg-overlay-t ring-r-button absolute z-30 mt-1 max-h-56 w-full overflow-clip rounded-md shadow-md ring-1 backdrop-blur-lg transition duration-(--duration-input) ease-in-out"
                :options="{
                    allowOutsideClick: true,
                    initialFocus: () => selectInput?.el,
                    returnFocusOnDeactivate: true,
                }"
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
                    @keydown.down.stop.prevent="
                        (event: Event) => {
                            if (select.selectOpen) {
                                selectableItemActiveNext();
                            } else {
                                select.toggleSelect(true);
                            }
                            event.preventDefault();
                        }
                    "
                    @keydown.up.stop.prevent="
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
                    <section class="flex w-full gap-2 p-2" @focusin="lastActiveItemId = -1">
                        <TextInput
                            v-model="newValue"
                            @focus="selectButton?.scrollIntoView({ behavior: 'smooth', block: 'center' })"
                            @change="selectInput?.$el.scrollIntoView({ behavior: 'smooth', block: 'center' })"
                            @keydown.enter.stop="handleCreate"
                            @keydown.space.stop=""
                            ref="selectInput"
                            role="combobox"
                            class="h-9 scroll-m-4 ring-inset"
                            aria-autocomplete="list"
                            aria-controls="selectableItemsList"
                            :placeholder="'Search for a tag'"
                            :aria-expanded="select.selectOpen ? 'true' : 'false'"
                            :aria-activedescendant="lastActiveItemId ? `${lastActiveItemId}-${select.selectId}` : null"
                            :maxlength="props.max"
                        />
                        <ButtonIcon :type="'button'" :disabled="!newValue" @click="handleCreate" class="inline-flex size-9 p-0 ring-inset" title="Add a new tag">
                            <template #icon>
                                <MdiLightPlus class="ms-0.5 size-6" />
                            </template>
                        </ButtonIcon>
                    </section>
                    <section v-show="filteredItemsList.length == 0" class="text-foreground-6 p-2 pl-8 select-none" @focusin="lastActiveItemId = -1">
                        <p class="block truncate">No Results... Add New?</p>
                    </section>
                    <ul
                        class="scrollbar-minimal max-h-48 overflow-auto last:rounded-b-md"
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
                                :class="[
                                    {
                                        'bg-overlay-accent dark:bg-overlay-accent/70': select.selectableItemActive === item,
                                        'text-foreground-6': !select.selectableItemActive === item,
                                    },
                                    'data-[disabled=true]:button-disabled data-[disabled=true]:button-disabled-pointer relative flex h-full cursor-pointer items-center py-2 pl-8 focus:rounded-md',
                                ]"
                                :tabindex="'0'"
                                role="option"
                                :aria-selected="select.selectableItemActive === item ? 'true' : 'false'"
                            >
                                <span class="block truncate">{{ item.name }}</span>
                            </li>
                        </template>
                    </ul>
                </OnClickOutside>
            </UseFocusTrap>
        </Transition>
    </div>
    <span v-if="select.selectedItems?.length > 0" class="flex h-fit flex-wrap gap-1 pt-2 pb-1">
        <BadgeTag
            v-for="(chip, index) in select.selectedItems"
            :key="index"
            :label="chip.name"
            :removeable="true"
            @clickAction="handleRemoveChip(chip.name)"
            :btn-class="'bg-inherit shadow-none'"
        />
    </span>
</template>
