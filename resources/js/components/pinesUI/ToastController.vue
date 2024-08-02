<script>
import ToastEventBus from '../../service/toastEventBus';
import ToastNotification from '../pinesUI/ToastNotification.vue';


var messageIdx = 0;

function UniqueComponentId(prefix = 'pv_id_') {
    return prefix + Math.random().toString(16).slice(2);
}

export default {
    name: 'ToastController',
    emits: ['close'],
    data() {
        return {
            messages: [],
            positionRoot: 'bottom-left',
            toastsHovered: false,
            expanded: false,
            layout: 'default',
            paddingBetweenToasts: 16,
            heightRecalculateTimeout: null
        };
    },
    mounted() {
        ToastEventBus.on('add', this.onAdd);
        ToastEventBus.on('remove', this.onRemove);
        ToastEventBus.on('remove-group', this.onRemoveGroup);
        ToastEventBus.on('remove-all-groups', this.onRemoveAllGroups);
        this.stackToasts();
    },
    beforeUnmount() {
        ToastEventBus.off('add', this.onAdd);
        ToastEventBus.off('remove', this.onRemove);
        ToastEventBus.off('remove-group', this.onRemoveGroup);
        ToastEventBus.off('remove-all-groups', this.onRemoveAllGroups);
    },
    watch: {
        // whenever question changes, this function will run
        toastsHovered(value) {
            if (this.layout == 'default') {
                // if (this.position.includes('bottom')) {
                //     this.resetBottom();
                // } else {
                //     this.resetTop();
                // }

                if (value) {
                    // calculate the new positions
                    this.expanded = true;
                    if (this.layout == 'default') {
                        this.stackToasts();
                    }
                } else {
                    if (this.layout == 'default') {
                        this.expanded = false;
                        // setTimeout(function(){
                        this.stackToasts();
                        // }, 10);
                        // setTimeout(function () {
                        //     this.stackToasts();
                        // }, 10)
                    }
                }
            }
        }
    },
    methods: {
        add(message) {

            if (message.position && this.positionIsValid(message.position)) this.positionRoot = message.position
            if (message.type && !this.typeIsValid(message.type)) message.type = 'default';

            if (message.idx == null) {
                message.idx = messageIdx;
                messageIdx++;
            }

            if (message.id == null) {
                message.id = UniqueComponentId('toast_');
            }

            this.messages.unshift(message);
        },
        remove(params) {
            // console.log(params.message.idx);

            if (!params.message.idx) return;
            // for (let i = 0; i < this.messages.length; i++) {
            //     if (this.messages[i].idx === params.message.idx) {
            //         console.log('found ' + params.message.idx);

            //         this.messages.splice(i, 1);
            //         break;
            //     }
            // }
            // if (this.messages[params.message.idx]) {
            //     // delete this.messages[params.message.idx]
            //     // this.$emit(params.type, { message: params.message });
            // }
        },
        onAdd(message) {
            if (this.group == message.group) {
                this.add(message);
            }
        },
        onRemove(message) {
            this.remove({ message, type: 'close' });
        },
        onRemoveGroup(group) {
            if (this.group === group) {
                this.messages = [];
            }
        },
        onRemoveAllGroups() {
            this.messages = [];
        },
        stackToasts() {
            this.positionToasts();
            this.calculateHeightOfToastsContainer();
            if (this.heightRecalculateTimeout) clearTimeout(this.heightRecalculateTimeout);

            let that = this;
            this.heightRecalculateTimeout = setTimeout(function () {
                that.calculateHeightOfToastsContainer();
            }, 300);
        },
        positionToasts() {
            try {
                let scaleBuffer = 1;
                let yBuffer = -16;
                let zBuffer = 100;
                let bottomFlag = this.positionRoot.includes("bottom")
                let totalHeight = 0;
                let topToast = null;
                for (let i = 0; i < this.messages.length; i++) {
                    const toast = document.getElementById(this.messages[i].id);
                    toast.style.zIndex = zBuffer;
                    if (zBuffer > 0) zBuffer -= 10;

                    yBuffer += 16;

                    if (this.expanded) {
                        totalHeight = totalHeight + (totalHeight ? this.paddingBetweenToasts : 0);
                        
                        if (bottomFlag) {
                            toast.style.top = "auto";
                            toast.style.bottom = totalHeight + "px";
                        }
                        else toast.style.top = totalHeight + "px";

                        totalHeight += toast.getBoundingClientRect().height;
                        toast.style.scale = 1;
                        continue;
                    }

                    toast.style.top = "auto";
                    toast.style.bottom = "auto";
                    toast.style.scale = scaleBuffer;
                    toast.style.transform = yBuffer ? `translateY(${bottomFlag ? '-' : ''}${yBuffer}px)` : '';
                    if (i === 0) topToast = toast;
                    else this.alignBottom(topToast, toast);
                    scaleBuffer -= 0.06;
                }

                if(this.messages[3]) {
                    let burnToast = document.getElementById(this.messages[3].id) ;
                    burnToast.classList.remove("opacity-100");
                    burnToast.classList.add("opacity-0");
                    this.messages.splice(3, 1);
                    this.stackToasts();
                    // let that = this;
                    // // Burn 🔥 (remove) last toast
                    // setTimeout(function () {
                    //     for (let i = 0; i < that.messages.length; i++) {
                    //         if (that.messages[i].idx === burnToast.idx) {
                    //             that.messages.splice(i, 1);
                    //             break;
                    //         }
                    //     }
                    // }, 300);

                    // if (bottomFlag && this.messages[1]) {
                    //     this.messages[1].style.top = "auto";
                    // }
                }
            } catch (error) {
                console.log(error);
            }
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
            element2.style.top = top2 + "px";
        },
        calculateHeightOfToastsContainer() {
            if (this.messages.length == 0) {
                this.$refs.container.style.height = "0px";
                return;
            }

            let lastToast = this.messages[this.messages.length - 1];
            let lastToastRectangle = document.getElementById(lastToast.id).getBoundingClientRect();

            let firstToast = this.messages[0];
            let firstToastRectangle = document.getElementById(firstToast.id).getBoundingClientRect();

            if (this.toastsHovered) {
                if (this.positionRoot.includes("bottom")) {
                    this.$refs.container.style.height =
                        firstToastRectangle.top +
                        firstToastRectangle.height -
                        lastToastRectangle.top +
                        "px";
                } else {
                    this.$refs.container.style.height =
                        lastToastRectangle.top +
                        lastToastRectangle.height -
                        firstToastRectangle.top +
                        "px";
                }
            } else {
                this.$refs.container.style.height = firstToastRectangle.height + "px";
            }
        },
        positionIsValid(newPosition) {
            return newPosition == 'top-right' || newPosition == 'top-left' || newPosition == 'top-center' || newPosition == 'bottom-right' || newPosition == 'bottom-left' || newPosition == 'bottom-center'
        },
        typeIsValid(newType) {
            return newType == 'success' || newType == 'info' || newType == 'warning' || newType == 'danger' || newType == 'default'
        }
    },
    components: {
        ToastNotification: ToastNotification
    }

};
</script>

