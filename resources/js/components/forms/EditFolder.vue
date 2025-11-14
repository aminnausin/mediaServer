<script setup lang="ts">
import type { FolderResource, FolderTagResource, TagResource } from '@/types/resources';
import type { FormField, SelectItem } from '@/types/types';
import type { SeriesUpdateRequest } from '@/types/requests';

import { handleStorageURL, toCalendarFormattedDate } from '@/service/util';
import { computed, reactive, ref, watch } from 'vue';
import { useGetAllTags } from '@/service/queries';
import { UseCreateTag } from '@/service/mutations';
import { toast } from '@/service/toaster/toastService';

import FormInputNumber from '@/components/inputs/FormInputNumber.vue';
import InputMultiChip from '@/components/pinesUI/InputMultiChip.vue';
import FormInputLabel from '@/components/labels/FormInputLabel.vue';
import FormErrorList from '@/components/labels/FormErrorList.vue';
import FormTextArea from '@/components/inputs/FormTextArea.vue';
import DatePicker from '@/components/pinesUI/DatePicker.vue';
import FormInput from '@/components/inputs/FormInput.vue';
import mediaAPI from '@/service/mediaAPI.ts';
import useForm from '@/composables/useForm';

const emit = defineEmits(['handleFinish']);
const props = defineProps<{ folder: FolderResource }>();

const { data: tagsQuery } = useGetAllTags();
const createTag = UseCreateTag();

const isAudio = computed(() => {
    return props.folder?.is_majority_audio ?? false;
});

const allTags = ref<TagResource[]>([]);
// 'title', 'description', 'studio', 'seasons', 'episodes', 'films', 'date_start', 'date_end', 'thumbnail_url', 'editor_id';
const fields = reactive<FormField[]>([
    {
        name: 'title',
        text: 'Title',
        type: 'text',
        required: true,
        value: props.folder?.series?.title,
        default: props.folder?.series?.title,
        max: 255,
    },
    {
        name: 'description',
        text: 'Description',
        type: 'textArea',
        value: props.folder?.series?.description,
        placeholder: '',
        default: '',
    },
    {
        name: 'studio',
        text: isAudio.value ? 'Album Artist' : 'Studio',
        type: 'text',
        value: props.folder?.series?.studio,
        default: '',
    },
    {
        name: 'rating',
        text: 'Average Score',
        type: 'number',
        value: props.folder?.series?.rating,
        subtext: 'Percentage out of 100%',
        default: 0,
    },
    {
        name: 'episodes',
        text: isAudio.value ? 'Tracks' : 'Episodes',
        type: 'number',
        value: props.folder?.series?.episodes ?? 1,
        subtext: `The number of ${isAudio.value ? 'tracks in the album or folder' : 'episodes in the series'}`,
        default: 0,
        min: 0,
    },
    {
        name: 'seasons',
        text: isAudio.value ? 'Discs' : 'Seasons',
        type: 'number',
        value: props.folder?.series?.seasons ?? 1,
        subtext: `The number of ${isAudio.value ? 'discs in the album or folder' : 'seasons in the series'}`,
        default: 0,
        min: 0,
    },
    {
        name: 'films',
        text: isAudio.value ? 'Singles' : 'Films',
        type: 'number',
        value: props.folder?.series?.films ?? 1,
        subtext: `The number of ${isAudio.value ? 'singles in the folder' : 'films in the series'}`,
        default: 0,
        min: 0,
    },
    {
        name: 'date_start',
        text: 'Start Date',
        type: 'date',
        value: props.folder?.series?.date_start ? toCalendarFormattedDate(props.folder?.series?.date_start) : null,
        subtext: `The release date of the first ${isAudio.value ? 'track' : 'item'}`,
        default: null,
    },
    {
        name: 'date_end',
        text: 'End Date',
        type: 'date',
        value: props.folder?.series?.date_end ? toCalendarFormattedDate(props.folder?.series?.date_end) : null,
        subtext: `The release date of the last ${isAudio.value ? 'track' : 'item'}`,
        default: null,
    },
    {
        name: 'thumbnail_url',
        text: 'Folder Thumbnail URL',
        type: 'url',
        value: handleStorageURL(props.folder?.series?.thumbnail_url),
        subtext: `A thumbnail associated with the ${isAudio.value ? 'album or folder' : 'series'}`,
        default: null,
    },
    {
        name: 'tags',
        text: 'Tags',
        type: 'multi',
        value: props.folder?.series?.folder_tags ?? [],
        default: props.folder?.series?.folder_tags ?? [],
        subtext: `Tags that describe the ${isAudio.value ? 'album or ' : ''}folder`,
        max: 24,
    },
]);

