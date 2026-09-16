<script setup lang="ts">
import type { MediaInfoResource } from '@/contracts/mediaInfo';
import type { VideoResource } from '@/contracts/media';

import ProiconsChevronRight from '~icons/proicons/chevron-right';

defineProps<{ data: VideoResource; mediaInfo: MediaInfoResource }>();
</script>

<template>
    <div class="flex flex-col gap-4">
        <details class="group/raw-metadata flex flex-col open:gap-2">
            <summary class="hover:text-foreground-0 flex w-fit cursor-pointer items-center gap-1.5 transition-colors">
                <ProiconsChevronRight class="-ms-1 size-3 transition-transform duration-200 group-open/raw-metadata:rotate-90" />

                Raw Metadata
            </summary>

            <pre class="scrollbar-minimal bg-surface-2 text-foreground-1 max-h-90 overflow-x-auto rounded-lg p-3 text-xs leading-relaxed whitespace-pre-wrap shadow-sm">{{
                JSON.stringify(
                    {
                        ...data,
                        metadata: {
                            ...data.metadata,
                            lyrics: '...',
                        },
                        fonts: '...',
                    },
                    null,
                    2,
                )
            }}</pre>
        </details>

        <details class="group/raw-info flex flex-col open:gap-2">
            <summary class="hover:text-foreground-0 flex w-fit cursor-pointer items-center gap-1.5 transition-colors">
                <ProiconsChevronRight class="-ms-1 size-3 transition-transform duration-200 group-open/raw-info:rotate-90" />

                Raw Media Info
            </summary>

            <pre class="scrollbar-minimal bg-surface-2 text-foreground-1 max-h-90 overflow-x-auto rounded-lg p-3 text-xs leading-relaxed whitespace-pre-wrap shadow-sm">{{
                JSON.stringify(mediaInfo.raw_metadata, null, 2)
            }}</pre>
        </details>
    </div>
</template>
