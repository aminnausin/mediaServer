<script setup lang="ts">
import { ref } from 'vue';

const activeAccordion = ref<string>('');
const accordions = ref<{ id: string; key: string; value: string }[]>([
    { id: '1', key: 'What is Pines?', value: 'Pines is a UI library built for AlpineJS and TailwindCSS.' },
    {
        id: '2',
        key: 'How do I install Pines?',
        value: 'Add AlpineJS and TailwindCSS to your page and then copy and paste any of these elements into your project.',
    },
    {
        id: '3',
        key: 'Can I use Pines with other libraries or frameworks?',
        value: 'Absolutely! Pines works with any other library or framework. Pines works especially well with the TALL stack.',
    },
]);

function setActiveAccordion(id: string) {
    activeAccordion.value = activeAccordion.value == id ? '' : id;
}
</script>
<template>
    <div class="relative w-full mx-auto overflow-hidden text-sm font-normal bg-white border border-gray-200 divide-y divide-gray-200 rounded-md">
        <div v-for="accordion in accordions" :key="accordion.id" class="cursor-pointer group">
            <button @click="setActiveAccordion(accordion.id)" class="flex items-center justify-between w-full p-4 text-left select-none group-hover:underline">
                <span>{{ accordion.key }}</span>
                <svg
                    :class="`w-4 h-4 duration-200 ease-out ${activeAccordion == accordion.id ? 'rotate-180' : ''}`"
                    viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>
            <Transition
                enter-active-class=""
                enter-from-class="h-0 overflow-hidden"
                enter-to-class="h-full overflow-hidden"
                leave-active-class=""
                leave-from-class="h-0 overflow-hidden"
                leave-to-class="h-0 overflow-hidden"
            >
                <div class="ease-in duration-300" v-show="activeAccordion == accordion.id" v-cloak>
                    <div class="p-4 pt-0 opacity-70">{{ accordion.value }}</div>
                </div>
            </Transition>
        </div>
    </div>
</template>
