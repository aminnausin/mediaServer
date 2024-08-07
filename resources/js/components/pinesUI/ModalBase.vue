<script setup>
import ButtonCorner from '../inputs/ButtonCorner.vue';

defineProps({
    modalData: {
        required: true
    },
    action: {
        require: false,
        type: Function,
        default: () => {}
    },
    useControls: {
        type: Boolean,
        required: false,
        default: true
    }
})

const submitModal = async (action, modalData) => {
    await action();
    modalData.toggleModal(false);
}
</script>
<template>
    <div @keydown.escape="modalData.toggleModal(false)" :class="{ 'z-20': modalData.modalOpen }" class="relative w-auto h-auto">
        <Teleport to="body">
            <div v-show="modalData.modalOpen || modalData.isAnimating"
                class="fixed top-0 left-0 z-[25] flex items-center justify-center w-full h-screen text-neutral-900 dark:text-neutral-200"
                v-cloak>
                <Transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="ease-in duration-300" leave-from-class="opacity-100" leave-to-class="opacity-0">
                    <div v-if="modalData.modalOpen" @click="modalData.toggleModal(false)" class="absolute inset-0 w-full h-full  backdrop-blur-sm bg-opacity-70"></div>
                </Transition>
                <Transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0 -translate-y-2 sm:scale-95" enter-to-class="opacity-100 translate-y-0 sm:scale-100" leave-active-class="ease-in duration-200" leave-from-class="opacity-100 translate-y-0 sm:scale-100" leave-to-class="opacity-0 -translate-y-2 sm:scale-95">
                    <div v-if="modalData.modalOpen" x-trap.inert.noscroll="modalOpen" class="relative w-full m-6 p-6 bg-white dark:bg-neutral-800/90 backdrop-blur-lg border shadow-lg border-neutral-200 dark:border-neutral-700 sm:max-w-lg rounded-md sm:rounded-lg">
                        <div class="flex items-center justify-between pb-3">
                            <h3 class="text-lg font-semibold">{{ modalData?.title ?? 'Modal Title' }}</h3>
                            <ButtonCorner @click="modalData.toggleModal(false)"/>
                        </div>
                        <slot name="content">
                            <div class="relative w-auto pb-8">
                                <p>This is placeholder text. Replace it with your own content.</p>
                            </div>
                        </slot>
                        <slot v-if="useControls" name="controls">
                            <div class="relative flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                                <button @click="modalData.toggleModal(false)" type="button" 
                                    class="inline-flex items-center justify-center h-10 px-4 py-2 text-sm font-medium transition-colors border dark:border-neutral-600 rounded-md focus:outline-none "
                                    :class="'focus:ring-1 focus:ring-neutral-100 dark:focus:ring-neutral-400 focus:ring-offset-1 hover:bg-neutral-100 dark:hover:bg-neutral-900'">Cancel</button>
                                <button @click="submitModal(action, modalData)" type="button" 
                                    class="inline-flex items-center justify-center h-10 px-4 py-2 text-sm font-medium text-white transition-colors border border-transparent rounded-md focus:outline-none "
                                    :class="'focus:ring-1 focus:ring-violet-900 focus:ring-offset-1 bg-neutral-950 hover:bg-neutral-800 dark:hover:bg-neutral-900 '">{{ modalData.submitText }}</button>
                            </div>
                        </slot>
                    </div>
                </Transition>
            </div>
        </Teleport>
    </div>
</template>