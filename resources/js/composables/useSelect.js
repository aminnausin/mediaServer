import { reactive, watch } from "vue";

export default function useSelect(options, refs) {
    const select = reactive({
        selectOpen: false,
        selectedItem: "",
        selectableItems: options,
        selectableItemActive: null,
        selectId: 'select-12',
        selectKeydownValue: "",
        selectKeydownTimeout: 1000,
        selectKeydownClearTimeout: null,
        selectDropdownPosition: "bottom",
        selectableItemsList: refs.selectableItemsList,
        selectButton: refs.selectButton,
        toggleSelect(state){
            if(state !== undefined) this.selectOpen = state === true;
            else this.selectOpen = !this.selectOpen;
        },
        updateRefs(values) {
            this.selectButton = values.selectButton;
            this.selectableItemsList = values.selectableItemsList;
        },
        selectableItemIsActive(item) {
            return (
                this.selectableItemActive &&
                this.selectableItemActive.value == item.value
            );
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
                let activeElement = document.getElementById(
                    this.selectableItemActive.value + "-" + this.selectId
                );
                if(!activeElement) return;
                let newScrollPos =
                    activeElement.offsetTop +
                    activeElement.offsetHeight -
                    this.selectableItemsList.offsetHeight;
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
                        this.selectedItem = this.selectableItemActive === selectedItemBestMatch; // What does this line do there was only 1 equal
                    }
                }

                if (this.selectKeydownValue != "") {
                    clearTimeout(this.selectKeydownClearTimeout);
                    this.selectKeydownClearTimeout = setTimeout(() => {
                        this.selectKeydownValue = "";
                    }, this.selectKeydownTimeout);
                }
            }
        },
        selectItemsFindBestMatch() {
            let typedValue = this.selectKeydownValue.toLowerCase();
            var bestMatch = null;
            var bestMatchIndex = -1;
            for (var i = 0; i < this.selectableItems.length; i++) {
                var title = this.selectableItems[i].title.toLowerCase();
                var index = title.indexOf(typedValue);
                if (
                    index > -1 &&
                    (bestMatchIndex == -1 || index < bestMatchIndex) &&
                    !this.selectableItems[i].disabled
                ) {
                    bestMatch = this.selectableItems[i];
                    bestMatchIndex = index;
                }
            }
            return bestMatch;
        },
        selectPositionUpdate() {
            let selectDropdownBottomPos =
            this.selectButton.getBoundingClientRect().top +
            this.selectButton.offsetHeight +
                parseInt( window.getComputedStyle(this.selectableItemsList).maxHeight );
            if (window.innerHeight < selectDropdownBottomPos) {
                this.selectDropdownPosition = "top";
            } else {
                this.selectDropdownPosition = "bottom";
            }
        },
    });

    // watch(() => select.selectOpen, 
    // function () {
    //     if (!select.selectedItem) {
    //         select.selectableItemActive = select.selectableItems[0];
    //     } else {
    //         select.selectableItemActive = select.selectedItem;
    //     }
    //     setTimeout(function () {
    //         select.selectScrollToActiveItem();
    //     }, 10);
    //     select.selectPositionUpdate();
    //     window.addEventListener("resize", (event) => {
    //         select.selectPositionUpdate();
    //     });
    // }, {immediate: false});

    return select;
}
