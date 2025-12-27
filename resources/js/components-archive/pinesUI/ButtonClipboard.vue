<script setup lang="ts">
import { toRef } from 'vue';

import useClipboard from '@/composables/useClipboard';
import ButtonIcon from '@/components-archive/buttons/ButtonIcon.vue';

const props = defineProps<{ text: string; tabindex?: number }>();

const clipboard = useClipboard(toRef(props.text));
</script>

<template>
    <div class="flex justify-between gap-4">
        <input
            :value="props.text"
            disabled
            class="block h-8 w-full cursor-text rounded-md border-none bg-white p-2 text-gray-900 shadow-xs ring-1 ring-neutral-200 outline-hidden ring-inset placeholder:text-neutral-400 dark:bg-neutral-700 dark:text-gray-300 dark:ring-neutral-700"
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
                <div v-if="clipboard.copyNotification.value" class="absolute left-0" v-cloak>
                    <div class="-ml-1.5 flex h-7 -translate-x-full items-center rounded-sm border-r border-green-500 bg-green-500 px-3 text-xs text-white">
                        <span>Copied!</span>
                        <div class="absolute top-1/2 right-0 -mt-px inline-block h-full translate-x-3 -translate-y-2 overflow-hidden">
                            <div class="h-3 w-3 origin-top-left rotate-45 transform border border-transparent bg-green-500"></div>
                        </div>
                    </div>
                </div>
            </Transition>
            <ButtonIcon
                @click="clipboard.copyToClipboard()"
                class="group flex h-8 w-9 items-center justify-center text-xs text-neutral-600 hover:bg-neutral-100 hover:text-gray-900 focus:ring-green-600/50! dark:text-gray-300 dark:hover:text-gray-400"
            >
                <template #icon>
                    <svg
                        v-if="clipboard.copyNotification.value"
                        class="h-4 w-4 stroke-current text-green-500"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        v-cloak
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <svg v-if="!clipboard.copyNotification.value" class="h-4 w-4 stroke-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
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
