<script setup>
import { RouterLink } from 'vue-router';

import ButtonCorner from '../inputs/ButtonCorner.vue';

const props = defineProps(['label', 'colour', 'URL', 'removeable']);

const styling = `p-1 px-2 text-sm leading-none rounded-xl truncate lowercase text-neutral-100 dark:text-neutral-300 ${props.colour ? `bg-${props.colour}` : 'bg-violet-600 dark:bg-violet-900/90'} shrink-0`;
</script>
<template>
    <RouterLink v-if="props.URL" :class="styling" :to="props.URL">
        {{ props.label }}
        <ButtonCorner
            v-if="removeable"
            :positionClasses="'w-4 h-4'"
            :textClasses="'text-white dark:text-rose-700'"
            :colourClasses="'dark:bg-neutral-900/20 dark:hover:bg-rose-700 hover:bg-rose-300 hover:text-rose-700 dark:hover:text-white drop-shadow'"
            :label="'Remove'"
            @click.stop.prevent="$emit('clickAction')"
        />
    </RouterLink>
    <p v-else :class="`${styling} ${removeable ? 'flex gap-1 items-center justify-between' : ''}`">
        {{ props.label }}
        <ButtonCorner
            v-if="removeable"
            :positionClasses="'w-4 h-4'"
            :textClasses="'text-white dark:text-rose-700'"
            :colourClasses="'dark:bg-neutral-900/20 dark:hover:bg-rose-700 hover:bg-rose-300 hover:text-rose-700 dark:hover:text-white drop-shadow'"
            :label="'Remove'"
            @click.stop.prevent="$emit('clickAction')"
        />
    </p>
</template>
