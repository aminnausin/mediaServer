<script setup>
    const { URL } = defineProps(['URL'])
</script>

<template>
    <video id="vid-source" width="100%" :src="URL" type="video/mp4" controls class="focus:outline-none aspect-video">
        <track kind="captions">
    </video>
</template>