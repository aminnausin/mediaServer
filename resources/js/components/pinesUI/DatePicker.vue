<script setup lang="ts">
import { useTemplateRef } from 'vue';
import { OnClickOutside } from '@vueuse/components';

import DatePickerValueButton from '@/components/pinesUI/DatePickerValueButton.vue';
import useDatePicker from '@/composables/useDatePicker';

const { field } = defineProps(['field']);

const datePickerInput = useTemplateRef('datePickerInput');
const datePickerCalendar = useTemplateRef('datePickerCalendar');

const model = defineModel<string>();

const {
    datePickerOpen,
    datePickerPanel,
    toggleDatePicker,
    datePickerDays,
    datePickerMonthVerbose,
    datePickerMonth,
    datePickerYear,
    datePickerDecade,
    datePickerPosition,
    datePickerMonthNames,
    datePickerPrevious,
    datePickerNext,
    datePickerIsSelectedDate,
    datePickerIsToday,
    datePickerDaysInMonth,
    datePickerBlankDaysInMonth,
    datePickerValueClicked,
    showDatePickerPanel,
} = useDatePicker({ model }, datePickerInput, datePickerCalendar);
</script>

<template>
    <OnClickOutside class="relative" @trigger="toggleDatePicker(false)">
        <input
            ref="datePickerInput"
            :class="[
                'h-10 px-3 py-2 rounded-md shadow-sm block mt-1 w-full text-sm',
                'focus:outline-none border-none',
                'disabled:cursor-not-allowed disabled:opacity-50',
                'text-gray-900 dark:text-neutral-100 bg-white dark:bg-neutral-700 placeholder:text-neutral-400',
                'ring-inset focus:ring-inset ring-1 ring-neutral-200 dark:ring-neutral-700',
                'focus:ring-[0.125rem] focus:ring-indigo-400 dark:focus:ring-indigo-500',
            ]"
            @click="toggleDatePicker()"
            @keydown.esc="toggleDatePicker(false)"
            :name="field?.name"
            :title="field?.name"
            type="text"
            :required="field?.required"
            :placeholder="field?.placeholder ?? 'Select Date'"
            :aria-autocomplete="field?.autocomplete ? 'list' : 'none'"
            v-model="model"
            readonly
        />
        <div
            @click="
                toggleDatePicker();
                if (datePickerOpen) {
                    datePickerInput?.focus();
                }
            "
            class="absolute top-0 right-0 px-3 py-2 cursor-pointer text-neutral-400 hover:text-neutral-500"
        >
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
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
                v-if="datePickerOpen"
                :class="[
                    'absolute left-0 rounded-md shadow-sm z-30 text-sm',
                    'p-4 antialiased max-w-[17rem] w-full',
                    'focus:outline-none border shadow border-neutral-200/70 dark:border-neutral-600',
                    'disabled:cursor-not-allowed disabled:opacity-50',
                    'text-gray-900 dark:text-neutral-100 bg-white dark:bg-neutral-700 placeholder:text-neutral-400',
                    'ring-inset focus:ring-inset ring-1 ring-neutral-200 dark:ring-neutral-700',
                    'focus:ring-[0.125rem] focus:ring-indigo-400 dark:focus:ring-indigo-500',
                    `${datePickerPosition === 'top' ? 'bottom-0 mb-12' : 'top-0 mt-12'}`,
                ]"
                ref="datePickerCalendar"
            >
                <div class="flex items-center justify-between mb-2">
                    <div class="flex text-lg [&>*]:px-2">
                        <button
                            v-if="datePickerPanel === 'D'"
                            class="font-bold dark:text-neutral-100 transition duration-100 ease-in-out rounded-lg cursor-pointer focus:outline-none focus:shadow-outline hover:bg-gray-100 dark:hover:bg-neutral-900"
                            type="button"
                            @click="showDatePickerPanel('M')"
                        >
                            {{ datePickerMonthVerbose }}
                        </button>

                        <h3 v-if="datePickerPanel === 'Y'">{{ Math.floor(datePickerYear / 10) * 10 }} - {{ Math.floor(datePickerYear / 10) * 10 + 10 }}</h3>

                        <button
                            v-else
                            @click="showDatePickerPanel('Y')"
                            type="button"
                            class="font-normal text-gray-600 dark:text-neutral-200 transition duration-100 ease-in-out rounded-lg cursor-pointer focus:outline-none focus:shadow-outline hover:bg-gray-100 dark:hover:bg-neutral-900"
                        >
                            {{ datePickerYear }}
                        </button>
                    </div>
                    <div>
                        <button
                            @click="datePickerPrevious()"
                            type="button"
                            class="inline-flex p-1 transition duration-100 ease-in-out rounded-full cursor-pointer focus:outline-none focus:shadow-outline hover:bg-gray-100 dark:hover:bg-neutral-900"
                        >
                            <svg class="inline-flex w-6 h-6 text-gray-400 dark:text-neutral-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <button
                            @click="datePickerNext()"
                            type="button"
                            class="inline-flex p-1 transition duration-100 ease-in-out rounded-full cursor-pointer focus:outline-none focus:shadow-outline hover:bg-gray-100 dark:hover:bg-neutral-900"
                        >
                            <svg class="inline-flex w-6 h-6 text-gray-400 dark:text-neutral-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-7 mb-3" v-if="datePickerPanel === 'D'">
                    <div v-for="(day, index) in datePickerDays" :key="index" class="px-0.5">
                        <div class="text-xs font-medium text-center text-gray-800 dark:text-neutral-300">{{ day }}</div>
                    </div>
                </div>
                <div class="grid grid-cols-7 text-sm" v-if="datePickerPanel === 'D'">
                    <div v-for="(blankDay, index) in datePickerBlankDaysInMonth" :key="index" class="p-1 text-center border border-transparent"></div>
                    <div v-for="(day, dayIndex) in datePickerDaysInMonth" :key="dayIndex" class="px-0.5 mb-1 aspect-square leading-none">
                        <div
                            @click="datePickerValueClicked(day)"
                            :class="{
                                'bg-neutral-200 dark:bg-neutral-800/70 dark:hover:bg-neutral-900': datePickerIsToday(day) == true,
                                'text-gray-600 dark:text-neutral-300 hover:bg-neutral-200 dark:hover:text-neutral-100 dark:hover:bg-neutral-900':
                                    datePickerIsToday(day) == false && datePickerIsSelectedDate(day) == false,
                                'bg-neutral-800 dark:bg-violet-700 text-white hover:bg-opacity-75 dark:bg-opacity-60': datePickerIsSelectedDate(day) == true,
                            }"
                            class="flex items-center justify-center text-center rounded-full cursor-pointer h-7 w-7"
                        >
                            {{ day }}
                        </div>
                    </div>
                </div>
                <div v-if="datePickerPanel === 'M'" class="grid grid-cols-3 gap-2">
                    <DatePickerValueButton
                        v-for="(month, index) in datePickerMonthNames"
                        :key="month"
                        :value="index"
                        :is-selected="datePickerMonth === index"
                        @click="datePickerValueClicked(index)"
                    >
                        <template #text>
                            {{ month.slice(0, 3) }}
                        </template>
                    </DatePickerValueButton>
                </div>
                <div v-if="datePickerPanel === 'Y'" class="grid grid-cols-3 grid-rows-4 gap-2 px-4">
                    <DatePickerValueButton
                        v-for="year in datePickerDecade"
                        :key="year"
                        :value="year"
                        :is-selected="datePickerYear === year"
                        @click="datePickerValueClicked(year)"
                    />
                </div>
            </div>
        </Transition>
    </OnClickOutside>
</template>
