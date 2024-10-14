<script setup>
import { RouterLink } from 'vue-router';

const props = defineProps(['label', 'colour', 'URL', 'removeable']);
</script>
<template>
    <RouterLink
        v-if="props.URL"
        :class="`p-1 rounded-xl truncate text-slate-300 bg-${props.colour ? `bg-${props.colour}` : 'bg-violet-900/70'} shrink-0`"
        :to="props.URL"
    >
        #{{ props.label }}
    </RouterLink>
    <p
        v-else
        :class="`p-1 px-2 text-sm leading-none rounded-xl truncate text-slate-300 ${props.colour ? `bg-${props.colour}` : 'bg-violet-900/70'} shrink-0`"
    >
        {{ props.label }}
    </p>
</template>
