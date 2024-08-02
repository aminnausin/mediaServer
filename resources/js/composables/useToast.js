import { reactive } from "vue";

export default function useToast(props) {
    const toast = reactive({
        title: props?.title ?? "Default Toast Notification",
        description: props?.description ?? "",
        type: props?.type ?? "default",
        position: "top-center",
        expanded: false,
        popToast(title, type, description) {
            if(title) this.title = title;
            if(type) this.type = type;
            if(description) this.description = description;
            if(props?.emit) {
                props.emit('toast-show', window.toast(this.title, {
                    description: this.description,
                    type: this.type,
                    position: this.position,
                    html: null,
                }));
            }
        },
    });

    return toast;
}
