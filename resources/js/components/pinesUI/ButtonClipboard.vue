<script setup lang="ts">
import { toast } from '@/service/toaster/toastService';
import { ref } from 'vue';

import ButtonIcon from '@/components/inputs/ButtonIcon.vue';

const props = defineProps(['text', 'tabindex']);

const copyNotification = ref(false);
const copyTimeout = ref<null | number>(null);
const copyText = ref(props.text);

const copyToClipboard = async () => {
    try {
        await navigator.clipboard.writeText(copyText.value);
        copyNotification.value = true;
        if (copyTimeout.value !== null) clearTimeout(copyTimeout.value);
        copyTimeout.value = setTimeout(function () {
            copyNotification.value = false;
        }, 3000);
    } catch (error) {
        console.log(error);
        toast.add('Error', {
            type: 'danger',
            description: 'Unable to copy. Network is not secure.',
            life: 3000,
        });
    }
};
</script>

<template>
    <div class="flex justify-between gap-4">
        <input
            :value="props.text"
            disabled
            class="h-8 w-full rounded-md shadow-sm block p-2 border-none outline-none text-gray-900 dark:text-gray-300 bg-white dark:bg-neutral-700 placeholder:text-neutral-400 ring-inset ring-1 ring-neutral-200 dark:ring-neutral-700"
        />
        <div class="relative z-20 flex items-center">
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 translate-x-2"
                enter-to-class="opacity-100 translate-x-0"
                leave-active-class="transition ease-in duration-300"
                leave-from-class="opacity-100 translate-x-0"
                leave-to-class="opacity-0 translate-x-2"
            >
                <div v-if="copyNotification" class="absolute left-0" v-cloak>
                    <div class="px-3 h-7 -ml-1.5 items-center flex text-xs bg-green-500 border-r border-green-500 -translate-x-full text-white rounded">
                        <span>Copied!</span>
                        <div class="absolute right-0 inline-block h-full -mt-px overflow-hidden translate-x-3 -translate-y-2 top-1/2">
                            <div class="w-3 h-3 origin-top-left transform rotate-45 bg-green-500 border border-transparent"></div>
                        </div>
                    </div>
                </div>
            </Transition>
            <ButtonIcon
                @click="copyToClipboard()"
                :tabindex="props.tabindex ?? 1"
                class="flex items-center justify-center h-8 w-9 text-xs group text-neutral-500 hover:text-neutral-600 dark:text-gray-300 dark:hover:text-gray-400 hover:bg-neutral-100 focus:!ring-green-600/50"
            >
                <template #icon>
                    <svg
                        v-if="copyNotification"
                        class="w-4 h-4 text-green-500 stroke-current"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        x-cloak
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <svg v-if="!copyNotification" class="w-4 h-4 stroke-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <g fill="none" stroke="none">
                            <path
                                d="M7.75 7.757V6.75a3 3 0 0 1 3-3h6.5a3 3 0 0 1 3 3v6.5a3 3 0 0 1-3 3h-.992"
                                stroke="currentColor"
                                stroke-width="1.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            ></path>
                            <path
                                d="M3.75 10.75a3 3 0 0 1 3-3h6.5a3 3 0 0 1 3 3v6.5a3 3 0 0 1-3 3h-6.5a3 3 0 0 1-3-3v-6.5z"
                                stroke="currentColor"
                                stroke-width="1.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            ></path>
                        </g>
                    </svg>
                </template>
            </ButtonIcon>
        </div>
    </div>
</template>
