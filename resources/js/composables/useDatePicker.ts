import { computed, nextTick, onMounted, ref, watch, type ModelRef, type Ref } from 'vue';

interface DatePickerProps {
    model?: ModelRef<string | undefined>;
    defaultDate?: string;
    useDefaultDate?: boolean;
}

export default function useDatePicker(props: DatePickerProps, datePickerInput: Ref<HTMLElement | null>, datePickerCalendar: Ref<HTMLElement | null>) {
    const datePickerOpen = ref(false);
    const datePickerValue = ref(props.model?.value ?? props.defaultDate ?? '');
    const datePickerFormat = ref<'F d, Y' | 'd M, Y' | 'Y M d' | 'MM-DD-YYYY' | 'DD-MM-YYYY' | 'YYYY-MM-DD' | 'D d M, Y'>('F d, Y');
    const datePickerMonth = ref(0);
    const datePickerYear = ref(0);
    const datePickerDay = ref(0);
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

    function datePickerValueClicked(value: number) {
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

            default:
                datePickerDay.value = value;
                datePickerValue.value = datePickerFormatDate(new Date(datePickerYear.value, datePickerMonth.value, value));
                datePickerOpen.value = false;

                if (props.model) {
                    props.model.value = datePickerValue.value;
                }
                break;
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

    function datePickerIsSelectedDate(day: number) {
        const d = new Date(datePickerYear.value, datePickerMonth.value, day);
        return datePickerValue.value === datePickerFormatDate(d);
    }

    function datePickerIsToday(day: number) {
        const today = new Date();
        const d = new Date(datePickerYear.value, datePickerMonth.value, day);
        return today.toDateString() === d.toDateString();
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

    function setDate(date: Date, setDate: boolean = true) {
        datePickerMonth.value = date.getMonth();
        datePickerYear.value = date.getFullYear();
        datePickerDay.value = date.getDate();

        if (setDate) datePickerValue.value = datePickerFormatDate(date);

        calculateDays();
    }

    function resetDate() {
        if (datePickerValue.value) {
            return setDate(new Date(Date.parse(datePickerValue.value)));
        }

        setDate(new Date(), props.useDefaultDate);
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
        datePickerIsSelectedDate,
        datePickerIsToday,
        showDatePickerPanel,
    };
}
