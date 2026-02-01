<script setup lang="ts">
import type { LyricsUpdateRequest } from '@/types/requests';
import type { VideoResource } from '@/types/resources';
import type { FormField } from '@aminnausin/cedar-ui';

import { generateLyricsSearchQuery, updateLyrics } from '@/service/lyricsService';
import { computed, onMounted, reactive, watch } from 'vue';
import { FormInput, FormLabel, FormErrorList } from '@/components/cedar-ui/form';
import { ButtonForm, ButtonText } from '@/components/cedar-ui/button';
import { TableLoadingSpinner } from '@/components/cedar-ui/table';
import { toFormattedDuration } from '@/service/util';
import { useDateFieldModel } from '@/components/cedar-ui/date-picker/useDateFieldModel';
import { FormNumberField } from '@/components/cedar-ui/number-field';
import { useLyricStore } from '@/stores/LyricStore';
import { FormTextArea } from '@/components/cedar-ui/textarea';
import { storeToRefs } from 'pinia';
import { DatePicker } from '@/components/cedar-ui/date-picker';
import { toast } from '@aminnausin/cedar-ui';

import LrcLibCard from '@/components/cards/data/LrcLibCard.vue';
import useForm from '@/composables/useForm';

const { isLoadingLyrics, stateLyrics, searchResults, hasSearchedForLyrics, dirtyLyric } = storeToRefs(useLyricStore());
const { handlePreviewLyrics, handleSelectLyrics, handleSearchSyncedLyrics, resetLyrics } = useLyricStore();

const emit = defineEmits(['handleFinish', 'preview']);
const props = defineProps<{ video: VideoResource }>();

const fields = reactive<FormField[]>([
    {
        name: 'track',
        text: 'Track Title',
        type: 'text',
        required: true,
        value: props.video.title,
        default: props.video.title,
        max: 255,
    },
    {
        name: 'artist',
        text: 'Artist Name',
        type: 'text',
        required: true,
        placeholder: 'Use the artist name to refine your search',
        value: dirtyLyric.value?.artistName,
        max: 255,
    },
    {
        name: 'album',
        text: 'Album Name',
        type: 'text',
        required: true,
        placeholder: 'Use the album name to refine your search',
        value: dirtyLyric.value?.albumName,
        max: 255,
    },
    {
        name: 'duration',
        text: `Song Duration`,
        type: 'text',
        value: toFormattedDuration(props.video.metadata?.duration),
        disabled: !props.video.metadata?.duration,
        subtext: 'Try to match this value',
        placeholder: `No duration set`,
    },
    {
        name: 'lyrics',
        text: `Lyrics`,
        type: 'textArea',
        value: props.video.metadata?.lyrics,
        subtext: 'Format: [mm:ss] line',
        placeholder: `No lyrics yet`,
        default: '',
    },
]);

const form = useForm<LyricsUpdateRequest>({
    track: props.video.metadata?.title ?? props.video.title ?? '',
    lyrics: props.video.metadata?.lyrics ?? '',
    artist: props.video.metadata?.artist ?? '',
    album: props.video.metadata?.album ?? '',
});

const changedMetadata = computed(() => {
    const changes = {
        lyrics: !!(props.video.metadata?.lyrics && form.fields.lyrics !== props.video.metadata.lyrics),
        artist: !!(props.video.metadata?.artist && form.fields.artist !== props.video.metadata.artist),
        album: !!(props.video.metadata?.album && form.fields.album !== props.video.metadata.album),
    };

    return { isDirty: changes.lyrics || changes.artist || changes.album, ...changes };
});

const handleSubmit = async () => {
    form.submit(
        async (fields) => {
            if (!props.video.metadata?.id) {
                throw new Error('Metadata Malformed!');
            }
            return updateLyrics(props.video.metadata.id, fields);
        },
        {
            onSuccess: (response) => {
                emit('handleFinish', response?.data);
                toast.add('Success', { type: 'success', description: 'Edit submitted!', life: 3000 });
            },
            onError: () => {
                toast.add('Error', { type: 'danger', description: 'Unable to update lyrics details.', life: 3000 });
            },
        },
    );
};

const handleLoadLyricInfo = () => {
    if (!dirtyLyric.value?.id) {
        form.reset(...Object.keys(form.fields));
        return;
    }

    form.fields.album = dirtyLyric.value.albumName;
    form.fields.artist = dirtyLyric.value.artistName;
    form.fields.lyrics = stateLyrics.value;
};

