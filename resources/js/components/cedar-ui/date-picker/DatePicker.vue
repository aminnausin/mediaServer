<script setup lang="ts">
import { CedarCalendar, CedarChevronLeft, CedarChevronRight } from '../icons';
import { nextTick, useTemplateRef, watch } from 'vue';
import { ButtonIcon, ButtonText } from '../button';
import { OnClickOutside } from '@vueuse/components';
import { UseFocusTrap } from '@vueuse/integrations/useFocusTrap/component';
import { cn } from '@aminnausin/cedar-ui';

import { ButtonDatePicker, useDatePicker } from '.';
import { InputShell } from '../input';

const props = defineProps<{ field: any; disabled?: boolean }>();

const datePickerInput = useTemplateRef('datePickerInput');
const datePickerCalendar = useTemplateRef('datePickerCalendar');

const model = defineModel<string | null>();

const {
    datePickerOpen,
    datePickerPanel,
    toggleDatePicker,
    datePickerDays,
    datePickerMonthVerbose,
    datePickerDay,
    datePickerMonth,
    datePickerYear,
    datePickerDecade,
    datePickerPosition,
    datePickerMonthNames,
    datePickerPrevious,
    datePickerNext,
    datePickerIsSelectedMonth,
    datePickerIsSelectedDate,
    datePickerIsToday,
    datePickerDaysInMonth,
    datePickerBlankDaysInMonth,
    datePickerValueClicked,
    showDatePickerPanel,
} = useDatePicker({ model }, datePickerInput, datePickerCalendar);

async function focusInitialElement() {
    await nextTick();

    if (!datePickerCalendar.value) return;

    const el: HTMLElement | null = datePickerCalendar.value.querySelector('[data-initial-focus=true]');
    el?.focus({ preventScroll: true });
}

function shouldFocusDay(day: number, index: number): boolean {
    if (datePickerIsSelectedDate(day)) return true; // its the selected date
    if (datePickerIsToday(day) && !datePickerDay) return true; // its today and either no day is selected
    if (!datePickerIsSelectedMonth() && index === 0) return true; // its a month that isnt current and isnt selected
    return false;
}

watch(datePickerOpen, (open) => {
    if (!open) return;

    focusInitialElement();
});

watch(datePickerPanel, () => {
    focusInitialElement();
});
</script>

