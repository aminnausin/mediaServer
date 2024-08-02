import { reactive } from "vue";
import { useToast } from '../composables/useToast';

export default function useClipboard(copyText) {

    const toast = useToast();
    return reactive({
        copyText,
        copyNotification: false,
        copyToClipboard() {
            try {
                navigator.clipboard.writeText(this.copyText);
                this.copyNotification = true;
                let that = this;
                setTimeout(function () {
                    that.copyNotification = false;
                }, 3000);
            } catch (error) {
                console.log(error);
                toast.add({ type: 'danger', title:'Error', description:'Unable to copy. Network is not secure.', life: 3000});
            }
        }
    });
}