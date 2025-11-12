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
                'mt-1 block h-10 w-full rounded-md px-3 py-2 text-sm shadow-xs',
                'border-none focus:outline-hidden',
                'disabled:cursor-not-allowed disabled:opacity-50',
                'bg-white text-gray-900 placeholder:text-neutral-400 dark:bg-neutral-700 dark:text-neutral-100',
                'ring-1 ring-neutral-200 ring-inset focus:ring-inset dark:ring-neutral-700',
                'focus:ring-2 focus:ring-indigo-400 dark:focus:ring-indigo-500',
            ]"
            @click="toggleDatePicker()"
            @keydown.esc="toggleDatePicker(false)"
            :name="field?.name"
            :title="field.text ?? field.name"
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
            class="absolute top-0 right-0 cursor-pointer px-3 py-2 text-neutral-400 hover:text-neutral-500"
        >
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                    'absolute left-0 z-30 rounded-md text-sm shadow-xs',
                    'w-full max-w-68 p-4 antialiased',
                    'border border-neutral-200/70 shadow-sm focus:outline-hidden dark:border-neutral-600',
                    'disabled:cursor-not-allowed disabled:opacity-50',
                    'bg-white text-gray-900 placeholder:text-neutral-400 dark:bg-neutral-700 dark:text-neutral-100',
                    'ring-1 ring-neutral-200 ring-inset focus:ring-inset dark:ring-neutral-700',
                    'focus:ring-2 focus:ring-indigo-400 dark:focus:ring-indigo-500',
                    `${datePickerPosition === 'top' ? 'bottom-0 mb-12' : 'top-0 mt-12'}`,
                ]"
                ref="datePickerCalendar"
            >
                <div class="mb-2 flex items-center justify-between">
                    <div class="flex text-lg *:px-2">
                        <button
                            v-if="datePickerPanel === 'D'"
                            class="focus:shadow-outline cursor-pointer rounded-lg font-bold transition duration-100 ease-in-out hover:bg-gray-100 focus:outline-hidden dark:text-neutral-100 dark:hover:bg-neutral-900"
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
                            class="focus:shadow-outline cursor-pointer rounded-lg font-normal text-gray-600 transition duration-100 ease-in-out hover:bg-gray-100 focus:outline-hidden dark:text-neutral-200 dark:hover:bg-neutral-900"
                        >
                            {{ datePickerYear }}
                        </button>
                    </div>
                    <div>
                        <button
                            @click="datePickerPrevious()"
                            type="button"
                            class="focus:shadow-outline inline-flex cursor-pointer rounded-full p-1 transition duration-100 ease-in-out hover:bg-gray-100 focus:outline-hidden dark:hover:bg-neutral-900"
                        >
                            <svg class="inline-flex h-6 w-6 text-gray-400 dark:text-neutral-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <button
                            @click="datePickerNext()"
                            type="button"
                            class="focus:shadow-outline inline-flex cursor-pointer rounded-full p-1 transition duration-100 ease-in-out hover:bg-gray-100 focus:outline-hidden dark:hover:bg-neutral-900"
                        >
                            <svg class="inline-flex h-6 w-6 text-gray-400 dark:text-neutral-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="mb-3 grid grid-cols-7" v-if="datePickerPanel === 'D'">
                    <div v-for="(day, index) in datePickerDays" :key="index" class="px-0.5">
                        <div class="text-center text-xs font-medium text-gray-800 dark:text-neutral-300">{{ day }}</div>
                    </div>
                </div>
                <div class="grid grid-cols-7 text-sm" v-if="datePickerPanel === 'D'">
                    <div v-for="(blankDay, index) in datePickerBlankDaysInMonth" :key="index" class="border border-transparent p-1 text-center"></div>
                    <div v-for="(day, dayIndex) in datePickerDaysInMonth" :key="dayIndex" class="mb-1 aspect-square px-0.5 leading-none">
                        <div
                            @click="datePickerValueClicked(day)"
                            :class="{
                                'bg-neutral-200 dark:bg-neutral-800/70 dark:hover:bg-neutral-900': datePickerIsToday(day) == true,
                                'text-gray-600 hover:bg-neutral-200 dark:text-neutral-300 dark:hover:bg-neutral-900 dark:hover:text-neutral-100':
                                    datePickerIsToday(day) == false && datePickerIsSelectedDate(day) == false,
                                'hover:bg-opacity-75 dark:bg-opacity-60 bg-neutral-800 text-white dark:bg-violet-700': datePickerIsSelectedDate(day) == true,
                            }"
                            class="flex h-7 w-7 cursor-pointer items-center justify-center rounded-full text-center"
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
