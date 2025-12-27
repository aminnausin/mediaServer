<script setup lang="ts">
import type { TagResource, VideoResource, VideoTagResource } from '@/types/resources';
import type { FormField, SelectItem } from '@/types/types';
import type { MetadataUpdateRequest } from '@/types/requests';

import { computed, reactive, ref, watch } from 'vue';
import { toCalendarFormattedDate } from '@/service/util';
import { useContentStore } from '@/stores/ContentStore';
import { FormTextArea } from '@/components/cedar-ui/textarea';
import { useGetAllTags } from '@/service/queries';
import { UseCreateTag } from '@/service/mutations';
import { storeToRefs } from 'pinia';
import { MediaType } from '@/types/types';
import { FormLabel } from '@/components/cedar-ui/form';
import { toast } from '@aminnausin/cedar-ui';

import FormInputNumber from '@/components/inputs/FormInputNumber.vue';
import InputMultiChip from '@/components/pinesUI/InputMultiChip.vue';
import FormErrorList from '@/components/labels/FormErrorList.vue';
import DatePicker from '@/components/pinesUI/DatePicker.vue';
import FormInput from '@/components/inputs/FormInput.vue';
import mediaAPI from '@/service/mediaAPI.ts';
import useForm from '@/composables/useForm';

const { stateFolder } = storeToRefs(useContentStore());

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
        disabled: !isAudio.value && !stateFolder.value.is_majority_audio,
        default: '',
    },
    {
        name: 'artist',
        text: `Artist`,
        type: 'text',
        value: props.video?.metadata?.artist,
        placeholder: `No artist yet.`,
        disabled: !isAudio.value,
        default: '',
        max: 255,
    },
    {
        name: 'album',
        text: `Album`,
        type: 'text',
        value: props.video?.metadata?.album,
        placeholder: `No album yet.`,
        disabled: !isAudio.value,
        default: '',
        max: 255,
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
    artist: props.video?.metadata?.artist ?? '',
    album: props.video?.metadata?.album ?? '',
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
    <form class="flex flex-col flex-wrap gap-4 sm:flex-row sm:justify-between" @submit.prevent="handleSubmit">
        <div v-for="(field, index) in fields.filter((field) => !field.disabled)" :key="index" class="w-full" :class="field.class">
            <FormLabel :for="field.name" :text="field.text" :subtext="field.subtext" class="capitalize" />

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
            <FormErrorList :errors="form.errors" :field-name="field.name" />
        </div>

        <div class="relative flex w-full flex-col-reverse gap-2 pt-1 sm:flex-row sm:justify-end">
            <button
                @click="$emit('handleFinish')"
                type="button"
                class="inline-flex h-10 items-center justify-center rounded-md border px-4 py-2 text-sm font-medium transition-colors focus:outline-hidden dark:border-neutral-600"
                :class="'hover:bg-neutral-100 focus:ring-1 focus:ring-neutral-100 focus:ring-offset-1 dark:hover:bg-neutral-900 dark:focus:ring-neutral-400'"
                :disabled="form.processing"
            >
                Cancel
            </button>
            <button
                @click="handleSubmit"
                type="button"
                class="inline-flex h-10 items-center justify-center rounded-md border border-transparent px-4 py-2 text-sm font-medium text-white transition-colors focus:outline-hidden"
                :class="'bg-neutral-950 hover:bg-neutral-800 focus:ring-1 focus:ring-violet-900 focus:ring-offset-1 dark:hover:bg-neutral-900'"
                :disabled="form.processing"
            >
                Submit Details
            </button>
        </div>
    </form>
</template>