<template>
    <teleport to='body'>
        <ul class="fixed w-full group z-[99] sm:max-w-xs" id="toastRoot"
            :class="{ 'right-0 top-0 sm:mt-6 sm:mr-6': positionRoot == 'top-right', 'left-0 top-0 sm:mt-6 sm:ml-6': positionRoot == 'top-left', 'left-1/2 -translate-x-1/2 top-0 sm:mt-6': positionRoot == 'top-center', 'right-0 bottom-0 sm:mr-6 sm:mb-6': positionRoot == 'bottom-right', 'left-0 bottom-0 sm:ml-6 sm:mb-6': positionRoot == 'bottom-left', 'left-1/2 -translate-x-1/2 bottom-0 sm:mb-6': positionRoot == 'bottom-center' }"
            v-cloak ref="container"
            @mouseenter="toastsHovered=true;"
            @mouseleave="toastsHovered=false">
            <ToastNotification v-for="toast in messages" :key="toast.idx" :type="toast.type" v-bind="toast" :stack="stackToasts"
                :count="messages.length" @close="(event) => {
                    for (let i = 0; i < messages.length; i++) {
                        if (messages[i].idx === toast.idx) {
                            messages.splice(i, 1);
                            stackToasts();
                            break;
                        }
                    }
                }" />
        </ul>
    </teleport>
</template>