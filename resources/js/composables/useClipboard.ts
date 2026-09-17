import type { Ref } from 'vue';

import { toast } from '@aminnausin/cedar-ui';
import { ref } from 'vue';

export default function useClipboard(copyText?: Ref<string>) {
    const copyNotification = ref<Set<string>>(new Set());

    const copyToClipboard = async (text?: string, key?: string) => {
        const payload = text ?? copyText?.value;

        if (!payload) {
            toast.warning('Nothing to copy.');
            return;
        }

        try {
            await navigator.clipboard.writeText(payload);
            copyNotification.value.add(key ?? payload);
            setTimeout(() => {
                copyNotification.value.delete(key ?? payload);
            }, 3000);
        } catch (error) {
            console.error(error);
            toast.error('Error', {
                description: 'Unable to copy. Network is not secure.',
            });
        }
    };

    return {
        copyNotification,
        copyToClipboard,
    };
}
