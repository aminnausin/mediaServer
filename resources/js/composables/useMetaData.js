import { reactive, ref } from "vue";
import { toFormattedDuration } from "@/service/util";

// This so does not work lol
export default function useMetaData(data) {
    const fields = ref({
        title: data?.title ?? data?.name,
        duration: toFormattedDuration(data?.duration) ?? 'N/A',
        views: data?.view_count ? `${data?.view_count} View${data?.view_count !== 1 ? 's' : ''}` : '0 Views',
    })

    const updateData = (props) => {
        fields.value = {
            title: props?.title ?? props?.name,
            duration: toFormattedDuration(props?.duration) ?? 'N/A',
            views: props?.view_count ? `${props?.view_count} View${props?.view_count !== 1 ? 's' : ''}` : '0 Views',
        }
    }
    
    return reactive({
        fields,
        updateData
    });
}

// const videoMetaData = computed(() => {
//     const fields = props.video.attributes;
//     const output = {
//         title: fields?.title ?? fields?.name,
//         duration: toFormattedDuration(fields?.duration) ?? 'N/A',
//         views: fields?.view_count ? `${fields?.view_count} View${fields?.view_count !== 1 ? 's' : ''}` : '0 Views',
//     }

//     return output;
// })