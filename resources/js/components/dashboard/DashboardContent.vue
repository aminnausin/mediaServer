<script setup lang="ts">
import type { CategoryResource } from '@/types/resources';

import { onMounted, ref } from 'vue';
import { getCategories } from '@/service/mediaAPI';

import ButtonText from '@/components/inputs/ButtonText.vue';
import ButtonIcon from '@/components/inputs/ButtonIcon.vue';

import ProiconsAddCircle from '~icons/proicons/add-circle';
import CircumEdit from '~icons/circum/edit';

const categories = ref<CategoryResource[]>([]);

onMounted(async () => {
    const { data: rawCategories } = await getCategories();

    categories.value = rawCategories?.data;
});
</script>
<template>
    <section>
        <span class="flex items-center justify-between">
            <ButtonText title="Add New Category">
                <template #text>
                    <div class="flex justify-between items-center gap-2">
                        <p class="text-nowrap">Add New Category</p>
                        <ProiconsAddCircle height="24" width="24" />
                    </div>
                </template>
            </ButtonText>
            <p class="text-slate-400 uppercase">Categories: {{ categories.length }}</p>
        </span>
        <div class="flex flex-wrap w-full pt-4">
            <div
                v-for="(category, index) in categories"
                :key="index"
                class="flex flex-col gap-2 p-4 overflow-clip rounded-xl shadow-lg dark:bg-primary-dark-800/70 bg-white ring-1 ring-gray-900/5 w-1/3"
            >
                <h3 class="capitalize text-lg flex items-end justify-between">
                    {{ category.name }}
                    <ButtonIcon class="w-6 h-6 !p-1">
                        <template #icon>
                            <CircumEdit class="w-full h-full" />
                        </template>
                    </ButtonIcon>
                </h3>
                <p class="text-slate-400">Folders: {{ category.folders_count }}</p>
            </div>
        </div>
    </section>
</template>
