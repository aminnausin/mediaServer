import { reactive } from "vue";

export default function useClipboard(copyText) {
    return reactive({
        copyText,
        copyNotification: false,
        copyToClipboard() {
            navigator.clipboard.writeText(this.copyText);
            this.copyNotification = true;
            let that = this;
            setTimeout(function () {
                that.copyNotification = false;
            }, 3000);
        }
    });
}