<script setup lang="ts">
const props = withDefaults(
    defineProps<{
        currentPage?: number;
        pageNumber?: number;
        text?: string;
        underline?: boolean;
        sticky?: boolean;
        disabled?: boolean;
    }>(),
    {
        underline: false,
        sticky: false,
        disabled: false,
    },
);
</script>

<template>
    <li :class="{ hidden: props.currentPage !== props.pageNumber && !props.text && !props.sticky }" class="h-full md:block z-0">
        <button
            class="relative inline-flex items-center h-full px-3 group hover:text-gray-900 hover:dark:text-white disabled:dark:text-neutral-500 disabled:text-neutral-400 disabled:cursor-not-allowed"
            :class="{ 'text-gray-900 dark:text-white bg-gray-50 dark:bg-neutral-900': props.currentPage === props.pageNumber }"
            :disabled="props.disabled ?? false"
        >
            <slot name="content">
                <span>
                    {{ props?.text ?? props.pageNumber }}
                </span>
            </slot>

            <span
                v-if="!props.text || props.underline"
                class="box-content absolute bottom-0 w-0 h-px -mx-px duration-200 ease-out translate-y-px border-transparent group-hover:left-0 group-hover:w-full group-hover:border-l group-hover:border-r bg-violet-600 group-hover:border-violet-600"
                bg-neutral-900
                dark:bg-violet-600
                group-hover:border-neutral-900
                group-hover:dark:border-violet-600
                :class="{
                    'left-0 w-full border-l border-r': props.currentPage === props.pageNumber,
                    'left-1/2': props.currentPage !== props.pageNumber,
                }"
            >
                <!-- bg-neutral-900 dark:bg-violet-600 group-hover:border-neutral-900 group-hover:dark:border-violet-600 -->
            </span>
        </button>
    </li>
</template>
