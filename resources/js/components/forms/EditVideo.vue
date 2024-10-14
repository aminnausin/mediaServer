<script setup>
import useForm from '../../composables/useForm';
import mediaAPI from '@/service/mediaAPI.ts';
import FormInput from '../inputs/FormInput.vue';
import FormTextArea from '../inputs/FormTextArea.vue';
import FormInputLabel from '../labels/FormInputLabel.vue';
import DatePicker from '../pinesUI/DatePicker.vue';
import FormInputNumber from '../inputs/FormInputNumber.vue';

import { reactive } from 'vue';
import { useToast } from '../../composables/useToast';

const emit = defineEmits(['handleFinish']);
const props = defineProps(['video']);

const toast = useToast();

const fields = reactive([
    {
        name: 'title',
        text: 'Title',
        type: 'text',
        required: true,
        value: props.video?.title,
        default: props.video?.name,
        subtext: 'The intended title of the episode',
        max: 255,
    },
    {
        name: 'description',
        text: 'Description',
        type: 'textArea',
        value: props.video?.description,
        default: '',
    },
    {
        name: 'episode',
        text: 'Episode',
        type: 'number',
        value: props.video?.episode ?? 1,
        default: 0,
        min: 0,
    },
    {
        name: 'season',
        text: 'Season',
        type: 'number',
        value: props.video?.season ?? 1,
        default: 0,
        min: 0,
    },
    {
        name: 'release_date',
        text: 'Release Date',
        type: 'date',
        value: props.video?.release_date ?? null,
        default: null,
    },
    {
        name: 'tags',
        text: 'Tags',
        type: 'text',
        value: props.video?.tags,
        default: props.video?.tags,
        subtext: 'Tags that describe the video',
        max: 128,
    },
]);

const form = useForm({
    title: props.video?.title ?? props.video?.name,
    description: props.video?.description ?? '',
    episode: props.video?.episode ?? null,
    season: props.video?.season ?? null,
    date_released: props.video?.date_released ?? null,
    tags: props.video?.tags ?? null,
});

const handleSubmit = async () => {
    form.submit(
        async (fields) => {
            console.log(props.video);

            // return mediaAPI.updateMetadata(props.video.relationships.metadata.id, fields);
            if (props.video.relationships?.metadata?.id) {
                return mediaAPI.updateMetadata(props.video.relationships.metadata.id, fields);
            } else return mediaAPI.createMetadata({ ...fields, video_id: props.video.id });
        },
        {
            onSuccess: (response) => {
                emit('handleFinish', response?.data);
                toast.add({ type: 'success', title: 'Success', description: 'Edit submitted!', life: 3000 });
            },
            onError: () => {
                toast.add({ type: 'danger', title: 'Error', description: 'Unable to update video details.', life: 3000 });
            },
        },
    );
};
</script>

<template>
    <form class="flex flex-col sm:flex-row sm:justify-between flex-wrap gap-4" @submit.prevent="handleSubmit">
        <div v-for="(field, index) in fields" :key="index" class="w-full" :class="field.class">
            <FormInputLabel :field="field" />

            <FormTextArea v-if="field.type === 'textArea'" v-model="form.fields[field.name]" :field="field" :tabindex="index + 1" />
            <DatePicker v-else-if="field.type === 'date'" v-model="form.fields[field.name]" />
            <FormInputNumber v-else-if="field.type === 'number'" v-model="form.fields[field.name]" :field="field" :tabindex="index + 1" />
            <FormInput v-else v-model="form.fields[field.name]" :field="field" :tabindex="index + 1" />
            <ul class="text-sm text-red-600 dark:text-red-400">
                <li v-for="(item, index) in form.errors[field.name]" :key="index">{{ item }}</li>
            </ul>
        </div>
        <div class="relative flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-1 w-full">
            <button
                @click="$emit('handleFinish')"
                type="button"
                tabindex="97"
                class="inline-flex items-center justify-center h-10 px-4 py-2 text-sm font-medium transition-colors border dark:border-neutral-600 rounded-md focus:outline-none"
                :class="'focus:ring-1 focus:ring-neutral-100 dark:focus:ring-neutral-400 focus:ring-offset-1 hover:bg-neutral-100 dark:hover:bg-neutral-900'"
            >
                Cancel
            </button>
            <button
                @click="handleSubmit"
                type="button"
                tabindex="98"
                class="inline-flex items-center justify-center h-10 px-4 py-2 text-sm font-medium text-white transition-colors border border-transparent rounded-md focus:outline-none"
                :class="'focus:ring-1 focus:ring-violet-900 focus:ring-offset-1 bg-neutral-950 hover:bg-neutral-800 dark:hover:bg-neutral-900 '"
            >
                Submit Details
            </button>
        </div>
    </form>
</template>
