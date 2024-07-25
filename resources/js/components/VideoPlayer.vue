<script setup>
    import { storeToRefs } from 'pinia';
    import { useContentStore } from '../stores/ContentStore';
    import { ref, watch } from 'vue';

    const pastFirst = ref(false);
    const ContentStore = useContentStore();
    const { stateVideo } = storeToRefs(ContentStore);
    const { addRecord } = ContentStore;

    const initVideoPlayer = () => {
        let vidSource = document.getElementById('vid-source');
        let root = document.getElementById('root');

        root.scrollIntoView();

        if(pastFirst.value === true) vidSource.play();
    }

    const playVideo = () => {
        pastFirst.value = true;
        addRecord(stateVideo.value.id)
    }

    watch(stateVideo, initVideoPlayer)
</script>

<template>
    <video id="vid-source" width="100%" :src="`../${stateVideo?.attributes?.path}`" type="video/mp4" controls class="focus:outline-none aspect-video" @play="playVideo" :autoplay="pastFirst === true">
        <track kind="captions">
    </video>
</template>