import { reactive, watch } from 'vue';

/*
    UNUSED -> Left for reference only
*/
export default function useToastController($el = document.querySelector('#toastRoot'), layout = 'default', position = 'top-center') {
    const toast = reactive({
        toasts: [],
        toastsHovered: false,
        expanded: false,
        layout: layout,
        position: position,
        paddingBetweenToasts: 16,
        deleteToastWithId(id) {
            for (let i = 0; i < this.toasts.length; i++) {
                if (this.toasts[i].id === id) {
                    this.toasts.splice(i, 1);
                    break;
                }
            }
        },
        getToastWithId(id) {
            for (let i = 0; i < this.toasts.length; i++) {
                if (this.toasts[i].id === id) {
                    return this.toasts[i];
                }
            }
        },
        burnToast(id) {
            let burnToast = this.getToastWithId(id);
            let burnToastElement = document.getElementById(burnToast.id);
            if (burnToastElement) {
                if (this.toasts.length == 1) {
                    if (this.layout == 'default') {
                        this.expanded = false;
                    }
                    burnToastElement.classList.remove('translate-y-0');
                    if (this.position.includes('bottom')) {
                        burnToastElement.classList.add('translate-y-full');
                    } else {
                        burnToastElement.classList.add('-translate-y-full');
                    }
                    burnToastElement.classList.add('-translate-y-full');
                }
                burnToastElement.classList.add('opacity-0');
                setTimeout(function () {
                    that.deleteToastWithId(id);
                    setTimeout(function () {
                        this.stackToasts();
                    }, 1);
                }, 300);
            }
        },
        stackToasts() {
            this.positionToasts();
            this.calculateHeightOfToastsContainer();
            setTimeout(function () {
                this.calculateHeightOfToastsContainer();
            }, 300);
        },
        positionToasts() {
            if (this.toasts.length == 0) return;
            let topToast = document.getElementById(this.toasts[0].id);

            //Custom Addition
            if (!topToast) return;

            topToast.style.zIndex = 100;
            if (this.expanded) {
                if (this.position.includes('bottom')) {
                    topToast.style.top = 'auto';
                    topToast.style.bottom = '0px';
                } else {
                    topToast.style.top = '0px';
                }
            }

            // let bottomPositionOfFirstToast = this.getBottomPositionOfElement(topToast); Not used

            if (this.toasts.length == 1) return;

            let middleToast = document.getElementById(this.toasts[1].id);

            //Custom Addition
            if (!middleToast) return;

            middleToast.style.zIndex = 90;

            if (this.expanded) {
                let middleToastPosition = topToast.getBoundingClientRect().height + this.paddingBetweenToasts + 'px';

                if (this.position.includes('bottom')) {
                    middleToast.style.top = 'auto';
                    middleToast.style.bottom = middleToastPosition;
                } else {
                    middleToast.style.top = middleToastPosition;
                }

                middleToast.style.scale = '100%';
                middleToast.style.transform = 'translateY(0px)';
            } else {
                middleToast.style.scale = '94%';
                if (this.position.includes('bottom')) {
                    middleToast.style.transform = 'translateY(-16px)';
                } else {
                    this.alignBottom(topToast, middleToast);
                    middleToast.style.transform = 'translateY(16px)';
                }
            }

            if (this.toasts.length == 2) return;

            let bottomToast = document.getElementById(this.toasts[2].id);

            //Custom Addition
            if (!bottomToast) return;

            bottomToast.style.zIndex = 80;
            if (this.expanded) {
                let bottomToastPosition =
                    topToast.getBoundingClientRect().height +
                    this.paddingBetweenToasts +
                    middleToast.getBoundingClientRect().height +
                    this.paddingBetweenToasts +
                    'px';

                if (this.position.includes('bottom')) {
                    bottomToast.style.top = 'auto';
                    bottomToast.style.bottom = bottomToastPosition;
                } else {
                    bottomToast.style.top = bottomToastPosition;
                }

                bottomToast.style.scale = '100%';
                bottomToast.style.transform = 'translateY(0px)';
            } else {
                bottomToast.style.scale = '88%';
                if (this.position.includes('bottom')) {
                    bottomToast.style.transform = 'translateY(-32px)';
                } else {
                    this.alignBottom(topToast, bottomToast);
                    bottomToast.style.transform = 'translateY(32px)';
                }
            }

            if (this.toasts.length == 3) return;
            let burnToast = document.getElementById(this.toasts[3].id);

            //Custom Addition
            if (!burnToast) return;

            burnToast.style.zIndex = 70;
            if (this.expanded) {
                let burnToastPosition =
                    topToast.getBoundingClientRect().height +
                    this.paddingBetweenToasts +
                    middleToast.getBoundingClientRect().height +
                    this.paddingBetweenToasts +
                    bottomToast.getBoundingClientRect().height +
                    this.paddingBetweenToasts +
                    'px';

                if (this.position.includes('bottom')) {
                    burnToast.style.top = 'auto';
                    burnToast.style.bottom = burnToastPosition;
                } else {
                    burnToast.style.top = burnToastPosition;
                }

                burnToast.style.scale = '100%';
                burnToast.style.transform = 'translateY(0px)';
            } else {
                burnToast.style.scale = '82%';
                this.alignBottom(topToast, burnToast);
                burnToast.style.transform = 'translateY(48px)';
            }

            burnToast.firstElementChild.classList.remove('opacity-100');
            burnToast.firstElementChild.classList.add('opacity-0');

            // Burn 🔥 (remove) last toast
            setTimeout(function () {
                this.toasts.pop();
            }, 300);

            if (this.position.includes('bottom')) {
                middleToast.style.top = 'auto';
            }

            return;
        },
        alignBottom(element1, element2) {
            // Get the top position and height of the first element
            let top1 = element1.offsetTop;
            let height1 = element1.offsetHeight;

            // Get the height of the second element
            let height2 = element2.offsetHeight;

            // Calculate the top position for the second element
            let top2 = top1 + (height1 - height2);

            // Apply the calculated top position to the second element
            element2.style.top = top2 + 'px';
        },
        alignTop(element1, element2) {
            // Get the top position of the first element
            let top1 = element1.offsetTop;

            // Apply the same top position to the second element
            element2.style.top = top1 + 'px';
        },
        resetBottom() {
            for (let i = 0; i < this.toasts.length; i++) {
                if (document.getElementById(this.toasts[i].id)) {
                    let toastElement = document.getElementById(this.toasts[i].id);
                    toastElement.style.bottom = '0px';
                }
            }
        },
        resetTop() {
            for (let i = 0; i < this.toasts.length; i++) {
                if (document.getElementById(this.toasts[i].id)) {
                    let toastElement = document.getElementById(this.toasts[i].id);
                    toastElement.style.top = '0px';
                }
            }
        },
        getBottomPositionOfElement(el) {
            return el.getBoundingClientRect().height + el.getBoundingClientRect().top;
        },
        calculateHeightOfToastsContainer() {
            if (this.toasts.length == 0) {
                $el.value.style.height = '0px';
                return;
            }

            let lastToast = this.toasts[this.toasts.length - 1];
            let lastToastRectangle = document.getElementById(lastToast.id).getBoundingClientRect();

            let firstToast = this.toasts[0];
            let firstToastRectangle = document.getElementById(firstToast.id).getBoundingClientRect();

            if (this.toastsHovered) {
                if (this.position.includes('bottom')) {
                    $el.value.style.height = firstToastRectangle.top + firstToastRectangle.height - lastToastRectangle.top + 'px';
                } else {
                    $el.value.style.height = lastToastRectangle.top + lastToastRectangle.height - firstToastRectangle.top + 'px';
                }
            } else {
                $el.value.style.height = firstToastRectangle.height + 'px';
            }
        },
    });

    watch(
        () => toast.toastsHovered,
        function (value) {
            if (toast.layout == 'default') {
                if (toast.position.includes('bottom')) {
                    toast.resetBottom();
                } else {
                    toast.resetTop();
                }

                if (value) {
                    // calculate the new positions
                    toast.expanded = true;
                    if (toast.layout == 'default') {
                        toast.stackToasts();
                    }
                } else {
                    if (toast.layout == 'default') {
                        toast.expanded = false;
                        // setTimeout(function(){
                        toast.stackToasts();
                        // }, 10);
                        setTimeout(function () {
                            toast.stackToasts();
                        }, 10);
                    }
                }
            }
        },
    );
    return toast;
}