<template>
    <OnClickOutside class="group relative text-sm" @trigger="toggleDatePicker(false)">
        <slot name="trigger">
            <InputShell>
                <template #input="{ class: inputClass }">
                    <input
                        ref="datePickerInput"
                        @keydown.space.prevent="toggleDatePicker()"
                        @keydown.enter.prevent="toggleDatePicker()"
                        @click="toggleDatePicker()"
                        @keydown.esc="toggleDatePicker(false)"
                        v-model="model"
                        type="text"
                        :id="field.name"
                        :name="field.name"
                        :title="field.text ?? field.name"
                        :required="field.required"
                        :disabled="field.disabled"
                        :placeholder="field?.placeholder ?? 'Select Date'"
                        :aria-autocomplete="field.autocomplete ? 'list' : 'none'"
                        :class="['mt-1 pe-12', { 'placeholder:text-foreground-2': datePickerOpen, 'button-disabled': disabled }, inputClass]"
                        readonly
                    />
                </template>
            </InputShell>
        </slot>
        <ButtonIcon
            @click="
                toggleDatePicker();
                if (datePickerOpen && datePickerInput) {
                    datePickerInput?.focus();
                }
            "
            variant="ghost"
            :class="[
                'text-foreground-3 group-hover:text-foreground-2 absolute top-0 right-0 h-full cursor-pointer rounded-l-none px-3',
                'hocus:text-foreground-0 focus:ring-primary ring-inset hover:bg-transparent focus:bg-transparent focus:ring-2', // Clear colour
                { 'text-foreground-0 bg-inherit': datePickerOpen },
            ]"
        >
            <CedarCalendar class="aspect-square h-full shrink-0" />
        </ButtonIcon>
        <Transition enter-from-class="opacity-0" enter-to-class="opacity-100" leave-from-class="opacity-100" leave-active-class="duration-100" leave-to-class="opacity-0">
            <UseFocusTrap v-if="datePickerOpen">
                <div
                    ref="datePickerCalendar"
                    :class="[
                        'absolute left-0 z-30 w-full max-w-68 p-4',
                        'rounded-md shadow-xs transition ease-in-out',
                        'text-foreground bg-overlay border-overlay-border border',
                        `${datePickerPosition === 'top' ? 'bottom-0 mb-12' : 'top-0 mt-12'}`,
                    ]"
                >
                    <div class="mb-2 flex items-center justify-between">
                        <div class="flex text-lg *:px-2">
                            <ButtonText v-if="datePickerPanel === 'D'" variant="ghost" @click="showDatePickerPanel('M')" class="hocus:bg-overlay-accent font-bold" :title="'Month'">
                                {{ datePickerMonthVerbose }}
                            </ButtonText>

                            <h3 v-if="datePickerPanel === 'Y'" aria-label="Year Range">
                                {{ Math.floor(datePickerYear / 10) * 10 }} - {{ Math.floor(datePickerYear / 10) * 10 + 10 }}
                            </h3>

                            <ButtonText
                                v-else
                                variant="ghost"
                                @click="showDatePickerPanel('Y')"
                                :class="['hocus:bg-overlay-accent', { 'text-foreground-1 dark:text-neutral-200': datePickerPanel === 'D' }]"
                                :title="'Year'"
                            >
                                {{ datePickerYear }}
                            </ButtonText>
                        </div>
                        <div class="text-foreground-3 dark:text-neutral-200">
                            <ButtonIcon variant="ghost" class="hocus:bg-overlay-accent inline-flex rounded-full p-0" :title="'Previous Page'" @click="datePickerPrevious()">
                                <CedarChevronLeft class="size-6" />
                            </ButtonIcon>
                            <ButtonIcon variant="ghost" class="hocus:bg-overlay-accent inline-flex rounded-full p-0" :title="'Next Page'" @click="datePickerNext()">
                                <CedarChevronRight class="size-6" />
                            </ButtonIcon>
                        </div>
                    </div>
                    <div class="grid grid-cols-7" v-if="datePickerPanel === 'D'">
                        <p v-for="(day, index) in datePickerDays" :key="index" class="text-foreground-0 dark:text-foreground-1 mb-2 text-center text-xs font-medium">
                            {{ day }}
                        </p>
                        <span v-for="(_, index) in datePickerBlankDaysInMonth" :key="index"></span>
                        <div v-for="(day, dayIndex) in datePickerDaysInMonth" class="aspect-square leading-none" :key="dayIndex">
                            <ButtonDatePicker
                                class="mx-auto aspect-square size-7 rounded-full p-0"
                                :value="day"
                                :is-selected="datePickerIsSelectedDate(day)"
                                :is-default="datePickerIsToday(day)"
                                :data-initial-focus="shouldFocusDay(day, dayIndex)"
                                @click="datePickerValueClicked(day)"
                            />
                        </div>
                    </div>

                    <div v-if="datePickerPanel === 'M'" class="grid grid-cols-3 gap-2">
                        <ButtonDatePicker
                            v-for="(month, index) in datePickerMonthNames"
                            :key="month"
                            :value="index"
                            :is-selected="datePickerMonth === index"
                            :data-initial-focus="datePickerMonth === index"
                            @click="datePickerValueClicked(index)"
                        >
                            {{ month.slice(0, 3) }}
                        </ButtonDatePicker>
                    </div>
                    <div v-if="datePickerPanel === 'Y'" class="grid grid-cols-3 grid-rows-4 gap-2 px-4">
                        <ButtonDatePicker
                            v-for="year in datePickerDecade"
                            :key="year"
                            :value="year"
                            :is-selected="datePickerYear === year"
                            :data-initial-focus="datePickerYear === year"
                            @click="datePickerValueClicked(year)"
                        />
                    </div>
                </div>
            </UseFocusTrap>
        </Transition>
    </OnClickOutside>
</template>
