<script setup lang="ts">
import type { TablePaginationButtonProps } from '@aminnausin/cedar-ui';

import { ButtonBase } from '@/components/cedar-ui/button';
import { cn } from '@aminnausin/cedar-ui';

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
                cn('group hover:text-foreground-0 disabled:text-foreground-2/60 flex h-full flex-col gap-0 rounded-none py-0 opacity-100!', {
                    'text-foreground-0 bg-overlay-accent': currentPage === pageNumber,
                    'hover:bg-overlay-accent focus-visible:bg-overlay-accent': !disabled,
                    'px-3': !underline && $slots.content,
                })
            "
            :disabled="disabled ?? false"
            :use-size="false"
            :animate-scale="false"
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
                        'bg-primary -mx-px mt-auto box-content h-px w-0 translate-y-px border-transparent duration-(--duration-input) ease-out',
                        'group-hover:border-primary group-hover:w-full group-hover:border-x',
                        'in-focus-visible:max-w-0 in-focus-visible:border-none',
                        {
                            'w-full border-x': currentPage === pageNumber,
                        },
                    )
                "
            ></span>
        </button-base>
    </li>
</template>
