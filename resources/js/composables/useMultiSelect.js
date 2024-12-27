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
        },
        selectableItemIsActive(item) {
            console.log(item);

            return false;
            // return this.selectableItemActive && this.selectableItemActive.value == item;
        },
        selectableItemActiveNext() {
            let index = this.selectableItems.indexOf(this.selectableItemActive);
            if (index < this.selectableItems.length - 1) {
                this.selectableItemActive = this.selectableItems[index + 1];
                this.selectScrollToActiveItem();
            }
        },
        selectableItemActivePrevious() {
            let index = this.selectableItems.indexOf(this.selectableItemActive);
            if (index > 0) {
                this.selectableItemActive = this.selectableItems[index - 1];
                this.selectScrollToActiveItem();
            }
        },
        selectScrollToActiveItem() {
            if (this.selectableItemActive) {
                let activeElement = document.getElementById(this.selectableItemActive.value + '-' + this.selectId);
                if (!activeElement) return;
                let newScrollPos = activeElement.offsetTop + activeElement.offsetHeight - this.selectableItemsList.offsetHeight;
                if (newScrollPos > 0) {
                    this.selectableItemsList.scrollTop = newScrollPos;
                } else {
                    this.selectableItemsList.scrollTop = 0;
                }
            }
        },
        selectKeydown(event) {
            if (event.keyCode >= 65 && event.keyCode <= 90) {
                this.selectKeydownValue += event.key;
                let selectedItemBestMatch = this.selectItemsFindBestMatch();
                if (selectedItemBestMatch) {
                    if (this.selectOpen) {
                        this.selectableItemActive = selectedItemBestMatch;
                        this.selectScrollToActiveItem();
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
            var bestMatch = null;
            var bestMatchIndex = -1;
            for (var i = 0; i < this.selectableItems.length; i++) {
                var name = this.selectableItems[i].name.toLowerCase();
                var index = name.indexOf(typedValue);
                if (index > -1 && (bestMatchIndex == -1 || index < bestMatchIndex) && !this.selectableItems[i].disabled) {
                    bestMatch = this.selectableItems[i];
                    bestMatchIndex = index;
                }
            }
            return bestMatch;
        },
        selectPositionUpdate() {
            if (!this.selectButton || !this.selectableItemsList) return;
            let selectDropdownBottomPos =
                this.selectButton?.getBoundingClientRect().top +
                this.selectButton.offsetHeight +
                parseInt(window.getComputedStyle(this.selectableItemsList).maxHeight);
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
