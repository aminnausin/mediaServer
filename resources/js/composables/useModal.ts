import { reactive, ref } from 'vue';

export interface ModalProps {
    title?: string;
    submitText?: string;
    animationTime?: number;
}

export default function useModal(props: ModalProps) {
    const closingTimeoutID = ref<number | null>(null);
    return reactive({
        title: '',
        ...props,
        modalOpen: false,
        isAnimating: false,
        animationTime: props.animationTime ?? 300,
        toggleModal(state: boolean | null = null) {
            if (this.isAnimating) return;
            if (closingTimeoutID.value) clearTimeout(closingTimeoutID.value);

            if (state != null) this.modalOpen = state;
            else this.modalOpen = !this.modalOpen;

            this.isAnimating = true;

            closingTimeoutID.value = window.setTimeout(() => {
                this.isAnimating = false;
            }, this.animationTime);
        },
        setTitle(title: string) {
            this.title = title;
        },
    });
}
