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
    <li :class="{ hidden: props.currentPage !== props.pageNumber && !props.text && !props.sticky }" class="z-0 h-full md:block">
        <button
            class="group relative inline-flex h-full items-center px-3 hover:text-gray-900 disabled:cursor-not-allowed disabled:text-neutral-400 dark:hover:text-white dark:disabled:text-neutral-500"
            :class="{ 'bg-gray-50 text-gray-900 dark:bg-neutral-900 dark:text-white': props.currentPage === props.pageNumber }"
            :disabled="props.disabled ?? false"
        >
            <slot name="content">
                <span>
                    {{ props?.text ?? props.pageNumber }}
                </span>
            </slot>

            <span
                v-if="!props.text || props.underline"
                class="absolute bottom-0 -mx-px box-content h-px w-0 translate-y-px border-transparent bg-violet-600 duration-200 ease-out group-hover:left-0 group-hover:w-full group-hover:border-r group-hover:border-l group-hover:border-violet-600"
                bg-neutral-900
                dark:bg-violet-600
                group-hover:border-neutral-900
                dark:group-hover:border-violet-600
                :class="{
                    'left-0 w-full border-r border-l': props.currentPage === props.pageNumber,
                    'left-1/2': props.currentPage !== props.pageNumber,
                }"
            >
                <!-- bg-neutral-900 dark:bg-violet-600 group-hover:border-neutral-900 dark:group-hover:border-violet-600 -->
            </span>
        </button>
    </li>
</template>
