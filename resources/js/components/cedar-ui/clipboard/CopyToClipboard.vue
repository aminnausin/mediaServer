<script setup lang="ts">
import { cn, useClipboard } from '@aminnausin/cedar-ui';
import { ButtonIcon } from '../button';
import { TextInput } from '../input';
import { ref } from 'vue';

const props = defineProps<{ text: string; tabindex?: number }>();
const text = ref(props.text);

const clipboard = useClipboard(text);
</script>

<template>
    <div class="flex h-8 justify-between gap-4">
        <TextInput name="clipboard-text" class="cursor-text" v-model="text" disabled />
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
                    <div class="-ml-1.5 flex h-7 -translate-x-full items-center rounded border-r border-green-500 bg-green-500 px-3 text-xs text-white">
                        <span>Copied!</span>
                        <div class="absolute top-1/2 right-0 -mt-px inline-block h-full translate-x-3 -translate-y-2 overflow-hidden">
                            <div class="h-3 w-3 origin-top-left rotate-45 transform border border-transparent bg-green-500"></div>
                        </div>
                    </div>
                </div>
            </Transition>

            <ButtonIcon
                @click="clipboard.copyToClipboard()"
                :class="
                    cn('group hover:text-foreground-0 text-foreground-1 focus:ring-green-600/50', {
                        'ring-green-600/50': clipboard.copyNotification.value,
                    })
                "
            >
                <template #icon>
                    <svg
                        v-if="clipboard.copyNotification.value"
                        class="text-success size-4 stroke-current"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        v-cloak
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <slot v-else v-cloak>
                        <svg class="size-4 stroke-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
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
                    </slot>
                </template>
            </ButtonIcon>
        </div>
    </div>
</template>
