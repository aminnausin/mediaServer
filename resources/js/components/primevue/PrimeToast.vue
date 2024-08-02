<script>
import ToastEventBus from '../../service/toastEventBus';
import PrimeBaseToast from './PrimeBaseToast.vue';
import ToastNotification from '../pinesUI/ToastNotification.vue';


var messageIdx = 0;

function isEmpty(value) {
    return value === null || value === undefined || value === '' || (Array.isArray(value) && value.length === 0) || (!(value instanceof Date) && typeof value === 'object' && Object.keys(value).length === 0);
}


function UniqueComponentId(prefix = 'pv_id_') {
    return prefix + Math.random().toString(16).slice(2);
}

export default {
    name: 'PrimeToast',
    extends: PrimeBaseToast,
    emits: ['close'],
    data() {
        return {
            messages: {},
            positionRoot: 'bottom-left'
        };
    },
    mounted() {
        ToastEventBus.on('add', this.onAdd);
        ToastEventBus.on('remove', this.onRemove);
        ToastEventBus.on('remove-group', this.onRemoveGroup);
        ToastEventBus.on('remove-all-groups', this.onRemoveAllGroups);
    },
    beforeUnmount() {
        ToastEventBus.off('add', this.onAdd);
        ToastEventBus.off('remove', this.onRemove);
        ToastEventBus.off('remove-group', this.onRemoveGroup);
        ToastEventBus.off('remove-all-groups', this.onRemoveAllGroups);
    },
    methods: {
        add(message) {
            
            if(message.position && this.positionIsValid(message.position)) this.positionRoot = message.position
            if (message.idx == null) {
                message.idx = messageIdx++;
            }

            if(message.id == null) {
                message.id = UniqueComponentId('toast_');
            }

            let stack = {...this.messages};
            stack[message.idx] = message;
            this.messages = {...stack};
        },
        async remove(params) {
            if (this.messages[params.message.idx]) {
                this.messages[params.message.idx] = {};
                // this.$emit(params.type, { message: params.message });
            }
        },
        onAdd(message) {
            if (this.group == message.group) {
                this.add(message);
            }
        },
        onRemove(message) {
            console.log(`remove ${message.idx}`);
            
            this.remove({ message, type: 'close' });
        },
        onRemoveGroup(group) {
            if (this.group === group) {
                this.messages = {};
            }
        },
        onRemoveAllGroups() {
            this.messages = {};
        },
        positionIsValid(newPosition) {
            return newPosition == 'top-right' || newPosition == 'top-left' || newPosition == 'top-center' || newPosition == 'bottom-right' || newPosition == 'bottom-left' || newPosition == 'bottom-center' 
        }
    },
    computed: {
        attributeSelector() {
            return UniqueComponentId();
        }
    },
    components: {
        ToastNotification: ToastNotification
    }
};
</script>

<template>
    <teleport to='body'>
        <ul class="fixed w-full group z-[99] sm:max-w-xs flex flex-col gap-4" id="toastRoot"
            :class="{ 'right-0 top-0 sm:mt-6 sm:mr-6': positionRoot == 'top-right', 'left-0 top-0 sm:mt-6 sm:ml-6': positionRoot == 'top-left', 'left-1/2 -translate-x-1/2 top-0 sm:mt-6': positionRoot == 'top-center', 'right-0 bottom-0 sm:mr-6 sm:mb-6': positionRoot == 'bottom-right', 'left-0 bottom-0 sm:ml-6 sm:mb-6': positionRoot == 'bottom-left', 'left-1/2 -translate-x-1/2 bottom-0 sm:mb-6': positionRoot == 'bottom-center' }"
            v-cloak ref="container">
            <ToastNotification v-for="(toast, index) in Object.values(messages)" :key="index" v-bind="toast" :count="Object.keys(messages).length" @close="async (event) => { 
                remove(event);
            }"/>
        </ul>
    </teleport>
</template>