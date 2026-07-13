import type { ModelRef, Ref } from 'vue';

import { computed, nextTick, onMounted, ref, watch } from 'vue';

export type DatePickerFormat = 'F d, Y' | 'd M, Y' | 'Y M d' | 'MM-DD-YYYY' | 'DD-MM-YYYY' | 'YYYY-MM-DD' | 'D d M, Y';
interface DatePickerProps {
    model?: ModelRef<string | undefined | null>;
    defaultDate?: string;
    useDefaultDate?: boolean;
    format?: DatePickerFormat;
}

function toISODate(date: Date): string {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

function parseISODate(iso?: string | null): Date | null {
    if (!iso || iso === null) return null;

    const match = /^(\d{4})-(\d{2})-(\d{2})/.exec(iso);
    if (!match) return null;
    const [, y, m, d] = match;

    return new Date(Number(y), Number(m) - 1, Number(d));
}

export default function useDatePicker(props: DatePickerProps, datePickerInput: Ref<HTMLElement | null>, datePickerCalendar: Ref<HTMLElement | null>) {
    const datePickerMonth = ref(0);
    const datePickerYear = ref(0);
    const datePickerDay = ref(0);

    const datePickerOpen = ref(false);
    const datePickerValue = ref('');
    const datePickerFormat = ref<'F d, Y' | 'd M, Y' | 'Y M d' | 'MM-DD-YYYY' | 'DD-MM-YYYY' | 'YYYY-MM-DD' | 'D d M, Y'>(props.format ?? 'F d, Y');

    const datePickerDaysInMonth = ref<number[]>([]);
    const datePickerBlankDaysInMonth = ref<number[]>([]);
    const datePickerPosition = ref<'top' | 'bottom'>('bottom');

    const datePickerMonthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    const datePickerDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    const datePickerPanel = ref<'Y' | 'M' | 'D'>('D');

    const datePickerMonthVerbose = computed(() => {
        let index = Math.max(datePickerMonth.value, 0);
        if (index >= datePickerMonthNames.length) index = 0;
        return datePickerMonthNames[index];
    });

    const datePickerDecade = computed(() => {
        const startYear = Math.floor(datePickerYear.value / 10) * 10;
        return Array.from({ length: 10 }, (_, i) => startYear + i);
    });

    function toggleDatePicker(state?: boolean) {
        datePickerOpen.value = state ?? !datePickerOpen.value;
        datePickerPanel.value = 'D';
    }

    function showDatePickerPanel(panel: 'Y' | 'M' | 'D' = 'D') {
        datePickerPanel.value = panel;
    }

    function datePickerFormatDate(date: Date): string {
        const formattedDay = datePickerDays[date.getDay()];
        const formattedDate = ('0' + date.getDate()).slice(-2);
        const formattedMonth = datePickerMonthNames[date.getMonth()];
        const formattedMonthShortName = formattedMonth.substring(0, 3);
        const formattedMonthNumber = ('0' + (date.getMonth() + 1)).slice(-2);
        const formattedYear = date.getFullYear();

        switch (datePickerFormat.value) {
            case 'F d, Y':
                return `${formattedMonth} ${formattedDate}, ${formattedYear}`;
            case 'd M, Y':
                return `${formattedDate} ${formattedMonthShortName}, ${formattedYear}`;
            case 'Y M d':
                return `${formattedYear} ${formattedMonthShortName} ${formattedDate}`;
            case 'MM-DD-YYYY':
                return `${formattedMonthNumber}-${formattedDate}-${formattedYear}`;
            case 'DD-MM-YYYY':
                return `${formattedDate}-${formattedMonthNumber}-${formattedYear}`;
            case 'YYYY-MM-DD':
                return `${formattedYear}-${formattedMonthNumber}-${formattedDate}`;
            case 'D d M, Y':
                return `${formattedDay} ${formattedDate} ${formattedMonthShortName} ${formattedYear}`;
            default:
                return `${formattedMonth} ${formattedDate}, ${formattedYear}`;
        }
    }

    function datePickerValueClicked(value?: number) {
        if (!value) {
            datePickerValue.value = '';
            datePickerOpen.value = false;

            if (props.model) props.model.value = '';

            return;
        }

        switch (datePickerPanel.value) {
            case 'Y':
                datePickerYear.value = value;
                datePickerPanel.value = 'M';
                break;
            case 'M':
                datePickerMonth.value = value;
                datePickerPanel.value = 'D';
                calculateDays();
                break;

            default: {
                datePickerDay.value = value;

                const selectedDate = new Date(datePickerYear.value, datePickerMonth.value, value);

                datePickerValue.value = datePickerFormatDate(selectedDate);
                datePickerOpen.value = false;

                if (props.model) props.model.value = toISODate(selectedDate);
                break;
            }
        }
    }

    function datePickerPrevious() {
        switch (datePickerPanel.value) {
            case 'Y':
                datePickerYear.value -= 10;
                break;
            case 'M':
                datePickerYear.value -= 1;
                break;
            default:
                if (datePickerMonth.value === 0) {
                    datePickerYear.value--;
                    datePickerMonth.value = 11;
                } else {
                    datePickerMonth.value--;
                }
                calculateDays();
                break;
        }
    }

    function datePickerNext() {
        switch (datePickerPanel.value) {
            case 'Y':
                datePickerYear.value += 10;

                break;
            case 'M':
                datePickerYear.value += 1;
                break;

            default:
                if (datePickerMonth.value === 11) {
                    datePickerMonth.value = 0;
                    datePickerYear.value++;
                } else {
                    datePickerMonth.value++;
                }
                calculateDays();
                break;
        }
    }

    /**
     * Is the date picker looking at the month of the currently selected value
     */
    function datePickerIsSelectedMonth() {
        if (!props.model?.value) return false;

        const selected = parseISODate(props.model.value);
        return selected ? selected.getMonth() === datePickerMonth.value && selected.getFullYear() === datePickerYear.value : false;
    }

    function datePickerIsSelectedDate(day: number) {
        if (!props.model?.value) return false;

        const selected = parseISODate(props.model.value);
        return selected ? selected.getFullYear() === datePickerYear.value && selected.getMonth() === datePickerMonth.value && selected.getDate() === day : false;
    }

    function datePickerIsToday(day: number) {
        const today = new Date();
        const d = new Date(datePickerYear.value, datePickerMonth.value, day);
        return today.toDateString() === d.toDateString();
    }

    function datePickerIsCurrentMonth() {
        if (!props.model?.value) return false;

        const today = new Date();
        const selected = parseISODate(props.model.value);
        return selected ? selected.getMonth() === today.getMonth() && selected.getFullYear() === today.getFullYear() : false;
    }

    function calculateDays() {
        const daysInMonth = new Date(datePickerYear.value, datePickerMonth.value + 1, 0).getDate();
        const dayOfWeek = new Date(datePickerYear.value, datePickerMonth.value).getDay();

        datePickerBlankDaysInMonth.value = Array.from({ length: dayOfWeek }, (_, i) => i + 1);
        datePickerDaysInMonth.value = Array.from({ length: daysInMonth }, (_, i) => i + 1);
    }

    function updatePosition() {
        if (!datePickerInput.value || !datePickerCalendar.value || !datePickerOpen.value) return;

        const inputRect = datePickerInput.value.getBoundingClientRect();
        const calendarHeight = datePickerCalendar.value.offsetHeight;
        const bottomPos = inputRect.top + inputRect.height + calendarHeight;

        datePickerPosition.value = bottomPos > window.innerHeight ? 'top' : 'bottom';
    }

    function setDate(date: Date, setDate: boolean = true, setValue?: boolean) {
        datePickerMonth.value = date.getMonth();
        datePickerYear.value = date.getFullYear();
        datePickerDay.value = date.getDate();

        if (setDate) {
            datePickerValue.value = datePickerFormatDate(date);
        }

        calculateDays();

        if (setValue && props.model) {
            props.model.value = toISODate(date);
            datePickerOpen.value = false;
        }
    }

    function resetDate() {
        const parsed = parseISODate(props.model?.value);
        if (parsed) return setDate(parsed);

        setDate(new Date(), !!props.useDefaultDate);
    }

    onMounted(() => {
        resetDate();
    });

    watch(datePickerOpen, async (open) => {
        if (!open) {
            window.removeEventListener('resize', updatePosition);
            return;
        }
        await nextTick();
        resetDate();
        updatePosition();
        window.addEventListener('resize', updatePosition);
    });

    watch(datePickerFormat, () => {
        if (!props.model?.value) return;
        const selected = parseISODate(props.model.value);
        if (selected) datePickerValue.value = datePickerFormatDate(selected);
    });

    return {
        datePickerInput,
        datePickerCalendar,
        datePickerOpen,
        datePickerValue,
        datePickerPanel,
        datePickerFormat,
        datePickerMonth,
        datePickerMonthVerbose,
        datePickerYear,
        datePickerDecade,
        datePickerDay,
        datePickerDaysInMonth,
        datePickerBlankDaysInMonth,
        datePickerMonthNames,
        datePickerDays,
        datePickerPosition,
        toggleDatePicker,
        datePickerValueClicked,
        datePickerPrevious,
        datePickerNext,
        datePickerIsSelectedMonth,
        datePickerIsSelectedDate,
        datePickerIsToday,
        datePickerIsCurrentMonth,
        showDatePickerPanel,
        setDate,
    };
}
