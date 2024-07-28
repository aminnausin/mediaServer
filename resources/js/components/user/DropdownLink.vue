<script setup>
    import { RouterLink } from 'vue-router';
    const { linkData, selected, external } = defineProps(['linkData', 'selected', 'external'])
</script>
<template>
    <a v-if="external"
        :class="{ 'font-bold': selected}"  :href="linkData.url"
        class="first:rounded-t-md hover:bg-neutral-100 dark:hover:bg-neutral-950 flex justify-between w-full px-4 py-2 text-sm leading-5 text-left"
        role="menuitem" target="_blank"> {{ linkData.text }}
    </a>
    <RouterLink v-else
        :class="{ 'font-bold': selected}"  :to="linkData.url"
        class="first:rounded-t-md hover:bg-neutral-100 dark:hover:bg-neutral-950 flex justify-between w-full px-4 py-2 text-sm leading-5 text-left"
        role="menuitem"> {{ linkData.text }}
    </RouterLink>
</template>