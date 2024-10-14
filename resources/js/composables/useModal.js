import { reactive } from 'vue';

export default function useModal(props) {
    let closingTimeoutID;
    const modal = reactive({
        ...props,
        modalOpen: false,
        isAnimating: false,
        animationTime: props.animationTime ?? 300,
        toggleModal(state = null) {
            if (this.isAnimating) return;
            clearTimeout(closingTimeoutID);

            if (state != null) this.modalOpen = state;
            else this.modalOpen = !this.modalOpen;

            this.isAnimating = true;

            closingTimeoutID = setTimeout(() => {
                this.isAnimating = false;
            }, this.animationTime);
        },
    });
    return modal;
}
