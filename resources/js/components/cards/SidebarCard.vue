<script setup lang="ts">
import { RouterLink } from 'vue-router';

import ButtonCorner from '@/components/inputs/ButtonCorner.vue';

import CircumFolderOn from '~icons/circum/folder-on';
import CircumShare1 from '~icons/circum/share-1';

const props = defineProps<{
    link: string;
    title?: string;
    class?: string;
}>();
</script>

<template>
    <RouterLink
        :to="link"
        :class="`flex flex-col sm:flex-row flex-wrap gap-4 lg:gap-2 relative
        w-full group cursor-pointer rounded-lg shadow p-3
        text-left dark:text-white
        dark:bg-primary-dark-800/70 bg-primary-800
        dark:hover:bg-primary-dark-600 hover:bg-gray-200
        divide-gray-300 dark:divide-gray-400 ${props.class}`"
    >
        <section class="flex justify-between gap-4 w-full items-center">
            <slot name="header">
                <h2 class="w-full truncate" :title="title">{{ title }}</h2>
                <div class="flex justify-end gap-1">
                    <ButtonCorner
                        :positionClasses="'w-7 h-7'"
                        :textClasses="'hover:text-violet-600 dark:hover:text-violet-500'"
                        :colourClasses="'dark:hover:bg-neutral-800 hover:bg-gray-300'"
                        :label="'Share'"
                        @click.stop.prevent="$emit('clickAction', link)"
                    >
                        <template #icon>
                            <CircumShare1 width="20" height="20" />
                        </template>
                    </ButtonCorner>
                    <ButtonCorner
                        :positionClasses="'w-7 h-7'"
                        :textClasses="`${true ? 'text-violet-600' : 'hover:text-violet-600'} dark:hover:text-violet-500`"
                        :colourClasses="'dark:hover:bg-neutral-800 hover:bg-gray-300'"
                        :to="''"
                        :label="'Open Folder'"
                    >
                        <template #icon>
                            <CircumFolderOn width="20" height="20" />
                        </template>
                    </ButtonCorner>
                </div>
            </slot>
        </section>
        <!-- Extra information if required<section class="flex flex-col sm:flex-row sm:justify-between w-full flex-wrap gap-2 text-sm">
            <slot name="body">

                <h3 class="text-neutral-500 w-full text-wrap truncate sm:text-nowrap flex-1" :title="`${2} Episode${2 > 1 ? 's' : ''}`">{{ 2 }} Episode{{ 2 > 1 ? 's' : '' }}</h3>
                <h3 class="truncate text-nowrap sm:text-right text-neutral-500 w-fit lg:hidden xl:block">
                    some other folder statistic or data like number of seasons or if its popular or something
                    {{ '20gb' }}
                </h3>
            </slot>
        </section>
                -->
    </RouterLink>
</template>
