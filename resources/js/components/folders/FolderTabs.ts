import type { ComputedRef, Ref } from 'vue';
import type { FolderResource } from '@/contracts/media';

import { formatFileSize, toPlural, toTimeSpan } from '@/service/util';
import { createTabStore } from '@/stores/TabStore';
import { storeToRefs } from 'pinia';

import LucideLayoutDashboard from '~icons/lucide/layout-dashboard';
import ProiconsInfoSquare from '~icons/proicons/info-square';
import ProiconsVideoClip from '~icons/proicons/video-clip';
import ProiconsMusicNote from '~icons/proicons/music-note';
import ProiconsGraph from '~icons/proicons/graph';
import ProiconsPhoto from '~icons/proicons/photo';

export function useFolderTabs(stateFolder: Ref<FolderResource>, isAudio: ComputedRef<boolean>) {
    const tabsStore = createTabStore(`folder-info`, () => [
        { name: 'overview', icon: LucideLayoutDashboard, description: `${stateFolder.value.title}` },
        {
            name: 'files',
            icon: isAudio ? ProiconsMusicNote : ProiconsVideoClip,
            description: `${stateFolder.value.file_count} ${isAudio.value ? 'Track' : 'Video'}${toPlural(stateFolder.value.file_count)}`,
        },
        { name: 'images', icon: ProiconsPhoto, description: `${stateFolder.value.series?.images?.length ?? 0} Image${toPlural(stateFolder.value.series?.images?.length ?? 0)}` },
        {
            name: 'metadata',
            icon: ProiconsInfoSquare,
            description: stateFolder.value.edited_at ? `Edited ${toTimeSpan(stateFolder.value.edited_at, '')}` : 'Never Edited',
        },
        { name: 'stats', icon: ProiconsGraph, description: `Total Size ${formatFileSize(stateFolder.value.total_size ?? 0)}` },
    ])();

    const { activeTab: activeFolderTab, tabs } = storeToRefs(tabsStore);

    return { tabs, activeFolderTab, setTab: tabsStore.setTab };
}
