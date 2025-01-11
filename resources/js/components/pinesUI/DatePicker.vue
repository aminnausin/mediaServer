<script setup>
import useDatePicker from '../../composables/useDatePicker';

import { ref, watch } from 'vue';
import { OnClickOutside } from '@vueuse/components';

const { field, tabindex } = defineProps(['field', 'tabindex']);

const model = defineModel();
const datePickerInput = ref(null);
const datePickerCalendar = ref(null);
const datePicker = useDatePicker({ model: model }, { datePickerInput, datePickerCalendar });

watch(
    datePickerInput,
    () => {
        datePicker.datePickerInput = datePickerInput;
    },
    { immediate: true },
);
watch(
    datePickerCalendar,
    () => {
        datePicker.selectableItemsList = datePickerCalendar;
    },
    { immediate: true },
);
</script>

<template>
    <OnClickOutside class="relative" @trigger="datePicker.toggleDatePicker(false)">
        <input
            ref="datePickerInput"
            :class="
                'h-10 px-3 py-2 rounded-md shadow-sm block mt-1 w-full ' +
                'focus:outline-none border-none ' +
                'disabled:cursor-not-allowed disabled:opacity-50 ' +
                'text-gray-900 dark:text-neutral-100 bg-white dark:bg-neutral-700 placeholder:text-neutral-400 ' +
                'ring-inset focus:ring-inset ring-1 ring-neutral-200 dark:ring-neutral-700 ' +
                'focus:ring-[0.125rem] focus:ring-indigo-400 dark:focus:ring-indigo-500'
            "
            @click="datePicker.toggleDatePicker()"
            @keydown.esc="datePicker.toggleDatePicker(false)"
            :name="field?.name"
            :title="field?.name"
            type="text"
            :required="field?.required"
            :placeholder="field?.placeholder ?? 'Select Date'"
            :aria-autocomplete="field?.autocomplete ? 'list' : 'none'"
            :tabindex="tabindex ?? 0"
            v-model="model"
            readonly
        />
        <div
            @click="
                datePicker.toggleDatePicker();
                if (datePicker.datePickerOpen) {
                    datePickerInput.focus();
                }
            "
            class="absolute top-0 right-0 px-3 py-2 cursor-pointer text-neutral-400 hover:text-neutral-500"
        >
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                />
            </svg>
        </div>
        <Transition
            enter-from-class="opacity-0 scale-95"
            enter-active-class="transform duration-150 ease-in-out"
            enter-to-class="opacity-1 scale-100"
            leave-from-class="opacity-1 scale-100"
            leave-active-class="opacity-0 transform scale-95 duration-75 ease-in-out"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="datePicker.datePickerOpen"
                :class="
                    'absolute left-0 rounded-md shadow-sm z-30 ' +
                    'p-4 antialiased max-w-[17rem] w-full ' +
                    'focus:outline-none border shadow border-neutral-200/70 dark:border-neutral-600 ' +
                    'disabled:cursor-not-allowed disabled:opacity-50 ' +
                    'text-gray-900 dark:text-neutral-100 bg-white dark:bg-neutral-700 placeholder:text-neutral-400 ' +
                    'ring-inset focus:ring-inset ring-1 ring-neutral-200 dark:ring-neutral-700 ' +
                    'focus:ring-[0.125rem] focus:ring-indigo-400 dark:focus:ring-indigo-500 ' +
                    `${datePicker.datePickerPosition === 'top' ? 'bottom-0 mb-12' : 'top-0 mt-12'}`
                "
                ref="datePickerCalendar"
            >
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <span class="text-lg font-bold dark:text-neutral-100">
                            {{ datePicker.datePickerMonthNames[datePicker.datePickerMonth] }}
                        </span>
                        <span class="ml-1 text-lg font-normal text-gray-600 dark:text-neutral-200">
                            {{ datePicker.datePickerYear }}
                        </span>
                    </div>
                    <div>
                        <button
                            @click="datePicker.datePickerPreviousMonth()"
                            type="button"
                            class="inline-flex p-1 transition duration-100 ease-in-out rounded-full cursor-pointer focus:outline-none focus:shadow-outline hover:bg-gray-100 dark:hover:bg-neutral-900"
                        >
                            <svg
                                class="inline-flex w-6 h-6 text-gray-400 dark:text-neutral-200"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <button
                            @click="datePicker.datePickerNextMonth()"
                            type="button"
                            class="inline-flex p-1 transition duration-100 ease-in-out rounded-full cursor-pointer focus:outline-none focus:shadow-outline hover:bg-gray-100 dark:hover:bg-neutral-900"
                        >
                            <svg
                                class="inline-flex w-6 h-6 text-gray-400 dark:text-neutral-200"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-7 mb-3">
                    <div v-for="(day, index) in datePicker.datePickerDays" :key="index" class="px-0.5">
                        <div class="text-xs font-medium text-center text-gray-800 dark:text-neutral-300">{{ day }}</div>
                    </div>
                </div>
                <div class="grid grid-cols-7">
                    <div
                        v-for="(blankDay, index) in datePicker.datePickerBlankDaysInMonth"
                        :key="index"
                        class="p-1 text-sm text-center border border-transparent"
                    ></div>
                    <div v-for="(day, dayIndex) in datePicker.datePickerDaysInMonth" :key="dayIndex" class="px-0.5 mb-1 aspect-square">
                        <div
                            @click="datePicker.datePickerDayClicked(day)"
                            :class="{
                                'bg-neutral-200 dark:bg-neutral-800/70 dark:hover:bg-neutral-900':
                                    datePicker.datePickerIsToday(day) == true,
                                'text-gray-600 dark:text-neutral-400 hover:bg-neutral-200 dark:hover:text-neutral-100 dark:hover:bg-neutral-900':
                                    datePicker.datePickerIsToday(day) == false && datePicker.datePickerIsSelectedDate(day) == false,
                                'bg-neutral-800 dark:bg-violet-700 text-white hover:bg-opacity-75 dark:bg-opacity-60':
                                    datePicker.datePickerIsSelectedDate(day) == true,
                            }"
                            class="flex items-center justify-center text-sm leading-none text-center rounded-full cursor-pointer h-7 w-7"
                        >
                            {{ day }}
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </OnClickOutside>
</template>
