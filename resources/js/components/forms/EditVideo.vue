<script setup lang="ts">
import type { TagResource, VideoResource, VideoTagResource } from '@/types/resources';
import type { FormField, SelectItem } from '@/types/types';
import type { MetadataUpdateRequest } from '@/types/requests';

import { computed, reactive, ref, watch } from 'vue';
import { toCalendarFormattedDate } from '@/service/util';
import { useGetAllTags } from '@/service/queries';
import { UseCreateTag } from '@/service/mutations';
import { MediaType } from '@/types/types';
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
const props = defineProps<{ video: VideoResource }>();

const { data: tagsQuery } = useGetAllTags();
const createTag = UseCreateTag();

const isAudio = computed(() => {
    return props.video.metadata?.media_type === MediaType.AUDIO;
});

const allTags = ref<TagResource[]>([]);
const fields = reactive<FormField[]>([
    {
        name: 'title',
        text: 'Title',
        type: 'text',
        required: true,
        value: props.video?.title,
        default: props.video?.name,
        max: 255,
    },
    {
        name: 'description',
        text: 'Description',
        type: 'textArea',
        value: props.video?.description,
        placeholder: 'No description yet.',
        default: '',
    },
    {
        name: 'lyrics',
        text: `Embedded Lyrics`,
        type: 'textArea',
        value: props.video?.metadata?.lyrics,
        subtext: 'Format: [mm:ss] line',
        placeholder: `No lyrics yet.`,
        disabled: !isAudio.value,
        default: '',
    },
    {
        name: 'episode',
        text: 'Episode',
        type: 'number',
        value: props.video?.episode ?? 1,
        default: 0,
        min: 0,
        disabled: props.video.metadata?.media_type !== 0,
    },
    {
        name: 'season',
        text: 'Season',
        type: 'number',
        value: props.video?.season ?? 1,
        default: 0,
        min: 0,
        disabled: props.video.metadata?.media_type !== 0,
    },
    {
        name: 'episode',
        text: 'Track',
        type: 'number',
        value: props.video?.episode ?? 1,
        default: 0,
        min: 0,
        disabled: props.video.metadata?.media_type !== 1,
    },
    {
        name: 'season',
        text: 'Disc',
        type: 'number',
        value: props.video?.season ?? 1,
        default: 0,
        min: 0,
        disabled: props.video.metadata?.media_type !== 1,
    },
    {
        name: 'poster_url',
        text: 'Thumbnail URL',
        type: 'url',
        value: props.video?.metadata?.poster_url,
        subtext: `Give the ${isAudio.value ? 'song' : 'video'} a thumbnail`,
        default: null,
    },
    {
        name: 'date_released',
        text: 'Release Date',
        type: 'date',
        value: props.video?.date_released ? toCalendarFormattedDate(props.video?.date_released) : null,
        default: null,
    },
    {
        name: 'video_tags',
        text: 'Tags',
        type: 'multi',
        value: props.video?.video_tags ?? [],
        default: props.video?.video_tags ?? [],
        subtext: `Describe the ${isAudio.value ? 'song' : 'video'} with tags`,
        max: 24,
    },
]);

const form = useForm<MetadataUpdateRequest>({
    title: props.video?.title ?? props.video?.name,
    description: props.video?.description ?? '',
    lyrics: props.video?.metadata?.lyrics ?? '',
    episode: props.video?.episode?.toString() ?? '',
    season: props.video?.season?.toString() ?? '',
    poster_url: props.video?.metadata?.poster_url ?? '',
    date_released: props.video?.date_released ? toCalendarFormattedDate(props.video?.date_released) : '',
    video_tags: props.video?.video_tags ?? [],
    deleted_tags: [],
});

const handleSubmit = async () => {
    form.submit(
        async (fields) => {
            if (props.video?.metadata?.id) {
                return mediaAPI.updateMetadata(props.video.metadata.id, fields);
            } else return mediaAPI.createMetadata({ ...fields, video_id: props.video.id });
        },
        {
            onSuccess: (response) => {
                emit('handleFinish', response?.data);
                toast.add('Success', { type: 'success', description: 'Edit submitted!', life: 3000 });
            },
            onError: () => {
                toast.add('Error', { type: 'danger', description: 'Unable to update media details.', life: 3000 });
            },
        },
    );
};

