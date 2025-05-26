import { reactive } from 'vue';

export default function useModal(props) {
    let closingTimeoutID;
    return reactive({
        title: '',
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

            closingTimeoutID = window.setTimeout(() => {
                this.isAnimating = false;
            }, this.animationTime);
        },
        setTitle(title) {
            if (title && this?.title) this.title = title;
        },
    });
}
