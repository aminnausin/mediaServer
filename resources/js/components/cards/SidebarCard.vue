<script setup lang="ts">
import { computed, useSlots } from 'vue';
import { RouterLink } from 'vue-router';

import ButtonCorner from '@/components/inputs/ButtonCorner.vue';

import CircumFolderOn from '~icons/circum/folder-on';
import CircumShare1 from '~icons/circum/share-1';

const props = defineProps<{
    link?: string;
    to?: string;
    title?: string;
}>();

const slots = useSlots();
const isCompleteElement = computed(() => !!slots.header && !!slots.body);
</script>

<template>
    <a
        v-if="to"
        :href="to"
        :class="[
            'flex flex-col sm:flex-row flex-wrap relative',
            'w-full group cursor-pointer rounded-lg shadow p-3',
            'text-left text-neutral-600 dark:text-neutral-400',
            'dark:bg-primary-dark-800/70 bg-primary-800',
            'dark:hover:bg-primary-dark-600 hover:bg-gray-200',
            { 'gap-4 lg:gap-2 ': isCompleteElement },
        ]"
    >
        <section class="flex justify-between gap-4 w-full items-center">
            <slot name="header">
                <h3 class="w-full truncate dark:text-white" :title="title">{{ title }}</h3>
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
        <section class="flex flex-col sm:flex-row sm:justify-between w-full flex-wrap gap-2 text-sm">
            <slot name="body">
                <h4 class="w-full text-wrap truncate sm:text-nowrap flex-1" v-if="false">Episodes: 2</h4>
                <h4 class="truncate text-nowrap sm:text-right w-fit hidden" v-if="false">
                    <!-- some other folder statistic or data like number of seasons or if its popular or something -->
                    {{ '20gb' }}
                </h4>
            </slot>
        </section>
    </a>
    <RouterLink
        v-else-if="link"
        :to="link"
        :class="[
            'flex flex-col sm:flex-row flex-wrap relative',
            'w-full group cursor-pointer rounded-lg shadow p-3',
            'text-left text-neutral-600 dark:text-neutral-400',
            'dark:bg-primary-dark-800/70 bg-primary-800',
            'dark:hover:bg-primary-dark-600 hover:bg-gray-200',
            { 'gap-4 lg:gap-2 ': isCompleteElement },
        ]"
    >
        <section class="flex justify-between gap-4 w-full items-center">
            <slot name="header">
                <h3 class="w-full truncate dark:text-white" :title="title">{{ title }}</h3>
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
        <section class="flex flex-col sm:flex-row sm:justify-between w-full flex-wrap gap-2 text-sm">
            <slot name="body">
                <h4 class="w-full text-wrap truncate sm:text-nowrap flex-1" v-if="false">Episodes: 2</h4>
                <h4 class="truncate text-nowrap sm:text-right w-fit hidden" v-if="false">
                    <!-- some other folder statistic or data like number of seasons or if its popular or something -->
                    {{ '20gb' }}
                </h4>
            </slot>
        </section>
    </RouterLink>
</template>