function positionToast() {
    if (messages.value.length == 0 || !messages.value[0].id) return;
    let topToast = document.getElementById(messages.value[0].id);

    if (!topToast) return;

    topToast.style.zIndex = `${100}`;
    if (expanded.value) {
        if (position.value.includes('bottom')) {
            topToast.style.top = 'auto';
            topToast.style.bottom = '0px';
        } else {
            topToast.style.top = '0px';
        }
    }
    console.log('totalHeight', 1, 0);

    if (messages.value.length == 1 || !messages.value[1].id) return;

    let middleToast = document.getElementById(messages.value[1].id);

    if (!middleToast) return;

    middleToast.style.zIndex = `${90}`;

    if (expanded.value) {
        console.log(topToast.getBoundingClientRect().height);

        let middleToastPosition = topToast.getBoundingClientRect().height + paddingBetweenToasts.value + 'px';

        if (position.value.includes('bottom')) {
            middleToast.style.top = 'auto';
            middleToast.style.bottom = middleToastPosition;
        } else {
            middleToast.style.top = middleToastPosition;
        }
        console.log('totalHeight', 2, middleToastPosition);

        middleToast.style.scale = '100%';
        middleToast.style.transform = 'translateY(0px)';
    } else {
        middleToast.style.scale = '94%';
        if (position.value.includes('bottom')) {
            middleToast.style.transform = 'translateY(-16px)';
        } else {
            alignBottom(topToast, middleToast);
            middleToast.style.transform = 'translateY(16px)';
        }
    }

    if (messages.value.length == 2 || !messages.value[2].id) return;

    let bottomToast = document.getElementById(messages.value[2].id);

    if (!bottomToast) return;

    bottomToast.style.zIndex = `${80}`;
    if (expanded.value) {
        console.log(middleToast.getBoundingClientRect().height);
        let bottomToastPosition =
            topToast.getBoundingClientRect().height +
            paddingBetweenToasts.value +
            middleToast.getBoundingClientRect().height +
            paddingBetweenToasts.value +
            'px';

        console.log('totalHeight', 3, bottomToastPosition);
        if (position.value.includes('bottom')) {
            bottomToast.style.top = 'auto';
            bottomToast.style.bottom = bottomToastPosition;
        } else {
            bottomToast.style.top = bottomToastPosition;
        }

        bottomToast.style.scale = '100%';
        bottomToast.style.transform = 'translateY(0px)';
    } else {
        bottomToast.style.scale = '88%';
        if (position.value.includes('bottom')) {
            bottomToast.style.transform = 'translateY(-32px)';
        } else {
            alignBottom(topToast, bottomToast);
            bottomToast.style.transform = 'translateY(32px)';
        }
    }

    if (messages.value.length == 3 || !messages.value[3].id) return;

    let burnToast = document.getElementById(messages.value[3].id);

    if (!burnToast) return;

    burnToast.style.zIndex = `${70}`;
    if (expanded.value) {
        console.log(bottomToast.getBoundingClientRect().height);
        let burnToastPosition =
            topToast.getBoundingClientRect().height +
            paddingBetweenToasts.value +
            middleToast.getBoundingClientRect().height +
            paddingBetweenToasts.value +
            bottomToast.getBoundingClientRect().height +
            paddingBetweenToasts.value +
            'px';

        console.log('totalHeight', 4, burnToastPosition);

        if (position.value.includes('bottom')) {
            burnToast.style.top = 'auto';
            burnToast.style.bottom = burnToastPosition;
        } else {
            burnToast.style.top = burnToastPosition;
        }

        burnToast.style.scale = '100%';
        burnToast.style.transform = 'translateY(0px)';
    } else {
        burnToast.style.scale = '82%';
        alignBottom(topToast, burnToast);
        burnToast.style.transform = 'translateY(48px)';
    }

    burnToast.firstElementChild?.classList.remove('opacity-100');
    burnToast.firstElementChild?.classList.add('opacity-0');

    // Burn 🔥 (remove) last toast
    setTimeout(function () {
        messages.value.pop();
    }, 300);

    if (position.value.includes('bottom')) {
        middleToast.style.top = 'auto';
    }

    return;
}