const handleCreateTag = async (name: string) => {
    try {
        const { data: response } = await createTag.mutateAsync({ name });

        toast.add('Success', { type: 'success', description: 'Tag created!', life: 3000 });
        form.fields['video_tags'] = [...form.fields['video_tags'], { id: response.id, name: response.name }];
    } catch (error) {
        console.log(error);
        toast.error('Unable to create tag. It may already exist.');
    }
};

const handleSetTags = (newTags: VideoTagResource[]) => {
    const existingVideoTags = props.video.video_tags ?? [];

    form.fields['video_tags'] = newTags.map((tag) => ({
        id: tag.id,
        name: tag.name,
        video_tag_id: existingVideoTags.find((vt) => vt.id === tag.id)?.video_tag_id,
    }));

    // Take tags from existing list
    form.fields['deleted_tags'] = existingVideoTags.filter((videoTag) => !newTags.some((tag) => tag.id === videoTag.id)).map((videoTag) => videoTag.video_tag_id);
};

const handleRemoveTag = (tag: VideoTagResource) => {
    console.log(tag);

    form.fields['video_tags'] = form.fields['video_tags']?.filter((itm) => itm.name !== tag.name);

    if (tag.video_tag_id) form.fields['deleted_tags'] = [...form.fields['deleted_tags'], tag.video_tag_id];
};

watch(tagsQuery, () => {
    if (tagsQuery.value?.data?.data) {
        allTags.value = tagsQuery.value.data.data; // Array of tag resources
    }
});
</script>

<template>
    <form class="flex flex-col sm:flex-row sm:justify-between flex-wrap gap-4" @submit.prevent="handleSubmit">
        <div v-for="(field, index) in fields.filter((field) => !field.disabled)" :key="index" class="w-full" :class="field.class">
            <FormInputLabel :field="field" />

            <FormTextArea v-if="field.type === 'textArea'" v-model="form.fields[field.name]" :field="field" />
            <DatePicker v-else-if="field.type === 'date'" v-model="form.fields[field.name]" :field="field" />
            <FormInputNumber v-else-if="field.type === 'number'" v-model="form.fields[field.name]" :field="field" />
            <InputMultiChip
                v-else-if="field.name === 'video_tags'"
                :placeholder="'Add tags'"
                :defaultItems="(form.fields[field.name] as SelectItem[]) ?? []"
                :options="allTags"
                :max="field.max"
                @createAction="handleCreateTag"
                @selectItems="handleSetTags"
                @removeAction="handleRemoveTag"
            />
            <FormInput v-else v-model="form.fields[field.name]" :field="field" />
            <FormErrorList>
                <li v-for="(item, index) in form.errors[field.name]" :key="index">{{ item }}</li>
            </FormErrorList>
        </div>

        <div class="relative flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-1 w-full">
            <button
                @click="$emit('handleFinish')"
                type="button"
                class="inline-flex items-center justify-center h-10 px-4 py-2 text-sm font-medium transition-colors border dark:border-neutral-600 rounded-md focus:outline-none"
                :class="'focus:ring-1 focus:ring-neutral-100 dark:focus:ring-neutral-400 focus:ring-offset-1 hover:bg-neutral-100 dark:hover:bg-neutral-900'"
                :disabled="form.processing"
            >
                Cancel
            </button>
            <button
                @click="handleSubmit"
                type="button"
                class="inline-flex items-center justify-center h-10 px-4 py-2 text-sm font-medium text-white transition-colors border border-transparent rounded-md focus:outline-none"
                :class="'focus:ring-1 focus:ring-violet-900 focus:ring-offset-1 bg-neutral-950 hover:bg-neutral-800 dark:hover:bg-neutral-900 '"
                :disabled="form.processing"
            >
                Submit Details
            </button>
        </div>
    </form>
</template>
