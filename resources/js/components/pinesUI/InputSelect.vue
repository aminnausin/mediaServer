<script setup>
import useSelect from '../../composables/useSelect';

import { onMounted, ref, watch } from 'vue';
import { OnClickOutside } from '@vueuse/components'

const props = defineProps({
    // label: {
    //     type: String,
    //     default: 'Select:'
    // },
    placeholder: {
        type: String,
        default: 'Select Item'
    },
    defaultItem: {
        type: Number,
        default: null
    },
    options: {
        type: Array,
        default: () => {
            return [
                {
                    title: 'Title',
                    value: "title",
                    disabled: false
                },
                {
                    title: 'Date Uploaded',
                    value: "date",
                    disabled: false
                },
                {
                    title: 'Date released',
                    value: "date_released",
                    disabled: false
                },
                {
                    title: 'Episode',
                    value: "episode",
                    disabled: true
                },
                {
                    title: 'Season',
                    value: "season",
                    disabled: true
                },
            ];
        }
    }
})

const emit = defineEmits(['selectItem']);
const selectButton = ref(null);
const selectableItemsList = ref(null);
const select = useSelect(props.options, { selectableItemsList, selectButton });

const handleItemClick = (item, setFocus = true) => {
    select.selectedItem = item; 
    select.toggleSelect(false); 
    if(setFocus) selectButton.value?.focus();
    emit('selectItem', select.selectedItem);
}

onMounted(() => {
    if(props.defaultItem != undefined && props.defaultItem < props.options.length && props.defaultItem >=0){
        handleItemClick(props.options[props.defaultItem], false);
    }
})

watch(selectButton, () => { select.selectButton = selectButton; }, { immediate: true })
watch(selectableItemsList, () => { select.selectableItemsList = selectableItemsList; }, { immediate: true })
</script>
<template>
    <div @keydown.escape="if (select.selectOpen) { select.toggleSelect(false); }"
        @keydown.down="if (select.selectOpen) { select.selectableItemActiveNext(); } else { select.toggleSelect(true); } event.preventDefault();"
        @keydown.up="if (select.selectOpen) { select.selectableItemActivePrevious(); } else { select.toggleSelect(true); } event.preventDefault();"
        @keydown.enter="select.selectedItem = select.selectableItemActive; select.toggleSelect(false);"
        @keydown="select.selectKeydown($event);" class="relative">

        <OnClickOutside @trigger="select.toggleSelect(false);">
            <button ref="selectButton" @click="select.toggleSelect();"
                :class="{ 'hocus:ring-0': select.selectOpen }"
                class="relative h-10 flex items-center justify-between w-full py-2 pl-3 pr-10 text-left rounded-md shadow-sm cursor-default text-sm border-none focus:outline-none ring-inset ring-[1px] ring-neutral-200 dark:ring-neutral-700 hocus:ring-[0.125rem] hover:ring-violet-400 hover:dark:ring-violet-700 focus:ring-indigo-400 dark:focus:ring-indigo-500 text-gray-900 dark:text-neutral-100 bg-white dark:bg-neutral-800">
                <span class="truncate">{{ select.selectedItem ? select.selectedItem.title : placeholder }}</span>
                <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"
                        class="w-5 h-5 text-gray-400">
                        <path fill-rule="evenodd"
                            d="M10 3a.75.75 0 01.55.24l3.25 3.5a.75.75 0 11-1.1 1.02L10 4.852 7.3 7.76a.75.75 0 01-1.1-1.02l3.25-3.5A.75.75 0 0110 3zm-3.76 9.2a.75.75 0 011.06.04l2.7 2.908 2.7-2.908a.75.75 0 111.1 1.02l-3.25 3.5a.75.75 0 01-1.1 0l-3.25-3.5a.75.75 0 01.04-1.06z"
                            clip-rule="evenodd"></path>
                    </svg>
                </span>
            </button>

            <Transition 
                enter-active-class="transition ease-out duration-50" 
                enter-from-class="opacity-0 -translate-y-1"
                enter-to-class="opacity-100">
                <ul v-show="select.selectOpen" ref="selectableItemsList"
                    
                    :class="{ 'bottom-0 mb-10': select.selectDropdownPosition == 'top', 'top-0 mt-10': select.selectDropdownPosition == 'bottom' }"
                    class="z-30 absolute w-full mt-1 overflow-auto text-sm rounded-md shadow-md max-h-56 focus:outline-none ring-1 ring-opacity-5 ring-black dark:ring-neutral-700 bg-white dark:bg-neutral-800"
                    v-cloak>

                    <template v-for="item in select.selectableItems" :key="item.value">
                        <li @click="handleItemClick(item)" :id="item.value + '-' + select.selectId"
                            :data-disabled="item.disabled ? item.disabled : ''"
                            :class="{ 'bg-neutral-100 dark:bg-neutral-900 text-gray-900 dark:text-neutral-100': select.selectableItemIsActive(item), 'text-gray-700 dark:text-neutral-300': !select.selectableItemIsActive(item) }"
                            @mousemove="select.selectableItemActive = item"
                            class="relative flex items-center h-full py-2 pl-8 cursor-default select-none data-[disabled=true]:opacity-50 data-[disabled=true]:pointer-events-none">
                            <svg v-if="select.selectedItem.value == item.value"
                                class="absolute left-0 w-4 h-4 ml-2 stroke-current text-neutral-400"
                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            <span class="block font-medium truncate">{{ item.title }}</span>
                        </li>
                    </template>

                </ul>
            </Transition>
        </OnClickOutside>
    </div>
</template>