<script setup>
import ButtonCorner from '../inputs/ButtonCorner.vue';
import CircumPlay1 from '~icons/circum/play-1';

import { toTimeSpan } from '../../service/util';
import { computed } from 'vue';

const props = defineProps(['data']);

const rawDate = new Date(props.data.attributes.created_at.replace(' ', 'T'));
const timeSpan = toTimeSpan(rawDate);

const videoLink = computed(() => {
    return `/${encodeURIComponent(props.data.relationships.category_name)}/${encodeURIComponent(props.data.relationships.folder_name)}?video=${props.data.relationships.video_id}`;
});
</script>

<template>
    <button
        @click.stop.prevent="$emit('clickAction')"
        class="text-left relative flex flex-col gap-4 sm:flex-row flex-wrap rounded-xl dark:bg-primary-dark-800/70 bg-primary-800 dark:hover:bg-primary-dark-600 hover:bg-gray-200 dark:text-white shadow p-3 w-full group cursor-pointer divide-gray-300 dark:divide-neutral-400"
    >
        <section class="flex justify-between gap-4 w-full">
            <h2 class="text-xl w-full truncate">{{ props.data.relationships.video_name }}</h2>
            <div class="flex justify-end gap-1">
                <ButtonCorner
                    :positionClasses="'w-7 h-7'"
                    :textClasses="'hover:text-violet-600 dark:hover:text-violet-500'"
                    :colourClasses="'dark:hover:bg-neutral-800 hover:bg-gray-300'"
                    :to="videoLink"
                >
                    <template #icon>
                        <CircumPlay1 width="20" height="20" />
                    </template>
                </ButtonCorner>
                <ButtonCorner
                    :positionClasses="'w-7 h-7'"
                    :textClasses="'text-rose-700'"
                    :colourClasses="'dark:hover:bg-neutral-800 hover:bg-gray-300'"
                    @click.stop.prevent="$emit('clickAction')"
                />
            </div>
        </section>
        <section class="flex flex-col sm:flex-row sm:justify-between w-full">
            <h3 class="text-neutral-500 w-full text-wrap truncate sm:text-nowrap">
                {{ props.data.relationships.folder_name }} · {{ timeSpan }}
            </h3>
            <h3 class="truncate sm:text-right text-neutral-500 w-full line-clamp-2">
                {{
                    `${rawDate.toLocaleDateString('en-ca', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit',
                        hour: '2-digit',
                        hour12: false,
                        minute: '2-digit',
                    })}`
                }}
            </h3>
        </section>
    </button>
</template>
