import { nextTick, onMounted, reactive, watch } from "vue";

export default function useDatePicker(props, refs) {
    const datePicker = reactive({
        datePickerOpen: false,
        datePickerValue: props?.defaultDate ?? "",
        datePickerFormat: "M d, Y",
        datePickerMonth: "",
        datePickerYear: "",
        datePickerDay: "",
        datePickerDaysInMonth: [],
        datePickerBlankDaysInMonth: [],
        datePickerMonthNames: [
            "January",
            "February",
            "March",
            "April",
            "May",
            "June",
            "July",
            "August",
            "September",
            "October",
            "November",
            "December",
        ],
        datePickerDays: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
        datePickerInput: refs.datePickerInput,
        datePickerCalendar: refs.datePickerCalendar,
        datePickerPosition: "bottom",
        updateRefs(values) {
            this.datePickerInput = values.datePickerInput;
            this.datePickerCalendar = values.datePickerCalendar;
        },
        toggleDatePicker(state) {
            if (state !== undefined) this.datePickerOpen = state === true;
            else this.datePickerOpen = !this.datePickerOpen;
        },
        datePickerDayClicked(day) {
            let selectedDate = new Date(
                this.datePickerYear,
                this.datePickerMonth,
                day
            );
            this.datePickerDay = day;
            this.datePickerValue = this.datePickerFormatDate(selectedDate);
            this.datePickerIsSelectedDate(day);
            this.datePickerOpen = false;
        },
        datePickerPreviousMonth() {
            if (this.datePickerMonth == 0) {
                this.datePickerYear--;
                this.datePickerMonth = 12;
            }
            this.datePickerMonth--;
            this.datePickerCalculateDays();
        },
        datePickerNextMonth() {
            if (this.datePickerMonth == 11) {
                this.datePickerMonth = 0;
                this.datePickerYear++;
            } else {
                this.datePickerMonth++;
            }
            this.datePickerCalculateDays();
        },
        datePickerIsSelectedDate(day) {
            const d = new Date(this.datePickerYear, this.datePickerMonth, day);
            return this.datePickerValue === this.datePickerFormatDate(d)
                ? true
                : false;
        },
        datePickerIsToday(day) {
            const today = new Date();
            const d = new Date(this.datePickerYear, this.datePickerMonth, day);
            return today.toDateString() === d.toDateString() ? true : false;
        },
        datePickerCalculateDays() {
            let daysInMonth = new Date(
                this.datePickerYear,
                this.datePickerMonth + 1,
                0
            ).getDate();
            // find where to start calendar day of week
            let dayOfWeek = new Date(
                this.datePickerYear,
                this.datePickerMonth
            ).getDay();
            let blankdaysArray = [];
            for (let i = 1; i <= dayOfWeek; i++) {
                blankdaysArray.push(i);
            }
            let daysArray = [];
            for (let i = 1; i <= daysInMonth; i++) {
                daysArray.push(i);
            }
            this.datePickerBlankDaysInMonth = blankdaysArray;
            this.datePickerDaysInMonth = daysArray;
        },
        datePickerFormatDate(date) {
            let formattedDay = this.datePickerDays[date.getDay()];
            let formattedDate = ("0" + date.getDate()).slice(-2); // appends 0 (zero) in single digit date
            let formattedMonth = this.datePickerMonthNames[date.getMonth()];
            let formattedMonthShortName = this.datePickerMonthNames[
                date.getMonth()
            ].substring(0, 3);
            let formattedMonthInNumber = (
                "0" +
                (parseInt(date.getMonth()) + 1)
            ).slice(-2);
            let formattedYear = date.getFullYear();

            if (this.datePickerFormat === "M d, Y") {
                return `${formattedMonthShortName} ${formattedDate}, ${formattedYear}`;
            }
            if (this.datePickerFormat === "MM-DD-YYYY") {
                return `${formattedMonthInNumber}-${formattedDate}-${formattedYear}`;
            }
            if (this.datePickerFormat === "DD-MM-YYYY") {
                return `${formattedDate}-${formattedMonthInNumber}-${formattedYear}`;
            }
            if (this.datePickerFormat === "YYYY-MM-DD") {
                return `${formattedYear}-${formattedMonthInNumber}-${formattedDate}`;
            }
            if (this.datePickerFormat === "D d M, Y") {
                return `${formattedDay} ${formattedDate} ${formattedMonthShortName} ${formattedYear}`;
            }

            return `${formattedMonth} ${formattedDate}, ${formattedYear}`;
        },
        datePickerPositionUpdate() {
            let datePickerBottomPos =
                this.datePickerInput.getBoundingClientRect().top +
                this.datePickerInput.offsetHeight +
                this.datePickerCalendar.offsetHeight;
            
            if (window.innerHeight < datePickerBottomPos) {
                this.datePickerPosition = "top";
            } else {
                this.datePickerPosition = "bottom";
            }
        },
    });

    onMounted(() => {
        datePicker.currentDate = new Date();
        if (datePicker.datePickerValue) {
            datePicker.currentDate = new Date(Date.parse(datePicker.datePickerValue));
        }
        datePicker.datePickerMonth = datePicker.currentDate.getMonth();
        datePicker.datePickerYear = datePicker.currentDate.getFullYear();
        datePicker.datePickerDay = datePicker.currentDate.getDay();
        datePicker.datePickerValue = datePicker.datePickerFormatDate( datePicker.currentDate );
        datePicker.datePickerCalculateDays();
    })
    const updatePosition = () => {
        if(!datePicker.datePickerOpen) return;
        datePicker.datePickerPositionUpdate();
    }
    watch(
        () => datePicker.datePickerOpen,
        async function (value) {
            if(!value){
                removeEventListener("resize", updatePosition);
                return;
            }
            await nextTick();
            updatePosition()
            window.addEventListener("resize", updatePosition);
        },
        { immediate: false }
    );

    return datePicker;
}
