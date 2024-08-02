<template>
    <!-- <div :class="[cx('message'), message.styleClass]" role="alert" aria-live="assertive" aria-atomic="true"
        v-bind="ptm('message')">
        <component v-if="templates.container" :is="templates.container" :message="message"
            :closeCallback="onCloseClick" />
        <div v-else :class="[cx('messageContent'), message.contentStyleClass]" v-bind="ptm('messageContent')">
            <template v-if="!templates.message">
                <component
                    :is="templates.messageicon ? templates.messageicon : templates.icon ? templates.icon : iconComponent && iconComponent.name ? iconComponent : 'span'"
                    :class="cx('messageIcon')" v-bind="ptm('messageIcon')" />
                <div :class="cx('messageText')" v-bind="ptm('messageText')">
                    <span :class="cx('summary')" v-bind="ptm('summary')">{{ message.summary }}</span>
                    <div :class="cx('detail')" v-bind="ptm('detail')">{{ message.detail }}</div>
                </div>
            </template>
            <component v-else :is="templates.message" :message="message"></component>
            <div v-if="message.closable !== false" v-bind="ptm('buttonContainer')">
                <button v-ripple :class="cx('closeButton')" type="button" :aria-label="closeAriaLabel"
                    @click="onCloseClick" autofocus v-bind="{ ...closeButtonProps, ...ptm('closeButton') }">
                    <component :is="templates.closeicon || 'TimesIcon'" :class="[cx('closeIcon'), closeIcon]"
                        v-bind="ptm('closeIcon')" />
                </button>
            </div>
        </div>
    </div> -->
    <div
        class="absolute bottom-0 flex flex-col items-start shadow-[0_5px_15px_-3px_rgb(0_0_0_/_0.08)] w-full bg-white border border-gray-100 sm:rounded-md sm:max-w-xs group p-4">
        <div class="relative">
            <div class="flex items-center text-green-500">

                <svg class="w-[18px] h-[18px] mr-1.5 -ml-1" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2ZM16.7744 9.63269C17.1238 9.20501 17.0604 8.57503 16.6327 8.22559C16.2051 7.87615 15.5751 7.93957 15.2256 8.36725L10.6321 13.9892L8.65936 12.2524C8.24484 11.8874 7.61295 11.9276 7.248 12.3421C6.88304 12.7566 6.92322 13.3885 7.33774 13.7535L9.31046 15.4903C10.1612 16.2393 11.4637 16.1324 12.1808 15.2547L16.7744 9.63269Z"
                        fill="currentColor"></path>
                </svg>
                <p class="text-[13px] font-medium leading-none text-gray-800">{{ 'NAAAA' }}</p>
            </div>
            <p :class="{ 'pl-5': true }" class="mt-1.5 text-xs leading-none opacity-70">{{ 'De' }}</p>
        </div>
        <span
            class="absolute right-0 p-1.5 mr-2.5 text-gray-400 duration-100 ease-in-out rounded-full opacity-0 cursor-pointer hover:bg-gray-50 hover:text-gray-500">
            <!--:class="{ 'top-1/2 -translate-y-1/2': !props.toast.description, 'top-0 mt-2.5': (props.toast.description), 'opacity-100': toastHovered, 'opacity-0': !toastHovered }"-->
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" @click="onCloseClick">
                <path fill-rule="evenodd"
                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                    clip-rule="evenodd">
                </path>
            </svg>
        </span>
    </div>
</template>

<script>
// import BaseComponent from '@primevue/core/basecomponent';
// import CheckIcon from '@primevue/icons/check';
// import ExclamationTriangleIcon from '@primevue/icons/exclamationtriangle';
// import InfoCircleIcon from '@primevue/icons/infocircle';
// import TimesIcon from '@primevue/icons/times';
// import TimesCircleIcon from '@primevue/icons/timescircle';
// import Ripple from 'primevue/ripple';

export default {
    type: 'default',
    name: 'ToastMessage',
    description: 'des',
    hostName: 'Toast',
    // extends: BaseComponent,
    emits: ['close'],
    closeTimeout: null,
    props: {
        message: {
            type: null,
            default: null
        },
        templates: {
            type: Object,
            default: null
        },
        closeIcon: {
            type: String,
            default: null
        },
        infoIcon: {
            type: String,
            default: null
        },
        warnIcon: {
            type: String,
            default: null
        },
        errorIcon: {
            type: String,
            default: null
        },
        successIcon: {
            type: String,
            default: null
        },
        closeButtonProps: {
            type: null,
            default: null
        }
    },
    mounted() {
        if (this.message.life) {
            this.closeTimeout = setTimeout(() => {
                this.close({ message: this.message, type: 'life-end' });
            }, this.message.life);
        }
    },
    beforeUnmount() {
        this.clearCloseTimeout();
    },
    methods: {
        close(params) {
            console.log('prine close dont work');
            
            this.$emit('close', params);
        },
        onCloseClick() {
            this.clearCloseTimeout();
            this.close({ message: this.message, type: 'close' });
        },
        clearCloseTimeout() {
            if (this.closeTimeout) {
                clearTimeout(this.closeTimeout);
                this.closeTimeout = null;
            }
        }
    },
    computed: {
        // iconComponent() {
        //     return {
        //         info: !this.infoIcon && InfoCircleIcon,
        //         success: !this.successIcon && CheckIcon,
        //         warn: !this.warnIcon && ExclamationTriangleIcon,
        //         error: !this.errorIcon && TimesCircleIcon
        //     }[this.message.severity];
        // },
        // closeAriaLabel() {
        //     return this.$primevue.config.locale.aria ? this.$primevue.config.locale.aria.close : undefined;
        // }
    },
    // components: {
    //     TimesIcon: TimesIcon,
    //     InfoCircleIcon: InfoCircleIcon,
    //     CheckIcon: CheckIcon,
    //     ExclamationTriangleIcon: ExclamationTriangleIcon,
    //     TimesCircleIcon: TimesCircleIcon
    // },
    // directives: {
    //     ripple: Ripple
    // }
};
</script>