const handleResetFields = () => {
    form.fields = { ...form.fields, ...buildDefaultData() };
    resetLyrics();
};

const buildDefaultData = () => {
    return {
        track: props.video.metadata?.title ?? props.video.title ?? '',
        lyrics: props.video.metadata?.lyrics ?? '',
        artist: props.video.metadata?.artist ?? '',
        album: props.video.metadata?.album ?? '',
    };
};

onMounted(() => {
    handleLoadLyricInfo();
});

watch(
    () => dirtyLyric.value,
    () => {
        handleLoadLyricInfo();
    },
);
</script>

<template>
    <form class="flex flex-col flex-wrap gap-4 text-sm sm:flex-row sm:justify-between" @submit.prevent="handleSubmit">
        <div v-for="(field, index) in fields.filter((field) => !field.disabled)" :key="index" class="w-full" :class="field.class">
            <FormLabel :for="field.name" :text="field.text" :subtext="field.subtext" />
            <FormInput v-if="field.name === 'duration'" :field="field" v-model="field.value" disabled title="Song Duration" />
            <FormTextArea v-else-if="field.type === 'textArea'" v-model="form.fields[field.name]" :field="field" />
            <DatePicker v-else-if="field.type === 'date'" v-model="useDateFieldModel(form, field.name).value" :field="field" />
            <FormNumberField v-else-if="field.type === 'number'" v-model="form.fields[field.name]" :field="field" />
            <FormInput v-else v-model="form.fields[field.name]" :field="field" />
            <FormErrorList :errors="form.errors" :field-name="field.name" />
        </div>

        <div class="xs:*:w-fit xs:flex-row flex w-full flex-col items-center gap-2 *:w-full">
            <ButtonText
                class="lyrics-button *:text-center sm:px-3"
                :disabled="isLoadingLyrics"
                @click="handleSearchSyncedLyrics(generateLyricsSearchQuery(video.metadata, form.fields.track, form.fields.album, form.fields.artist))"
                text="Search for Lyrics"
                title="Search for Lyrics"
            />
            <ButtonText :disabled="isLoadingLyrics" @click="resetLyrics" class="lyrics-button *:text-center sm:px-3" text="Reset Lyrics" title="Reset Lyrics" />

            <p class="xs:ml-auto min-w-fit text-center text-nowrap" v-show="hasSearchedForLyrics && searchResults?.length">Results: {{ searchResults?.length }}</p>
        </div>

        <div class="flex w-full flex-col gap-2" v-if="isLoadingLyrics || hasSearchedForLyrics || searchResults?.length !== 0">
            <LrcLibCard v-for="result in searchResults" :key="result.id" :data="result" @preview="handlePreviewLyrics(result)" @select="handleSelectLyrics(result)" />

            <TableLoadingSpinner v-if="isLoadingLyrics || (hasSearchedForLyrics && searchResults?.length === 0)" :is-loading="isLoadingLyrics" class="text-sm" />
        </div>

        <ButtonText
            v-if="!hasSearchedForLyrics && searchResults?.length === 1"
            :disabled="isLoadingLyrics"
            @click="handleSearchSyncedLyrics(generateLyricsSearchQuery(video.metadata, form.fields.track, form.fields.album, form.fields.artist))"
            class="lyrics-button w-full *:text-center sm:px-3"
            text="Load More Results"
            title="Load More Results"
        />

        <div class="text-danger-2 w-full text-center dark:text-rose-400" v-if="changedMetadata.isDirty">
            <p v-show="changedMetadata.lyrics">Overwriting Existing Lyrics!</p>

            <p v-show="changedMetadata.artist">Overwriting Existing Artist Name!</p>

            <p v-show="changedMetadata.album">Overwriting Existing Album Name!</p>
        </div>

        <div class="relative mt-4 flex w-full flex-col-reverse gap-2 *:h-9 sm:flex-row sm:justify-end">
            <ButtonForm
                @click="
                    () => {
                        $emit('handleFinish');
                        resetLyrics();
                    }
                "
                variant="reset"
                :disabled="form.processing"
            >
                Cancel
            </ButtonForm>
            <ButtonForm @click="handleResetFields" variant="danger" :disabled="form.processing"> Reset </ButtonForm>
            <ButtonForm @click="handleSubmit" variant="submit" :disabled="form.processing"> Save </ButtonForm>
        </div>
    </form>
</template>

<style lang="css" scoped>
.lyrics-button {
    &:is(.dark *) {
        --color-r-button: var(--color-neutral-700); /* neutral-700 */
    }
}
</style>
