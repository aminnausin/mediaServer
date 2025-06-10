import { reactive, ref, type Ref } from 'vue';
import { toast } from '@/service/toaster/toastService';

interface UseClipboard {
    copyText: string;
    copyNotification: boolean;
    copyToClipboard: () => void;
}

export default function useClipboard(copyText: Ref<string>) {
    const copyNotification = ref(false);

    const copyToClipboard = async () => {
        try {
            await navigator.clipboard.writeText(copyText.value);
            copyNotification.value = true;
            setTimeout(() => {
                copyNotification.value = false;
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
