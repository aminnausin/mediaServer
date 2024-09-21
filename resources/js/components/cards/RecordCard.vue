<script setup>
import ButtonCorner from '../inputs/ButtonCorner.vue';
import CircumShare1 from '~icons/circum/share-1';
import CircumPlay1 from '~icons/circum/play-1';

import { toTimeSpan } from '../../service/util';
import { computed } from 'vue';
    
const props = defineProps(['record']);
const rawDate = new Date(props.record.attributes.created_at.replace(' ', 'T'));
const timeSpan = toTimeSpan(rawDate);

const videoLink = computed(() => {
    if(!props.record.relationships.video_id) return false;
    return (`/${encodeURIComponent(props.record.relationships.category_name)}/${encodeURIComponent(props.record.relationships.folder_name)}?video=${props.record.relationships.video_id}`);
})
</script>

<template>


    <div 
        class="text-left relative flex flex-col gap-4 lg:gap-2 sm:flex-row flex-wrap rounded-lg dark:bg-primary-dark-800/70 bg-primary-800 dark:hover:bg-primary-dark-600 hover:bg-gray-200 dark:text-white shadow p-3 w-full group cursor-pointer divide-gray-300 dark:divide-gray-400">
        <section class="flex justify-between gap-4 w-full">
            <h2 class="text-xl w-full truncate" :title=(props.record.relationships.video_name)>{{ props.record.relationships.video_name }}</h2>
            <div class="flex justify-end gap-1">
                <ButtonCorner :positionClasses="'w-7 h-7'"
                    :textClasses="'hover:text-neutral-900 dark:hover:text-violet-400'"
                    :colourClasses="'dark:hover:bg-neutral-800 hover:bg-gray-300'"
                    :label="'Share Folder'"
                    @click.stop.prevent="$emit('clickAction', videoLink)">
                    <template #icon>
                        <CircumShare1 width="20" height="20" />
                    </template>
                </ButtonCorner>
                <ButtonCorner v-if="videoLink" :positionClasses="'w-7 h-7'"
                    :textClasses="`hover:text-violet-600 dark:hover:text-violet-500`"
                    :colourClasses="'dark:hover:bg-neutral-800 hover:bg-gray-300'" :to="videoLink"
                    :label="'Watch Video'">
                    <template #icon>
                        <CircumPlay1 width="20" height="20" />
                    </template>
                </ButtonCorner>
            </div>
        </section>
        <section class="flex flex-col sm:flex-row sm:justify-between w-full">
            <h3 class="hidden lg:block text-neutral-500 w-full text-wrap truncate sm:text-nowrap">
                {{ props.record.relationships.folder_name }}
            </h3>
            <h3 class="hidden lg:block truncate text-right text-neutral-500 w-full line-clamp-2">
                {{ timeSpan }}
            </h3>
            <h3 class="lg:hidden text-neutral-500 w-full text-wrap truncate sm:text-nowrap">
                {{ props.record.relationships.folder_name }} · {{ timeSpan }}
            </h3>
            <h3 class="lg:hidden truncate sm:text-right text-neutral-500 w-full line-clamp-2">
                {{ 
                    `${rawDate.toLocaleDateString('en-ca', {
                        year: "numeric",
                        month: '2-digit', day: '2-digit', hour: '2-digit', hour12: false, minute: '2-digit'
                    })}` 
                }}
            </h3>
        </section>
    </div>
</template>