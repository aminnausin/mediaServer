<script setup lang="ts">
import { cn, type TablePaginationButtonProps } from '@aminnausin/cedar-ui';

import { ButtonBase } from '../button';

const props = withDefaults(defineProps<TablePaginationButtonProps>(), {
    underline: false,
    sticky: false,
    disabled: false,
});
</script>

<template>
    <li :class="{ hidden: currentPage !== pageNumber && !text && !sticky }" class="h-full @xs:block">
        <button-base
            :class="
                cn('group hover:text-foreground-0 disabled:text-foreground-2/60 pointer-events-auto! flex h-full flex-col gap-0 rounded-none py-0 opacity-100!', {
                    'text-foreground-0 bg-overlay-accent': currentPage === pageNumber,
                    'hover:bg-overlay-accent': !disabled,
                    'px-3': !underline && $slots.content,
                })
            "
            :disabled="disabled ?? false"
            :use-size="false"
        >
            <slot name="content">
                <span class="-mb-px flex h-full flex-1 items-center px-3 text-center">
                    {{ text ?? pageNumber }}
                </span>
            </slot>

            <span
                v-if="!text || underline"
                :class="
                    cn(
                        'bg-primary border-transparent duration-(--duration-input) ease-out',
                        '-mx-px mt-auto box-content h-px w-0 translate-y-px',
                        'group-hover:border-primary group-hover:left-0 group-hover:w-full group-hover:border-x',
                        {
                            'left-0 w-full border-r border-l': currentPage === pageNumber,
                            'left-1/2': currentPage !== pageNumber,
                        },
                    )
                "
            >
            </span>
        </button-base>
    </li>
</template>