const form = useForm<SeriesUpdateRequest>({
    folder_id: props.folder.id,
    title: props.folder?.series?.title ?? props.folder?.name,
    description: props.folder?.series?.description ?? '',
    studio: props.folder?.series?.studio ?? '',
    rating: props.folder?.series?.rating?.toString() ?? null,
    episodes: props.folder?.series?.episodes?.toString() ?? null,
    seasons: props.folder?.series?.seasons?.toString() ?? null,
    films: props.folder?.series?.films?.toString() ?? null,
    date_start: props.folder?.series?.date_start ? toCalendarFormattedDate(props.folder?.series?.date_start) : null,
    date_end: props.folder?.series?.date_end ? toCalendarFormattedDate(props.folder?.series?.date_end) : null,
    thumbnail_url: handleStorageURL(props.folder?.series?.thumbnail_url) ?? null,
    tags: props.folder.series?.folder_tags ?? [],
    deleted_tags: [],
});

const handleSubmit = async () => {
    form.submit(
        async (fields) => {
            if (!props.folder?.series?.id) {
                return mediaAPI.createSeries({ ...fields, folder_id: -1 });
            }

            return mediaAPI.updateSeries(props.folder?.series?.id, { ...fields });
        },
        {
            onSuccess: (response) => {
                emit('handleFinish', response?.data);
                toast.add('Success', { type: 'success', description: 'Edit submitted!', life: 3000 });
            },
            onError: () => {
                toast.add('Error', { type: 'danger', description: 'Unable to update folder details.', life: 3000 });
            },
        },
    );
};

const handleCreateTag = async (name: string) => {
    try {
        const { data: response } = await createTag.mutateAsync({ name });

        toast.add('Success', { type: 'success', description: 'Tag created!', life: 3000 });
        form.fields['tags'] = [...form.fields['tags'], { id: response.id, name: response.name }];
    } catch (error) {
        console.log(error);
        toast.error('Unable to create tag. It may already exist.');
    }
};

const handleSetTags = (newTags: FolderTagResource[]) => {
    const existingTags = props.folder?.series?.folder_tags ?? [];

    form.fields['tags'] = newTags.map((tag) => ({
        id: tag.id,
        name: tag.name,
        folder_tag_id: existingTags.find((existingTag) => existingTag.id === tag.id)?.folder_tag_id,
    }));

    // Take tags from existing list
    form.fields['deleted_tags'] = existingTags.filter((existingTag) => !newTags.some((tag) => tag.id === existingTag.id)).map((tag) => tag.folder_tag_id);
};

const handleRemoveTag = (tag: FolderTagResource) => {
    form.fields['tags'] = form.fields['tags']?.filter((itm) => itm.name !== tag.name);

    if (tag.folder_tag_id) form.fields['deleted_tags'] = [...form.fields['deleted_tags'], tag.folder_tag_id];
};

watch(tagsQuery, () => {
    if (tagsQuery.value?.data?.data) {
        allTags.value = tagsQuery.value.data.data; // Array of tag resources
    }
});
</script>

<template>
    <form class="flex flex-col sm:flex-row sm:justify-between flex-wrap gap-4" @submit.prevent="handleSubmit">
        <div v-for="(field, index) in fields" :key="index" class="w-full" :class="field.class">
            <FormInputLabel :field="field" />

            <FormTextArea v-if="field.type === 'textArea'" v-model="form.fields[field.name]" :field="field" />
            <DatePicker v-else-if="field.type === 'date'" v-model="form.fields[field.name]" :field="field" />
            <FormInputNumber v-else-if="field.type === 'number'" v-model="form.fields[field.name]" :field="field" />
            <InputMultiChip
                v-else-if="field.name === 'tags'"
                :placeholder="'Add tags'"
                :defaultItems="(form.fields[field.name] as SelectItem[]) ?? []"
                :options="allTags"
                :max="field.max"
                @createAction="handleCreateTag"
                @selectItems="handleSetTags"
                @removeAction="handleRemoveTag"
            />
            <FormInput v-else v-model="form.fields[field.name]" :field="field" />

            <FormErrorList :errors="form.errors" :field-name="field.name" />
        </div>
        <div class="relative flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-1 w-full">
            <button
                @click="$emit('handleFinish')"
                type="button"
                class="inline-flex items-center justify-center h-10 px-4 py-2 text-sm font-medium transition-colors border dark:border-neutral-600 rounded-md focus:outline-hidden"
                :class="'focus:ring-1 focus:ring-neutral-100 dark:focus:ring-neutral-400 focus:ring-offset-1 hover:bg-neutral-100 dark:hover:bg-neutral-900'"
                :disabled="form.processing"
            >
                Cancel
            </button>
            <button
                @click="handleSubmit"
                type="button"
                class="inline-flex items-center justify-center h-10 px-4 py-2 text-sm font-medium text-white transition-colors border border-transparent rounded-md focus:outline-hidden"
                :class="'focus:ring-1 focus:ring-violet-900 focus:ring-offset-1 bg-neutral-950 hover:bg-neutral-800 dark:hover:bg-neutral-900 '"
                :disabled="form.processing"
            >
                Submit Details
            </button>
        </div>
    </form>
</template>
