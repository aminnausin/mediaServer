<script setup lang="ts">
import type { FolderResource, FolderTagResource, TagResource } from '@/types/resources';
import type { SeriesUpdateRequest } from '@/types/requests';
import type { SelectItem } from '@/types/types';
import type { FormField } from '@aminnausin/cedar-ui';

import { handleStorageURL, toCalendarFormattedDate } from '@/service/util';
import { FormInput, FormLabel, FormErrorList } from '@/components/cedar-ui/form';
import { computed, reactive, ref, watch } from 'vue';
import { handleEditFolderImages } from '@/service/folder/folderActions';
import { ButtonBase, ButtonForm } from '@/components/cedar-ui/button';
import { useDateFieldModel } from '@/components/cedar-ui/date-picker/useDateFieldModel';
import { FormNumberField } from '@/components/cedar-ui/number-field';
import { InputMultiChip } from '@/components/cedar-ui/multi-select';
import { useGetAllTags } from '@/service/queries';
import { FormTextArea } from '@/components/cedar-ui/textarea';
import { UseCreateTag } from '@/service/mutations';
import { DatePicker } from '@/components/cedar-ui/date-picker';
import { toast } from '@aminnausin/cedar-ui';

import ModalFormFooter from '@/components/forms/ModalFormFooter.vue';
import ProIconsPhoto from '@/components/icons/ProIconsPhoto.vue';
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
        min: 0,
        max: 100,
    },
    {
        name: 'episodes',
        text: isAudio.value ? 'Tracks' : 'Episodes',
        type: 'number',
        value: props.folder?.series?.episodes,
        subtext: `The number of ${isAudio.value ? 'tracks in the album or folder' : 'episodes in the series'}`,
        default: 0,
        min: 0,
    },
    {
        name: 'seasons',
        text: isAudio.value ? 'Discs' : 'Seasons',
        type: 'number',
        value: props.folder?.series?.seasons,
        subtext: `The number of ${isAudio.value ? 'discs in the album or folder' : 'seasons in the series'}`,
        default: 0,
        min: 0,
    },
    {
        name: 'films',
        text: isAudio.value ? 'Singles' : 'Films',
        type: 'number',
        value: props.folder?.series?.films,
        subtext: `The number of ${isAudio.value ? 'singles in the folder' : 'films in the series'}`,
        default: 0,
        min: 0,
    },
    {
        name: 'avg_intro_duration',
        text: 'Average Intro Duration',
        type: 'number',
        value: isAudio.value ? null : props.folder.series?.avg_intro_duration,
        disabled: isAudio.value,
        subtext: 'Assign a default intro duration for all videos in the folder',
        default: 90,
        min: 0,
    },
    {
        name: 'started_at',
        text: 'Start Date',
        type: 'date',
        value: toCalendarFormattedDate(props.folder?.series?.started_at),
        subtext: `The release date of the first ${isAudio.value ? 'track' : 'item'}`,
        default: null,
    },
    {
        name: 'ended_at',
        text: 'End Date',
        type: 'date',
        value: toCalendarFormattedDate(props.folder?.series?.ended_at),
        subtext: `The release date of the last ${isAudio.value ? 'track' : 'item'}`,
        default: null,
    },
    {
        name: 'thumbnail_url',
        text: 'Folder Thumbnail URL (read-only)',
        type: 'url',
        value: handleStorageURL(props.folder?.series?.thumbnail_url),
        subtext: `Use image editor below instead`,
        default: null,
        disabled: true,
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
    rating: props.folder?.series?.rating ?? null,
    episodes: props.folder?.series?.episodes ?? null,
    seasons: props.folder?.series?.seasons ?? null,
    films: props.folder?.series?.films ?? null,
    started_at: props.folder?.series?.started_at ?? '',
    ended_at: props.folder?.series?.ended_at ?? '',
    avg_intro_duration: props.folder.series?.avg_intro_duration ?? 0,
    thumbnail_url: props.folder.series?.thumbnail_url ?? null,
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
    <form class="flex flex-col flex-wrap gap-4 text-sm sm:flex-row sm:justify-between" @submit.prevent="handleSubmit">
        <div v-for="(field, index) in fields.filter((itm) => !(itm.disabled && !itm.value))" :key="index" class="w-full" :class="field.class">
            <FormLabel :for="field.name" :text="field.text" :subtext="field.subtext" />

            <FormTextArea v-if="field.type === 'textArea'" v-model="form.fields[field.name]" :field="field" />
            <DatePicker v-else-if="field.type === 'date'" v-model="useDateFieldModel(form, field.name).value" :field="field" />
            <FormNumberField v-else-if="field.type === 'number'" v-model="form.fields[field.name]" :field="field" />
            <InputMultiChip
                v-else-if="field.name === 'tags'"
                :field-name="field.name"
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

        <ModalFormFooter class="*:h-9">
            <ButtonBase
                variant="transparent"
                type="button"
                class="text-foreground-2 hover:text-foreground-0 xs:-ms-1 xs:mr-auto xs:max-h-none xs:px-1 max-h-6 gap-1.5 p-0 text-xs transition-colors"
                @click="() => handleEditFolderImages(props.folder)"
            >
                <ProIconsPhoto class="size-3.5" />
                Edit Images
            </ButtonBase>
            <ButtonForm variant="reset" :disabled="form.processing" @click="$emit('handleFinish')">Cancel</ButtonForm>
            <ButtonForm variant="submit" :disabled="form.processing" @click="handleSubmit">Save</ButtonForm>
        </ModalFormFooter>
    </form>
</template>
