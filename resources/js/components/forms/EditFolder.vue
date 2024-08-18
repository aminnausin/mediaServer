<script setup>
import useForm from '../../composables/useForm';
import mediaAPI from '../../service/mediaAPI';
import FormInput from '../inputs/FormInput.vue';
import FormTextArea from '../inputs/FormTextArea.vue';
import FormInputLabel from '../labels/FormInputLabel.vue';

import { reactive } from 'vue';
import { useToast } from '../../composables/useToast';
import DatePicker from '../pinesUI/DatePicker.vue';
import FormInputNumber from '../inputs/FormInputNumber.vue';

const emit = defineEmits(['handleFinish']);
const props = defineProps(['video']);

const toast = useToast();


'title',
'description',
'studio',
'seasons',
'episodes',
'films',
'date_start',
'date_end',
'thumbnail_url',
'editor_id'
const fields = reactive([
    { 
        name: 'title', 
        text: 'Title', 
        type: 'text', 
        required: true, 
        value: props.folder?.series?.attributes.title,
        default: props.folder?.series?.attributes.title,
        subtext: 'The intended title of the episode',
        max: 255
    },
    { 
        name: 'description', 
        text: 'Description', 
        type: 'textArea', 
        value: props.folder?.series?.attributes.description,
        default: ''
    },
    { 
        name: 'studio', 
        text: 'Studio', 
        type: 'text', 
        value: props.folder?.series?.attributes.studio,
        subtext: 'The producer',
        default: ''
    },
    { 
        name: 'episodes', 
        text: 'Episodes', 
        type: 'number', 
        value: props.folder?.series?.attributes.episodes ?? 1, 
        subtext: 'The number of episodes in the series',
        default: 0,
        min: 0,
    },
    { 
        name: 'seasons', 
        text: 'Seasons', 
        type: 'number', 
        value: props.folder?.series?.attributes.seasons ?? 1, 
        subtext: 'The number of seasons in the series',
        default: 0,
        min: 0,
    },
    { 
        name: 'films', 
        text: 'Films', 
        type: 'number', 
        value: props.folder?.series?.attributes.films ?? 1, 
        subtext: 'The number of films in the series',
        default: 0,
        min: 0,
    },
    { 
        name: 'date_start', 
        text: 'Start Date', 
        type: 'date', 
        value: props.folder?.series?.attributes.date_start, 
        default: null,
    },
    { 
        name: 'date_end', 
        text: 'End Date', 
        type: 'date', 
        value: props.folder?.series?.attributes.date_end, 
        default: null,
    },
]);

const form = useForm({ 
    title: props.video?.attributes.title ?? props.video?.attributes.name, 
    description: props.video?.attributes.description ?? '', 
    episode: props.video?.attributes.episode ?? null, 
    season: props.video?.attributes.season ?? null,
});

const handleSubmit = async () => {
    form.submit(
        async (fields) => {
            return mediaAPI.updateVideo(props.video.id, fields);
        },
        {
            onSuccess: (response) => {
                emit('handleFinish', response?.data);
                toast.add({ type: 'success', title:'Success', description:'Edit submitted!', life: 3000});
            },
            onError: () => {
                toast.add({ type: 'danger', title:'Error', description:'Unable to update video details.', life: 3000});
            },
        }
    )
}
</script>

<template>
    <form class="flex flex-col sm:flex-row sm:justify-between flex-wrap gap-4" @submit.prevent="handleSubmit">
        <div v-for="(field, index) in fields" :key="index" class='w-full' :class="field.class">

            <FormInputLabel :field="field"/>

            <FormTextArea v-if="field.type === 'textArea'" v-model="form.fields[field.name]" :field="field" :tabindex="index + 1"/>
            <DatePicker v-else-if="field.type === 'date'"/>
            <FormInputNumber v-else-if="field.type === 'number'" v-model="form.fields[field.name]" :field="field" :tabindex="index + 1"/>
            <FormInput v-else v-model="form.fields[field.name]" :field="field" :tabindex="index + 1"/>
            <ul class="text-sm text-red-600 dark:text-red-400">
                <li v-for="(item, index) in form.errors[field.name]" :key="index">{{ item }}</li>
            </ul>
        </div>
        <div class="relative flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-1 w-full">
            <button @click="$emit('handleFinish')" type="button" 
                class="inline-flex items-center justify-center h-10 px-4 py-2 text-sm font-medium transition-colors border dark:border-neutral-600 rounded-md focus:outline-none "
                :class="'focus:ring-1 focus:ring-neutral-100 dark:focus:ring-neutral-400 focus:ring-offset-1 hover:bg-neutral-100 dark:hover:bg-neutral-900'">Cancel</button>
            <button @click="handleSubmit" type="button" 
                class="inline-flex items-center justify-center h-10 px-4 py-2 text-sm font-medium text-white transition-colors border border-transparent rounded-md focus:outline-none "
                :class="'focus:ring-1 focus:ring-violet-900 focus:ring-offset-1 bg-neutral-950 hover:bg-neutral-800 dark:hover:bg-neutral-900 '">Submit Details</button>
        </div>
    </form>
</template>