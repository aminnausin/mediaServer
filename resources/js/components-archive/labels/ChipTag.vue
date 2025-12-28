<script setup lang="ts">
import { RouterLink } from 'vue-router';

import ButtonCorner from '@/components-archive/buttons/ButtonCorner.vue';

const props = defineProps(['label', 'colour', 'textClass', 'URL', 'removeable']);
</script>
<template>
    <RouterLink
        v-if="props.URL"
        :class="`p-1 px-2 text-sm ${props.textClass ? `${props.textClass} ` : ''}leading-none truncate rounded-xl text-neutral-100 lowercase dark:text-neutral-300 ${props.colour ? `${props.colour}` : 'bg-violet-600 dark:bg-violet-900'} shrink-0 transition-colors duration-200`"
        :to="props.URL"
    >
        <slot name="content">
            {{ props.label }}
        </slot>
        <ButtonCorner
            v-if="removeable"
            :positionClasses="'w-4 h-4'"
            :textClasses="`text-white dark:text-rose-700`"
            :colourClasses="'dark:bg-neutral-900/20 dark:hover:bg-rose-700 hover:bg-rose-300 hover:text-rose-700 dark:hover:text-white drop-shadow-sm'"
            :label="'Remove'"
            @click.stop.prevent="$emit('clickAction')"
        />
    </RouterLink>
    <p
        v-else
        :class="`p-1 px-2 text-sm ${props.textClass ? `${props.textClass} ` : ''}leading-none truncate rounded-xl text-neutral-100 lowercase dark:text-neutral-300 ${props.colour ? `${props.colour}` : 'bg-violet-600 dark:bg-violet-900'} shrink-0 cursor-default transition-colors duration-200 ${removeable ? 'flex items-center justify-between gap-1' : ''}`"
    >
        <slot name="content">
            {{ props.label }}
        </slot>
        <ButtonCorner
            v-if="removeable"
            :positionClasses="'w-4 h-4'"
            :textClasses="`text-white dark:text-rose-700 ${textClass ?? ''}`"
            :colourClasses="'dark:bg-neutral-900/20 dark:hover:bg-rose-700 hover:bg-rose-300 hover:text-rose-700 dark:hover:text-white drop-shadow-sm'"
            :label="'Remove'"
            @click.stop.prevent="$emit('clickAction')"
        />
    </p>
</template>
