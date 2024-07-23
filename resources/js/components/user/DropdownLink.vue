<script setup>
    import { RouterLink } from 'vue-router';
    const { linkData, selected } = defineProps(['linkData', 'selected'])
</script>
<template>
    <RouterLink  
        :class="{ 'font-bold': selected}"  :to="linkData.url"
        class="rounded-t-md hover:bg-neutral-100 flex justify-between w-full px-4 py-2 text-sm leading-5 text-left"
        role="menuitem"> {{ linkData.text }}
    </RouterLink>
</template>