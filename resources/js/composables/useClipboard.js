import { toast } from '@/service/toaster/toastService';
import { reactive } from 'vue';

export default function useClipboard(copyText) {
    return reactive({
        copyText,
        copyNotification: false,
        copyToClipboard() {
            try {
                navigator.clipboard.writeText(this.copyText);
                this.copyNotification = true;
                window.setTimeout(function () {
                    this.copyNotification = false;
                }, 3000);
            } catch (error) {
                console.log(error);
                toast.add('Error', {
                    type: 'danger',
                    description: 'Unable to copy. Network is not secure.',
                    life: 3000,
                });
            }
        },
    });
}
