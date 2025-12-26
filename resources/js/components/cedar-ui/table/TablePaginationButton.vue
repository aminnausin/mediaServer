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
    <li :class="{ hidden: currentPage !== pageNumber && !text && !sticky }" class="z-0 h-full md:block">
        <button-base
            class="group hover:text-foreground-0 disabled:text-foreground-2 pointer-events-auto! relative inline-flex h-full rounded-none px-3"
            :class="{
                'text-foreground-0 bg-overlay-accent': currentPage === pageNumber,
                'hover:bg-overlay-accent': !disabled,
            }"
            :disabled="disabled ?? false"
            :use-size="false"
        >
            <slot name="content">
                <span>
                    {{ text ?? pageNumber }}
                </span>
            </slot>

            <span
                v-if="!text || underline"
                :class="
                    cn(
                        'bg-primary group-hover:border-primary absolute bottom-0 -mx-px box-content h-px w-0 translate-y-px border-transparent duration-200 ease-out group-hover:left-0 group-hover:w-full group-hover:border-r group-hover:border-l',
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
