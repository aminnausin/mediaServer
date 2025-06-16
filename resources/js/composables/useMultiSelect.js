import { reactive, watch } from 'vue';

export default function useMultiSelect({ options, defaultItems }, refs) {
    const select = reactive({
        selectOpen: false,
        selectedItems: defaultItems,
        selectableItems: options,
        selectableItemActive: null,
        selectId: 'select-12',
        selectKeydownValue: '',
        selectKeydownTimeout: 1000,
        selectKeydownClearTimeout: null,
        selectDropdownPosition: 'bottom',
        selectableItemsList: refs.selectableItemsList,
        selectButton: refs.selectButton,
        toggleSelect(state) {
            if (state !== undefined) this.selectOpen = state === true;
            else this.selectOpen = !this.selectOpen;
        },
        updateRefs(values) {
            this.selectButton = values.selectButton;
            this.selectableItemsList = values.selectableItemsList;

            this.selectPositionUpdate();
        },
        // In a multiselect (combobox) nothing is ever selected in the list. It appears elsewhere and is removed from the list
        selectableItemIsActive() {
            return false;
        },
        async selectScrollToActiveItem(id, focus = true) {
            let activeElement = document.getElementById(id + '-' + this.selectId);

            if (!activeElement) return;

            activeElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            if (focus) activeElement.focus({ preventScroll: false });
        },
        selectKeydown(event) {
            // Handles input and therefore search
            if (event.keyCode < 65 || event.keyCode > 90) return;

            this.selectKeydownValue += event.key;
            let selectedItemBestMatch = this.selectItemsFindBestMatch();

            if (selectedItemBestMatch && this.selectOpen) {
                this.selectableItemActive = selectedItemBestMatch;
            }

            if (this.selectKeydownValue === '') return;

            clearTimeout(this.selectKeydownClearTimeout);
            this.selectKeydownClearTimeout = window.setTimeout(() => {
                this.selectKeydownValue = '';
            }, this.selectKeydownTimeout);
        },
        selectItemsFindBestMatch() {
            let typedValue = this.selectKeydownValue.toLowerCase();
            let bestMatch = null;
            let bestMatchIndex = -1;
            for (const selectableItem of this.selectableItems) {
                let name = selectableItem.name.toLowerCase();
                let index = name.indexOf(typedValue);
                if (index > -1 && (bestMatchIndex == -1 || index < bestMatchIndex) && !selectableItem.disabled) {
                    bestMatch = selectableItem;
                    bestMatchIndex = index;
                }
            }
            return bestMatch;
        },
        selectPositionUpdate() {
            if (!this.selectButton || !this.selectableItemsList) return;

            let selectDropdownBottomPos =
                this.selectButton?.getBoundingClientRect().top + this.selectButton.offsetHeight + parseInt(window.getComputedStyle(this.selectableItemsList).maxHeight);

            this.selectDropdownPosition = window.innerHeight < selectDropdownBottomPos ? 'top' : 'bottom';
        },
        setOptions(options) {
            if (Array.isArray(options)) this.selectableItems = options;
        },
    });

    const updatePosition = () => {
        if (!select.selectOpen) return;
        select.selectPositionUpdate();
    };

    watch(
        () => select.selectOpen,
        function (value) {
            if (!select.selectedItems) {
                select.selectableItemActive = select.selectableItems[0];
            } else {
                select.selectableItemActive = select.selectedItem;
            }

            if (!value) {
                window.removeEventListener('resize', updatePosition);
                return;
            }

            updatePosition();
            window.addEventListener('resize', updatePosition);
        },
        { immediate: false },
    );

    return select;
}
