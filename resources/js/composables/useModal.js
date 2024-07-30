import { reactive, ref } from "vue";

export default function useModal(props){
    const modalOpen = ref(false);

    const toggleModal = (state = null) => {
        if (state != null) modalOpen.value = state;
        else modalOpen.value = !modalOpen.value;
    }

    return reactive({
        props,
        modalOpen,
        toggleModal
    });
}