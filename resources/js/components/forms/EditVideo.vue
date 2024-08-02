<script setup>
import useForm from '../../composables/useForm';
import useToast from '../../composables/useToast';
import mediaAPI from '../../service/mediaAPI';
import FormInput from '../inputs/FormInput.vue';
import FormTextArea from '../inputs/FormTextArea.vue';
import FormInputLabel from '../labels/FormInputLabel.vue';

import { reactive } from 'vue';

const emit = defineEmits(['handleFinish','toast-show']);
const props = defineProps(['video']);

const toast = useToast({emit});

const fields = reactive([
    { 
        name: 'title', 
        text: 'Title', 
        type: 'text', 
        required: true, 
        value: props.video?.attributes.title,
        default: props.video?.attributes.name,
        subtext: 'The intended title of the episode',
        max: 255
    },
    { 
        name: 'description', 
        text: 'Description', 
        type: 'textArea', 
        value: props.video?.attributes.description,
        default: ''
    },
    { 
        name: 'episode', 
        text: 'Episode', 
        type: 'number', 
        value: props.video?.attributes.episode ?? 1, 
        default: 0,
        min: 0,
    },
    { 
        name: 'season', 
        text: 'Season', 
        type: 'number', 
        value: props.video?.attributes.season ?? 1, 
        default: 0,
        min: 0,
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
                toast.popToast('Edit submitted!');
            },
            onError: () => {
                toast.popToast('Unable to update video details.');
            },
        }
    )
}
</script>

<template>
    <form class="flex flex-col sm:flex-row sm:justify-between flex-wrap gap-4" @submit.prevent="handleSubmit">
        <div v-for="(field, index) in fields" :key="index" class='w-full' :class="field.class">

            <FormInputLabel :field="field"/>

            <FormTextArea v-if="field.type === 'textArea'" v-model="form.fields[field.name]" :field="field"/>
            <FormInput v-else v-model="form.fields[field.name]" :field="field" />

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