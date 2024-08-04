import { reactive } from "vue";
import { toFormattedDuration } from "@/service/util";
import { useRoute } from "vue-router";

// This so does not work lol
export default function useMetaData(data) {
    const route = useRoute();

    return reactive({
        data,
        fields: {
            title: data?.title ?? data?.name,
            duration: toFormattedDuration(data?.duration) ?? 'N/A',
            views: data?.view_count ? `${data?.view_count} View${data?.view_count !== 1 ? 's' : ''}` : '0 Views',
            description: data.description ?? '',
            url: (data?.skipBaseURL ? '' : document.location.origin) + route.path + `?video=${data.id}`,
        },
        updateData(props){
            this.fields.title = props?.title ?? props?.name,
            this.fields.duration = toFormattedDuration(props?.duration) ?? 'N/A',
            this.fields.views = props?.view_count ? `${props?.view_count} View${props?.view_count !== 1 ? 's' : ''}` : '0 Views',
            this.fields.description = props?.description ?? '',
            this.fields.url = (props?.skipBaseURL ? '' : document.location.origin) + route.path + `?video=${props.id}`
        }
    });
}