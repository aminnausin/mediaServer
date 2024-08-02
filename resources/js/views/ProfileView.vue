<script setup>
import LayoutBase from '../components/layout/LayoutBase.vue';
import useToast from '../composables/useToast';
const emit = defineEmits(['toast-show']);
const toast = useToast({emit});
</script>

<template>
    <LayoutBase>
        <template #content>
            <section class="min-h-[80vh]">
                <div class="relative w-auto h-auto">
                    <div class="relative space-y-5">
                        <div class="relative">
                            <p class="mb-2 text-xs font-medium text-center text-gray-500 sm:text-left">Types</p>
                            <div class="relative flex flex-col px-10 space-y-2 sm:space-x-5 sm:space-y-0 sm:flex-row sm:px-0">
                                <button class="inline-flex items-center justify-center flex-shrink-0 px-3 py-1 text-xs font-medium transition-colors border rounded-md h-9 hover:bg-gray-50 active:bg-white focus:bg-white focus:outline-none"
                                    @click="toast.popToast();">
                                    Default
                                </button>
                                <!-- <button class="inline-flex items-center justify-center flex-shrink-0 px-3 py-1 text-xs font-medium transition-colors border rounded-md h-9 hover:bg-gray-50 active:bg-white focus:bg-white focus:outline-none"
                                    @click="$emit('setPage', null)">
                                    Default
                                </button> -->
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </template>
    </LayoutBase>
</template>