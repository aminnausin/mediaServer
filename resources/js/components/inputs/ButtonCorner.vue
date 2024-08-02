<script setup>
import IconDelete from '../icons/IconDelete.vue';
const props = defineProps(['positionClasses', 'colourClasses', 'textClasses', 'to', 'label']);
</script>

<template>
    <router-link v-if="props?.to" :aria-label="props?.label ?? 'Link'" :to="props?.to ?? '/'"
        class="flex items-center justify-center rounded-full"
        :class="`${props?.positionClasses ?? 'absolute top-0 right-0 w-8 h-8 mt-5 mr-5'} ${props?.colourClasses ?? 'hover:bg-gray-50 hover:dark:bg-gray-700'} ${ props?.textClasses ?? 'text-gray-600 dark:text-gray-400 hover:text-gray-800 hover:dark:text-gray-200'}`">
        <slot name="icon">
            <IconDelete/>
        </slot>
    </router-link>
    <button v-else :aria-label="props?.label ?? 'Close Modal'"
        class="flex items-center justify-center rounded-full"
        :class="`${props?.positionClasses ?? 'absolute top-0 right-0 w-8 h-8 mt-5 mr-5'} ${props?.colourClasses ?? 'hover:bg-gray-50 hover:dark:bg-gray-700'} ${ props?.textClasses ?? 'text-gray-600 dark:text-gray-400 hover:text-gray-800 hover:dark:text-gray-200'}`">
        <slot name="icon">
            <IconDelete/>
        </slot>
    </button>
</template>