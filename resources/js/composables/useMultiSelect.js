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
        selectableItemIsActive(item) {
            return false;
            // return this.selectableItemActive && this.selectableItemActive.value == item;
        },
        selectableItemActiveNext() {
            let index = this.selectableItems.indexOf(this.selectableItemActive);
            if (index < this.selectableItems.length - 1) {
                this.selectableItemActive = this.selectableItems[index + 1];
                this.selectScrollToActiveItem(index + 1);
            }
        },
        selectableItemActivePrevious() {
            let index = this.selectableItems.indexOf(this.selectableItemActive);
            if (index > 0) {
                this.selectableItemActive = this.selectableItems[index - 1];
                this.selectScrollToActiveItem(index - 1);
            }
        },
        selectScrollToActiveItem(index) {
            let activeElement = document.getElementById(index + '-' + this.selectId);

            if (!activeElement) return;

            activeElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
        },
        selectKeydown(event) {
            if (event.keyCode >= 65 && event.keyCode <= 90) {
                this.selectKeydownValue += event.key;
                let selectedItemBestMatch = this.selectItemsFindBestMatch();
                if (selectedItemBestMatch) {
                    if (this.selectOpen) {
                        this.selectableItemActive = selectedItemBestMatch;
                        let index = this.selectableItems.indexOf(this.selectableItemActive);
                        this.selectScrollToActiveItem(index);
                    } else {
                        // this.selectedItem = this.selectableItemActive === selectedItemBestMatch; // What does this line do there was only 1 equal
                    }
                }

                if (this.selectKeydownValue != '') {
                    clearTimeout(this.selectKeydownClearTimeout);
                    this.selectKeydownClearTimeout = setTimeout(() => {
                        this.selectKeydownValue = '';
                    }, this.selectKeydownTimeout);
                }
            }
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

            if (window.innerHeight < selectDropdownBottomPos) {
                this.selectDropdownPosition = 'top';
            } else {
                this.selectDropdownPosition = 'bottom';
            }
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
